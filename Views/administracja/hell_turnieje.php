<!doctype html><html lang="pl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Turnieje -- Hell</title>
  <link href="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css>" rel="stylesheet" crossorigin="anonymous">
  <script src="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js>" crossorigin="anonymous"></script>
  <style>body{background:#f8f9fa}</style>
</head>
<body>
<?= view('administracja/_navbar') ?>
<div class="container py-3" style="max-width:800px">

<?php $sukces=session()->getFlashdata('success'); $fail=session()->getFlashdata('fail'); ?>
<?php if($sukces):?><div class="alert alert-success"><?= esc($sukces) ?></div><?php endif ?>
<?php if($fail):?><div class="alert alert-danger"><?= esc($fail) ?></div><?php endif ?>

<!-- Aktywny turniej -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header fw-semibold">Aktywny turniej</div>
  <div class="card-body">
    <form method="post" action="/AdminDash/zmienAktywnyTurniej" class="row g-2 align-items-end">
      <?= csrf_field() ?>
      <div class="col">
        <select name="aktywnyTurniej" class="form-select">
          <?php foreach ($turnieje as $t): ?>
            <option value="<?= $t['ID'] ?>" <?= ($config['activeTournamentId'] == $t['ID']) ? 'selected' : '' ?>>
              <?= esc($t['CompetitionName']) ?>
            </option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary">Zmień aktywny</button>
      </div>
    </form>
  </div>
</div>

<!-- Dodaj turniej -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between">
    Turnieje
    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#formTurniej">+ Dodaj</button>
  </div>
  <div class="collapse" id="formTurniej">
    <div class="card-body border-bottom">
      <form method="post" action="/AdminDash/dodajTurniej" class="row g-2">
        <?= csrf_field() ?>
        <div class="col-md-6"><input type="text" name="nazwa" class="form-control" placeholder="Nazwa turnieju" required></div>
        <div class="col-md-4"><input type="text" name="CompetitionID" class="form-control" placeholder="CompetitionID (API)"></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Zapisz</button></div>
      </form>
    </div>
  </div>
  <div class="card-body p-0">
    <table class="table table-sm mb-0">
      <thead class="table-light"><tr><th class="ps-3">Nazwa</th><th>CompetitionID</th><th>Aktywny</th></tr></thead>
      <tbody>
        <?php foreach ($turnieje as $t): ?>
        <tr>
          <td class="ps-3"><?= esc($t['CompetitionName']) ?></td>
          <td><?= $t['CompetitionID'] ?></td>
          <td><?= $t['Active'] ? '<span class="badge bg-success">Tak</span>' : '' ?></td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Notatki -->
<div class="card border-0 shadow-sm mb-4">
  <div class="card-header fw-semibold">Notatki</div>
  <div class="card-body">
    <?php if (empty($notatki)): ?>
      <p class="text-muted small mb-0">Brak notatek.</p>
    <?php else: ?>
      <?php foreach ($notatki as $n): ?>
      <div class="d-flex justify-content-between align-items-start mb-2">
        <span class="small"><?= nl2br(esc($n['tresc'])) ?></span>
        <form method="post" action="/AdminDash/ukryjNotatke/<?= $n['id'] ?>" class="ms-2">
          <?= csrf_field() ?><button class="btn btn-sm btn-outline-secondary">✕</button>
        </form>
      </div>
      <?php endforeach ?>
    <?php endif ?>
  </div>
</div>

<?= view('administracja/dodajNotatke', ['config' => $config, 'allKluby' => $allKluby]) ?>

</div></body></html>
