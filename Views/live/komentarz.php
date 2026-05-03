<p>Komentarz autorski</p>

<p>Czy świat potrzebuje nowego / kolejnego livescore'a? Nie. Jest ich dużo i są bardzo kompleksowe. </p>

<p>Mam z nimi tylko jeden problem. Nie są dla mnie wygodne. Nie interesują mnie wszystkie ligi świata, interesuje mnie Ekstraklasa (tak, wiem, to masochizm), Bundesliga (i druga Bundesliga specjalnie dla Pawła), Premiership, La Liga. Liga mistrzów. Liga Europy (no chyba że jednak nie). Pierwsza liga (mam swoje powody, a jak Wisła spadnie (oby nie) - to będę miał kolejny). Interesują mnie turnieje Euro i Mistrzostwa Świata... Taki ze mnie niedzielny kibic.</p>

<p>Zakładam, że wyniki będę sprawdzał na komórce. Ma być szybko, wygodnie i czytelnie. </p>

<p>Więc.. świat nie potrzebuje. Ja potrzebuję. </p>

<h2>Stan na 19 lipca 2022</h2>
<p>Znowu wszystko zacząłem od nowa. Sama przyjemność. I troszkę nawet na chwilę ten pomysł porzuciłem. Ale wrócił, a ja znowu mam przyjemność z kombinowania jak chcę to zrobić i co chcę, żeby działało. </p>
<p>Co się zadziało od ostatniego razu? Zmieniłem serwer, przeniosłem tylko część funkcjonalności (bo to nie była dobra funkcjonalność). Czyli w zasadzie, zaczynamy od początku.</p>
<h2>Co działa?</h2>
<figure><table>
<thead>
<tr><th style='text-align:left;' >Funkcja</th><th style='text-align:left;' >Stan</th><th style='text-align:left;' >Komentarz</th></tr></thead>
<tbody><tr><td style='text-align:left;' >Wynik meczów rozgrywanych live</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >&nbsp;</td></tr><tr><td style='text-align:left;' >Inne mecze zaplanowane na dziś</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >Muszę uspójnić górę z dołem</td></tr><tr><td style='text-align:left;' >Szczegóły meczu live</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >I to mi się (prawdę powiedziawszy) podoba i udało. Są strzelcy, są zmiany, są ikonki</td></tr><tr><td style='text-align:left;' >Mądre cache&#39;owanie</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >I to jest spoko. Cache&#39;uje sobie wybrane treści co minutę (lub rzadziej w zależności od potrzeby).</td></tr><tr><td style='text-align:left;' >Odświeżanie wyników</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >A to mnie właśnie chyba najbardziej jara, bo okazuje się, że strona samodzielnie się odświeża, przy okazji nie wywalając pamięci podręcznej w kosmos i nie plując błędami. PHP + Jquery does the trick</td></tr><tr><td style='text-align:left;' >Zapamiętywanie aktywnego meczu</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >Kiedy użytkownik wybierze sobie, jaki mecz go interesuje (tzn kliknie w jego ikonkę "i") to strona to zapamięta i po odświeżeniu, wybrany mecze (mecze) będzie wciąż otwarty i będzie widział szczegóły. A co!</td></tr></tbody>
</table></figure>

