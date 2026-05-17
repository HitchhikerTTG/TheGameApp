<?php $lastDate = null; ?>

<?php foreach ($mecze as $match):
    if (!isset($match['details'])) continue;

    $matchDate    = date('Y-m-d', strtotime($match['details']['date']));
    $naszCzas     = date('H:i',   strtotime($match['details']['naszCzas']));
    $homeTeamName = $match['details']['home_team']['plName'] ?? $match['details']['home_team']['name'] ?? '?';
    $awayTeamName = $match['details']['away_team']['plName'] ?? $match['details']['away_team']['name'] ?? '?';

    $statusRaw  = $match['details']['status'] ?? '';
    $isFinished = ($statusRaw === 'Zakonczony');
    $isLive     = ($match['rozpoczety'] == 1 && !$isFinished);
    $isUpcoming = ($match['rozpoczety'] == 0);

    $homeScore = $match['details']['home_team']['score'] ?? null;
    $awayScore = $match['details']['away_team']['score'] ?? null;
    $userHome  = $match['typy']['HomeTyp'] ?? null;
    $userAway  = $match['typy']['AwayTyp'] ?? null;
    $isGolden  = ($match['Id'] == $usedGoldenBall);
    $userPkt   = $match['typy']['pkt'] ?? null;

    $isExact = $isFinished && $userHome !== null
        && (string)$userHome === (string)$homeScore
        && (string)$userAway === (string)$awayScore;

    /* ── DATE GROUP HEADER ── */
    if ($lastDate !== $matchDate):
        $lastDate = $matchDate;
        $dayFormatted = (new DateTime($matchDate))->format('l, j F');
?>
  <p class="section-label mt-4 mb-1">Mecze</p>
  <div class="d-flex align-items-baseline gap-2 mb-3">
    <span class="ff-bebas day-date"><?= $dayFormatted ?></span>
    <span class="text-secondary" style="font-size:13px;"><?= esc($match['details']['competition'] ?? '') ?></span>
  </div>
<?php endif; ?>

<div class="card match-card mb-3">

  <!-- HEAD -->
  <div class="match-head d-flex align-items-center justify-content-between px-3 py-2">
    <span class="match-time">
      <?= $naszCzas ?>
      <?php if ($isLive && isset($match['details']['minute'])): ?>
        · <span style="color:var(--ty-red)">●</span> <?= (int)$match['details']['minute'] ?>'
      <?php elseif ($isFinished): ?>
        · Zakończony
      <?php endif; ?>
    </span>
    <?php if ($isUpcoming): ?>
      <span class="status-badge status-upcoming">Przyjmuje typy</span>
    <?php elseif ($isLive): ?>
      <span class="status-badge status-live">Na żywo</span>
    <?php elseif ($isFinished): ?>
      <span class="status-badge status-done">
        <?= $userPkt !== null ? '+' . $userPkt . ' pkt' : 'Zakończony' ?>
      </span>
    <?php else: ?>
      <span class="status-badge status-locked">Zamknięty</span>
    <?php endif; ?>
  </div>

  <div class="card-body px-3 py-3">

    <?php if ($isUpcoming): /* ── UPCOMING: stepper + form ── */ ?>

      <form action="/theGame/nowyZapisTypu" method="post" class="betting-form">
        <input type="hidden" name="userUID"   value="<?= $userID ?>">
        <input type="hidden" name="gameID"    value="<?= $match['Id'] ?>">
        <input type="hidden" name="turniejID" value="<?= $turniejID ?>">

        <div class="d-grid mb-1" style="grid-template-columns:1fr 20px 1fr; gap:8px;">
  <div class="text-center team-name"><?= esc($homeTeamName) ?></div>
  <div></div>
  <div class="text-center team-name"><?= esc($awayTeamName) ?></div>
</div>
<div class="d-grid mb-3" style="grid-template-columns:1fr 20px 1fr; gap:8px; align-items:center;">
  <div class="team h_<?= $match['details']['home_team']['id'] ?? '' ?>">
    <div class="stepper">
      <button type="button" class="step-btn minus">−</button>
      <span class="ff-bebas step-val"><?= (int)($userHome ?? 0) ?></span>
      <button type="button" class="step-btn plus">+</button>
    </div>
    <input type="hidden" name="H" class="score-value" value="<?= (int)($userHome ?? 0) ?>">
  </div>
  <div class="ff-bebas text-center vs-div">:</div>
  <div class="team a_<?= $match['details']['away_team']['id'] ?? '' ?>">
    <div class="stepper">
      <button type="button" class="step-btn minus">−</button>
      <span class="ff-bebas step-val"><?= (int)($userAway ?? 0) ?></span>
      <button type="button" class="step-btn plus">+</button>
    </div>
    <input type="hidden" name="A" class="score-value" value="<?= (int)($userAway ?? 0) ?>">
  </div>
