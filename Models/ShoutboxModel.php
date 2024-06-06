<?php namespace App\Models;

use CodeIgniter\Model;

class ShoutboxModel extends Model
{
    protected $table = 'shoutbox_messages';
    protected $allowedFields = ['uniID', 'username', 'message', 'club_hash', 'created_at'];

    public function getMessages($clubHash)
    {
        return $this->where('club_hash', $clubHash)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    public function addMessage($data)
    {
        return $this->insert($data);
    }
}