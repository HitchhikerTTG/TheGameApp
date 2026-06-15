<?php
namespace App\Services;

use App\Libraries\Postmark;
use App\Models\UserModel;
use App\Models\TerminarzModel;
use CodeIgniter\Database\BaseConnection;
use App\Models\TypyModel;


class EmailService
{
    private Postmark $postmark;
    private BaseConnection $db;

    public function __construct()
    {
        $this->postmark = new Postmark();
        $this->db       = \Config\Database::connect();
    }

    /**
     * Dodaje/aktualizuje wpis w kolejce z 3-minutowym rolling window.
     */
     
     
    public function queueBetSaved(string $uniID, int $gameID, string $homeScore, string $awayScore, int $goldenGame = 0): void

    {
        $userModel = model(UserModel::class);
        $user = $userModel->select('email, nick, notify_bet_saved')
                          ->where('uniID', $uniID)
                          ->first();

        if (!$user || empty($user['notify_bet_saved'])) {
            return;
        }

        
        $terminarzModel = model(\App\Models\TerminarzModel::class);
        $mecz = $terminarzModel->getMeczById($gameID);

        //$dataMeczu   = $mecz ? date('d.m.Y', strtotime($mecz['Date'])) . ' ' . substr($mecz['Time'], 0, 5) : "mecz #{$gameID}";
        //$nazwyDruzyn = $mecz ? "{$mecz['HomeName']} - {$mecz['AwayName']}" : '';
        
        if ($mecz) {
           $dt = new \DateTime($mecz['Date'] . ' ' . $mecz['Time'], new \DateTimeZone('UTC'));
            $dt->setTimezone(new \DateTimeZone('Europe/Warsaw'));
            $lokalnyczas = $dt->format('d.m.Y H:i');

            $jsonPath = WRITEPATH . "mecze/{$mecz['TurniejID']}/{$mecz['ApiID']}.json";
            $details  = file_exists($jsonPath) ? (json_decode(file_get_contents($jsonPath), true) ?? []) : [];
            $homeName = $details['home_team']['plName'] ?? $details['home_team']['name'] ?? $mecz['HomeName'];
            $awayName = $details['away_team']['plName'] ?? $details['away_team']['name'] ?? $mecz['AwayName'];

            $dataMeczu   = $lokalnyczas;
            $nazwyDruzyn = "{$homeName} - {$awayName}";
        } else {
            $dataMeczu   = "mecz #{$gameID}";
            $nazwyDruzyn = '';
        }

        $sendAfter = date('Y-m-d H:i:s', strtotime('+3 minutes'));

        $nowyTyp = [
            'gameID'     => $gameID,
            'data'       => $dataMeczu,
            'mecz'       => $nazwyDruzyn,
            'typH'       => $homeScore,
            'typA'       => $awayScore,
            'zlotaPilka' => $goldenGame === 1,
        ];

        $existing = $this->db->table('email_queue')
            ->where('uniID', $uniID)
            ->where('type', 'bet_saved')
            ->where('sent', 0)
            ->get()->getRow();

        if ($existing) {
            $typy = json_decode($existing->body, true) ?: [];
            $znaleziony = false;
            foreach ($typy as &$typ) {
                if ($typ['gameID'] === $gameID) {
                    $typ = $nowyTyp;
                    $znaleziony = true;
                    break;
                }
            }
            if (!$znaleziony) {
                $typy[] = $nowyTyp;
            }
            $this->db->table('email_queue')->where('id', $existing->id)->update([
                'body'       => json_encode($typy),
                'send_after' => $sendAfter,
            ]);
        } else {
            $this->db->table('email_queue')->insert([
                'uniID'      => $uniID,
                'type'       => 'bet_saved',
                'subject'    => 'Zapisałem Twoje nowe typy',
                'body'       => json_encode([$nowyTyp]),
                'send_after' => $sendAfter,
            ]);
        }

    }

