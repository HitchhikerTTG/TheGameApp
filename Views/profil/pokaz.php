<div class="px-3 py-4" style="max-width:640px;margin:0 auto;">

  <!-- NAGŁÓWEK GRACZA -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="ff-bebas" style="font-size:48px;line-height:1;">
      <?= esc($gracz['emoji'] ?? '🙂') ?>
    </div>
    <div>
      <h1 class="ff-bebas mb-0" style="font-size:32px;"><?= esc($gracz['nick']) ?></h1>
      <?php if ($jaMojeKonto): ?>
        <a href="/profil" class="text-secondary" style="font-size:13px;">← Moje ustawienia</a>
      <?php endif ?>
    </div>
  </div>

  <!-- STATYSTYKI AKTYWNEGO TURNIEJU -->
  <p class="section-label mb-2"><?= esc($turniejName) ?></p>

  <!-- Blok punktów -->
  <div class="card match-card mb-3">
    <div class="card-body px-3 py-3">
      <div class="d-grid mb-0" style="grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center;">
        <div>
          <div class="ff-bebas" style="font-size:36px;color:var(--ty-accent);"><?= (int)$pktLacznie ?></div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Punkty łącznie</div>
        </div>
        <div>
          <div class="ff-bebas" style="font-size:36px;"><?= (int)$rankingPozycja ?>.</div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Miejsce</div>
        </div>
        <div>
          <div class="ff-bebas" style="font-size:36px;color:var(--ty-green);"><?= (int)$dokladne ?></div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Dokładnych</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Szczegóły -->
  <div class="card match-card mb-3">
    <div class="card-body px-3 py-3">
      <div class="py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <?php if (!empty($szczegolyMeczow)): ?>
        <details>
          <summary class="d-flex justify-content-between" style="cursor:pointer;list-style:none;">
            <span class="text-secondary" style="font-size:14px;">Punkty za mecze</span>
            <strong><?= (int)$pktMecze ?></strong>
          </summary>
          <div class="mt-2">
            <?php foreach ($szczegolyMeczow as $m): ?>
            <div class="d-flex justify-content-between align-items-center py-2" style="border-top:1px solid var(--bs-border-color);font-size:13px;">
              <div>
                <?= esc($m['HomeName']) ?> <?= (int)$m['ScoreHome'] ?>:<?= (int)$m['ScoreAway'] ?> <?= esc($m['AwayName']) ?>
                <div class="text-secondary" style="font-size:12px;">
                  Twój typ: <?= (int)$m['HomeTyp'] ?>:<?= (int)$m['AwayTyp'] ?><?= $m['GoldenGame'] ? ' ⚽' : '' ?>
                </div>
              </div>
              <strong <?= $m['pkt'] > 0 ? 'style="color:var(--ty-green);"' : '' ?>><?= (int)$m['pkt'] ?> pkt</strong>
            </div>
            <?php endforeach ?>
          </div>
        </details>
        <?php else: ?>
        <div class="d-flex justify-content-between">
          <span class="text-secondary" style="font-size:14px;">Punkty za mecze</span>
          <strong><?= (int)$pktMecze ?></strong>
        </div>
        <?php endif ?>
      </div>

      <div class="py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <?php if (!empty($szczegolyPytan)): ?>
        <details>
          <summary class="d-flex justify-content-between" style="cursor:pointer;list-style:none;">
            <span class="text-secondary" style="font-size:14px;">Punkty za pytania</span>
            <strong><?= (int)$pktPytania ?></strong>
          </summary>
          <div class="mt-2">
            <?php foreach ($szczegolyPytan as $p): ?>
            <div class="d-flex justify-content-between align-items-center py-2" style="border-top:1px solid var(--bs-border-color);font-size:13px;">
              <div>
                <?= esc($p['tresc']) ?>
                <div class="text-secondary" style="font-size:12px;">
                  Twoja odp.: <?= esc($p['mojaOdp']) ?> · Poprawna: <?= esc($p['poprawna']) ?>
                </div>
              </div>
              <strong <?= $p['pktZdobyte'] > 0 ? 'style="color:var(--ty-green);"' : '' ?>><?= (int)$p['pktZdobyte'] ?> / <?= (int)$p['pktMax'] ?> pkt</strong>
            </div>
            <?php endforeach ?>
          </div>
        </details>
        <?php else: ?>
        <div class="d-flex justify-content-between">
          <span class="text-secondary" style="font-size:14px;">Punkty za pytania</span>
          <strong><?= (int)$pktPytania ?></strong>
        </div>
        <?php endif ?>
      </div>

            <?php if ($liczbaTypow > 0): ?>
      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Trafnych typów (z pkt)</span>
        <strong><?= (int)$trafieniaKierunkowe ?> / <?= (int)$liczbaTypow ?>
          <span class="text-secondary fw-normal">(<?= $skutecznoscKierunkowa ?>%)</span>
        </strong>
      </div>
      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Skuteczność dokładna</span>
        <strong><?= (int)$dokladne ?> / <?= (int)$liczbaTypow ?>
          <span class="text-secondary fw-normal">(<?= $skutecznoscDokladna ?>%)</span>
        </strong>
      </div>
      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Średnio pkt / mecz</span>
        <strong><?= $srednioPktNaMecz ?>
          <span class="text-secondary fw-normal" style="font-size:13px;">
            (śr. turnieju: <?= $srednieTurnieju['sredniaPktNaMecz'] ?>)
          </span>
        </strong>
      </div>
      <?php endif ?>

      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Złota piłka ⚽</span>
        <strong><?= $goldenTrafiona ?> / <?= $goldenUzyta ?>
          <span class="text-secondary fw-normal" style="font-size:13px;">trafień / użyć</span>
        </strong>
      </div>

      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Najdłuższa passa z punktami 🔥</span>
        <strong><?= (int)$serie['najdluzsza'] ?> meczów</strong>
      </div>

