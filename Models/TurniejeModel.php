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
        // Ustawienie wszystkich turniejów na nieaktywne
        $this->where('Active', 1)->set('Active', 0)->update();

        // Ustawienie wybranego turnieju na aktywny
        $this->update($aktywnyTurniejId, ['Active' => 1]);

        // Pobranie danych aktywnego turnieju
        return $this->find($aktywnyTurniejId);
    }

}