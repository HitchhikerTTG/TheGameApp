<?php
// ── Stałe ───────────────────────────────────────────────────────────
$f       = 'font-family:Arial,Helvetica,sans-serif;';
$cBg     = '#f0f0f3';  $cCard   = '#ffffff';
$cHeader = '#0d0d0f';  $cAccent = '#e8ff47';
$cText   = '#1a1a2e';  $cMuted  = '#555560';
$cLabel  = '#aaaabc';  $cGreen  = '#1a9e5c';
$cGold   = '#b38a00';  $cRed    = '#d93636';
$cBgGray = '#f8f8f4';  $cBgGreen= '#f0fff8';
$cBgYellow='#fffbf0';  $cBorderY= '#f0e68c';
$cBorder = '#f0f0f3';

$fsBody  = '18px'; $fsSub   = '16px'; $fsSmall = '15px';
$fsLabel = '12px'; $fsStat  = '26px'; $fsScore = '22px';
$fsLogo  = '24px';

// ── Helpery ─────────────────────────────────────────────────────────
$sectionLabel = fn(string $text) =>
    '<p style="margin:0 0 14px;' . $f . 'font-size:' . $fsLabel . ';font-weight:700;'
    . 'letter-spacing:2px;text-transform:uppercase;color:' . $cLabel . ';">'
    . $text . '</p>';

$komentarzBlock = fn(string $text, string $borderColor, string $bg) =>
    '<table width="100%" cellpadding="0" cellspacing="0" border="0">'
    . '<tr>'
    . '<td width="4" bgcolor="' . $borderColor . '" style="font-size:0;">&nbsp;</td>'
    . '<td style="padding:12px 16px;background:' . $bg . ';'
    .   $f . 'font-size:' . $fsBody . ';color:' . $cText . ';line-height:1.6;">'
    . $text . '</td>'
    . '</tr></table>';

$matchCard = fn(string $inner, string $bg, string $border = '', string $mb = '8px') =>
    '<table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:' . $mb . ';">'
    . '<tr><td bgcolor="' . $bg . '" style="padding:14px 16px;border-radius:8px;'
    . ($border ? 'border:1px solid ' . $border . ';' : '') . '">'
    . '<table width="100%" cellpadding="0" cellspacing="0" border="0">' . $inner . '</table>'
    . '</td></tr></table>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style type="text/css">
    body { margin:0; padding:0; background:<?= $cBg ?>; }
    table { border-collapse:collapse; }
    .wrap { width:100%; max-width:520px; margin:0 auto; }
  </style>
</head>
<body style="margin:0;padding:0;background:<?= $cBg ?>;<?= $f ?>">

