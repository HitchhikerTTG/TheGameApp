<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Mecze<?= $this->endSection() ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Mecze</h4>

<?php $doZamkniecia = array_filter($terminarz, fn($m) => !($m['zakonczony'] ?? 0) && ($m['Rozpoczety'] ?? 0)); ?>
<?php if (!empty($doZamkniecia)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header fw-semibold">Do zamknięcia / wpisania wyników</div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="table-light"><tr><th class="ps-3">Mecz</th><th>Data</th><th>Wynik</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($doZamkniecia as $m): ?>
        <tr>
          <td class="ps-3"><?= esc($m['plHomeName'] ?? $m['HomeName']) ?> – <?= esc($m['plAwayName'] ?? $m['AwayName']) ?></td>
          <td class="small text-muted"><?= esc(substr($m['Date'],0,10)) ?> <?= esc(substr($m['naszCzas'] ?? $m['Time'],0,5)) ?></td>
          <td>
            <form method="post" action="<?= site_url('Serwisant/przelicz') ?>" class="d-flex gap-1 align-items-center">
              <?= csrf_field() ?>
              <input type="hidden" name="meczId" value="<?= (int)$m['Id'] ?>">
              <input type="number" name="scoreH" value="<?= (int)$m['ScoreHome'] ?>" min="0" class="form-control form-control-sm" style="width:56px">
              <span>:</span>
              <input type="number" name="scoreA" value="<?= (int)$m['ScoreAway'] ?>" min="0" class="form-control form-control-sm" style="width:56px">
              <button class="btn btn-sm btn-primary">Zapisz</button>
            </form>
          </td>
          <td><?php if ($m['apiScoreH'] !== null): ?>
  <span class="text-muted small ms-2">
    API: <?= (int)$m['apiScoreH'] ?>:<?= (int)$m['apiScoreA'] ?>
    <?php if ($m['apiStatus'] === 'Zakonczony'): ?>
      <span class="badge bg-success">FT</span>
    <?php elseif ($m['apiStatus'] === 'Live'): ?>
      <span class="badge bg-danger">Live</span>
    <?php endif ?>
  </span>
  <button type="button" class="btn btn-sm btn-outline-success ms-1"
          onclick="this.closest('form').querySelector('[name=scoreH]').value=<?= (int)$m['apiScoreH'] ?>;
                   this.closest('form').querySelector('[name=scoreA]').value=<?= (int)$m['apiScoreA'] ?>">
    Użyj
  </button>
<?php endif ?></td>
          <td><a href="<?= site_url('Serwisant/przeliczMecz/' . (int)$m['Id']) ?>" class="btn btn-sm btn-outline-secondary">Przelicz pkt</a></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif ?>

<div class="card border-0 shadow-sm">
  <div class="card-header fw-semibold">Wszystkie mecze turnieju</div>
  <div class="card-body p-0">
    <table class="table table-sm table-hover mb-0">
      <thead class="table-light"><tr><th class="ps-3">Mecz</th><th>Data</th><th>Wynik</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($wszystkieMecze as $m): ?>
        <tr>
          <td class="ps-3"><?= esc($m['plHomeName'] ?? $m['HomeName']) ?> – <?= esc($m['plAwayName'] ?? $m['AwayName']) ?></td>
          <td class="small text-muted"><?= esc(substr($m['Date'],0,10)) ?> <?= esc(substr($m['naszCzas'] ?? $m['Time'],0,5)) ?></td>
          <td><?= $m['zakonczony'] ? (int)$m['ScoreHome'].':'.(int)$m['ScoreAway'] : '–' ?></td>
          <td>
            <?php if ($m['zakonczony']): ?>
              <span class="badge bg-success">Zakończony</span>
            <?php elseif ($m['Rozpoczety']): ?>
              <span class="badge bg-danger">Na żywo</span>
            <?php else: ?>
              <span class="badge bg-secondary">Oczekuje</span>
            <?php endif ?>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
