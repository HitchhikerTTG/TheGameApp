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
        $model = new ShoutboxModel();
        $messages = $model->getMessages($clubHash);

        // Decode HTML entities
        foreach ($messages as &$message) {
            $message['message'] = html_entity_decode($message['message']);
        }

        return $this->response->setJSON($messages);
    }

    public function postMessage()
    {
        $forbiddenWords = include APPPATH . 'Config/forbidden_words.php';
        $emojis = ['&#x1F955;', '&#x1F33D;', '&#x1F345;', '&#x1F346;', '&#x1F966;', '&#x1F344;', '&#x1F354;', '&#x1F347;', '&#x1F349;', '&#x1F352;', '&#x1F353;', '&#x1F351;', '&#x1F34D;', '&#x1F34C;', '&#x1F34F;'];

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

        // Convert to HTML entities
        $message = htmlentities($message);

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