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
          <tr data-odp-id="<?= $odp['id'] ?>">
  <td><?= esc($odp['nick']) ?></td>
  <td><?= esc($odp['odp']) ?></td>
  <td>
  <input type="hidden"
         name="oceny[<?= (int)$odp['id'] ?>]"
         value="<?= $odp['pkt'] > 0 ? 1 : 0 ?>">

  <button type="button"
          class="btn btn-sm btn-ocena <?= $odp['pkt'] > 0 ? 'btn-success' : 'btn-outline-secondary' ?>"
          data-correct="1">✓</button>

  <button type="button"
          class="btn btn-sm btn-ocena <?= ($odp['pkt'] !== null && $odp['pkt'] == 0) ? 'btn-danger' : 'btn-outline-secondary' ?>"
          data-correct="0">✗</button>

  <span class="ms-2 small text-muted pkt-label">
    <?= $odp['pkt'] !== null ? $odp['pkt'] . ' pkt' : '-' ?>
  </span>
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
function setAll(correct) {
    document.querySelectorAll('tr[data-odp-id]').forEach(function (tr) {
        applyOcena(tr, correct);
    });
}

function applyOcena(tr, correct) {
    // Aktualizuj hidden input
    const input = tr.querySelector('input[type="hidden"][name^="oceny"]');
    if (input) input.value = correct;

    // Aktualizuj style przycisków
    tr.querySelectorAll('.btn-ocena').forEach(function (b) {
        const isThis = parseInt(b.dataset.correct, 10) === correct;
        b.classList.toggle('btn-success',          isThis && correct === 1);
        b.classList.toggle('btn-danger',           isThis && correct === 0);
        b.classList.toggle('btn-outline-secondary', !isThis);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-ocena').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tr      = btn.closest('tr[data-odp-id]');
            const correct = parseInt(btn.dataset.correct, 10);
            applyOcena(tr, correct);
        });
    });
});


</script>
<?= $this->endSection() ?>
