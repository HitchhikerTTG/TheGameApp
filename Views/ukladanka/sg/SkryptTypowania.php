<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", async function() {
    const jsonUrl = '/mecze/2/1676019'; // Zastąp odpowiednią ścieżką do pliku JSON
    const container = document.getElementById('jsonContent');

    try {
        const response = await fetch(jsonUrl);
        if (!response.ok) {
            throw new Error(`Network response was not ok for ${jsonUrl}`);
        }
        const data = await response.json();
        console.log('Dane JSON:', data);

        // Konwersja obiektu JSON na sformatowany tekst
        const formattedJson = JSON.stringify(data, null, 2);

        // Wyświetlenie danych JSON w elemencie HTML
        container.textContent = formattedJson;
    } catch (error) {
        console.error('Error fetching and parsing JSON:', error);
        container.textContent = 'Error loading JSON data.';
    }
});
</script>