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
    $najlepszyTyper = $digestService->getNajlepszyTyper($turniejID, $pytaniaWczorajIds);  // ← nowe, raz przed pętlą
    $najwiekszySkokGracza = $digestService->getNajwiekszySkokPozycji($turniejID); // i jeszcze nowsze

    foreach ($users as $user) {
        $data    = $digestService->buildForUser($user, $turniejID, $adminKomentarz, $adminKomentarz2,$adminKomentarz3,$pytaniaWczorajIds, $pytaniaDzisiajIds,$najlepszyTyper, $najwiekszySkokGracza);
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
    return view('emails/digest', array_merge($data, ['url' => $url]));
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


