<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabelaDanych = <?php echo json_encode($tabelaDanych); ?>;
    var userID = <?php echo json_encode($userID); ?>;
    var aktualnyFiltr = 'pelny';
    var widokSkrócony = true; // Domyślnie skrócony widok

// Funkcja pomocnicza do ustalania pozycji z uwzględnieniem równych wyników
function ustalPozycje(dane, filtr) {
    let aktualnaPozycja = 1;
    let graczeNaPozycji = 1; // Liczba graczy na aktualnej pozycji
    dane[0].wyswietlanaPozycja = aktualnaPozycja; // Pierwszemu graczowi przypisujemy pozycję 1

    for (let i = 1; i < dane.length; i++) {
        if (dane[i][filtr] === dane[i - 1][filtr]) {
            dane[i].wyswietlanaPozycja = '-';
            graczeNaPozycji++; // Zwiększamy liczbę graczy na tej samej pozycji
        } else {
            aktualnaPozycja += graczeNaPozycji; // Zwiększamy pozycję o liczbę graczy na poprzedniej pozycji
            dane[i].wyswietlanaPozycja = aktualnaPozycja;
            graczeNaPozycji = 1; // Resetujemy liczbę graczy na pozycji
        }
    }
}

function sortujIDodajPozycje(filtr) {
    if (filtr === 'pelny') {
        // Dla pełnego widoku sortuj dane na podstawie ogólnej liczby punktów
        tabelaDanych.sort((a, b) => b.punkty - a.punkty);
        ustalPozycje(tabelaDanych, 'punkty');
    } else {
        // Dla innych filtrów sortuj dane na podstawie wybranej kategorii
        tabelaDanych.sort((a, b) => b[filtr] - a[filtr]);
        ustalPozycje(tabelaDanych, filtr);
    }
}

function generujTabele(filtr) {
    sortujIDodajPozycje(filtr);
    if (filtr === 'pelny') {
        // Dla pełnego widoku sortuj dane na podstawie ogólnej liczby punktów
        tabelaDanych.sort((a, b) => b.punkty - a.punkty);
    } else {
        // Dla innych filtrów sortuj dane na podstawie wybranej kategorii
        tabelaDanych.sort((a, b) => b[filtr] - a[filtr]);
    }
    // Po sortowaniu zawsze ustal pozycje
    ustalPozycje(tabelaDanych);



        sortujIDodajPozycje(filtr);
        var html = '<table>';
        html += '<tr><th>Pozycja</th><th>Nick</th>';

        switch (filtr) {
            case 'pelny':
                html += '<th>Punkty</th><th>Punkty za mecze (dokładne trafienia)</th><th>Punkty za pytania</th>';
                break;
            case 'punktyZaMecze':
                html += '<th>Punkty za mecze</th>';
                break;
            case 'dokladneTrafienia':
                html += '<th>Dokładnie wytypowane mecze</th>';
                break;
            case 'punktyZaPytania':
                html += '<th>Punkty za pytania</th>';
                break;
        }

        html += '</tr>';

        let limit = widokSkrócony ? 10 : tabelaDanych.length;
        tabelaDanych.slice(0, limit).forEach(gracz => {
            let klasaStylu = gracz.uid == userID ? 'class="user-row"' : '';
            html += `<tr ${klasaStylu}><td>${gracz.wyswietlanaPozycja}</td><td>${gracz.nick}</td>`;
            
            switch (filtr) {
                case 'pelny':
                    html += `<td>${gracz.punkty}</td><td>${gracz.punktyZaMecze} (${gracz.dokladneTrafienia})</td><td>${gracz.punktyZaPytania}</td>`;
                    break;
                case 'punktyZaMecze':
                    html += `<td>${gracz.punktyZaMecze}</td>`;
                    break;
                case 'dokladneTrafienia':
                    html += `<td>${gracz.dokladneTrafienia}</td>`;
                    break;
                case 'punktyZaPytania':
                    html += `<td>${gracz.punktyZaPytania}</td>`;
                    break;
            }

            html += `</tr>`;
        });

        html += '</table>';
        document.getElementById('tabelaGraczyContainer').innerHTML = html;
    }

    document.querySelectorAll('.filtr').forEach(button => {
        button.addEventListener('click', function() {
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

<!-- Przyciski filtrów -->
<button class="filtr" data-filtr="pelny">Pełny widok</button>
<button class="filtr" data-filtr="punktyZaMecze">Punkty za mecze</button>
<button class="filtr" data-filtr="dokladneTrafienia">Dokładnie wytypowane mecze</button>
<button class="filtr" data-filtr="punktyZaPytania">Punkty za pytania</button>



<div class="row">
  <div class="col mt-3 mb-3">
  <p>A teraz to, po co wszyscy tu przychodzimy, czyli...</p>
  <h3>Aktualna tabela Typera Mistrzostw</h3>
  </div>
</div>
<div class="row">
<div class="col">
<div id="tabelaGraczyContainer" class="table"></div>
</div>
</div>

<!-- Przycisk przełączania między pełnym a skróconym widokiem -->
<button id="przelacznikWidoku">Rozwiń tabelę</button>