<?php

namespace App\Services;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TerminarzModel;
use App\Models\TypyModel;
use App\Libraries\Common;

    /* 
    Gdzies tu muszę zacząć spisywać logikę. 

    W tym serwisie staramy się pożenić trochę elementów, które zostaną docelowo przekazane do odpowiedniego kontrolera. 

    Kontroler do MeczSerwis: Daj mi dane do meczów (kontroler bedize wiedział o które mecze prosi (najblizsze / do rozegrania / wszystkie) abym mógł przekazać dane do widoku. Dane które będę potrzebował do widoku to tabela asocjacjyjna mecze, w której będzie tabela z mecz_id, typy, joker, punkty dla konkretnego UserID

    MeczSerwis do terminarza: Daj mi numery meczów, o które poprosił kontroler ale upewnij się, że mamy pliki json w odpowiedniej lokalizacji dla tych meczów - jeśli ich nie mamy, lub są „z wczoraj” a mecze są jeszcze niezakończone Lub ich data jest przed dziś - pobierz dane z serwisu i zaktualizuj JSON (json powinien zawierać także datę aktualizacji oraz status meczu)

    MeczSerwis do typów: Daj mi typy użytkownika dla podanej tabeli meczów (typ gospodarz, typ gość, czy użył jokera, czy może użyć jokera)

    MeczSerwis do Kontrolera - tu masz tabelę zgodnie z tym o co prosiłeś.

    Trochę idąc od końca... 

    Kontroler powie: Daj mi dane meczów dla użytkownika X w turnieju A zgodnie z filtrem:
            1 = najblizsze
            2 = do_rozegrania
            3 = wszystkie
            4 = rozegrane

    Mecz serwis ma:
        1) sprawdzić jakie są najbliższe mecze w tym turnieju
        2) sprawdzić aktualnośc danych JSON dla tych mecżów (dane wspólne). Stwórz lub uaktualnij jeśli aktualizacja starsza niż dziś
        3) podaj typy użytkownika dla tych mecżów
        4) Połącz te dane i zwróć je do użytkownika    

    01.05.2024 Ha ha, a jednak będzie aktualizacja :]
        1) dodanie informacji o liczbie typów dla danego meczu (pobierz z model) - wydaje się relatively easy
        2) jesli mecz się rozpoczął, opublikuj listę meczów

    */






class MeczService {
    protected $terminarzModel;
    protected $typyModel;
    protected $common;

    public function __construct()
    {
        $this->terminarzModel = new TerminarzModel();
        $this->typyModel = new TypyModel();
        $this->common = new Common();
    }

    public function meczeUzytkownikaWTurnieju($userUniID, $turniejID, $zewnetrznyTurniejID, $filtr){
    $static = ($filtr === 'rozegrane');
    $lista_meczow = [];

    switch ($filtr) {
        case "najblizsze":      $lista_meczow = $this->getMeczeDnia($turniejID, true); break;
        case "najblizsze_24h":  $lista_meczow = $this->terminarzModel->getMecze24h($turniejID, true); break;
        case "do_rozegrania":   $lista_meczow = $this->getMeczeTurniejuDoRozegrania($turniejID, true); break;
        case "wszystkie":       $lista_meczow = $this->getMeczeTurnieju($turniejID, true); break;
        case "rozegrane":       $lista_meczow = $this->getRozegraneMeczeTurnieju($turniejID, true); break;
    }

    // Statyczne archiwum: NIE odpytujemy API (dane zakończonych meczów się nie zmieniają)
    if (!$static) {
        $this->manageJsonFiles($lista_meczow, $turniejID, $zewnetrznyTurniejID);
    }

    $wypelniona_lista = $this->getUserTypesForMatches($lista_meczow, $userUniID, $turniejID, $static);

    if ($static) {
        $ids = array_column($wypelniona_lista, 'Id');
        $liczby = $this->typyModel->liczbaTypowDlaMeczow($ids);   // [GameID => count]
        foreach ($wypelniona_lista as &$mecz) {
            $mecz['liczbaTypow'] = $liczby[$mecz['Id']] ?? 0;
            $mecz['rozpoczety']  = 1;                              // rozegrany = rozpoczęty
        }
        unset($mecz);
        return $wypelniona_lista;                                  // bez czyRozpoczety/UPDATE, bez odswiezLiveMecze
    }

    // ── ścieżka live/przyszłe mecze: bez zmian względem oryginału ──
    foreach ($wypelniona_lista as &$mecz) {
        $mecz['liczbaTypow'] = $this->typyModel->liczbaTypowDlaMeczu($mecz['Id']);
        $mecz['rozpoczety']  = $this->terminarzModel->czyRozpoczety($mecz['Id']);
        if ($mecz['rozpoczety']) {
            $pathTypy = WRITEPATH . "typy/{$mecz['Id']}.json";
            if (!file_exists($pathTypy)) { $this->wygenerujTypyDlaMeczu($mecz['Id']); }
        }
    }
    unset($mecz);

    $this->odswiezLiveMecze($wypelniona_lista, $turniejID, $zewnetrznyTurniejID);
    return $wypelniona_lista;
}


