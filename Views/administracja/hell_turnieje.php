<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Turnieje<?= $this->endSection() ?>
<?= $this->section('content') ?>

<h4 class="mb-4">Turnieje</h4>

<div class="row g-4">
  <div class="col-md-6">
    <?= view('administracja/listaTurniejow') ?>
  </div>
  <div class="col-md-6">
    <?= view('administracja/dodajTurniej') ?>
    <?= view('administracja/dodajKlub') ?>
    <?= view('administracja/listaKlubow', ['kluby' => $allKluby]) ?>

  </div>
</div>

<hr class="my-4">
<?= view('administracja/dodajNotatke', ['allKluby' => $allKluby, 'notatki' => $notatki]) ?>



<?= $this->endSection() ?>
