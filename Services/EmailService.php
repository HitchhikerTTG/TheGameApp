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
 
    // ── Stałe stylistyczne ───────────────────────────────────────────
    $colorBg       = '#f0f0f3';
    $colorCard     = '#ffffff';
    $colorHeader   = '#0d0d0f';
    $colorAccent   = '#e8ff47';
    $colorText     = '#1a1a2e';
    $colorMuted    = '#555560';
    $colorLabel    = '#aaaabc';
    $colorGreen    = '#1a9e5c';
    $colorGold     = '#b38a00';
    $colorRed      = '#d93636';
    $colorBgGray   = '#f8f8f4';
    $colorBgGreen  = '#f0fff8';
    $colorBgYellow = '#fffbf0';
    $colorBorderY  = '#f0e68c';
    $colorBorder   = '#f0f0f3';
 
    $fsBody  = '18px';   // treść główna
    $fsLabel = '14px';   // nagłówki sekcji uppercase, nagłówki tabel
    $fsBig   = '30px';   // liczby statystyk
    $fsLogo  = '26px';   // logo w headerze
 
    $tdLabel = $f . 'font-size:' . $fsLabel . ';font-weight:700;letter-spacing:2px;'
             . 'text-transform:uppercase;color:' . $colorLabel . ';';
 
    $sectionLabel = fn(string $text) =>
        '<p style="margin:0 0 12px;' . $tdLabel . '">' . $text . '</p>';
 
    $thCell = fn(string $text, string $align = 'left') =>
        '<td align="' . $align . '" style="' . $f . 'font-size:' . $fsLabel . ';font-weight:700;'
        . 'letter-spacing:1px;text-transform:uppercase;color:' . $colorLabel . ';'
        . 'padding:12px ' . ($align === 'left' ? '10px' : '8px') . ';white-space:nowrap;">'
        . $text . '</td>';
 
    // ── HEADER ──────────────────────────────────────────────────────
    $headerHtml =
        '<tr>'
        . '<td bgcolor="' . $colorHeader . '" style="padding:28px 32px 24px;border-radius:12px 12px 0 0;">'
        . '<table width="100%" cellpadding="0" cellspacing="0" border="0">'
        . '<tr>'
        . '<td>'
        . '<span style="' . $f . 'font-size:' . $fsLogo . ';font-weight:900;letter-spacing:2px;'
        .   'color:#ffffff;text-transform:uppercase;">TYPER</span>'
        . '<span style="' . $f . 'font-size:' . $fsLogo . ';font-weight:900;color:' . $colorAccent . ';">.</span>'
        . '<span style="' . $f . 'font-size:' . $fsLogo . ';font-weight:900;letter-spacing:2px;'
        .   'color:#ffffff;text-transform:uppercase;">MŚ 2026</span>'
        . '</td>'
        . '<td align="right" valign="middle">'
        . '<span style="' . $f . 'font-size:' . $fsLabel . ';font-weight:700;letter-spacing:2px;'
        .   'text-transform:uppercase;color:#555560;">Digest &middot; ' . date('d.m.Y') . '</span>'
        . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td colspan="2" style="padding-top:16px;">'
        . '<p style="margin:0;' . $f . 'font-size:' . $fsBody . ';color:#a0a0b0;line-height:1.5;">'
        . 'Hej <strong style="color:#ffffff;">' . $nick . '</strong>! &#x1F44B;&nbsp;'
        . 'Oto Tw&oacute;j codzienny przegląd &mdash; wyniki, punkty i to, co czeka dzi&#x15B;.'
        . '</p>'
        . '</td>'
        . '</tr>'
        . '</table>'
        . '</td>'
        . '</tr>';
 
    // ── helper: komentarz z lewą kreską ─────────────────────────────
    $komentarzBlock = function(string $text, string $borderColor, string $bgColor) use ($f, $fsBody, $colorText): string {
        return
            '<table width="100%" cellpadding="0" cellspacing="0" border="0">'
            . '<tr>'
            . '<td width="3" bgcolor="' . $borderColor . '" style="font-size:0;">&nbsp;</td>'
            . '<td style="padding:10px 14px;background:' . $bgColor . ';'
            .   $f . 'font-size:' . $fsBody . ';color:' . $colorText . ';line-height:1.6;">'
            . $text
            . '</td>'
            . '</tr>'
            . '</table>';
    };
 
    // ── KOMENTARZ ADMINA (otwarcie) ──────────────────────────────────
    $komentarzHtml = '';
    if (!empty($data['adminKomentarz'])) {
        $komentarzHtml =
            '<tr><td bgcolor="' . $colorCard . '" style="padding:0 32px;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" border="0">'
            . '<tr><td style="padding:24px 0 0;">'
            . $komentarzBlock(esc($data['adminKomentarz']), $colorAccent, $colorBgGray)
            . '</td></tr>'
            . '</table>'
            . '</td></tr>';
    }
 
    // ── STATYSTYKI ───────────────────────────────────────────────────
    $pktHtml = '';
    if (isset($data['wszystkiePkt'])) {
        $kafelek = fn(string $bg, string $label, string $value, string $valueColor) =>
            '<td width="32%" align="center" bgcolor="' . $bg . '" style="padding:20px 8px;border-radius:8px;">'
            . '<p style="margin:0 0 4px;' . $f . 'font-size:' . $fsLabel . ';font-weight:700;'
            .   'letter-spacing:1px;text-transform:uppercase;color:#aaaabc;">' . $label . '</p>'
            . '<p style="margin:0;' . $f . 'font-size:30px;font-weight:900;color:' . $valueColor . ';">' . $value . '</p>'
            . '</td>';
 
        $pktHtml =
            '<tr><td bgcolor="' . $colorCard . '" style="padding:24px 32px 0;">'
            . $sectionLabel('Twoje statystyki')
            . '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>'
            . $kafelek($colorBgGray,   'Punkty og&oacute;&#322;em', (int)$data['wszystkiePkt'],    $colorText)
            . '<td width="4%" style="font-size:0;">&nbsp;</td>'
            . $kafelek($colorBgGreen,  'Wczoraj',                   '+' . (int)$data['wczorajPkt'], $colorGreen)
            . '<td width="4%" style="font-size:0;">&nbsp;</td>'
            . $kafelek($colorBgYellow, 'Miejsce',                   (int)$data['rankingPozycja'] . '.', $colorGold)
            . '</tr></table>'
            . '</td></tr>';
    }
 
    // ── WYNIKI Z OSTATNICH 24H ───────────────────────────────────────
    $wczorajHtml = '';
    if (!empty($data['wczorajMecze'])) {
        $rows = '';
        foreach ($data['wczorajMecze'] as $m) {
            $wynik  = (int)$m['homeScore'] . ':' . (int)$m['awayScore'];
            $typTxt = $m['userHome'] !== null
                ? (int)$m['userHome'] . ':' . (int)$m['userAway'] . ($m['isGolden'] ? ' &#x26BD;' : '')
                : '<em style="color:' . $colorLabel . ';">brak</em>';
            $pkt    = (int)$m['pkt'];
            $pktTxt = $pkt > 0 ? '+' . $pkt : '0';
            $pktCol = $pkt > 0 ? $colorGreen : $colorLabel;
 
            $tdBody = $f . 'font-size:' . $fsBody . ';color:' . $colorText . ';padding:12px 10px;';
 
            $rows .=
                '<tr style="border-bottom:1px solid ' . $colorBorder . ';">'
                . '<td style="' . $tdBody . '">'
                .   esc($m['homeName']) . ' &ndash; ' . esc($m['awayName'])
                . '</td>'
                . '<td align="center" style="' . $f . 'font-size:' . $fsBody . ';font-weight:700;'
                .   'color:' . $colorText . ';padding:12px 8px;white-space:nowrap;">' . $wynik . '</td>'
                . '<td align="center" style="' . $f . 'font-size:' . $fsBody . ';'
                .   'color:' . $colorMuted . ';padding:12px 8px;white-space:nowrap;">' . $typTxt . '</td>'
                . '<td align="center" style="' . $f . 'font-size:' . $fsBody . ';font-weight:700;'
                .   'color:' . $pktCol . ';padding:12px 8px;white-space:nowrap;">' . $pktTxt . '</td>'
                . '</tr>';
        }
 
        $wczorajHtml =
            '<tr><td bgcolor="' . $colorCard . '" style="padding:28px 32px 0;">'
            . $sectionLabel('Wyniki z ostatnich 24h')
            . '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">'
            . '<tr bgcolor="' . $colorBgGray . '">'
            . $thCell('Mecz')
            . $thCell('Wynik',    'center')
            . $thCell('Tw&oacute;j typ', 'center')
            . $thCell('Pkt',      'center')
            . '</tr>'
            . $rows
            . '</table>'
            . '</td></tr>';
    }
 
    // ── KOMENTARZ 2 + WYNIKI PYTAŃ ──────────────────────────────────
    $wczorajPytaniaHtml = '';
    if (!empty($data['wczorajPytania'])) {
        $wczorajPytaniaHtml = '<tr><td bgcolor="' . $colorCard . '" style="padding:28px 32px 0;">';
 
        if (!empty($data['adminKomentarz2'])) {
            $wczorajPytaniaHtml .=
                $komentarzBlock(esc($data['adminKomentarz2']), $colorGreen, $colorBgGreen)
                . '<table width="100%" cellpadding="0" cellspacing="0" border="0">'
                . '<tr><td style="padding:12px 0 0;font-size:0;">&nbsp;</td></tr>'
                . '</table>';
        }
 
        $wczorajPytaniaHtml .= $sectionLabel('Wyniki za pytania');
 
        foreach ($data['wczorajPytania'] as $p) {
            $prawidlowaHtml = $p['odpowiedz']
                ? '<strong style="color:' . $colorGreen . ';">&#x2713;&nbsp;' . esc($p['odpowiedz']) . '</strong>'
                : '<em style="color:' . $colorLabel . ';">nie podano</em>';
            $userOdpHtml = $p['userOdp']
                ? '<strong style="color:' . $colorText . ';">' . esc($p['userOdp']) . '</strong>'
                : '<span style="color:' . $colorRed . ';">brak odpowiedzi</span>';
            $pktColor = (int)$p['pkt'] > 0 ? $colorGreen : $colorLabel;
            $pktLabel = (int)$p['pkt'] > 0 ? '+' . (int)$p['pkt'] . ' pkt' : '0 pkt';
 
            $wczorajPytaniaHtml .=
                '<table width="100%" cellpadding="0" cellspacing="0" border="0"'
                . ' style="border-collapse:collapse;margin-bottom:10px;'
                .   'border:1px solid ' . $colorBorder . ';border-radius:8px;">'
                . '<tr><td style="' . $f . 'font-size:' . $fsBody . ';font-weight:600;'
                .   'color:' . $colorText . ';padding:12px 14px 6px;line-height:1.4;">'
                . esc($p['tresc'])
                . '</td></tr>'
                . '<tr><td style="padding:0 14px 12px;">'
                . '<table cellpadding="0" cellspacing="0" border="0"><tr>'
                . '<td style="' . $f . 'font-size:' . $fsLabel . ';color:' . $colorLabel . ';padding-right:20px;">'
                .   'Prawid&#322;owa:&nbsp;' . $prawidlowaHtml . '</td>'
                . '<td style="' . $f . 'font-size:' . $fsLabel . ';color:' . $colorLabel . ';padding-right:20px;">'
                .   'Twoja:&nbsp;' . $userOdpHtml . '</td>'
                . '<td style="' . $f . 'font-size:' . $fsLabel . ';font-weight:700;color:' . $pktColor . ';">'
                .   $pktLabel . '</td>'
                . '</tr></table>'
                . '</td></tr>'
                . '</table>';
        }
 
        $wczorajPytaniaHtml .= '</td></tr>';
    }
 
    // ── NADCHODZĄCE MECZE ────────────────────────────────────────────
    $dzisiajHtml = '';
    if (!empty($data['dzisiajMecze'])) {
        $rows = '';
        foreach ($data['dzisiajMecze'] as $m) {
            $typTxt = $m['hasTyp']
                ? '<strong style="color:' . $colorText . ';">'
                .   (int)$m['userHome'] . ':' . (int)$m['userAway']
                .   ($m['isGolden'] ? '&nbsp;&#x26BD;' : '')
                . '</strong>'
                : '<a href="' . $url . '" style="' . $f . 'font-size:13px;font-weight:700;'
                .   'color:#ffffff;text-decoration:none;background:' . $colorRed . ';'
                .   'padding:4px 12px;border-radius:4px;display:inline-block;">Obstaw!</a>';
 
            $rows .=
                '<tr style="border-bottom:1px solid ' . $colorBorder . ';">'
                . '<td style="' . $f . 'font-size:' . $fsBody . ';color:' . $colorText . ';padding:12px 10px;">'
                .   esc($m['homeName']) . ' &ndash; ' . esc($m['awayName']) . '</td>'
                . '<td align="center" style="' . $f . 'font-size:' . $fsBody . ';'
                .   'color:' . $colorMuted . ';padding:12px 8px;white-space:nowrap;">'
                .   esc($m['naszCzas']) . '</td>'
                . '<td align="center" style="padding:12px 8px;white-space:nowrap;">' . $typTxt . '</td>'
                . '</tr>';
        }
 
        $dzisiajHtml =
            '<tr><td bgcolor="' . $colorCard . '" style="padding:28px 32px 0;">'
            . $sectionLabel('Nadchodz&#x105;ce mecze (24h)')
            . '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">'
            . '<tr bgcolor="' . $colorBgGray . '">'
            . $thCell('Mecz')
            . $thCell('Godz.',        'center')
            . $thCell('Tw&oacute;j typ', 'center')
            . '</tr>'
            . $rows
            . '</table>'
            . '</td></tr>';
    }
 
    // ── PYTANIA DNIA (aktywne) ───────────────────────────────────────
    $dzisiajPytaniaHtml = '';
    if (!empty($data['dzisiajPytania'])) {
        $dzisiajPytaniaHtml = '<tr><td bgcolor="' . $colorCard . '" style="padding:28px 32px 0;">'
            . $sectionLabel('Pytanie dnia');
 
        foreach ($data['dzisiajPytania'] as $p) {
            $opisHtml   = !empty($p['opis'])
                ? '<tr><td bgcolor="' . $colorBgYellow . '" style="' . $f . 'font-size:' . $fsLabel . ';'
                .   'color:' . $colorMuted . ';padding:4px 14px 0;">' . esc($p['opis']) . '</td></tr>'
                : '';
            $zrodloHtml = !empty($p['zrodlo'])
                ? '<tr><td bgcolor="' . $colorBgYellow . '" style="' . $f . 'font-size:' . $fsLabel . ';'
                .   'color:' . $colorLabel . ';padding:2px 14px 0;">&#x1F4CA;&nbsp;' . esc($p['zrodlo']) . '</td></tr>'
                : '';
 
            $userOdpHtml = $p['hasOdp']
                ? '<tr><td bgcolor="' . $colorBgYellow . '" style="padding:12px 14px 16px;border-radius:0 0 8px 8px;">'
                .   '<table cellpadding="0" cellspacing="0" border="0"><tr>'
                .   '<td style="' . $f . 'font-size:' . $fsBody . ';color:' . $colorMuted . ';padding-right:12px;">'
                .     'Twoja odpowied&#x17A;: <strong style="color:' . $colorText . ';">' . esc($p['userOdp']) . '</strong>'
                .   '</td>'
                .   '<td><a href="' . $url . '" style="' . $f . 'font-size:' . $fsLabel . ';'
                .     'font-weight:700;color:' . $colorGold . ';text-decoration:none;">zmie&#x144; &rarr;</a></td>'
                .   '</tr></table>'
                . '</td></tr>'
                : '<tr><td bgcolor="' . $colorBgYellow . '" align="right"'
                .   ' style="padding:10px 14px 14px;border-radius:0 0 8px 8px;">'
                .   '<a href="' . $url . '" style="' . $f . 'font-size:' . $fsBody . ';font-weight:700;'
                .     'color:' . $colorRed . ';text-decoration:none;">Wpisz odpowied&#x17A; &rarr;</a>'
                . '</td></tr>';
 
            $dzisiajPytaniaHtml .=
                '<table width="100%" cellpadding="0" cellspacing="0" border="0"'
                . ' style="border-collapse:collapse;border:1px solid ' . $colorBorderY . ';border-radius:8px;margin-bottom:10px;">'
                . '<tr><td bgcolor="' . $colorBgYellow . '" style="' . $f . 'font-size:' . $fsBody . ';'
                .   'font-weight:600;color:' . $colorText . ';padding:16px 14px 8px;line-height:1.4;'
                .   'border-radius:8px 8px 0 0;">' . esc($p['tresc']) . '</td></tr>'
                . $opisHtml
                . $zrodloHtml
                . $userOdpHtml
                . '</table>';
        }
 
        $dzisiajPytaniaHtml .= '</td></tr>';
    }
 
    // ── KOMENTARZ ZAMKNIĘCIA ─────────────────────────────────────────
    $komentarzClosingHtml = '';
    if (!empty($data['adminKomentarz3'])) {
        $komentarzClosingHtml =
            '<tr><td bgcolor="' . $colorCard . '" style="padding:24px 32px 0;">'
            . $komentarzBlock(esc($data['adminKomentarz3']), $colorGreen, $colorBgGreen)
            . '</td></tr>';
    }
 
    // ── FOOTER ───────────────────────────────────────────────────────
    $footerHtml =
        '<tr><td bgcolor="' . $colorCard . '" style="padding:32px 32px 28px;border-radius:0 0 12px 12px;">'
        . '<table width="100%" cellpadding="0" cellspacing="0" border="0">'
        . '<tr><td style="border-top:1px solid ' . $colorBorder . ';padding-top:20px;">'
        . '<p style="margin:0 0 6px;' . $f . 'font-size:' . $fsLabel . ';color:' . $colorLabel . ';font-style:italic;">'
        .   'may the odds be always in your <em>flavour</em>'
        . '</p>'
        . '<p style="margin:0;' . $f . 'font-size:' . $fsLabel . ';color:#cccccc;">'
        .   'Je&#x15B;li dobrze si&#x119; bawisz, postaw kaw&#x119; &#x2615; &rarr; '
        .   '<a href="https://buycoffee.to/wit" style="color:#cccccc;">buycoffee.to/wit</a>'
        . '</p>'
        . '</td></tr>'
        . '</table>'
        . '</td></tr>';
 
    // ── SKŁADAMY EMAIL ───────────────────────────────────────────────
    return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'
         . ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
         . '<html xmlns="http://www.w3.org/1999/xhtml"><head>'
         . '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
         . '<meta name="viewport" content="width=device-width,initial-scale=1">'
         . '</head>'
         . '<body style="margin:0;padding:0;background:' . $colorBg . ';' . $f . '">'
 
         . '<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="' . $colorBg . '">'
         . '<tr><td align="center" style="padding:32px 12px;">'
 
         . '<table width="580" cellpadding="0" cellspacing="0" border="0"'
         . ' style="border-collapse:collapse;max-width:580px;">'
 
         . $headerHtml
         . $komentarzHtml
         . $pktHtml
         . $wczorajHtml
         . $wczorajPytaniaHtml
         . $dzisiajHtml
         . $dzisiajPytaniaHtml
         . $komentarzClosingHtml
         . $footerHtml
 
         . '</table>'
         . '</td></tr>'
         . '</table>'
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


