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
      <div class="py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <?php if (!empty($szczegolyMeczow)): ?>
        <details>
          <summary class="d-flex justify-content-between" style="cursor:pointer;list-style:none;">
            <span class="text-secondary" style="font-size:14px;">Punkty za mecze</span>
            <strong><?= (int)$pktMecze ?></strong>
          </summary>
          <div class="mt-2">
            <?php foreach ($szczegolyMeczow as $m): ?>
            <div class="d-flex justify-content-between align-items-center py-2" style="border-top:1px solid var(--bs-border-color);font-size:13px;">
              <div>
                <?= esc($m['HomeName']) ?> <?= (int)$m['ScoreHome'] ?>:<?= (int)$m['ScoreAway'] ?> <?= esc($m['AwayName']) ?>
                <div class="text-secondary" style="font-size:12px;">
                  Twój typ: <?= (int)$m['HomeTyp'] ?>:<?= (int)$m['AwayTyp'] ?><?= $m['GoldenGame'] ? ' ⚽' : '' ?>
                </div>
              </div>
              <strong <?= $m['pkt'] > 0 ? 'style="color:var(--ty-green);"' : '' ?>><?= (int)$m['pkt'] ?> pkt</strong>
            </div>
            <?php endforeach ?>
          </div>
        </details>
        <?php else: ?>
        <div class="d-flex justify-content-between">
          <span class="text-secondary" style="font-size:14px;">Punkty za mecze</span>
          <strong><?= (int)$pktMecze ?></strong>
        </div>
        <?php endif ?>
      </div>

      <div class="py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <?php if (!empty($szczegolyPytan)): ?>
        <details>
          <summary class="d-flex justify-content-between" style="cursor:pointer;list-style:none;">
            <span class="text-secondary" style="font-size:14px;">Punkty za pytania</span>
            <strong><?= (int)$pktPytania ?></strong>
          </summary>
          <div class="mt-2">
            <?php foreach ($szczegolyPytan as $p): ?>
            <div class="d-flex justify-content-between align-items-center py-2" style="border-top:1px solid var(--bs-border-color);font-size:13px;">
              <div>
                <?= esc($p['tresc']) ?>
                <div class="text-secondary" style="font-size:12px;">
                  Twoja odp.: <?= esc($p['mojaOdp']) ?> · Poprawna: <?= esc($p['poprawna']) ?>
                </div>
              </div>
              <strong <?= $p['pktZdobyte'] > 0 ? 'style="color:var(--ty-green);"' : '' ?>><?= (int)$p['pktZdobyte'] ?> / <?= (int)$p['pktMax'] ?> pkt</strong>
            </div>
            <?php endforeach ?>
          </div>
        </details>
        <?php else: ?>
        <div class="d-flex justify-content-between">
          <span class="text-secondary" style="font-size:14px;">Punkty za pytania</span>
          <strong><?= (int)$pktPytania ?></strong>
        </div>
        <?php endif ?>
      </div>

            <?php if ($liczbaTypow > 0): ?>
      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Trafnych typów (z pkt)</span>
        <strong><?= (int)$trafieniaKierunkowe ?> / <?= (int)$liczbaTypow ?>
          <span class="text-secondary fw-normal">(<?= $skutecznoscKierunkowa ?>%)</span>
        </strong>
      </div>
      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Skuteczność dokładna</span>
        <strong><?= (int)$dokladne ?> / <?= (int)$liczbaTypow ?>
          <span class="text-secondary fw-normal">(<?= $skutecznoscDokladna ?>%)</span>
        </strong>
      </div>
      <div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Średnio pkt / mecz</span>
        <strong><?= $srednioPktNaMecz ?>
          <span class="text-secondary fw-normal" style="font-size:13px;">
            (śr. turnieju: <?= $srednieTurnieju['sredniaPktNaMecz'] ?>)
          </span>
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

<div class="d-flex justify-content-between py-2" style="border-bottom:1px solid var(--bs-border-color);">
        <span class="text-secondary" style="font-size:14px;">Obecna passa</span>
        <strong <?= $serie['obecna'] > 0 ? 'style="color:var(--ty-green);"' : '' ?>>
          <?= (int)$serie['obecna'] ?> meczów<?= $serie['obecna'] > 2 ? ' 🔥' : '' ?>
        </strong>
      </div>

      <?php if ($najczestszyWynikGracza): ?>
      <div class="d-flex justify-content-between py-2">
        <span class="text-secondary" style="font-size:14px;">Najczęściej wpisywany wynik</span>
        <strong class="ff-bebas" style="font-size:18px;">
          <?= (int)$najczestszyWynikGracza->HomeTyp ?> : <?= (int)$najczestszyWynikGracza->AwayTyp ?>
          <span class="text-secondary fw-normal" style="font-size:13px;">(<?= (int)$najczestszyWynikGracza->liczba ?>×)</span>
        </strong>
      </div>
      <?php endif ?>

    </div>
  </div>

  <!-- NAJLEPSZY / NAJGORSZY MECZ -->
  <?php if ($najlepszyMecz): ?>
  <div class="d-flex gap-2 mb-3">
    <div class="card match-card flex-fill">
      <div class="card-body px-3 py-3 text-center">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Najlepszy mecz</div>
        <div style="font-size:14px;margin-top:4px;"><?= esc($najlepszyMecz['HomeName']) ?> – <?= esc($najlepszyMecz['AwayName']) ?></div>
        <div class="ff-bebas" style="font-size:22px;color:var(--ty-green);"><?= (int)$najlepszyMecz['pkt'] ?> pkt</div>
      </div>
    </div>
    <div class="card match-card flex-fill">
      <div class="card-body px-3 py-3 text-center">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);">Najgorszy mecz</div>
        <div style="font-size:14px;margin-top:4px;"><?= esc($najgorszyMecz['HomeName']) ?> – <?= esc($najgorszyMecz['AwayName']) ?></div>
        <div class="ff-bebas" style="font-size:22px;"><?= (int)$najgorszyMecz['pkt'] ?> pkt</div>
      </div>
    </div>
  </div>
  <?php endif ?>

  <!-- TREND PUNKTOWY -->
  <?php if (count($trendPunktowy) > 1): ?>
  <div class="card match-card mb-3">
    <div class="card-body px-3 py-3">
      <div style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;color:var(--bs-secondary-color);margin-bottom:8px;">
        Trend punktowy (suma narastająco)
      </div>
      <?php
        $max = max($trendPunktowy) ?: 1;
        $w = 300; $h = 60; $n = count($trendPunktowy);
        $points = [];
        foreach ($trendPunktowy as $i => $v) {
            $x = $n > 1 ? ($i / ($n - 1)) * $w : 0;
            $y = $h - ($v / $max) * $h;
            $points[] = round($x, 1) . ',' . round($y, 1);
        }
      ?>
      <svg viewBox="0 0 <?= $w ?> <?= $h ?>" style="width:100%;height:60px;" preserveAspectRatio="none">
        <polyline points="<?= esc(implode(' ', $points), 'attr') ?>" fill="none" stroke="var(--ty-accent)" stroke-width="2"/>
      </svg>
    </div>
  </div>
  <?php endif ?>


