<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class TurniejeModel extends Model{
    protected $table = 'turnieje';
	protected $primaryKey ='Id';
    
    protected $allowedFields = [				
			'id', // lokalny 
			'CompetitionID', //api
			'CompetitionName',
			'Active'
    ];

	public function znajdzLokalnyIdTurnieju($competitionId) {
        $turniej = $this->where('CompetitionID', $competitionId)->first();
        return $turniej ? $turniej['id'] : null;
    }
    
    public function zmienAktywnyTurniej($aktywnyTurniejId) {
23        // Ustawienie wszystkich turniejów na nieaktywne
24        $this->where('Active', 1)->set('Active', 0)->update();
25
26        // Ustawienie wybranego turnieju na aktywny
27        $this->update($aktywnyTurniejId, ['Active' => 1]);
28
29        // Pobranie danych aktywnego turnieju
30        return $this->find($aktywnyTurniejId);
31    }

}