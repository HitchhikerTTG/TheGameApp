<div id="fileContent"></div>
    <script>
        document.addEventListener("DOMContentLoaded", async function() {
            const fileUrl = 'https://winiwoni.pl/mecze/2/1676019';
            const container = document.getElementById('fileContent');

            try {
                const response = await fetch(fileUrl);
                if (!response.ok) {
                    throw new Error(`Network response was not ok for ${fileUrl}`);
                }
                const text = await response.text();
                console.log('Data fetched:', text);
                container.textContent = text;
            } catch (error) {
                console.error('Error fetching the file:', error);
                container.textContent = 'Error loading file data.';
            }
        });
    </script> 
    
