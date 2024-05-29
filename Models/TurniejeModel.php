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

}