<div class="px-3 py-4" style="max-width:680px;margin:0 auto;">

  <div class="d-flex align-items-baseline justify-content-between mb-4">
    <div>
      <p class="section-label mb-0">Statystyki</p>
      <h2 class="ff-bebas mb-0" style="font-size:28px;">#Pojedynek · <?= esc($turniejName) ?></h2>
    </div>
    <a href="/statystyki" class="btn btn-sm btn-outline-secondary">← Statystyki</a>
  </div>

  <!-- FORMULARZ WYBORU GRACZY -->
  <form method="get" action="/statystyki/pojedynek" class="card match-card mb-4">
    <div class="card-body px-3 py-3">
      <div class="d-grid gap-2" style="grid-template-columns:1fr 1fr;">
        <div>
          <div class="stat-label mb-1">Gracz 1</div>
          <select name="g1" class="form-select form-select-sm">
            <option value="">-- wybierz --</option>
            <?php foreach ($gracze as $g): ?>
              <option value="<?= esc($g['slug']) ?>"
                      <?= ($g['slug'] === ($slug1 ?? '')) ? 'selected' : '' ?>>
                <?= !empty($g['emoji']) ? esc($g['emoji']) . ' ' : '' ?><?= esc($g['nick']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <div class="stat-label mb-1">Gracz 2</div>
          <select name="g2" class="form-select form-select-sm">
            <option value="">-- wybierz --</option>
            <?php foreach ($gracze as $g): ?>
              <option value="<?= esc($g['slug']) ?>"
                      <?= ($g['slug'] === ($slug2 ?? '')) ? 'selected' : '' ?>>
                <?= !empty($g['emoji']) ? esc($g['emoji']) . ' ' : '' ?><?= esc($g['nick']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <button type="submit" class="btn btn-sm btn-outline-secondary mt-2 w-100">Porównaj</button>
    </div>
  </form>

  <?php if ($gracz1 && $gracz2 && !empty($porownanie)): ?>

    <?php
      $g1Total = end($porownanie)['g1Sum'];
      $g2Total = end($porownanie)['g2Sum'];
      $lider   = $g1Total > $g2Total ? $gracz1 : ($g2Total > $g1Total ? $gracz2 : null);
    ?>

  <!-- PODSUMOWANIE -->
  <div class="d-flex gap-2 mb-3">
    <div class="card match-card flex-fill">
      <div class="card-body px-3 py-3 text-center">
        <div class="stat-label"><?= !empty($gracz1['emoji']) ? esc($gracz1['emoji']) . ' ' : '' ?><?= esc($gracz1['nick']) ?></div>
        <div class="ff-bebas" style="font-size:36px;color:var(--ty-green);"><?= $g1Total ?></div>
        <div style="font-size:12px;color:var(--bs-secondary-color);">punktów</div>
      </div>
    </div>
    <div class="card match-card flex-fill">
      <div class="card-body px-3 py-3 text-center">
        <div class="stat-label"><?= !empty($gracz2['emoji']) ? esc($gracz2['emoji']) . ' ' : '' ?><?= esc($gracz2['nick']) ?></div>
        <div class="ff-bebas" style="font-size:36px;color:var(--ty-red);"><?= $g2Total ?></div>
        <div style="font-size:12px;color:var(--bs-secondary-color);">punktów</div>
      </div>
    </div>
  </div>
  
 

  <!-- WYKRES PORÓWNAWCZY SVG -->
  <?php if (count($porownanie) > 1): ?>
  <div class="card match-card mb-3">
    <div class="card-body px-3 py-3">
      <div class="stat-label mb-2">Punkty narastająco</div>
      <?php
        $n      = count($porownanie);
        $maxPkt = max(
            max(array_column($porownanie, 'g1Sum')),
            max(array_column($porownanie, 'g2Sum')),
            1
        );
        $svgW = 300; $svgH = 100; $pL = 0; $pB = 4;
        $chartW = $svgW - $pL; $chartH = $svgH - $pB;

        $pts1 = []; $pts2 = [];
        // Startujemy od 0 na lewej krawędzi
        $pts1[] = '0,' . $svgH;
        $pts2[] = '0,' . $svgH;
        foreach ($porownanie as $i => $m) {
            $x  = $pL + ($n > 1 ? ($i / ($n - 1)) * $chartW : 0);
            $y1 = $svgH - $pB - ($m['g1Sum'] / $maxPkt) * $chartH;
            $y2 = $svgH - $pB - ($m['g2Sum'] / $maxPkt) * $chartH;
            $pts1[] = round($x, 1) . ',' . round($y1, 1);
            $pts2[] = round($x, 1) . ',' . round($y2, 1);
        }
      ?>
      <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>" style="width:100%;height:100px;" preserveAspectRatio="none">
        <!-- Linia bazowa -->
        <line x1="0" y1="<?= $svgH - $pB ?>" x2="<?= $svgW ?>" y2="<?= $svgH - $pB ?>"
              stroke="var(--bs-border-color)" stroke-width="0.5"/>
        <!-- Gracz 1 -->
        <polyline points="<?= esc(implode(' ', array_slice($pts1, 1)), 'attr') ?>"
                  fill="none" stroke="var(--ty-green)" stroke-width="2.5"/>
        <!-- Gracz 2 -->
        <polyline points="<?= esc(implode(' ', array_slice($pts2, 1)), 'attr') ?>"
                  fill="none" stroke="var(--ty-red)" stroke-width="2.5"/>
      </svg>

      <!-- Legenda -->
      <div class="d-flex gap-3 mt-1" style="font-size:11px;">
        <span style="color:var(--ty-green);">── <?= esc($gracz1['nick']) ?></span>
        <span style="color:var(--ty-red);">── <?= esc($gracz2['nick']) ?></span>
      </div>
    </div>
  </div>
  <?php endif; ?>

   <!-- WYKRES ZMIAN POZYCJI -->
  <?php
    $tp1 = $trendPozycjiG1 ?? [];
    $tp2 = $trendPozycjiG2 ?? [];
    $nTp = max(count($tp1), count($tp2));
  ?>
  <?php if ($nTp > 1): ?>
  <div class="card match-card mb-3">
    <div class="card-body px-3 py-3">
      <div class="stat-label mb-2">Pozycja w tabeli (cały turniej)</div>
      <?php
        $allPoz = array_merge($tp1, $tp2);
        $minP   = min($allPoz);
        $maxP   = max($allPoz);
        $zakres = max(1, $maxP - $minP);
        $svgW = 300; $svgH = 100; $pB = 4;
        $chartH = $svgH - $pB;

        $ptsP1 = []; $ptsP2 = [];
        foreach ($tp1 as $i => $poz) {
            $x = $nTp > 1 ? ($i / ($nTp - 1)) * $svgW : 0;
            $y = (($poz - $minP) / $zakres) * $chartH;
            $ptsP1[] = round($x, 1) . ',' . round($y, 1);
        }
        foreach ($tp2 as $i => $poz) {
            $x = $nTp > 1 ? ($i / ($nTp - 1)) * $svgW : 0;
            $y = (($poz - $minP) / $zakres) * $chartH;
            $ptsP2[] = round($x, 1) . ',' . round($y, 1);
        }
      ?>
      <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>" style="width:100%;height:100px;" preserveAspectRatio="none">
        <?php if (!empty($ptsP1)): ?>
        <polyline points="<?= esc(implode(' ', $ptsP1), 'attr') ?>"
                  fill="none" stroke="var(--ty-green)" stroke-width="2.5"/>
        <?php endif ?>
        <?php if (!empty($ptsP2)): ?>
        <polyline points="<?= esc(implode(' ', $ptsP2), 'attr') ?>"
                  fill="none" stroke="var(--ty-red)" stroke-width="2.5"/>
        <?php endif ?>
      </svg>
      <div style="font-size:10px;color:var(--bs-secondary-color);margin-top:4px;">
        Im niżej na wykresie, tym lepsza pozycja (1. miejsce = dół)
      </div>
      <div class="d-flex gap-3 mt-1" style="font-size:11px;">
        <span style="color:var(--ty-green);">── <?= esc($gracz1['nick']) ?></span>
        <span style="color:var(--ty-red);">── <?= esc($gracz2['nick']) ?></span>
      </div>
    </div>
  </div>
  <?php endif ?>

  <!-- TABELA MECZ PO MECZU -->
  <p class="section-label mt-4 mb-2">Mecz po meczu</p>
  <div class="card match-card mb-2">
    <div style="overflow-x:auto;">
      <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
          <tr style="font-size:10px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);border-bottom:1px solid var(--bs-border-color);">
            <th style="padding:8px 12px;text-align:left;font-weight:700;">Mecz</th>
            <th style="padding:8px 6px;text-align:center;font-weight:700;">Wynik</th>
            <th style="padding:8px 6px;text-align:center;font-weight:700;color:var(--ty-green);"><?= esc($gracz1['nick']) ?></th>
            <th style="padding:8px 6px;text-align:center;font-weight:700;color:var(--ty-red);"><?= esc($gracz2['nick']) ?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($porownanie as $m): ?>
          <tr style="border-bottom:1px solid var(--bs-border-color);">
            <td style="padding:8px 12px;">
              <div style="font-size:12px;"><?= esc($m['mecz']) ?></div>
              <div style="font-size:11px;color:var(--bs-secondary-color);">
                Typ: <?= esc($m['g1Typ']) ?> · <?= esc($m['g2Typ']) ?>
              </div>
            </td>
            <td style="padding:8px 6px;text-align:center;" class="ff-bebas"><?= esc($m['wynik']) ?></td>
            <td style="padding:8px 6px;text-align:center;">
              <span class="ff-bebas" style="font-size:15px;<?= $m['g1Pkt'] > $m['g2Pkt'] ? 'color:var(--ty-green);' : '' ?>">
                +<?= $m['g1Pkt'] ?>
              </span>
              <div style="font-size:10px;color:var(--bs-secondary-color);"><?= $m['g1Sum'] ?> łącznie</div>
            </td>
            <td style="padding:8px 6px;text-align:center;">
              <span class="ff-bebas" style="font-size:15px;<?= $m['g2Pkt'] > $m['g1Pkt'] ? 'color:var(--ty-red);' : '' ?>">
                +<?= $m['g2Pkt'] ?>
              </span>
              <div style="font-size:10px;color:var(--bs-secondary-color);"><?= $m['g2Sum'] ?> łącznie</div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php elseif ($gracz1 && $gracz2): ?>
    <p class="text-secondary">Brak wspólnych meczów do porównania.</p>
  <?php elseif ($gracz1 || $gracz2): ?>
    <p class="text-secondary">Wybierz drugiego gracza aby zobaczyć porównanie.</p>
  <?php endif; ?>

</div>

<style>
.stat-label { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);margin-bottom:4px; }
</style>