<table width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="<?= $cBg ?>">
<tr><td align="center" style="padding:24px 12px;">
<table class="wrap" cellpadding="0" cellspacing="0" border="0">

  <!-- HEADER -->
  <tr>
    <td bgcolor="<?= $cHeader ?>" style="padding:24px 24px 20px;border-radius:12px 12px 0 0;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td>
            <span style="<?= $f ?>font-size:<?= $fsLogo ?>;font-weight:900;letter-spacing:2px;color:#ffffff;text-transform:uppercase;">TYPER</span><span style="<?= $f ?>font-size:<?= $fsLogo ?>;font-weight:900;color:<?= $cAccent ?>;"> .</span><span style="<?= $f ?>font-size:<?= $fsLogo ?>;font-weight:900;letter-spacing:2px;color:#ffffff;text-transform:uppercase;"> M&#346; 2026</span>
          </td>
          <td align="right" valign="middle" style="white-space:nowrap;">
            <span style="<?= $f ?>font-size:<?= $fsLabel ?>;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:<?= $cMuted ?>;"><?= date('d.m.Y') ?></span>
          </td>
        </tr>
        <tr>
          <td colspan="2" style="padding-top:14px;">
            <p style="margin:0;<?= $f ?>font-size:<?= $fsBody ?>;color:#a0a0b0;line-height:1.5;">
              Hej <strong style="color:#ffffff;"><?= esc($nick) ?></strong>! &#x1F44B; Oto Tw&oacute;j codzienny przegląd.
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

  <!-- KOMENTARZ ADMINA -->
  <?php if (!empty($adminKomentarz)): ?>
  <tr>
    <td bgcolor="<?= $cCard ?>" style="padding:20px 24px 0;">
      <?= $komentarzBlock(esc($adminKomentarz), $cAccent, $cBgGray) ?>
    </td>
  </tr>
  <?php endif ?>

  <!-- STATYSTYKI -->
  <?php if (isset($wszystkiePkt)): ?>
  <tr>
    <td bgcolor="<?= $cCard ?>" style="padding:24px 24px 0;">
      <?= $sectionLabel('Twoje statystyki') ?>
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td width="32%" align="center" bgcolor="<?= $cBgGray ?>" style="padding:14px 4px;border-radius:8px;">
            <p style="margin:0 0 4px;<?= $f ?>font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:<?= $cLabel ?>;">Punkty</p>
            <p style="margin:0;<?= $f ?>font-size:<?= $fsStat ?>;font-weight:900;color:<?= $cText ?>;"><?= (int)$wszystkiePkt ?></p>
          </td>
          <td width="2%" style="font-size:0;">&nbsp;</td>
          <td width="32%" align="center" bgcolor="<?= $cBgGreen ?>" style="padding:14px 4px;border-radius:8px;">
            <p style="margin:0 0 4px;<?= $f ?>font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:<?= $cLabel ?>;">Wczoraj</p>
            <p style="margin:0;<?= $f ?>font-size:<?= $fsStat ?>;font-weight:900;color:<?= $cGreen ?>;">+<?= (int)$wczorajPkt ?></p>
          </td>
          <td width="2%" style="font-size:0;">&nbsp;</td>
          <td width="32%" align="center" bgcolor="<?= $cBgYellow ?>" style="padding:14px 4px;border-radius:8px;">
            <p style="margin:0 0 4px;<?= $f ?>font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:<?= $cLabel ?>;">Miejsce</p>
            <p style="margin:0;<?= $f ?>font-size:<?= $fsStat ?>;font-weight:900;color:<?= $cGold ?>;"><?= (int)$rankingPozycja ?>.</p>
          </td>
        </tr>
      </table>
    </td>
  </tr>
  <?php endif ?>

  <!-- WYNIKI MECZÓW -->
  <?php if (!empty($wczorajMecze)): ?>
  <tr>
    <td bgcolor="<?= $cCard ?>" style="padding:24px 24px 0;">
      <?= $sectionLabel('Wyniki z ostatnich 24h') ?>
      <?php foreach ($wczorajMecze as $m):
        $wynik  = (int)$m['homeScore'] . ' : ' . (int)$m['awayScore'];
        $typTxt = $m['userHome'] !== null
            ? (int)$m['userHome'] . ':' . (int)$m['userAway'] . ($m['isGolden'] ? ' &#x26BD;' : '')
            : '<em style="color:' . $cLabel . ';">brak</em>';
        $pkt    = (int)$m['pkt'];
        $pktCol = $pkt > 0 ? $cGreen : $cLabel;
        $inner  =
            '<tr>'
            . '<td style="' . $f . 'font-size:' . $fsBody . ';font-weight:600;color:' . $cText . ';">'
            .   esc($m['homeName']) . ' &ndash; ' . esc($m['awayName']) . '</td>'
            . '<td align="right" style="' . $f . 'font-size:' . $fsScore . ';font-weight:900;color:' . $cText . ';white-space:nowrap;">' . $wynik . '</td>'
            . '</tr>'
            . '<tr>'
            . '<td style="' . $f . 'font-size:' . $fsSmall . ';color:' . $cLabel . ';padding-top:4px;">'
            .   'Tw&oacute;j typ: <strong style="color:' . $cText . ';">' . $typTxt . '</strong></td>'
            . '<td align="right" style="' . $f . 'font-size:' . $fsBody . ';font-weight:900;color:' . $pktCol . ';padding-top:4px;white-space:nowrap;">'
            .   ($pkt > 0 ? '+' . $pkt : '0') . ' pkt</td>'
            . '</tr>';
        echo $matchCard($inner, $cBgGray);
      endforeach ?>
    </td>
  </tr>
  <?php endif ?>

  <!-- WYNIKI PYTAŃ -->
  <?php if (!empty($wczorajPytania)): ?>
  <tr>
    <td bgcolor="<?= $cCard ?>" style="padding:24px 24px 0;">
      <?php if (!empty($adminKomentarz2)): ?>
        <?= $komentarzBlock(esc($adminKomentarz2), $cGreen, $cBgGreen) ?>
        <div style="height:16px;font-size:0;">&nbsp;</div>
      <?php endif ?>
      <?= $sectionLabel('Wyniki za pytania') ?>
      <?php foreach ($wczorajPytania as $p):
        $prawidlowa = $p['odpowiedz']
            ? '<strong style="color:' . $cGreen . ';">&#x2713;&nbsp;' . esc($p['odpowiedz']) . '</strong>'
            : '<em style="color:' . $cLabel . ';">nie podano</em>';
        $userOdp = $p['userOdp']
            ? '<strong style="color:' . $cText . ';">' . esc($p['userOdp']) . '</strong>'
            : '<span style="color:' . $cRed . ';">brak</span>';
        $pkt     = (int)$p['pkt'];
        $pktCol  = $pkt > 0 ? $cGreen : $cLabel;
      ?>
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:8px;border:1px solid <?= $cBorder ?>;border-radius:8px;">
        <tr>
          <td style="<?= $f ?>font-size:<?= $fsBody ?>;font-weight:600;color:<?= $cText ?>;padding:14px 16px 8px;line-height:1.4;">
            <?= esc($p['tresc']) ?>
          </td>
        </tr>
        <tr>
          <td style="padding:0 16px 14px;">
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
              <tr>
                <td style="<?= $f ?>font-size:<?= $fsSub ?>;color:<?= $cLabel ?>;">Prawid&#322;owa: <?= $prawidlowa ?></td>
                <td align="right" style="<?= $f ?>font-size:<?= $fsSub ?>;color:<?= $cLabel ?>;white-space:nowrap;">
                  Twoja: <?= $userOdp ?> &nbsp;<strong style="color:<?= $pktCol ?>;"><?= ($pkt > 0 ? '+' . $pkt : '0') ?> pkt</strong>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
      <?php endforeach ?>
    </td>
  </tr>
  <?php endif ?>

  <!-- NADCHODZĄCE MECZE -->
  <?php if (!empty($dzisiajMecze)): ?>
  <tr>
    <td bgcolor="<?= $cCard ?>" style="padding:24px 24px 0;">
      <?= $sectionLabel('Nadchodz&#x105;ce mecze (24h)') ?>
      <?php foreach ($dzisiajMecze as $m): ?>
        <?php if ($m['hasTyp']):
          $inner =
              '<tr>'
              . '<td style="' . $f . 'font-size:' . $fsBody . ';font-weight:600;color:' . $cText . ';">'
              .   esc($m['homeName']) . ' &ndash; ' . esc($m['awayName']) . '</td>'
              . '<td align="right" style="' . $f . 'font-size:' . $fsSmall . ';color:' . $cMuted . ';white-space:nowrap;">'
              .   esc($m['naszCzas']) . '</td>'
              . '</tr>'
              . '<tr><td colspan="2" style="' . $f . 'font-size:' . $fsSub . ';color:' . $cLabel . ';padding-top:4px;">'
              .   'Tw&oacute;j typ: <strong style="color:' . $cText . ';">'
              .   (int)$m['userHome'] . ':' . (int)$m['userAway'] . ($m['isGolden'] ? ' &#x26BD;' : '')
              .   '</strong></td></tr>';
          echo $matchCard($inner, $cBgGray);
        else:
          $inner =
              '<tr>'
              . '<td style="' . $f . 'font-size:' . $fsBody . ';font-weight:600;color:' . $cText . ';">'
              .   esc($m['homeName']) . ' &ndash; ' . esc($m['awayName']) . '</td>'
              . '<td align="right" style="' . $f . 'font-size:' . $fsSmall . ';color:' . $cMuted . ';white-space:nowrap;">'
              .   esc($m['naszCzas']) . '</td>'
              . '</tr>'
              . '<tr><td colspan="2" style="padding-top:10px;">'
              . '<a href="' . $url . '" style="' . $f . 'font-size:17px;font-weight:700;color:#ffffff;'
              .   'text-decoration:none;background:' . $cRed . ';padding:8px 20px;border-radius:6px;display:inline-block;">Typuj! &raquo;</a>'
              . '</td></tr>';
          echo $matchCard($inner, '#fff5f5', '#ffd0d0');
        endif ?>
      <?php endforeach ?>
    </td>
  </tr>
  <?php endif ?>

  <!-- PYTANIA DNIA -->
  <?php if (!empty($dzisiajPytania)): ?>
  <tr>
    <td bgcolor="<?= $cCard ?>" style="padding:24px 24px 0;">
      <?= $sectionLabel('Pytanie dnia') ?>
      <?php foreach ($dzisiajPytania as $p): ?>
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid <?= $cBorderY ?>;border-radius:8px;margin-bottom:10px;">
        <tr>
          <td bgcolor="<?= $cBgYellow ?>" style="<?= $f ?>font-size:<?= $fsBody ?>;font-weight:600;color:<?= $cText ?>;padding:16px 16px 8px;line-height:1.4;border-radius:8px 8px 0 0;">
            <?= esc($p['tresc']) ?>
          </td>
        </tr>
        <?php if (!empty($p['opis'])): ?>
        <tr>
          <td bgcolor="<?= $cBgYellow ?>" style="<?= $f ?>font-size:<?= $fsSmall ?>;color:<?= $cMuted ?>;padding:4px 16px 0;">
            <?= esc($p['opis']) ?>
          </td>
        </tr>
        <?php endif ?>
        <?php if (!empty($p['zrodlo'])): ?>
        <tr>
          <td bgcolor="<?= $cBgYellow ?>" style="<?= $f ?>font-size:14px;color:<?= $cLabel ?>;padding:2px 16px 0;">
            &#x1F4CA;&nbsp;<?= esc($p['zrodlo']) ?>
          </td>
        </tr>
        <?php endif ?>
        <?php if (!empty($p['deadline'])): ?>
        <tr>
          <td bgcolor="<?= $cBgYellow ?>" style="<?= $f ?>font-size:14px;color:<?= $cLabel ?>;padding:4px 16px 0;">
            Odpowiedz przed: <?= esc($p['deadline']) ?>
          </td>
        </tr>
        <?php endif ?>
        <tr>
          <td bgcolor="<?= $cBgYellow ?>" style="padding:12px 16px 16px;border-radius:0 0 8px 8px;">
            <?php if ($p['hasOdp']): ?>
            <table width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
              <td style="<?= $f ?>font-size:<?= $fsBody ?>;color:<?= $cMuted ?>;">
                Twoja: <strong style="color:<?= $cText ?>;"><?= esc($p['userOdp']) ?></strong>
              </td>
              <td align="right">
                <a href="<?= $url ?>" style="<?= $f ?>font-size:<?= $fsSub ?>;font-weight:700;color:<?= $cGold ?>;text-decoration:none;">zmie&#x144; &rarr;</a>
              </td>
            </tr></table>
            <?php else: ?>
            <a href="<?= $url ?>" style="<?= $f ?>font-size:<?= $fsBody ?>;font-weight:700;color:<?= $cRed ?>;text-decoration:none;">Wpisz odpowied&#x17A; &raquo;</a>
            <?php endif ?>
          </td>
        </tr>
      </table>
      <?php endforeach ?>
    </td>
  </tr>
  <?php endif ?>

  <!-- KOMENTARZ ZAMKNIĘCIA -->
  <?php if (!empty($adminKomentarz3)): ?>
  <tr>
    <td bgcolor="<?= $cCard ?>" style="padding:24px 24px 0;">
      <?= $komentarzBlock(esc($adminKomentarz3), $cGreen, $cBgGreen) ?>
    </td>
  </tr>
  <?php endif ?>

  <!-- FOOTER -->
  <tr>
    <td bgcolor="<?= $cCard ?>" style="padding:28px 24px 24px;border-radius:0 0 12px 12px;">
      <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
          <td style="border-top:1px solid <?= $cBorder ?>;padding-top:20px;">
            <p style="margin:0 0 6px;<?= $f ?>font-size:14px;color:<?= $cLabel ?>;font-style:italic;">may the odds be always in your <em>flavour</em></p>
            <p style="margin:0 0 6px;<?= $f ?>font-size:14px;color:<?= $cLabel ?>">Jeśli widzisz w tym mailu coś, co się nie zgadza - daj mi proszę znać (choćby odpisując na tego maila). Postaram się to naprostować. Dziękuję. </em></p>
            <p style="margin:0;<?= $f ?>font-size:14px;color:#cccccc;">
              A jeśli dobrze się bawisz, pamietaj że zawsze możesz mi postawić kaw&#x119; &#x2615; &rarr;
              <a href="https://buycoffee.to/wit" style="color:#cccccc;">buycoffee.to/wit</a>
            </p>
          </td>
        </tr>
      </table>
    </td>
  </tr>

</table>
</td></tr>
</table>

</body>
</html>