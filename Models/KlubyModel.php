<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class KlubyModel extends Model{
    protected $table = 'kluby';
	protected $primaryKey ='id';
    
    protected $allowedFields = [				
			'id',
			'Nazwa',
			'Opis'
    ];

    // 1. Pobiera wszystkie kluby
    public function getAllClubs() {
        return $this->findAll();
    }

    // 2. Pobiera konkretny klub na podstawie jego ID
    public function getClubById($id) {
        return $this->asArray()->where(['id' => $id])->first();
    }

    // 3. Dodaje nowy klub
    public function addClub($data) {
        return $this->save($data);
    }

    // 4. Aktualizuje dane klubu
    public function updateClub($id, $data) {
        return $this->update($id, $data);
    }

    // 5. Usuwa klub
    public function deleteClub($id) {
        return $this->delete($id);
    }


}
?>