<div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Obecna passa</span>
        <strong <?= $serie['obecna'] > 0 ? 'style="color:var(--ty-green);"' : '' ?>>
          <?= (int)$serie['obecna'] ?> meczów<?= $serie['obecna'] > 2 ? ' 🔥' : '' ?>
        </strong>
      </div>

      <?php if ($najczestszyWynikGracza): ?>
      <div class="d-flex justify-content-between py-2">
        <span class="text-secondary" style="font-size:14px;">Najczęściej wpisywany wynik</span>
        <strong class="ff-bebas" style="font-size:18px;">
          <?= (int)$najczestszyWynikGracza->HomeTyp ?> : <?= (int)$najczestszyWynikGracza->AwayTyp ?>
          <span class="text-secondary fw-normal" style="font-size:13px;">(<?= (int)$najczestszyWynikGracza->liczba ?>×)</span>
        </strong>
      </div>
      <?php endif ?>

    </div>
  </div>

  <!-- NAJLEPSZY / NAJGORSZY MECZ -->
  <?php if ($najlepszyMecz): ?>
  <div class="d-flex gap-2 mb-3">
    <div class="card match-card flex-fill">
      <div class="card-body px-3 py-3 text-center">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Najlepszy mecz</div>
        <div style="font-size:14px;margin-top:4px;"><?= esc($najlepszyMecz['HomeName']) ?> – <?= esc($najlepszyMecz['AwayName']) ?></div>
        <div class="ff-bebas" style="font-size:22px;color:var(--ty-green);"><?= (int)$najlepszyMecz['pkt'] ?> pkt</div>
      </div>
    </div>
    <div class="card match-card flex-fill">
      <div class="card-body px-3 py-3 text-center">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Najgorszy mecz</div>
        <div style="font-size:14px;margin-top:4px;"><?= esc($najgorszyMecz['HomeName']) ?> – <?= esc($najgorszyMecz['AwayName']) ?></div>
        <div class="ff-bebas" style="font-size:22px;"><?= (int)$najgorszyMecz['pkt'] ?> pkt</div>
      </div>
    </div>
  </div>
  <?php endif ?>

  <!-- TREND PUNKTOWY -->
  <?php if (count($trendPunktowy) > 1): ?>
  <div class="card match-card mb-3">
    <div class="card-body px-3 py-3">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);margin-bottom:8px;">
        Trend punktowy (suma narastająco)
      </div>
      <?php
        $max = max($trendPunktowy) ?: 1;
        $maxY5 = (int)(ceil($max / 5) * 5) ?: 5;
        $w = 300; $h = 150; $pR = 40; $pT = 10; $pB = 10;
        $chartW = $w - $pR; $chartH = $h - $pT - $pB;
        $n = count($trendPunktowy);
        $points = [];
        foreach ($trendPunktowy as $i => $v) {
            $x = $n > 1 ? ($i / ($n - 1)) * $chartW : 0;
            $y = $pT + $chartH - ($v / $maxY5) * $chartH;
            $points[] = round($x, 1) . ',' . round($y, 1);
        }
      ?>
      <svg viewBox="0 0 <?= $w ?> <?= $h ?>" style="width:100%;height:130px;" preserveAspectRatio="none">
        <?php for ($tick = 0; $tick <= $maxY5; $tick += 5):
          $ty = round($pT + $chartH - ($tick / $maxY5) * $chartH, 1); ?>
          <line x1="0" y1="<?= $ty ?>" x2="<?= $chartW ?>" y2="<?= $ty ?>"
                stroke="var(--bs-border-color)" stroke-width="0.5" stroke-dasharray="2,3"/>
          <text x="<?= $chartW + 4 ?>" y="<?= $ty + 3 ?>"
                style="font-size:9px;fill:var(--bs-secondary-color);"><?= $tick ?></text>
        <?php endfor; ?>
        <line x1="<?= $chartW ?>" y1="<?= $pT ?>" x2="<?= $chartW ?>" y2="<?= $pT + $chartH ?>"
              stroke="var(--bs-border-color)" stroke-width="0.5"/>
        <polyline points="<?= esc(implode(' ', $points), 'attr') ?>" fill="none" stroke="var(--ty-accent)" stroke-width="2"/>
      </svg>
    </div>
  </div>
  <?php endif ?>
    <!-- TREND POZYCJI -->
  <?php if (count($trendPozycji ?? []) > 1):
    $n      = count($trendPozycji);
    $minPoz = min($trendPozycji);
    $maxPoz = max($trendPozycji);
    $tickMin = max(1, (int)(floor($minPoz / 5) * 5));
    $tickMax = (int)(ceil($maxPoz / 5) * 5);
    $tickZakres = max(1, $tickMax - $tickMin);
    $w = 300; $h = 150; $pR = 44; $pT = 10; $pB = 10;
    $chartW = $w - $pR; $chartH = $h - $pT - $pB;
    $ostatnia = end($trendPozycji);
    $points = [];
    foreach ($trendPozycji as $i => $poz) {
        $x = $n > 1 ? ($i / ($n - 1)) * $chartW : 0;
        $y = $pT + (($poz - $tickMin) / $tickZakres) * $chartH;
        $points[] = round($x, 1) . ',' . round($y, 1);
    }
  ?>
  <div class="card match-card mb-3">
    <div class="card-body px-3 py-3">
      <div style="font-size:12px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);margin-bottom:8px;">
        Pozycja w tabeli mecz po meczu
        <span style="font-size:11px;font-weight:normal;text-transform:none;letter-spacing:0;">
          – aktualna: <strong><?= $ostatnia ?>. miejsce</strong>
        </span>
      </div>
      <svg viewBox="0 0 <?= $w ?> <?= $h ?>" style="width:100%;height:130px;" preserveAspectRatio="none">
        <?php for ($tick = $tickMin; $tick <= $tickMax; $tick += 5):
          $ty = round($pT + (($tick - $tickMin) / $tickZakres) * $chartH, 1); ?>
          <line x1="0" y1="<?= $ty ?>" x2="<?= $chartW ?>" y2="<?= $ty ?>"
                stroke="var(--bs-border-color)" stroke-width="0.5" stroke-dasharray="2,3"/>
          <text x="<?= $chartW + 4 ?>" y="<?= $ty + 3 ?>"
                style="font-size:9px;fill:var(--bs-secondary-color);"><?= $tick ?>.</text>
        <?php endfor; ?>
        <line x1="<?= $chartW ?>" y1="<?= $pT ?>" x2="<?= $chartW ?>" y2="<?= $pT + $chartH ?>"
              stroke="var(--bs-border-color)" stroke-width="0.5"/>
        <polyline points="<?= esc(implode(' ', $points), 'attr') ?>"
                  fill="none" stroke="var(--ty-accent)" stroke-width="2"/>
      </svg>
      <div style="font-size:12px;color:var(--bs-secondary-color);margin-top:4px;">
        Im wyżej na wykresie, tym lepsza pozycja w tabeli
      </div>
    </div>
  </div>
  <?php endif ?>
  <!-- MAPA TYPÓW GRACZA -->
