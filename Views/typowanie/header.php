<!doctype html>
<html lang="pl" data-bs-theme="light">
<head>
  <base href="<?= base_url(); ?>">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title><?= esc($title) ?> | Typer</title>
  <link rel="canonical" href="<?= site_url() ?>">
  <link rel="preconnect" href="<https://fonts.googleapis.com">
  <link rel="preconnect" href="<https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
  <link rel="stylesheet" type="text/css" href="/newStyle2026.css">

</head>

<body>

<nav class="topbar px-3 py-2 d-flex align-items-center justify-content-between">
  <a href="/typowanie" class="ff-bebas topbar-logo">Typer<span>.</span><?= esc($title) ?></a>
  <div class="d-flex align-items-center gap-2">
    <?php $nick = session()->get('username'); if ($nick): ?>
      <span class="text-secondary" style="font-size:13px;font-weight:500;"><?= esc($nick) ?></span>
    <?php endif; ?>
    <button class="theme-btn" id="themeToggle" onclick="toggleTheme()">☀️</button>
    <div class="dropdown">
      <button class="theme-btn" data-bs-toggle="dropdown" aria-expanded="false">☰</button>
      <ul class="dropdown-menu dropdown-menu-end">
        <li><a class="dropdown-item" href="/zasady">Zasady typera</a></li>
        <li><a class="dropdown-item" href="/profil">Edycja preferencji</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item" href="/auth/logout">Wyloguj się</a></li>
      </ul>
    </div>
  </div>
</nav>

<?php
$session = \Config\Services::session();
$sukces  = $session->getFlashData('success');
$fail    = $session->getFlashData('fail');
if ($sukces): ?>
  <div class="alert alert-success mx-3 mt-3 mb-0"><?= esc($sukces) ?></div>
<?php elseif ($fail): ?>
  <div class="alert alert-danger mx-3 mt-3 mb-0"><?= esc($fail) ?></div>
<?php endif; ?>

<script>
function initTheme() {
  var saved = localStorage.getItem('theme') || 'light';
  document.documentElement.setAttribute('data-bs-theme', saved);
  document.getElementById('themeToggle').textContent = saved === 'dark' ? '🌙' : '☀️';
}
function toggleTheme() {
  var current = document.documentElement.getAttribute('data-bs-theme');
  var next = current === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-bs-theme', next);
  localStorage.setItem('theme', next);
  document.getElementById('themeToggle').textContent = next === 'dark' ? '🌙' : '☀️';
}
initTheme();
</script>

<div class="px-3 pb-5">
 