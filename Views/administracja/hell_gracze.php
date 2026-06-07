<?= $this->extend('layouts/hell') ?>

<?= $this->section('title') ?>Gracze<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php $error = session()->getFlashdata('error'); ?>
<?php if ($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif ?>

<div class="row g-4">

  <div class="col-lg-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-semibold">Przypisz gracza do klubu</div>
      <div class="card-body">
        <?php if (empty($usersNoClub)): ?>
          <p class="text-muted small mb-0">Wszyscy gracze są już w klubach.</p>
        <?php else: ?>
        <form method="post" action="/hell/przypiszUdoK" class="row g-2">
          <?= csrf_field() ?>
          <div class="col-12">
            <select name="userID" class="form-select form-select-sm">
              <?php foreach ($usersNoClub as $u): ?>
                <option value="<?= esc($u['uniID']) ?>"><?= esc($u['nick']) ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="col-12">
            <select name="clubID" class="form-select form-select-sm">
              <?php foreach ($clubs as $c): ?>
                <option value="<?= $c['id'] ?>"><?= esc($c['Nazwa']) ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-sm btn-primary w-100">Przypisz</button>
          </div>
        </form>
        <?php endif ?>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-semibold">Gracze w klubach</div>
      <div class="card-body p-0">
        <?php if (empty($clubMembers)): ?>
          <p class="text-muted small p-3 mb-0">Brak przypisań.</p>
        <?php else: ?>
        <table class="table table-sm table-hover mb-0">
          <thead class="table-light">
            <tr><th class="ps-3">Nick</th><th>Klub</th><th></th></tr>
          </thead>
          <tbody>
            <?php foreach ($clubMembers as $m): ?>
            <tr>
              <td class="ps-3"><?= esc($m['nick'] ?? $m['uniID']) ?></td>
              <td><?= esc($m['KlubNazwa'] ?? ('Klub #' . $m['KlubID'])) ?></td>
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
        <?php endif ?>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection() ?>
