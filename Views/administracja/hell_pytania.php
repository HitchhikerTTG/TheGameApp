<!doctype html><html lang="pl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pytania -- Hell</title>
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

<div class="card border-0 shadow-sm mb-4">
  <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
    Pytania
    <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#formDodaj">+ Dodaj pytanie</button>
  </div>
  
  <div class="collapse" id="formDodaj">
    <div class="card-body border-bottom">
      <form method="post" action="/AdminDash/dodajPytanie" class="row g-2">
        <?= csrf_field() ?>
        <input type="hidden" name="TurniejID" value="<?= $config['activeTournamentId'] ?>">
        <div class="col-12">
          <input type="text" name="tresc" class="form-control" placeholder="Treść pytania" required maxlength="255">
        </div>
        <div class="col-12">
          <input type="text" name="odpowiedz" class="form-control" placeholder="Prawidłowa odpowiedź (opcjonalnie -- dla Twojej referencji)" maxlength="255">
        </div>
        <div class="col-md-4">
          <input type="number" name="pkt" class="form-control" placeholder="Pkt za poprawną" min="0" required>
        </div>
        <div class="col-md-4">
          <input type="datetime-local" name="wazneDo" class="form-control" required>
          <div class="form-text">Format: YYYY-MM-DD HH:MM -- serwer przyjmie z sekundami :00</div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">Zapisz pytanie</button>
        </div>
      </form>
    </div>
  </div>

  <form method="post" action="/AdminDash/updateQuestionStatus">
    <?= csrf_field() ?>
    <table class="table table-sm mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3">Treść</th>
          <th>Odpowiedź</th>
          <th>Pkt</th>
          <th>Ważne do</th>
          <th class="text-center">Aktywne</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pytania as $p): ?>
        <tr>
          <td class="ps-3"><?= esc($p['tresc']) ?></td>
          <td class="text-muted small"><?= esc($p['odpowiedz'] ?? '--') ?></td>
          <td><?= $p['pkt'] ?></td>
          <td class="small text-muted"><?= esc($p['wazneDo']) ?></td>
          <td class="text-center">
            <input type="checkbox" name="aktywne[]" value="<?= $p['id'] ?>" <?= $p['aktywne'] ? 'checked' : '' ?>>
          </td>
          <td class="pe-3 text-end">
            <a href="/hell/pytania/odpowiedzi/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">Odpowiedzi →</a>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
    <div class="card-footer">
      <button type="submit" class="btn btn-sm btn-secondary">Zapisz aktywne</button>
    </div>
  </form>
</div>

</div></body></html>
  