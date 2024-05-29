<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class PomocnicaPiPModel extends Model{
    protected $table = 'pomocniczaPozycjePkt';
    protected $primaryKey = 'UniID';
    protected $allowedFields = [				'ApiID',
			'UniID',
			'Pozycja',
			'Punkty',
            'Zmodyfikowane'
    ];
}