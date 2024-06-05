<div id="fileContent"></div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            const fileUrl = '/data.txt'; // Zmień ścieżkę na odpowiednią

            $.get(fileUrl, function(data) {
                console.log('Data fetched:', data);
                $('#fileContent').text(data);
            }).fail(function() {
                console.error('Error fetching the file.');
                $('#fileContent').text('Error loading file data.');
            });
        });
    </script>