<!doctype html>
<html lang="pl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $this->renderSection('title') ?> — Hell</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <style>body{background:#f8f9fa}</style>
  <?= $this->renderSection('head') ?>
</head>
<body>

<?= view('administracja/_navbar') ?>

<div class="container py-3" style="max-width:960px">

<?php
$sukces = session()->getFlashdata('success');
$fail   = session()->getFlashdata('fail');
?>
<?php if ($sukces): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?= esc($sukces) ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>
<?php if ($fail): ?>
  <div class="alert alert-danger alert-dismissible fade show">
    <?= esc($fail) ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif ?>

<?= $this->renderSection('content') ?>

</div>

<?= $this->renderSection('modals') ?>
<?= $this->renderSection('scripts') ?>

</body>
</html>
