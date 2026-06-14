<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Odpowiedzi<?= $this->endSection() ?>
<?= $this->section('content') ?>

<div class="mb-4">
  <a href="/hell/pytania" class="btn btn-sm btn-outline-secondary mb-3">← Pytania</a>
<div class="card border-0 shadow-sm mb-4">
  <div class="card-body">
    <div class="d-flex justify-content-between align-items-start">
      <div>
        <h5 class="mb-1"><?= esc($pytanie['tresc']) ?></h5>
        <div class="d-flex gap-3 mt-2 small">
          <span class="text-muted">Pkt: <strong><?= (int)$pytanie['pkt'] ?></strong></span>
          <?php if (!empty($pytanie['odpowiedz'])): ?>
            <span class="badge bg-success align-self-center">Prawidłowa: <?= esc($pytanie['odpowiedz']) ?></span>
          <?php else: ?>
            <span class="text-muted">Brak prawidłowej odpowiedzi</span>
          <?php endif ?>
          <span class="text-muted">Ważne do: <?= esc(substr($pytanie['wazneDo'],0,16)) ?></span>
          <?php if (!empty($pytanie['aktywne'])): ?>
            <span class="badge bg-primary">Aktywne</span>
          <?php endif ?>
        </div>
      </div>
      <button class="btn btn-sm btn-outline-secondary" type="button"
              data-bs-toggle="collapse" data-bs-target="#formEdycja">
        ✏ Edytuj pytanie
      </button>
    </div>

    <div class="collapse mt-3" id="formEdycja">
      <hr>
      <form method="post" action="<?= site_url('hell/pytania/edytuj/' . (int)$pytanie['id']) ?>">
        <?= csrf_field() ?>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Treść pytania</label>
            <input type="text" name="tresc" class="form-control" required
                   value="<?= esc($pytanie['tresc']) ?>">
          </div>
          <div class="col-12">
            <label class="form-label">Opis <span class="text-muted">(opcjonalny)</span></label>
            <textarea name="opis" class="form-control" rows="2"><?= esc($pytanie['opis'] ?? '') ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Źródło prawdy <span class="text-muted">(opcjonalne)</span></label>
            <input type="text" name="zrodlo" class="form-control"
                   value="<?= esc($pytanie['zrodlo'] ?? '') ?>" placeholder="np. FIFA.com">
          </div>
          <div class="col-md-3">
            <label class="form-label">Punkty</label>
            <input type="number" name="pkt" class="form-control" min="1" required
                   value="<?= (int)$pytanie['pkt'] ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Ważne do</label>
            <input type="datetime-local" name="wazneDo" class="form-control"
                   value="<?= esc(str_replace(' ', 'T', substr($pytanie['wazneDo'], 0, 16))) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Prawidłowa odpowiedź <span class="text-muted">(opcjonalna)</span></label>
            <input type="text" name="odpowiedz" class="form-control"
                   value="<?= esc($pytanie['odpowiedz'] ?? '') ?>" placeholder="np. Bayern Monachium">
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check form-switch mb-2">
              <input class="form-check-input" type="checkbox" name="aktywne" value="1"
                     id="aktywneSwitch" <?= !empty($pytanie['aktywne']) ? 'checked' : '' ?>>
              <label class="form-check-label" for="aktywneSwitch">Aktywne</label>
            </div>
          </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Zapisz zmiany</button>
      </form>
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
