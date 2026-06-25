<?php namespace App\Models;

use CodeIgniter\Model;

class TabelaModel extends Model
{

    public function przeliczTabeleGraczy($turniejID=null){

        //$configPath = WRITEPATH . 'ActiveTournament.json';
        //$jsonString = file_get_contents($configPath);
        //$config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną

        $config = get_active_tournament_config();

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

        $userIdsInTournament = $ktoWCoGraModel->getUsersOfTournament($turniejID);

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
                'uid'              => $uzytkownik['id'],
                'uniID'              => $uzytkownik['uniID'],
                'nick'             => $uzytkownik['nick'],
                'slug'              => $uzytkownik['slug'],
                'emoji'            => $uzytkownik['emoji'] ?? '',   // ← dodać
                'punkty'           => $liczbapkt,
                'punktyZaMecze'    => $liczbaPktZaTypy,
                'punktyZaPytania'  => $liczbaPktZaPytania,
                'dokladneTrafienia'=> $dokladneTrafienia,
            ];

            }

        // Przekształcenie wyników do formatu JSON
        $json = json_encode($wyniki, JSON_PRETTY_PRINT);

        // Wydrukowanie JSON
        $jsonData = json_encode($wyniki, JSON_PRETTY_PRINT); 
        file_put_contents(WRITEPATH . 'tabelaGraczy_'.$turniejID.'.json', $jsonData);
        // Snapshot historii pozycji po każdym przeliczeniu
        if (!empty($wyniki)) {
            $dbSnap = \Config\Database::connect();
            $ostatniMecz = $dbSnap->table('terminarz')
                ->where('TurniejID', $turniejID)
                ->where('zakonczony', 1)
                ->orderBy('Date', 'DESC')
                ->orderBy('Time', 'DESC')
                ->limit(1)
                ->get()->getRowArray();

            if ($ostatniMecz) {
                $sortowane = $wyniki;
                usort($sortowane, fn($a, $b) => $b['punkty'] <=> $a['punkty']);

                $pozycje = [];
                $pos = 1;
                foreach ($sortowane as $i => $w) {
                    if ($i > 0 && $w['punkty'] < $sortowane[$i-1]['punkty']) $pos = $i + 1;
                    $pozycje[$w['uniID']] = $pos;
                }

                $histDir  = WRITEPATH . 'gracze/';
                $histPath = $histDir . "historia_pozycji_{$turniejID}.json";
                if (!is_dir($histDir)) mkdir($histDir, 0775, true);

                $historia = file_exists($histPath)
                    ? (json_decode(file_get_contents($histPath), true) ?? [])
                    : [];

                $historia[$ostatniMecz['Id']] = [
                    'meczId' => $ostatniMecz['Id'],
                    'data'   => $ostatniMecz['Date'],
                    'pozycje'=> $pozycje,
                ];
                file_put_contents($histPath, json_encode($historia, JSON_PRETTY_PRINT));
            }
        }    
    }
 
    public function gimmeTabelaGraczy($turniejID){
            //$configPath = WRITEPATH . 'ActiveTournament.json'; // Załóżmy, że to Twoja domyślna lokalizacja
            //$jsonString = file_get_contents($configPath);
            //$config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
            
            $config = get_active_tournament_config();
            
            if ($turniejID === null) {
                // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
                $turniejID = $this->config['activeTournamentId'];
                }

            // Wczytanie danych TURNIEJU  z pliku JSON
            

            if ($turniejID !== null) {
                $turniejPath = WRITEPATH . "tabelaGraczy_{$turniejID}.json"; // Ścieżka do pliku JSON konkretnego turnieju
                }

            if (file_exists($turniejPath)) {
                $jsonString = file_get_contents($turniejPath);
                $tabelaDanych = json_decode($jsonString, true);
            } else {
                $tabelaDanych = []; // Pusty array, jeśli plik nie istnieje
            }
        
            return $tabelaDanych;
    }


    // ────────────────────────────────────────────────────────────────
    // Odczyt pojedynczego gracza z gotowej (cache'owanej) tabeli
    // ────────────────────────────────────────────────────────────────
    public function getWierszGracza(int $turniejID, string $uniID): ?array
    {
        foreach ($this->gimmeTabelaGraczy($turniejID) as $wiersz) {
            if ((string)($wiersz['uniID'] ?? '') === $uniID) {
                return $wiersz;
            }
        }
        return null;
    }

        // Pozycja gracza w rankingu (competition ranking - remisy dzielą miejsce)
        public function getPozycjaGracza(int $turniejID, string $uniID): int
        {
            $tabela = $this->gimmeTabelaGraczy($turniejID);
            usort($tabela, fn($a, $b) => $b['punkty'] <=> $a['punkty']);

            $pozycja          = 1;
            $poprzedniePunkty = null;
            $licznik          = 0;

            foreach ($tabela as $wiersz) {
                $licznik++;
                if ($poprzedniePunkty === null || $wiersz['punkty'] < $poprzedniePunkty) {
                    $pozycja          = $licznik;
                    $poprzedniePunkty = $wiersz['punkty'];
                }
                if ((string)($wiersz['uniID'] ?? '') === $uniID) {
                    return $pozycja;
                }
            }

            return $licznik + 1;
        }

