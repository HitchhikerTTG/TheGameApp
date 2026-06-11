<?php namespace App\Models;

use CodeIgniter\Model;

class ShoutboxModel extends Model
{
    protected $table = 'shoutbox_messages';
    protected $allowedFields = ['uniID', 'username', 'message', 'club_hash', 'created_at'];

    public function getMessages($clubHash)
    {
        return $this->db->table('shoutbox_messages')
                    ->select('shoutbox_messages.*, uzytkownicy.emoji')
                    ->join('uzytkownicy', 'shoutbox_messages.uniID = uzytkownicy.uniID', 'left')
                    ->where('shoutbox_messages.club_hash', $clubHash)
                    ->orderBy('shoutbox_messages.created_at', 'DESC')
                    ->get()->getResultArray();
    }


    public function addMessage($data)
    {
        return $this->insert($data);
    }
}