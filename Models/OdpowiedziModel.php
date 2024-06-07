<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class OdpowiedziModel extends Model{
    protected $table = 'odpowiedzi';
    
    protected $allowedFields = [
    		'idPyt',
			'uniidOdp',
			'odp',
			'kiedyModyfikowana',
			'pkt',
			'TurniejID'
    ];

	public function punktyZaPytania($userUniID, $turniejID){
		    $zapytanieOPytania = $this->builder();
            $zapytanieOPytania->where('uniidOdp', $userUniID);
            $zapytanieOPytania->where('TurniejID', $turniejID);
            $zapytanieOPytania->selectSum('pkt');
//         $liczbaPktZaPytania = $zapytanieOPytania->get()->getRow()->pkt;
			 $result = $zapytanieOPytania->get()->getRow();
	        return $result->pkt ?? 0; // Zwraca 0 jeśli nie ma wyników

	}
	
	   public function saveAnswer($data)
    {
        $existingAnswer = $this->where([
            'idPyt' => $data['idPyt'],
            'uniidOdp' => $data['uniidOdp']
        ])->first();

        if ($existingAnswer) {
            $data['id'] = $existingAnswer['id'];
        }

        return $this->save($data);
    }

}

