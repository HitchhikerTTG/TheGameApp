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
<p class="section-label mt-4 mb-2">Mapa wyników i typów</p>
<div class="card match-card mb-2">
  <div class="card-body px-3 py-3">

    <div class="d-flex justify-content-between align-items-center mb-2">
      <div style="font-size:11px;color:var(--bs-secondary-color);">
        Oś X: bramki gospodarza &nbsp;·&nbsp; Oś Y: bramki gości
      </div>
      <button class="btn btn-sm btn-outline-secondary" id="btn-typy-map"
              onclick="toggleTypyMap()">Pokaż typy graczy</button>
    </div>

    <?php
      $mW = $statystyki['mapaWynikow'] ?? [];
      $mT = $statystyki['mapaTypow']   ?? [];
      $maxH = 5; $maxA = 5;
      foreach ($mW as $h => $arr) foreach ($arr as $a => $c) { $maxH = max($maxH, min((int)$h, 7)); $maxA = max($maxA, min((int)$a, 7)); }
      foreach ($mT as $h => $arr) foreach ($arr as $a => $c) { $maxH = max($maxH, min((int)$h, 7)); $maxA = max($maxA, min((int)$a, 7)); }
      $maxFreqW = 1; foreach ($mW as $arr) foreach ($arr as $c) $maxFreqW = max($maxFreqW, $c);
      $maxFreqT = 1; foreach ($mT as $arr) foreach ($arr as $c) $maxFreqT = max($maxFreqT, $c);
      $cell = 44; $pL = 22; $pB = 22;
      $svgW = ($maxH + 1) * $cell + $pL;
      $svgH = ($maxA + 1) * $cell + $pB;
      $maxR = ($cell / 2) - 5;
    ?>

    <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>" style="width:100%;max-width:400px;display:block;margin:0 auto;" aria-label="Mapa wyników">

      <?php /* Siatka + etykiety osi */ ?>
      <?php for ($h = 0; $h <= $maxH; $h++):
        $x = $pL + $h * $cell + $cell/2; ?>
        <line x1="<?= $x ?>" y1="0" x2="<?= $x ?>" y2="<?= $svgH - $pB ?>"
              stroke="var(--bs-border-color)" stroke-width="0.5"/>
        <text x="<?= $x ?>" y="<?= $svgH - 6 ?>" text-anchor="middle"
              style="font-size:10px;fill:var(--bs-secondary-color);"><?= $h ?></text>
      <?php endfor; ?>
      <?php for ($a = 0; $a <= $maxA; $a++):
        $y = ($maxA - $a) * $cell + $cell/2; ?>
        <line x1="<?= $pL ?>" y1="<?= $y ?>" x2="<?= $svgW ?>" y2="<?= $y ?>"
              stroke="var(--bs-border-color)" stroke-width="0.5"/>
        <text x="<?= $pL - 4 ?>" y="<?= $y + 4 ?>" text-anchor="end"
              style="font-size:10px;fill:var(--bs-secondary-color);"><?= $a ?></text>
      <?php endfor; ?>

      <?php /* Bąbelki wyników */ ?>
      <?php for ($h = 0; $h <= $maxH; $h++): for ($a = 0; $a <= $maxA; $a++):
        $cnt = $mW[$h][$a] ?? 0; if (!$cnt) continue;
        $cx = $pL + $h * $cell + $cell/2;
        $cy = ($maxA - $a) * $cell + $cell/2;
        $r  = max(4, round(($cnt / $maxFreqW) * $maxR, 1)); ?>
        <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>"
                fill="var(--ty-accent)" fill-opacity="0.85">
          <title><?= $h ?>:<?= $a ?> -- padło <?= $cnt ?> <?= $cnt === 1 ? 'raz' : 'razy' ?></title>
        </circle>
      <?php endfor; endfor; ?>

      <?php /* Warstwa typów graczy (ukryta domyślnie) */ ?>
      <g id="typy-map-layer" style="display:none;">
        <?php for ($h = 0; $h <= $maxH; $h++): for ($a = 0; $a <= $maxA; $a++):
          $cnt = $mT[$h][$a] ?? 0; if (!$cnt) continue;
          $cx = $pL + $h * $cell + $cell/2;
          $cy = ($maxA - $a) * $cell + $cell/2;
          $r  = max(4, round(($cnt / $maxFreqT) * $maxR, 1)); ?>
          <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>"
                  fill="none" stroke="var(--ty-red)" stroke-width="1.5" stroke-opacity="0.75">
            <title><?= $h ?>:<?= $a ?> -- wytypowano <?= $cnt ?> razy</title>
          </circle>
        <?php endfor; endfor; ?>
      </g>
    </svg>

    <div class="d-flex gap-3 mt-2" style="font-size:11px;color:var(--bs-secondary-color);">
      <span>
        <svg width="10" height="10"><circle cx="5" cy="5" r="5" fill="var(--ty-accent)" fill-opacity="0.85"/></svg>
        Rzeczywiste wyniki
      </span>
      <span id="legenda-typy" style="display:none;">
        <svg width="10" height="10"><circle cx="5" cy="5" r="5" fill="none" stroke="var(--ty-red)" stroke-width="1.5"/></svg>
        Typy graczy
      </span>
    </div>

  </div>
</div>

<script>
function toggleTypyMap() {
  var layer = document.getElementById('typy-map-layer');
  var btn   = document.getElementById('btn-typy-map');
  var leg   = document.getElementById('legenda-typy');
  var show  = layer.style.display === 'none';
  layer.style.display = show ? '' : 'none';
  leg.style.display   = show ? '' : 'none';
  btn.textContent = show ? 'Ukryj typy graczy' : 'Pokaż typy graczy';
}
</script>

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