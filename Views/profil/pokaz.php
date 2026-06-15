<div class="px-3 py-4" style="max-width:640px;margin:0 auto;">

  <!-- NAGŁÓWEK GRACZA -->
  <div class="d-flex align-items-center gap-3 mb-4">
    <div class="ff-bebas" style="font-size:48px;line-height:1;">
      <?= esc($gracz['emoji'] ?? '🙂') ?>
    </div>
    <div>
      <h1 class="ff-bebas mb-0" style="font-size:32px;"><?= esc($gracz['nick']) ?></h1>
      <?php if ($jaMojeKonto): ?>
        <a href="/profil" class="text-secondary" style="font-size:13px;">← Moje ustawienia</a>
      <?php endif ?>
    </div>
  </div>

  <!-- STATYSTYKI AKTYWNEGO TURNIEJU -->
  <p class="section-label mb-2"><?= esc($turniejName) ?></p>

  <!-- Blok punktów -->
  <div class="card match-card mb-3">
    <div class="card-body px-3 py-3">
      <div class="d-grid mb-0" style="grid-template-columns:1fr 1fr 1fr;gap:8px;text-align:center;">
        <div>
          <div class="ff-bebas" style="font-size:36px;color:var(--ty-accent);"><?= (int)$pktLacznie ?></div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Punkty łącznie</div>
        </div>
        <div>
          <div class="ff-bebas" style="font-size:36px;"><?= (int)$rankingPozycja ?>.</div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Miejsce</div>
        </div>
        <div>
          <div class="ff-bebas" style="font-size:36px;color:var(--ty-green);"><?= (int)$dokladne ?></div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Dokładnych</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Szczegóły -->
  <div class="card match-card mb-3">
    <div class="card-body px-3 py-3">

      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Punkty za mecze</span>
        <strong><?= (int)$pktMecze ?></strong>
      </div>
      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Punkty za pytania</span>
        <strong><?= (int)$pktPytania ?></strong>
      </div>

      <?php if ($liczbaTypow > 0): ?>
      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Trafnych typów (z pkt)</span>
        <strong><?= (int)$trafieniaKierunkowe ?> / <?= (int)$liczbaTypow ?>
          <span class="text-secondary fw-normal">(<?= round($trafieniaKierunkowe / $liczbaTypow * 100) ?>%)</span>
        </strong>
      </div>
      <?php endif ?>

      <?php if ($ulubionyWynik): ?>
      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Ulubiony trafiony wynik</span>
        <strong class="ff-bebas" style="font-size:18px;">
          <?= (int)$ulubionyWynik->HomeTyp ?> : <?= (int)$ulubionyWynik->AwayTyp ?>
          <span class="text-secondary fw-normal" style="font-size:13px;">(<?= (int)$ulubionyWynik->liczba ?>×)</span>
        </strong>
      </div>
      <?php endif ?>

      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Złota piłka ⚽</span>
        <strong><?= $goldenTrafiona ?> / <?= $goldenUzyta ?>
          <span class="text-secondary fw-normal" style="font-size:13px;">trafień / użyć</span>
        </strong>
      </div>

      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Najdłuższa passa z punktami 🔥</span>
        <strong><?= (int)$serie['najdluzsza'] ?> meczów</strong>
      </div>

      <div class="d-flex justify-content-between py-2">
        <span class="text-secondary" style="font-size:14px;">Obecna passa</span>
        <strong <?= $serie['obecna'] > 0 ? 'style="color:var(--ty-green);"' : '' ?>>
          <?= (int)$serie['obecna'] ?> meczów<?= $serie['obecna'] > 2 ? ' 🔥' : '' ?>
        </strong>
      </div>

    </div>
  </div>

  <!-- WSZECH CZASY (mini) -->
  <?php if ($turniejeGracza > 0): ?>
  <p class="section-label mt-4 mb-2">Wszech czasów</p>
  <div class="card match-card mb-3">
    <div class="card-body px-3 py-3">
      <div class="d-grid" style="grid-template-columns:1fr 1fr;gap:8px;text-align:center;">
        <div>
          <div class="ff-bebas" style="font-size:32px;"><?= (int)$pktAllTime ?></div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Punkty łącznie</div>
        </div>
        <div>
          <div class="ff-bebas" style="font-size:32px;"><?= (int)$turniejeGracza ?></div>
          <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Turniejów</div>
        </div>
      </div>
      <div class="text-center mt-2">
        <a href="/wszechczasy" style="font-size:13px;color:var(--ty-accent);">Zobacz pełną tabelę wszech czasów →</a>
      </div>
    </div>
  </div>
  <?php endif ?>

  <!-- LINKI -->
  <div class="d-flex gap-2 mt-3">
    <a href="/statystyki" class="btn btn-outline-secondary btn-sm flex-fill text-center">Statystyki turnieju</a>
    <a href="/wszechczasy" class="btn btn-outline-secondary btn-sm flex-fill text-center">Wszech czasów</a>
  </div>

</div>