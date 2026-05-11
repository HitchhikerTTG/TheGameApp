<?php
namespace App\Services;

use App\Libraries\Postmark;
use App\Models\UserModel;
use CodeIgniter\Database\BaseConnection;

class EmailService
{
    private Postmark $postmark;
    private BaseConnection $db;

    public function __construct()
    {
        $this->postmark = new Postmark();
        $this->db       = \Config\Database::connect();
    }

    /**
     * Dodaje/aktualizuje wpis w kolejce z 3-minutowym rolling window.
     */
    public function queueBetSaved(string $uniID, int $gameID, string $homeScore, string $awayScore): void
    {
        $userModel = model(UserModel::class);
        $user = $userModel->select('email, nick, notify_bet_saved')
                          ->where('uniID', $uniID)
                          ->first();

        if (!$user || empty($user['notify_bet_saved'])) {
            return;
        }

        $sendAfter = date('Y-m-d H:i:s', strtotime('+3 minutes'));
        $body      = "Mecz #{$gameID}: {$homeScore}:{$awayScore}";

        $existing = $this->db->table('email_queue')
            ->where('uniID', $uniID)
            ->where('type', 'bet_saved')
            ->where('sent', 0)
            ->get()->getRow();

        if ($existing) {
            $this->db->table('email_queue')->where('id', $existing->id)->update([
                'body'       => $body,
                'send_after' => $sendAfter,
            ]);
        } else {
            $this->db->table('email_queue')->insert([
                'uniID'      => $uniID,
                'type'       => 'bet_saved',
                'subject'    => 'Typ zapisany - JakiWynik.com',
                'body'       => $body,
                'send_after' => $sendAfter,
            ]);
        }
    }

    /**
     * Wysyła wszystkie dojrzałe emaile z kolejki. Wywołuje cron.
     * Zwraca liczbę wysłanych wiadomości.
     */
    public function processQueue(): int
    {
        $userModel = model(UserModel::class);
        $pending = $this->db->table('email_queue')
            ->where('sent', 0)
            ->where('send_after <=', date('Y-m-d H:i:s'))
            ->get()->getResultArray();

        $sent = 0;
        foreach ($pending as $item) {
            $user = $userModel->select('email, nick')
                              ->where('uniID', $item['uniID'])
                              ->first();

            if (!$user) {
                continue;
            }

            $html = "<p>Hej <strong>{$user['nick']}</strong>!</p>"
                  . "<p>Twój typ został zapisany:<br><strong>{$item['body']}</strong></p>"
                  . "<p>Powodzenia!</p>";

            if ($this->postmark->sendEmail('ogloszenia@jakiwynik.com', $user['email'], '', $item['subject'], $html)) {
                $this->db->table('email_queue')->where('id', $item['id'])->update(['sent' => 1]);
                $sent++;
            }
        }

        return $sent;
    }
}