</div>


        <?php
        if ($usedGoldenBall == 0)               { $goldenLabel = '⚽ Złota piłka -- 2× punkty'; $goldenDisabled = false; }
        elseif ($usedGoldenBall == $match['Id']) { $goldenLabel = '⚽ Złota piłka -- 2× punkty'; $goldenDisabled = false; }
        else                                     { $goldenLabel = 'Złota piłka użyta na inny mecz'; $goldenDisabled = true; }
        ?>
        <div class="golden-row mb-3 <?= $isGolden ? 'active' : '' ?> <?= $goldenDisabled ? 'disabled-golden' : '' ?>"
             id="golden-row-<?= $match['Id'] ?>"
             <?= !$goldenDisabled ? 'onclick="typerToggleGolden(' . $match['Id'] . ')"' : '' ?>>
          <span class="golden-label"><?= $goldenLabel ?></span>
          <div class="form-check form-switch mb-0 pe-none">
            <input class="form-check-input golden-game-checkbox" type="checkbox"
                   id="goldenGame<?= $match['Id'] ?>" name="goldenGame" value="1"
                   data-game-id="<?= $match['Id'] ?>"
                   <?= $isGolden ? 'checked' : '' ?>
                   <?= $goldenDisabled ? 'disabled' : '' ?>
                   onclick="event.stopPropagation()">
          </div>
        </div>

        <?php if (!empty($match['details']['odds'])): ?>
        <div class="d-flex gap-2 mb-3">
          <div class="odd-pill"><div class="odd-label"><?= esc($homeTeamName) ?></div><div class="odd-val"><?= $match['details']['odds']['1'] ?? 'N/A' ?></div></div>
          <div class="odd-pill"><div class="odd-label">Remis</div><div class="odd-val"><?= $match['details']['odds']['X'] ?? 'N/A' ?></div></div>
          <div class="odd-pill"><div class="odd-label"><?= esc($awayTeamName) ?></div><div class="odd-val"><?= $match['details']['odds']['2'] ?? 'N/A' ?></div></div>
        </div>
        <?php endif; ?>

        <button type="submit"
                class="btn-type ff-bebas <?= $userHome !== null ? 'done' : '' ?>"
                id="btn-submit-<?= $match['Id'] ?>">
          <?= $userHome !== null ? '✓ Wytypowano' : 'Typuję' ?>
        </button>
        <?php if ($match['liczbaTypow'] > 0): ?>
          <p class="social-proof text-center mt-2 mb-0"><?= (int)$match['liczbaTypow'] ?> graczy już wytypowało</p>
        <?php endif; ?>
      </form>

    <?php else: /* ── LIVE or FINISHED: wynik ── */ ?>

      <div class="d-grid mb-2" style="grid-template-columns:1fr auto 1fr; gap:12px; align-items:center;">
        <div class="text-center">
          <div class="team-name mb-1"><?= esc($homeTeamName) ?></div>
          <?php if ($homeScore !== null): ?><div class="ff-bebas score-display"><?= (int)$homeScore ?></div><?php endif; ?>
        </div>
        <div class="ff-bebas vs-div">:</div>
        <div class="text-center">
          <div class="team-name mb-1"><?= esc($awayTeamName) ?></div>
          <?php if ($awayScore !== null): ?><div class="ff-bebas score-display"><?= (int)$awayScore ?></div><?php endif; ?>
        </div>
      </div>

      <?php if ($userHome !== null): ?>
        <p class="text-center mb-0" style="font-size:13px;">
          Twój typ: <strong><?= (int)$userHome ?> : <?= (int)$userAway ?></strong>
          <?php if ($isGolden): ?>&nbsp;·&nbsp;<span class="chip-green">⚽ Złota</span><?php endif; ?>
          <?php if ($isExact): ?>&nbsp;·&nbsp;<span style="color:var(--ty-green);font-weight:600;">✓ Dokładnie!</span><?php endif; ?>
        </p>
      <?php endif; ?>

    <?php endif; ?>
  </div><!-- /.card-body -->

  <!-- COLLAPSE: jak typowali? -->
  <?php if ($match['rozpoczety'] && !empty($match['typyGraczy'])): ?>
    <div class="collapse-trigger" onclick="typerToggleResults(<?= $match['ApiID'] ?>)">
      <span><?= $isFinished ? 'Wyniki graczy' : 'Jak typowali inni?' ?></span>
      <span id="arrow-<?= $match['ApiID'] ?>">›</span>
    </div>
    <div id="results-<?= $match['ApiID'] ?>" class="px-3 pb-3" style="display:none;">
      <div class="results-row" style="font-size:11px;color:var(--bs-tertiary-color);font-weight:700;text-transform:uppercase;">
        <div>#</div><div>Nick</div><div>Typ</div><div>Pkt</div>
      </div>
      <?php $pos = 1; foreach ($match['typyGraczy'] as $typ):
            $isMe = ($typ['username'] === session()->get('username')); ?>
        <div class="results-row">
          <div class="res-pos"><?= $pos++ ?></div>
          <div class="res-nick <?= $isMe ? 'res-me' : '' ?>"><?= esc($typ['username']) ?><?= $isMe ? ' ← Ty' : '' ?></div>
          <div class="res-type"><?= (int)$typ['HomeTyp'] ?>:<?= (int)$typ['AwayTyp'] ?><?= $typ['GoldenGame'] == 1 ? ' ⚽' : '' ?></div>
          <div class="res-pts ff-bebas"><?= isset($typ['pkt']) ? $typ['pkt'] : '--' ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</div><!-- /.match-card -->
<?php endforeach; ?>

<div class="d-flex gap-2 mt-3">
  <a href="/wszystkieMecze" class="btn btn-outline-secondary btn-sm flex-fill text-center">Wszystkie typy &raquo;</a>
  <a href="/archiwumturnieju" class="btn btn-outline-secondary btn-sm flex-fill text-center">Archiwum meczów &raquo;</a>
</div>
