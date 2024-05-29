<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(async function() {
    const mecze = <?= json_encode($mecze); ?>;
    const turniejId = <?= json_encode($turniejID); ?>;
    const tbody = $('table tbody');
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
                const dateRow = $('<tr>').html(`<td colspan="5"><strong>Data meczu: ${data.date}</strong></td>`);
                tbody.append(dateRow);
                lastDate = data.date;
            }

            const typyTekst = mecz.typy !== 'Brak typów' ? `Twój typ: ${mecz.typy.HomeTyp}:${mecz.typy.AwayTyp}` : 'Wytypuj';
            const row = $('<tr>').html(`
                <td>${data.time.substring(0, 5)}</td>
                <td>${data.home_team.name} - ${data.away_team.name}</td>
                <td>${typyTekst}</td>
                <td><button class="btn btn-info toggle-details" data-api-id="${mecz.ApiID}">Szczegóły</button></td>
            `);
            tbody.append(row);

            const detailsRow = $('<tr>').css('display', 'none').html(`
                <td colspan="5">Zdaniem bukmacherów: ${data.odds['1'] || 'N/A'} | ${data.odds['2'] || 'N/A'} | ${data.odds['X'] || 'N/A'}</td>
            `);
            tbody.append(detailsRow);

            const formRow = $('<tr>').css('display', 'none').html(`
                <td colspan="5">
                    <form action="https://jakiwynik.com/typer/zapiszTypMeczu" method="post">
                        <input type="hidden" name="userID" value="25">
                        <input type="hidden" name="gameID" value="${mecz.Id}">
                        <input type="hidden" name="turniejID" value="${turniejId}">
                        Typ gospodarzy: <input type="number" name="H" min="0" class="form-control" value="${mecz.typy.HomeTyp || ''}">
                        Typ gości: <input type="number" name="A" min="0" class="form-control" value="${mecz.typy.AwayTyp || ''}">
                        <button type="submit" class="btn btn-primary">Zapisz typ</button>
                    </form>
                </td>
            `);
            tbody.append(formRow);
        } catch (error) {
            console.error('Error loading match data:', error);
            alert('Błąd ładowania danych meczu. Sprawdź konsolę dla szczegółów.');
        }
    }

    $('table').on('click', '.toggle-details', function() {
        const apiId = $(this).data('api-id');
        const detailsRow = $(this).closest('tr').next('tr');
        const formRow = detailsRow.next('tr');

        if (detailsRow.is(':hidden')) {
            localStorage.setItem(`details-${apiId}`, 'open');
            detailsRow.show();
            formRow.show();
        } else {
            localStorage.removeItem(`details-${apiId}`);
            detailsRow.hide();
            formRow.hide();
        }
    });
});
</script>