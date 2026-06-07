<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Hell</title>
  <link href="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css>" rel="stylesheet" crossorigin="anonymous">
  <script src="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js>" crossorigin="anonymous"></script>
  <style>body{background:#f8f9fa}.stat-card{border:none;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08)}</style>
</head>
<body>

<?= view('administracja/_navbar') ?>

<div class="container py-3" style="max-width:960px">

<?php
$sukces = session()->getFlashdata('success');
$fail   = session()->getFlashdata('fail');
if ($sukces): ?><div class="alert alert-success alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= esc($sukces) ?></div><?php endif ?>
<?php if ($fail): ?><div class="alert alert-danger alert-dismissible fade show"><button type="button" class="btn-close" data-bs-dismiss="alert"></button><?= esc($fail) ?></div><?php endif ?>

<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card stat-card p-3 text-center">
      <div class="fs-2 fw-bold text-primary"><?= count($terminarz) ?></div>
      <div class="small text-muted">mecze do zamknięcia</div>
      <a href="/hell/mecze" class="stretched-link"></a>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card p-3 text-center">
      <div class="fs-2 fw-bold text-warning"><?= count(array_filter($pytania, fn($p) => $p['aktywne'])) ?></div>
      <div class="small text-muted">aktywne pytania</div>
      <a href="/hell/pytania" class="stretched-link"></a>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card p-3 text-center">
      <div class="fs-2 fw-bold text-success"><?= count($mecze) ?></div>
      <div class="small text-muted">mecze w turnieju</div>
      <a href="/hell/mecze" class="stretched-link"></a>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card stat-card p-3 text-center">
      <div class="fs-1">⚡</div>
      <div class="small text-muted fw-semibold"><?= esc($config['activeTournamentName'] ?? '--') ?></div>
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
        <td class="ps-3 py-2"><?= date('H:i', strtotime($m['Time'] . ' UTC')) ?></td>
        <td><?= esc($m['HomeName']) ?> – <?= esc($m['AwayName']) ?></td>
        <td class="text-end pe-3"><a href="/hell/mecze" class="btn btn-sm btn-danger">Zamknij →</a></td>
      </tr>
      <?php endforeach ?>
    </table>
  </div>
</div>
<?php endif ?>

<?php if (!empty($notatki)): ?>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header fw-semibold">Notatki</div>
  <div class="card-body">
    <?php foreach ($notatki as $n): ?>
    <div class="d-flex justify-content-between align-items-start mb-2">
      <span class="small"><?= nl2br(esc($n['tresc'])) ?></span>
      <form method="post" action="/AdminDash/ukryjNotatke/<?= $n['id'] ?>" class="ms-3">
        <?= csrf_field() ?>
        <button class="btn btn-sm btn-outline-secondary">✕</button>
      </form>
    </div>
    <?php endforeach ?>
  </div>
</div>
<?php endif ?>

<?= view('administracja/dodajNotatke', ['config' => $config, 'allKluby' => $allKluby]) ?>

</div>
</body>
</html>
