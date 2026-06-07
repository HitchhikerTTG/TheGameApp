<?= $this->extend('layouts/hell') ?>

<?= $this->section('title') ?>Pytania<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $error = session()->getFlashdata('error'); ?>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif ?>

<div class="card border-0 shadow-sm">
  <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
    Pytania turnieju
    <button class="btn btn-sm btn-primary" data-bs-toggle="collapse" data-bs-target="#formDodajPytanie">
      + Dodaj pytanie
    </button>
  </div>

  <div class="collapse" id="formDodajPytanie">
    <div class="card-body border-bottom bg-light">
      <form method="post" action="/AdminDash/dodajPytanie" class="row g-2">
        <?= csrf_field() ?>
        <input type="hidden" name="TurniejID" value="<?= $config['activeTournamentId'] ?>">
        <div class="col-12">
          <input type="text" name="tresc" class="form-control" placeholder="Treść pytania" required maxlength="255"
                 value="<?= old('tresc') ?>">
        </div>
        <div class="col-12">
          <input type="text" name="odpowiedz" class="form-control"
                 placeholder="Prawidłowa odpowiedź — dla Twojej referencji (opcjonalnie)" maxlength="255">
        </div>
        <div class="col-md-3">
          <input type="number" name="pkt" class="form-control" placeholder="Pkt za poprawną" min="0" required
                 value="<?= old('pkt') ?>">
        </div>
        <div class="col-md-5">
          <input type="datetime-local" name="wazneDo" class="form-control" required value="<?= old('wazneDo') ?>">
          <div class="form-text small">Deadline na typowanie</div>
        </div>
        <div class="col-md-4 d-flex align-items-start">
          <button type="submit" class="btn btn-primary w-100 mt-0">Zapisz pytanie</button>
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
          <th>Prawidłowa odp.</th>
          <th>Pkt</th>
          <th>Ważne do</th>
          <th class="text-center" style="width:80px">Aktywne</th>
          <th style="width:130px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pytania as $p): ?>
        <tr>
          <td class="ps-3"><?= esc($p['tresc']) ?></td>
          <td class="text-muted small"><?= esc($p['odpowiedz'] ?? '—') ?></td>
          <td><?= (int)$p['pkt'] ?></td>
          <td class="small text-muted"><?= esc(substr($p['wazneDo'], 0, 16)) ?></td>
          <td class="text-center">
            <input type="checkbox" name="aktywne[]" value="<?= $p['id'] ?>"
                   <?= $p['aktywne'] ? 'checked' : '' ?>>
          </td>
          <td class="pe-3 text-end">
            <a href="/hell/pytania/odpowiedzi/<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
              Odpowiedzi →
            </a>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>
    </table>
    <div class="card-footer bg-white">
      <button type="submit" class="btn btn-sm btn-secondary">Zapisz aktywne</button>
    </div>
  </form>
</div>

<?= $this->endSection() ?>
