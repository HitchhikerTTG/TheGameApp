<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class GrupyModel extends Model{
    protected $table = 'grupy';
    
    protected $allowedaFields = [				'ApiID',
			'Name',
			'Stage',
			'Stage_PL'
    ];
}