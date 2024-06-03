<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(async function() {
    const mecze = <?= json_encode($mecze); ?>;
    const turniejId = <?= json_encode($turniejID); ?>;
    const userID = <?=json_encode($userID);?>;
    const usedGoldenBall = <?=json_encode($usedGoldenBall) ?>;
    const currentTime = <?= time(); ?>; // Przykładowe przekazanie czasu serwera w formacie timestamp    
    const container = $('#matchesAccordion');
    let lastDate = null;



    mecze.sort((a, b) => new Date(a.Date + 'T' + a.Time) - new Date(b.Date + 'T' + b.Time));

    for (const mecz of mecze) {
    if (!mecz || !mecz.ApiID) {
        console.error(`Nieprawidłowy obiekt meczu:`, mecz);
        continue;
    }

    const jsonUrl = `/mecze/${turniejId}/${mecz.ApiID}`;
    console.log(`Probuje wczytac dane meczu ID: ${mecz.Id} z URL: ${jsonUrl}`);

    try {
        const response = await fetch(jsonUrl);
        console.log('Response received:', response);
        const text = await response.text();
        console.log('Response text:', text);

        let jsonString;
        try {
            jsonString = JSON.parse(text);
        } catch (e) {
            const jsonMatch = text.match(/<pre[^>]*>([^<]*)<\/pre>/);
            if (jsonMatch && jsonMatch.length >= 2) {
                jsonString = JSON.parse(jsonMatch[1]);
            } else {
                throw new Error('No JSON found in response');
            }
        }
        console.log('Dane meczu:', jsonString);

        if (!jsonString.home_team || !jsonString.away_team) {
            throw new Error(`Brak danych drużyn w meczu ID: ${mecz.Id}`);
        }

        // Reszta kodu, który przetwarza dane
    } catch (error) {
        console.error(`Error loading match (ID: ${mecz.Id}, Home: ${mecz.HomeTeam}, Away: ${mecz.AwayTeam}) data:`, error);
    }
    
        
    }


$('.accordion-collapse').on('shown.bs.collapse', function () {
    let id = this.id;
    localStorage.setItem(`details-${id}`, 'true');
});

$('.accordion-collapse').on('hidden.bs.collapse', function () {
    let id = this.id;
    localStorage.setItem(`details-${id}`, 'false');
});

$('.accordion-collapse').each(function () {
    let id = this.id;
    let isOpen = localStorage.getItem(`details-${id}`) === 'true';
    if (isOpen) {
        $(`#${id}`).addClass('show');
    } else {
        $(`#${id}`).removeClass('show');
    }
});

$('body').on('submit', 'form', function(event) {
    const form = $(this);
    const accordionId = form.closest('.accordion-collapse').attr('id');
    $(`#collapse${accordionId}`).collapse('hide'); // Zwijanie akordeonu
    localStorage.setItem(`details-${accordionId}`, 'false'); // Aktualizacja localStorage
});

$('body').on('submit', 'form', function(event) {
    event.preventDefault(); // Zapobiega standardowej wysyłce formularza

    var form = $(this);
    var url = form.attr('action'); // URL z atrybutu action formularza

    // Wypisanie danych formularza w konsoli
    var formData = new FormData(form[0]);
    console.log("Dane formularza:");
    for (var pair of formData.entries()) {
        console.log(pair[0]+ ': ' + pair[1]);
    }

    $.ajax({
        type: "POST",
        url: url,
        data: form.serialize(), // Serializacja danych formularza
        success: function(response) {
            var newTypText = response.newTypText;
            var button = form.closest('.accordion-item').find('.accordion-button');
            var oldText = button.text();
            var newText = oldText.replace(/Twój typ: [^|]+|Wytypuj/, newTypText); // Zakładając, że format tekstu to "Drużyna 1 vs Drużyna 2 | Twój typ: x:x"
            button.text(newText);

            // Animacja podświetlenia na zielono
            button.css('background-color', 'lightgreen');
            setTimeout(function() { button.css('background-color', ''); }, 1000); // Reset koloru tła po 3 sekundach
        },
        error: function(xhr, status, error) {
            console.error('Wystąpił błąd: ', error);
            console.error('Status: ', status);
            console.error('Odpowiedź serwera: ', xhr.responseText);
            alert('Nie udało się przesłać formularza. Sprawdź konsolę dla szczegółów.');
        }
    });

    // Dodatkowo zwinąć akordeon po przesłaniu formularza
    var accordionId = form.closest('.accordion-collapse').attr('id');
    $(`#${accordionId}`).collapse('hide');
    localStorage.setItem(`details-${accordionId}`, 'false');
});

$('body').on('click', '.plus', function(event) {
    event.preventDefault(); // Zapobiega wysyłaniu formularza
    // Znajdź najbliższy wyświetlacz wyniku i odpowiedni ukryty input
    var $scoreDisplay = $(this).closest('.team').find('.score-display');
    var $scoreValue = $(this).closest('.team').find('.score-value');
    var currentVal = isNaN(parseInt($scoreDisplay.text())) ? 0 : parseInt($scoreDisplay.text());
    currentVal++;
    $scoreDisplay.text(currentVal);
    $scoreValue.val(currentVal);
});

$('body').on('click', '.minus', function(event) {
    event.preventDefault(); // Zapobiega wysyłaniu formularza
    var $scoreDisplay = $(this).closest('.team').find('.score-display');
    var $scoreValue = $(this).closest('.team').find('.score-value');
    var currentVal = parseInt($scoreDisplay.text()) || 0;
    if (currentVal > 0) {
        currentVal--;
        $scoreDisplay.text(currentVal);
        $scoreValue.val(currentVal);
    }
});



});

 
</script>