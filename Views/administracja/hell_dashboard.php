<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Panel admina</h4>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm text-center p-3">
      <div class="fs-2 fw-bold text-primary"><?= count(array_filter($mecze, fn($m) => !($m['zakonczony'] ?? 0))) ?></div>
      <div class="small text-muted">Mecze do zamknięcia</div>
      <a href="/hell/mecze" class="stretched-link"></a>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm text-center p-3">
      <div class="fs-2 fw-bold text-warning"><?= count($pytania) ?></div>
      <div class="small text-muted">Aktywne pytania</div>
      <a href="/hell/pytania" class="stretched-link"></a>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm text-center p-3">
      <div class="fs-2 fw-bold text-success"><?= count($mecze) ?></div>
      <div class="small text-muted">Mecze w turnieju</div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card border-0 shadow-sm text-center p-3 overflow-hidden">
      <div class="fs-6 fw-semibold text-truncate"><?= esc($config['activeTournamentName'] ?? '--') ?></div>
      <div class="small text-muted">Aktywny turniej</div>
      <a href="/hell/turnieje" class="stretched-link"></a>
    </div>
  </div>
</div>

<?= view('administracja/dodajNotatke', ['allKluby' => $allKluby, 'notatki' => $notatki]) ?>

<?= $this->endSection() ?>
