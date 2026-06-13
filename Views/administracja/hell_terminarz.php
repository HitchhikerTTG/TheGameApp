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
        
        <!-- Zamiast osobnych kolumn Date i Time – jedna sekcja "Kiedy" -->
        <div class="col-md-5">
          <label class="form-label small fw-semibold mb-1">Kiedy</label>
          <div class="d-flex flex-column gap-1">

            <!-- Wiersz: baza -->
            <div class="d-flex align-items-center gap-2">
              <span class="text-muted" style="font-size:11px;width:40px;">Baza:</span>
              <span class="<?= $maRoznice ? 'text-danger' : '' ?>">
                <?= esc($db['Date']) ?> <?= esc(substr($db['Time'], 0, 5)) ?> UTC
              </span>
              <span class="text-muted" style="font-size:11px;">
                (= <?= $dtDb->format('d.m.Y H:i') ?> WAW)
              </span>
            </div>

            <!-- Wiersz: API -->
            <?php if ($api): ?>
            <div class="d-flex align-items-center gap-2">
              <span class="text-muted" style="font-size:11px;width:40px;">API:</span>
              <span class="<?= $maRoznice ? 'fw-semibold' : 'text-muted' ?>">
                <?= esc($api['date']) ?> <?= esc(substr($api['time'], 0, 5)) ?> UTC
              </span>
              <span class="text-muted" style="font-size:11px;">
                (= <?= $dtApi->format('d.m.Y H:i') ?> WAW)
              </span>
            </div>
            <?php endif ?>

            <!-- Inputy – zawsze UTC, API jako sugerowana wartość gdy różnica -->
            <div class="d-flex gap-2 mt-1">
              <input type="date" name="Date"
                     class="form-control form-control-sm <?= in_array('Date', $roznice) ? 'border-warning' : '' ?>"
                     value="<?= in_array('Date', $roznice) ? esc($api['date']) : esc($db['Date']) ?>">
              <input type="time" name="Time"
                     class="form-control form-control-sm <?= in_array('Time', $roznice) ? 'border-warning' : '' ?>"
                     value="<?= in_array('Time', $roznice) ? esc(substr($api['time'],0,5)) : esc(substr($db['Time'],0,5)) ?>">
            </div>

          </div>
        </div>

                <!-- Gospodarze -->
<div class="col-md-3">
  <label class="form-label small fw-semibold mb-1">Gospodarz</label>
  <?php $diffHome = in_array('HomeName', $roznice); ?>
  <div class="d-flex flex-column gap-1">
    <?php if ($api && $diffHome): ?>
      <span class="badge bg-danger mb-1">API: <?= esc($api['home_name']) ?> (ID: <?= esc($api['home_id']) ?>)</span>
    <?php endif ?>
    <input type="text" name="HomeName" class="form-control form-control-sm <?= $diffHome ? 'border-warning' : '' ?>"
           value="<?= $diffHome ? esc($api['home_name']) : esc($db['HomeName']) ?>">
    <input type="hidden" name="HomeID"
           value="<?= $diffHome ? esc($api['home_id']) : esc($db['HomeID']) ?>">
    <span class="text-muted" style="font-size:11px;">W bazie: <?= esc($db['HomeName']) ?></span>
  </div>
</div>

<!-- Gość -->
<div class="col-md-3">
  <label class="form-label small fw-semibold mb-1">Gość</label>
  <?php $diffAway = in_array('AwayName', $roznice); ?>
  <div class="d-flex flex-column gap-1">
    <?php if ($api && $diffAway): ?>
      <span class="badge bg-danger mb-1">API: <?= esc($api['away_name']) ?> (ID: <?= esc($api['away_id']) ?>)</span>
    <?php endif ?>
    <input type="text" name="AwayName" class="form-control form-control-sm <?= $diffAway ? 'border-warning' : '' ?>"
           value="<?= $diffAway ? esc($api['away_name']) : esc($db['AwayName']) ?>">
    <input type="hidden" name="AwayID"
           value="<?= $diffAway ? esc($api['away_id']) : esc($db['AwayID']) ?>">
    <span class="text-muted" style="font-size:11px;">W bazie: <?= esc($db['AwayName']) ?></span>
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