    /**
     * Wysyła wszystkie dojrzałe emaile z kolejki. Wywołuje cron.
     * Zwraca liczbę wysłanych wiadomości.
     */
    public function processQueue(): int
    {
        $userModel = model(UserModel::class);
        $pending = $this->db->table('email_queue')
            ->where('sent', 0)
            ->where('send_after <=', date('Y-m-d H:i:s'))
            ->get()->getResultArray();

        $sent = 0;
        foreach ($pending as $item) {
            $user = $userModel->select('email, nick')
                              ->where('uniID', $item['uniID'])
                              ->first();

            if (!$user) {
                continue;
            }
            $typy = json_decode($item['body'], true) ?: [];
                $pozycje = '';
                foreach ($typy as $typ) {
                    $golden = ($typ['zlotaPilka'] ?? false) ? ' ⚽ Złota Piłka!' : '';
                    $pozycje .= "<li>{$typ['data']} | {$typ['mecz']} | Twój typ: {$typ['typH']}:{$typ['typA']}{$golden}</li>";
                }

            $html = "<p>Hej <strong>{$user['nick']}</strong>!</p>"
                  . "<p>Twoje typy zostały zapisane:<br><ul>{$pozycje}</ul></p>"
                  . "<p>May the odds be always in your favour!</p><i>Wit</i><br><br><p>Otrzymujesz tę wiadomość, ponieważ wspólnie gramy w typera jakiwynik.com. Jeśli nie chcesz otrzymywać tych wiadomości - napisz do mnie lub zmień to w swoich preferencjach na stronie. ";

            if ($this->postmark->sendEmail('Potwierdzenie <potwierdzenie@jakiwynik.com>', $user['email'], 'wit@jakiwynik.com', $item['subject'], $html)) {
                $this->db->table('email_queue')->where('id', $item['id'])->update(['sent' => 1]);
                $sent++;
            }
        }

        return $sent;
    }
    
