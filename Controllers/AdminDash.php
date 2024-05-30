<?php


namespace App\Controllers;


use App\Controllers\BaseController;
use App\Models\TerminarzModel;
use App\Models\TurniejeModel;
use App\Models\KlubyModel;
use App\Models\ClubMembersModel;
use App\Services\MeczService;
use App\Libraries\Common;
use App\Models\KtoWCoGraModel;


class AdminDash extends BaseController
{

protected $_key;
    protected $_secret;
    
    public $connection = null;
    protected $_baseUrl = "https://livescore-api.com/api-client/";
    


        public function __construct()
    {
        helper(['url', 'form']);

        
    }

    public function sendEmail()
    {
        $email = \Config\Services::email();

        $email->setFrom('typuj@jakiwynik.com');
        $email->setTo('nirski@re-medium.pl');
        $email->setSubject('Mail prosto ode mnie');
        $email->setMessage('O bardzo ważnej treści');

        if ($email->send()) 
        {
            echo 'E-mail został wysłany pomyślnie.';
        } 
        else 
        {
            $data = $email->printDebugger(['headers']);
            print_r($data);
        }
    }
    // 
    // FUNKCJE ZWIAZANE Z TURNIEJAMI
    //
    public function loadTournaments(){
    $turniejModel = new TurniejeModel();
    // $warunki = $turniejModel->builder(); // To nie jest potrzebne, jeśli chcesz pobrać wszystkie rekordy.
   // Jeśli chcesz pobrać wszystkie wiersze z tabeli:
    $turnieje = $turniejModel->findAll();
    $daneDoPrzekazania=[];
    if ($turnieje) {
        // Przygotuj dane do przekazania, ale bez tworzenia HTML
        $daneDoPrzekazania = [];
        foreach ($turnieje as $turniej) {
        $daneDoPrzekazania[] = [
            'ID' => $turniej['id'],
            'CompetitionID' => $turniej['CompetitionID'],
            'CompetitionName' => $turniej['CompetitionName'],
            'Active' => $turniej['Active']
        ];
    }
        } else {
        $daneDoPrzekazania = "W kwestii turniejów nie mam nic do pokazania";
    }

    return $daneDoPrzekazania;
    }

    public function dodajTurniej()
    {
        $turniej = model(TurniejeModel::class);

        if ($this->request->getMethod() === 'post' && $this->validate([
            'nazwa' => 'required|min_length[3]|max_length[255]',
        ])) {
            $turniej->save([
                'CompetitionName' => $this->request->getPost('nazwa'),
                'CompetitionID'  => $this->request->getPost('CompetitionID'),
            ]);
            
            session()->setFlashData('sukces', 'Dodane poprawnie. <br> Czujesz moc? Chcesz dodać kolejne?');
            return redirect()->to('hell');
        }

        return view('hell');
    }

