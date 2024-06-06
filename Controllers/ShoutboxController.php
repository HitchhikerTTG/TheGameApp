<?php namespace App\Controllers;

use App\Models\ShoutboxModel;
use CodeIgniter\Controller;

class ShoutboxController extends BaseController
{
    public function index()
    {
        return view('ukladanka/sg/chat');
    }

    public function getMessages()
    {
        $clubHash = session()->get('club_hash');
        $model = new ShoutboxModel();
        $messages = $model->getMessages($clubHash);
        return $this->response->setJSON($messages);
    }

    public function postMessage()
    {
        $forbiddenWords = ['badword1', 'badword2'];
        $emojis = ['🥕', '🌽', '🍅', '🍆'];
        
        $userId = session()->get('user_id');
        $username = session()->get('username');
        $clubHash = session()->get('club_hash');
        $message = $this->request->getPost('message');

        foreach ($forbiddenWords as $word) {
            if (stripos($message, $word) !== false) {
                $message = str_ireplace($word, $emojis[array_rand($emojis)], $message);
            }
        }

        $data = [
            'user_id' => $userId,
            'username' => $username,
            'message' => $message,
            'club_hash' => $clubHash
        ];

        $model = new ShoutboxModel();
        $model->addMessage($data);

        return $this->response->setJSON(['status' => 'success']);
    }
}