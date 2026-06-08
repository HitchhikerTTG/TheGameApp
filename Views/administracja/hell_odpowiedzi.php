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
    <button class="btn btn-sm btn-ocena <?= $odp['pkt'] > 0 ? 'btn-success' : 'btn-outline-secondary' ?>"
            data-correct="1">✓</button>
    <button class="btn btn-sm btn-ocena <?= $odp['pkt'] == 0 && $odp['pkt'] !== null ? 'btn-danger' : 'btn-outline-secondary' ?>"
            data-correct="0">✗</button>
    <span class="ms-2 small text-muted pkt-label"><?= $odp['pkt'] !== null ? $odp['pkt'].' pkt' : '--' ?></span>
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

document.addEventListener('DOMContentLoaded', function () {
    const pytanieID = <?= (int)$pytanie['id'] ?>;

    document.querySelectorAll('.btn-ocena').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const tr      = btn.closest('tr');
            const odpId   = parseInt(tr.dataset.odpId, 10);
            const correct = parseInt(btn.dataset.correct, 10);

            const body = new URLSearchParams({
                odpId:     odpId,
                correct:   correct,
                pytanieID: pytanieID,
                [document.querySelector('meta[name="csrf-token-name"]').content]:
                    document.querySelector('meta[name="csrf-hash"]').content
            });

            fetch('/hell/pytania/zapiszPunkty', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body.toString()
            })
            .then(r => r.json())
            .then(data => {
                if (data.ok) {
                    const label = tr.querySelector('.pkt-label');
                    if (label) label.textContent = data.pkt + ' pkt';

                    // Aktualizacja stylu przycisków
                    tr.querySelectorAll('.btn-ocena').forEach(b => {
                        b.classList.remove('btn-success', 'btn-danger', 'btn-outline-secondary');
                        if (parseInt(b.dataset.correct, 10) === correct) {
                            b.classList.add(correct ? 'btn-success' : 'btn-danger');
                        } else {
                            b.classList.add('btn-outline-secondary');
                        }
                    });
                }
            })
            .catch(() => alert('Błąd zapisu'));
        });
    });
});

</script>
<?= $this->endSection() ?>
