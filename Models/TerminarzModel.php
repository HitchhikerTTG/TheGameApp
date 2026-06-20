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
				->orderBy('Date', 'asc');
    	if ($onlyIds) {
        	$query->select('Id, ApiID, Date, Time');
    		}

 	   return $query->findAll();
	}

function custom_log($message) {
    $file = WRITEPATH . 'logs/custom_log.log';
    
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }
    
    if (!file_exists($file)) {
        file_put_contents($file, '');
    }
    
    $current = file_get_contents($file);
    $current .= "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    file_put_contents($file, $current);
}

public function zapiszLubAktualizujMecze($mecze, $localTurniejID) {
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
                $this->update($meczeWTerminarzu[$zaplanowanyMecz['id']], $meczDoZapisu);
                $this->custom_log("Zaktualizowano mecz ID: " . $meczeWTerminarzu[$zaplanowanyMecz['id']]);
            } else {
                $this->save($meczDoZapisu);
                $this->custom_log("Dodano nowy mecz: " . $zaplanowanyMecz['id']);
            }
        } catch (Exception $e) {
            $this->custom_log("Błąd podczas zapisu/aktualizacji meczu: " . $e->getMessage());
            $this->custom_log("Dane meczu: " . json_encode($meczDoZapisu));
        }
    }

	$this->custom_log("Zakończono zapiszLubAktualizujMecze");
}

public function getNajblizszeMecze($turniejId, $onlyIds =false)
    {
    $dzisiejszeMecze = $this->getMeczeByDateAndTurniejId(date('Y-m-d'), $turniejId, $onlyIds);

    if (!empty($dzisiejszeMecze)) {
        return $dzisiejszeMecze;
    }

    return $this->getMeczeNajblizszegoDniaByTurniejId($turniejId, $onlyIds);
    }


	public function getMeczeByDateAndTurniejId($date, $turniejId, $onlyIds = false)
{
    $query = $this->where('Date', $date)
                  ->where('TurniejID', $turniejId)
				  ->orderBy('Date', 'asc');

    if ($onlyIds) {
            $query->select('Id, ApiID, HomeID, AwayID, zakonczony');
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
            $query->select('Id, ApiID, HomeID, AwayID, zakonczony');
        }

        return $query->findAll();
    }

    return [];
}

public function getMeczeDoRozegrania($turniejID, $onlyIds = false){
    $query = $this->where('Date>=', date('Y-m-d'))
                  ->where('TurniejID', $turniejID)
				  ->orderBy('Date', 'asc');

    if ($onlyIds) {
        $query->select('Id, ApiID, Date, Time, HomeID, AwayID, zakonczony');
    }


    return $query->findAll();
}

public function getRozegraneMecze($turniejID, $onlyIds = false){
	$query = $this	->where('TurniejID', $turniejID)
					-> where('zakonczony', 1)
					-> orderBy('Date', 'asc');
	
	if ($onlyIds) {
        $query->select('Id, ApiID, HomeID, AwayID, zakonczony');

    }

    return $query->findAll();
    }

	public function getRozpoczeteNieZakonczone($turniejID, $onlyIds = false) {
    if (!is_numeric($turniejID)) {
        throw new InvalidArgumentException('Invalid TurniejID');
    }

    $query = $this->where('TurniejID', $turniejID)
                  ->where('Rozpoczety', 1)
                  ->where('zakonczony', 0)
                  ->orderBy('Date', 'asc');

    if ($onlyIds) {
        $query->select('Id, ApiID');
    }

    return $query->findAll();
}


    public function getMatchDateTime($gameID) {
        return $this->where('Id', $gameID)->select('Date, Time')->first();
    }

public function czyRozpoczety($gameID) {
    $result = $this->where('Id', $gameID)->select('Rozpoczety, Date, Time')->first();
    if (!$result) return null;

    if ($result['Rozpoczety'] == 1) return 1;

    $matchTime = strtotime($result['Date'] . ' ' . $result['Time'] . ' UTC');

    if (time() > $matchTime) {
        $this->update($gameID, ['Rozpoczety' => 1]);
        return 1;
    }

    return 0;
}
	
	public function getMeczeNaReminder(int $turniejID): array
{
    return $this->select('Id, ApiID, Date, Time, HomeName, AwayName')
        ->where('TurniejID', $turniejID)
        ->where('zakonczony', 0)
        ->where('Rozpoczety', 0)
        ->where('Date >=', date('Y-m-d'))
        ->where('Date <=', date('Y-m-d', strtotime('+1 day')))
        ->orderBy('Date', 'ASC')
        ->orderBy('Time', 'ASC')
        ->findAll();
}

public function getMecze24h(int $turniejId, bool $onlyIds = false): array
{
    $query = $this->where('TurniejID', $turniejId)
                  ->where("CONCAT(Date, ' ', Time) >=", date('Y-m-d H:i:s', strtotime('-6 hours')))
                  ->where("CONCAT(Date, ' ', Time) <=", date('Y-m-d H:i:s', strtotime('+24 hours')))
                  ->orderBy('Date', 'ASC')
                  ->orderBy('Time', 'ASC');

    if ($onlyIds) {
        $query->select('Id, ApiID, HomeID, AwayID, zakonczony');
    }

    return $query->findAll();
}

	public function setZakonczony(int $id): bool
{
    return $this->update($id, ['zakonczony' => 1]) !== false;
}

    public function getMeczeZakonczone24h(int $turniejID): array
{
    return $this->where('TurniejID', $turniejID)
        ->where('zakonczony', 1)
        ->where("CONCAT(Date, ' ', Time) >=", date('Y-m-d H:i:s', strtotime('-1 day')))
        ->where("CONCAT(Date, ' ', Time) <",  date('Y-m-d H:i:s'))
        ->orderBy('Date', 'ASC')
        ->orderBy('Time', 'ASC')
        ->findAll();
}

public function getMeczePrzyszle24h(int $turniejID): array
{
    return $this->where('TurniejID', $turniejID)
        ->where('zakonczony', 0)
        ->where('Rozpoczety', 0)
        ->where("CONCAT(Date, ' ', Time) >=", date('Y-m-d H:i:s'))
        ->where("CONCAT(Date, ' ', Time) <=", date('Y-m-d H:i:s', strtotime('+24 hours')))
        ->orderBy('Date', 'ASC')
        ->orderBy('Time', 'ASC')
        ->findAll();
}

}


