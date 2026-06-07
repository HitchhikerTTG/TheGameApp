<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Wpisz wyniki</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <style>body{background:#f8f9fa}</style>
</head>
<body>
<div class="container py-4" style="max-width:640px">

<?php $sukces = session()->getFlashdata('success'); $fail = session()->getFlashdata('fail'); ?>
<?php if ($sukces): ?><div class="alert alert-success"><?= esc($sukces) ?></div><?php endif ?>
<?php if ($fail): ?><div class="alert alert-danger"><?= esc($fail) ?></div><?php endif ?>

<h4 class="mb-4">Wpisz wyniki meczów</h4>

<?php if (empty($terminarz)): ?>
  <div class="alert alert-info">Brak meczów do zamknięcia.</div>
<?php else: ?>
  <?php foreach ($terminarz as $mecz): ?>
  <div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
      <div class="fw-semibold mb-2">
        <?= esc($mecz['plHomeName'] ?? $mecz['HomeName']) ?> – <?= esc($mecz['plAwayName'] ?? $mecz['AwayName']) ?>
        <span class="text-muted small ms-2"><?= esc(substr($mecz['naszCzas'] ?? $mecz['Time'],0,5)) ?></span>
      </div>
      <form method="post" action="/wyniki" class="d-flex gap-2 align-items-center">
        <?= csrf_field() ?>
        <input type="hidden" name="meczId" value="<?= (int)$mecz['Id'] ?>">
        <input type="number" name="scoreH" value="<?= (int)$mecz['ScoreHome'] ?>" min="0"
               class="form-control form-control-sm" style="width:64px">
        <span class="fw-bold">:</span>
        <input type="number" name="scoreA" value="<?= (int)$mecz['ScoreAway'] ?>" min="0"
               class="form-control form-control-sm" style="width:64px">
        <button class="btn btn-sm btn-primary">Zapisz</button>
      </form>
    </div>
  </div>
  <?php endforeach ?>
<?php endif ?>

</div>
</body>
</html>
