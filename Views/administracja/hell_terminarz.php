<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Porównaj terminarz<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-baseline mb-4">
  <h4 class="mb-0">Terminarz vs API</h4>
  <span class="text-muted small">Tylko mecze nierozegrane</span>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif ?>

<?php foreach ($porownanie as $row):
    $db      = $row['db'];
    $api     = $row['api'];
    $roznice = $row['roznice'];
    $maRoznice = !empty($roznice);

    // Konwersja czasu API na Warsaw
    $dtApi = null;
    if ($api) {
        $dtApi = new DateTime($api['date'] . ' ' . $api['time'], new DateTimeZone('UTC'));
        $dtApi->setTimezone(new DateTimeZone('Europe/Warsaw'));
    }

    // Konwersja czasu DB na Warsaw
    $dtDb = new DateTime($db['Date'] . ' ' . $db['Time'], new DateTimeZone('UTC'));
    $dtDb->setTimezone(new DateTimeZone('Europe/Warsaw'));
?>

<div class="card border-0 shadow-sm mb-3 <?= $maRoznice ? 'border-warning border' : '' ?>">
  <div class="card-header d-flex justify-content-between align-items-center
              <?= $maRoznice ? 'bg-warning bg-opacity-10' : '' ?>">
    <span class="fw-semibold">
      <?= esc($db['HomeName']) ?> – <?= esc($db['AwayName']) ?>
    </span>
    <?php if (!$api): ?>
      <span class="badge bg-secondary">Brak w API</span>
    <?php elseif ($maRoznice): ?>
      <span class="badge bg-warning text-dark">Różnice: <?= implode(', ', $roznice) ?></span>
    <?php else: ?>
      <span class="badge bg-success">OK</span>
    <?php endif ?>
  </div>

  <div class="card-body">
    <form method="post" action="<?= site_url('hell/terminarz/aktualizujMecz/' . (int)$db['Id']) ?>">
      <?= csrf_field() ?>

      <div class="row g-3 align-items-end">

        <!-- Data -->
        <div class="col-md-3">
          <label class="form-label small fw-semibold mb-1">Data</label>
          <?php $diffDate = in_array('Date', $roznice); ?>
          <div class="d-flex flex-column gap-1">
            <?php if ($api && $diffDate): ?>
              <span class="badge bg-danger mb-1">API: <?= esc($api['date']) ?> (<?= $dtApi->format('d.m') ?>)</span>
            <?php endif ?>
            <input type="date" name="Date" class="form-control form-control-sm <?= $diffDate ? 'border-warning' : '' ?>"
                   value="<?= esc($db['Date']) ?>">
            <span class="text-muted" style="font-size:11px;">W bazie: <?= $dtDb->format('d.m.Y') ?></span>
          </div>
        </div>

        <!-- Godzina -->
        <div class="col-md-2">
          <label class="form-label small fw-semibold mb-1">Godzina (UTC)</label>
          <?php $diffTime = in_array('Time', $roznice); ?>
          <div class="d-flex flex-column gap-1">
            <?php if ($api && $diffTime): ?>
              <span class="badge bg-danger mb-1">API: <?= esc(substr($api['time'], 0, 5)) ?> UTC = <?= $dtApi->format('H:i') ?> WAW</span>
            <?php endif ?>
            <input type="time" name="Time" class="form-control form-control-sm <?= $diffTime ? 'border-warning' : '' ?>"
                   value="<?= esc(substr($db['Time'], 0, 5)) ?>">
            <span class="text-muted" style="font-size:11px;">W bazie: <?= $dtDb->format('H:i') ?> WAW</span>
          </div>
        </div>

        <!-- Runda -->
        <div class="col-md-2">
          <label class="form-label small fw-semibold mb-1">Runda</label>
          <?php $diffRound = in_array('Round', $roznice); ?>
          <div class="d-flex flex-column gap-1">
            <?php if ($api && $diffRound): ?>
              <span class="badge bg-danger mb-1">API: <?= esc($api['round']) ?></span>
            <?php endif ?>
            <input type="text" name="Round" class="form-control form-control-sm <?= $diffRound ? 'border-warning' : '' ?>"
                   value="<?= esc($db['Round']) ?>">
            <span class="text-muted" style="font-size:11px;">W bazie: <?= esc($db['Round']) ?></span>
          </div>
        </div>

        <!-- Przycisk -->
        <div class="col-md-2">
          <button type="submit" class="btn btn-sm <?= $maRoznice ? 'btn-warning' : 'btn-outline-secondary' ?> w-100">
            <?= $maRoznice ? '⚠ Zapisz zmiany' : 'Zapisz' ?>
          </button>
        </div>

      </div>
    </form>
  </div>
</div>

<?php endforeach ?>

<?= $this->endSection() ?>