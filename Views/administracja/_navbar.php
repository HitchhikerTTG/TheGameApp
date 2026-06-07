<?php
$uri = service('request')->getUri()->getPath();
$a = fn(string $p): string => str_starts_with($uri, $p) ? ' active' : '';
?>
<nav class="navbar navbar-expand-sm navbar-dark bg-dark px-3 mb-4">
  <a class="navbar-brand fw-bold me-3" href="/hell">⚡</a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#hellNav">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="hellNav">
    <ul class="navbar-nav me-auto">
      <li class="nav-item"><a class="nav-link<?= $a('/hell/mecze') ?>" href="/hell/mecze">Mecze</a></li>
      <li class="nav-item"><a class="nav-link<?= $a('/hell/pytania') ?>" href="/hell/pytania">Pytania</a></li>
      <li class="nav-item"><a class="nav-link<?= $a('/hell/gracze') ?>" href="/hell/gracze">Gracze</a></li>
      <li class="nav-item">
        <a class="nav-link<?= (str_starts_with($uri,'/hell/kampanie')||str_starts_with($uri,'/hell/digest'))?' active':'' ?>"
           href="/hell/kampanie">Maile</a>
      </li>
      <li class="nav-item"><a class="nav-link<?= $a('/hell/turnieje') ?>" href="/hell/turnieje">Turnieje</a></li>
    </ul>
    <a href="/wyniki" class="btn btn-sm btn-outline-warning" target="_blank">Wyniki ↗</a>
  </div>
</nav>
