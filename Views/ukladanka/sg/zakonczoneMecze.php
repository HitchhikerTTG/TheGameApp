<?php $lastDate = null; ?>

<?php foreach ($mecze as $match):
    if (!isset($match['details'])) continue;

    $matchDate    = $match['details']['naszaData'] ?? date('Y-m-d', strtotime($match['details']['date']));
    $naszCzas     = date('H:i', strtotime($match['details']['naszCzas']));
    $homeTeamName = $match['details']['home_team']['plName'] ?? $match['details']['home_team']['name'] ?? '?';
    $awayTeamName = $match['details']['away_team']['plName'] ?? $match['details']['away_team']['name'] ?? '?';
    $homeScore    = $match['details']['home_team']['score'] ?? $match['ScoreHome'] ?? '?';
    $awayScore    = $match['details']['away_team']['score'] ?? $match['ScoreAway'] ?? '?';
    $userHome     = $match['typy']['HomeTyp'] ?? null;
    $userAway     = $match['typy']['AwayTyp'] ?? null;
    $userPkt      = $match['typy']['pkt'] ?? null;
    $isGolden     = ($match['Id'] == $usedGoldenBall);

    $isExact = $userHome !== null
        && (string)$userHome === (string)$homeScore
        && (string)$userAway === (string)$awayScore;

    if ($lastDate !== $matchDate):
        $lastDate = $matchDate;
        $dayFormatted = (new DateTime($matchDate))->format('l, j F');
?>
  <p class="section-label mt-4 mb-1">Archiwum</p>
  <div class="d-flex align-items-baseline gap-2 mb-3">
    <span class="ff-bebas day-date"><?= $dayFormatted ?></span>
    <span class="text-secondary" style="font-size:13px;"><?= esc($match['details']['competition'] ?? '') ?></span>
  </div>
<?php endif ?>

<div class="card match-card mb-3">

  <div class="match-head d-flex align-items-center justify-content-between px-3 py-2">
    <span class="match-time"><?= $naszCzas ?> · Zakończony</span>
    <span class="status-badge status-scored">
      <?= $userPkt !== null ? '+' . $userPkt . ' pkt' : 'Przeliczony' ?>
    </span>
  </div>

  <div class="card-body px-3 py-3">
    <div class="d-grid mb-2" style="grid-template-columns:1fr auto 1fr; gap:12px; align-items:center;">
      <div class="text-center">
        <div class="team-name mb-1"><?= esc($homeTeamName) ?></div>
        <div class="ff-bebas score-display"><?= (int)$homeScore ?></div>
      </div>
      <div class="ff-bebas vs-div">:</div>
      <div class="text-center">
        <div class="team-name mb-1"><?= esc($awayTeamName) ?></div>
        <div class="ff-bebas score-display"><?= (int)$awayScore ?></div>
      </div>
    </div>

    <?php if ($userHome !== null): ?>
      <p class="text-center mb-0" style="font-size:13px;">
        Twój typ: <strong><?= (int)$userHome ?> : <?= (int)$userAway ?></strong>
        <?php if ($isGolden): ?>&nbsp;·&nbsp;<span class="chip-green">⚽ Złota</span><?php endif ?>
        <?php if ($isExact): ?>&nbsp;·&nbsp;<span style="color:var(--ty-green);font-weight:600;">✓ Dokładnie!</span><?php endif ?>
      </p>
    <?php else: ?>
      <p class="text-center mb-0 text-secondary" style="font-size:13px;">Brak typu</p>
    <?php endif ?>
  </div>

  <?php if (!empty($match['typyGraczy'])): ?>
    <div class="collapse-trigger" onclick="typerToggleResults(<?= $match['ApiID'] ?>)">
      <span>Wyniki graczy</span>
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
          <div class="res-nick <?= $isMe ? 'res-me' : '' ?>"><?= !empty($typ['emoji']) ? esc($typ['emoji']) . ' ' : '' ?><?= esc($typ['username']) ?><?= $isMe ? ' ← Ty' : '' ?></div>
          <div class="res-type"><?= (int)$typ['HomeTyp'] ?>:<?= (int)$typ['AwayTyp'] ?><?= $typ['GoldenGame'] == 1 ? ' ⚽' : '' ?></div>
          <div class="res-pts ff-bebas"><?= isset($typ['pkt']) ? $typ['pkt'] : '--' ?></div>
        </div>
      <?php endforeach ?>
    </div>
  <?php endif ?>

</div>
<?php endforeach ?>

<div class="d-flex gap-2 mt-3">
  <a href="/typowanie" class="btn btn-outline-secondary btn-sm flex-fill text-center">← Wróć do typowania</a>
</div>