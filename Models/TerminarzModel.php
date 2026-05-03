<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class TerminarzModel extends Model{
    protected $table = 'terminarz';
	protected $primaryKey ='Id';
    
    protected $allowedFields = [				
			'Id',
			'ApiID',
			'HomeID',
			'HomeName',
			'AwayID',
			'AwayName',
			'CompetitionID',
			'CompetitionName',
			'Date',
			'Time',
			'Round',
			'GroupID',
			'ScoreHome',
			'ScoreAway',
			'zakonczony',
			'TurniejID', 
			'Rozpoczety'
    ];

	public function getMeczById($meczId) {
    	return $this->where('id', $meczId)->first();
	}

	public function getMeczeByTurniejId($turniejId, $onlyIds=false) {
		$query = $this->where('TurniejID', $turniejId)
				->orderBy('Date', 'asc'); // Sortowanie wyników względem daty;
    	if ($onlyIds) {
        	$query->select('Id, ApiID, Date, Time'); // Wybieramy tylko kolumnę Id
    		}

 	   return $query->findAll();
	}

function custom_log($message) {
    $file = WRITEPATH . 'logs/custom_log.log';
    
    // Sprawdź, czy katalog istnieje, jeśli nie, utwórz go
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }
    
    // Sprawdź, czy plik istnieje, jeśli nie, utwórz go
    if (!file_exists($file)) {
        file_put_contents($file, ''); // Utwórz pusty plik
    }
    
    // Odczytaj zawartość pliku
    $current = file_get_contents($file);
    
    // Dodaj nową wiadomość do logu
    $current .= "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    
    // Zapisz zaktualizowaną zawartość do pliku
    file_put_contents($file, $current);
}

public function zapiszLubAktualizujMecze($mecze, $localTurniejID) {
    // Opcjonalnie ustawienie domyślnego pliku logów dla innych przypadków
	// ini_set('error_log', WRITEPATH . 'logs/test_terminarz.log');

    $this->custom_log("Rozpoczęto zapiszLubAktualizujMecze");

    $istniejaceMecze = $this->select('id, ApiID')->findAll();
    $meczeWTerminarzu = [];
    foreach ($istniejaceMecze as $mecz) {
        $meczeWTerminarzu[$mecz['ApiID']] = $mecz['id'];
    }

    foreach ($mecze as $zaplanowanyMecz) {
        $meczDoZapisu = [
            'ApiID'=>$zaplanowanyMecz['id'],
            'HomeID'=>$zaplanowanyMecz['home_id'],
            'HomeName'=>$zaplanowanyMecz['home_name'],
            'AwayID'=>$zaplanowanyMecz['away_id'],
            'AwayName'=>$zaplanowanyMecz['away_name'],
            'CompetitionID'=>$zaplanowanyMecz['competition']['id'],
            'CompetitionName'=>$zaplanowanyMecz['competition']['name'],
            'Date'=>$zaplanowanyMecz['date'],
            'Time'=>$zaplanowanyMecz['time'],
            'Round'=>$zaplanowanyMecz['round'],
            'GroupID'=>$zaplanowanyMecz['group_id'],
            'TurniejID' =>$localTurniejID
        ];

        try {
            $this->custom_log("Przetwarzanie meczu: " . json_encode($meczDoZapisu));
            if (array_key_exists($zaplanowanyMecz['id'], $meczeWTerminarzu)) {
                // Aktualizacja
                $this->update($meczeWTerminarzu[$zaplanowanyMecz['id']], $meczDoZapisu);
                $this->custom_log("Zaktualizowano mecz ID: " . $meczeWTerminarzu[$zaplanowanyMecz['id']]);
            } else {
                // Dodanie nowego rekordu
                $this->save($meczDoZapisu);
                $this->custom_log("Dodano nowy mecz: " . $zaplanowanyMecz['id']);
            }
        } catch (Exception $e) {
            // Logowanie błędu
            $this->custom_log("Błąd podczas zapisu/aktualizacji meczu: " . $e->getMessage());
            $this->custom_log("Dane meczu: " . json_encode($meczDoZapisu));
        }
    }

	$this->custom_log("Zakończono zapiszLubAktualizujMecze");
}

