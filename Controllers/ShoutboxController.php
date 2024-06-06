<?php namespace App\Controllers;

use App\Models\ShoutboxModel;
use CodeIgniter\Controller;

class ShoutboxController extends BaseController
{
    public function index()
    {
        $wstep = [
            'title' => 'Testowanie shoutboxu'
        ];

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setBody(view('typowanie/header', $wstep) . view('ukladanka/sg/chat'));
    }

    public function getMessages()
    {
        $clubHash = session()->get('club_hash');
        $model = new ShoutboxModel();
        $messages = $model->getMessages($clubHash);
        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->setJSON($messages);
    }

    public function postMessage()
    {
        $forbiddenWords = include APPPATH . 'Config/forbidden_words.php';
       $emojis = ['🦴', '🐲', '🍅', '🍆', '🥦', '🍄', '🥔', '🍇', '🍉', '🍒', '🍓', '🍑', '🍍', '🍌', '🍏','🍿','🥐','💬','👀','🦥','🫵🏻','🙈'];

        $userId = session()->get('user_id');
        $username = session()->get('username');
        $clubHash = session()->get('club_hash');
        $message = $this->request->getPost('message');

        foreach ($forbiddenWords as $word) {
            if (stripos($message, $word) !== false) {
                $emojiCount = ceil(strlen($word) / 3);
                $replacement = '';
                for ($i = 0; $i < $emojiCount; $i++) {
                    $replacement .= $emojis[array_rand($emojis)];
                }
                $message = str_ireplace($word, $replacement, $message);
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

        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=UTF-8')
            ->setJSON(['status' => 'success']);
    }
}