<?php if (empty($pytania)): ?>
  <p class="text-secondary mt-4" style="font-size:14px;">Brak archiwalnych pytań.</p>
<?php else: ?>

<hr class="my-3" style="border-color:var(--bs-border-color);">
<p class="section-label mb-2">Archiwum pytań</p>

<?php foreach ($pytania as $pytanie):
    $hasAnswer   = !empty($pytanie['dotychczasowa_odpowiedz']);
    $rightAnswer = !empty($pytanie['odpowiedz']);
    $odpowiedzi  = $pytanie['odpowiedzi'] ?? [];

    // Czy gracz dostał punkty?
    $userPkt = null;
    foreach ($odpowiedzi as $o) {
        if ($o['nick'] === session()->get('username')) {
            $userPkt = (int)$o['pkt'];
            break;
        }
    }
?>

<div class="card match-card mb-3">
  <div class="match-head d-flex align-items-center justify-content-between px-3 py-2">
    <span class="match-time">Pytanie · <?= (int)$pytanie['pkt'] ?> pkt</span>
    <?php if ($userPkt !== null): ?>
      <span class="status-badge <?= $userPkt > 0 ? 'status-scored' : 'status-done' ?>">
        <?= $userPkt > 0 ? '+' . $userPkt . ' pkt' : '0 pkt' ?>
      </span>
    <?php elseif (!$hasAnswer): ?>
      <span class="status-badge status-locked">Brak odpowiedzi</span>
    <?php else: ?>
      <span class="status-badge status-upcoming">Nieprzeliczone</span>
    <?php endif ?>
  </div>

  <div class="card-body px-3 py-3">
    <p style="font-size:16px;font-weight:500;line-height:1.4;" class="mb-2">
      <?= esc($pytanie['tresc']) ?>
    </p>

    <?php if (!empty($pytanie['opis'])): ?>
      <p style="font-size:13px;color:var(--bs-secondary-color);line-height:1.4;" class="mb-1">
        <?= esc($pytanie['opis']) ?>
      </p>
    <?php endif ?>
    <?php if (!empty($pytanie['zrodlo'])): ?>
      <p style="font-size:12px;color:var(--bs-tertiary-color);" class="mb-2">
        Źródło: <?= esc($pytanie['zrodlo']) ?>
      </p>
    <?php endif ?>

    <div class="d-flex flex-column gap-1 mt-2" style="font-size:13px;">
      <?php if ($rightAnswer): ?>
        <div>
          <span class="text-secondary">Prawidłowa odpowiedź:</span>
          <strong><?= esc($pytanie['odpowiedz']) ?></strong>
        </div>
      <?php endif ?>
      <div>
        <span class="text-secondary">Twoja odpowiedź:</span>
        <?php if ($hasAnswer): ?>
          <strong><?= esc($pytanie['dotychczasowa_odpowiedz']) ?></strong>
        <?php else: ?>
          <span class="text-danger">nie udzielono</span>
        <?php endif ?>
      </div>
    </div>
  </div>

  <?php if (!empty($odpowiedzi)): ?>
  <div class="collapse-trigger" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'none' ? 'block' : 'none'">
    <span>Odpowiedzi graczy (<?= count($odpowiedzi) ?>)</span>
    <span>›</span>
  </div>
  <div style="display:none;">
    <div class="px-3 pb-3">
      <div class="results-row" style="font-size:11px;color:var(--bs-tertiary-color);font-weight:700;text-transform:uppercase;">
        <div>Nick</div><div>Odpowiedź</div><div>Pkt</div>
      </div>
      <?php foreach ($odpowiedzi as $o):
            $isMe = ($o['nick'] === session()->get('username')); ?>
        <div class="results-row">
          <div class="res-nick <?= $isMe ? 'res-me' : '' ?>"><?= esc($o['nick']) ?><?= $isMe ? ' ← Ty' : '' ?></div>
          <div class="res-type"><?= esc($o['odp']) ?></div>
          <div class="res-pts ff-bebas"><?= $o['pkt'] > 0 ? '+' . (int)$o['pkt'] : '–' ?></div>
        </div>
      <?php endforeach ?>
    </div>
  </div>
  <?php endif ?>

</div>

<?php endforeach ?>
<?php endif ?>