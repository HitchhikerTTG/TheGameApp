<?php

namespace App\Controllers;
$session = \Config\Services::session();
use App\Models\TabelaModel;
use App\Models\UserModel;
use App\Models\TypyModel;
use App\Models\OdpowiedziModel;
use App\Models\KtoWCoGraModel;
use App\Models\PomocnicaPiPModel;

class Tabela extends BaseController
{

    // Czego tu oczekuję:
    // dostarcz mi następujące informację, w formie JSON
    // Dla każdego gracza biorącego udział w turnieju (na podstawie przekazanego do funkcji turniejID) dostarcz mi:
    // Id użytkownika / Nick użytkownika / Suma punktów / Liczbę punktów za mecze / liczbę punktów za pytania / Liczbę dobrze wytypowanych spotkań / klub do którego należy 
    // jeśli turniejID nie zostało przekazane - użyj: $this->config['activeTournamentId']
    
    
    public function tabelaGraczy($turniejID=null){

        $configPath = WRITEPATH . 'ActiveTournament.json';
        $jsonString = file_get_contents($configPath);
        $config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną

        if ($turniejID === null) {
            // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
            $turniejID = $this->config['activeTournamentId'];
            }


        //to teraz potrzebuję ;] Pobrać listę wszystkich aktywnych graczy
        //dla każdego gracza policzyć liczbę punktów
        //posortować tabelę ze względu na liczbę punktów
        //nie wierzę, ze to mówię, ale kurde chyba sprawdzę co jest szybsze. Gdyż why not. 
        //a potem zróbmy cache'owanie - i jakąś flagę, czy coś się zmieniło, czy nie

        $ktoWCoGraModel = model(KtoWCoGraModel::class); 
#       $uzytkownicy = model(UserModel::class);
        $uzytkownicy = model(UserModel::class);
        $odpowiedz = model(OdpowiedziModel::class);
        $typy = model(TypyModel::class);
        $pomocnicza = model(PomocnicaPiPModel::class);
        
        $uzytkownicyBuilder=$uzytkownicy->builder();
        $uzytkownicyBuilder->where('activated',1);
        $aktywniUzytkownicy=$uzytkownicyBuilder->get()->getResultArray();

        $userIdsInTournament = $ktoWCoGraModel->getUser sOfTournament($turniejID);

        // Teraz masz listę ID użytkowników uczestniczących w turnieju, możesz zrobić kolejne zapytanie
        // do modelu użytkowników (lub innego modelu), aby pobrać szczegółowe informacje o tych użytkownikach.
        // Na przykład:
        $users = model(UserModel::class);

        $aktywniUzytkownicyWTurnieju = [];
            foreach ($userIdsInTournament as $userId) {
                $userInfo = $users->find($userId);
                    if ($userInfo && $userInfo['activated']) {
                    $aktywniUzytkownicyWTurnieju[] = $userInfo;
            }
        }
       #$uzytkownicyBuilder=$uzytkownicy->builder();
       #$uzytkownicyBuilder->where('activated',1);
       #$aktywniUzytkownicy=$ktoWCoGraModel->getUsersOfTournament($turniejID);
        $wyniki = [];
        foreach ($aktywniUzytkownicyWTurnieju as $uzytkownik) {

            //na razie na sucho, czyli wypiszemy same proste rzeczy :)
            // potrzebujemy wiedzieć, ile punktów ma dany użytkownik, czyli stworzymy sobie tabele, w której bedzie:
            // nick=>punkty
            $liczbaPktZaTypy = $typy->punktyZaMecze($uzytkownik['uniID'], $turniejID);
            $liczbaPktZaPytania = $odpowiedz->PunktyZaPytania($uzytkownik['uniID'], $turniejID);
            $dokladneTrafienia = $typy->dokladneTrafienia($uzytkownik['uniID'], $turniejID);

            $liczbapkt = $liczbaPktZaTypy + $liczbaPktZaPytania;

            $wyniki[] = [
                    'uid' => $uzytkownik['id'],
                    'nick' => $uzytkownik['nick'], // zakładam, że username to właściwe pole
                    'punkty' => $liczbapkt,
                    'punktyZaMecze' => $liczbaPktZaTypy,
                    'punktyZaPytania' => $liczbaPktZaPytania,
                    'dokladneTrafienia' => $dokladneTrafienia,
                ];
            }

        // Przekształcenie wyników do formatu JSON
        $json = json_encode($wyniki, JSON_PRETTY_PRINT);

        // Wydrukowanie JSON
        $jsonData = json_encode($wyniki, JSON_PRETTY_PRINT);
        file_put_contents(WRITEPATH . 'tabelaGraczy_'.$turniejID.'.json', $jsonData);
    }

    public function index($turniejID = null)
{
    $configPath = WRITEPATH . 'ActiveTournament.json'; // Załóżmy, że to Twoja domyślna lokalizacja
    if ($turniejID !== null) {
        $configPath = WRITEPATH . "tabelaGraczy_${turniejID}.json"; // Ścieżka do pliku JSON konkretnego turnieju
    }

    // Wczytanie danych z pliku JSON
    /*if (file_exists($configPath)) {
        $jsonString = file_get_contents($configPath);
        $tabelaDanych = json_decode($jsonString, true);
    } else {
        $tabelaDanych = []; // Pusty array, jeśli plik nie istnieje
    }*/

        $model = model(TabelaModel::class);
        $tabelaDanych = $model->gimmeTabelaGraczy($turniejID);



    // Przekazanie danych do widoku
    $data = [
        'tabelaDanych' => $tabelaDanych,
        'turniejID' => $turniejID,
        'userID' => session()->get('loggedInUser'),
        'title' => 'Wit pastwi się nad tabelą'

    ];

    return view('typowanie/header',$data)
           .view('tabela/tabela.php', $data)
           .view('typowanie/footer',$data);
}


}

?>