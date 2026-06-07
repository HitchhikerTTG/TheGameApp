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
        $pytanieModel = new \App\Models\PytaniaModel();
        $pytanie = $pytanieModel->find($data['idPyt']);
        
        // Sprawdzenie, czy aktualny czas nie przekracza daty „ważneDo"
        if (strtotime($pytanie['wazneDo']) < time()) {
            session()->setFlashData('error', 'Za późno na zmianę odpowiedzi.');
            return false; // Nie można zapisać odpowiedzi po upływie czasu ważności pytania
        }

        $existingAnswer = $this->where([
            'idPyt' => $data['idPyt'],
            'uniidOdp' => $data['uniidOdp']
        ])->first();

        if ($existingAnswer) {
            $data['id'] = $existingAnswer['id'];
        }

        return $this->save($data);
    }
    
    public function liczbaOdpowiedziNaPytanie($pytanieID) {
    $builder = $this->builder();
    $builder->where('idPyt', $pytanieID);
    return $builder->countAllResults(); // Zwraca liczbę wyników pasujących do danego meczu
}

	// Funkcja do pobierania odpowiedzi na pytanie
    public function pobierzOdpowiedziNaPytanie($pytanieID)
    {
        return $this->select('<odpowiedzi.id>, odpowiedzi.odp, odpowiedzi.pkt, uzytkownicy.nick')
                    ->join('uzytkownicy', 'uzytkownicy.uniID = odpowiedzi.uniidOdp')
                    ->where('odpowiedzi.idPyt', $pytanieID)
                    ->orderBy('uzytkownicy.nick', 'ASC')
                    ->findAll();
    }


public function liczbaOdpowiedzi($userUniID) {
    return $this->where('uniidOdp', $userUniID)
                ->where('idPyt >=', 25)
                ->countAllResults();
}

public function liczbaPrawidlowychOdpowiedzi($userUniID) {
    return $this->where('uniidOdp', $userUniID)
                ->where('idPyt >=', 25)
                ->whereIn('pkt', [1, 3])
                ->countAllResults();
}


}