public function getNajblizszeMecze($turniejId, $onlyIds =false)
    {
    // Pobierz mecze dla dzisiejszej daty dla określonego turnieju
    $dzisiejszeMecze = $this->getMeczeByDateAndTurniejId(date('Y-m-d'), $turniejId, $onlyIds);

    // Jeśli są dzisiejsze mecze, zwróć je
    if (!empty($dzisiejszeMecze)) {
        return $dzisiejszeMecze;
    }

    // Jeśli nie ma dzisiejszych meczów, znajdź mecze z najbliższego dnia z meczami dla tego turnieju
    return $this->getMeczeNajblizszegoDniaByTurniejId($turniejId, $onlyIds);
    }


	public function getMeczeByDateAndTurniejId($date, $turniejId, $onlyIds = false)
{
    $query = $this->where('Date', $date)
                  ->where('TurniejID', $turniejId)
				  ->orderBy('Date', 'asc'); // Sortowanie wyników względem daty;

    if ($onlyIds) {
        $query->select('Id, ApiID'); // Wybieramy tylko kolumnę Id
    }

    return $query->findAll();
}

public function getMeczeNajblizszegoDniaByTurniejId($turniejId, $onlyIds = false)
{
    $najblizszaData = $this->select('MIN(Date) as nextDate')
                           ->where('Date >', date('Y-m-d'))
                           ->where('TurniejID', $turniejId)
                           ->first();

    if (!empty($najblizszaData)) {
        $query = $this->where('Date', $najblizszaData['nextDate'])
                      ->where('TurniejID', $turniejId);

        if ($onlyIds) {
            $query->select('Id, ApiID'); // Wybieramy tylko kolumnę Id
        }

        return $query->findAll();
    }

    return [];
}

public function getMeczeDoRozegrania($turniejID, $onlyIds = false){
    $query = $this->where('Date>=', date('Y-m-d'))
                  ->where('TurniejID', $turniejID)
				  ->orderBy('Date', 'asc'); // Sortowanie wyników względem daty;

    if ($onlyIds) {
        $query->select('Id, ApiID,Date,Time'); // Wybieramy tylko kolumnę Id
    }

    return $query->findAll();
}

public function getRozegraneMecze($turniejID, $onlyIds = false){
	$query = $this	->where('TurniejID', $turniejID)
					-> where('zakonczony', 1)
					-> orderBy('Date', 'asc');
	
	if ($onlyIds) {
        $query->select('Id, ApiID'); // Wybieramy tylko kolumnę Id
    }

    return $query->findAll();
    }

	public function getRozpoczeteNieZakonczone($turniejID, $onlyIds = false) {
    // Upewnij się, że $turniejID jest prawidłowym identyfikatorem
    if (!is_numeric($turniejID)) {
        throw new InvalidArgumentException('Invalid TurniejID');
    }

    // Budowanie zapytania
    $query = $this->where('TurniejID', $turniejID)
                  ->where('Rozpoczety', 1)
                  ->where('zakonczony', 0)
                  ->orderBy('Date', 'asc');

    // Wybór tylko kolumn Id i ApiID, jeśli $onlyIds jest ustawione na true
    if ($onlyIds) {
        $query->select('Id, ApiID');
    }

    // Wykonanie zapytania i zwrócenie wyników
    return $query->findAll();
}


    public function getMatchDateTime($gameID) {
        return $this->where('Id', $gameID)->select('Date, Time')->first();
    }

public function czyRozpoczety($gameID) {
    $result = $this->where('Id', $gameID)->select('Rozpoczety')->first();
    return $result ? $result['Rozpoczety'] : null;
}
	

}


