<?php
$uri = service('request')->getUri()->getPath();
$nav = function(string $slug, string $path) use ($uri): string {
    return str_contains($uri, $path) ? 'active' : '';
};
?>
<nav class="navbar navbar-expand-sm navbar-dark bg-dark px-3 mb-0">
  <a class="navbar-brand fw-bold fs-6 me-3" href="/hell">⚡ Hell</a>
  <ul class="navbar-nav flex-row flex-wrap gap-1">
    <li class="nav-item"><a class="nav-link px-2 <?= $nav('mecze','/hell/mecze') ?>" href="/hell/mecze">Mecze</a></li>
    <li class="nav-item"><a class="nav-link px-2 <?= $nav('pytania','/hell/pytania') ?>" href="/hell/pytania">Pytania</a></li>
    <li class="nav-item"><a class="nav-link px-2 <?= $nav('gracze','/hell/gracze') ?>" href="/hell/gracze">Gracze</a></li>
    <li class="nav-item"><a class="nav-link px-2 <?= str_contains($uri,'/hell/kampanie')||str_contains($uri,'/hell/digest') ? 'active' : '' ?>" href="/hell/kampanie">Maile</a></li>
    <li class="nav-item"><a class="nav-link px-2 <?= $nav('turnieje','/hell/turnieje') ?>" href="/hell/turnieje">Turnieje</a></li>
  </ul>
  <div class="ms-auto">
    <a href="/wyniki" class="btn btn-sm btn-outline-warning">Wyniki ↗</a>
  </div>
</nav>
<div class="mb-4"></div>
