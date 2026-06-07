<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Gracze<?= $this->endSection() ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Gracze i kluby</h4>

<div class="row g-4">
  <div class="col-md-5">
    <div class="card border-0 shadow-sm">
      <div class="card-header fw-semibold">Przypisz gracza do klubu</div>
      <div class="card-body">
        <?= view('administracja/assignUserToClub') ?>
      </div>
    </div>
  </div>
  <div class="col-md-7">
    <?php foreach ($kluby as $klub): ?>
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header fw-semibold"><?= esc($klub['Nazwa']) ?></div>
      <div class="card-body p-0">
        <table class="table table-sm mb-0">
          <tbody>
            <?php foreach ($klub['members'] as $czlonek): ?>
            <tr>
              <td class="ps-3"><?= esc($czlonek['nick']) ?></td>
              <td class="text-end pe-3">
                <form method="post" action="<?= site_url('hell/usunUzK') ?>" style="display:inline">
                  <?= csrf_field() ?>
                  <input type="hidden" name="userID" value="<?= esc($czlonek['uniID']) ?>">
                  <input type="hidden" name="klubID" value="<?= (int)$klub['id'] ?>">
                  <button type="submit" class="btn btn-sm btn-outline-danger"
                          onclick="return confirm('Usunąć z klubu?')">Usuń</button>
                </form>
              </td>
            </tr>
            <?php endforeach ?>
            <?php if (empty($klub['members'])): ?>
            <tr><td class="ps-3 text-muted small">Brak członków</td></tr>
            <?php endif ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endforeach ?>
  </div>
</div>

<?= $this->endSection() ?>
