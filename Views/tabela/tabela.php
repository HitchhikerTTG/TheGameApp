<script>
document.addEventListener('DOMContentLoaded', function() {
    var tabelaDanych = <?php echo json_encode($tabelaDanych); ?>;
    var userID = <?php echo json_encode($userID); ?>;
    var aktualnyFiltr = 'pelny';
    var widokSkrócony = true; // Domyślnie skrócony widok

    function ustalPozycje(dane) {
        dane.sort((a, b) => b.punkty - a.punkty);

        let pozycje = [];
        let aktualnaPozycja = 1;

        for (let i = 0; i < dane.length; i++) {
            if (i === 0 || dane[i].punkty !== dane[i - 1].punkty) {
                aktualnaPozycja = i + 1;
            }
            pozycje.push({
                uid: dane[i].uid,
                nick: dane[i].nick,
                punkty: dane[i].punkty,
                pozycja: aktualnaPozycja,
                wyswietlanaPozycja: (i === 0 || dane[i].punkty !== dane[i - 1].punkty) ? aktualnaPozycja : '-'
            });
        }

        return pozycje;
    }

    function generujTabele(pozycje) {
        var html = '<table class="table">';
        html += '<thead class="table-dark"><tr><th>#</th><th>Nick</th><th class="text-center">Punkty</th></tr></thead>';
        html += '<tbody>';

        let pozycjaUzytkownika = pozycje.findIndex(p => p.uid == userID) + 1;
        let liczbaGraczyZWiekszaLiczbaPunktow = pozycje.filter(p => p.punkty > pozycje.find(p => p.uid == userID).punkty).length;
        let limit = widokSkrócony ? 10 : pozycje.length;

        pozycje.slice(0, limit).forEach(gracz => {
            let klasaStylu = '';
            if (gracz.uid == userID) {
                klasaStylu = 'table-light';
            } else if (gracz.pozycja == 1) {
                klasaStylu = 'table-warning';
            } else if (gracz.pozycja == 2) {
                klasaStylu = 'table-secondary';
            } else if (gracz.pozycja == 3) {
                klasaStylu = 'table-danger';
            }
            let wyswietlanaPozycja = gracz.wyswietlanaPozycja;
            html += `<tr class="${klasaStylu}"><td>${wyswietlanaPozycja}</td><td>${gracz.nick}</td><td class="text-center">${gracz.punkty}</td></tr>`;
        });

        if (widokSkrócony && pozycjaUzytkownika > 10) {
            html += '<tr><td colspan="3">&nbsp;</td></tr>'; // Pusty wiersz dla oddzielenia
            let daneUzytkownika = pozycje.find(p => p.uid == userID);
            html += `<tr class="table-light"><td>${liczbaGraczyZWiekszaLiczbaPunktow + 1}</td><td>${daneUzytkownika.nick}</td><td class="text-center">${daneUzytkownika.punkty}</td></tr>`;
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
            let pozycje = ustalPozycje(tabelaDanych);
            generujTabele(pozycje);
        });
    });

    document.getElementById('przelacznikWidoku').addEventListener('click', function() {
        widokSkrócony = !widokSkrócony;
        this.innerText = widokSkrócony ? "Rozwiń tabelę" : "Zwiń tabel