<?php
  /* ── Budujemy siatkę typów ──
     X = AwayTyp (bramki gości), Y = HomeTyp (bramki gospodarza) */
  $typGrid = [];
  foreach ($szczegolyMeczow as $m) {
      if ($m['HomeTyp'] === null || $m['AwayTyp'] === null) continue;
      $h = (int)$m['HomeTyp']; $a = (int)$m['AwayTyp'];
      if (!isset($typGrid[$h][$a])) {
          $typGrid[$h][$a] = ['total' => 0, 'points' => 0, 'exact' => 0];
      }
      $typGrid[$h][$a]['total']++;
      if ((int)$m['pkt'] > 0)  $typGrid[$h][$a]['points']++;
      if ((int)$m['pkt'] >= 3) $typGrid[$h][$a]['exact']++;
  }
?>
<?php if (!empty($typGrid)): ?>
<?php
  $maxCnt = 1;
  foreach ($typGrid as $hA) foreach ($hA as $c) $maxCnt = max($maxCnt, $c['total']);

  /* Rozmiar siatki */
  $gMaxH = 5; $gMaxA = 5;
  foreach ($typGrid as $h => $aA) foreach ($aA as $a => $c) {
      $gMaxH = max($gMaxH, min((int)$h, 8));
      $gMaxA = max($gMaxA, min((int)$a, 8));
  }

  $cell = 50; $pL = 28; $pT = 8; $pB = 38; $pR = 8;
  $svgW = $pL + ($gMaxA + 1) * $cell + $pR;
  $svgH = $pT + ($gMaxH + 1) * $cell + $pB;
  $maxR = $cell / 2 - 5;

  /* Kolor bąbelka na podstawie trafności */
  $bubbleStyle = function(array $c): array {
      if ($c['exact'] > 0) {
          /* Zielony: dokładny (intensywność zależy od udziału dokładnych) */
          $op = round(min(0.95, 0.65 + ($c['exact'] / $c['total']) * 0.30), 2);
          return ['fill' => 'var(--ty-green)', 'op' => $op];
      }
      if ($c['points'] > 0) {
          /* Czarny: trafienie 1x2 */
          $op = round(min(0.88, 0.55 + ($c['points'] / $c['total']) * 0.33), 2);
          return ['fill' => 'var(--ty-accent)', 'op' => $op];
      }
      /* Szary: pudło */
      return ['fill' => 'var(--bs-secondary-color)', 'op' => 0.18];
  };
