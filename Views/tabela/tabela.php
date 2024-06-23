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
                pozycja: (i === 0 || dane[i].punkty !== dane[i - 1].punkty) ? aktualnaPozycja : '-'
            });
        }

        return pozycje;
    }

    function generujTabele(pozycje) {
        var html = '<table class="table">';
        html += '<tr><th>#</th><th>Nick</th><th class="text-center">Punkty</th></tr>';

        let pozycjaUzytkownika = pozycje.find(p => p.uid == userID).pozycja;
        let limit = widokSkrócony ? 10 : pozycje.length;

        pozycje.slice(0, limit).forEach(gracz => {
            let klasaStylu = gracz.uid == userID ? 'class="user-row"' : '';
            html += `<tr ${klasaStylu}><td>${gracz.pozycja}</td><td>${gracz.nick}</td><td class="text-center">${gracz.punkty}</td></tr>`;
        });

        if (widokSkrócony && pozycjaUzytkownika > 10) {
            html += '<tr><td colspan="3">&nbsp;</td></tr>'; // Pusty wiersz dla oddzielenia
            let daneUzytkownika = pozycje.find(p => p.uid == userID);
            html += `<tr class="user-row"><td>${daneUzytkownika.pozycja}</td><td>${daneUzytkownika.nick}</td><td class="text-center">${daneUzytkownika.punkty}</td></tr>`;
        }

        html += '</table>';
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
        this.innerText = widokSkrócony ? "Rozwiń tabelę" : "Zwiń tabelę";
        let pozycje = ustalPozycje(tabelaDanych);
        generujTabele(pozycje);
    });

    let pozycje = ustalPozycje(tabelaDanych);
    generujTabele(pozycje);
});
</script>

<style>
.miejsce-1 { font-weight: bold; }
.miejsce-2 { font-style: italic; }
.miejsce-3 { text-decoration: underline; }
.user-row { background-color: #ffff99; }
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
<div id="tabelaGraczyContainer" class="table"></div>
</div>
</div>

<button id="przelacznikWidoku">Rozwiń tabelę</button>