<div class="px-3 py-4" style="max-width:680px;margin:0 auto;">

  <div class="d-flex align-items-baseline justify-content-between mb-4">
    <div>
      <p class="section-label mb-0">Statystyki</p>
      <h2 class="ff-bebas mb-0" style="font-size:28px;">Pojedynek</h2>
    </div>
  </div>

  <!-- Formularz wyboru graczy -->
  <form method="get" action="/statystyki/pojedynek" class="d-flex gap-2 mb-4 align-items-end flex-wrap">
    <div class="flex-fill">
      <div class="stat-label mb-1">Gracz 1</div>
      <select name="g1" class="form-select form-select-sm">
        <?php foreach ($gracze as $g): ?>
          <option value="<?= esc($g['slug']) ?>" <?= ($g['slug'] === $slug1) ? 'selected' : '' ?>>
            <?= esc(($g['emoji'] ? $g['emoji'] . ' ' : '') . $g['nick']) ?>
          </option>
        <?php endforeach ?>
      </select>
    </div>
    <div class="ff-bebas pb-1" style="font-size:20px;">vs</div>
    <div class="flex-fill">
      <div class="stat-label mb-1">Gracz 2</div>
      <select name="g2" class="form-select form-select-sm">
        <option value="">– wybierz –</option>
        <?php foreach ($gracze as $g): ?>
          <option value="<?= esc($g['slug']) ?>" <?= ($g['slug'] === $slug2) ? 'selected' : '' ?>>
            <?= esc(($g['emoji'] ? $g['emoji'] . ' ' : '') . $g['nick']) ?>
          </option>
        <?php endforeach ?>
      </select>
    </div>
    <button type="submit" class="btn btn-sm btn-outline-secondary pb-1">Porównaj</button>
  </form>

  <?php if ($gracz1 && $gracz2 && !empty($porownanie)): ?>

    <!-- Podsumowanie -->
    <?php
      $g1Total = end($porownanie)['g1Sum'];
      $g2Total = end($porownanie)['g2Sum'];
      reset($porownanie);
    ?>
    <div class="card match-card mb-3">
      <div class="card-body px-3 py-3">
        <div class="d-grid" style="grid-template-columns:1fr auto 1fr;gap:8px;text-align:center;">
          <div>
            <div class="ff-bebas" style="font-size:36px;<?= $g1Total >= $g2Total ? 'color:var(--ty-green)' : '' ?>">
              <?= esc($gracz1['emoji'] ?? '') ?> <?= (int)$g1Total ?>
            </div>
            <div style="font-size:13px;"><?= esc($gracz1['nick']) ?></div>
          </div>
          <div class="ff-bebas" style="font-size:24px;align-self:center;">vs</div>
          <div>
            <div class="ff-bebas" style="font-size:36px;<?= $g2Total >= $g1Total ? 'color:var(--ty-green)' : '' ?>">
              <?= (int)$g2Total ?> <?= esc($gracz2['emoji'] ?? '') ?>
            </div>
            <div style="font-size:13px;"><?= esc($gracz2['nick']) ?></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabela meczów -->
    <div class="card match-card mb-3">
      <div class="card-body px-3 py-2">
        <div class="results-row" style="font-size:11px;color:var(--bs-tertiary-color);font-weight:700;text-transform:uppercase;margin-bottom:4px;">
          <div>Mecz</div>
          <div style="text-align:right"><?= esc($gracz1['nick']) ?></div>
          <div style="text-align:center">vs</div>
          <div><?= esc($gracz2['nick']) ?></div>
        </div>
        <?php foreach ($porownanie as $row):
          $g1Win = (int)$row['g1Pkt'] > (int)$row['g2Pkt'];
          $g2Win = (int)$row['g2Pkt'] > (int)$row['g1Pkt'];
        ?>
        <div class="results-row py-1" style="border-top:1px solid var(--bs-border-color);font-size:13px;">
          <div>
            <?= esc($row['HomeName']) ?> – <?= esc($row['AwayName']) ?>
            <div class="text-secondary" style="font-size:11px;"><?= esc($row['ScoreHome']) ?>:<?= esc($row['ScoreAway']) ?></div>
          </div>
          <div style="text-align:right;font-weight:<?= $g1Win ? '700' : '400' ?>;color:<?= $g1Win ? 'var(--ty-green)' : 'inherit' ?>">
            +<?= (int)$row['g1Pkt'] ?>
            <?php if ($row['g1HomeTyp'] !== null): ?><div class="text-secondary" style="font-size:11px;"><?= (int)$row['g1HomeTyp'] ?>:<?= (int)$row['g1AwayTyp'] ?><?= $row['g1Golden'] ? ' ⚽' : '' ?></div><?php endif ?>
          </div>
          <div style="text-align:center;font-size:11px;color:var(--bs-secondary-color);"><?= (int)$row['g1Sum'] ?> / <?= (int)$row['g2Sum'] ?></div>
          <div style="font-weight:<?= $g2Win ? '700' : '400' ?>;color:<?= $g2Win ? 'var(--ty-green)' : 'inherit' ?>">
            +<?= (int)$row['g2Pkt'] ?>
            <?php if ($row['g2HomeTyp'] !== null): ?><div class="text-secondary" style="font-size:11px;"><?= (int)$row['g2HomeTyp'] ?>:<?= (int)$row['g2AwayTyp'] ?><?= $row['g2Golden'] ? ' ⚽' : '' ?></div><?php endif ?>
          </div>
        </div>
        <?php endforeach ?>
      </div>
    </div>

  <?php elseif ($gracz1 && $gracz2): ?>
    <p class="text-secondary">Brak rozegranych meczów w tym turnieju.</p>
  <?php elseif ($gracz1): ?>
    <p class="text-secondary">Wybierz drugiego gracza z listy powyżej.</p>
  <?php endif ?>

</div>

<style>
.stat-label { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);margin-bottom:4px; }
.results-row { display:grid; grid-template-columns:1fr 80px 60px 80px; gap:4px; align-items:start; }
</style>