    public function prepareMeczData($meczId, $userId) {
        $mecz = $this->terminarzModel->getMeczById($meczId);
        $typy = $this->typyModel->getTypyByMeczId($meczId);
        $userTyp = $this->typyModel->getTypyByMeczIdAndUserId($meczId, $userId);

        $data = [
            'mecz' => $mecz,
            'typyGraczy' => $typy,
            'typUzytkownika' => $userTyp,
            // dodaj inne dane związane z live i preMecz jeśli potrzebne
        ];

        return $data;
    }

    public function prepareMeczeTurnieju($turniejId) {
        $mecze = $this->terminarzModel->getMeczeByTurniejId($turniejId);
        return $mecze;
        }

    public function getMeczeUzytkownikaWTurnieju($userId, $turniejId) {
        // Pobierz mecze dla danego turnieju
        $mecze = $this->terminarzModel->getMeczeByTurniejId($turniejId);
        $typyModel = new \App\Models\TypyModel();
        
        // Przejrzyj każdy mecz i pobierz dla niego typy użytkownika
        foreach ($mecze as &$mecz) {
            // Pobierz typy dla danego użytkownika i meczu
            $typy = $typyModel->getTypyByMeczIdAndUserId($mecz['Id'], $userId);

            // Jeśli istnieją typy dla meczu, przypisz je, w przeciwnym razie dodaj informację o ich braku
                if (!empty($typy)) {
                    $mecz['typy'] = $typy;
                } else {
                    $mecz['typy'] = 'Brak typów';
                }
        }

        return $mecze;

    }







    public function generatePreMatchData($matchId) {
        $matchDetails = $this->terminarzModel->getMatchDetails($matchId);
        $historicalMatches = $this->terminarzModel->getHistoricalMatches($matchDetails->teamIds);
        $data = [
            'teams' => $matchDetails->teams,
            'history' => $historicalMatches,
            // inne dane
        ];
        file_put_contents("path/to/json/pre-match_$matchId.json", json_encode($data));
    }

    public function updateLiveMatchData($matchId) {
        $liveData = $this->zdarzeniaModel->getLiveMatchData($matchId);
        file_put_contents("path/to/json/live-match_$matchId.json", json_encode($liveData));
    }

    public function generatePostMatchData($matchId) {
        $matchResults = $this->terminarzModel->getMatchResults($matchId);
        $typResults = $this->typyModel->getTypResultsForMatch($matchId);
        $data = [
            'results' => $matchResults,
            'typResults' => $typResults,
            // inne dane
        ];
        file_put_contents("path/to/json/post-match_$matchId.json", json_encode($data));
    }
/* TU ZABAWA ZACZYNA SIĘ TROCHE OD NOWA */




    public function getMeczeDnia($turniejId, $onlyIds=false)
    {
    // Zwraca mecze dla dzisiejszej daty lub z najbliższego dnia z meczami
    return $this->terminarzModel->getNajblizszeMecze($turniejId, $onlyIds);

    // zwraca tabelkę
    }

    public function getMeczeTurnieju($turniejId,$onlyIds=false)
    {
        // Zwraca wszystkie mecze dla określonego turnieju
        return $this->terminarzModel->getMeczeByTurniejId($turniejId,$onlyIds);
    }

