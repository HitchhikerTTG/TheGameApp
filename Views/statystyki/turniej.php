<div class="px-3 py-4" style="max-width:680px;margin:0 auto;">

  <div class="d-flex align-items-baseline justify-content-between mb-4">
    <div>
      <p class="section-label mb-0">Statystyki</p>
      <h2 class="ff-bebas mb-0" style="font-size:28px;"><?= esc($turniejName) ?></h2>
    </div>
    <?php if (session()->get('isAdmin')): ?>
      <a href="/statystyki/przelicz" class="btn btn-sm btn-outline-secondary">↺ Przelicz</a>
    <?php endif ?>
  </div>

  <?php if (empty($statystyki)): ?>
    <p class="text-secondary">Brak danych -- statystyki zostaną obliczone po przeliczeniu pierwszych meczów.</p>
  <?php else: ?>

  <?php
    // helper lokalny
    $mecz = fn(?array $m, string $pole = 'liczba', string $suffix = '') =>
        $m ? esc($m['HomeName']) . ' – ' . esc($m['AwayName'])
           . ' <span class="text-secondary">(' . $m['ScoreHome'] . ':' . $m['ScoreAway'] . ')</span>'
           . ' <strong>' . $m[$pole] . $suffix . '</strong>'
        : '<em class="text-secondary">brak danych</em>';
  ?>

  <!-- MECZE -->
  <p class="section-label mt-4 mb-2">Mecze</p>

  <div class="card match-card mb-2">
  <div class="card-body px-3 py-3">
    <div class="stat-label">Mecz z największą liczbą typów</div>
    <?php $d = $statystyki['meczNajwiecejTypow'] ?? null; ?>
    <?php if ($d && !empty($d['mecze'])): ?>
      <div class="stat-value">
        <strong><?= (int)$d['liczba'] ?> typów</strong>
        <?= count($d['mecze']) > 1 ? ' <span class="text-secondary small">(' . count($d['mecze']) . ' mecze z takim samym wynikiem)</span>' : '' ?>
      </div>
      <?php foreach ($d['mecze'] as $m): ?>
        <div class="stat-value mt-1">
          <?= esc($m['HomeName']) ?> – <?= esc($m['AwayName']) ?>
          <span class="text-secondary small">(<?= (int)$m['ScoreHome'] ?>:<?= (int)$m['ScoreAway'] ?>)</span>
        </div>
      <?php endforeach ?>
    <?php else: ?>
      <div class="stat-value"><em class="text-secondary">brak danych</em></div>
    <?php endif ?>
  </div>
</div>

  <div class="card match-card mb-2">
  <div class="card-body px-3 py-3">
    <div class="stat-label">Mecz z największą liczbą trafień 1X2</div>
    <?php $d = $statystyki['meczNajwiecejTrafien1X2'] ?? null; ?>
    <?php if ($d && !empty($d['mecze'])): ?>
      <div class="stat-value"><strong><?= (int)$d['liczba'] ?> trafień</strong><?= count($d['mecze']) > 1 ? ' <span class="text-secondary small">(' . count($d['mecze']) . ' mecze)</span>' : '' ?></div>
      <?php foreach ($d['mecze'] as $m): ?>
        <div class="stat-value mt-1"><?= esc($m['HomeName']) ?> – <?= esc($m['AwayName']) ?> <span class="text-secondary small">(<?= (int)$m['ScoreHome'] ?>:<?= (int)$m['ScoreAway'] ?>)</span></div>
      <?php endforeach ?>
    <?php else: ?><div class="stat-value"><em class="text-secondary">brak danych</em></div><?php endif ?>
  </div>
</div>

