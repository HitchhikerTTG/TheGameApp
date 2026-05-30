<?php
function renderMarkdown(string $src): string {
    $s = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');
    $s = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="img-fluid rounded my-2">', $s);
    $s = preg_replace('/^### (.+)$/m', '<h5>$1</h5>', $s);
    $s = preg_replace('/^## (.+)$/m',  '<h4>$1</h4>', $s);
    $s = preg_replace('/^# (.+)$/m',   '<h3>$1</h3>', $s);
    $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s);
    $s = preg_replace('/\*(.+?)\*/s',     '<em>$1</em>',         $s);
    $s = preg_replace('/`(.+?)`/',        '<code>$1</code>',     $s);
    $s = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $s);
    $paragraphs = preg_split('/\n{2,}/', trim($s));
    $html = '';
    foreach ($paragraphs as $p) {
        $p = trim($p);
        if ($p === '') continue;
        $html .= preg_match('/^<h[1-6]/', $p) ? nl2br($p) : '<p>' . nl2br($p) . '</p>';
    }
    return $html;
}
?>

<?php if (!empty($notatki)): ?>
<hr class="my-3" style="border-color:var(--bs-border-color);">
<p class="section-label mb-2">Ogłoszenia</p>

<div class="card match-card mb-4" id="notatki-card">
  <div class="px-3 pt-3 pb-1">

    <div class="oglos-badge">📣 Ogłoszenie</div>

    <?php foreach ($notatki as $idx => $n): ?>
    <div class="notatka-a" data-idx="<?= $idx ?>"
         <?= $idx > 0 ? 'style="display:none;"' : '' ?>>
      <?= renderMarkdown($n['tresc']) ?>
    </div>
    <?php endforeach; ?>

    <div class="notatka-meta">
      <span class="notatka-date"><?= esc(substr($notatki[0]['created_at'], 0, 10)) ?></span>
      &nbsp;·&nbsp;
      <span class="notatka-counter">1 / <?= count($notatki) ?></span>
    </div>

  </div>

  <?php if (count($notatki) > 1): ?>
  <div class="notatka-nav">
    <button class="btn-nav" onclick="notatkiNav(1)">‹ Poprzednia</button>
    <button class="btn-nav" id="notatki-next-btn" onclick="notatkiNav(-1)" disabled>Następna ›</button>
  </div>
  <?php endif; ?>
</div>

<script>
(function () {
  var card    = document.getElementById('notatki-card');
  var items   = card.querySelectorAll('.notatka-a');
  var dates   = <?= json_encode(array_column(array_values($notatki), 'created_at')) ?>;
  var total   = items.length;
  var current = 0;

  function sync() {
    card.querySelector('.notatka-date').textContent    = dates[current].slice(0, 10);
    card.querySelector('.notatka-counter').textContent = (current + 1) + ' / ' + total;
    var btnPrev = card.querySelector('[onclick="notatkiNav(1)"]');
    var btnNext = document.getElementById('notatki-next-btn');
    if (btnPrev) btnPrev.disabled = (current >= total - 1);
    if (btnNext) btnNext.disabled = (current <= 0);
  }

  window.notatkiNav = function (dir) {
    var next = current + dir;
    if (next < 0 || next >= total) return;
    items[current].style.display = 'none';
    items[next].style.display    = '';
    current = next;
    sync();
  };
})();
</script>
<?php endif; ?>