    public function getMeczeTurniejuDoRozegrania($turniejId,$onlyIds=false)
    {
        // Zwraca wszystkie mecze dla określonego turnieju
        return $this->terminarzModel->getMeczeDoRozegrania($turniejId,$onlyIds);
    }

    public function getRozegraneMeczeTurnieju($turniejID, $onlyIds=false){
        return $this->terminarzModel->getRozegraneMecze($turniejID, $onlyIds);
    }

    public function getInfoForMatches($mecze)
    {
        // Przetwarza dane dla listy meczów, może zawierać logikę pobierania danych z plików JSON
        foreach ($mecze as &$mecz) {
            $mecz['info'] = $this->loadMatchInfo($mecz['id']);
        }
        return $mecze;
    }

    protected function loadMatchInfo($matchId)
    {
        // Wczytuje dane meczu z pliku JSON lub aktualizuje je, jeśli są przestarzałe
        $path = "path/to/json/{$matchId}.json";
        if (file_exists($path) && filemtime($path) > strtotime('-1 day')) {
            return json_decode(file_get_contents($path), true);
        } else {
            $updatedData = $this->fetchMatchDataFromApi($matchId);
            file_put_contents($path, json_encode($updatedData));
            return $updatedData;
        }
    }

    protected function fetchMatchDataFromApi($matchId)
    {
        // Logika pobierania danych z zewnętrznego API
        // Tutaj przykładowe pobieranie danych, które musisz dostosować do swojego API
        return [
            'details' => 'Dane z API dla meczu ' . $matchId
        ];
    }

public function getUserTypesForMatches($mecze, $userUniID, $turniejID, $batch = false)
{
    if ($batch) {
        $ids = array_column($mecze, 'Id');
        $idx = $this->typyModel->typyGraczaDlaMeczow($userUniID, $ids);   // [GameID => wiersz]
        foreach ($mecze as &$mecz) { $mecz['typy'] = $idx[$mecz['Id']] ?? 'Brak typów'; }
        unset($mecz);
        return $mecze;
    }
    foreach ($mecze as &$mecz) {
        $mecz['typy'] = $this->typyModel->getTypyByMeczIdAndUserId($mecz['Id'], $userUniID) ?? 'Brak typów';
    }
    unset($mecz);
    return $mecze;
}


private function manageJsonFiles($mecze, $turniejID, $zewnetrznyTurniejID) {
    $updatedData = [];
    $mesydz ="";
    foreach ($mecze as $mecz) {
        // Używamy ApiID do generowania nazwy pliku JSON
        $filePath = $this->getJsonFilePath($turniejID, $mecz['ApiID']);
        if ($this->needsUpdate($filePath)) {
            $file = WRITEPATH . 'logs/test_log.log';
            file_put_contents($file, 'Mecz wymaga aktualizacji', FILE_APPEND);

            // Pobieramy dane z zewnętrznego API używając ApiID
            //$updatedData[$mecz['ApiID']] = $this->fetchDataFromExternalApi($mecz['ApiID']);
            // Zapisujemy zaktualizowane dane do pliku JSON
            //$this->saveToJson($filePath, $updatedData[$mecz['ApiID']]);
            //Ponieważ nie ma inne opcji (w sensie aktualizacji tylko jednego meczu, zaktualizujemy wszystkie)
            $this->zapiszDaneDoJson($turniejID, $zewnetrznyTurniejID);
            $mesydz = "Pliki meczów tego turnieju zostały zaktualizowane";

        } else {
            // Odczytujemy istniejące dane z pliku JSON
            //$updatedData[$mecz['ApiID']] = $this->readFromJson($filePath);
            $mesydz = "Pliki meczów tego turnieju są aktualne - happy hippo";
        }
    }



    return $mesydz;
}


private function getJsonFilePath($turniejID, $meczId) {
    $baseDir = WRITEPATH . "mecze/{$turniejID}/{$meczId}";
    return $baseDir.".json";
}

private function needsUpdate($filePath) {
    // Logika określająca, czy plik JSON wymaga aktualizacji
    return !file_exists($filePath) || filemtime($filePath) < strtotime('-1 day');
}

private function fetchDataFromExternalApi($meczId) {
    // Logika pobierania danych z zewnętrznego API
}

private function saveToJson($filePath, $data) {
    file_put_contents($filePath, json_encode($data));
}

private function readFromJson($filePath) {
    return json_decode(file_get_contents($filePath), true);
}

public function fetchFixtures($turniejId, $page = 1) {
        $params = [
            'competition_id' => $turniejId,
            'page' => $page
        ];

        $wynik = $this->common->getFixtures($params);
        
        //Logowanie danych zwróconych przez _makeRequest
        //$file = WRITEPATH . 'logs/test_log.log';
        //file_put_contents($file, "Zwrócone dane: " . print_r($wynik['data'], true) . "\n", FILE_APPEND);


        return $wynik['data'];
    }

/*public function zapiszDaneDoJson($turniejID, $zewnetrznyTurniejID){
    $jsonzapi = $this->fetchFixtures($zewnetrznyTurniejID)['fixtures']; // pobranie danych z API
    $przetworzonyJSONZbiorczy = $this->processMatchesData($jsonzapi); // przetworzenie danych 
    $this->saveMatchesAsJsonFiles($turniejID,$przetworzonyJSONZbiorczy); // zapisanie danych

}*/


public function zapiszDaneDoJson($turniejID, $zewnetrznyTurniejID) {
    $allFixtures = [];
    $page = 1;
    $hasNextPage = true;
    


    while ($hasNextPage) {
        // Pobierz dane z API dla bieżącej strony
        $response = $this->fetchFixtures($zewnetrznyTurniejID, $page);

        // Logowanie danych zwróconych przez _makeRequest
//        $file = WRITEPATH . 'logs/test_log.log';
//        file_put_contents($file, "Repsponse: " . print_r($response, true) . "\n", FILE_APPEND);

        if (isset($response['fixtures'])) {
            $fixtures = $response['fixtures'];
            $allFixtures = array_merge($allFixtures, $fixtures);
            $file = WRITEPATH . 'logs/test_log.log';
            file_put_contents($file, 'Teraz juz mamy odpowiedz', FILE_APPEND);
//          file_put_contents($file, $allFixtures, FILE_APPEND);

        } else {
            $file = WRITEPATH . 'logs/test_log.log';
            file_put_contents($file, "\n Chciałbyś Wit, no ale nie.", FILE_APPEND);
        }


        // Sprawdź, czy jest następna strona
        $hasNextPage = isset($response['next_page']) && !empty($response['next_page']);
        if ($hasNextPage) {
            $page++;
        }
    }

    // Przetwórz i zapisz dane
    $przetworzonyJSONZbiorczy = $this->processMatchesData($allFixtures);
    $this->saveMatchesAsJsonFiles($turniejID, $przetworzonyJSONZbiorczy);
}



// Aby sparsować ten JSON i przetworzyć dane w taki sposób, aby zawierały tylko wybrane pola oraz dodatkowe informacje, a następnie zapisać je do odrębnych plików JSON dla każdego meczu, możesz użyć poniższego podejścia w PHP. Przygotowałem przykład, jak to zrobić krok po kroku:

### Krok 0: Przygotowanie funkcji do aktualizacji czasu / przepisanie go na localTime;

function convertToTimezone($dateTime, $timezone) {
    $date = new \DateTime($dateTime, new \DateTimeZone('UTC'));
    $date->setTimezone(new \DateTimeZone($timezone));
    return $date->format('H:i:s');
}


### Krok 1: Parsowanie i filtracja danych

// Stwórz funkcję w PHP, która przetwarza dane wejściowe (powyższy JSON), filtruje niezbędne pola i dodaje nowe informacje.


function processMatchesData($matchesData) {
    $processedMatches = [];
    $userTimezone = 'Europe/Warsaw'; // Zmienna przechowująca strefę czasową użytkownika. Możesz ją dynamicznie ustawić.

    foreach ($matchesData as $match) {
        // Konwersja czasu meczu z UTC na lokalny czas
        //$localTime = $this->convertToTimezone($match['date'] . ' ' . $match['time'], $userTimezone);

        $dt = new \DateTime($match['date'] . ' ' . $match['time'], new \DateTimeZone('UTC'));
        $dt->setTimezone(new \DateTimeZone($userTimezone));



        // Wybieranie tylko niezbędnych pól
        $processedMatch = [
            'match_id' => $match['id'],
            'status' => 'PreMecz',
            'OstatniaAktualizacja' => date('Y-m-d H:i:s'), // Dodanie daty i czasu ostatniej aktualizacji
            'home_team' => [
                'id' => $match['home_id'],
                'name' => $match['home_name'],
                'plName' => isset($match['home_translations']['pl']) ? $match['home_translations']['pl'] : $match['home_name'] // Użycie tłumaczenia lub domyślnej nazwy
            ],
            'away_team' => [
                'id' => $match['away_id'],
                'name' => $match['away_name'],
                'plName' => isset($match['away_translations']['pl']) ? $match['away_translations']['pl'] : $match['away_name'] // Użycie tłumaczenia lub domyślnej nazwy
            ],
            'competition' => $match['competition']['name'],
            'date' => $match['date'],
            'time' => $match['time'],
            'naszCzas' => $dt->format('H:i:s'),
            'naszaData' => $dt->format('Y-m-d'), // Zapisanie lokalnego czasu
            'location' => $match['location'] ?? 'Unknown', // Dodanie wartości domyślnej, jeśli lokalizacja nie istnieje
            'odds' => $match['odds']['pre'], // Przykładowe przetworzenie zakładów
            'additional_info' => 'Any additional info here' // Przykład dodawania nowych pól
        ];

        // Dodanie przetworzonego meczu do listy
        $processedMatches[] = $processedMatch;
    }

    return $processedMatches;
}
### Krok 2: Zapisywanie danych do plików JSON

// Następnie możesz zapisać każdy przetworzony mecz do odrębnego pliku JSON. Funkcja poniżej pokazuje, jak to zrobić:

function saveMatchesAsJsonFiles($turniejID, $matches) {
    $baseDir = WRITEPATH . "mecze/{$turniejID}/"; // Bazowy katalog dla plików JSON

    // Sprawdź, czy katalog istnieje, jeśli nie, utwórz go
    if (!file_exists($baseDir)) {
        mkdir($baseDir, 0777, true); // Parametr `true` pozwala na rekursywne tworzenie katalogów
    }

    foreach ($matches as $match) {
        $filePath = $baseDir . "{$match['match_id']}.json"; // Pełna ścieżka do pliku JSON
        file_put_contents($filePath, json_encode($match, JSON_PRETTY_PRINT)); // Zapisz dane do pliku JSON
    }
}

public function odswiezLiveMecze(array $mecze, int $turniejID, string $competitionApiId): void
{
    $startedMecze = array_filter($mecze, fn($m) => !empty($m['Rozpoczety']));
    if (empty($startedMecze)) return;

    // Sprawdź czy którykolwiek potrzebuje odświeżenia (próg: 2 minuty)
    $needsRefresh = false;
    foreach ($startedMecze as $mecz) {
    $livePath = WRITEPATH . "live/{$mecz['ApiID']}.json";
    // Sprawdzaj świeżość pliku live, nie statycznego – static JSON już nie będzie aktualizowany w tej metodzie
    if (!file_exists($livePath) || (time() - filemtime($livePath)) > 120) {
        $needsRefresh = true;
        break;
    }
}
    if (!$needsRefresh) return;

    $liveController = new \App\Controllers\LiveScore();
    $today = date('Y-m-d');

    try {
        $liveMecze = $liveController->getLivescoresSimple(['competition_id' => $competitionApiId]);
    } catch (\Exception $e) {
        $liveMecze = [];
        $this->common->custom_log("Live API error: " . $e->getMessage());
    }

    try {
        $historyMecze = $liveController->getHistory([
            'competition_id' => $competitionApiId,
            'from' => $today,
            'to'   => $today,
        ]);
    } catch (\Exception $e) {
        $historyMecze = [];
        $this->common->custom_log("History API error: " . $e->getMessage());
    }

$liveIndex = [];
foreach ($liveMecze as $lm) {
    if (!empty($lm['fixture_id'])) {
        $liveIndex[(string)$lm['fixture_id']] = $lm;
    }
}

$historyIndex = [];
foreach ($historyMecze as $hm) {
    if (!empty($hm['fixture_id'])) {
        $historyIndex[(string)$hm['fixture_id']] = $hm;
    }
}

$terminarzModel = model(\App\Models\TerminarzModel::class);
$liveDir = WRITEPATH . 'live/';
if (!is_dir($liveDir)) {
    mkdir($liveDir, 0755, true);
}

foreach ($startedMecze as $mecz) {
    $key      = (string)$mecz['ApiID'];  // fixture_id
    $livePath = $liveDir . $key . '.json';

    // Statyczny JSON zostawiamy bez zmian do Step 6
    $staticPath = WRITEPATH . "mecze/{$turniejID}/{$key}.json";

    if (isset($historyIndex[$key])) {
        // --- CASE A: mecz zakończony w history feed ---
        $hm     = $historyIndex[$key];
        $scores = $hm['scores'] ?? [];
        // scores.score = wynik ostateczny (po dogrywce/karnych jeśli były)
        $finalScore = $scores['score'] ?? '0 - 0';
        [$homeScore, $awayScore] = $this->parseScore($finalScore);

        $timeLabel = 'FT';
        if (!empty($scores['ps_score'])) $timeLabel = 'AP';
        elseif (!empty($scores['et_score'])) $timeLabel = 'AET';

        file_put_contents($livePath, json_encode([
            'fixture_id'   => $key,
            'match_id'     => (string)($hm['id'] ?? ''),
            'status'       => 'FINISHED',
            'time'         => $timeLabel,
            'score'        => $finalScore,
            'ht_score'     => $scores['ht_score'] ?? '',
            'ft_score'     => $scores['ft_score'] ?? '',
            'et_score'     => $scores['et_score'] ?? '',
            'ps_score'     => $scores['ps_score'] ?? '',
            'last_changed' => date('Y-m-d H:i:s'),
            'home_score'   => $homeScore,
            'away_score'   => $awayScore,
        ], JSON_PRETTY_PRINT));

        // KLUCZOWA ZMIANA: aktualizacja DB -- to naprawia "zombie live matches"
        $terminarzModel->setZakonczony((int)$mecz['Id']);

    } elseif (isset($liveIndex[$key])) {
        // --- CASE B: mecz trwa ---
        $lm     = $liveIndex[$key];
        $scores = $lm['scores'] ?? [];
        $raw    = $scores['score'] ?? '0 - 0';
        [$homeScore, $awayScore] = $this->parseScore($raw);

        file_put_contents($livePath, json_encode([
            'fixture_id'   => $key,
            'match_id'     => (string)($lm['id'] ?? ''),
            'status'       => $lm['status'] ?? 'IN PLAY',
            'time'         => (string)($lm['time'] ?? ''),
            'score'        => $raw,
            'ht_score'     => $scores['ht_score'] ?? '',
            'last_changed' => date('Y-m-d H:i:s'),
            'home_score'   => $homeScore,
            'away_score'   => $awayScore,
        ], JSON_PRETTY_PRINT));

    } else {
        // --- CASE C: brak w obu feedach -- fallback ---
        $matchTime = strtotime($mecz['Date'] . ' ' . $mecz['Time'] . ' UTC');
        if ($matchTime && time() > $matchTime + 6600) {  // 110 min
            $existing = file_exists($livePath)
                ? (json_decode(file_get_contents($livePath), true) ?? [])
                : [];

            file_put_contents($livePath, json_encode([
                'fixture_id'   => $key,
                'match_id'     => $existing['match_id'] ?? '',
                'status'       => 'FINISHED_FALLBACK',
                'time'         => 'FT',
                'score'        => $existing['score'] ?? '? - ?',
                'ht_score'     => $existing['ht_score'] ?? '',
                'last_changed' => date('Y-m-d H:i:s'),
                'home_score'   => $existing['home_score'] ?? 0,
                'away_score'   => $existing['away_score'] ?? 0,
            ], JSON_PRETTY_PRINT));

            $terminarzModel->setZakonczony((int)$mecz['Id']);
        }
    }
    // Static JSON celowo NIE jest już tu modyfikowany (dane live oddzielone)
}
}


public function wygenerujTypyDlaMeczu($matchId) {
    $typyModel = new \App\Models\TypyModel();
    $types = $typyModel->ktoTypujeTenMeczLimited($matchId);

    $this->common->custom_log("Tak, próbuję wygenerować te typy: ");

    $countWin1 = 0;
    $countWin2 = 0;
    $countDraw = 0;
    $goldenBallCount = 0;
    $typeCounts = [];
    $playersWithPoints = 0;
    $correctPredictions = 0;
    $doublePointsPlayers = 0;

    foreach ($types as $typ) {
        if ($typ['HomeTyp'] > $typ['AwayTyp']) {
            $countWin1++;
        } elseif ($typ['HomeTyp'] < $typ['AwayTyp']) {
            $countWin2++;
        } else {
            $countDraw++;
        }

        if ($typ['GoldenGame'] == 1) {
            $goldenBallCount++;
        }

        $typeKey = $typ['HomeTyp'] . ':' . $typ['AwayTyp'];
        if (isset($typeCounts[$typeKey])) {
            $typeCounts[$typeKey]++;
        } else {
            $typeCounts[$typeKey] = 1;
        }

        $pkt = intval($typ['pkt']);
        if ($pkt > 0) {
            $playersWithPoints++;
        }
        if ($pkt == 3 || $pkt == 6) {
            $correctPredictions++;
        }
        if ($pkt == 2 || $pkt == 6) {
            $doublePointsPlayers++;
        }
    }

    // ── POPRAWKA: guard przed pustą tablicą ──
    if (!empty($typeCounts)) {
        $mostPopularType = array_search(max($typeCounts), $typeCounts);
        $mostPopularTypeCount = $typeCounts[$mostPopularType];
    } else {
        $mostPopularType = null;
        $mostPopularTypeCount = 0;
    }

    $summary = [
        'mostPopularType'      => $mostPopularType,
        'mostPopularTypeCount' => $mostPopularTypeCount,
        'countWin1'            => $countWin1,
        'countWin2'            => $countWin2,
        'countDraw'            => $countDraw,
        'goldenBallCount'      => $goldenBallCount
    ];

    $zakonczone = [
        'playersWithPoints'   => $playersWithPoints,
        'correctPredictions'  => $correctPredictions,
        'doublePointsPlayers' => $doublePointsPlayers
    ];

    $data = [
        'types'     => $types,
        'summary'   => $summary,
        'zakonczone' => $zakonczone
    ];

    $jsonData = json_encode($data, JSON_PRETTY_PRINT);

    $baseDir = WRITEPATH . "typy";

    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0755, true);
    }

    file_put_contents("{$baseDir}/{$matchId}.json", $jsonData);
}

private function parseScore(string $score): array
{
    $parts     = explode(' - ', $score);  // spacja-myślnik-spacja, nie samo '-'
    $homeScore = isset($parts[0]) ? (int)trim($parts[0]) : 0;
    $awayScore = isset($parts[1]) ? (int)trim($parts[1]) : 0;
    return [$homeScore, $awayScore];
}

public function liczbaTypowDlaMeczow(array $meczIds): array
{
    if (empty($meczIds)) return [];
    $rows = $this->db->table($this->table)
        ->select('GameID, COUNT(*) AS cnt')
        ->whereIn('GameID', $meczIds)
        ->groupBy('GameID')
        ->get()->getResultArray();
    $out = [];
    foreach ($rows as $r) { $out[(int)$r['GameID']] = (int)$r['cnt']; }
    return $out;
}

public function typyGraczaDlaMeczow($userUniID, array $meczIds): array
{
    if (empty($meczIds)) return [];
    $rows = $this->where('uniID', $userUniID)->whereIn('GameID', $meczIds)->findAll();
    $out = [];
    foreach ($rows as $r) { $out[(int)$r['GameID']] = $r; }
    return $out;
}

}

?>
