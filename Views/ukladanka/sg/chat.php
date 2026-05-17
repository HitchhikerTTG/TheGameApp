<hr class="my-3" style="border-color:var(--bs-border-color);">
<p class="section-label mb-2">Shoutbox</p>

<div class="card match-card mb-3">
  <!-- Podgląd ostatniej wiadomości -->
  <div class="d-flex align-items-center gap-2 px-3 py-2"
       style="border-bottom:1px solid var(--bs-border-color);cursor:pointer;" onclick="typerToggleShout()">
    <div class="shout-avatar" id="shout-preview-avatar">??</div>
    <div class="flex-grow-1 overflow-hidden">
      <div class="shout-nick" id="shout-preview-nick">Ładowanie…</div>
      <div class="shout-msg text-truncate" id="shout-preview-msg"></div>
    </div>
    <div class="shout-time flex-shrink-0" id="shout-preview-time"></div>
  </div>
  <div class="text-end px-3 py-2" style="font-size:13px;color:var(--ty-accent);cursor:pointer;"
       onclick="typerToggleShout()" id="shout-expand-btn">Rozwiń czat ›</div>

  <!-- Feed -->
  <div class="shout-feed" id="shoutbox-feed"></div>

  <!-- Input -->
  <div class="d-flex gap-2 px-3 py-2" style="border-top:1px solid var(--bs-border-color);">
    <input class="shout-input" id="message" placeholder="Napisz coś…">
    <button class="shout-send" id="shout-send-btn">Wyślij</button>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/emoji-js/3.7.0/emoji.min.js"></script>
<script>
$(document).ready(function() {
  var emoji = new EmojiConvertor();
  var lastMessageId = null;

  function initials(name) {
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
  }

  function loadMessages() {
    $.getJSON('<?= site_url('shoutbox/getMessages') ?>', function(data) {
      if (!data.length) return;

      // Preview (last message)
      var newest = data[0];
      if (lastMessageId !== <newest.id>) {
        lastMessageId = <newest.id>;
        var truncated = newest.message.length > 45 ? newest.message.substring(0, 45) + '…' : newest.message;
        $('#shout-preview-avatar').text(initials(newest.username));
        $('#shout-preview-nick').text(newest.username);
        $('#shout-preview-msg').html(emoji.replace_colons(truncated));
        $('#shout-preview-time').text(newest.created_at ? newest.created_at.split(' ')[1].slice(0,5) : '');
      }

      // Full feed
      var html = '';
      data.forEach(function(msg) {
        html += '<div class="d-flex gap-2 px-3 py-2" style="border-bottom:1px solid var(--bs-border-color);">'
          + '<div class="shout-avatar">' + initials(msg.username) + '</div>'
          + '<div><div class="shout-nick">' + msg.username + '</div>'
          + '<div class="shout-msg">' + emoji.replace_colons(msg.message) + '</div>'
          + '<div class="shout-time">' + (msg.created_at || '') + '</div></div></div>';
      });
      $('#shoutbox-feed').html(html);
    });
  }

  function typerToggleShout() {
    var feed = document.getElementById('shoutbox-feed');
    var btn  = document.getElementById('shout-expand-btn');
    feed.classList.toggle('open');
    btn.textContent = feed.classList.contains('open') ? 'Zwiń czat ‹' : 'Rozwiń czat ›';
  }
  window.typerToggleShout = typerToggleShout;

  $('#shout-send-btn').click(function() {
    var msg = $('#message').val().trim();
    if (!msg) return;
    $.post('<?= site_url('shoutbox/postMessage') ?>', { message: msg }, function(response) {
      if (response.status === 'success') { $('#message').val(''); loadMessages(); }
    }, 'json');
  });

  $('#message').keypress(function(e) {
    if (e.which == 13) { e.preventDefault(); $('#shout-send-btn').click(); }
  });

  loadMessages();
  setInterval(loadMessages, 5000);
});
</script>
