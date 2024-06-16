<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabelaDanych = <?php echo json_encode($tabelaDanych); ?>;
    var userID = <?php echo json_encode($userID); ?>;
    var aktualnyFiltr = 'pelny';
    var widokSkrócony = true; // Domyślnie skrócony widok
    
    console.log('Uzytkownik', userID);

    function ustalPozycje(dane, filtr) {
        let aktualnaPozycja = 1;
        dane[0].wyswietlanaPozycja = aktualnaPozycja;
        let graczeNaPozycji = 1;

        for (let i = 1; i < dane.length; i++) {
            if (dane[i][filtr] === dane[i - 1][filtr]) {
                dane[i].wyswietlanaPozycja = '-';
                graczeNaPozycji++;
            } else {
                aktualnaPozycja += graczeNaPozycji;
                dane[i].wyswietlanaPozycja = aktualnaPozycja;
                graczeNaPozycji = 1;
            }
        }
    }

    function sortujIDodajPozycje(filtr) {
        if (filtr === 'pelny') {
            tabelaDanych.sort((a, b) => b.punkty - a.punkty);
            ustalPozycje(tabelaDanych, 'punkty');
        } else {
            tabelaDanych.sort((a, b) => b[filtr] - a[filtr]);
            ustalPozycje(tabelaDanych, filtr);
        }
    }

function generujTabele(filtr) {
    sortujIDodajPozycje(filtr);
    var html = '<table class="table">';
    html += '<tr><th>#</th><th>Nick</th>';

    switch (filtr) {
        case 'pelny':
            html += '<th class="text-center">Punkty</th>';
            break;
        case 'punktyZaMecze':
            html += '<th class="text-center">Punkty</th><th class="text-end">Dokładny wynik</th>';
            break;
        case 'dokladneTrafienia':
            html += '<th>Dokładne</th>';
            break;
        case 'punktyZaPytania':
            html += '<th>Punkty</th>';
            break;
    }

    html += '</tr>';

    // Określenie pozycji użytkownika zgodnie z pełnym widokiem
    let pozycjaUzytkownikaWPelnejTabeli = tabelaDanych.findIndex(gracz => gracz.uid == userID) + 1;
    let pozycjaUzytkownika = pozycjaUzytkownikaWPelnejTabeli; // Użyjemy tej zmiennej do wyświetlenia pozycji użytkownika

    let limit = widokSkrócony ? 10 : tabelaDanych.length;
    tabelaDanych.slice(0, limit).forEach((gracz, index) => {
        let klasaStylu = gracz.uid == userID ? 'class="user-row"' : '';
        let wyswietlanaPozycja = gracz.wyswietlanaPozycja === '-' && gracz.uid == userID ? pozycjaUzytkownikaWPelnejTabeli : gracz.wyswietlanaPozycja;

        html += `<tr ${klasaStylu}><td>${wyswietlanaPozycja}</td><td>${gracz.nick}</td>`;

        // Wypisanie wartości kolumn zgodnie z wybranym filtrem
        switch (filtr) {
            case 'pelny':
                html += `<td class="text-center">${gracz.punkty}</td>`;
                break;
            case 'punktyZaMecze':
                html += `<td class="text-center">${gracz.punktyZaMecze}</td><td class="text-end">${gracz.dokladneTrafienia}</td>`;
                break;
            case 'dokladneTrafienia':
                html += `<td>${gracz.dokladneTrafienia}</td>`;
                break;
            case 'punktyZaPytania':
                html += `<td class="text-end">${gracz.punktyZaPytania}</td>`;
                break;
        }

        html += `</tr>`;
    });

// Dodanie dodatkowego wiersza dla użytkownika w skróconej wersji tabeli, jeśli jest poza top 10
if (widokSkrócony && pozycjaUzytkownikaWPelnejTabeli > 10) {
    html += '<tr><td colspan="5">&nbsp;</td></tr>'; // Pusty wiersz dla oddzielenia
    let daneUzytkownika = tabelaDanych.find(gracz => gracz.uid == userID);
    if (daneUzytkownika) {
        html += `<tr class="user-row"><td>${pozycjaUzytkownika}</td><td>${daneUzytkownika.nick}</td>`;

        // Wypisanie wartości kolumn zgodnie z wybranym filtrem
        switch (filtr) {
            case 'pelny':
                html += `<td class="text-center">${daneUzytkownika.punkty}</td>`;
                break;
            case 'punktyZaMecze':
                html += `<td class="text-center">${daneUzytkownika.punktyZaMecze}</td><td class="text-end">${daneUzytkownika.dokladneTrafienia}</td>`;
                break;
            case 'dokladneTrafienia':
                html += `<td>${daneUzytkownika.dokladneTrafienia}</td>`;
                break;
            case 'punktyZaPytania':
                html += `<td class="text-end">${daneUzytkownika.punktyZaPytania}</td><td></td>`;
                break;
        }

        html += `</tr>`;
    }
}

    html += '</table>';
    document.getElementById('tabelaGraczyContainer').innerHTML = html;
}

    document.querySelectorAll('.filtr').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault(); // Zapobiegaj przeładowaniu strony
            document.querySelectorAll('.filtr').forEach(lnk => lnk.classList.remove('active')); // Usuń klasę 'active' z wszystkich zakładek
            this.classList.add('active'); // Dodaj klasę 'active' do klikniętej zakładki
            aktualnyFiltr = this.dataset.filtr;
            generujTabele(aktualnyFiltr);
        });
    });


    document.getElementById('przelacznikWidoku').addEventListener('click', function() {
        widokSkrócony = !widokSkrócony;
        this.innerText = widokSkrócony ? "Rozwiń tabelę" : "Zwiń tabelę";
        generujTabele(aktualnyFiltr);
    });

    generujTabele('pelny'); // Generuj pełną tabelę przy pierwszym ładowaniu
});
</script>

<!-- Przyciski filtrów 
<button class="filtr" data-filtr="pelny">Ogółem</button>
<button class="filtr" data-filtr="punktyZaMecze">Mecze</button>
<button class="filtr" data-filtr="dokladneTrafienia">Dokładne</button>
<button class="filtr" data-filtr="punktyZaPytania">Pytania</button>
-->

<style>
.miejsce-1 { font-weight: bold; }
.miejsce-2 { font-style: italic; }
.miejsce-3 { text-decoration: underline; }
.user-row { background-color: #ffff99; } /* Wyróżnienie wiersza użytkownika */
</style>

<div class="row">
  <div class="col mt-3 mb-3">
  <p>A teraz to, po co wszyscy tu przychodzimy, czyli...</p>
  <h3>Aktualna tabela Typera Mistrzostw</h3>
  </div>
</div>
<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link active filtr" data-filtr="pelny" href="#">Ogółem </a>
  </li>
  <li class="nav-item">
    <a class="nav-link filtr" data-filtr="punktyZaMecze" href="#">Mecze</a>
  </li>
  <li class="nav-item">
    <a class="nav-link filtr" data-filtr="punktyZaPytania" href="#">Pytania</a>
  </li>
</ul>
<div class="row">
<div class="col">
<div id="tabelaGraczyContainer" class="table"></div>
</div>
</div>

<!-- Przycisk przełączania między pełnym a skróconym widokiem -->
<button id="przelacznikWidoku">Rozwiń tabelę</button>


<pre>
<?/* print_r($tabelaDanych); */?>
</pre>