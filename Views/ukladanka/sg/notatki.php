<?php if (!empty($notatki)): ?>
<hr class="my-3" style="border-color:var(--bs-border-color);">
<p class="section-label mb-2">Ogłoszenia</p>

<div class="card match-card mb-3" id="notatki-card">
  <div class="px-3 pt-3 pb-2">
    <div id="notatki-body" style="font-size:15px;line-height:1.6;"></div>
    <div class="text-end mt-1" style="font-size:12px;color:var(--bs-tertiary-color);">
      <span id="notatki-date"></span>
      &nbsp;·&nbsp;
      <span id="notatki-counter"></span>
    </div>
  </div>

  <?php if (count($notatki) > 1): ?>
  <div class="d-flex justify-content-between px-3 py-2"
       style="border-top:1px solid var(--bs-border-color);font-size:13px;">
    <button class="btn-type ff-bebas action-btn"
            id="notatki-prev"
            onclick="notatkiNav(1)"
            style="font-size:14px;padding:6px 12px;">
      ‹ Poprzednia
    </button>
    <button class="btn-type ff-bebas action-btn"
            id="notatki-next"
            onclick="notatkiNav(-1)"
            style="font-size:14px;padding:6px 12px;">
      Następna ›
    </button>
  </div>
  <?php endif; ?>
</div>

<script src="<https://cdn.jsdelivr.net/npm/marked@9/marked.min.js>"></script>
<script>
(function() {
  var notatki = <?= json_encode(array_values($notatki)) ?>;
  var current = 0;

  function render(idx) {
    var n = notatki[idx];
    document.getElementById('notatki-body').innerHTML = marked.parse(n.tresc);
    document.getElementById('notatki-date').textContent = n.created_at.slice(0, 10);
    document.getElementById('notatki-counter').textContent = (idx + 1) + ' / ' + notatki.length;
    var prev = document.getElementById('notatki-prev');
    var next = document.getElementById('notatki-next');
    if (prev) prev.disabled = (idx >= notatki.length - 1);
    if (next) next.disabled = (idx <= 0);
  }

  window.notatkiNav = function(dir) {
    var n = current + dir;
    if (n < 0 || n >= notatki.length) return;
    current = n;
    render(current);
  };

  render(0);
})();
</script>
<?php endif; ?>
