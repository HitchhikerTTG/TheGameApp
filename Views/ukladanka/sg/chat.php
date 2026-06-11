<hr class="my-3" style="border-color:var(--bs-border-color);">
<p class="section-label mb-2">Shoutbox</p>

<div class="card match-card mb-3">

  <!-- podgląd: chowa się po rozwinięciu -->
  <div class="d-flex align-items-center gap-2 px-3 py-2"
       id="shout-preview"
       style="border-bottom:1px solid var(--bs-border-color);cursor:pointer;"
       onclick="typerToggleShout()">
    <div class="shout-avatar" id="shout-preview-avatar">??</div>
    <div class="flex-grow-1 overflow-hidden">
      <div class="shout-nick" id="shout-preview-nick">Ładowanie…</div>
      <div class="shout-msg text-truncate" id="shout-preview-msg"></div>
    </div>
    <div class="shout-time flex-shrink-0" id="shout-preview-time"></div>
  </div>

  <!-- feed: pojawia się po rozwinięciu -->
  <div class="shout-feed" id="shoutbox-feed"></div>

  <!-- input: pojawia się po rozwinięciu -->
  <div class="d-flex gap-2 px-3 py-2 d-none" id="shout-input-row"
       style="border-top:1px solid var(--bs-border-color);">
    <input class="shout-input" id="message" placeholder="Napisz coś…">
    <button class="shout-send" id="shout-send-btn">Wyślij</button>
  </div>

  <!-- przycisk zawsze na dole -->
  <div class="text-center px-3 py-2" id="shout-expand-btn"
       style="font-size:13px;color:var(--ty-accent);cursor:pointer;border-top:1px solid var(--bs-border-color);"
       onclick="typerToggleShout()">
    Rozwiń czat ›
  </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/emoji-js/3.7.0/emoji.min.js"></script>
<script>
/* ── bezpieczna emoji – działa nawet bez biblioteki ── */
var _emoji = null;
try {
  _emoji = new EmojiConvertor();
  _emoji.replace_mode = 'unified';
  _emoji.allow_native = true;
} catch(e) {}
function _emojiReplace(text) {
  if (!_emoji) return text;
  return _emoji.replace_colons(_emoji.replace_emoticons(text));
}

function initials(name) {
  return (name || '?').split(' ').map(function(w){ return w[0] || ''; }).join('').toUpperCase().slice(0,2) || '??';
}

function typerToggleShout() {
  var feed     = document.getElementById('shoutbox-feed');
  var btn      = document.getElementById('shout-expand-btn');
  var preview  = document.getElementById('shout-preview');
  var inputRow = document.getElementById('shout-input-row');
  if (!feed) return;
  feed.classList.toggle('open');
  var isOpen = feed.classList.contains('open');
  btn.textContent = isOpen ? 'Zwiń czat ‹' : 'Rozwiń czat ›';
  if (preview)  preview.classList.toggle('d-none', isOpen);
  if (inputRow) inputRow.classList.toggle('d-none', !isOpen);
}


function escHtml(s) {
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/'"'/g,'&quot;');
}


$(document).ready(function() {
  var lastMessageId = null;

  function loadMessages() {
    $.getJSON('<?= site_url('shoutbox/getMessages') ?>', function(data) {
      if (!data) return;

      if (!data.length) {
        $('#shout-preview-nick').text('Brak wiadomości');
        $('#shout-preview-avatar').text('?');
        return;
      }

      var newest = data[0];
      if (lastMessageId !== newest.id) {
        lastMessageId = newest.id;
        var truncated = newest.message.length > 45
          ? newest.message.substring(0, 45) + '…'
          : newest.message;
        $('#shout-preview-avatar').text(newest.emoji || initials(newest.username));
        $('#shout-preview-nick').text(newest.username);
        $('#shout-preview-msg').html(_emojiReplace(truncated));
        if (newest.created_at) {
          $('#shout-preview-time').text(newest.created_at.split(' ')[1].slice(0,5));
        }
      }

      var html = '';
      data.slice().reverse().forEach(function(msg) {
        html += '<div class="d-flex gap-2 px-3 py-2" style="border-bottom:1px solid var(--bs-border-color);">'
          + '<div class="shout-avatar"' + (msg.emoji ? ' style="font-size:1.75rem;line-height:1;"' : '') + '>' + (msg.emoji ? escHtml(msg.emoji) : initials(msg.username)) + '</div>'
          + '<div class="shout-nick">' + displayNick(msg.emoji, msg.username) + '</div>'
          + '<div class="shout-msg">' + _emojiReplace(msg.message) + '</div>'
          + '</div></div>';
      });
      $('#shoutbox-feed').html(html);
      var feed = document.getElementById('shoutbox-feed');
      if (feed) feed.scrollTop = feed.scrollHeight;

    }).fail(function(jqXHR) {
      if (jqXHR.status === 401) {
        $('#shout-preview-nick').text('Sesja wygasła – odśwież stronę');
        $('#shoutbox-feed').html('<div class="px-3 py-2 text-muted" style="font-size:13px;">Odśwież stronę, aby kontynuować.</div>');
      } else {
        $('#shout-preview-nick').text('Błąd ładowania czatu');
      }
    });
  }

  $('#shout-send-btn').on('click', function() {
    var msg = $('#message').val().trim();
    if (!msg) return;
    $.post('<?= site_url('shoutbox/postMessage') ?>', { message: msg })
      .done(function(response) {
        if (response && response.status === 'success') { $('#message').val(''); loadMessages(); }
      })
      .fail(function(jqXHR) {
        if (jqXHR.status === 401) {
          alert('Sesja wygasła. Odśwież stronę, aby wysłać wiadomość.');
        }
      });
  });

  $('#message').on('keypress', function(e) {
    if (e.which === 13) { e.preventDefault(); $('#shout-send-btn').click(); }
  });

  loadMessages();
  setInterval(loadMessages, 5000);
});
</script>
