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
 
    // ── Stałe kolorystyczne ──────────────────────────────────────────
    $cBg        = '#f0f0f3';
    $cCard      = '#ffffff';
    $cHeader    = '#0d0d0f';
    $cAccent    = '#e8ff47';
    $cText      = '#1a1a2e';
    $cMuted     = '#555560';
    $cLabel     = '#aaaabc';
    $cGreen     = '#1a9e5c';
    $cGold      = '#b38a00';
    $cRed       = '#d93636';
    $cBgGray    = '#f8f8f4';
    $cBgGreen   = '#f0fff8';
    $cBgYellow  = '#fffbf0';
    $cBorderY   = '#f0e68c';
    $cBorder    = '#f0f0f3';
 
    // ── Stałe typograficzne ──────────────────────────────────────────
    $fsBody     = '18px';
    $fsSub      = '16px';
    $fsSmall    = '15px';
    $fsLabel    = '12px';
    $fsStat     = '26px';
    $fsScore    = '22px';
    $fsLogo     = '24px';
 
    // ── Helper: nagłówek sekcji ──────────────────────────────────────
    $sectionLabel = fn(string $text) =>
        '<p style="margin:0 0 14px;' . $f . 'font-size:' . $fsLabel . ';font-weight:700;'
        . 'letter-spacing:2px;text-transform:uppercase;color:' . $cLabel . ';">'
        . $text . '</p>';
 
    // ── Helper: blok komentarza z lewą kreską ────────────────────────
    $komentarzBlock = fn(string $text, string $borderColor, string $bgColor, string $marginBottom = '0') =>
        '<table width="100%" cellpadding="0" cellspacing="0" border="0"'
        . ($marginBottom ? ' style="margin-bottom:' . $marginBottom . ';"' : '') . '>'
        . '<tr>'
        . '<td width="4" bgcolor="' . $borderColor . '" style="font-size:0;">&nbsp;</td>'
        . '<td style="padding:12px 16px;background:' . $bgColor . ';'
        .   $f . 'font-size:' . $fsBody . ';color:' . $cText . ';line-height:1.6;">'
        . $text
        . '</td>'
        . '</tr>'
        . '</table>';
 
    // ── Helper: karta meczu (wyniki i nadchodzące) ───────────────────
    $matchCard = fn(string $inner, string $bg = '', string $border = '', string $mb = '8px') =>
        '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:' . $mb . ';">'
        . '<tr>'
        . '<td bgcolor="' . ($bg ?: $cBgGray) . '" style="padding:14px 16px;border-radius:8px;'
        . ($border ? 'border:1px solid ' . $border . ';' : '') . '">'
        . '<table width="100%" cellpadding="0" cellspacing="0" border="0">'
        . $inner
        . '</table>'
        . '</td>'
        . '</tr>'
        . '</table>';
 
    // ════════════════════════════════════════════════════════════════
    // HEADER
    // ════════════════════════════════════════════════════════════════
    $headerHtml =
        '<tr>'
        . '<td bgcolor="' . $cHeader . '" style="padding:24px 24px 20px;border-radius:12px 12px 0 0;">'
        . '<table width="100%" cellpadding="0" cellspacing="0" border="0">'
        . '<tr>'
        . '<td>'
        . '<span style="' . $f . 'font-size:' . $fsLogo . ';font-weight:900;letter-spacing:2px;color:#ffffff;text-transform:uppercase;">TYPER</span>'
        . '<span style="' . $f . 'font-size:' . $fsLogo . ';font-weight:900;color:' . $cAccent . ';">.</span>'
        . '<span style="' . $f . 'font-size:' . $fsLogo . ';font-weight:900;letter-spacing:2px;color:#ffffff;text-transform:uppercase;">M&#346; 2026</span>'
        . '</td>'
        . '<td align="right" valign="middle" style="white-space:nowrap;">'
        . '<span style="' . $f . 'font-size:' . $fsLabel . ';font-weight:700;letter-spacing:1px;text-transform:uppercase;color:' . $cMuted . ';">'
        . date('d.m.Y')
        . '</span>'
        . '</td>'
        . '</tr>'
        . '<tr>'
        . '<td colspan="2" style="padding-top:14px;">'
        . '<p style="margin:0;' . $f . 'font-size:' . $fsBody . ';color:#a0a0b0;line-height:1.5;">'
        . 'Hej <strong style="color:#ffffff;">' . $nick . '</strong>! &#x1F44B; Oto Tw&oacute;j codzienny przegląd.'
        . '</p>'
        . '</td>'
        . '</tr>'
        . '</table>'
        . '</td>'
        . '</tr>';
 
    // ════════════════════════════════════════════════════════════════
    // KOMENTARZ ADMINA (otwarcie)
    // ════════════════════════════════════════════════════════════════
    $komentarzHtml = '';
    if (!empty($data['adminKomentarz'])) {
        $komentarzHtml =
            '<tr><td bgcolor="' . $cCard . '" style="padding:20px 24px 0;">'
            . $komentarzBlock(esc($data['adminKomentarz']), $cAccent, $cBgGray)
            . '</td></tr>';
    }
 
    // ════════════════════════════════════════════════════════════════
    // STATYSTYKI
    // ════════════════════════════════════════════════════════════════
    $pktHtml = '';
    if (isset($data['wszystkiePkt'])) {
        $kafelek = fn(string $bg, string $label, string $value, string $vColor) =>
            '<td width="32%" align="center" bgcolor="' . $bg . '" style="padding:14px 4px;border-radius:8px;">'
            . '<p style="margin:0 0 4px;' . $f . 'font-size:11px;font-weight:700;letter-spacing:1px;'
            .   'text-transform:uppercase;color:' . $cLabel . ';">' . $label . '</p>'
            . '<p style="margin:0;' . $f . 'font-size:' . $fsStat . ';font-weight:900;color:' . $vColor . ';">' . $value . '</p>'
            . '</td>';
 
        $pktHtml =
            '<tr><td bgcolor="' . $cCard . '" style="padding:24px 24px 0;">'
            . $sectionLabel('Twoje statystyki')
            . '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>'
            . $kafelek($cBgGray,   'Punkty',   (string)(int)$data['wszystkiePkt'],         $cText)
            . '<td width="2%" style="font-size:0;">&nbsp;</td>'
            . $kafelek($cBgGreen,  'Wczoraj',  '+' . (string)(int)$data['wczorajPkt'],     $cGreen)
            . '<td width="2%" style="font-size:0;">&nbsp;</td>'
            . $kafelek($cBgYellow, 'Miejsce',  (string)(int)$data['rankingPozycja'] . '.', $cGold)
            . '</tr></table>'
            . '</td></tr>';
    }
 
    // ════════════════════════════════════════════════════════════════
    // WYNIKI MECZÓW (ostatnie 24h)
    // ════════════════════════════════════════════════════════════════
    $wczorajHtml = '';
    if (!empty($data['wczorajMecze'])) {
        $rows = '';
        foreach ($data['wczorajMecze'] as $m) {
            $wynik  = (int)$m['homeScore'] . ' : ' . (int)$m['awayScore'];
            $hasTyp = $m['userHome'] !== null;
            $typTxt = $hasTyp
                ? (int)$m['userHome'] . ':' . (int)$m['userAway'] . ($m['isGolden'] ? ' &#x26BD;' : '')
                : '<em style="color:' . $cLabel . ';">brak</em>';
            $pkt    = (int)$m['pkt'];
            $pktTxt = $pkt > 0 ? '+' . $pkt . ' pkt' : '0 pkt';
            $pktCol = $pkt > 0 ? $cGreen : $cLabel;
 
            $inner =
                '<tr>'
                . '<td style="' . $f . 'font-size:' . $fsBody . ';font-weight:600;color:' . $cText . ';">'
                .   esc($m['homeName']) . ' &ndash; ' . esc($m['awayName'])
                . '</td>'
                . '<td align="right" style="' . $f . 'font-size:' . $fsScore . ';font-weight:900;'
                .   'color:' . $cText . ';white-space:nowrap;">' . $wynik . '</td>'
                . '</tr>'
                . '<tr>'
                . '<td style="' . $f . 'font-size:' . $fsSmall . ';color:' . $cLabel . ';padding-top:4px;">'
                .   'Tw&oacute;j typ: <strong style="color:' . $cText . ';">' . $typTxt . '</strong>'
                . '</td>'
                . '<td align="right" style="' . $f . 'font-size:' . $fsBody . ';font-weight:900;'
                .   'color:' . $pktCol . ';padding-top:4px;white-space:nowrap;">' . $pktTxt . '</td>'
                . '</tr>';
 
            $rows .= $matchCard($inner, $cBgGray, '', '8px');
        }
 
        $wczorajHtml =
            '<tr><td bgcolor="' . $cCard . '" style="padding:24px 24px 0;">'
            . $sectionLabel('Wyniki z ostatnich 24h')
            . $rows
            . '</td></tr>';
    }
 
    // ════════════════════════════════════════════════════════════════
    // KOMENTARZ 2 + WYNIKI PYTAŃ
    // ════════════════════════════════════════════════════════════════
    $wczorajPytaniaHtml = '';
    if (!empty($data['wczorajPytania'])) {
        $blok = '<tr><td bgcolor="' . $cCard . '" style="padding:24px 24px 0;">';
 
        if (!empty($data['adminKomentarz2'])) {
            $blok .= $komentarzBlock(esc($data['adminKomentarz2']), $cGreen, $cBgGreen, '16px');
        }
 
        $blok .= $sectionLabel('Wyniki za pytania');
 
        foreach ($data['wczorajPytania'] as $p) {
            $prawidlowaHtml = $p['odpowiedz']
                ? '<strong style="color:' . $cGreen . ';">&#x2713;&nbsp;' . esc($p['odpowiedz']) . '</strong>'
                : '<em style="color:' . $cLabel . ';">nie podano</em>';
            $userOdpHtml = $p['userOdp']
                ? '<strong style="color:' . $cText . ';">' . esc($p['userOdp']) . '</strong>'
                : '<span style="color:' . $cRed . ';">brak</span>';
            $pkt      = (int)$p['pkt'];
            $pktColor = $pkt > 0 ? $cGreen : $cLabel;
            $pktLabel = $pkt > 0 ? '+' . $pkt . ' pkt' : '0 pkt';
 
            $blok .=
                '<table width="100%" cellpadding="0" cellspacing="0" border="0"'
                . ' style="margin-bottom:8px;border:1px solid ' . $cBorder . ';border-radius:8px;">'
                . '<tr><td style="' . $f . 'font-size:' . $fsBody . ';font-weight:600;'
                .   'color:' . $cText . ';padding:14px 16px 8px;line-height:1.4;">'
                . esc($p['tresc'])
                . '</td></tr>'
                . '<tr><td style="padding:0 16px 14px;">'
                . '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr>'
                . '<td style="' . $f . 'font-size:' . $fsSub . ';color:' . $cLabel . ';">'
                .   'Prawid&#322;owa: ' . $prawidlowaHtml
                . '</td>'
                . '<td align="right" style="' . $f . 'font-size:' . $fsSub . ';color:' . $cLabel . ';white-space:nowrap;">'
                .   'Twoja: ' . $userOdpHtml
                .   ' &nbsp;<strong style="color:' . $pktColor . ';">' . $pktLabel . '</strong>'
                . '</td>'
                . '</tr></table>'
                . '</td></tr>'
                . '</table>';
        }
 
        $blok .= '</td></tr>';
        $wczorajPytaniaHtml = $blok;
    }
 
    // ════════════════════════════════════════════════════════════════
    // NADCHODZĄCE MECZE
    // ════════════════════════════════════════════════════════════════
    $dzisiajHtml = '';
    if (!empty($data['dzisiajMecze'])) {
        $rows = '';
        foreach ($data['dzisiajMecze'] as $m) {
            if ($m['hasTyp']) {
                $typRow =
                    '<tr>'
                    . '<td colspan="2" style="' . $f . 'font-size:' . $fsSub . ';color:' . $cLabel . ';padding-top:4px;">'
                    .   'Tw&oacute;j typ: <strong style="color:' . $cText . ';">'
                    .   (int)$m['userHome'] . ':' . (int)$m['userAway']
                    .   ($m['isGolden'] ? ' &#x26BD;' : '')
                    .   '</strong>'
                    . '</td>'
                    . '</tr>';
 
                $inner =
                    '<tr>'
                    . '<td style="' . $f . 'font-size:' . $fsBody . ';font-weight:600;color:' . $cText . ';">'
                    .   esc($m['homeName']) . ' &ndash; ' . esc($m['awayName'])
                    . '</td>'
                    . '<td align="right" style="' . $f . 'font-size:' . $fsSmall . ';color:' . $cMuted . ';white-space:nowrap;">'
                    .   esc($m['naszCzas'])
                    . '</td>'
                    . '</tr>'
                    . $typRow;
 
                $rows .= $matchCard($inner, $cBgGray, '', '8px');
            } else {
                $inner =
                    '<tr>'
                    . '<td style="' . $f . 'font-size:' . $fsBody . ';font-weight:600;color:' . $cText . ';">'
                    .   esc($m['homeName']) . ' &ndash; ' . esc($m['awayName'])
                    . '</td>'
                    . '<td align="right" style="' . $f . 'font-size:' . $fsSmall . ';color:' . $cMuted . ';white-space:nowrap;">'
                    .   esc($m['naszCzas'])
                    . '</td>'
                    . '</tr>'
                    . '<tr>'
                    . '<td colspan="2" style="padding-top:10px;">'
                    . '<a href="' . $url . '" style="' . $f . 'font-size:17px;font-weight:700;'
                    .   'color:#ffffff;text-decoration:none;background:' . $cRed . ';'
                    .   'padding:8px 20px;border-radius:6px;display:inline-block;">Obstaw! &rarr;</a>'
                    . '</td>'
                    . '</tr>';
 
                $rows .= $matchCard($inner, '#fff5f5', '#ffd0d0', '8px');
            }
        }
 
        $dzisiajHtml =
            '<tr><td bgcolor="' . $cCard . '" style="padding:24px 24px 0;">'
            . $sectionLabel('Nadchodz&#x105;ce mecze (24h)')
            . $rows
            . '</td></tr>';
    }
 
    // ════════════════════════════════════════════════════════════════
    // PYTANIA DNIA (aktywne)
    // ════════════════════════════════════════════════════════════════
    $dzisiajPytaniaHtml = '';
    if (!empty($data['dzisiajPytania'])) {
        $blok = '<tr><td bgcolor="' . $cCard . '" style="padding:24px 24px 0;">'
            . $sectionLabel('Pytanie dnia');
 
        foreach ($data['dzisiajPytania'] as $p) {
            $opisHtml   = !empty($p['opis'])
                ? '<tr><td bgcolor="' . $cBgYellow . '" style="' . $f . 'font-size:' . $fsSmall . ';'
                .   'color:' . $cMuted . ';padding:4px 16px 0;">' . esc($p['opis']) . '</td></tr>'
                : '';
            $zrodloHtml = !empty($p['zrodlo'])
                ? '<tr><td bgcolor="' . $cBgYellow . '" style="' . $f . 'font-size:14px;'
                .   'color:' . $cLabel . ';padding:2px 16px 0;">&#x1F4CA;&nbsp;' . esc($p['zrodlo']) . '</td></tr>'
                : '';
            $deadlineHtml =
                '<tr><td bgcolor="' . $cBgYellow . '" style="' . $f . 'font-size:14px;'
                . 'color:' . $cLabel . ';padding:4px 16px 0;">&#x1F4CA; Odpowiedz przed: '
                . esc($p['deadline'] ?? '') . '</td></tr>';
 
            $odpHtml = $p['hasOdp']
                ? '<tr><td bgcolor="' . $cBgYellow . '" style="padding:12px 16px 16px;border-radius:0 0 8px 8px;">'
                .   '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>'
                .   '<td style="' . $f . 'font-size:' . $fsBody . ';color:' . $cMuted . ';">'
                .     'Twoja: <strong style="color:' . $cText . ';">' . esc($p['userOdp']) . '</strong>'
                .   '</td>'
                .   '<td align="right">'
                .     '<a href="' . $url . '" style="' . $f . 'font-size:' . $fsSub . ';font-weight:700;'
                .     'color:' . $cGold . ';text-decoration:none;">zmie&#x144; &rarr;</a>'
                .   '</td>'
                .   '</tr></table>'
                . '</td></tr>'
                : '<tr><td bgcolor="' . $cBgYellow . '" align="right"'
                .   ' style="padding:12px 16px 16px;border-radius:0 0 8px 8px;">'
                .   '<a href="' . $url . '" style="' . $f . 'font-size:' . $fsBody . ';font-weight:700;'
                .   'color:' . $cRed . ';text-decoration:none;">Wpisz odpowied&#x17A; &rarr;</a>'
                . '</td></tr>';
 
            $blok .=
                '<table width="100%" cellpadding="0" cellspacing="0" border="0"'
                . ' style="border:1px solid ' . $cBorderY . ';border-radius:8px;margin-bottom:10px;">'
                . '<tr><td bgcolor="' . $cBgYellow . '" style="' . $f . 'font-size:' . $fsBody . ';'
                .   'font-weight:600;color:' . $cText . ';padding:16px 16px 8px;line-height:1.4;'
                .   'border-radius:8px 8px 0 0;">' . esc($p['tresc']) . '</td></tr>'
                . $opisHtml
                . $zrodloHtml
                . $deadlineHtml
                . $odpHtml
                . '</table>';
        }
 
        $blok .= '</td></tr>';
        $dzisiajPytaniaHtml = $blok;
    }
 
    // ════════════════════════════════════════════════════════════════
    // KOMENTARZ ZAMKNIĘCIA
    // ════════════════════════════════════════════════════════════════
    $komentarzClosingHtml = '';
    if (!empty($data['adminKomentarz3'])) {
        $komentarzClosingHtml =
            '<tr><td bgcolor="' . $cCard . '" style="padding:24px 24px 0;">'
            . $komentarzBlock(esc($data['adminKomentarz3']), $cGreen, $cBgGreen)
            . '</td></tr>';
    }
 
    // ════════════════════════════════════════════════════════════════
    // FOOTER
    // ════════════════════════════════════════════════════════════════
    $footerHtml =
        '<tr><td bgcolor="' . $cCard . '" style="padding:28px 24px 24px;border-radius:0 0 12px 12px;">'
        . '<table width="100%" cellpadding="0" cellspacing="0" border="0">'
        . '<tr><td style="border-top:1px solid ' . $cBorder . ';padding-top:20px;">'
        . '<p style="margin:0 0 6px;' . $f . 'font-size:14px;color:' . $cLabel . ';font-style:italic;">'
        .   'may the odds be always in your <em>flavour</em>'
        . '</p>'
        . '<p style="margin:0;' . $f . 'font-size:14px;color:#cccccc;">'
        .   'Postaw kaw&#x119; &#x2615; &rarr; '
        .   '<a href="https://buycoffee.to/wit" style="color:#cccccc;">buycoffee.to/wit</a>'
        . '</p>'
        . '</td></tr>'
        . '</table>'
        . '</td></tr>';
 
    // ════════════════════════════════════════════════════════════════
    // SKŁADAMY EMAIL
    // ════════════════════════════════════════════════════════════════
    return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"'
         . ' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'
         . '<html xmlns="http://www.w3.org/1999/xhtml"><head>'
         . '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">'
         . '<meta name="viewport" content="width=device-width,initial-scale=1">'
         . '<style type="text/css">'
         . 'body{margin:0;padding:0;background:' . $cBg . ';}'
         . 'table{border-collapse:collapse;}'
         . '.wrap{width:100%;max-width:520px;margin:0 auto;}'
         . '</style>'
         . '</head>'
         . '<body style="margin:0;padding:0;background:' . $cBg . ';' . $f . '">'
 
         . '<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="' . $cBg . '">'
         . '<tr><td align="center" style="padding:24px 12px;">'
         . '<table class="wrap" cellpadding="0" cellspacing="0" border="0">'
 
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


