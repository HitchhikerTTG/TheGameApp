<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(async function() {
    const mecze = <?= json_encode($mecze); ?>;
    const turniejId = <?= json_encode($turniejID); ?>;
    const container = $('#matchContainer');
    let lastDate = null;

    mecze.sort((a, b) => new Date(a.Date + 'T' + a.Time) - new Date(b.Date + 'T' + b.Time));

    for (const mecz of mecze) {
        const jsonUrl = `/mecze/${turniejId}/${mecz.ApiID}`;
        try {
            const response = await fetch(jsonUrl);
            if (!response.ok) {
                throw new Error(`Network response was not ok for ${jsonUrl}`);
            }
            const data = await response.json();

            if (lastDate !== data.date) {
                container.append(`<div class="row"><div class="col-12"><strong>Data meczu: ${data.date}</strong></div></div>`);
                lastDate = data.date;
            }
            const typyTekst = mecz.typy !== 'Brak typów' ? `Twój typ: ${mecz.typy.HomeTyp}:${mecz.typy.AwayTyp}` : 'Wytypuj';
            let detailsHTML = `
                <div class="row mt-2">
                    <div class="col-2">${data.time.substring(0, 5)}</div>
                    <div class="col-6">${data.home_team.name} - ${data.away_team.name}</div>
                    <div class="col-3">${typyTekst}</div>
                    <div class="col-1"><button class="btn btn-info toggle-details" data-api-id="${mecz.ApiID}"><i class="bi bi-caret-down"></i></button></div>
                </div>
                <div class="row details-row" style="display:none;">
                    <div class="col-12">Zdaniem bukmacherów: ${data.odds['1'] || 'N/A'} | ${data.odds['2'] || 'N/A'} | ${data.odds['X'] || 'N/A'}</div>
                </div>
                <div class="row form-row mb-3" style="display:none;">
                    <div class="col-12">
                        <form action="https://jakiwynik.com/typer/zapiszTypMeczu" method="post">
                        <input type="hidden" name="userID" value="25">
                        <input type="hidden" name="gameID" value="${mecz.Id}">
                        <input type="hidden" name="turniejID" value="${turniejId}">

                        <div class="row">
                            <div class="col">
                                Twój Typ
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                            ${data.home_team.name}
                            </div>
                            <div class="col">
                            </div>
                            <div class="col">
                                ${data.away_team.name}
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col-5"><span class="minus" style="font-size:35px">-</span><input type="number" size="1" name="H" value="${mecz.typy.HomeTyp || '-'}" class="qty form-control-lg" style="width:55px; font-size: 2.25em; border-style:none none double none;border-radius:0px"><span class="add" style="font-size:35px">+</span>
                            </div>
                            <div class="col-2">
                            :
                            </div>
                            <div class="col-5">
                            <span class="minus" style="font-size:35px">-</span><input type="number" size="1" name="A" value="${mecz.typy.AwayTyp || '-'}" class="qty form-control-lg" style="width:55px; font-size: 2.25em; border-style:none none double none;border-radius:0px"><span class="add" style="font-size:35px">+</span>
                            </div>
                        </div>
                        <div class="row text-center">
                            <div class="col">
                            <button type="submit" class="btn btn-primary">Zapisz typ</button>
                            </form>
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

    // Automatyczne rozwijanie wierszy zgodnie ze stanem w localStorage po stworzeniu wszystkich wierszy
    $('.toggle-details').each(function() {
        const apiId = $(this).data('api-id');
        const matchRow = $(this).closest('.row');
        const detailsRow = matchRow.next('.details-row');
        const formRow = detailsRow.next('.form-row');
        if (localStorage.getItem(`details-${apiId}`) === 'open') {
            detailsRow.show();
            formRow.show();
        }
    });


 $('body').on('click', '.toggle-details', function() {
    const detailsRow = $(this).closest('.row').next('.details-row');
    const formRow = detailsRow.next('.form-row');
    console.log(detailsRow, formRow); // Dodano logowanie do debugowania
    const apiId = $(this).data('api-id');

    if (detailsRow.is(':hidden')) {
        detailsRow.slideDown();
        formRow.slideDown();
        localStorage.setItem(`details-${apiId}`, 'open');
        console.log('Opening:', apiId); // Logowanie otwarcia
    } else {
        detailsRow.slideUp();
        formRow.slideUp();
        localStorage.removeItem(`details-${apiId}`);
        console.log('Closing:', apiId); // Logowanie zamknięcia
    }
});

$(function() {
    $(".add").click(function() {
        var $qty = $(this).prev(".qty");
        var currentVal = parseInt($qty.val());
        if (!isNaN(currentVal)) {
            $qty.val(currentVal + 1);
        } else {
            $qty.val(1); // Ustaw wartość na 1, jeśli obecna wartość jest NaN
        }
    });

    $(".minus").click(function() {
        var $qty = $(this).next(".qty");
        var currentVal = parseInt($qty.val());
        if (!isNaN(currentVal) && currentVal > 0) {
            $qty.val(currentVal - 1);
        } else {
            $qty.val(0); // Ustaw wartość na 0, jeśli obecna wartość jest NaN lub 0
        }
    });
});



});
</script>
