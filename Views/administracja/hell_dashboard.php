<?= $this->extend('layouts/hell') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center position-relative">
      <div class="fs-2 fw-bold text-danger"><?= count($terminarz) ?></div>
      <div class="small text-muted">do zamknięcia</div>
      <a href="/hell/mecze" class="stretched-link"></a>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center position-relative">
      <div class="fs-2 fw-bold text-warning"><?= count(array_filter($pytania, fn($p) => $p['aktywne'])) ?></div>
      <div class="small text-muted">aktywne pytania</div>
      <a href="/hell/pytania" class="stretched-link"></a>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center position-relative">
      <div class="fs-2 fw-bold text-success"><?= count($mecze) ?></div>
      <div class="small text-muted">mecze w turnieju</div>
      <a href="/hell/mecze" class="stretched-link"></a>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card border-0 shadow-sm p-3 text-center position-relative">
      <div class="fs-5 fw-semibold lh-sm mt-1"><?= esc($config['activeTournamentName'] ?? '—') ?></div>
      <div class="small text-muted">aktywny turniej</div>
      <a href="/hell/turnieje" class="stretched-link"></a>
    </div>
  </div>
</div>

<?php if (!empty($terminarz)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header bg-danger text-white fw-semibold">Mecze do zamknięcia</div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <?php foreach ($terminarz as $m): ?>
      <tr>
        <td class="ps-3 py-2 text-muted small"><?= date('H:i', strtotime($m['Time'] . ' UTC')) ?></td>
        <td class="py-2"><?= esc($m['HomeName']) ?> – <?= esc($m['AwayName']) ?></td>
        <td class="pe-3 py-2 text-end"><a href="/hell/mecze" class="btn btn-sm btn-danger">Zamknij →</a></td>
      </tr>
      <?php endforeach ?>
    </table>
  </div>
</div>
<?php endif ?>

<?= view('administracja/dodajNotatke', ['config' => $config, 'allKluby' => $allKluby, 'notatki' => $notatki]) ?>

<?= $this->endSection() ?>
