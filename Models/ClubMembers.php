<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class ClubMembersModel extends Model{
    protected $table = 'clubMembers';
	protected $primaryKey ='id';
    
    protected $allowedFields = [				
			'id',
			'ClubID',
			'UserID',
			'memberSince'
    ];

    public function listClubMembers($clubID) {
    return $this->where('klubID', $clubID)->findAll();
    }

    public function addUserToClub($userID, $clubID) {
    // Sprawdzenie, czy użytkownik już jest w klubie
    if (!$this->where('klubID', $clubID)->where('userID', $userID)->first()) {
        return $this->save([
            'klubID' => $clubID,
            'userID' => $userID
//            'data_dolaczenia' => date('Y-m-d H:i:s') // Opcjonalnie
        ]);
    }
    return false; // Użytkownik już jest w klubie
    }

    public function removeUserFromClub($userID, $clubID) {
    $record = $this->where('klubID', $clubID)->where('userID', $userID)->first();
    if ($record) {
        return $this->delete($record['id']);
    }
    return false; // Nie znaleziono rekordu
}

    public function getClubsByUser($userID) {
        return $this->select('kluby.Nazwa, kluby.id as klubID, club_members.*')
                ->join('kluby', 'kluby.id = club_members.klubID')
                ->where('club_members.userID', $userID)
                ->findAll();
    }

    public function isActiveMember($userID, $clubID) {
    $record = $this->where('klubID', $clubID)
                   ->where('userID', $userID)
                   ->first();

    return !is_null($record);
}


}
?>