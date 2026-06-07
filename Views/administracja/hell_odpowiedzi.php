<!doctype html><html lang="pl">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Odpowiedzi -- Hell</title>
  <link href="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css>" rel="stylesheet" crossorigin="anonymous">
  <script src="<https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js>" crossorigin="anonymous"></script>
  <style>body{background:#f8f9fa}.odpowiedz-row:hover{background:#f0f4ff}</style>
</head>
<body>
<?= view('administracja/_navbar') ?>
<div class="container py-3" style="max-width:700px">

<?php $sukces=session()->getFlashdata('success'); ?>
<?php if($sukces):?><div class="alert alert-success"><?= esc($sukces) ?></div><?php endif ?>

<div class="d-flex align-items-baseline gap-3 mb-3">
  <a href="/hell/pytania" class="text-muted small">← Pytania</a>
  <h5 class="mb-0"><?= esc($pytanie['tresc']) ?></h5>
</div>

<div class="mb-3 small text-muted">
  <span class="badge bg-secondary">Max pkt: <?= (int)$pytanie['pkt'] ?></span>
  <span class="ms-2">Ważne do: <?= esc($pytanie['wazneDo']) ?></span>
</div>

<div class="mb-3 d-flex gap-2">
  <button type="button" class="btn btn-sm btn-outline-success" onclick="setAll(<?= (int)$pytanie['pkt'] ?>)">
    ✓ Wszystkim prawidłowa (+<?= (int)$pytanie['pkt'] ?> pkt)
  </button>
  <button type="button" class="btn btn-sm btn-outline-danger" onclick="setAll(0)">
    ✗ Wszystkim błędna (0 pkt)
  </button>
</div>

<form method="post" action="/hell/pytania/zapiszPunkty">
  <?= csrf_field() ?>
  <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">

  <?php if (empty($odpowiedzi)): ?>
    <div class="alert alert-light text-muted">Brak odpowiedzi na to pytanie.</div>
  <?php else: ?>
  <div class="card border-0 shadow-sm mb-3">
    <table class="table mb-0">
      <thead class="table-light">
        <tr><th class="ps-3">Nick</th><th>Odpowiedź</th><th style="width:90px">Pkt</th></tr>
      </thead>
      <tbody>
        <?php foreach ($odpowiedzi as $odp): ?>
        <tr class="odpowiedz-row">
          <td class="ps-3 fw-semibold"><?= esc($odp['nick']) ?></td>
          <td><?= esc($odp['odp']) ?></td>
          <td>
            <input type="number" name="pkt[<?= $odp['id'] ?>]"
                   value="<?= (int)$odp['pkt'] ?>"
                   min="0" max="<?= (int)$pytanie['pkt'] ?>"
                   class="form-control form-control-sm pkt-input">
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
  <button type="submit" class="btn btn-primary w-100">Zapisz punkty</button>
  <?php endif ?>
</form>

</div>
<script>
function setAll(val) {
  document.querySelectorAll('.pkt-input').forEach(el => el.value = val);
}
</script>
</body></html>