<div class="card match-card mb-2">
  <div class="card-body px-3 py-3">
    <div class="stat-label">Mecz z największą liczbą dokładnych trafień</div>
    <?php $d = $statystyki['meczNajwiecejDokladnychTrafien'] ?? null; ?>
    <?php if ($d && !empty($d['mecze'])): ?>
      <div class="stat-value"><strong><?= (int)$d['liczba'] ?> trafień</strong><?= count($d['mecze']) > 1 ? ' <span class="text-secondary small">(' . count($d['mecze']) . ' mecze)</span>' : '' ?></div>
      <?php foreach ($d['mecze'] as $m): ?>
        <div class="stat-value mt-1"><?= esc($m['HomeName']) ?> – <?= esc($m['AwayName']) ?> <span class="text-secondary small">(<?= (int)$m['ScoreHome'] ?>:<?= (int)$m['ScoreAway'] ?>)</span></div>
      <?php endforeach ?>
    <?php else: ?><div class="stat-value"><em class="text-secondary">brak danych</em></div><?php endif ?>
  </div>
</div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Mecz który przyznał graczom najwięcej punktów łącznie</div>
      <div class="stat-value"><?= $mecz($statystyki['meczNajwiecejPkt'], 'suma', ' pkt') ?></div>
    </div>
  </div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Mecz z największą liczbą użytych złotych piłek ⚽</div>
      <div class="stat-value"><?= $mecz($statystyki['meczNajwiecejGoldenUzytych'], 'liczba', ' razy') ?></div>
    </div>
  </div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Mecz w którym trafiono najwięcej złotych piłek ⚽✓</div>
      <div class="stat-value"><?= $mecz($statystyki['meczNajwiecejGoldenTrafiony'], 'liczba', ' trafień') ?></div>
    </div>
  </div>

  <!-- TYPY -->
  <p class="section-label mt-4 mb-2">Typy graczy</p>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Najpopularniejszy trafiony wynik</div>
      <?php $t = $statystyki['najpopularniejszyTrafiony']; ?>
      <div class="stat-value">
        <?= $t
          ? '<strong class="ff-bebas" style="font-size:22px;">' . (int)$t['HomeTyp'] . ' : ' . (int)$t['AwayTyp'] . '</strong>'
            . ' <span class="text-secondary small">(' . $t['liczba'] . ' razy)</span>'
          : '<em class="text-secondary">brak danych</em>' ?>
      </div>
    </div>
  </div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Najpopularniejszy nie trafiony wynik 😬</div>
      <?php $t = $statystyki['najpopularniejszyNieTrafiony']; ?>
      <div class="stat-value">
        <?= $t
          ? '<strong class="ff-bebas" style="font-size:22px;">' . (int)$t['HomeTyp'] . ' : ' . (int)$t['AwayTyp'] . '</strong>'
            . ' <span class="text-secondary small">(' . $t['liczba'] . ' razy)</span>'
          : '<em class="text-secondary">brak danych</em>' ?>
      </div>
    </div>
  </div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Wynik który zastosowany do wszystkich meczów dałby najwięcej punktów 🤓</div>
      <?php $t = $statystyki['najskuteczniejszyWynik']; ?>
      <div class="stat-value">
        <?= $t
          ? '<strong class="ff-bebas" style="font-size:22px;">' . (int)$t['ScoreHome'] . ' : ' . (int)$t['ScoreAway'] . '</strong>'
            . ' <span class="text-secondary small">(dałby łącznie ' . (int)($t['totalPkt'] ?? $t['liczba'] ?? 0) . ' pkt)</span>'
          : '<em class="text-secondary">brak danych</em>' ?>
      </div>
    </div>
  </div>
<div class="card match-card mb-2">
  <div class="card-body px-3 py-3">
    <div class="stat-label">Typ wpisywany najczęściej ✍️</div>
    <?php $t = $statystyki['typNajczesciejOddawany'] ?? null; ?>
    <div class="stat-value">
      <?= $t
        ? '<strong class="ff-bebas" style="font-size:22px;">' . (int)$t['HomeTyp'] . ' : ' . (int)$t['AwayTyp'] . '</strong>'
          . ' <span class="text-secondary small">(' . (int)$t['ile'] . ' razy, łącznie ' . (int)$t['sumapt'] . ' pkt)</span>'
        : '<em class="text-secondary">brak danych</em>' ?>
    </div>
  </div>
</div>

