<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabelaDanych = <?php echo json_encode($tabelaDanych); ?>;
    var userID = <?php echo json_encode($userID); ?>;
    var aktualnyFiltr = 'pelny';
    var widokSkrócony = true; // Domyślnie skrócony widok

    function ustalPozycje(dane, filtr) {
        let kluczSortowania;
        switch(filtr) {
            case 'punktyZaMecze':
                kluczSortowania = 'punktyZaMecze';
                break;
            case 'punktyZaPytania':
                kluczSortowania = 'punktyZaPytania';
                break;
            default:
                kluczSortowania = 'punkty';
        }

        dane.sort((a, b) => b[kluczSortowania] - a[kluczSortowania]);

        let pozycje = [];
        let aktualnaPozycja = 1;

        for (let i = 0; i < dane.length; i++) {
            if (i === 0 || dane[i][kluczSortowania] !== dane[i - 1][kluczSortowania]) {
                aktualnaPozycja = i + 1;
            }
            pozycje.push({
                uid: dane[i].uid,
                nick: dane[i].nick,
                punkty: dane[i][kluczSortowania],
                dokladneTrafienia: dane[i].dokladneTrafienia,
                pozycja: (i === 0 || dane[i][kluczSortowania] !== dane[i - 1][kluczSortowania]) ? aktualnaPozycja : '-'
            });
        }

        return pozycje;
    }

    function getKolor(liczbaGraczyZWiekszaLiczbaPunktow, uid) {
        if (uid === userID && liczbaGraczyZWiekszaLiczbaPunktow > 2) {
            return 'bg-light'; // szary dla zalogowanego użytkownika, jeśli ma więcej niż 2 graczy z większą liczbą punktów
        } else if (liczbaGraczyZWiekszaLiczbaPunktow === 0) {
            return 'bg-warning'; // gold
        } else if (liczbaGraczyZWiekszaLiczbaPunktow === 1) {
            return 'bg-secondary'; // silver
        } else if (liczbaGraczyZWiekszaLiczbaPunktow === 2) {
            return 'bg-danger'; // bronze
        } else {
            return '';
        }
    }

    function generujTabele(pozycje, filtr) {
        var html = '<table class="table">';
        html += '<thead><tr><th>#</th><th>Nick</th><th class="text-center">Punkty</th>';
        if (filtr === 'punktyZaMecze') {
            html += '<th class="text-center">Dokładne trafienia</th>';
        }
        html += '</tr></thead>';
        html += '<tbody>';

        let pozycjaUzytkownika = pozycje.findIndex(p => p.uid == userID) + 1;
        let liczbaGraczyZWiekszaLiczbaPunktow = pozycje.filter(p => p.punkty > pozycje.find(p => p.uid == userID).punkty).length;
        let limit = widokSkrócony ? 10 : pozycje.length;

        pozycje.slice(0, limit).forEach(gracz => {
            let liczbaGraczyZWiekszaLiczbaPunktowGracz = pozycje.filter(p => p.punkty > gracz.punkty).length;
            let klasaStylu = getKolor(liczbaGraczyZWiekszaLiczbaPunktowGracz, gracz.uid);

            let wyswietlanaPozycja = gracz.pozycja === '-' && gracz.uid == userID ? liczbaGraczyZWiekszaLiczbaPunktow + 1 : gracz.pozycja;
            html += `<tr class="${klasaStylu}"><td>${wyswietlanaPozycja}</td><td>${gracz.nick}</td><td class="text-center">${gracz.punkty}</td>`;
            if (filtr === 'punktyZaMecze') {
                html += `<td class="text-center">${gracz.dokladneTrafienia}</td>`;
            }
            html += '</tr>';
        });

        if (widokSkrócony && pozycjaUzytkownika > 10) {
            html += '<tr><td colspan="4">&nbsp;</td></tr>'; // Pusty wiersz dla oddzielenia
            let daneUzytkownika = pozycje.find(p => p.uid == userID);
            html += `<tr class="user-row ${getKolor(liczbaGraczyZWiekszaLiczbaPunktow, userID)}"><td>${liczbaGraczyZWiekszaLiczbaPunktow + 1}</td><td>${daneUzytkownika.nick}</td><td class="text-center">${daneUzytkownika.punkty}</td>`;
            if (filtr === 'punktyZaMecze') {
                html += `<td class="text-center">${daneUzytkownika.dokladneTrafienia}</td>`;
            }
            html += '</tr>';
        }

        html += '</tbody></table>';
        document.getElementById('tabelaGraczyContainer').innerHTML = html;
    }

    document.querySelectorAll('.filtr').forEach(link => {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            document.querySelectorAll('.filtr').forEach(lnk => lnk.classList.remove('active'));
            this.classList.add('active');
            aktualnyFiltr = this.dataset.filtr;
            let pozycje = ustalPozycje(tabelaDanych, aktualnyFiltr);
            generujTabele(pozycje, aktualnyFiltr);
        });
    });

    document.getElementById('przelacznikWidoku').addEventListener('click', function() {
        widokSkrócony = !widokSkrócony;
        this.innerText = widokSkrócony ? "Rozwiń tabelę" : "Zwiń tabelę";
        let pozycje = ustalPozycje(tabelaDanych, aktualnyFiltr);
        generujTabele(pozycje, aktualnyFiltr);
    });

    let pozycje = ustalPozycje(tabelaDanych, aktualnyFiltr);
    generujTabele(pozycje, aktualnyFiltr);
});
</script>

<style>
.bg-warning { background-color: #ffd700 !important; } /* Złote tło */
.bg-secondary { background-color: #c0c0c0 !important; } /* Srebrne tło */
.bg-danger { background-color: #cd7f32 !important; } /* Brązowe tło */
.bg-light { background-color: #d3d3d3 !important; } /* Szare tło */
.user-row { font-weight: bold; }
</style>

<div class="row">
  <div class="col mt-3 mb-3">
    <p>A teraz to, po co wszyscy tu przychodzimy, czyli...</p>
    <h3>Aktualna tabela Typera Mistrzostw</h3>
  </div>
</div>
<ul class="nav nav-tabs">
  <li class="nav-item">
    <a class="nav-link active filtr" data-filtr="pelny" href="#">Ogółem</a>
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
    <div id="tabelaGraczyContainer" class="table-responsive"></div>
  </div>
</div>

<button id="przelacznikWidoku" class="btn btn-primary mt-3">Rozwiń tabelę</button>  