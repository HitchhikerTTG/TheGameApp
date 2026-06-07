<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Mecze<?= $this->endSection() ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Mecze</h4>

<?php if (!empty($terminarz)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
    <span>Do zamknięcia / wpisania wyników</span>
    <span class="badge bg-danger"><?= count($terminarz) ?></span>
  </div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3">Mecz</th>
          <th>Godzina</th>
          <th>Wynik</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($terminarz as $m): ?>
        <tr>
          <td class="ps-3 fw-semibold">
            <?= esc($m['plHomeName'] ?? $m['HomeName']) ?>
            <span class="text-muted fw-normal">–</span>
            <?= esc($m['plAwayName'] ?? $m['AwayName']) ?>
          </td>
          <td class="small text-muted">
            <?= esc(substr($m['Date'], 0, 10)) ?>
            <?= esc(substr($m['naszCzas'] ?? $m['Time'], 0, 5)) ?>
          </td>
          <td>
            <form method="post" action="<?= site_url('hell/mecze/zapisz') ?>"
                  class="d-flex gap-1 align-items-center">
              <?= csrf_field() ?>
              <input type="hidden" name="meczId" value="<?= (int)$m['Id'] ?>">
              <input type="number" name="scoreH"
                     value="<?= $m['apiScoreH'] ?? (int)$m['ScoreHome'] ?>"
                     min="0" class="form-control form-control-sm text-center" style="width:56px">
              <span class="fw-bold">:</span>
              <input type="number" name="scoreA"
                     value="<?= $m['apiScoreA'] ?? (int)$m['ScoreAway'] ?>"
                     min="0" class="form-control form-control-sm text-center" style="width:56px">
              <?php if ($m['apiScoreH'] !== null): ?>
                <span class="badge <?= $m['apiStatus'] === 'Zakonczony' ? 'bg-success' : 'bg-danger' ?> ms-1">
                  API <?= (int)$m['apiScoreH'] ?>:<?= (int)$m['apiScoreA'] ?>
                  <?= $m['apiStatus'] === 'Zakonczony' ? 'FT' : 'Live' ?>
                </span>
              <?php endif ?>
              <button class="btn btn-sm btn-primary ms-1">Zapisz i przelicz</button>
            </form>
          </td>
          <td class="text-end pe-3 small text-muted">
            <?= count($terminarz) ?> typujących
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
<div class="alert alert-success mb-4">Brak meczów do zamknięcia.</div>
<?php endif ?>

<div class="card border-0 shadow-sm">
  <div class="card-header fw-semibold">Wszystkie mecze turnieju</div>
  <div class="card-body p-0">
    <?php if (empty($wszystkieMecze)): ?>
      <p class="text-muted small p-3 mb-0">Brak zakończonych meczów.</p>
    <?php else: ?>
    <table class="table table-sm table-hover mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3">Mecz</th>
          <th>Data</th>
          <th>Wynik</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($wszystkieMecze as $m): ?>
        <tr>
          <td class="ps-3">
            <?= esc($m['plHomeName'] ?? $m['HomeName']) ?>
            <span class="text-muted">–</span>
            <?= esc($m['plAwayName'] ?? $m['AwayName']) ?>
          </td>
          <td class="small text-muted">
            <?= esc(substr($m['Date'], 0, 10)) ?>
            <?= esc(substr($m['naszCzas'] ?? $m['Time'], 0, 5)) ?>
          </td>
          <td class="fw-bold">
            <?= (int)$m['ScoreHome'] ?>:<?= (int)$m['ScoreAway'] ?>
          </td>
          <td>
            <span class="badge bg-success">Zakończony</span>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
    <?php endif ?>
  </div>
</div>

<?= $this->endSection() ?>
