<div id="fileContent"></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const fileUrl = '/data.txt'; // Zmień ścieżkę na odpowiednią

            // Logowanie pełnej ścieżki URL
            const fullUrl = new URL(fileUrl, window.location.href).href;
            console.log('Attempting to fetch file from URL:', fullUrl);

            $.get(fileUrl, function(data) {
                console.log('Data fetched:', data);
                $('#fileContent').text(data);
            }).fail(function() {
                console.error('Error fetching the file from:', fullUrl);
                $('#fileContent').text('Error loading file data.');
            });
        });
    </script>