<h2>Jakie rozgrywki są obsługiwane?</h2>
<figure><table>
<thead>
<tr><th style='text-align:left;' >Kraj / Federacja</th><th style='text-align:left;' >Rozgrywki</th><th style='text-align:left;' >Live</th><th style='text-align:left;' >Terminarz &amp; Tabela</th></tr></thead>
<tbody><tr><td style='text-align:left;' >FIFA</td><td style='text-align:left;' >🏆🌏: Mistrzostwa Świata,<br> Puchar Narodów Afryki</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >👎🏻</td></tr><tr><td style='text-align:left;' >UEFA</td><td style='text-align:left;' >Mistrzostwa Europy,<br> 🏆LM: Liga Mistrzów,<br>🇪🇺 LE: Liga Europy,<br>🇪🇺 LK: Liga Konferencji,<br> Liga Narodów</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >👎🏻</td></tr><tr><td style='text-align:left;' >Anglia</td><td style='text-align:left;' >🏴󠁧󠁢󠁥󠁮󠁧󠁿: Premiership,<br> 🏴󠁧󠁢󠁥󠁮󠁧󠁿🏆: FA CUP</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >👎🏻</td></tr><tr><td style='text-align:left;' >Niemcy</td><td style='text-align:left;' >🇩🇪: Bundesliga,<br> 🏆: DFB CUP</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >👎🏻</td></tr><tr><td style='text-align:left;' >Włochy</td><td style='text-align:left;' >🇮🇹: Serie A,<br> 🇮🇹🏆: Coppa Italia</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >👎🏻</td></tr><tr><td style='text-align:left;' >Hiszpania</td><td style='text-align:left;' >🇪🇸: Santander La Liga,<br> 🇪🇸🏆: Copa del Rey</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >👎🏻</td></tr><tr><td style='text-align:left;' >Polska</td><td style='text-align:left;' >🇵🇱<em>E: Ekstraklasa,<br> 🇵🇱</em>1L: 1wsza liga,<br> 🇵🇱🏆: Puchar Polski</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >👎🏻</td></tr><tr><td style='text-align:left;' >Inne</td><td style='text-align:left;' >🤝: Mecze towarzyskie</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >👎🏻</td></tr></tbody>
</table></figure>
<h2></h2>


<h2>Co dopiero będzie działać?</h2>
<figure><table>
<thead>
<tr><th style='text-align:left;' >Funkcja</th><th style='text-align:left;' >Jak ważne?</th><th style='text-align:left;' >Kiedy / komentarz</th></tr></thead>
<tbody><tr><td style='text-align:left;' >Wyświetlanie meczów zakończonych</td><td style='text-align:left;' >Dość ważne</td><td style='text-align:left;' >Nie jestem dobry w planowaniu</td></tr><tr><td style='text-align:left;' >Automatyczna strona rozgrywek</td><td style='text-align:left;' >W zasadzie, to wszystko co związane z rozgrywkami wymaga wypracowania, bo na razie jest nieistniejące (musiałem nawet wyczyścić menu, żeby nie straszyło). Czyli to jest do nadrobienia, nawet jeśli cząstki tego już działały</td><td style='text-align:left;' >&nbsp;</td></tr><tr><td style='text-align:left;' >półautomatyczna strona rozgrywek (kolejka wpisana z palca)</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >&nbsp;</td></tr><tr><td style='text-align:left;' >Tabela per rozgrywki</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >&nbsp;</td></tr><tr><td style='text-align:left;' >Najlepsi strzelcy per rozgrywki</td><td style='text-align:left;' >👍🏻</td><td style='text-align:left;' >&nbsp;</td></tr><tr><td style='text-align:left;' >Szczegóły meczu archiwalnego</td><td style='text-align:left;' >👎🏻</td><td style='text-align:left;' >&nbsp;</td></tr><tr><td style='text-align:left;' >Uporządkowane rozgrywki (starsze mecze)</td><td style='text-align:left;' >👎🏻</td><td style='text-align:left;' >&nbsp;</td></tr></tbody>
</table></figure>
<h2>Historia mojej nauki...</h2>

<h3>Rozpiska na 10.04</h3>

<p>1) przerzucić skrypt z prototypu na front (próbując nie zepsuć tego &quot;po drodze&quot;
2) ogarnąć ten aktualny terminarz... chyba na podstawie tabeli. Ale czy to się uda... ?</p>

<p>Jestem niemal pewien, że coś zepsuję. </p>

<p>Szybki edit. Muszę pomyśleć jak to zrobić;/</p>

<br><br>

