<?= $this->extend('layouts/hell') ?>
<?= $this->section('title') ?>Kampanie email<?= $this->endSection() ?>
<?= $this->section('head') ?>
<style>
  .form-check-label .badge { font-size: .7rem; vertical-align: middle; }
  #sentStatus { min-height: 1.4rem; }
</style>
<?= $this->endSection() ?>
<?= $this->section('content') ?>
<!-- (cały istniejący kod od <div class="d-flex justify-content-between..."> do końca historii wysyłek) -->
<!-- Dodać link do digest: -->
<div class="d-flex justify-content-between align-items-baseline mb-4">
    <h4 class="mb-0">Kampanie email</h4>
    <div class="d-flex gap-2 align-items-baseline">
      <span class="text-muted small">Szablony: <code>public/maile/</code> · placeholder: <code>{nick}</code></span>
      <a href="/hell/digest" class="btn btn-sm btn-outline-primary ms-2">📧 Poranny digest →</a>
    </div>
</div>
<!-- ...reszta bez zmian... -->
<?= $this->endSection() ?>
<?= $this->section('modals') ?>
<!-- Modal: podgląd + Modal: potwierdzenie (bez zmian) -->
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script>
<!-- (cały istniejący JS bez zmian) -->
</script>
<?= $this->endSection() ?>
