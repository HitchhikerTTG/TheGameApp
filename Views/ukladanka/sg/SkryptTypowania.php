<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", async function() {
    const jsonUrl = 'https://jakiwynik.com/mecze/2/1676019';
    const container = document.getElementById('jsonContent');

    try {
        const response = await fetch(jsonUrl);
        if (!response.ok) {
            throw new Error(`Network response was not ok for ${jsonUrl}`);
        }
        const text = await response.text();
        console.log('Response text:', text);

        // Wyodrębnij JSON, jeśli zawiera HTML
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            const jsonMatch = text.match(/<pre[^>]*>([^<]*)<\/pre>/);
            if (jsonMatch && jsonMatch.length >= 2) {
                data = JSON.parse(jsonMatch[1]);
            } else {
                throw new Error('No JSON found in response');
            }
        }

        console.log('Dane JSON:', data);
        container.textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        console.error('Error fetching and parsing JSON:', error);
        container.textContent = 'Error loading JSON data.';
    }
});
</script>