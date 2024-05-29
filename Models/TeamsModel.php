<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class TeamsModel extends Model{
    protected $table = 'teams';
    
    protected $allowedFields = [
        'nick',
        'ApiID',
        'Name',
        'Flag'

    ];
}