    public function zmienAktywnyTurniej(){
    $turniejModel = model(TurniejeModel::class);
    $ktoWCoGraModel = model(KtoWCoGraModel::class);
    $userModel = model(UserModel::class);
    $logger = \Config\Services::logger();



    // Pobranie ID turnieju z formularza (lub innego źródła, zależnie od implementacji)
    $aktywnyTurniejId = $this->request->getVar('aktywnyTurniej');

    // Zmiana aktywnego turnieju (i pobranie ID aktywnego turnieju)
    $turniej = $turniejModel->zmienAktywnyTurniej($aktywnyTurniejId);

    //$logger->info('Dane dotyczące turnieju: ' . json_encode($turniej));


    // Lokalizacja pliku konfiguracyjnego
    $configPath = WRITEPATH . 'ActiveTournament.json';

    $config['activeTournamentId'] = $turniej['id']; // Przykładowa zmiana ID
    $config['activeTournamentName'] = $turniej['CompetitionName']; // Przykładowa zmiana nazwy
    $config['activeCompetitionId'] = $turniej['CompetitionID']; // Przykładowa zmiana nazwy 
    $newJsonString = json_encode($config, JSON_PRETTY_PRINT); // JSON_PRETTY_PRINT dla czytelności
    file_put_contents($configPath, $newJsonString);



    
    // Pobranie listy użytkowników w aktywnym turnieju
    $usersInActiveTournament = $ktoWCoGraModel->getUsersOfTournament($turniej['id']);

    // Resetowanie flag dla wszystkich użytkowników
   $userModel->resetAllUsersActiveTournamentFlag();

    // Ustawienie flagi aktywnego turnieju dla użytkowników w aktywnym turnieju
    $userModel->setActiveTournamentFlagForUsers($usersInActiveTournament);
    
    // Przekierowanie lub wyświetlenie wiadomości po zmianie aktywnego turnieju
    return redirect()->to('hell')->with('message', 'Aktywny turniej został zmieniony.');




    }


/*    public function zapiszMeczeTurnieju($idTurnieju, $page=1){
    $common = new \App\Libraries\Common();
     $parametry_turniejowe['competition_id']=$idTurnieju;
     
     if ($page==1) {
        echo "<p>--- sprawdzam stronę nr ".$page." ---</p>";
     $data['turniejowe']=$common->getFixtures($parametry_turniejowe);   
     } else {
        echo "<p>--- sprawdzam stronę nr ".$page." ---</p>";
        $parametry_turniejowe['page']=$page;
        $data['turniejowe']=$common->getFixtures($parametry_turniejowe);
     }
     $terminarzModel = new \App\Models\TerminarzModel();
     $licznik=1;

    $meczeWTerminarzu = [];
    $meczeWTerminarzu = $terminarzModel->FindColumn('ApiID');

     foreach ($data['turniejowe']['fixtures'] as $zaplanowanyMecz){
    
    $meczDoZapisu = [
    'ApiID'=>$zaplanowanyMecz['id'],
    'HomeID'=>$zaplanowanyMecz['home_id'],
    'HomeName'=>$zaplanowanyMecz['home_name'],
    'AwayID'=>$zaplanowanyMecz['away_id'],
    'AwayName'=>$zaplanowanyMecz['away_name'],
    'CompetitionID'=>$zaplanowanyMecz['competition']['id'],
    'CompetitionName'=>$zaplanowanyMecz['competition']['name'],
    'Date'=>$zaplanowanyMecz['date'],
    'Time'=>$zaplanowanyMecz['time'],
    'Round'=>$zaplanowanyMecz['round'],
    'GroupID'=>$zaplanowanyMecz['group_id']
    ];
    

    //jeśli nie ma jeszcze żadnych meczów w terminarzu, dodaj mecze:

    if (!$meczeWTerminarzu) {
        $mesydz = "<p> ( ◡̀_◡́)ᕤ:  Tego meczu jeszcze nie było, zatem ZOSTAŁ DODANY MECZ ".$licznik++."; To mecz ".$zaplanowanyMecz['home_name']." vs ".$zaplanowanyMecz['away_name']." </p>";
        $query = $terminarzModel->save($meczDoZapisu);
    } else if (!in_array($zaplanowanyMecz['id'], $meczeWTerminarzu)) {
        $mesydz = "<p> ( ◡̀_◡́)ᕤ:  Tego meczu jeszcze nie było, zatem ZOSTAŁ DODANY MECZ ".$licznik++."; To mecz ".$zaplanowanyMecz['home_name']." vs ".$zaplanowanyMecz['away_name']." </p>";
        $query = $terminarzModel->save($meczDoZapisu);

    } else {
        
        $kluczyk = array_search($zaplanowanyMecz['id'], $meczeWTerminarzu);
        //echo "ten mecz juz mamy, jego id to ".++$kluczyk;
        $kluczyk++;
        $mesydz = "<p> ( ◡̀_◡́)ᕤ:  MECZ o indeksie ".$kluczyk." czyli mecz ".$zaplanowanyMecz['home_name']." vs ".$zaplanowanyMecz['away_name']." został zaktualizowany.</p>";
        $query = $terminarzModel->update($kluczyk,$meczDoZapisu);

    }

    if(!$query)
        {
            echo "<p>¯\_(ツ)_/¯: NIE ZOSTAŁ DODANY MECZ ".$licznik++."; To mecz ".$zaplanowanyMecz['home_name']." vs ".$zaplanowanyMecz['away_name']." </p>";
        }
        else
        {
            echo $mesydz;
        }

    }

    

    if ($data['turniejowe']['next_page']) {
        echo "<p>Mam coś do dodania</p>";
        $page++;
        $this->zapiszMeczeTurnieju($idTurnieju,$page);
    } else {
        echo "<p>Zakończyłem prace ręczne </p>";

    }


    }
*/
// NOWY POMYSŁ NA ZARZADZANIE ZAPISU TERMINARZA 

