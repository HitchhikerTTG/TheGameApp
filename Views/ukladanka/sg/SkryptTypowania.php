    <div id="fileContent"></div>

    <script>
        $(document).ready(function() {
            const fileUrl = '/data.txt';
            
            // Logowanie pełnej ścieżki URL
            const fullUrl = new URL(fileUrl, window.location.href).href;
            console.log('Attempting to fetch file from URL:', fullUrl);

            $.get(fileUrl, function(data) {
                console.log('Data fetched:', data);
                $('#fileContent').text(data);
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error('Error fetching the file from:', fullUrl);
                console.error('Error details:', textStatus, errorThrown);
                console.error('Response:', jqXHR.responseText);
                $('#fileContent').text('Error loading file data.');
            });
        });
    </script>