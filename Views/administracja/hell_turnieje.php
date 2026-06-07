<?= $this->extend('layouts/hell') ?>

<?= $this->section('title') ?>Turnieje<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="row g-4">

  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-semibold">Aktywny turniej</div>
      <div class="card-body">
        <form method="post" action="/AdminDash/zmienAktywnyTurniej" class="row g-2 align-items-end">
          <?= csrf_field() ?>
          <div class="col">
            <select name="aktywnyTurniej" class="form-select">
              <?php foreach ($turnieje as $t): ?>
                <option value="<?= $t['ID'] ?>" <?= ($config['activeTournamentId'] == $t['ID']) ? 'selected' : '' ?>>
                  <?= esc($t['CompetitionName']) ?>
                </option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="col-auto">
            <button type="submit" class="btn btn-primary">Zmień</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
        Turnieje
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#formTurniej">
          + Dodaj
        </button>
      </div>
      <div class="collapse" id="formTurniej">
        <div class="card-body border-bottom bg-light">
          <form method="post" action="/AdminDash/dodajTurniej" class="row g-2">
            <?= csrf_field() ?>
            <div class="col-7">
              <input type="text" name="nazwa" class="form-control form-control-sm" placeholder="Nazwa turnieju" required>
            </div>
            <div class="col-3">
              <input type="text" name="CompetitionID" class="form-control form-control-sm" placeholder="API ID">
            </div>
            <div class="col-2">
              <button type="submit" class="btn btn-sm btn-primary w-100">+</button>
            </div>
          </form>
        </div>
      </div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tbody>
            <?php foreach ($turnieje as $t): ?>
            <tr>
              <td class="ps-3"><?= esc($t['CompetitionName']) ?></td>
              <td class="text-muted small"><?= $t['CompetitionID'] ?></td>
              <td><?= $t['Active'] ? '<span class="badge bg-success">Aktywny</span>' : '' ?></td>
            </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<div class="mt-4">
  <?= view('administracja/dodajNotatke', ['config' => $config, 'allKluby' => $allKluby, 'notatki' => $notatki]) ?>
</div>

<?= $this->endSection() ?>