<h3>Rozpiska na 30.03.2022</h3>
<p>Szalony pomysł na dziś wygląda tak, że... trzeba uporządkować skrypty, bo one chyba robią dużo zamieszania. Stąd propozycja struktury:<p>
- header<br>
- elementy interesujące<br>
- footer<br>
- skrypty<br>
<p>A, tak poza wszystkim, staram się wyciąć wszystko co zbędne (pod kątem css, pod kątem skryptów właśnie, będzie brzydko, tzn minimalistycznie)</p>
<br><br>
<h3>Rozpiska na 29.03.2022</h3>
<p>Właśnnie się dowiedziałem / zorientowałem, że mój sposób na refresh morduje baterię i przeglądarkę i pamięć. Zło. Back to square 1.</p>


<h3>Rozpiska na 27.03.2022</h3>

<p>Ok. Chyba poprawiłem odświeżanie wyników live (co prawda, przy okazji mogłem spieprzyć nawigację. Coś za coś).</p>
<p>Równocześnie, nauczyłem się robić zegarek :D Taki ciut udawany, bo odświeżający się co 11 sekund :D no ale jest.</p>
<p>Teraz chcę spróbować robić tak, żeby okiełznać parametry przekazywane do funkcji... Tzn chodzi o to, żeby naumieć się parametrów domyślnych. Yeap...</p>
<p>Aha... dla dociekliwych eksperymenty dzieją się na http://jakiwynik.com/prototyp</p>
<p>I chciałbym wiedzieć dlaczego za dużo jest połączeń. Tego do końca nie kumam. No ale po kolei.</p>

<br><br>

<h3>Rozpiska na 26.03.2022</h3>
<p> Było kilka dni przerwy... </p>
<p>Cholera. Przestało działać. Trzeba mi naprawiać.</p>
<p>I trzeba się nauczyć aktualizować stronę główną. Pewnie od tego dobrze by było zacząć. Żeby aktualizować, co minutę, tylko część strony. A jeśli nie ma wcale albo już meczów, które są zapisane &quot;na dziś&quot; to trzeba grzecznie wyłączyć ten opis. + może dopisać &quot;sprawdź jutrzejsze&quot;. Hmm...
No to do dzieła. </p>

<br><br>

<h3>Rozpiska na 19.03.2022</h3>

<p>Wczoraj wieczorem, serwis zaczął pluć błędami. Trochę tak, jakby słał za dużo zapytań). Trzeba mi zrobić mądrzejsze cache'owanie. </p>

<br><br>

<h3>Rozpiska na 18.03.2022</h3>

<p>[] Może dobrze by było dodać jakieś szczegóły meczu..
[] Aaaaby to zrobić, to... zacznę od meczów archiwalnych. Zamienię je na akordeony i zobaczymy co dalej. </p>

<br><br>


<h3>Rozpiska na 17.03.2022</h3>

<ol>
<li>Wdrożyłem cache'owanie na podstronach strzelców i tabeli. Per rozgrywki. Yey! To pozwoli na mądrzejsze zaciąganie tych danych. Zaaaaaajefajnie.</li>
<li>Uzupełniłem nawigację o 2.gą Bundesligę, Premiership i La Liga</li>
<li>Chyba nadużywam akordeonu;/</li>
</ol>


<br><br>

<h3>Rozpiska na 16.03.2022</h3>

<p>Jest podgląd wyniku live wybranych meczów z terminem &quot;dziś&quot;. Jest możliwość łatwego wyboru ligi (Ekstraklasa / 1liga / Bundesliga). TODO: uzupełnić o Premiership, LaLiga. </p>

<p>Dla każdej ligi jest Terminarz najbliższej kolejki (problem - kolejkę muszę podać z palca. To nie jest wygodne). Jest aktualna tabela i najlepsi strzelcy. </p>

<p>Chcę dodać cache'owanie (bo na razie każde odświeżenie to niepotrzebne zapytania). Ale to chce zrobić na poziomie widoku per rozgrywki (zastosowanie cache'owania na poziomie widoku budującego podstronę doprowadziło do tego, że wszystkie tabele rozgrywek były trochę za bardzo takie same (wszędzie była ekstraklasa).</p>

<p>Chcę dodać </p>

<p>Będę psuł bardziej, zanim zacznę naprawiać </p>