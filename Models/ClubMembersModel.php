<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class ClubMembersModel extends Model{
    protected $table = 'clubMembers';
	protected $primaryKey ='id';
    
    protected $allowedFields = [				
			'id',
			'ClubID',
			'uniID',
			'memberSince'
    ];

    public function listClubMembers($clubID) {
    return $this->where('klubID', $clubID)->findAll();
    }

    public function addUserToClub($userID, $clubID) {
    // Sprawdzenie, czy użytkownik już jest w klubie
    if (!$this->where('klubID', $clubID)->where('uniID', $userID)->first()) {
        return $this->save([
            'klubID' => $clubID,
            'uniID' => $userID
//            'data_dolaczenia' => date('Y-m-d H:i:s') // Opcjonalnie
        ]);
    }
    return false; // Użytkownik już jest w klubie
    }

    public function removeUserFromClub($userID, $clubID) {
    $record = $this->where('klubID', $clubID)->where('uniID', $userID)->first();
    if ($record) {
        return $this->delete($record['id']);
    }
    return false; // Nie znaleziono rekordu
}

    public function getClubsByUser($uniID) {
        return $this->select('kluby.Nazwa, kluby.id as klubID, clubMembers.*')
                ->join('kluby', 'kluby.id = clubMembers.klubID')
                ->where('clubMembers.uniID', $uniID)
                ->first();
    }

    public function isActiveMember($userID, $clubID) {
    $record = $this->where('klubID', $clubID)
                   ->where('uniID', $userID)
                   ->first();

    return !is_null($record);
}


}
?>