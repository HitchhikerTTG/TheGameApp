<hr class="my-3" style="border-color:var(--bs-border-color);">
<p class="section-label mb-2">Tabela</p>

<div class="card match-card mb-3">
  <div class="d-flex align-items-center justify-content-between px-3 py-2"
       style="border-bottom:1px solid var(--bs-border-color);">
    <span class="ff-bebas" style="font-size:18px;">Klasyfikacja</span>
    <div class="d-flex gap-1" id="lb-tabs">
      <div class="lb-tab active" data-filtr="punkty"          onclick="lbSetTab(this)">Ogółem</div>
      <div class="lb-tab"        data-filtr="punktyZaMecze"    onclick="lbSetTab(this)">Mecze</div>
      <div class="lb-tab"        data-filtr="punktyZaPytania"  onclick="lbSetTab(this)">Pytania</div>
    </div>
  </div>

  <div id="lb-container"></div>

  <div class="text-center py-3" style="font-size:13px;color:var(--ty-accent);cursor:pointer;"
       id="lb-toggle" onclick="lbToggle()">Pokaż wszystkich →</div>
</div>

<script>
(function() {
    var tabelaDanych = <?= json_encode($tabelaDanych) ?>;
    var userID       = <?= json_encode($userID) ?>;
    window.typerLeaderboardData = tabelaDanych;
    window.typerCurrentUserID   = userID;
    var filtr        = 'punkty';
    var skrocony     = true;

  // ← NOWE: escHtml przez natywny DOM, displayNick z separatorem niełamliwym
  function escHtml(s) {
    var d = document.createElement('div');
    d.textContent = String(s);
    return d.innerHTML;
  }
  
  function displayNick(emoji, nick, slug) {
    var label = (emoji ? escHtml(emoji) + ' ' : '') + escHtml(nick);
    if (!slug) return label;
    return '<a href="/profil/' + encodeURIComponent(slug) + '" style="color:inherit;text-decoration:none;">' + label + '</a>';
    
  } 

  function ustalPozycje(dane, f) {
    var sorted = dane.slice().sort(function(a, b) { return b[f] - a[f]; });
    var pos = 1;
      return sorted.map(function(g, i) {
      if (i > 0 && g[f] !== sorted[i-1][f]) pos = i + 1;
      // ← NOWE: emoji przekazywane dalej, inaczej displayNick zawsze dostaje undefined
      return { uid: g.uid, nick: g.nick, slug: g.slug,emoji: g.emoji || '', punkty: g[f], pozycja: pos };
    });
  }

  function medalClass(pos) {
    return pos === 1 ? 'lb-pos-1' : pos === 2 ? 'lb-pos-2' : pos === 3 ? 'lb-pos-3' : '';
  }

  function render() {
    var pozycje = ustalPozycje(tabelaDanych, filtr);
    var limit   = skrocony ? 10 : pozycje.length;
    var userPos = pozycje.findIndex(function(p) { return p.uid == userID; });
    var html    = '';

    pozycje.slice(0, limit).forEach(function(g) {
      var isMe = (g.uid == userID);
      html += '<div class="lb-row' + (isMe ? ' me' : '') + '">'
        + '<div class="ff-bebas lb-pos ' + medalClass(g.pozycja) + '">' + g.pozycja + '</div>'
        + '<div class="lb-nick' + (isMe ? '" style="color:var(--ty-accent)"' : '"') + '>'
        + displayNick(g.emoji, g.nick, g.slug) + (isMe ? ' ← Ty' : '') + '</div>'
        + '<div class="ff-bebas lb-pts' + (isMe ? '" style="color:var(--ty-accent)"' : '"') + '>'
        + g.punkty + '</div></div>';
    });

    if (skrocony && userPos >= 10) {
      var g = pozycje[userPos];
      html += '<div class="lb-row" style="border-top:2px dashed var(--bs-border-color);">'
        + '<div class="ff-bebas lb-pos" style="color:var(--ty-accent);">' + g.pozycja + '</div>'
        + '<div class="lb-nick" style="color:var(--ty-accent);">' + displayNick(g.emoji, g.nick) + ' ← Ty</div>'
        + '<div class="ff-bebas lb-pts" style="color:var(--ty-accent);">' + g.punkty + '</div></div>';
    }

    document.getElementById('lb-container').innerHTML = html;
  }

  window.lbSetTab = function(el) {
    document.querySelectorAll('.lb-tab').forEach(function(t) { t.classList.remove('active'); });
    el.classList.add('active');
    filtr = el.dataset.filtr;
    render();
  };

  window.lbToggle = function() {
    skrocony = !skrocony;
    document.getElementById('lb-toggle').textContent = skrocony ? 'Pokaż wszystkich →' : 'Zwiń tabelę ←';
    render();
  };

  render();
})();
</script>
