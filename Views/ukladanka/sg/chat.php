<div id="shoutbox">
    <div id="messages" style="height: 300px; overflow-y: auto;"></div>
    <form id="shoutboxForm">
        <input type="text" id="message" name="message" placeholder="Enter your message" required>
        <button type="submit">Send</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        function loadMessages() {
            $.getJSON('<?= site_url('shoutbox/getMessages'); ?>', function(data) {
                $('#messages').empty();
                data.slice(-100).reverse().forEach(function(message) { // Get last 100 messages and reverse order
                    $('#messages').prepend('<div><strong>' + message.username + ':</strong> ' + message.message + '</div>');
                });
            });
        }

        $('#shoutboxForm').submit(function(event) {
            event.preventDefault();
            $.post('<?= site_url('shoutbox/postMessage'); ?>', { message: $('#message').val() }, function(response) {
                if (response.status === 'success') {
                    $('#message').val('');
                    loadMessages();
                }
            }, 'json');
        });

        loadMessages();
        setInterval(loadMessages, 5000); // Refresh messages every 5 seconds
    });
</script>