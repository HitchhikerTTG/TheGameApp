<div class="px-3 py-4" style="max-width:680px;margin:0 auto;">

  <div class="d-flex align-items-baseline justify-content-between mb-4">
    <div>
      <p class="section-label mb-0">Statystyki</p>
      <h2 class="ff-bebas mb-0" style="font-size:28px;"><?= esc($turniejName) ?></h2>
    </div>
    <?php if (session()->get('isAdmin')): ?>
      <a href="/statystyki/przelicz" class="btn btn-sm btn-outline-secondary">↺ Przelicz</a>
    <?php endif ?>
  </div>

  <?php if (empty($statystyki)): ?>
    <p class="text-secondary">Brak danych -- statystyki zostaną obliczone po przeliczeniu pierwszych meczów.</p>
  <?php else: ?>

  <?php
    // helper lokalny
    $mecz = fn(?array $m, string $pole = 'liczba', string $suffix = '') =>
        $m ? esc($m['HomeName']) . ' – ' . esc($m['AwayName'])
           . ' <span class="text-secondary">(' . $m['ScoreHome'] . ':' . $m['ScoreAway'] . ')</span>'
           . ' <strong>' . $m[$pole] . $suffix . '</strong>'
        : '<em class="text-secondary">brak danych</em>';
  ?>

  <!-- MECZE -->
  <p class="section-label mt-4 mb-2">Mecze</p>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Mecz z największą liczbą typów</div>
      <div class="stat-value"><?= $mecz($statystyki['meczNajwiecejTypow'], 'liczba', ' typów') ?></div>
    </div>
  </div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Mecz z największą liczbą dokładnych trafień</div>
      <div class="stat-value"><?= $mecz($statystyki['meczNajwiecejTrafien'], 'liczba', ' trafień') ?></div>
    </div>
  </div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Mecz który przyznał graczom najwięcej punktów łącznie</div>
      <div class="stat-value"><?= $mecz($statystyki['meczNajwiecejPkt'], 'suma', ' pkt') ?></div>
    </div>
  </div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Mecz z największą liczbą użytych złotych piłek ⚽</div>
      <div class="stat-value"><?= $mecz($statystyki['meczNajwiecejGoldenUzytych'], 'liczba', ' razy') ?></div>
    </div>
  </div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Mecz w którym trafiono najwięcej złotych piłek ⚽✓</div>
      <div class="stat-value"><?= $mecz($statystyki['meczNajwiecejGoldenTrafiony'], 'liczba', ' trafień') ?></div>
    </div>
  </div>

  <!-- TYPY -->
  <p class="section-label mt-4 mb-2">Typy graczy</p>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Najpopularniejszy trafiony wynik</div>
      <?php $t = $statystyki['najpopularniejszyTrafiony']; ?>
      <div class="stat-value">
        <?= $t
          ? '<strong class="ff-bebas" style="font-size:22px;">' . (int)$t['HomeTyp'] . ' : ' . (int)$t['AwayTyp'] . '</strong>'
            . ' <span class="text-secondary small">(' . $t['liczba'] . ' razy)</span>'
          : '<em class="text-secondary">brak danych</em>' ?>
      </div>
    </div>
  </div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Najpopularniejszy nie trafiony wynik 😬</div>
      <?php $t = $statystyki['najpopularniejszyNieTrafiony']; ?>
      <div class="stat-value">
        <?= $t
          ? '<strong class="ff-bebas" style="font-size:22px;">' . (int)$t['HomeTyp'] . ' : ' . (int)$t['AwayTyp'] . '</strong>'
            . ' <span class="text-secondary small">(' . $t['liczba'] . ' razy)</span>'
          : '<em class="text-secondary">brak danych</em>' ?>
      </div>
    </div>
  </div>

  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Wynik który zastosowany do wszystkich meczów dałby najwięcej punktów 🤓</div>
      <?php $t = $statystyki['najskuteczniejszyWynik']; ?>
      <div class="stat-value">
        <?= $t
          ? '<strong class="ff-bebas" style="font-size:22px;">' . (int)$t['ScoreHome'] . ' : ' . (int)$t['ScoreAway'] . '</strong>'
            . ' <span class="text-secondary small">(padł ' . $t['liczba'] . ' razy)</span>'
          : '<em class="text-secondary">brak danych</em>' ?>
      </div>
    </div>
  </div>

  <!-- ROZKŁAD TRAFIEŃ -->
  <p class="section-label mt-4 mb-2">Rozkład dokładnych trafień</p>
  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <?php
        $r = $statystyki['rozkładTrafien'];
        $max = max(array_keys($r) ?: [0]);
      ?>
      <div class="d-flex gap-3 flex-wrap">
        <div class="text-center">
          <div class="ff-bebas" style="font-size:28px;color:var(--ty-accent);"><?= $r[0] ?? 0 ?></div>
          <div style="font-size:12px;color:var(--bs-secondary-color);">meczów bez trafienia</div>
        </div>
        <div class="text-center">
          <div class="ff-bebas" style="font-size:28px;color:var(--ty-green);"><?= $r[1] ?? 0 ?></div>
          <div style="font-size:12px;color:var(--bs-secondary-color);">meczów z 1 trafieniem</div>
        </div>
        <div class="text-center">
          <div class="ff-bebas" style="font-size:28px;color:var(--ty-green);"><?= $r[2] ?? 0 ?></div>
          <div style="font-size:12px;color:var(--bs-secondary-color);">meczów z 2 trafieniami</div>
        </div>
        <?php if ($max > 2): ?>
        <div class="text-center">
          <div class="ff-bebas" style="font-size:28px;color:var(--ty-green);">
            <?= array_sum(array_filter($r, fn($k) => $k > 2, ARRAY_FILTER_USE_KEY)) ?>
          </div>
          <div style="font-size:12px;color:var(--bs-secondary-color);">meczów z 3+ trafieniami</div>
        </div>
        <?php endif ?>
      </div>
    </div>
  </div>

  <!-- PYTANIA -->
  <?php if (!empty($statystyki['pytanieNajwiecejPoprawnych']) || !empty($statystyki['pytanieNajmniejPoprawnych'])): ?>
  <p class="section-label mt-4 mb-2">Pytania</p>

  <?php if (!empty($statystyki['pytanieNajwiecejPoprawnych'])): $p = $statystyki['pytanieNajwiecejPoprawnych']; ?>
  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Pytanie na które odpowiedziało poprawnie najwięcej graczy 🧠</div>
      <div class="stat-value mt-1"><?= esc($p['tresc']) ?></div>
      <div class="mt-1" style="font-size:13px;">
        Odpowiedź: <strong><?= esc($p['odpowiedz'] ?? '–') ?></strong>
        · <span class="text-secondary"><?= (int)$p['poprawnych'] ?> / <?= (int)$p['wszystkich'] ?> graczy</span>
      </div>
    </div>
  </div>
  <?php endif ?>

  <?php if (!empty($statystyki['pytanieNajmniejPoprawnych'])): $p = $statystyki['pytanieNajmniejPoprawnych']; ?>
  <div class="card match-card mb-2">
    <div class="card-body px-3 py-3">
      <div class="stat-label">Pytanie które zaskoczyło graczy najbardziej 🤯</div>
      <div class="stat-value mt-1"><?= esc($p['tresc']) ?></div>
      <div class="mt-1" style="font-size:13px;">
        Odpowiedź: <strong><?= esc($p['odpowiedz'] ?? '–') ?></strong>
        · <span class="text-secondary"><?= (int)$p['poprawnych'] ?> / <?= (int)$p['wszystkich'] ?> graczy</span>
      </div>
    </div>
  </div>
  <?php endif ?>
  <?php endif ?>

  <p class="text-secondary mt-4" style="font-size:12px;">
    Ostatnia aktualizacja: <?= esc($statystyki['obliczoneAt'] ?? '–') ?>
  </p>

  <?php endif ?>

</div>

<style>
.stat-label { font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);margin-bottom:4px; }
.stat-value { font-size:15px; }
</style>