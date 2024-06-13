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

    public function __construct()
    {
        $this->terminarzModel = new TerminarzModel();
        $this->typyModel = new TypyModel();
         $this->common = new Common();
    }

    public function meczeUzytkownikaWTurnieju($userUniID, $turniejID, $zewnetrznyTurniejID, $filtr){

        $lista_meczow=[];
        // Po pierwsze, określmy które mecze nas interesują na podstawie filtra
        
        switch($filtr){
            case "najblizsze":
                $lista_meczow=$this->getMeczeDnia($turniejID, true);
                break;
            case "do_rozegrania":
                $lista_meczow =$this->getMeczeTurniejuDoRozegrania($turniejID, true);
                break;
            case "wszystkie":
                $lista_meczow = $this->getMeczeTurnieju($turniejID, true);
                break;
            case "rozegrane":
                $lista_meczow = $this->getRozegraneMeczeTurnieju($turniejID, true);
                break;
        }
//        echo "<p>Lista meczów, chciałbym, żeby miała dwa pola - iD i zewnętrze ip</p><pre>";
//        print_r($lista_meczow);
//        echo "</pre>";
        // kiedy mamy listę tych meczów powinniśmy sprawdzić pliki JSON dla tych meczów
        $JSONySprawdzone = $this->manageJsonFiles($lista_meczow, $turniejID, $zewnetrznyTurniejID);
        // jeśli $jsonySprawdzone są true wtedy idziemy dalej, jeśli 0, wtedy przykro i trzeba sprawdzic // wypluć bład.

#        $wypelniona_lista=$lista_meczow;         
#        echo "Sprawdzam użytkownika: ".$userID;
        $wypelniona_lista = $this->getUserTypesForMatches($lista_meczow, $userUniID, $turniejID);

           // Dodanie liczby typów dla każdego meczu
    foreach ($wypelniona_lista as &$mecz) {
        $mecz['liczbaTypow'] = $this->typyModel->liczbaTypowDlaMeczu($mecz['Id']);
    }

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

    public function getUserTypesForMatches($mecze, $userUniID, $turniejID)
    {
        // Pobiera typy użytkownika dla listy meczów
        foreach ($mecze as &$mecz) {
            $mecz['typy'] = $this->typyModel->getTypyByMeczIdAndUserId($mecz['Id'], $userUniID) ?? 'Brak typów';
            /*$mecz['isGoldenGame'] = $this->typyModel->czyGraczUzylJokeraWTymMeczu($mecz['Id'], $userUniID) ?? "Nie";
            $mecz['hasGoldenGame'] = $this->typyModel->czyGraczMozeJeszczeUzycJokera($turniejID, $userUniID) ?? 'Nie';*/
        }
        return $mecze;
    }


private function manageJsonFiles($mecze, $turniejID, $zewnetrznyTurniejID) {
    $updatedData = [];
    $mesydz ="";
    foreach ($mecze as $mecz) {
        // Używamy ApiID do generowania nazwy pliku JSON
        $filePath = $this->getJsonFilePath($turniejID, $mecz['ApiID']);
        if ($this->needsUpdate($filePath)) {
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

        return $this->common->getFixtures($params);
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

        if (isset($response['fixtures'])) {
            $fixtures = $response['fixtures'];
            $allFixtures = array_merge($allFixtures, $fixtures);
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
        $localTime = $this->convertToTimezone($match['date'] . ' ' . $match['time'], $userTimezone);

        // Wybieranie tylko niezbędnych pól
        $processedMatch = [
            'match_id' => $match['id'],
            'status' => 'PreMecz',
            'OstatniaAktualizacja' => date('Y-m-d H:i:s'), // Dodanie daty i czasu ostatniej aktualizacji
            'home_team' => [
                'id' => $match['home_id'],
                'name' => $match['home_name']
            ],
            'away_team' => [
                'id' => $match['away_id'],
                'name' => $match['away_name']
            ],
            'competition' => $match['competition']['name'],
            'date' => $match['date'],
            'time' => $match['time'],
            'naszCzas' => $localTime, // Zapisanie lokalnego czasu
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



    // Controller w PHP
    public function wygenerujTypyDlaMeczu($matchId) {
        $typyModel = new \App\Models\TypyModel();
        $types = $typyModel->ktoTypujeTenMecz($matchId);

        $jsonData = json_encode($types);

        // Opcja 1: Zapisz jako plik
        $baseDir = WRITEPATH . "typy/"; // Bazowy katalog dla plików JSON
        file_put_contents("{$baseDir}/{$matchId}.json", $jsonData);

    
    }
}
?>
