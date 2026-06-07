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
use App\Models\UserModel;
use App\Models\PytaniaModel;
use App\Models\NotatkiModel;



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

        if ($this->request->getMethod() === 'POST' && $this->validate([
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

function custom_log($message) {
    $file = WRITEPATH . 'logs/custom_log.log';
    
    // Sprawdź, czy katalog istnieje, jeśli nie, utwórz go
    if (!is_dir(dirname($file))) {
        mkdir(dirname($file), 0755, true);
    }
    
    // Sprawdź, czy plik istnieje, jeśli nie, utwórz go
    if (!file_exists($file)) {
        file_put_contents($file, ''); // Utwórz pusty plik
    }
    
    // Odczytaj zawartość pliku
    $current = file_get_contents($file);
    
    // Dodaj nową wiadomość do logu
    $current .= "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    
    // Zapisz zaktualizowaną zawartość do pliku
    file_put_contents($file, $current);
}


//    public function zapiszMeczeTurnieju($iDTurnieju, $page=1) {
//        $turniejeModel = model(TurniejeModel::class);
//        $terminarzModel = model(TerminarzModel::class);
//        $localIdTurnieju = $turniejeModel->znajdzLokalnyIdTurnieju($iDTurnieju);
 
    public function zapiszMeczeTurnieju($iDTurnieju, $localIdTurnieju, $page=1) {
        $terminarzModel = model(TerminarzModel::class);


        $this->custom_log("Rozpoczęto ZapiszMeczeTurnieju");


//        if (!$localIdTurnieju) {
//            echo "Nie znaleziono lokalnego ID dla turnieju o zewnętrznym ID: $iDTurnieju";
//            return;
//        }
    
        $parametry_turniejowe = [
        'competition_id' => $iDTurnieju,
        'page' => $page
        ];

        //$this->custom_log("Parametry: " . print_r($parametry_turniejowe, true));
        
        $common = new \App\Libraries\Common();
        $data['turniejowe'] = $common->getFixtures($parametry_turniejowe);
        //$this->custom_log("Parametry: " . print_r($data['turniejowe'], true));
        $terminarzModel->zapiszLubAktualizujMecze($data['turniejowe']['data']['fixtures'], $localIdTurnieju);

        if ($data['turniejowe']['data']['next_page']) {
    
            $this->zapiszMeczeTurnieju($iDTurnieju, $localIdTurnieju, $page + 1);
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
    
        $validated = $this->validate([
            'nazwa' => [
                'rules'=>'required|min_length[3]|max_length[255]',
            'errors' =>[
                'required' =>'Musisz podać nazwę klubu',
                'min_length' => 'Nazwa klubu mieć co najmniej 3 znaki',
                'max_length' => 'nazwa klubu nie może przekraczać 255 znaków',
                ]
                ],
                ]);
        if (!$validated) {
            return view('administracja/dodajKlub', ['validation' => $this->validator]);
        } else {
            $daneDoZapisu=[
                'Nazwa' => $this->request->getPost('nazwa'),
                'Opis'  => $this->request->getPost('opis'),
            ];
            $klubModel->addClub($daneDoZapisu);
            session()->setFlashData('sukces', 'Dodane poprawnie. <br> Czujesz moc? Chcesz dodać kolejne?');
            return redirect()->to('hell');
        }

    }

public function dodajPytanie()
{
    $validated = $this->validate([
        'tresc' => [
            'rules'  => 'required|min_length[3]|max_length[255]',
            'errors' => [
                'required'   => 'Treść pytania jest wymagana',
                'min_length' => 'Treść pytania musi mieć co najmniej 3 znaki',
            ],
        ],
        'pkt' => [
            'rules'  => 'required|is_natural',
            'errors' => [
                'required'   => 'Liczba punktów jest wymagana',
                'is_natural' => 'Liczba punktów musi być liczbą naturalną',
            ],
        ],
        'wazneDo' => [
            'rules'  => 'required',
            'errors' => ['required' => 'Data ważności jest wymagana'],
        ],
    ]);

    if (!$validated) {
        session()->setFlashdata('fail', $this->validator->listErrors());
        return redirect()->to('/hell/pytania');
    }

    $wazneDo = str_replace('T', ' ', $this->request->getPost('wazneDo'));
    if (strlen($wazneDo) === 16) { $wazneDo .= ':00'; }

    $config = get_active_tournament_config();
    $data = [
        'tresc'     => $this->request->getPost('tresc'),
        'odpowiedz' => strip_tags($this->request->getPost('odpowiedz') ?? ''),
        'pkt'       => $this->request->getPost('pkt'),
        'wazneDo'   => $wazneDo,
        'utworzone' => date('Y-m-d H:i:s'),
        'zamkniete' => 0,
        'TurniejID' => (int)$config['activeTournamentId'],
    ];

    if (model(PytaniaModel::class)->addQuestion($data)) {
        session()->setFlashdata('success', 'Pytanie dodane.');
    } else {
        session()->setFlashdata('fail', 'Wystąpił błąd podczas dodawania pytania.');
    }
    return redirect()->to('/hell/pytania');
}


        public function getTourmanentQuestions($turniejID){
        $pytaniaModel = new PytaniaModel();
        return $pytaniaModel->getPytanieByTurniejID($turniejID);
    }

public function updateQuestionStatus()
{
    $id   = (int)$this->request->getPost('question_id');
    $nowy = (int)$this->request->getPost('aktywne');
    model(PytaniaModel::class)->update($id, ['aktywne' => $nowy]);
    session()->setFlashdata('success', 'Status pytania zaktualizowany.');
    return redirect()->to('/hell/pytania');
}


    public function dodajNotatke()
{
    $notatkiModel = model(NotatkiModel::class);
    $config       = get_active_tournament_config();

    $validated = $this->validate([
        'tresc' => [
            'rules'  => 'required|min_length[10]|max_length[4000]',
            'errors' => [
                'required'   => 'Treść notatki jest wymagana.',
                'min_length' => 'Notatka musi mieć co najmniej 10 znaków.',
                'max_length' => 'Notatka nie może przekraczać 4000 znaków.',
            ],
        ],
    ]);

    if (!$validated) {
        session()->setFlashdata('fail', $this->validator->listErrors());
        return redirect()->to('/hell');
    }

    $klubIDRaw = $this->request->getPost('KlubID');
    $data = [
        'tresc'        => $this->request->getPost('tresc'),
        'opublikowana' => (int)($this->request->getPost('opublikowana') ?? 0),
        'TurniejID'    => (int)$config['activeTournamentId'],
        'KlubID'       => ($klubIDRaw !== '' && $klubIDRaw !== null) ? (int)$klubIDRaw : null,
    ];

    if ($notatkiModel->addNotatka($data)) {
        session()->setFlashdata('success', 'Notatka została opublikowana.');
    } else {
        session()->setFlashdata('fail', 'Nie udało się zapisać notatki.');
    }

    return redirect()->to('/hell');
}

public function ukryjNotatke(int $id)
{
    $notatkiModel = model(NotatkiModel::class);
    $notatkiModel->ukryj($id);
    session()->setFlashdata('success', 'Notatka ukryta.');
    return redirect()->to('/hell');
}



    /* TU ZARZĄDZAMY KLUBAMI I UŻYTKOWNIKAMI */
    
    /*public function listClubMembers()
    {
        $clubMembersModel = new ClubMembersModel();
        $clubMembers = $clubMembersModel->getAllClubMembers();
        
        return view('administracja/assignUserToClub', ['clubMembers' => $clubMembers]);
    }*/


    public function assignUserToClub()
    {
        $clubMembersModel = model(ClubMembersModel::class);

        // Walidacja danych
        $validation = \Config\Services::validation();
        $validation->setRules([
            'userID' => 'required|alpha_numeric|is_not_unique[uzytkownicy.uniID]',
            'clubID' => 'required|integer|is_not_unique[kluby.id]'
        ], [
            'userID' => [
                'required' => 'Użytkownik jest wymagany.',
                'alpha_numeric' => 'ID użytkownika musi być ciągiem alfanumerycznym.',
                'is_not_unique' => 'Wybrany użytkownik nie istnieje.'
            ],
            'clubID' => [
                'required' => 'Klub jest wymagany.',
                'integer' => 'ID klubu musi być liczbą całkowitą.',
                'is_not_unique' => 'Wybrany klub nie istnieje.'
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            session()->setFlashdata('error', $validation->listErrors());
            return redirect()->back()->withInput();
        }

        $userID = $this->request->getPost('userID');
        $clubID = $this->request->getPost('clubID');

        // Sprawdzenie, czy uniID istnieje w tabeli uzytkownicy
        $userModel = model(UserModel::class);
        $user = $userModel->where('uniID', $userID)->first();

        if (!$user) {
            session()->setFlashdata('error', 'Wybrany użytkownik nie istnieje.');
            return redirect()->back()->withInput();
        }

        if ($clubMembersModel->addUserToClub($userID, $clubID)) {
            session()->setFlashdata('success', 'Użytkownik został przypisany do klubu.');
        } else {
            session()->setFlashdata('error', 'Nie udało się przypisać użytkownika do klubu.');
        }

        return redirect()->to('/AdminDash/assignUserToClubView');
    }

    public function assignUserToClubView() {
    $userModel = model(UserModel::class);
    $clubMembersModel = model(ClubMembersModel::class);
    $klubyModel = model(KlubyModel::class);

    $users = $userModel->findAll();
    $clubs = $klubyModel->findAll();
    $clubMembers = $clubMembersModel->getAllClubMembers();
    $usersInAnyClub = $clubMembersModel->getUsersInAnyClub();

    // Filtracja użytkowników, którzy są już w klubach
    $users = array_filter($users, function($user) use ($usersInAnyClub) {
        foreach ($usersInAnyClub as $userInClub) {
            if ($user['uniID'] == $userInClub['uniID']) {
                return false;
            }
        }
        return true;
    });

    return view('administracja/assignUserToClub', [
        'users' => $users,
        'clubs' => $clubs,
        'clubMembers' => $clubMembers,
        'validation' => \Config\Services::validation()
    ]);
}




    public function removeUserFromClub()
    {
        $clubMembersModel = model(ClubMembersModel::class);

        if ($this->request->getMethod() === 'POST' && $this->validate([
            'userID' => 'required|is_natural',
            'clubID' => 'required|is_natural',
        ])) {
            $userID = $this->request->getPost('userID');
            $clubID = $this->request->getPost('clubID');

            if ($clubMembersModel->removeUserFromClub($userID, $clubID)) {
                session()->setFlashData('success', 'Użytkownik został usunięty z klubu.');
            } else {
                session()->setFlashData('fail', 'Nie znaleziono użytkownika w tym klubie.');
            }
            return redirect()->to('/AdminDash/removeUserFromClub');
        }

        return view('administracja/removeUserFromClub', [
            'validation' => $this->validator
        ]);
    }

public function index()
{
    $config    = get_active_tournament_config();
    $turniejID = (int)($config['activeTournamentId'] ?? 0);
    return view('administracja/hell_dashboard', [
        'config'   => $config,
        'mecze'    => $turniejID ? (new \App\Services\MeczService())->getRozegraneMeczeTurnieju($turniejID) : [],
        'pytania'  => $turniejID ? model(\App\Models\PytaniaModel::class)
                                       ->where('TurniejID', $turniejID)->where('aktywne', 1)->findAll() : [],
        'notatki'  => $turniejID ? model(\App\Models\NotatkiModel::class)->getForAdmin($turniejID) : [],
        'allKluby' => model(\App\Models\KlubyModel::class)->getAllClubs(),
    ]);
}



public function mecze()
{
    $config    = get_active_tournament_config();
    $turniejID = (int)($config['activeTournamentId'] ?? 0);

    $terminarz    = [];
    $wszystkieMecze = [];

    if ($turniejID > 0) {
        $terminarz      = model(\App\Models\TerminarzModel::class)
                            ->getRozpoczeteNieZakonczone($turniejID) ?? [];
        $wszystkieMecze = (new \App\Services\MeczService())
                            ->getRozegraneMeczeTurnieju($turniejID) ?? [];

        foreach ([&$terminarz, &$wszystkieMecze] as &$lista) {
            foreach ($lista as &$m) {
                $path = WRITEPATH . "mecze/{$turniejID}/{$m['ApiID']}.json";
                if (file_exists($path)) {
                    $d = json_decode(file_get_contents($path), true) ?? [];
                    $m['plHomeName'] = $d['home_team']['plName'] ?? $d['home_team']['name'] ?? null;
                    $m['plAwayName'] = $d['away_team']['plName'] ?? $d['away_team']['name'] ?? null;
                    $m['naszCzas']   = $d['naszCzas'] ?? null;
                    $m['apiScoreH']  = $d['home_team']['score'] ?? null;
                    $m['apiScoreA']  = $d['away_team']['score'] ?? null;
                    $m['apiStatus']  = $d['status'] ?? null;
                }
            }
        }
        unset($lista, $m);
    }

    return view('administracja/hell_mecze', [
        'terminarz'      => $terminarz,
        'wszystkieMecze' => $wszystkieMecze,
    ]);
}


public function pytania()
{
    $config  = get_active_tournament_config();
    $pytania = $this->getTourmanentQuestions($config['activeTournamentId']);

    return view('administracja/hell_pytania', [
        'pageTitle' => 'Pytania',
        'config'    => $config,
        'pytania'   => $pytania,
    ]);
}

public function odpowiedziNaPytanie(int $pytanieID)
{
    $pytanie    = model(PytaniaModel::class)->getPytanieById($pytanieID);
    $odpowiedzi = model(\App\Models\OdpowiedziModel::class)->pobierzOdpowiedziNaPytanie($pytanieID);

    return view('administracja/hell_odpowiedzi', [
        'pageTitle'  => 'Odpowiedzi na pytanie',
        'pytanie'    => $pytanie,
        'odpowiedzi' => $odpowiedzi,
    ]);
}

public function zapiszPunktyOdpowiedzi()
{
    $odpowiedziModel = model(\App\Models\OdpowiedziModel::class);
    $pytanieID       = (int)$this->request->getPost('pytanieID');
    $pktArr          = $this->request->getPost('pkt') ?? [];

    foreach ($pktArr as $odpId => $pkt) {
        $odpowiedziModel->update((int)$odpId, ['pkt' => max(0, (int)$pkt)]);
    }

    session()->setFlashdata('success', 'Punkty zapisane.');
    return redirect()->to('/hell/pytania/odpowiedzi/' . $pytanieID);
}

public function gracze()
{
    $klubyModel        = model(\App\Models\KlubyModel::class);
    $userModel         = model(\App\Models\UserModel::class);
    $clubMembersModel  = model(\App\Models\ClubMembersModel::class);

    $allUsers    = $userModel->select('uniID, nick')->findAll();
    $usersByUniId = array_column($allUsers, null, 'uniID');

    $assignedUniIds = array_column($clubMembersModel->getUsersInAnyClub(), 'uniID');

    $kluby = $klubyModel->findAll();
    foreach ($kluby as &$k) {
        $memberRows   = $clubMembersModel->listClubMembers($k['id']);
        $k['members'] = array_values(array_map(
            fn($row) => $usersByUniId[$row['uniID']] ?? ['uniID' => $row['uniID'], 'nick' => '?'],
            $memberRows
        ));
    }
    unset($k);

    $usersNoClub = array_values(array_filter(
        $allUsers,
        fn($u) => !in_array($u['uniID'], $assignedUniIds, true)
    ));
    
    $clubMembers = $clubMembersModel->getAllClubMembers();

    return view('administracja/hell_gracze', [
        'kluby'      => $kluby,
        'allKluby'   => $kluby,
        'users'      => $allUsers,
        'usersNoClub'=> $usersNoClub,
        'clubMembers' => $clubMembers,
    ]);
}



public function turnieje()
{
    $config   = get_active_tournament_config();
    $notatki  = model(NotatkiModel::class)->getForAdmin($config['activeTournamentId']);
    $allKluby = model(KlubyModel::class)->getAllClubs();

    return view('administracja/hell_turnieje', [
        'pageTitle' => 'Turnieje',
        'config'    => $config,
        'turnieje'  => $this->loadTournaments(),
        'notatki'   => $notatki,
        'allKluby'  => $allKluby,
    ]);
}



    public function kampanie()
{
    $db = \Config\Database::connect();

    $files   = array_map('basename', glob(FCPATH . 'maile/*.html') ?: []);
    $sent    = $db->table('email_campaigns')->get()->getResultArray();
    $sentMap = [];
    foreach ($sent as $s) {
        $sentMap[$s['template_file']][$s['target_group']] = $s;
    }

    return view('administracja/kampanie', [
        'pageTitle'   => 'Kampanie email',
        'files'       => $files,
        'sentMap'     => $sentMap,
        'activeCount' => model(\App\Models\UserModel::class)->where('PlaysTheActiveTournament', 1)->countAllResults(),
        'allCount'    => model(\App\Models\UserModel::class)->countAllResults(),
    ]);
}

public function testKampania()
{
    $ok = (new \App\Services\EmailService())->sendCampaignTest(
        $this->request->getPost('template_file'),
        $this->request->getPost('subject'),
        'wit@nirski.com'
    );
    session()->setFlashdata($ok ? 'success' : 'fail',
        $ok ? 'Test wysłany na wit@nirski.com.' : 'Błąd wysyłki testowej.');
    return redirect()->to('/hell/kampanie');
}

public function digest()
{
    $config = get_active_tournament_config();
    return view('administracja/digest', [
        'pageTitle'   => 'Poranny digest',
        'config'      => $config,
        'activeCount' => model(\App\Models\UserModel::class)
                            ->where('PlaysTheActiveTournament', 1)
                             ->where('digest_optout', 0)
                            ->countAllResults(),
    ]);
}

public function wyslijDigest()
{
    $config    = get_active_tournament_config();
    $turniejID = (int)$config['activeTournamentId'];
    $komentarz = trim(strip_tags($this->request->getPost('komentarz') ?? ''));
    $subject   = trim(strip_tags($this->request->getPost('subject') ?? 'Dzień dobry, {nick}! Co w trawce piszczy?'));

    $users = model(\App\Models\UserModel::class)
        ->select('uniID, nick, email')
        ->where('PlaysTheActiveTournament', 1)
        ->where('digest_optout', 0)  
        ->findAll();

    if (empty($users)) {
        session()->setFlashdata('fail', 'Brak aktywnych graczy w tym turnieju.');
        return redirect()->to('/hell/digest');
    }

    $sent = (new \App\Services\EmailService())->sendDigest($users, $turniejID, $komentarz, $subject);
    session()->setFlashdata('success', "Wysłano digest do {$sent} graczy.");
    return redirect()->to('/hell/digest');
}



public function wyslijKampanie()
{
    $db           = \Config\Database::connect();
    $templateFile = $this->request->getPost('template_file');
    $subject      = $this->request->getPost('subject');
    $targetGroup  = $this->request->getPost('target_group');

    if ($targetGroup === 'tournament') {
        $targetGroup = 'tournament_' . (int)$this->request->getPost('tournament_id');
    }

    $existing = $db->table('email_campaigns')
        ->where('template_file', $templateFile)
        ->where('target_group', $targetGroup)
        ->where('sent_at IS NOT NULL', null, false)
        ->get()->getRow();

    if ($existing) {
        session()->setFlashdata('fail', 'Ta kampania została już wysłana do tej grupy!');
        return redirect()->to('/hell/kampanie');
    }

    $count = (new \App\Services\EmailService())->sendCampaign($templateFile, $subject, $targetGroup);
    session()->setFlashdata('success', "Wysłano do {$count} odbiorców.");
    return redirect()->to('/hell/kampanie');
}



}




   
?>
