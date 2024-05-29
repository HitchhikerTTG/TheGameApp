<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class RecuerdaModel extends Model{

    protected $table = 'recuerda';
    
    protected $allowedFields = [
        'uniID',
        'token',
        'expires_at',
        'last_used',
        'user_agent',
    ];
    public function setRememberMeToken($userUniId, $token, $expiresAt, $userAgent) {
        // Sprawdź czy istnieje aktywny token dla tego uniID i user_agent
        $existingToken = $this->where('uniID', $userUniId)
                              ->where('user_agent', $userAgent)
                              ->where('expires_at >', date('Y-m-d H:i:s'))
                              ->first();
        
        if ($existingToken) {
            // Aktualizuj istniejący token
            $data = [
                'expires_at' => $expiresAt,
                'last_used' => date('Y-m-d H:i:s')
            ];
            $this->update($existingToken['id'], $data);
        } else {
            // Brak istniejącego tokena, stwórz nowy
            $data = [
                'uniID' => $userUniId,
                'token' => $token,
                'user_agent' => $userAgent,
                'expires_at' => $expiresAt,
                'last_used' => date('Y-m-d H:i:s')
            ];
            $this->insert($data);
        }
    }

    public function removeExpiredTokens() {
        $this->where('expires_at <', date('Y-m-d H:i:s'))->delete();
    }

    public function removeRememberMeToken($token)
    {
        $this->where('token', $token)->delete();
    }

    public function getRememberMeToken($token)
    {
        return $this->where('token', $token)->first();
    }

    public function verifyRememberMeToken($token, $currentUserAgent) {
    $tokenData = $this->where('token', $token)
                      ->where('expires_at >', date('Y-m-d H:i:s'))
                      ->where('user_agent', $currentUserAgent) // Dodatkowe sprawdzenie zgodności user_agent
                      ->first();

    if ($tokenData) {
        // Ustaw nową datę wygaśnięcia, na przykład tydzień od teraz
        $newExpiresAt = date('Y-m-d H:i:s', time() + 604800); // 604800 sekund to jeden tydzień

        // Aktualizuj datę ostatniego użycia oraz datę wygaśnięcia
        $this->update($tokenData['id'], [
            'last_used' => date('Y-m-d H:i:s'),
            'expires_at' => $newExpiresAt
        ]);

        // Możesz zwrócić więcej informacji o użytkowniku, jeśli to konieczne
        return $tokenData['uniID']; // Przykład zwracania uniID
    }

    return false; // Token nie istnieje, jest nieważny lub user_agent nie zgadza się
}

}

?>

