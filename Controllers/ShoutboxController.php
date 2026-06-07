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

        return view('typowanie/header', $wstep)
               .view('ukladanka/sg/chat');
    }

public function getMessages()
{
    $clubHash = session()->get('club_hash');
    if (!$clubHash) {
        return $this->response->setJSON([]);
    }
    $model    = new ShoutboxModel();
    $messages = $model->getMessages($clubHash);
    foreach ($messages as &$message) {
        $message['message'] = html_entity_decode($message['message']);
    }
    return $this->response->setJSON($messages);
}


    public function postMessage()
    {
        $forbiddenWords = include APPPATH . 'Config/forbidden_words.php';
        $emojis = ['🍔', '🍟', '🍕', '🌭', '🍿', '🥗', '🍣', '🍱', '🍩', '🍪', '🍫', '🍬', '🍰', '🎂', '🍎', '🍉', '🍇', '🍒', '🍓', '🥑', '🥥', '🍷', '🥂', '😍', '😂'];

        $userId = session()->get('loggedInUser');
        $username = session()->get('username');
        $clubHash = session()->get('club_hash');
        $message = $this->request->getPost('message');

        // Sanitacja danych
        $message = strip_tags($message); // Usuwa HTML i PHP tagi
        $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); // Konwertuje specjalne znaki na encje HTML

        foreach ($forbiddenWords as $word) {
            if (stripos($message, $word) !== false) {
                $emojiCount = ceil(strlen($word) / 3)+1;
                $replacement = '';
                for ($i = 0; $i < $emojiCount; $i++) {
                    $replacement .= $emojis[array_rand($emojis)];
                }
                $message = str_ireplace($word, $replacement, $message);
            }
        }

        // Convert to HTML entities
        //$message = htmlentities($message);

        $data = [
            'uniID' => $userId,
            'username' => $username,
            'message' => $message,
            'club_hash' => $clubHash
        ];

        $model = new ShoutboxModel();
        $model->addMessage($data);

        return $this->response->setJSON(['status' => 'success']);
    }
}