    public function sendReminders(): int
{
    $config    = get_active_tournament_config();
    $turniejID = (int)$config['activeTournamentId'];

    $matches = (new TerminarzModel())->getMeczeNaReminder($turniejID);
    if (empty($matches)) {
        return 0;
    }

    $userModel = model(UserModel::class);
    $typyModel = model(TypyModel::class);

    $users = $userModel->select('uniID, email, nick')
                       ->where('PlaysTheActiveTournament', 1)
                       ->where('notify_reminder', 1)
                       ->findAll();

    $sent = 0;
    foreach ($users as $user) {
        $unbet = [];
        foreach ($matches as $match) {
            if (!$typyModel->getTypyByMeczIdAndUserId($match['Id'], $user['uniID'])) {
                $unbet[] = $match;
            }
        }
        if (empty($unbet)) {
            continue;
        }

        $pozycje = '';
        foreach ($unbet as $match) {
            $dt = new \DateTime($match['Date'] . ' ' . $match['Time'], new \DateTimeZone('UTC'));
            $dt->setTimezone(new \DateTimeZone('Europe/Warsaw'));
            $lokalnyczas = $dt->format('d.m.Y H:i');

            $jsonPath = WRITEPATH . "mecze/{$turniejID}/{$match['ApiID']}.json";
            $details  = file_exists($jsonPath) ? (json_decode(file_get_contents($jsonPath), true) ?? []) : [];
            $homeName = $details['home_team']['plName'] ?? $details['home_team']['name'] ?? $match['HomeName'];
            $awayName = $details['away_team']['plName'] ?? $details['away_team']['name'] ?? $match['AwayName'];

            $pozycje .= "<li>{$lokalnyczas} -- {$homeName} vs {$awayName}</li>";
        }

        $url  = base_url('typowanie');
        $html = "<p>Hej <strong>{$user['nick']}</strong>!</p>"
              . "<p>Nie obstawiłeś jeszcze wyników następujących meczów:</p>"
              . "<ul>{$pozycje}</ul>"
              . "<p><a href=\"{$url}\" style=\"display:inline-block;padding:10px 20px;background:#198754;color:#fff;text-decoration:none;border-radius:5px;\">➡ Obstaw teraz</a></p>"

              . "<p>May the odds be always in your favour!</p><i>Wit</i><br><br><p><small>Otrzymujesz tę wiadomość, ponieważ wspólnie gramy w typera jakiwynik.com. Jeśli nie chcesz otrzymywać tych wiadomości - napisz do mnie lub zmień to w swoich preferencjach na <a href=\"" . base_url('profil') . "\">swoim profilu</a>.</small></p>";

        if ($this->postmark->sendEmail('Przypomnienie <przypomnienie@jakiwynik.com>', $user['email'], 'wit@jakiwynik.com', 'Nie zapomnij obstawić! -- JakiWynik.com', $html)) {
            $sent++;
        }
    }
    return $sent;
}

public function sendCampaignTest(string $templateFile, string $subject, string $toEmail): bool
{
    $html = @file_get_contents(FCPATH . 'maile/' . basename($templateFile));
    if ($html === false) {
        return false;
    }
    return $this->postmark->sendEmail(
        'ogloszenia@jakiwynik.com', $toEmail, '', '[TEST] ' . $subject, $html
    );
}

public function sendCampaign(string $templateFile, string $subject, string $targetGroup): int
{
    $html = @file_get_contents(FCPATH . 'maile/' . basename($templateFile));
    if ($html === false) {
        return 0;
    }
    $recipients = $this->getCampaignRecipients($targetGroup);
    $sent = 0;
    foreach ($recipients as $user) {
        $personalHtml = str_replace('{nick}', esc($user['nick'] ?? ''), $html);
        if ($this->postmark->sendEmail(
            'Typer MŚ <ogloszenia@jakiwynik.com>', $user['email'], 'wit@jakiwynik.com', $subject, $personalHtml, '', 'broadcast'
        )) {
            $sent++;
        }
    }
    \Config\Database::connect()->table('email_campaigns')->insert([
        'template_file'    => $templateFile,
        'subject'          => $subject,
        'target_group'     => $targetGroup,
        'sent_at'          => date('Y-m-d H:i:s'),
        'recipients_count' => $sent,
    ]);
    return $sent;
}

public function sendDigest(array $users, int $turniejID, string $adminKomentarz,string $adminKomentarz2,string $adminKomentarz3, string $subjectTemplate = 'Dzień dobry, {nick}! Co w trawce piszczy?', array  $pytaniaWczorajIds = [],
    array  $pytaniaDzisiajIds = [] ): int
{
    $digestService = new \App\Services\DigestService();
    $url  = base_url('typowanie');
    $sent = 0;

    foreach ($users as $user) {
        $data    = $digestService->buildForUser($user, $turniejID, $adminKomentarz, $adminKomentarz2,$adminKomentarz3,$pytaniaWczorajIds, $pytaniaDzisiajIds);
        $html    = $this->buildDigestHtml($data, $url);
        $subject = str_replace('{nick}', $user['nick'] ?? '', $subjectTemplate);

        if ($this->postmark->sendEmail(
            'Poranny niezbędnik typera <digest@jakiwynik.com>',
            $user['email'],
            'wit@jakiwynik.com',
            $subject,
            $html,
            '',
            'broadcast'
        )) {
            $sent++;
        }
    }

    // aktualizacja po błędzie
    $existing = $this->db->table('email_campaigns')
        ->where('template_file', 'digest')
        ->where('target_group', 'active')
        ->get()->getRow();

    if ($existing) {
        $this->db->table('email_campaigns')
            ->where('template_file', 'digest')
            ->where('target_group', 'active')
            ->update([
                'subject'          => $subjectTemplate,
                'sent_at'          => date('Y-m-d H:i:s'),
                'recipients_count' => $sent,
            ]);
    } else {
        $this->db->table('email_campaigns')->insert([
            'template_file'    => 'digest',
            'subject'          => $subjectTemplate,
            'target_group'     => 'active',
            'sent_at'          => date('Y-m-d H:i:s'),
            'recipients_count' => $sent,
        ]);
    }





    return $sent;
}


private function buildDigestHtml(array $data, string $url): string
{
    $nick = esc($data['nick']);

    // ── Komentarz otwarcia ──
    $komentarzHtml = '';
    if (!empty($data['adminKomentarz'])) {
        $komentarzHtml = '<p style="background:#f0f4ff;border-left:3px solid #4f46e5;padding:10px 14px;'
                       . 'border-radius:4px;margin-bottom:16px;">' . esc($data['adminKomentarz']) . '</p>';
    }

    // ── Blok punktów (WAŻNE -- na górze) ──
    $pktHtml = '';
    if (isset($data['wszystkiePkt'])) {
        $pktHtml = '<div style="background:#f0f4ff;border-radius:8px;padding:16px 20px;margin:16px 0;display:flex;gap:24px;flex-wrap:wrap;">'
            . '<div style="text-align:center;flex:1;">'
            .   '<div style="font-size:11px;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Wszystkie punkty</div>'
            .   '<div style="font-size:28px;font-weight:700;color:#4f46e5;">' . (int)$data['wszystkiePkt'] . '</div>'
            . '</div>'
            . '<div style="text-align:center;flex:1;">'
            .   '<div style="font-size:11px;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Wczoraj zdobyte</div>'
            .   '<div style="font-size:28px;font-weight:700;color:#059669;">' . (int)$data['wczorajPkt'] . '</div>'
            . '</div>'
            . '<div style="text-align:center;flex:1;">'
            .   '<div style="font-size:11px;text-transform:uppercase;color:#6b7280;letter-spacing:.05em;">Pozycja w rankingu</div>'
            .   '<div style="font-size:28px;font-weight:700;color:#d97706;">' . (int)$data['rankingPozycja'] . '. miejsce</div>'
            . '</div>'
            . '</div>';
    }

    // ── Wczorajsze mecze ──
    $wczorajHtml = '';
    if (!empty($data['wczorajMecze'])) {
        $rows = '';
        foreach ($data['wczorajMecze'] as $m) {
            $wynik  = (int)$m['homeScore'] . ':' . (int)$m['awayScore'];
            $typTxt = $m['userHome'] !== null
                ? (int)$m['userHome'] . ':' . (int)$m['userAway'] . ($m['isGolden'] ? ' ⚽' : '')
                : '<em style="color:#6b7280;">brak</em>';
            $pktTxt = $m['pkt'] > 0 ? '+' . $m['pkt'] . ' pkt' : '0 pkt';
            $rows  .= '<tr style="border-bottom:1px solid #f3f4f6;">'
                    . '<td style="padding:6px 8px;">' . esc($m['homeName']) . ' – ' . esc($m['awayName']) . '</td>'
                    . '<td style="padding:6px 8px;text-align:center;font-weight:700;">' . $wynik . '</td>'
                    . '<td style="padding:6px 8px;text-align:center;">' . $typTxt . '</td>'
                    . '<td style="padding:6px 8px;text-align:center;font-weight:700;color:#4f46e5;">' . $pktTxt . '</td>'
                    . '</tr>';
        }
        $wczorajHtml = '<h3 style="font-size:14px;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin:20px 0 8px;">Wyniki z ostatnich 24h</h3>'
                     . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                     . '<thead><tr style="background:#f9fafb;font-size:11px;text-transform:uppercase;color:#9ca3af;">'
                     . '<th style="padding:6px 8px;text-align:left;">Mecz</th>'
                     . '<th style="padding:6px 8px;text-align:center;">Wynik</th>'
                     . '<th style="padding:6px 8px;text-align:center;">Twój typ</th>'
                     . '<th style="padding:6px 8px;text-align:center;">Pkt</th>'
                     . '</tr></thead><tbody>' . $rows . '</tbody></table>';
    }

    // ── Wczorajsze pytanie(a) ──
    $wczorajPytaniaHtml = '';
    if (!empty($data['wczorajPytania'])) {
        $komentarzPytanieHtml = '';
        if (!empty($data['adminKomentarz2'])) {
            $komentarzPytanieHtml = '<p style="background:#f0fff4;border-left:3px solid #22c55e;padding:10px 14px;'
                                  . 'border-radius:4px;margin:16px 0 8px;">' . esc($data['adminKomentarz2']) . '</p>';
        }
        $wczorajPytaniaHtml .= $komentarzPytanieHtml;
        $wczorajPytaniaHtml .= '<h3 style="font-size:14px;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin:20px 0 8px;">Wyniki za pytania</h3>';

        foreach ($data['wczorajPytania'] as $p) {
            $prawidlowaHtml = $p['odpowiedz']
                ? '<span style="color:#059669;font-weight:700;">✓ ' . esc($p['odpowiedz']) . '</span>'
                : '<em style="color:#9ca3af;">nie podano</em>';
            $userOdpHtml = $p['userOdp']
                ? esc($p['userOdp'])
                : '<em style="color:#ef4444;">brak odpowiedzi</em>';
            $pktColor = $p['pkt'] > 0 ? '#059669' : '#6b7280';

            $wczorajPytaniaHtml .= '<div style="border:1px solid #e5e7eb;border-radius:6px;padding:12px 14px;margin-bottom:10px;">'
                . '<p style="margin:0 0 8px;font-size:14px;font-weight:500;">' . esc($p['tresc']) . '</p>'
                . '<div style="display:flex;gap:16px;font-size:13px;flex-wrap:wrap;">'
                . '<span><span style="color:#6b7280;">Prawidłowa:</span> ' . $prawidlowaHtml . '</span>'
                . '<span><span style="color:#6b7280;">Twoja:</span> ' . $userOdpHtml . '</span>'
                . '<span style="font-weight:700;color:' . $pktColor . ';">' . ($p['pkt'] > 0 ? '+' . $p['pkt'] : '0') . ' pkt</span>'
                . '</div>'
                . '</div>';
        }
    }

    // ── Nadchodzące mecze ──
    $dzisiajHtml = '';
    if (!empty($data['dzisiajMecze'])) {
        $rows = '';
        foreach ($data['dzisiajMecze'] as $m) {
            $typTxt = $m['hasTyp']
                ? '<strong>' . (int)$m['userHome'] . ':' . (int)$m['userAway'] . '</strong>' . ($m['isGolden'] ? ' ⚽' : '')
                : '<a href="' . $url . '" style="color:#ef4444;font-weight:700;">Obstaw!</a>';
            $rows  .= '<tr style="border-bottom:1px solid #f3f4f6;">'
                    . '<td style="padding:6px 8px;">' . esc($m['homeName']) . ' – ' . esc($m['awayName']) . '</td>'
                    . '<td style="padding:6px 8px;text-align:center;">' . esc($m['naszCzas']) . '</td>'
                    . '<td style="padding:6px 8px;text-align:center;">' . $typTxt . '</td>'
                    . '</tr>';
        }
        $dzisiajHtml = '<h3 style="font-size:14px;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin:20px 0 8px;">Nadchodzące mecze (24h)</h3>'
                     . '<table style="width:100%;border-collapse:collapse;font-size:14px;">'
                     . '<thead><tr style="background:#f9fafb;font-size:11px;text-transform:uppercase;color:#9ca3af;">'
                     . '<th style="padding:6px 8px;text-align:left;">Mecz</th>'
                     . '<th style="padding:6px 8px;text-align:center;">Godzina</th>'
                     . '<th style="padding:6px 8px;text-align:center;">Twój typ</th>'
                     . '</tr></thead><tbody>' . $rows . '</tbody></table>';
    }

    // ── Dzisiejsze pytanie(a) ──
    $dzisiajPytaniaHtml = '';
    if (!empty($data['dzisiajPytania'])) {
        $dzisiajPytaniaHtml = '<h3 style="font-size:14px;text-transform:uppercase;letter-spacing:.05em;color:#6b7280;margin:20px 0 8px;">Pytanie dnia</h3>';
        foreach ($data['dzisiajPytania'] as $p) {
            $opisHtml   = !empty($p['opis'])   ? '<p style="font-size:13px;color:#6b7280;margin:6px 0 0;">' . esc($p['opis']) . '</p>'   : '';
            $zrodloHtml = !empty($p['zrodlo']) ? '<p style="font-size:12px;color:#9ca3af;margin:4px 0 0;">Skąd będę wiedział: ' . esc($p['zrodlo']) . '</p>' : '';
            $userOdpHtml = $p['hasOdp']
                ? '<p style="font-size:13px;margin:8px 0 0;">Twoja odpowiedź: <strong>' . esc($p['userOdp']) . '</strong> <a href="' . $url . '" style="color:#4f46e5;font-size:12px;">zmień</a></p>'
                : '<p style="text-align:right;font-size:12px;text-transform:uppercase;margin:8px 0 0;"><a href="' . $url . '" style="color:#ef4444;font-weight:700;">Wpisz odpowiedź →</a></p>';

            $dzisiajPytaniaHtml .= '<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:4px;padding:10px 14px;margin-bottom:10px;">'
                . '<p style="margin:0;font-weight:500;">' . esc($p['tresc']) . '</p>'
                . $opisHtml
                . $zrodloHtml
                . $userOdpHtml
                . '</div>';
        }
    }

    // ── Komentarz zamknięcia ──
    $komentarzClosingHtml = '';
    if (!empty($data['adminKomentarz3'])) {
        $komentarzClosingHtml = '<p style="background:#f0fff4;border-left:3px solid #22c55e;padding:10px 14px;'
                              . 'border-radius:4px;margin-top:16px;">' . esc($data['adminKomentarz3']) . '</p>';
    }

    return '<!DOCTYPE html><html><head><meta charset="utf-8"></head>'
         . '<body style="font-family:sans-serif;background:#f9fafb;margin:0;padding:0;">'
         . '<div style="max-width:600px;margin:24px auto;background:#fff;border-radius:8px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.07);">'
         . '<p style="margin:0 0 16px;font-size:16px;">Hej <strong>' . $nick . '</strong>! 👋</p>'
         . $komentarzHtml
         . $pktHtml               // ← punkty wysoko
         . $wczorajHtml
         . $wczorajPytaniaHtml    // ← wyniki pytań pod meczami wczoraj
         . $dzisiajHtml
         . $dzisiajPytaniaHtml    // ← aktywne pytania pod meczami dziś
         . $komentarzClosingHtml
         . '<hr style="border:none;border-top:1px solid #f3f4f6;margin:24px 0;">'
         . '<p style="font-size:14px;color:#9ca3af;margin:0;">may the odds be always in your <em>flavour</em></p>'
         . '</div></body></html>';
}
    

private function getCampaignRecipients(string $targetGroup): array
{
    $userModel = model(UserModel::class);
    if ($targetGroup === 'active') {
        return $userModel->select('email, nick')
                         ->where('PlaysTheActiveTournament', 1)
                         ->findAll();
    }
    if ($targetGroup === 'all') {
        return $userModel->select('email, nick')->findAll();
    }
    if (str_starts_with($targetGroup, 'tournament_')) {
        $tid = (int)substr($targetGroup, 11);
        return $userModel->select('uzytkownicy.email, uzytkownicy.nick')
                         ->join('ktowcogra', '<uzytkownicy.id> = ktowcogra.userID')
                         ->where('ktowcogra.turniejID', $tid)
                         ->findAll();
    }
    return [];
}

public function sendPasswordChanged(string $email, string $nick): bool
{
    $dt  = (new \DateTime('now', new \DateTimeZone('Europe/Warsaw')))->format('d.m.Y H:i');
    $url = base_url('auth');

    $html = '<!DOCTYPE html><html><head><meta charset="utf-8"></head>'
          . '<body style="font-family:sans-serif;background:#f9fafb;margin:0;padding:0;">'
          . '<div style="max-width:560px;margin:24px auto;background:#fff;border-radius:8px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.07);">'
          . '<p style="margin:0 0 16px;font-size:16px;">Hej <strong>' . esc($nick) . '</strong>!</p>'
          . '<p>Twoje hasło zostało zmienione '
          . '<span style="background:#dcfce7;color:#166534;font-size:13px;padding:2px 8px;border-radius:12px;font-weight:600;">'
          . $dt . '</span></p>'
          . '<p>Aaaaaby zalogować się na swoje konto (a Twoje konto to przecież: '
          . '<strong>' . esc($nick) . '</strong>) użyj hasła, które właśnie ustawiłeś.</p>'
          . '<p style="margin:16px 0;">'
          . '<a href="' . $url . '" style="background:#4f46e5;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:600;">Zaloguj się →</a>'
          . '</p>'
          . '<hr style="border:none;border-top:1px solid #f3f4f6;margin:20px 0;">'
          . '<p style="font-size:13px;color:#6b7280;">Nie zmieniałeś hasła? Proszę odpisz na tego maila i daj mi znać, że dzieje się coś, co dziać się nie powinno.</p>'
          . '<p style="font-size:13px;color:#9ca3af;margin:8px 0 0;">May the odds be always in your <em>flavour</em></p>'
          . '</div></body></html>';

    return $this->postmark->sendEmail(
        'gospodarz@jakiwynik.com',
        $email,
        'wit@jakiwynik.com',
        'Twoje hasło w JakiWynik zostało zmienione',
        $html,
        '',
        'outbound'
    );
}



}


