<div id="shoutbox">
    <div id="lastMessage" style="display: flex; justify-content: space-between; align-items: center;">
        <span id="lastMessageText"></span>
        <button id="joinChat">Dołącz do rozmowy</button>
    </div>
    <div id="messagesContainer" style="display: none;">
        <div id="messages" style="height: 450px; overflow-y: auto; display: flex; flex-direction: column-reverse;"></div>
        <form id="shoutboxForm">
            <input type="text" id="message" name="message" placeholder="Enter your message" required>
            <button type="submit">➤</button>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        function loadMessages() {
            $.getJSON('<?= site_url('shoutbox/getMessages'); ?>', function(data) {
                $('#messages').empty();
                if (data.length > 0) {
                    const lastMessage = data[data.length - 1];
                    const truncatedMessage = lastMessage.message.length > 45 ? lastMessage.message.substring(0, 45) + '...' : lastMessage.message;
                    $('#lastMessageText').html('<strong>' + lastMessage.username + ':</strong> ' + truncatedMessage);
                }
                data.forEach(function(message) {
                    $('#messages').append('<div><strong>' + message.username + ':</strong> ' + message.message + '</div>');
                });
            });
        }

        $('#joinChat').click(function() {
            $('#lastMessage').hide();
            $('#messagesContainer').show();
        });

        $('#shoutboxForm').submit(function(event) {
            event.preventDefault();
            $.post('<?= site_url('shoutbox/postMessage'); ?>', { message: $('#message').val() }, function(response) {
                if (response.status === 'success') {
                    $('#message').val('');
                    loadMessages();
                }
            }, 'json');
        });

        $('#message').keypress(function(event) {
            if (event.which == 13) {
                event.preventDefault();
                $('#shoutboxForm').submit();
            }
        });

        loadMessages();
        setInterval(loadMessages, 5000); // Refresh messages every 5 seconds
    });
</script>   