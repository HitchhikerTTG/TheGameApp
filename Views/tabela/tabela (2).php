<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabelaDanych = <?php echo json_encode($tabelaDanych); ?>;
    var userID = <?php echo json_encode($userID); ?>;
    var userInfoDiv = document.getElementById('userInfo');

    // Sortowanie danych po liczbie punktów (malejąco)
    tabelaDanych.sort((a, b) => b.punkty - a.punkty);

    // Ustalanie niezależnych pozycji dla graczy
    let aktualnaPozycja = 1;
    let graczeNaPozycji = 1;
    tabelaDanych[0].pozycja = aktualnaPozycja; // Pierwszemu graczowi przypisujemy pozycję 1

    for (let i = 1; i < tabelaDanych.length; i++) {
        if (tabelaDanych[i].punkty === tabelaDanych[i - 1].punkty) {
            tabelaDanych[i].pozycja = "-";
            graczeNaPozycji++;
        } else {
            aktualnaPozycja += graczeNaPozycji;
            tabelaDanych[i].pozycja = aktualnaPozycja;
            graczeNaPozycji = 1; // Resetujemy licznik graczy na pozycji
        }
    }

    // Prezentacja danych użytkownika
    var daneUzytkownika = tabelaDanych.find(gracz => gracz.uid == userID);
    var pozycjaUzytkownika = daneUzytkownika ? daneUzytkownika.pozycja : "Informacje o użytkowniku nie są dostępne.";
    userInfoDiv.innerHTML = `<p>${daneUzytkownika.nick}: ${daneUzytkownika.punkty} punktów (Pozycja: ${pozycjaUzytkownika})</p>`;

    function generujTabele(limit = null) {
        var html = '<table>';
        html += '<tr><th>Pozycja</th><th>Nick</th><th>Punkty</th><th>Punkty za mecze (dokładne trafienia)</th><th>Punkty za pytania</th></tr>';

        let licznikWidocznychPozycji = 0;
        let dodanoPustyWiersz = false;

        tabelaDanych.forEach(function(gracz, index) {
            let pokazGracza = index < limit || gracz.uid == userID || !limit;
            let klasaStylu = index < 3 ? `class="miejsce-${index + 1}"` : (gracz.uid == userID ? 'class="user-row"' : '');

            if (limit && licznikWidocznychPozycji === limit && !dodanoPustyWiersz && gracz.uid != userID && pozycjaUzytkownika > limit) {
                html += '<tr><td></td><td>...</td><td>...</td><td>...</td><td>...</td></tr>'; // Dodajemy pusty wiersz przed użytkownikiem
                dodanoPustyWiersz = true;
            }

            if (pokazGracza || (dodanoPustyWiersz && gracz.uid == userID)) {
                html += `<tr ${klasaStylu}>
                            <td>${gracz.pozycja}</td>
                            <td>${gracz.nick}</td>
                            <td>${gracz.punkty}</td>
                            <td>${gracz.punktyZaMecze} (${gracz.dokladneTrafienia})</td>
                            <td>${gracz.punktyZaPytania}</td>
                        </tr>`;
                licznikWidocznychPozycji++;
            }
        });

        html += '</table>';
        document.getElementById('tabelaGraczyContainer').innerHTML = html;
    }

    generujTabele(10); // Generuj tabelę z limitem 10 na start

    document.getElementById('przelacznikWidoku').addEventListener('click', function() {
        if (this.innerText === "Pełny widok") {
            generujTabele(); // Pokaż pełną tabelę
            this.innerText = "Widok skrócony";
        } else {
            generujTabele(10); // Pokaż tylko top 10 i ewentualnie użytkownika
            this.innerText = "Pełny widok";
        }
    });
});
</script>
<style>
.miejsce-1 { font-weight: bold; }
.miejsce-2 { font-style: italic; }
.miejsce-3 { text-decoration: underline; }
.user-row { background-color: #ffff99; } /* Wyróżnienie wiersza użytkownika */
</style>
<div id="userInfo"></div>
<hr>
<div id="tabelaGraczyContainer"></div>
<button id="przelacznikWidoku">Widok skrócony</button>