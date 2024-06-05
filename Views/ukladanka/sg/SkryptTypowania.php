<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <div id="fileContent"></div>

    <script>
        $(document).ready(function() {
            const fileUrl = 'https://nirski.com/ksiazki.html'; // Zmień ścieżkę na odpowiednią

            $.get(fileUrl, function(data) {
                console.log('Data fetched:', data);
                $('#fileContent').text(data);
            }).fail(function() {
                console.error('Error fetching the file.');
                $('#fileContent').text('Error loading file data.');
            });
        });
    </script>