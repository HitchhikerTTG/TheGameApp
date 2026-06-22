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
        $w = 300; $h = 60; $n = count($trendPunktowy);
        $points = [];
        foreach ($trendPunktowy as $i => $v) {
            $x = $n > 1 ? ($i / ($n - 1)) * $w : 0;
            $y = $h - ($v / $max) * $h;
            $points[] = round($x, 1) . ',' . round($y, 1);
        }
      ?>
      <svg viewBox="0 0 <?= $w ?> <?= $h ?>" style="width:100%;height:60px;" preserveAspectRatio="none">
        <polyline points="<?= esc(implode(' ', $points), 'attr') ?>" fill="none" stroke="var(--ty-accent)" stroke-width="2"/>
      </svg>
    </div>
  </div>
  <?php endif ?>
  
  <!-- MAPA TYPÓW GRACZA -->
<?php
  // Budujemy siatkę: [homeTyp][awayTyp] => ['total'=>N, 'points'=>N, 'exact'=>N]
  $typGrid = [];
  foreach ($szczegolyMeczow as $m) {
      if ($m['HomeTyp'] === null || $m['AwayTyp'] === null) continue;
      $h = (int)$m['HomeTyp']; $a = (int)$m['AwayTyp'];
      if (!isset($typGrid[$h][$a])) {
          $typGrid[$h][$a] = ['total' => 0, 'points' => 0, 'exact' => 0];
      }
      $typGrid[$h][$a]['total']++;
      if ((int)$m['pkt'] > 0) $typGrid[$h][$a]['points']++;
      if ((int)$m['pkt'] >= 3) $typGrid[$h][$a]['exact']++;
  }
?>
<?php if (!empty($typGrid)): ?>
<?php
  $maxCnt = 1;
  foreach ($typGrid as $hArr) foreach ($hArr as $cell) $maxCnt = max($maxCnt, $cell['total']);
  $maxH = 5; $maxA = 5;
  foreach ($typGrid as $h => $arr) foreach ($arr as $a => $c) {
      $maxH = max($maxH, min((int)$h, 8)); $maxA = max($maxA, min((int)$a, 8));
  }
  $cell = 42; $pL = 22; $pB = 22;
  $svgW = ($maxH + 1) * $cell + $pL;
  $svgH = ($maxA + 1) * $cell + $pB;
  $maxR = ($cell / 2) - 5;
?>
<p class="section-label mt-4 mb-2">Mapa typów</p>
<div class="card match-card mb-3">
  <div class="card-body px-3 py-3">
    <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>" style="width:100%;max-width:360px;display:block;margin:0 auto;" aria-label="Mapa typów gracza">

      <?php /* Siatka + etykiety */ ?>
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

      <?php /* Bąbelki typów */ ?>
      <?php foreach ($typGrid as $h => $aArr): foreach ($aArr as $a => $cellData):
        $cx = $pL + $h * $cell + $cell/2;
        $cy = ($maxA - $a) * $cell + $cell/2;
        $r  = max(5, round(($cellData['total'] / $maxCnt) * $maxR, 1));
        if ($cellData['exact'] > 0) {
            $fill = 'var(--ty-green)'; $op = '0.9';
        } elseif ($cellData['points'] > 0) {
            $fill = 'var(--ty-accent)'; $op = '0.75';
        } else {
            $fill = 'var(--bs-secondary-color)'; $op = '0.35';
        }
        $title = $h . ':' . $a . ' -- typowane ' . $cellData['total'] . ' razy';
        if ($cellData['exact'] > 0) $title .= ' (' . $cellData['exact'] . '× dokładnie)';
        elseif ($cellData['points'] > 0) $title .= ' (' . $cellData['points'] . '× trafiony kierunek)';
      ?>
        <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>"
                fill="<?= $fill ?>" fill-opacity="<?= $op ?>">
          <title><?= esc($title) ?></title>
        </circle>
        <?php if ($cellData['exact'] > 0 && $r >= 8): ?>
        <text x="<?= $cx ?>" y="<?= $cy + 4 ?>" text-anchor="middle"
              style="font-size:<?= min(12, (int)$r) ?>px;fill:white;pointer-events:none;font-weight:700;">✓</text>
        <?php endif; ?>
      <?php endforeach; endforeach; ?>

    </svg>

    <div class="d-flex gap-3 mt-2" style="font-size:11px;color:var(--bs-secondary-color);">
      <span><svg width="10" height="10"><circle cx="5" cy="5" r="5" fill="var(--ty-green)" fill-opacity="0.9"/></svg> Dokładny ✓</span>
      <span><svg width="10" height="10"><circle cx="5" cy="5" r="5" fill="var(--ty-accent)" fill-opacity="0.75"/></svg> Kierunkowy</span>
      <span><svg width="10" height="10"><circle cx="5" cy="5" r="5" fill="var(--bs-secondary-color)" fill-opacity="0.35"/></svg> Pudło</span>
    </div>
  </div>
</div>
<?php endif; ?>


