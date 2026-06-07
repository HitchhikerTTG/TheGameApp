<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Odpowiedzi<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="mb-4">
  <a href="/hell/pytania" class="btn btn-sm btn-outline-secondary mb-3">← Pytania</a>
  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <h5 class="mb-1"><?= esc($pytanie['tresc']) ?></h5>
      <div class="d-flex gap-3 mt-2 small">
        <span class="text-muted">Pkt: <strong><?= (int)$pytanie['pkt'] ?></strong></span>
        <?php if (!empty($pytanie['odpowiedz'])): ?>
          <span class="badge bg-success align-self-center">Prawidłowa: <?= esc($pytanie['odpowiedz']) ?></span>
        <?php else: ?>
          <span class="text-muted">Brak zdefiniowanej prawidłowej odpowiedzi</span>
        <?php endif ?>
        <span class="text-muted">Ważne do: <?= esc(substr($pytanie['wazneDo'],0,16)) ?></span>
      </div>
    </div>
  </div>
</div>

<?php if (empty($odpowiedzi)): ?>
  <div class="alert alert-info">Brak odpowiedzi na to pytanie.</div>
<?php else: ?>
<form method="post" action="/hell/pytania/zapiszPunkty">
  <?= csrf_field() ?>
  <input type="hidden" name="pytanieID" value="<?= (int)$pytanie['id'] ?>">

  <div class="d-flex gap-2 mb-3">
    <button type="button" class="btn btn-sm btn-success" onclick="setAll(<?= (int)$pytanie['pkt'] ?>)">
      Zaznacz wszystkich jako poprawne (+<?= (int)$pytanie['pkt'] ?> pkt)
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger" onclick="setAll(0)">
      Zaznacz wszystkich jako niepoprawne (0 pkt)
    </button>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body p-0">
      <table class="table table-sm table-hover mb-0">
        <thead class="table-light">
          <tr><th class="ps-3">Nick</th><th>Odpowiedź</th><th style="width:120px">Pkt</th></tr>
        </thead>
        <tbody>
          <?php foreach ($odpowiedzi as $odp): ?>
          <tr>
            <td class="ps-3 fw-semibold"><?= esc($odp['nick']) ?></td>
            <td><?= esc($odp['odp']) ?></td>
            <td>
              <input type="number" name="pkt[<?= (int)$odp['id'] ?>]"
                     value="<?= (int)$odp['pkt'] ?>" min="0" max="<?= (int)$pytanie['pkt'] ?>"
                     class="form-control form-control-sm pkt-input">
            </td>
          </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-3">
    <button type="submit" class="btn btn-primary">Zapisz punkty</button>
  </div>
</form>
<?php endif ?>

<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function setAll(val) {
  document.querySelectorAll('.pkt-input').forEach(i => i.value = val);
}
</script>
<?= $this->endSection() ?>
