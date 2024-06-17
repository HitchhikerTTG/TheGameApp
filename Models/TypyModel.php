<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class TypyModel extends Model{
    protected $table = 'typy';
    protected $primaryKey = 'iD';
    protected $allowedFields = [
        'iD',
        'GameID',
        'UserID',
        'HomeTyp',
        'AwayTyp',
        'created_at',
        'ModifiedAt',
        'pkt',
        'TurniejID',
        'GoldenGame',
        'uniID'
    ];

    public function punktyZaMecze($userId, $turniejId) {
        $builder = $this->builder();
        $builder->where('uniID', $userId);
        $builder->where('TurniejID', $turniejId);
        $builder->selectSum('pkt');
        $result = $builder->get()->getRow();
        return $result->pkt ?? 0; // Zwraca 0 jeśli nie ma wyników
    }

    public function dokladneTrafienia($userId, $turniejId,$exactScorePoints = 3) {
        $builder = $this->builder();
        $builder->where('uniID', $userId);
        $builder->where('TurniejID', $turniejId);
        $builder->where('pkt', $exactScorePoints);
        
        return $builder->countAllResults();
    }

    public function getTypyByMeczId($meczId) {
       return $this->where('GameID', $meczId)->findAll();
    }
    public function getTypyByMeczIdAndUserId($meczId, $userUniID) {
       return $this->where('GameID', $meczId)->where('uniID', $userUniID)->first();
    }

    // Sprawdza, czy gracz użył jokera w danym meczu
    public function czyGraczUzylJokeraWTymMeczu($gameId, $userUniID) {
        return $this->where([
            'uniID' => $userUniID,
            'GameID' => $gameId,
            'GoldenGame' => true
        ])->first() != null;
    }

    // Sprawdza, czy gracz może jeszcze użyć jokera
    public function czyGraczMozeJeszczeUzycJokera($turniejId, $userUniID) {
        // Sprawdza czy istnieje jakikolwiek wpis z użytym jokerem dla danego użytkownika i turnieju
        return $this->where([
            'uniID' => $userUniID,
            'TurniejID' => $turniejId,
            'GoldenGame' => true
        ])->first() == null;
    }

public function usedGoldenBall($userUniId, $turniejId = null) {

    // Ścieżka do pliku konfiguracyjnego
    $configPath = WRITEPATH . 'ActiveTournament.json'; 
    $jsonString = file_get_contents($configPath);

    if ($jsonString === false) {
        // Obsługa błędu, jeśli nie udało się otworzyć pliku
        log_message('error', 'Nie udało się otworzyć pliku ActiveTournament.json');
        return 0; // Wartość domyślna
    }

    $config = json_decode($jsonString, true); 

    if (json_last_error() !== JSON_ERROR_NONE) {
        // Obsługa błędu, jeśli nie udało się zdekodować JSON-a
        log_message('error', 'Błąd dekodowania JSON: ' . json_last_error_msg());
        return 0; // Wartość domyślna
    }

    // Sprawdzenie, czy turniejId jest podany, jeśli nie, użycie ID aktywnego turnieju
    if ($turniejId === null) {
        $turniejId = $config['activeTournamentId'] ?? null;
    }

    if ($turniejId === null) {
        // Obsługa błędu, jeśli nie udało się ustawić ID turnieju
        log_message('error', 'Nie udało się ustawić ID turnieju');
        return 0; // Wartość domyślna
    }

    // Zapytanie do bazy danych
    $result = $this->select('GameID')
                   ->where('uniID', $userUniId)
                   ->where('TurniejID', $turniejId)
                   ->where('GoldenGame', 1)
                   ->first();

    return $result ? $result['GameID'] : 0; // Zwraca numer meczu, jeśli użytkownik wykorzystał "GoldenBall", w przeciwnym razie 0
}

    


    public function liczbaTypowDlaMeczu($meczID) {
    $builder = $this->builder();
    $builder->where('GameID', $meczID);
    return $builder->countAllResults(); // Zwraca liczbę wyników pasujących do danego meczu
}

    public function ktoTypujeTenMecz($meczId) {
        $builder = $this->db->table($this->table);
        $builder->select('typy.*, uzytkownicy.nick AS username');
        $builder->join('uzytkownicy', 'typy.uniID = uzytkownicy.uniID');
        $builder->where('typy.GameID', $meczId);
        return $builder->get()->getResultArray();
    }

        public function ktoTypujeTenMeczLimited($meczId) {
        $builder = $this->db->table($this->table);
        $builder->select('typy.GameID, typy.HomeTyp, typy.AwayTyp, typy.GoldenGame, typy.pkt, uzytkownicy.nick AS username');
        $builder->join('uzytkownicy', 'typy.uniID = uzytkownicy.uniID');
        $builder->where('typy.GameID', $meczId);
        return $builder->get()->getResultArray();
    }


public function canSaveTyp($gameID) {
    $terminarzModel = model(TerminarzModel::class);
    $match = $terminarzModel->getMatchDateTime($gameID);
    $matchTime = strtotime($match['Date'] . ' ' . $match['Time']);
    $currentTime = time();

    // Alternatively, you can use error_log() to log to the server error log
     error_log('Match time: ' . $matchTime);
     error_log('Current time: ' . $currentTime);

    return $currentTime <= $matchTime;
}

    public function zapiszTyp($data) {
    
    $terminarzModel = model(TerminarzModel::class);
    
    
     if ($terminarzModel->czyRozpoczety($data['GameID'])) {
            return false; // or handle as per your need
        }
             
        $warunki = $this->builder();
        $warunki->where('uniID', $data['uniID']);
        $warunki->where('GameID', $data['GameID']);
        $czyJestTenTyp = $warunki->get()->getResultArray();

        if ($czyJestTenTyp) {
            $aktualizowanyID = $czyJestTenTyp['0']['Id'];
            return $this->update($aktualizowanyID, $data);
        } else {
            return $this->insert($data);
        }
    }



    public function removeGoldenGame($userUniId, $gameID, $turniejID)
    {
        return $this->where([
            'uniID' => $userUniId,
            'GameID' => $gameID,
            'TurniejID' => $turniejID
        ])->set(['GoldenGame' => 0])->update();
        
        
    }



}