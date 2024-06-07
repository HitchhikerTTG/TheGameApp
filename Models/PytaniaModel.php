<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class PytaniaModel extends Model{
    protected $table = 'pytania';
    
    protected $allowedFields = [
        'tresc',
        'pkt',
        'utworzone',
        'wazneDo',
        'odpowiedz',
        'zamkniete',
        'TurniejID'
    ];

    public function getPytanieById(int $id)
    {
        return $this->where(['id' => $id])->first();
    }
    
    public function getPytanieByTurniejID(int $turniejID){
        
        return $this->where(['TurniejID' => $turniejID])->findAll();
        }

    public function addQuestion($data)
    {
        return $this->insert($data);
    }
}


