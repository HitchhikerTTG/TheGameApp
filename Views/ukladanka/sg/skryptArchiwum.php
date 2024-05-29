<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(async function() {
    const mecze = <?= json_encode($mecze); ?>;
    const turniejId = <?= json_encode($turniejID); ?>;
    const userID = <?=json_encode($userID);?>;
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
        let tableHtml ='';
        if(isPast){
                $.getJSON(typyUrl)
                    .done(function(data) {
                        console.log("Dane załadowane: ", data);
                        var tableHtml = '<table class="table"><thead><tr><th>Nick</th><th>Typ</th><th>Złota piłka</th></tr></thead><tbody>';
                        data.forEach(function(typ) {
                            tableHtml += '<tr>';
                            tableHtml += `<td>${typ.username}</td>`;
                            tableHtml += `<td>${typ.HomeTyp}:${typ.AwayTyp}</td>`;
                            tableHtml += `<td>${typ.GoldenGame ? ' ' : '<i class="bi bi-check2-circle"></i>'}</td>`;
                            tableHtml += '</tr>';
                            });
                        tableHtml += '</tbody></table>';
                        // Aktualizacja treści modala, jeśli dane zostały pomyślnie załadowane
                        $(`#typy${mecz.Id} .modal-body`).html(tableHtml);
                        })

                    .fail(function() {
                        console.log("Dane nie istnieją, wywołanie serwera...");
                        // Wywołanie funkcji serwerowej, aby wygenerować dane
                        $.ajax({
                            url: `https://jakiwynik.com/jaktypowali/${mecz.Id}`,
                            type: 'POST',
                            success: function(response) {
                                      console.log("Dane zostały wygenerowane: ", response);
                                        // Opcjonalnie odświeżenie lub wyświetlenie danych
                                      },
                              error: function(xhr) {
                              console.error("Nie udało się wygenerować danych: ", xhr.responseText);
                              }
                        });
                    });
        }
        const buttonHtml = isPast ? `
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#typy${mecz.Id}">
        Jak typowali?
    </button>
    <div class="modal fade" id="typy${mecz.Id}" tabindex="-1" aria-labelledby="typy${mecz.Id}Label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="typy${mecz.Id}Label">Typy innych</h1>
                </div>
                <div class="modal-body">
                    Tu będą widoczne typy wszystkich, którzy wytypowali ten mecz
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>
` : '';    
        

        try {
            const response = await fetch(jsonUrl);
            if (!response.ok) {
                throw new Error(`Network response was not ok for ${jsonUrl}`);
            }
            const data = await response.json();

            if (lastDate !== data.date) {
                container.append(`<div class="row"><div class="col-12"><strong> Data meczu: ${data.date}</strong></div></div>`);
                lastDate = data.date;
            }

            const typyTekst = mecz.typy !== 'Brak typów' ? `Twój typ: ${mecz.typy.HomeTyp}:${mecz.typy.AwayTyp}` : 'Brak typu';
            let detailsHTML = `
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading${mecz.ApiID}">
                    <button class="accordion-button ${isExpanded ? '' : 'collapsed'} px-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapse${mecz.ApiID}" aria-expanded="${isExpanded}" aria-controls="collapse${mecz.ApiID}">
                    ${data.home_team.name} vs ${data.away_team.name}  | ${mecz.pkt}.pkt
                    </button>
                </h2>
                <div id="collapse${mecz.ApiID}" class="accordion-collapse collapse ${isExpanded ? 'show' : ''}" aria-labelledby="heading${mecz.ApiID}">
                    <div class="accordion-body">
<div class="row match form-row text-center">
                    <div class="col">
                        <form action="https://jakiwynik.com/typer/zapiszTypMeczu" method="post">
                        <input type="hidden" name="userID" value="${userID}">
                        <input type="hidden" name="gameID" value="${mecz.Id}">
                        <input type="hidden" name="turniejID" value="${turniejId}">

                        <div class="row">
                            <div class="col">
                            Wynik meczu: ${data.home_team.score} : ${data.away_team.score}
                            </div>
                          
                        </div>
                            <div class="row">
                            <div class="col">
                                Twój typ ${typyTekst} | Punkty ${mecz.pkt}
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
                </div>
                    </div>
                </div>
            </div>
        `;




            container.append(detailsHTML);
        } catch (error) {
            console.error('Error loading match data:', error);
            alert('Błąd ładowania danych meczu.');
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
            // Obsługa błędu
            console.error('Wystąpił błąd: ', error);
            alert('Nie udało się przesłać formularza.');
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