public function przeliczHistoriePozycji(int $turniejID): void
{
    $db = \Config\Database::connect();

    // Wszyscy gracze turnieju
    $gracze = $db->query("
        SELECT u.uniID
        FROM uzytkownicy u
        JOIN ktowcogra k ON k.userID = u.id
        WHERE k.turniejID = ? AND u.activated = 1
    ", [$turniejID])->getResultArray();
    $uids = array_column($gracze, 'uniID');

    if (empty($uids)) return;

    // Mecze zakończone, chronologicznie
    $mecze = $db->table('terminarz')
        ->where('TurniejID', $turniejID)
        ->where('zakonczony', 1)
        ->orderBy('Date', 'ASC')
        ->orderBy('Time', 'ASC')
        ->get()->getResultArray();

    if (empty($mecze)) return;

    // Wszystkie typy graczy w turnieju (dla zakończonych meczów)
    $in = implode(',', array_map('intval', array_column($mecze, 'Id')));
    $typyRows = $db->query("
        SELECT ty.uniID, ty.GameID, ty.pkt
        FROM typy ty
        WHERE ty.GameID IN ({$in})
    ")->getResultArray();

    // Indeksuj typy: [GameID][uniID] = pkt
    $typyWgMeczu = [];
    foreach ($typyRows as $r) {
        $typyWgMeczu[$r['GameID']][$r['uniID']] = (int)$r['pkt'];
    }

    // Wszystkie pytania turnieju z datą zamknięcia i punktami graczy
    $pytaniaRows = $db->query("
        SELECT o.uniidOdp AS uniID, o.pkt, p.wazneDo
        FROM odpowiedzi o
        JOIN pytania p ON p.id = o.idPyt
        WHERE o.TurniejID = ? AND p.zamkniete = 1
    ", [$turniejID])->getResultArray();

    // Indeksuj pytania: [wazneDo][uniID] = suma pkt
    $pytaniaWgDaty = [];
    foreach ($pytaniaRows as $r) {
        $data = substr($r['wazneDo'], 0, 10); // tylko data YYYY-MM-DD
        $uid  = $r['uniID'];
        $pytaniaWgDaty[$data][$uid] = ($pytaniaWgDaty[$data][$uid] ?? 0) + (int)$r['pkt'];
    }
    ksort($pytaniaWgDaty);

    $historia      = [];
    $pktMeczePorGracza   = array_fill_keys($uids, 0);
    $pktPytaniaPorGracza = array_fill_keys($uids, 0);
    $doliczoneDataPytan  = [];

    foreach ($mecze as $mecz) {
        // Dodaj punkty z tego meczu
        foreach ($typyWgMeczu[$mecz['Id']] ?? [] as $uid => $pkt) {
            if (isset($pktMeczePorGracza[$uid])) {
                $pktMeczePorGracza[$uid] += $pkt;
            }
        }

        // Dolicz pytania z deadlinem <= data tego meczu (jeszcze nie doliczonych)
        foreach ($pytaniaWgDaty as $dataDeadline => $graczePkt) {
            if ($dataDeadline > $mecz['Date']) break;
            if (in_array($dataDeadline, $doliczoneDataPytan)) continue;

            foreach ($graczePkt as $uid => $pkt) {
                if (isset($pktPytaniaPorGracza[$uid])) {
                    $pktPytaniaPorGracza[$uid] += $pkt;
                }
            }
            $doliczoneDataPytan[] = $dataDeadline;
        }

        // Oblicz ranking
        $laczne = [];
        foreach ($uids as $uid) {
            $laczne[$uid] = ($pktMeczePorGracza[$uid] ?? 0)
                          + ($pktPytaniaPorGracza[$uid] ?? 0);
        }
        arsort($laczne);

        $pozycje = [];
        $pos     = 1;
        $prev    = null;
        $i       = 0;
        foreach ($laczne as $uid => $pkt) {
            $i++;
            if ($prev !== null && $pkt < $prev) $pos = $i;
            $pozycje[$uid] = $pos;
            $prev = $pkt;
        }

        $historia[$mecz['Id']] = [
            'meczId' => $mecz['Id'],
            'data'   => $mecz['Date'],
            'pozycje'=> $pozycje,
        ];
    }

    $histDir  = WRITEPATH . 'gracze/';
    $histPath = $histDir . "historia_pozycji_{$turniejID}.json";
    if (!is_dir($histDir)) mkdir($histDir, 0775, true);
    file_put_contents($histPath, json_encode($historia, JSON_PRETTY_PRINT));
}

}