<div class="card match-card mb-2">
  <div class="card-body px-3 py-3">
    <div class="stat-label">Typ który dał graczom najwięcej punktów 💰</div>
    <?php $t = $statystyki['typNajwiecejPkt'] ?? null; ?>
    <div class="stat-value">
      <?= $t
        ? '<strong class="ff-bebas" style="font-size:22px;">' . (int)$t['HomeTyp'] . ' : ' . (int)$t['AwayTyp'] . '</strong>'
          . ' <span class="text-secondary small">(' . (int)$t['sumapt'] . ' pkt łącznie, wpisywany ' . (int)$t['ile'] . ' razy)</span>'
        : '<em class="text-secondary">brak danych</em>' ?>
    </div>
  </div>
</div>

<!-- MAPA WYNIKÓW I TYPÓW -->
<?php if (!empty($statystyki['mapaWynikow'])): ?>
<p class="section-label mt-4 mb-2">Mapa wyników</p>
<p style="font-size:12px;color:var(--bs-secondary-color);margin-top:-6px;margin-bottom:8px;">
  Kolor komórki = liczba meczów z tym wynikiem · Biała kropka = 1 typ gracza
</p>
<div class="card match-card mb-2">
  <div class="card-body px-2 py-3">

  <?php
    /* ── Dane ── */
    $mW = $statystyki['mapaWynikow'] ?? [];
    $mT = $statystyki['mapaTypow']   ?? [];

    $maxH = 6; $maxA = 6;
    foreach ($mW as $h => $arr) foreach ($arr as $a => $c) {
        $maxH = max($maxH, min((int)$h, 8));
        $maxA = max($maxA, min((int)$a, 8));
    }
    foreach ($mT as $h => $arr) foreach ($arr as $a => $c) {
        $maxH = max($maxH, min((int)$h, 8));
        $maxA = max($maxA, min((int)$a, 8));
    }

    $maxFreqW = 1;
    foreach ($mW as $arr) foreach ($arr as $c) $maxFreqW = max($maxFreqW, $c);

    /* ── Wymiary SVG ── */
    $cell = 50; $pL = 28; $pT = 8; $pB = 38; $pR = 8;
    $svgW = $pL + ($maxA + 1) * $cell + $pR;
    $svgH = $pT + ($maxH + 1) * $cell + $pB;
    $minDim = min($maxH, $maxA);

    /* ── Kolor komórki (dark-teal → zieleń → żółty → pomarańcz → czerwony) ── */
    $heatColor = function(int $cnt, int $maxCnt): string {
        if ($cnt === 0) return '#ebebeb';
        $r = $cnt / $maxCnt;
        $stops = [
            [0.00, [0x2d, 0x4a, 0x3e]],
            [0.20, [0x2f, 0x70, 0x4a]],
            [0.45, [0x78, 0xc4, 0x4a]],
            [0.72, [0xe8, 0xa8, 0x38]],
            [1.00, [0xc0, 0x39, 0x2b]],
        ];
        for ($i = 1; $i < count($stops); $i++) {
            if ($r <= $stops[$i][0] || $i === count($stops) - 1) {
                $t = min(1, max(0,
                    ($r - $stops[$i-1][0]) / max(0.001, $stops[$i][0] - $stops[$i-1][0])
                ));
                return sprintf('#%02x%02x%02x',
                    (int)($stops[$i-1][1][0] + ($stops[$i][1][0] - $stops[$i-1][1][0]) * $t),
                    (int)($stops[$i-1][1][1] + ($stops[$i][1][1] - $stops[$i-1][1][1]) * $t),
                    (int)($stops[$i-1][1][2] + ($stops[$i][1][2] - $stops[$i-1][1][2]) * $t)
                );
            }
        }
        return '#c0392b';
    };

    /* ── Kolor tekstu na tle komórki ── */
    $textOnBg = function(string $hex): string {
        $r = hexdec(substr($hex, 1, 2));
        $g = hexdec(substr($hex, 3, 2));
        $b = hexdec(substr($hex, 5, 2));
        return ((0.299*$r + 0.587*$g + 0.114*$b) / 255 > 0.52) ? '#333333' : '#ffffff';
    };

    /* ── Deterministyczne pozycje kropek ── */
    $dotPositions = function(int $h, int $a, int $count, float $cX, float $cY, float $sz): array {
        $pad = 6; $inner = $sz - $pad * 2; $out = [];
        for ($d = 0; $d < min($count, 60); $d++) {
            $s1 = abs(($h * 7919 + $a * 3571 + $d * 1009 + 42) % 100000);
            $s2 = abs(($s1 * 6364 + $h * 17 + $a) % 100000);
            $out[] = [
                round($cX + $pad + ($s1 % 1000) / 999.0 * $inner, 1),
                round($cY + $pad + ($s2 % 1000) / 999.0 * $inner, 1),
            ];
        }
        return $out;
    };
  ?>

  <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>"
       style="width:100%;max-width:520px;display:block;margin:0 auto;"
       aria-label="Heatmapa wyników meczów">
    <defs>
      <linearGradient id="heatLegendGrad" x1="0" y1="0" x2="1" y2="0">
        <stop offset="0%"   stop-color="#2d4a3e"/>
        <stop offset="20%"  stop-color="#2f704a"/>
        <stop offset="45%"  stop-color="#78c44a"/>
        <stop offset="72%"  stop-color="#e8a838"/>
        <stop offset="100%" stop-color="#c0392b"/>
      </linearGradient>
    </defs>

    <!-- Komórki heatmapy -->
    <?php for ($h = 0; $h <= $maxH; $h++): for ($a = 0; $a <= $maxA; $a++):
      $cnt     = $mW[$h][$a] ?? 0;
      $cX      = $pL + $a * $cell;
      $cY      = $pT + ($maxH - $h) * $cell;
      $bg      = $heatColor($cnt, $maxFreqW);
      $txtCol  = $textOnBg($bg);
    ?>
      <rect x="<?= $cX ?>" y="<?= $cY ?>"
            width="<?= $cell ?>" height="<?= $cell ?>"
            fill="<?= $bg ?>" stroke="white" stroke-width="0.8" stroke-opacity="0.25"/>

      <?php if ($cnt > 0): ?>
      <!-- Liczba wyników -- wyśrodkowana, duża, czytelna -->
      <text x="<?= $cX + $cell/2 ?>" y="<?= $cY + $cell/2 + 6 ?>"
            text-anchor="middle"
            style="font-family:'Bebas Neue',sans-serif;font-size:18px;fill:<?= $txtCol ?>;fill-opacity:0.9;">
        <?= $cnt ?>
      </text>
      <?php endif; ?>
    <?php endfor; endfor; ?>

    <!-- Białe kropki = typy graczy (ukryte domyślnie) -->
    <g id="typy-heat-layer" style="display:none;">
    <?php for ($h = 0; $h <= $maxH; $h++): for ($a = 0; $a <= $maxA; $a++):
      $typCnt = $mT[$h][$a] ?? 0;
      if ($typCnt === 0) continue;
      $cX     = $pL + $a * $cell;
      $cY     = $pT + ($maxH - $h) * $cell;
      $bg     = $heatColor($mW[$h][$a] ?? 0, $maxFreqW);
      $dCol   = $textOnBg($bg);
      foreach ($dotPositions($h, $a, $typCnt, $cX, $cY, $cell) as $p):
    ?>
      <circle cx="<?= $p[0] ?>" cy="<?= $p[1] ?>" r="2.5"
              fill="<?= $dCol ?>" fill-opacity="0.75"/>
    <?php endforeach; endfor; endfor; ?>
    </g>

    <!-- Linia diagonalna (remis: H = A) -->
    <line x1="<?= $pL ?>"
          y1="<?= $pT + ($maxH + 1) * $cell ?>"
          x2="<?= $pL + ($minDim + 1) * $cell ?>"
          y2="<?= $pT + ($maxH - $minDim) * $cell ?>"
          stroke="white" stroke-width="1.5" stroke-dasharray="5 3" stroke-opacity="0.55"/>

    <!-- Etykiety stref -->
    <text x="<?= $pL + 5 ?>" y="<?= $pT + 16 ?>"
          style="font-size:9px;fill:white;fill-opacity:0.5;font-weight:700;letter-spacing:.06em;">
      GOSPOD. GÓRĄ ↗
    </text>
    <text x="<?= $pL + ($maxA) * $cell + $cell - 4 ?>"
          y="<?= $pT + ($maxH) * $cell + $cell - 5 ?>"
          text-anchor="end"
          style="font-size:9px;fill:white;fill-opacity:0.4;font-weight:700;letter-spacing:.06em;">
      ↙ GOŚCIE GÓRĄ
    </text>

    <!-- Oś X: bramki gości -->
    <?php for ($a = 0; $a <= $maxA; $a++): ?>
      <text x="<?= $pL + $a * $cell + $cell/2 ?>"
            y="<?= $pT + ($maxH + 1) * $cell + 16 ?>"
            text-anchor="middle"
            style="font-size:11px;fill:var(--bs-secondary-color);"><?= $a ?></text>
    <?php endfor; ?>
    <text x="<?= $pL + ($maxA + 1) * $cell / 2 ?>"
          y="<?= $svgH - 4 ?>"
          text-anchor="middle"
          style="font-size:10px;fill:var(--bs-secondary-color);">bramki gości →</text>

    <!-- Oś Y: bramki gospodarza -->
    <?php for ($h = 0; $h <= $maxH; $h++): ?>
      <text x="<?= $pL - 5 ?>"
            y="<?= $pT + ($maxH - $h) * $cell + $cell/2 + 4 ?>"
            text-anchor="end"
            style="font-size:11px;fill:var(--bs-secondary-color);"><?= $h ?></text>
    <?php endfor; ?>
    <text transform="rotate(-90,<?= $pL - 20 ?>,<?= $pT + ($maxH+1)*$cell/2 ?>)"
          x="<?= $pL - 20 ?>" y="<?= $pT + ($maxH+1)*$cell/2 + 4 ?>"
          text-anchor="middle"
          style="font-size:10px;fill:var(--bs-secondary-color);">↑ bramki gospodarza</text>

  </svg>

  <!-- Sterowanie i legenda -->
  <div class="d-flex align-items-center flex-wrap gap-3 mt-3 px-1" style="font-size:12px;">
    <label class="d-flex align-items-center gap-1" style="cursor:pointer;user-select:none;">
      <input type="checkbox" id="chk-typy-heat" onchange="toggleHeatDots(this.checked)">
      <span>Pokaż typy graczy</span>
    </label>
    <div class="d-flex align-items-center gap-1 ms-auto">
      <svg width="100" height="10">
        <rect width="100" height="10" fill="url(#heatLegendGrad)" rx="2"/>
      </svg>
      <span style="color:var(--bs-secondary-color);">0 → max meczów</span>
    </div>
    <span style="color:var(--bs-secondary-color);">
      <svg width="8" height="8"><circle cx="4" cy="4" r="4" fill="var(--bs-secondary-color)"/></svg>
      1 typ gracza
    </span>
  </div>

  </div>
