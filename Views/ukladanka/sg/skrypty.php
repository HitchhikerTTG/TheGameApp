<script>
$(document).ready(function() {
    const mecze = <?= json_encode($mecze); ?>;
    const tbody = $('table tbody');

    mecze.forEach(mecz => {
        let typyTekst = mecz.typy && mecz.typy !== "Brak typów" && mecz.typy.HomeTyp !== undefined && mecz.typy.AwayTyp !== undefined ? `Twój typ: ${mecz.typy.HomeTyp}:${mecz.typy.AwayTyp}` : 'Wytypuj';
        const row = $('<tr>').html(`
            <td>${mecz.Time.substring(0, 5)}</td>
            <td>${mecz.HomeName} - ${mecz.AwayName}</td>
            <td>${mecz.ScoreHome}:${mecz.ScoreAway}</td>
            <td>${typyTekst}</td>
            <td><button class="btn btn-info toggle-details" data-api-id="${mecz.ApiID}"><i class="bi bi-caret-down"></i></button></td>
        `);
        tbody.append(row);

        const detailsRow = $('<tr>').css('display', 'none').html(`
            <td colspan="5">Lorem ipsum dolor sit amet, consectetur adipiscing elit. (120 słów)</td>
        `);
        tbody.append(detailsRow);

        if (localStorage.getItem(`details-${mecz.ApiID}`)) {
            detailsRow.show();
        }
    });

    // Bind click event to dynamically created buttons
    $('table').on('click', '.toggle-details', function() {
        const apiId = $(this).data('api-id');
        const detailsRow = $(this).closest('tr').next('tr');
        
        if (detailsRow.is(':hidden')) {
            localStorage.setItem(`details-${apiId}`, 'open');
            detailsRow.show();
        } else {
            localStorage.removeItem(`details-${apiId}`);
            detailsRow.hide();
        }
    });
});
</script>