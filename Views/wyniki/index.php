<!doctype html><html lang="pl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Zapisz wynik</title>
  <link href="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css>" rel="stylesheet" crossorigin="anonymous">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:600px">
  <h5 class="mb-4 text-muted">Zamknij mecz i zapisz wynik</h5>

  <?php $sukces=session()->getFlashdata('success'); $error=session()->getFlashdata('error'); ?>
  <?php if($sukces):?><div class="alert alert-success"><?= esc($sukces) ?></div><?php endif ?>
  <?php if($error):?><div class="alert alert-danger"><?= $error ?></div><?php endif ?>

  <?php if (empty($terminarz)): ?>
    <div class="alert alert-light text-muted">Brak meczów do zamknięcia w aktywnym turnieju.</div>
  <?php else: ?>
    <?php foreach ($terminarz as $mecz): ?>
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-body">
        <div class="fw-semibold mb-3"><?= esc($mecz['HomeName']) ?> – <?= esc($mecz['AwayName']) ?></div>
        <form action="/wyniki" method="post" class="row g-2 align-items-center">
          <?= csrf_field() ?>
          <input type="hidden" name="meczID" value="<?= $mecz['Id'] ?>">
          <div class="col-auto">
            <input type="number" name="H" value="<?= htmlspecialchars($mecz['ScoreHome'] ?? '') ?>"
                   min="0" class="form-control" style="width:70px" placeholder="D" required>
          </div>
          <div class="col-auto fw-bold text-center">:</div>
          <div class="col-auto">
            <input type="number" name="A" value="<?= htmlspecialchars($mecz['ScoreAway'] ?? '') ?>"
                   min="0" class="form-control" style="width:70px" placeholder="G" required>
          </div>
          <div class="col">
            <button type="submit" class="btn btn-danger w-100">Zamknij mecz</button>
          </div>
        </form>
      </div>
    </div>
    <?php endforeach ?>
  <?php endif ?>
</div>
</body></html>
