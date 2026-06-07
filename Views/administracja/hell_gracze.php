<!doctype html><html lang="pl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gracze -- Hell</title>
  <link href="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css>" rel="stylesheet" crossorigin="anonymous">
  <script src="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js>" crossorigin="anonymous"></script>
  <style>body{background:#f8f9fa}</style>
</head>
<body>
<?= view('administracja/_navbar') ?>
<div class="container py-3" style="max-width:860px">

<?php $sukces=session()->getFlashdata('success'); $error=session()->getFlashdata('error'); ?>
<?php if($sukces):?><div class="alert alert-success"><?= esc($sukces) ?></div><?php endif ?>
<?php if($error):?><div class="alert alert-danger"><?= $error ?></div><?php endif ?>

<div class="row g-4">
  <!-- Przypisz do klubu -->
  <div class="col-md-5">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-header fw-semibold">Przypisz do klubu</div>
      <div class="card-body">
        <?php if (empty($usersNoClub)): ?>
          <p class="text-muted small">Wszyscy gracze są w klubach.</p>
        <?php else: ?>
        <form method="post" action="/hell/przypiszUdoK">
          <?= csrf_field() ?>
          <div class="mb-2">
            <select name="userID" class="form-select form-select-sm">
              <?php foreach ($usersNoClub as $u): ?>
                <option value="<?= esc($u['uniID']) ?>"><?= esc($u['nick']) ?> (<?= esc($u['email']) ?>)</option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="mb-3">
            <select name="clubID" class="form-select form-select-sm">
              <?php foreach ($clubs as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['Nazwa']) ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <button type="submit" class="btn btn-sm btn-primary w-100">Przypisz</button>
        </form>
        <?php endif ?>
      </div>
    </div>
  </div>

  <!-- Członkowie klubów -->
  <div class="col-md-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-semibold">Kluby i gracze</div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <thead class="table-light"><tr><th class="ps-3">Nick</th><th>Klub</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($clubMembers as $m): ?>
            <tr>
              <td class="ps-3"><?= esc($m['nick'] ?? $m['uniID']) ?></td>
              <td><?= esc($m['KlubNazwa'] ?? $m['KlubID']) ?></td>
              <td class="pe-3 text-end">
                <form method="post" action="/hell/usunUzK" class="d-inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="userID" value="<?= esc($m['uniID']) ?>">
                  <input type="hidden" name="clubID" value="<?= $m['KlubID'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Usuń</button>
                </form>
              </td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</div></body></html>
