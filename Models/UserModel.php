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
    public function getUsersWithoutTyp($matchID) {
        // Najpierw pobierzemy listę użytkowników, którzy grają w aktywnym turnieju
        $builder = $this->db->table($this->table);
        $builder->select('id, email');
        $builder->where('PlaysTheActiveTournament', 1);
        $activeUsers = $builder->get()->getResultArray();

        // Pobierz listę użytkowników, którzy podali typ na dany mecz
        $typyBuilder = $this->db->table('typy');
        $typyBuilder->select('user_id');
        $typyBuilder->where('GameID', $matchID);
        $typyUsers = $typyBuilder->get()->getResultArray();

        // Wyodrębnij user_id z wyników zapytania o typy
        $typyUserIds = array_column($typyUsers, 'user_id');

        // Filtracja użytkowników, którzy nie podali typu
        $usersWithoutTyp = array_filter($activeUsers, function($user) use ($typyUserIds) {
            return !in_array($user['id'], $typyUserIds);
        });

        // Zwróć tylko emaile
        return array_column($usersWithoutTyp, 'email');
    }
    

    public function getActiveUsersInTournament($tournamentID)
    {
        return $this->select('uzytkownicy.id, uzytkownicy.nick, uzytkownicy.email')
                    ->join('ktowcogra', 'uzytkownicy.id = ktowcogra.userID')
                    ->where('users.active', 1)
                    ->where('ktowcogra.turniejID', $tournamentID)
                    ->findAll();
    }

}