</div>

<script>
function toggleHeatDots(show) {
  document.getElementById('typy-heat-layer').style.display = show ? '' : 'none';
}
</script>
<?php endif; ?>

<!-- MAPA TYPÓW GRACZY Z TRAFIENIAMI -->
<p class="section-label mt-4 mb-2">Mapa typów graczy – skuteczność</p>
<p style="font-size:12px;color:var(--bs-secondary-color);margin-top:-6px;margin-bottom:8px;">
  Każdy punkt = jeden oddany typ · Zielony = dokładny, czarny = trafienie 1x2, szary = pudło
</p>

<?php if (!empty($statystyki['mapaTypowTrafienia'])): 
  $mTT = $statystyki['mapaTypowTrafienia'];
  
  $maxHT = 5; $maxAT = 5;
  $maxTotal = 1;
  foreach ($mTT as $h => $arr) foreach ($arr as $a => $c) {
      $maxHT    = max($maxHT, min((int)$h, 8));
      $maxAT    = max($maxAT, min((int)$a, 8));
      $maxTotal = max($maxTotal, $c['total']);
  }

  // Rozmiar komórki proporcjonalny do sqrt(total) – max komórka = $cellMax
  $cellMax = 90;  // px – rozmiar komórki z najczęściej typoawnym wynikiem
  $cellMin = 25;  // px – minimalna komórka (żeby nie znikała)
  $dotR    = 2.5; // stały promień każdego punktu
  $labelH  = 18;

  // Oblicz rozmiary komórek i pozycje w siatce
  // Najpierw oblicz szerokości kolumn i wysokości wierszy jako max w danej kolumnie/wierszu
  $colW = []; $rowH = [];
  for ($a = 0; $a <= $maxAT; $a++) $colW[$a] = $cellMin;
  for ($h = 0; $h <= $maxHT; $h++) $rowH[$h] = $cellMin;
  foreach ($mTT as $h => $arr) foreach ($arr as $a => $c) {
      if ($h > $maxHT || $a > $maxAT) continue;
      $sz = (int)round($cellMin + (sqrt($c['total']) / sqrt($maxTotal)) * ($cellMax - $cellMin));
      $colW[$a] = max($colW[$a], $sz);
      $rowH[$h] = max($rowH[$h], $sz+$labelH);
  }

  // Pozycje X kolumn i Y wierszy (kumulatywne)
  $pL = 28; $pT = 8; $pB = 38; $pR = 8;
  $colX = []; $cx = $pL;
  for ($a = 0; $a <= $maxAT; $a++) { $colX[$a] = $cx; $cx += $colW[$a]; }
  $rowY = []; $cy = $pT;
  for ($h = $maxHT; $h >= 0; $h--) { $rowY[$h] = $cy; $cy += $rowH[$h]; }  // H rosnące = w górę

  $svgWT = $cx + $pR;
  $svgHT = $cy + $pB;
