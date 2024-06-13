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

	public function zapiszLubAktualizujMecze($mecze, $localTurniejID) {
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

			if (array_key_exists($zaplanowanyMecz['id'], $meczeWTerminarzu)) {
            	// Aktualizacja
            	$this->update($meczeWTerminarzu[$zaplanowanyMecz['id']], $meczDoZapisu);
        	} else {
            	// Dodanie nowego rekordu
            	$this->save($meczDoZapisu);
        	}
    	}
    		
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


    public function getMatchDateTime($gameID) {
        return $this->where('Id', $gameID)->select('Date, Time')->first();
    }

public function czyRozpoczety($gameID) {
    $result = $this->where('Id', $gameID)->select('Rozpoczety')->first();
    return $result ? $result['Rozpoczety'] : null;
}
	

}


