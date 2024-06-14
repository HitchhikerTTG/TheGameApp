<?php 
namespace App\Models;  
use CodeIgniter\Model;
  
class UserModel extends Model{
    protected $table = 'uzytkownicy';
    
    protected $allowedFields = [
        'nick',
        'email',
        'passhash',
        'utworzone',
        'activated',
        'activatedON',
        'uniID',
        'PlaysTheActiveTournament'

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
        return $this->select('id, nick, activated,PlaysTheActiveTournament, uniID') // Zakładając, że chcesz tylko te trzy pola
                    ->where('uniID', $userUniId)
                    ->first();
    }
    // Nowa metoda do pobierania użytkownika na podstawie nicku
    public function getUserByNick($nick) {
        return $this->where('nick', $nick)->first();
    }


        // Funkcja, która zwraca listę email użytkowników, którzy nie podali typu na mecz o wskazanym ID
    public function getUserWithoutTyp($matchID) {
        // Najpierw pobierzemy listę użytkowników, którzy grają w aktywnym turnieju
        $builder = $this->db->table($this->table);
        $builder->select('users.email');
        $builder->where('users.PlaysTheActiveTournament', 1);
        $builder->whereNotExists(function($builder) use ($matchID) {
            $builder->select('*')
                    ->from('typy')
                    ->where('typy.GameID', $matchID)
                    ->where('typy.user_id = users.id');
        });

        $query = $builder->get();
        return $query->getResultArray();
    }

}