?>

<div class="card match-card mb-2">
  <div class="card-body px-2 py-3">
  <svg viewBox="0 0 <?= $svgWT ?> <?= $svgHT ?>"
       style="width:100%;max-width:520px;display:block;margin:0 auto;">

    <?php for ($h = $maxHT; $h >= 0; $h--): for ($a = 0; $a <= $maxAT; $a++):
      $c    = $mTT[$h][$a] ?? null;
      if (!$c || $c['total'] === 0) continue;  // ← POMIJAMY PUSTE
      $cX   = $colX[$a];
      $cY   = $rowY[$h];
      $szW  = $colW[$a];
      $szH  = $rowH[$h];
      $thisSz = (int)round($cellMin + (sqrt($c['total']) / sqrt($maxTotal)) * ($cellMax - $cellMin));
      $offX   = (int)(($szW - $thisSz) / 2);
      $offY   = $labelH + (int)(($szH - $labelH - $thisSz) / 2);  // ← kwadrat poniżej labela
      $bX     = $cX + $offX;
      $bY     = $cY + $offY;
    ?>
      <!-- Komórka -->
      <rect x="<?= $bX ?>" y="<?= $bY ?>"
            width="<?= $thisSz ?>" height="<?= $thisSz ?>"
            fill="var(--bs-body-bg)" stroke="var(--bs-border-color)" stroke-width="0.5"/>

      <?php if (true):   // zawsze prawda – $c['total'] > 0 zagwarantowane wyżej 
        $total = $c['total'];
        $exact = $c['exact'];
        $hit   = $c['hit'] - $exact;
        $miss  = $total - $c['hit'];

        // Punkty w siatce NxN, stały r=2.5
        $cols  = max(1, (int)ceil(sqrt($total)));
        $inner = $thisSz - 8;
        $step  = $cols > 1 ? $inner / ($cols - 1) : 0;
        $types = array_merge(
            array_fill(0, $exact, 'exact'),
            array_fill(0, $hit,   'hit'),
            array_fill(0, $miss,  'miss')
        );
        $dotColors  = ['exact' => 'var(--ty-green)', 'hit' => 'var(--ty-accent)', 'miss' => '#888'];
        $dotOpacity = ['exact' => '0.9',             'hit' => '0.8',              'miss' => '0.35'];
        $drawn = 0;
        for ($row = 0; $row < $cols && $drawn < $total; $row++):
          for ($col = 0; $col < $cols && $drawn < $total; $col++):
            $dx = $bX + 4 + ($cols > 1 ? $col * $step : $inner / 2);
            $dy = $bY + 4 + ($cols > 1 ? $row * $step : $inner / 2);
            $dt = $types[$drawn]; $drawn++;
      ?>
          <circle cx="<?= round($dx,1) ?>" cy="<?= round($dy,1) ?>" r="<?= $dotR ?>"
                  fill="<?= $dotColors[$dt] ?>" fill-opacity="<?= $dotOpacity[$dt] ?>"
                  data-dot-type="<?= $dt ?>"/>
      <?php   endfor; endfor; ?>

      <!-- Nagłówek wyniku – nad kwadratem, w dedykowanym obszarze $labelH -->
        <text x="<?= $cX + $szW/2 ?>" y="<?= $cY + $labelH - 6 ?>"
              text-anchor="middle"
              style="font-family:'Bebas Neue',sans-serif;font-size:14px;fill:var(--bs-body-color);fill-opacity:0.85;pointer-events:none;">
          <?= $h ?>:<?= $a ?>
        </text>
      <?php endif ?>
    <?php endfor; endfor ?>

    <!-- Oś X -->
    <?php for ($a = 0; $a <= $maxAT; $a++): ?>
      <text x="<?= $colX[$a] + $colW[$a]/2 ?>"
            y="<?= $pT + $svgHT - $pB + 16 ?>"
            text-anchor="middle" style="font-size:11px;fill:var(--bs-secondary-color);"><?= $a ?></text>
    <?php endfor ?>
    <text x="<?= ($svgWT - $pL) / 2 + $pL ?>"
          y="<?= $svgHT - 4 ?>" text-anchor="middle"
          style="font-size:10px;fill:var(--bs-secondary-color);">bramki gości →</text>

    <!-- Oś Y -->
    <?php for ($h = 0; $h <= $maxHT; $h++): ?>
      <text x="<?= $pL - 5 ?>"
            y="<?= $rowY[$h] + $rowH[$h]/2 + 4 ?>"
            text-anchor="end" style="font-size:11px;fill:var(--bs-secondary-color);"><?= $h ?></text>
    <?php endfor ?>
    <text transform="rotate(-90,<?= $pL - 20 ?>,<?= $pT + ($svgHT - $pT - $pB)/2 ?>)"
          x="<?= $pL - 20 ?>" y="<?= $pT + ($svgHT - $pT - $pB)/2 + 4 ?>"
          text-anchor="middle" style="font-size:10px;fill:var(--bs-secondary-color);">bramki gospodarza →</text>
  </svg>

  <!-- Legenda -->
    <div class="d-flex gap-3 flex-wrap mt-2 px-1" style="font-size:11px;color:var(--bs-secondary-color);">
    <span><svg width="8" height="8"><circle cx="4" cy="4" r="4" fill="var(--ty-green)" opacity="0.9"/></svg> Dokładne</span>
    <span><svg width="8" height="8"><circle cx="4" cy="4" r="4" fill="var(--ty-accent)" opacity="0.8"/></svg> Kierunkowe</span>
    <span><svg width="8" height="8"><circle cx="4" cy="4" r="4" fill="#888" opacity="0.35"/></svg> Pudło</span>
    <span class="ms-auto">Każdy punkt = 1 typ</span>
  </div>
  </div>
