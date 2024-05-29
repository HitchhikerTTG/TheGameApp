<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class TokenModel extends Model{
    protected $table = 'tokeny';
    
    protected $allowedFields = [
        'RequestorUNIID',
        'CreatedAT',
        'UseddAT',
        'ValidUntil',
        'Valid',
        'Reason',
        'Token',

    ];
}