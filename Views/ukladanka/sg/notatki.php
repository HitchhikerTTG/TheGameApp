<?php
/**
 * Minimal markdown → HTML (server-side, no external lib).
 * Obsługuje: # nagłówki, **bold**, *italic*, `code`, \n\n akapity.
 */
function renderMarkdown(string $src): string {
    $s = htmlspecialchars($src, ENT_QUOTES, 'UTF-8');
    // Nagłówki
    $s = preg_replace('/^### (.+)$/m', '<h5 class="mt-3 mb-1">$1</h5>', $s);
    $s = preg_replace('/^## (.+)$/m',  '<h4 class="mt-3 mb-1">$1</h4>', $s);
    $s = preg_replace('/^# (.+)$/m',   '<h3 class="mt-3 mb-1">$1</h3>', $s);
    // Bold / italic / code
    $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s);
    $s = preg_replace('/\*(.+?)\*/s',     '<em>$1</em>',         $s);
    $s = preg_replace('/`(.+?)`/',        '<code>$1</code>',     $s);
    // Podwójny newline → nowy akapit
    $paragraphs = preg_split('/\n{2,}/', trim($s));
    $html = '';
    foreach ($paragraphs as $p) {
        $p = trim($p);
        if ($p === '') continue;
        // Jeśli to nagłówek, nie owijaj w <p>
        if (preg_match('/^<h[1-6]/', $p)) {
            $html .= nl2br($p);
        } else {
            $html .= '<p class="mb-2">' . nl2br($p) . '</p>';
        }
    }
    return $html;
}
?>

<?php if (!empty($notatki)): ?>
<hr class="my-3" style="border-color:var(--bs-border-color);">
<p class="section-label mb-2">Ogłoszenia</p>

<div class="card match-card mb-3">

  <?php foreach ($notatki as $idx => $n): ?>
  <div class="notatka-item px-3 pt-3 pb-2" data-idx="<?= $idx ?>"
       style="<?= $idx > 0 ? 'display:none;' : '' ?>">
    <div style="font-size:15px;line-height:1.6;">
      <?= renderMarkdown($n['tresc']) ?>
    </div>
    <div class="text-end mt-1" style="font-size:12px;color:var(--bs-tertiary-color);">
      <?= esc(substr($n['created_at'], 0, 10)) ?>
      &nbsp;·&nbsp;
      <span class="notatka-counter"><?= ($idx + 1) ?> / <?= count($notatki) ?></span>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (count($notatki) > 1): ?>
  <div class="d-flex justify-content-between px-3 py-2"
       style="border-top:1px solid var(--bs-border-color);font-size:13px;">
    <button class="btn-type ff-bebas action-btn"
            onclick="notatkiNav(1)"
            style="font-size:14px;padding:6px 12px;">
      ‹ Poprzednia
    </button>
    <button class="btn-type ff-bebas action-btn"
            id="notatki-next-btn"
            onclick="notatkiNav(-1)"
            style="font-size:14px;padding:6px 12px;"
            disabled>
      Następna ›
    </button>
  </div>
  <?php endif; ?>

</div>

<script>
(function() {
  var items   = document.querySelectorAll('.notatka-item');
  var total   = items.length;
  var current = 0;

  window.notatkiNav = function(dir) {
    var next = current + dir;
    if (next < 0 || next >= total) return;
    items[current].style.display = 'none';
    items[next].style.display    = '';
    current = next;

    var prevBtn = document.querySelector('[onclick="notatkiNav(1)"]');
    var nextBtn = document.getElementById('notatki-next-btn');
    if (prevBtn) prevBtn.disabled = (current >= total - 1);
    if (nextBtn) nextBtn.disabled = (current <= 0);
  };
})();
</script>
<?php endif; ?>