</div>
<?php endif ?>


<div class="d-flex gap-2 mb-2 flex-wrap" id="dot-filter-btns" style="font-size:12px;">
  <button class="btn btn-sm btn-outline-secondary active" onclick="filterMapaDots('all',this)">Wszystkie</button>
  <button class="btn btn-sm btn-outline-success" onclick="filterMapaDots('exact',this)">Tylko dokładne</button>
  <button class="btn btn-sm btn-outline-primary" onclick="filterMapaDots('hit',this)">Trafienia</button>
  <button class="btn btn-sm btn-outline-secondary" onclick="filterMapaDots('miss',this)">Pudła</button>
</div>
<div class="d-flex gap-2 mb-2 align-items-center flex-wrap" style="font-size:12px;">
</div>

  <!-- ROZKŁAD TRAFIEŃ -->
  <p class="section-label mt-4 mb-2">Rozkład trafień (ile graczy zdobyło punkty)</p>
  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <?php
        $r = $statystyki['rozkładTrafien'];
        $max = max(array_keys($r) ?: [0]);
      ?>
      <div class="d-flex gap-3 flex-wrap">
        <div class="text-center">
          <div class="ff-bebas" style="font-size:28px;color:var(--ty-accent);"><?= $r[0] ?? 0 ?></div>
          <div style="font-size:12px;color:var(--bs-secondary-color);">meczów bez trafienia</div>
        </div>
        <div class="text-center">
          <div class="ff-bebas" style="font-size:28px;color:var(--ty-green);"><?= $r[1] ?? 0 ?></div>
          <div style="font-size:12px;color:var(--bs-secondary-color);">meczów z 1 trafieniem</div>
        </div>
        <div class="text-center">
          <div class="ff-bebas" style="font-size:28px;color:var(--ty-green);"><?= $r[2] ?? 0 ?></div>
          <div style="font-size:12px;color:var(--bs-secondary-color);">meczów z 2 trafieniami</div>
        </div>
        <?php if ($max > 2): ?>
        <div class="text-center">
          <div class="ff-bebas" style="font-size:28px;color:var(--ty-green);">
            <?= array_sum(array_filter($r, fn($k) => $k > 2, ARRAY_FILTER_USE_KEY)) ?>
          </div>
          <div style="font-size:12px;color:var(--bs-secondary-color);">meczów z 3+ trafieniami</div>
        </div>
        <?php endif ?>
      </div>
    </div>
  </div>

  <!-- PYTANIA -->
  <?php if (!empty($statystyki['pytanieNajwiecejPoprawnych']) || !empty($statystyki['pytanieNajmniejPoprawnych'])): ?>
  <p class="section-label mt-4 mb-2">Pytania</p>

  <?php if (!empty($statystyki['pytanieNajwiecejPoprawnych'])): $p = $statystyki['pytanieNajwiecejPoprawnych']; ?>
  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Pytanie na które odpowiedziało poprawnie najwięcej graczy 🧠</div>
      <div class="stat-value mt-1"><?= esc($p['tresc']) ?></div>
      <div class="mt-1" style="font-size:13px;">
        Odpowiedź: <strong><?= esc($p['odpowiedz'] ?? '–') ?></strong>
        · <span class="text-secondary"><?= (int)$p['poprawnych'] ?> / <?= (int)$p['wszystkich'] ?> graczy</span>
      </div>
    </div>
  </div>
  <?php endif ?>

  <?php if (!empty($statystyki['pytanieNajmniejPoprawnych'])): $p = $statystyki['pytanieNajmniejPoprawnych']; ?>
  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Pytanie które zaskoczyło graczy najbardziej 🤯</div>
      <div class="stat-value mt-1"><?= esc($p['tresc']) ?></div>
      <div class="mt-1" style="font-size:13px;">
        Odpowiedź: <strong><?= esc($p['odpowiedz'] ?? '–') ?></strong>
        · <span class="text-secondary"><?= (int)$p['poprawnych'] ?> / <?= (int)$p['wszystkich'] ?> graczy</span>
      </div>
    </div>
  </div>
  <?php endif ?>
  <?php endif ?>

  <p class="text-secondary mt-4" style="font-size:12px;">
    Ostatnia aktualizacja: <?= esc($statystyki['obliczoneAt'] ?? '–') ?>
  </p>

  <?php endif ?>

</div>

<style>
.stat-label { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);margin-bottom:4px; }
.stat-value { font-size:15px; }
</style>
<script>
function filterMapaDots(type, btn) {
  document.querySelectorAll('[data-dot-type]').forEach(function(el) {
    el.style.display = (type === 'all' || el.dataset.dotType === type) ? '' : 'none';
  });
  document.querySelectorAll('#dot-filter-btns button').forEach(function(b) {
    b.classList.remove('active');
  });
  if (btn) btn.classList.add('active');
}

</script>