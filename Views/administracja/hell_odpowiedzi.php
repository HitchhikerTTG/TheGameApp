<?= $this->extend('layouts/hell') ?>

<?= $this->section('title') ?>Odpowiedzi<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="d-flex align-items-center gap-3 mb-3">
  <a href="/hell/pytania" class="text-muted small text-decoration-none">← Pytania</a>
  <h5 class="mb-0"><?= esc($pytanie['tresc']) ?></h5>
</div>

<div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
  <?php if (!empty($pytanie['odpowiedz'])): ?>
    <span class="badge bg-success fs-6 fw-normal">✓ <?= esc($pytanie['odpowiedz']) ?></span>
  <?php endif ?>
  <span class="badge bg-secondary">Max: <?= (int)$pytanie['pkt'] ?> pkt</span>
  <span class="text-muted small">· Ważne do: <?= esc(substr($pytanie['wazneDo'], 0, 16)) ?></span>
</div>

<?php if (empty($odpowiedzi)): ?>
  <div class="alert alert-light text-muted">Brak odpowiedzi na to pytanie.</div>
<?php else: ?>

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
  <div class="card border-0 shadow-sm mb-3">
    <table class="table mb-0">
      <thead class="table-light">
        <tr>
          <th class="ps-3" style="width:160px">Nick</th>
          <th>Odpowiedź</th>
          <th style="width:100px">Pkt</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($odpowiedzi as $odp): ?>
        <tr>
          <td class="ps-3 fw-semibold"><?= esc($odp['nick']) ?></td>
          <td><?= esc($odp['odp']) ?></td>
          <td>
            <input type="number" name="pkt[<?= $odp['id'] ?>]"
                   value="<?= (int)$odp['pkt'] ?>"
                   min="0" max="<?= (int)$pytanie['pkt'] ?>"
                   class="form-control form-control-sm pkt-input text-center">
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
  <button type="submit" class="btn btn-primary">Zapisz punkty</button>
</form>

<?php endif ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function setAll(val) {
  document.querySelectorAll('.pkt-input').forEach(el => el.value = val);
}
</script>
<?= $this->endSection() ?>
