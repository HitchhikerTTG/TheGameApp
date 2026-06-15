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
    $f    = 'font-family:Arial,Helvetica,sans-serif;';

    // ── helper: nagłówek sekcji ──────────────────────────────────────
    $h = fn(string $label) =>
        '<tr><td style="' . $f . 'font-size:20px;font-weight:700;text-transform:uppercase;'
        . 'letter-spacing:.06em;color:#9ca3af;padding:20px 0 6px 0;mso-line-height-rule:exactly;">'
        . $label . '</td></tr>';

    // ── Komentarz admina (otwarcie) ──────────────────────────────────
    $komentarzHtml = '';
    if (!empty($data['adminKomentarz'])) {
        $komentarzHtml =
            '<tr><td style="' . $f . 'font-size:16px;color:#1e1b4b;padding:10px 14px;'
            . 'border-left:3px solid #4f46e5;background:#f0f4ff;">'
            . esc($data['adminKomentarz']) . '</td></tr>'
            . '<tr><td style="padding:8px 0;font-size:0;">&nbsp;</td></tr>';
    }

    // ── Blok punktów (3 kolumny — table zamiast flex) ────────────────
    $pktHtml = '';
    if (isset($data['wszystkiePkt'])) {
        $pktHtml =
            $h('Twoje statystyki') .
            '<tr><td style="padding:4px 0 16px;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" border="0">'
            . '<tr>'

            . '<td width="32%" align="center" valign="top" bgcolor="#eef2ff" style="padding:14px 6px;">'
            . '<p style="' . $f . 'font-size:14px;font-weight:700;text-transform:uppercase;color:#6b7280;margin:0 0 6px;">Twoje punkty</p>'
            . '<p style="' . $f . 'font-size:26px;font-weight:700;color:#4f46e5;margin:0;">' . (int)$data['wszystkiePkt'] . '</p>'
            . '</td>'

            . '<td width="2%" bgcolor="#ffffff" style="font-size:0;">&nbsp;</td>'

            . '<td width="32%" align="center" valign="top" bgcolor="#ecfdf5" style="padding:14px 6px;">'
            . '<p style="' . $f . 'font-size:14px;font-weight:700;text-transform:uppercase;color:#6b7280;margin:0 0 6px;">Wczoraj zdobyte</p>'
            . '<p style="' . $f . 'font-size:26px;font-weight:700;color:#059669;margin:0;">' . (int)$data['wczorajPkt'] . '</p>'
            . '</td>'

            . '<td width="2%" bgcolor="#ffffff" style="font-size:0;">&nbsp;</td>'

            . '<td width="32%" align="center" valign="top" bgcolor="#fefce8" style="padding:14px 6px;">'
            . '<p style="' . $f . 'font-size:14px;font-weight:700;text-transform:uppercase;color:#6b7280;margin:0 0 6px;">Twoja pozycja</p>'
            . '<p style="' . $f . 'font-size:26px;font-weight:700;color:#d97706;margin:0;">' . (int)$data['rankingPozycja'] . '.</p>'
            . '</td>'

            . '</tr></table>'
            . '</td></tr>';
    }

    // ── Wczorajsze mecze ─────────────────────────────────────────────
    $wczorajHtml = '';
    if (!empty($data['wczorajMecze'])) {
        $rows = '';
        foreach ($data['wczorajMecze'] as $m) {
            $wynik  = (int)$m['homeScore'] . ':' . (int)$m['awayScore'];
            $typTxt = $m['userHome'] !== null
                ? (int)$m['userHome'] . ':' . (int)$m['userAway'] . ($m['isGolden'] ? ' &#x26BD;' : '')
                : '<em style="color:#9ca3af;">brak</em>';
            $pktTxt = $m['pkt'] > 0 ? '+' . $m['pkt'] . ' pkt' : '0 pkt';
            $pktCol = $m['pkt'] > 0 ? '#4f46e5' : '#9ca3af';

            $rows .=
                '<tr style="border-bottom:1px solid #f3f4f6;">'
                . '<td style="' . $f . 'font-size:15px;padding:7px 6px;color:#111827;">'
                . esc($m['homeName']) . '&nbsp;&ndash;&nbsp;' . esc($m['awayName']) . '</td>'
                . '<td align="center" style="' . $f . 'font-size:15px;font-weight:700;padding:7px 6px;color:#111827;white-space:nowrap;">'
                . $wynik . '</td>'
                . '<td align="center" style="' . $f . 'font-size:15px;padding:7px 6px;color:#374151;white-space:nowrap;">'
                . $typTxt . '</td>'
                . '<td align="center" style="' . $f . 'font-size:15px;font-weight:700;padding:7px 6px;color:' . $pktCol . ';white-space:nowrap;">'
                . $pktTxt . '</td>'
                . '</tr>';
        }

        $wczorajHtml =
            $h('Wyniki z ostatnich 24h') .
            '<tr><td style="padding:0 0 16px;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">'
            . '<tr bgcolor="#f9fafb">'
            . '<th align="left"   style="' . $f . 'font-size:15px;font-weight:700;text-transform:uppercase;color:#9ca3af;padding:5px 6px;">Mecz</th>'
            . '<th align="center" style="' . $f . 'font-size:15px;font-weight:700;text-transform:uppercase;color:#9ca3af;padding:5px 6px;">Wynik</th>'
            . '<th align="center" style="' . $f . 'font-size:15px;font-weight:700;text-transform:uppercase;color:#9ca3af;padding:5px 6px;">Tw&oacute;j typ</th>'
            . '<th align="center" style="' . $f . 'font-size:15px;font-weight:700;text-transform:uppercase;color:#9ca3af;padding:5px 6px;">Pkt</th>'
            . '</tr>'
            . $rows
            . '</table>'
            . '</td></tr>';
    }

    // ── Wczorajsze pytania (wyniki) ──────────────────────────────────
    $wczorajPytaniaHtml = '';
    if (!empty($data['wczorajPytania'])) {
        if (!empty($data['adminKomentarz2'])) {
            $wczorajPytaniaHtml .=
                '<tr><td style="' . $f . 'font-size:16px;color:#14532d;padding:10px 14px;'
                . 'border-left:3px solid #22c55e;background:#f0fff4;">'
                . esc($data['adminKomentarz2']) . '</td></tr>'
                . '<tr><td style="padding:6px 0;font-size:0;">&nbsp;</td></tr>';
        }

        $wczorajPytaniaHtml .= $h('Wyniki za pytania');

        foreach ($data['wczorajPytania'] as $p) {
            $prawidlowaHtml = $p['odpowiedz']
                ? '<span style="color:#059669;font-weight:700;">&#x2713;&nbsp;' . esc($p['odpowiedz']) . '</span>'
                : '<em style="color:#9ca3af;">nie podano</em>';
            $userOdpHtml = $p['userOdp']
                ? esc($p['userOdp'])
                : '<span style="color:#ef4444;">brak odpowiedzi</span>';
            $pktColor = $p['pkt'] > 0 ? '#059669' : '#9ca3af';
            $pktLabel = $p['pkt'] > 0 ? '+' . $p['pkt'] . ' pkt' : '0 pkt';

            $wczorajPytaniaHtml .=
                '<tr><td style="padding:0 0 10px;">'
                . '<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f9fafb" style="border-collapse:collapse;">'
                . '<tr><td style="' . $f . 'font-size:16px;font-weight:500;color:#111827;padding:10px 12px 6px;">'
                . esc($p['tresc']) . '</td></tr>'
                . '<tr><td style="padding:0 12px 10px;">'
                . '<table cellpadding="0" cellspacing="0" border="0"><tr valign="top">'
                . '<td style="' . $f . 'font-size:15px;color:#6b7280;padding-right:16px;">Prawid&#322;owa:&nbsp;' . $prawidlowaHtml . '</td>'
                . '<td style="' . $f . 'font-size:15px;color:#6b7280;padding-right:16px;">Twoja:&nbsp;' . $userOdpHtml . '</td>'
                . '<td style="' . $f . 'font-size:15px;font-weight:700;color:' . $pktColor . ';">' . $pktLabel . '</td>'
                . '</tr></table>'
                . '</td></tr>'
                . '</table>'
                . '</td></tr>';
        }
        $wczorajPytaniaHtml .= '<tr><td style="padding:4px 0;font-size:0;">&nbsp;</td></tr>';
    }

    // ── Nadchodzące mecze ────────────────────────────────────────────
    $dzisiajHtml = '';
    if (!empty($data['dzisiajMecze'])) {
        $rows = '';
        foreach ($data['dzisiajMecze'] as $m) {
            $typTxt = $m['hasTyp']
                ? '<strong style="color:#111827;">' . (int)$m['userHome'] . ':' . (int)$m['userAway'] . '</strong>'
                  . ($m['isGolden'] ? '&nbsp;&#x26BD;' : '')
                : '<a href="' . $url . '" style="color:#ef4444;font-weight:700;text-decoration:none;">Obstaw!</a>';

            $rows .=
                '<tr style="border-bottom:1px solid #f3f4f6;">'
                . '<td style="' . $f . 'font-size:16px;padding:7px 6px;color:#111827;">'
                . esc($m['homeName']) . '&nbsp;&ndash;&nbsp;' . esc($m['awayName']) . '</td>'
                . '<td align="center" style="' . $f . 'font-size:15px;padding:7px 6px;color:#374151;white-space:nowrap;">'
                . esc($m['naszCzas']) . '</td>'
                . '<td align="center" style="' . $f . 'font-size:15px;padding:7px 6px;white-space:nowrap;">'
                . $typTxt . '</td>'
                . '</tr>';
        }

        $dzisiajHtml =
            $h('Nadchodz&#x105;ce mecze (24h)') .
            '<tr><td style="padding:0 0 16px;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">'
            . '<tr bgcolor="#f9fafb">'
            . '<th align="left"   style="' . $f . 'font-size:15px;font-weight:700;text-transform:uppercase;color:#9ca3af;padding:5px 6px;">Mecz</th>'
            . '<th align="center" style="' . $f . 'font-size:15px;font-weight:700;text-transform:uppercase;color:#9ca3af;padding:5px 6px;">Godzina</th>'
            . '<th align="center" style="' . $f . 'font-size:15px;font-weight:700;text-transform:uppercase;color:#9ca3af;padding:5px 6px;">Tw&oacute;j typ</th>'
            . '</tr>'
            . $rows
            . '</table>'
            . '</td></tr>';
    }

    // ── Dzisiejsze pytania (aktywne) ─────────────────────────────────
    $dzisiajPytaniaHtml = '';
    if (!empty($data['dzisiajPytania'])) {
        $dzisiajPytaniaHtml .= $h('Pytanie dnia');
        foreach ($data['dzisiajPytania'] as $p) {
            $opisHtml   = !empty($p['opis'])
                ? '<tr><td style="' . $f . 'font-size:15px;color:#6b7280;padding:4px 12px 0;">' . esc($p['opis']) . '</td></tr>'
                : '';
            $zrodloHtml = !empty($p['zrodlo'])
                ? '<tr><td style="' . $f . 'font-size:14px;color:#9ca3af;padding:2px 12px 0;">&#x1F4CA;&nbsp;' . esc($p['zrodlo']) . '</td></tr>'
                : '';
            $userOdpHtml = $p['hasOdp']
                ? '<tr><td style="' . $f . 'font-size:15px;color:#374151;padding:8px 12px 10px;">'
                  . 'Twoja odpowied&#x17A;: <strong>' . esc($p['userOdp']) . '</strong>&nbsp;&nbsp;'
                  . '<a href="' . $url . '" style="color:#4f46e5;font-size:15px;text-decoration:none;">zmie&#x144;</a>'
                  . '</td></tr>'
                : '<tr><td align="right" style="padding:8px 12px 10px;">'
                  . '<a href="' . $url . '" style="' . $f . 'font-size:15px;font-weight:700;color:#ef4444;text-decoration:none;">Wpisz odpowied&#x17A; &#x2192;</a>'
                  . '</td></tr>';

            $dzisiajPytaniaHtml .=
                '<tr><td style="padding:0 0 10px;">'
                . '<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#fffbeb" style="border-collapse:collapse;border:1px solid #fde68a;">'
                . '<tr><td style="' . $f . 'font-size:16px;font-weight:500;color:#111827;padding:10px 12px 4px;">'
                . esc($p['tresc']) . '</td></tr>'
                . $opisHtml
                . $zrodloHtml
                . $userOdpHtml
                . '</table>'
                . '</td></tr>';
        }
    }

    // ── Komentarz zamknięcia ─────────────────────────────────────────
    $komentarzClosingHtml = '';
    if (!empty($data['adminKomentarz3'])) {
        $komentarzClosingHtml =
            '<tr><td style="' . $f . 'font-size:16px;color:#14532d;padding:10px 14px;'
            . 'border-left:3px solid #22c55e;background:#f0fff4;">'
            . esc($data['adminKomentarz3']) . '</td></tr>'
            . '<tr><td style="padding:8px 0;font-size:0;">&nbsp;</td></tr>';
    }

    // ── Składamy cały email (table-based layout) ─────────────────────
    return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
         . '<html xmlns="http://www.w3.org/1999/xhtml"><head>'
         . '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
         . '<meta name="viewport" content="width=device-width,initial-scale=1">'
         . '</head>'
         . '<body style="margin:0;padding:0;background:#f3f4f6;">'

         . '<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#f3f4f6">'
         . '<tr><td align="center" style="padding:24px 8px;">'

         . '<table width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="border-collapse:collapse;">'
         . '<tr><td style="padding:28px 28px 0;">'

         . '<table width="100%" cellpadding="0" cellspacing="0" border="0">'

         . '<tr><td style="' . $f . 'font-size:16px;color:#111827;padding-bottom:16px;">'
         . 'Hej <strong>' . $nick . '</strong>! &#x1F44B;</td></tr>'

         . $komentarzHtml
         . $pktHtml
         . $wczorajHtml
         . $wczorajPytaniaHtml
         . $dzisiajHtml
         . $dzisiajPytaniaHtml
         . $komentarzClosingHtml

         . '<tr><td style="border-top:1px solid #e5e7eb;padding-top:20px;padding-bottom:8px;font-size:0;">&nbsp;</td></tr>'
         . '<tr><td style="' . $f . 'font-size:15px;color:#9ca3af;padding-bottom:6px;">'
         . 'may the odds be always in your <em>flavour</em></td></tr>'
         . '<tr><td style="' . $f . 'font-size:15px;color:#d1d5db;padding-bottom:28px;">'
         . 'Je&#x15B;li dobrze si&#x119; bawisz i doceniasz t&#x119; robot&#x119;, postaw kaw&#x119; &#x2615; &rarr; '
         . '<a href="https://buycoffee.to/wit" style="color:#d1d5db;text-decoration:none;">buycoffee.to/wit</a></td></tr>'

         . '</table>'
         . '</td></tr></table>'
         . '</td></tr></table>'
         . '</body></html>';
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


