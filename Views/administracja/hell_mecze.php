<!doctype html><html lang="pl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Mecze -- Hell</title>
  <link href="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css>" rel="stylesheet" crossorigin="anonymous">
  <script src="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js>" crossorigin="anonymous"></script>
  <style>body{background:#f8f9fa}</style>
</head>
<body>
<?= view('administracja/_navbar') ?>
<div class="container py-3" style="max-width:800px">

<?php $sukces=session()->getFlashdata('success'); $fail=session()->getFlashdata('fail'); $error=session()->getFlashdata('error'); ?>
<?php if($sukces):?><div class="alert alert-success"><?= esc($sukces) ?></div><?php endif ?>
<?php if($fail):?><div class="alert alert-danger"><?= esc($fail) ?></div><?php endif ?>
<?php if($error):?><div class="alert alert-warning"><?= $error ?></div><?php endif ?>

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
        <input type="number" name="H" value="<?= $mecz['ScoreHome'] ?? '' ?>" min="0" class="form-control form-control-sm" style="width:60px" required>
      </div>
      <div class="col-auto text-center fw-bold">:</div>
      <div class="col-auto">
        <input type="number" name="A" value="<?= $mecz['ScoreAway'] ?? '' ?>" min="0" class="form-control form-control-sm" style="width:60px" required>
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
      <thead class="table-light"><tr><th>Data</th><th>Mecz</th><th>Wynik</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($mecze as $m): ?>
        <tr>
          <td class="ps-3 small text-muted"><?= esc($m['Date']) ?></td>
          <td><?= esc($m['HomeName']) ?> – <?= esc($m['AwayName']) ?></td>
          <td><?= $m['zakonczony'] ? $m['ScoreHome'].':'.$m['ScoreAway'] : '--' ?></td>
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

</div></body></html>
