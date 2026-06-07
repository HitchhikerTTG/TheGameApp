<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class UserModel extends Model{
    protected $table = 'uzytkownicy';
    
    protected $allowedFields = [
        'id',
        'nick',
        'email',
        'passhash',
        'utworzone',
        'activated',
        'activatedON',
        'uniID',
        'PlaysTheActiveTournament',
    'notify_bet_saved',   // ← dodaj
    'notify_reminder',    // ← dodakj
    'digest_optout',

    ];

    public function changeActiveTournamentFlag($userId, $isActive)
    {
        $data = [
            'PlaysTheActiveTournament' => $isActive,
        ];

        return $this->update($userId, $data);
    }

    public function resetAllUsersActiveTournamentFlag()
{
    $data = [
        'PlaysTheActiveTournament' => false,
    ];

    return $this->where('id !=', 0)->update(null, $data); // Przykładowa klauzula where
}

       // Może być też funkcja do masowego ustawiania flagi dla użytkowników przypisanych do turnieju
public function setActiveTournamentFlagForUsers($userIds)
{
    if (empty($userIds)) {
        // Brak użytkowników do zaktualizowania, możesz zwrócić false lub wykonać inną akcję
        log_message('error', 'Próba wywołania setActiveTournamentFlagForUsers bez ID użytkowników.');
        return false;
    }

    $dataToUpdate = [];
    foreach ($userIds as $userId) {
        $dataToUpdate[] = [
            'id' => $userId,
            'PlaysTheActiveTournament' => true,
        ];
    }

    if (!empty($dataToUpdate)) {
        return $this->updateBatch($dataToUpdate, 'id');
    } else {
        // Nie ma danych do aktualizacji, zwróć false lub wykonaj inną akcję
        log_message('error', 'Nie znaleziono danych do aktualizacji w setActiveTournamentFlagForUsers.');
        return false;
    }
}

    public function getGameUserData($userUniId) {
        // Określenie, które pola mają zostać pobrane
        return $this->select('id, nick, activated, PlaysTheActiveTournament, uniID, notify_bet_saved, notify_reminder, digest_optout')

                    ->where('uniID', $userUniId)
                    ->first();
                    
        

                    
                    
                    
    }
    // Nowa metoda do pobierania użytkownika na podstawie nicku
    public function getUserByNick($nick) {
        return $this->where('nick', $nick)->first();
    }


    public function doKogoSlemy()
    {
        return $this->select('email')->findAll();
    }

    public function getActiveUsersInTournament($tournamentID) {
    $file = WRITEPATH . 'logs/test_log.log';

    // Zapisujemy URL do pliku logów
    file_put_contents($file, "wywołana metoda w modelu\n", FILE_APPEND);

    $query = $this->select('uzytkownicy.email')
                  ->join('ktowcogra', 'uzytkownicy.id = ktowcogra.userID')
                  ->where('uzytkownicy.PlaysTheActiveTournament', 1)
                  ->where('ktowcogra.turniejID', $tournamentID);

    $result = $query->findAll();

    // Zapisujemy liczbę zwróconych rekordów do pliku logów
    file_put_contents($file, "Liczba zwróconych rekordów: " . count($result) . "\n", FILE_APPEND);

    return $result;
}

    public function prepActiveUsersInTournament($tournamentID) {
    //$file = WRITEPATH . 'logs/test_log.log';

    // Zapisujemy URL do pliku logów
    //file_put_contents($file, "wywołana metoda w modelu\n", FILE_APPEND);

$query = $this->select('uzytkownicy.email, uzytkownicy.nick, uzytkownicy.id, uzytkownicy.uniID')
              ->join('ktowcogra', 'uzytkownicy.id = ktowcogra.userID')
              ->where('uzytkownicy.PlaysTheActiveTournament', 1)
              ->where('ktowcogra.turniejID', $tournamentID);

    $result = $query->findAll();

    // Zapisujemy liczbę zwróconych rekordów do pliku logów
    //file_put_contents($file, "Liczba zwróconych rekordów: " . count($result) . "\n", FILE_APPEND);

    return $result;
}

}


