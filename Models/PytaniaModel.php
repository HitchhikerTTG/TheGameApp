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
}