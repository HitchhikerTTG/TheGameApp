<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class KtoWCoGraModel extends Model{
    protected $table = 'ktowcogra';
    
    protected $allowedFields = [
        	'userID',
			'turniejID'
    ];

    public function getUserTournaments($userID) {
        $zapytanieGracz = $this->builder();
        $zapytanieGracz->where('userID', $userID);
        $dane = $zapytanieGracz->get()->getResultArray();
        return $dane;
    }

    public function addUserTTTournament($userID, $turniejID) {
    $data = [
        'userID' => $userID,
        'turniejID' => $turniejID
    ];

    // Sprawdzanie, czy użytkownik jest już przypisany do turnieju
    $exists = $this->where('userID', $userID)
                   ->where('turniejID', $turniejID)
                   ->first();

    if (!$exists) {
        return $this->save($data); // Zwraca true jeśli zapis się powiódł, false w przeciwnym wypadku
    } else {
        // Użytkownik jest już przypisany do turnieju
        return false; // Możesz zwrócić false lub obsłużyć to inaczej
    }
    }

    public function getUsersOfTournament($tournamentId) {
        $query = $this->where('turniejID', $tournamentId)->findAll();
        $userIds = [];

        foreach ($query as $row) {
            $userIds[] = $row['userID'];
        }

        return $userIds;
    }
}