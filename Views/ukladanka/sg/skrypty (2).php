<script>
document.addEventListener("DOMContentLoaded", function() {
    const mecze = <?= json_encode($mecze); ?>;

    const tbody = document.querySelector('table tbody');
    mecze.forEach(mecz => {
        const row = document.createElement('tr');
        let typyTekst = 'Wytypuj';
        if (mecz.typy && mecz.typy !== "Brak typów" && mecz.typy.HomeTyp !== undefined && mecz.typy.AwayTyp !== undefined) {
            typyTekst = `${mecz.typy.HomeTyp}:${mecz.typy.AwayTyp}`;
        }
        row.innerHTML = `
            <td>${mecz.Time.substring(0, 5)}</td>
            <td>${mecz.HomeName} - ${mecz.AwayName}</td>
            <td>${mecz.ScoreHome}:${mecz.ScoreAway}</td>
            <td>${typyTekst}</td>
            <td><button class="btn btn-info" onclick="toggleDetails(this, ${mecz.ApiID})"><i class="bi bi-caret-down"></i></button></td>
        `;
        tbody.appendChild(row);

        const detailsRow = document.createElement('tr');
        detailsRow.style.display = 'none'; // Start hidden
        detailsRow.innerHTML = `
            <td colspan="5">Lorem ipsum dolor sit amet, consectetur adipiscing elit. (120 słów)</td>
        `;
        tbody.appendChild(detailsRow);

        // Check if details should be open
        if (localStorage.getItem(`details-${mecz.ApiID}`)) {
            detailsRow.style.display = '';
        }
    });
});

function toggleDetails(button, apiId) {
    const detailsRow = button.parentNode.parentNode.nextElementSibling;
    if (detailsRow.style.display === 'none') {
        localStorage.setItem(`details-${apiId}`, 'open');
        detailsRow.style.display = '';
    } else {
        localStorage.removeItem(`details-${apiId}`);
        detailsRow.style.display = 'none';
    }
}
</script>