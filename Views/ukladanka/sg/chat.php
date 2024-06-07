<div id="shoutbox">
    <div id="lastMessage" style="display: flex; justify-content: space-between; align-items: center;">
        <span id="lastMessageText" style="flex: 4;"></span>
        <button id="joinChat" style="flex: 1; height: 30px;">💬</button>
    </div>
    <div id="messagesContainer" style="display: none;">
        <div id="messages" style="height: 450px; overflow-y: auto; display: flex; flex-direction: column-reverse;"></div>
        <form id="shoutboxForm" style="display: flex;">
            <input type="text" id="message" name="message" placeholder="Enter your message" required style="flex: 4;">
            <button type="submit" style="flex: 1;">➤</button>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        let lastMessageId = null;

        function loadMessages() {
            $.getJSON('<?= site_url('shoutbox/getMessages'); ?>', function(data) {
                if (data.length > 0) {
                    const lastMessage = data[data.length - 1];
                    if (lastMessageId !== lastMessage.id) {
                        lastMessageId = lastMessage.id;
                        const truncatedMessage = lastMessage.message.length > 45 ? lastMessage.message.substring(0, 45) + '...' : lastMessage.message;
                        $('#lastMessageText').html('<strong>' + lastMessage.username + ':</strong> ' + truncatedMessage);
                        $('#lastMessage').addClass('highlight');
                        setTimeout(function() {
                            $('#lastMessage').removeClass('highlight');
                        }, 3000);
                    }
                }
                $('#messages').empty();
                data.forEach(function(message) {
                    $('#messages').append('<div><strong>' + message.username + ':</strong> ' + message.message + '</div>');
                });
                $('#messages').scrollTop($('#messages')[0].scrollHeight); // Scroll to the bottom
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