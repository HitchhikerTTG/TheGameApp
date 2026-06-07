<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Poranny digest<?= $this->endSection() ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
<div class="col-lg-7">
<!-- (cały istniejący kod karty formularza -- od <div class="d-flex justify-content-between..."> do końca </div>) -->
</div>
</div>
<?= $this->endSection() ?>
<?= $this->section('modals') ?>
<!-- Modal: potwierdzenie (bez zmian) -->
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
function openModal() {
    new bootstrap.Modal(document.getElementById('modalPotwierdzenie')).show();
}
function submitDigest() {
    document.getElementById('formDigest').submit();
}
</script>
<?= $this->endSection() ?>