    public function zapiszMeczeTurnieju($iDTurnieju, $page=1) {
        $turniejeModel = model(TurniejeModel::class);
        $terminarzModel = model(TerminarzModel::class);
        $localIdTurnieju = $turniejeModel->znajdzLokalnyIdTurnieju($iDTurnieju);

        if (!$localIdTurnieju) {
            echo "Nie znaleziono lokalnego ID dla turnieju o zewnętrznym ID: $iDTurnieju";
            return;
        }
    
        $parametry_turniejowe = [
        'competition_id' => $iDTurnieju,
        'page' => $page
        ];
    
        $common = new \App\Libraries\Common();
        $data['turniejowe'] = $common->getFixtures($parametry_turniejowe);   
        $terminarzModel->zapiszLubAktualizujMecze($data['turniejowe']['fixtures'], $localIdTurnieju);

        if ($data['turniejowe']['next_page']) {
            $this->zapiszMeczeTurnieju($iDTurnieju, $page + 1);
        } else {
            echo "<p>Zakończyłem prace ręczne </p>";
        }
    }   






    // 
    // FUNKCJE ZWIAZANE Z KLUBAMI
    //

public function loadClubs(){
    $klubModel = model(KlubyModel::class);
    // $warunki = $turniejModel->builder(); // To nie jest potrzebne, jeśli chcesz pobrać wszystkie rekordy.
   // Jeśli chcesz pobrać wszystkie wiersze z tabeli:
    $kluby = $klubModel->getAllClubs();
    $daneDoPrzekazania=[];
    if ($kluby) {
        // Przygotuj dane do przekazania, ale bez tworzenia HTML
        $daneDoPrzekazania = [];
        foreach ($kluby as $klub) {
        $daneDoPrzekazania[] = [
            'ID' => $klub['id'],
            'Nazwa' => $klub['Nazwa'],
            'Opis' => $klub['Opis'],
            
        ];
    }
        } else {
            session()->setFlashData('info', 'Nie znaleziono klubów.');
    }

    return $daneDoPrzekazania;
    }

    public function dodajKlub()
    {
    $klubModel = model(KlubyModel::class);

        if ($this->request->getMethod() === 'post' && $this->validate([
            'nazwa' => 'required|min_length[3]|max_length[255]',
        ])) {
            $daneDoZapisu=[
                'Nazwa' => $this->request->getPost('nazwa'),
                'Opis'  => $this->request->getPost('opis'),
            ];
            $klubModel->addClub($daneDoZapisu);
            session()->setFlashData('sukces', 'Dodane poprawnie. <br> Czujesz moc? Chcesz dodać kolejne?');
            return redirect()->to('hell');
        }

        return view('hell');
    }


public function index() {
    // Wczytanie z pliku konfiguracyjnego json:
    $common = new Common();
    $tournaments = $common->loadTournaments();
    $kluby = $this->loadClubs();

    $meczService = new MeczService;

    $configPath = WRITEPATH . 'ActiveTournament.json';

    $config = [];
    if (file_exists($configPath)) {
        $jsonString = file_get_contents($configPath);
        $config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
    } else {
        // Ustawianie domyślnych wartości jeśli plik nie istnieje
        $config['activeTournamentId'] = 'Brak danych';
        $config['activeCompetitionId'] = 'Brak danych';
        $config['activeTournamentName'] = 'Brak danych';
    }

    $data = [
        'pageTitle' => 'Twoje własne osobiste piekielko',
        'message' => $config ? 'Well... here it all starts.' : 'Nie wybrano aktywnego turnieju.',
        'turnieje' => $tournaments,
        'kluby' => $kluby,
        'config' => $config
    ];

    $mecze = isset($config['activeTournamentId']) && $config['activeTournamentId'] !== 'Brak danych' 
             ? $meczService->getRozegraneMeczeTurnieju($config['activeTournamentId']) 
             : [];

    return view('administracja/index', $data)
           .view('administracja/listaTurniejow', $data)
           .view('administracja/dodajTurniej', $data)
           .view('administracja/dodajKlub', $data)
           .view('administracja/listaKlubow', $data)
           .view('administracja/listaMeczow', ['mecze' => $mecze]);
}




}

?>