?>

<p class="section-label mt-4 mb-1">Mapa typów</p>
<p style="font-size:12px;color:var(--bs-secondary-color);margin-bottom:8px;">
  Rozmiar bąbelka = ile razy wytypowałeś ten wynik · Kolor = efekt
</p>
<div class="card match-card mb-3">
  <div class="card-body px-2 py-3">

  <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>"
       style="width:100%;max-width:480px;display:block;margin:0 auto;"
       aria-label="Mapa wytypowanych wyników">

    <!-- Siatka -->
    <?php for ($h = 0; $h <= $gMaxH; $h++): ?>
      <line x1="<?= $pL ?>" y1="<?= $pT + ($gMaxH - $h) * $cell + $cell/2 ?>"
            x2="<?= $pL + ($gMaxA + 1) * $cell ?>"
            y2="<?= $pT + ($gMaxH - $h) * $cell + $cell/2 ?>"
            stroke="var(--bs-border-color)" stroke-width="0.5"/>
    <?php endfor; ?>
    <?php for ($a = 0; $a <= $gMaxA; $a++): ?>
      <line x1="<?= $pL + $a * $cell + $cell/2 ?>" y1="<?= $pT ?>"
            x2="<?= $pL + $a * $cell + $cell/2 ?>" y2="<?= $pT + ($gMaxH + 1) * $cell ?>"
            stroke="var(--bs-border-color)" stroke-width="0.5"/>
    <?php endfor; ?>

    <!-- Wykresy kołowe typów -->
    <?php
    $pieSlice = function(float $cx, float $cy, float $r, float $startDeg, float $endDeg, string $fill, float $op): string {
        if ($endDeg - $startDeg >= 359.9) {
            return "<circle cx=\"$cx\" cy=\"$cy\" r=\"$r\" fill=\"$fill\" fill-opacity=\"$op\"/>";
        }
        $s = deg2rad($startDeg - 90);
        $e = deg2rad($endDeg - 90);
        $x1 = round($cx + $r * cos($s), 2); $y1 = round($cy + $r * sin($s), 2);
        $x2 = round($cx + $r * cos($e), 2); $y2 = round($cy + $r * sin($e), 2);
        $large = ($endDeg - $startDeg > 180) ? 1 : 0;
        return "<path d=\"M $cx $cy L $x1 $y1 A $r $r 0 $large 1 $x2 $y2 Z\" fill=\"$fill\" fill-opacity=\"$op\"/>";
    };
    foreach ($typGrid as $h => $aArr): foreach ($aArr as $a => $cellData):
      if ($h > $gMaxH || $a > $gMaxA) continue;
      $cx  = $pL + $a * $cell + $cell/2;
      $cy  = $pT + ($gMaxH - $h) * $cell + $cell/2;
      $r   = max(6, round(($cellData['total'] / $maxCnt) * $maxR, 1));
      $lbl = $cellData['total'] . '×';

      $exactDeg     = ($cellData['total'] > 0) ? ($cellData['exact'] / $cellData['total']) * 360 : 0;
      $kierDeg      = ($cellData['total'] > 0) ? (max(0, $cellData['points'] - $cellData['exact']) / $cellData['total']) * 360 : 0;
      $pudlaDeg     = 360 - $exactDeg - $kierDeg;

      $a0 = 0; $a1 = $a0 + $exactDeg; $a2 = $a1 + $kierDeg;
    ?>
      <!-- Tooltip -->
      <title><?= $h ?>:<?= $a ?> – typowane <?= $cellData['total'] ?>×<?php
        if ($cellData['exact'])  echo ', dokładnie: '    . $cellData['exact']  . '×';
        if ($cellData['points']) echo ', kierunkowych: ' . max(0,$cellData['points']-$cellData['exact']) . '×';
        $pudla = $cellData['total'] - $cellData['points'];
        if ($pudla > 0) echo ', pudła: ' . $pudla . '×';
      ?></title>
      <!-- Szary spód (pudła) jako pełne koło -->
      <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>"
              fill="var(--bs-secondary-color)" fill-opacity="0.25"/>
      <!-- Wycinek kierunkowy (czarny) -->
      <?php if ($kierDeg > 0.5) echo $pieSlice($cx, $cy, $r, $a0, $a0+$kierDeg, 'var(--ty-accent)', 0.80); ?>
      <!-- Wycinek dokładny (zielony) – rysowany na wierzchu -->
      <?php if ($exactDeg > 0.5) echo $pieSlice($cx, $cy, $r, $a0+$kierDeg, $a0+$kierDeg+$exactDeg, 'var(--ty-green)', 0.90); ?>

      <?php if ($r >= 11): ?>
      <text x="<?= $cx ?>" y="<?= $cy - 3 ?>"
            text-anchor="middle"
            style="font-family:'Bebas Neue',sans-serif;font-size:<?= min(16, (int)($r * 0.85)) ?>px;fill:white;pointer-events:none;">
        <?= $lbl ?>
      </text>
      <text x="<?= $cx ?>" y="<?= $cy + 10 ?>"
            text-anchor="middle"
            style="font-size:<?= min(10, (int)($r * 0.55)) ?>px;fill:white;fill-opacity:0.75;pointer-events:none;">
        <?= $h ?>:<?= $a ?>
      </text>
      <?php endif; ?>

    <?php endforeach; endforeach; ?>

    <!-- Oś X: bramki gości -->
    <?php for ($a = 0; $a <= $gMaxA; $a++): ?>
      <text x="<?= $pL + $a * $cell + $cell/2 ?>"
            y="<?= $pT + ($gMaxH + 1) * $cell + 16 ?>"
            text-anchor="middle"
            style="font-size:11px;fill:var(--bs-secondary-color);"><?= $a ?></text>
    <?php endfor; ?>
    <text x="<?= $pL + ($gMaxA + 1) * $cell / 2 ?>"
          y="<?= $svgH - 4 ?>"
          text-anchor="middle"
          style="font-size:10px;fill:var(--bs-secondary-color);">bramki gości →</text>

    <!-- Oś Y: bramki gospodarza -->
    <?php for ($h = 0; $h <= $gMaxH; $h++): ?>
      <text x="<?= $pL - 5 ?>"
            y="<?= $pT + ($gMaxH - $h) * $cell + $cell/2 + 4 ?>"
            text-anchor="end"
            style="font-size:11px;fill:var(--bs-secondary-color);"><?= $h ?></text>
    <?php endfor; ?>
    <text transform="rotate(-90,<?= $pL - 20 ?>,<?= $pT + ($gMaxH+1)*$cell/2 ?>)"
          x="<?= $pL - 20 ?>" y="<?= $pT + ($gMaxH+1)*$cell/2 + 4 ?>"
          text-anchor="middle"
          style="font-size:10px;fill:var(--bs-secondary-color);">bramki gospodarza →</text>

  </svg>

  <!-- Legenda -->
  <div class="d-flex gap-3 flex-wrap mt-2 px-1" style="font-size:11px;color:var(--bs-secondary-color);">
    <span>
      <svg width="12" height="12" style="vertical-align:middle;">
        <path d="M6,6 L6,1 A5,5 0 0,1 11,6 Z" fill="var(--ty-green)" fill-opacity="0.9"/>
      </svg>
      Dokładne (zielony wycinek)
    </span>
    <span>
      <svg width="12" height="12" style="vertical-align:middle;">
        <path d="M6,6 L6,1 A5,5 0 0,1 11,6 Z" fill="var(--ty-accent)" fill-opacity="0.8"/>
      </svg>
      Trafienie 1x2 (czarny)
    </span>
    <span>
      <svg width="12" height="12" style="vertical-align:middle;">
        <circle cx="6" cy="6" r="5" fill="var(--bs-secondary-color)" fill-opacity="0.25"/>
      </svg>
      Pudła (szary)
    </span>
    <span class="ms-auto d-flex align-items-center gap-2">
      <svg width="16" height="16"><circle cx="8" cy="8" r="4" fill="var(--bs-secondary-color)" fill-opacity="0.4"/></svg>1×
      <svg width="24" height="24"><circle cx="12" cy="12" r="10" fill="var(--bs-secondary-color)" fill-opacity="0.4"/></svg><?= $maxCnt ?>×
    </span>
  </div>

  </div>
</div>
<?php endif; ?>


