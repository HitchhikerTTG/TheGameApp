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
        const jsonUrl = `/mecze/${turniejId}/${mecz.ApiID}`;
        const typyUrl = `/typy/${mecz.Id}`;
        const isExpanded = localStorage.getItem(`details-${mecz.ApiID}`) === 'true'; // Pobieranie stanu rozwinięcia z localStorage

        // Przykład zastosowania Date.UTC() do przeliczenia lokalnej daty na czas UTC
        const matchDate = new Date(mecz.Date + 'T' + mecz.Time + 'Z'); // Dodanie 'Z' konwertuje czas na UTC
        const matchTimeUTC = Date.UTC(matchDate.getUTCFullYear(), matchDate.getUTCMonth(), matchDate.getUTCDate(), matchDate.getUTCHours(), matchDate.getUTCMinutes()) / 1000;
        const currentTime = Date.now() / 1000; // Pobranie aktualnego czasu w sekundach
        const isPast = currentTime > matchTimeUTC; // Sprawdzenie, czy czas meczu już minął
        const disabledAttr = isPast ? 'disabled' : ''; // Atrybut disabled, jeśli czas minął
        
        const localDate = new Date(matchTimeUTC * 1000); // Konwersja sekund na milisekundy
        const formattedTime = localDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
        const isCheckboxDisabled = (usedGoldenBall !== 0 && usedGoldenBall !== mecz.Id);
        let tableHtml ='';
        
        try {
            const response = await fetch(jsonUrl);
            console.log('Response received:', response);
            if (!response.ok) {
                throw new Error(`Network response was not ok for ${jsonUrl}`);
            }
            const data = await response.json();

            if (lastDate !== data.date) {
                container.append(`<div class="row"><div class="col-12"><strong> Data meczu: ${data.date}, ${formattedTime}</strong></div></div>`);
                lastDate = data.date;
            }

            const typyTekst = mecz.typy !== 'Brak typów' ? `Twój typ: ${mecz.typy.HomeTyp}:${mecz.typy.AwayTyp}` : 'Wytypuj';
            let detailsHTML = `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading${mecz.ApiID}">
                    <button class="accordion-button ${isExpanded ? '' : 'collapsed'} px-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${mecz.ApiID}" aria-expanded="${isExpanded}" aria-controls="collapse${mecz.ApiID}">
                    ${data.home_team.name} vs ${data.away_team.name} | ${typyTekst}
                    </button>
                </h2>
                <div id="collapse${mecz.ApiID}" class="accordion-collapse collapse ${isExpanded ? 'show' : ''}" aria-labelledby="heading${mecz.ApiID}">
                    <div class="accordion-body">
<div class="row match form-row text-center">
                    <div class="col">
                        <form action="https://jakiwynik.com/theGame/nowyZapisTypu" method="post">
                        <input type="hidden" name="userUID" value="${userID}">
                        <input type="hidden" name="gameID" value="${mecz.Id}">
                        <input type="hidden" name="turniejID" value="${turniejId}">

                        <div class="row">
                            <div class ="col team h_${data.home_team.id}">
                                <div class="row">
                                    <div class="col team-name">
                                    ${data.home_team.name}
                                    </div>    
                                </div>
                                <div class="row">
                                    <div class="col text-center">
                                        <div class="score-display">${mecz.typy.HomeTyp || '-'}</div>
                                        <input type="hidden" name="H" class="score-value" value="${mecz.typy.HomeTyp || 0}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col zminusem"><button type="button" class="minus">-</button></div>
                                    <div class="col zplusem"><button type="button" class="plus">+</button></div>
                                </div>
                            </div>
                            <div class ="col team a_${data.away_team.id}">
                                <div class="row">
                                    <div class="col team-name">
                                    ${data.away_team.name}
                                    </div>    
                                </div>
                                <div class="row">
                                    <div class="col">
                                        <div class="score-display">${mecz.typy.AwayTyp || '-'}</div>
                                        <input type="hidden" name="A" class="score-value" value="${mecz.typy.AwayTyp || 0}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col zminusem text-center"><button type="button" class="minus">-</button></div>
                                    <div class="col zplusem text-center"><button type="button" class="plus">+</button></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
    <input type="checkbox" id="goldenGame${mecz.Id}" name="goldenGame" value="1" ${mecz.isGoldenGame ? 'checked' : ''} ${isCheckboxDisabled ? 'disabled' : ''}>
    <label for="goldenGame${mecz.Id}">Golden Game</label>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col">
                                <button type="submit" ${disabledAttr} class="btn btn-primary">Typuję!</button>
                                </form>
                            </div>                        
                        </div>
                    <div class ="row">
                        <div class="col">
                        Liczba typów dla tego meczu: ${mecz.liczbaTypow}
                        <!-- Scrollable modal -->
                           <!-- Button trigger modal -->
                            ${buttonHtml}                            
                        </div>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col betting-hints">
                            <div class="col-12">
                                <div class="hints-title">Podpowiedź bookmacherów</div>
                                    <div class="odds-container">
                                    <div class="odds">1: ${data.odds['1'] || 'N/A'}</div>
                                    <div class="odds">X: ${data.odds['X'] || 'N/A'}</div>
                                    <div class="odds">2: ${data.odds['2'] || 'N/A'}</div>                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    </div>
                </div>
            </div>
        `;




            container.append(detailsHTML);
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