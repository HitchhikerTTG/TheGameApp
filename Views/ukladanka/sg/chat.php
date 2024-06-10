<div id="shoutbox">
    <div id="lastMessage" style="display: flex; justify-content: space-between; align-items: center;">
        <span id="lastMessageText" style="flex: 8;"></span>
        <button id="joinChat" style="flex: 1; height: 30px;">💬</button>
    </div>
    <div id="messagesContainer" style="display: none; position: relative;">
        <button id="minimizeChat" style="position: absolute; top: 10px; right: 10px;">-</button>
        <div id="messages" style="height: 450px; overflow-y: auto; display: flex; flex-direction: column-reverse;"></div>
        <form id="shoutboxForm" style="display: flex;">
            <input type="text" id="message" name="message" placeholder="Twoja wiadomość" required style="flex: 4;">
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
                $('#messages').empty();
                if (data.length > 0) {
                    const newestMessage = data[0]; // Assuming data is sorted from newest to oldest
                    if (lastMessageId !== newestMessage.id) {
                        lastMessageId = newestMessage.id;
                        const truncatedMessage = newestMessage.message.length > 45 ? newestMessage.message.substring(0, 45) + '...' : newestMessage.message;
                        $('#lastMessageText').html('<strong>' + newestMessage.username + ':</strong> ' + truncatedMessage);
                        $('#lastMessage').addClass('highlight');
                        setTimeout(function() {
                            $('#lastMessage').removeClass('highlight');
                        }, 3000);
                    }
                }
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

        $('#minimizeChat').click(function() {
            $('#messagesContainer').hide();
            $('#lastMessage').show();
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

<style>
    .highlight {
        background-color: #a5d525;
    }
</style>