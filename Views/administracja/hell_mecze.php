<?= $this->extend('layouts/hell') ?>

<?= $this->section('title') ?>Mecze<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $error = session()->getFlashdata('error'); ?>
<?php if ($error): ?><div class="alert alert-warning"><?= $error ?></div><?php endif ?>

<?php if (!empty($terminarz)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-danger text-white fw-semibold">Zamknij mecz i zapisz wynik</div>
  <div class="card-body">
    <?php foreach ($terminarz as $mecz): ?>
    <form action="/serwisant/zapiszWynikMeczu" method="post" class="row g-2 align-items-center mb-3">
      <?= csrf_field() ?>
      <input type="hidden" name="meczID" value="<?= $mecz['Id'] ?>">
      <div class="col-auto text-muted small"><?= date('H:i', strtotime($mecz['Time'] . ' UTC')) ?></div>
      <div class="col fw-semibold"><?= esc($mecz['HomeName']) ?> – <?= esc($mecz['AwayName']) ?></div>
      <div class="col-auto">
        <input type="number" name="H" value="<?= $mecz['ScoreHome'] ?? '' ?>" min="0"
               class="form-control form-control-sm text-center" style="width:62px" required>
      </div>
      <div class="col-auto fw-bold px-0">:</div>
      <div class="col-auto">
        <input type="number" name="A" value="<?= $mecz['ScoreAway'] ?? '' ?>" min="0"
               class="form-control form-control-sm text-center" style="width:62px" required>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-danger">Zamknij</button>
      </div>
    </form>
    <?php endforeach ?>
  </div>
</div>
<?php else: ?>
<div class="alert alert-light text-muted mb-4">Brak meczów do zamknięcia.</div>
<?php endif ?>

<div class="card border-0 shadow-sm">
  <div class="card-header fw-semibold">Wszystkie mecze turnieju</div>
  <div class="card-body p-0">
    <table class="table table-sm table-hover mb-0">
      <thead class="table-light">
        <tr><th class="ps-3">Data</th><th>Mecz</th><th>Wynik</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($mecze as $m): ?>
        <tr>
          <td class="ps-3 small text-muted"><?= esc(substr($m['Date'] ?? '', 0, 10)) ?></td>
          <td><?= esc($m['HomeName']) ?> – <?= esc($m['AwayName']) ?></td>
          <td><?= $m['zakonczony'] ? ((int)$m['ScoreHome'] . ':' . (int)$m['ScoreAway']) : '—' ?></td>
          <td class="pe-3 text-end">
            <?php if (!$m['zakonczony']): ?>
              <a href="/przeliczMecz/<?= $m['Id'] ?>" class="btn btn-sm btn-outline-secondary">Przelicz</a>
            <?php endif ?>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
