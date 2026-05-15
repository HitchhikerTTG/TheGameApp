<?php


namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TerminarzModel;
use App\Models\TypyModel;
use App\Models\TabelaModel;
use App\Models\KtoWCoGraModel;
use App\Services\MeczService;
use App\Models\PytaniaModel;
use App\Models\OdpowiedziModel;
use App\Services\EmailService;



$session = \Config\Services::session();

use DateTime;
use DateTimeZone;

class TheGame extends BaseController
{

    //protected $_key;
    //protected $_secret;

    protected $meczService;
    
    // DODAĆ (przed konstruktorem):
    protected array $config  = [];
    protected $userModel;
    protected $tabelaModel;
    protected $pytaniaModel;
    protected $odpowiedzModel;

    // KONSTRUKTOR -- dodać 3 linie:
    public function __construct()
    {
        $this->meczService = new MeczService();
        $this->config      = get_active_tournament_config();
        $this->userModel   = model(UserModel::class);
        $this->tabelaModel = model(TabelaModel::class);
        $this->pytaniaModel= model(PytaniaModel::class);
        $this->odpowiedzModel =model(OdpowiedziModel::class);
        
    }



    public function index($turniejID = null){

        //$configPath =  WRITEPATH . 'ActiveTournament.json'; // Załóżmy, że to Twoja domyślna lokalizacja
        //$jsonString = file_get_contents($configPath);
        //$config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
        
        //$config = get_active_tournament_config();
            
        if ($turniejID === null) {
            // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
            $turniejID = $this->config['activeTournamentId'];
            $turniejName = $this->config['activeTournamentName'];
            } else {
                $turniejName = "Wit musi zmienić sposób pobierania danych turnieju";
            }

        $loggedInUserId = session()->get('loggedInUser');


        //$model = model(TabelaModel::class);
        $tabelaDanych = $this->tabelaModel->gimmeTabelaGraczy($turniejID);

        //$userModel = model(UserModel::class);
        $daneUzytkownika = $this->userModel->getGameUserData($loggedInUserId);

        $mecze = $this->meczService->getMeczeUzytkownikaWTurnieju($loggedInUserId, $turniejID);
       // $mecze = $this->meczService->prepareMeczeTurnieju($turniejID);    
        $pytania = [];

        // Przekazanie danych do widoku
        $daneTurniejowe = [
            'tabelaDanych' => $tabelaDanych,
            'turniejID' => $turniejID,
            'userID' => session()->get('loggedInUser')
            //'title' => 'Wit pastwi się nad tabelą'
            ];
        
        $wstep = [
            'title'=> $turniejName
        ];


        return view('typowanie/header', $wstep)
                .view('ukladanka/sg/belkausera', ['daneUzytkownika'=>$daneUzytkownika])
                .view('ukladanka/sg/mecze',['mecze' => $mecze,'turniejID'=>$turniejID])
                .view('ukladanka/sg/pytania',$pytania)
                .view('ukladanka/sg/chat')
                .view('tabela/tabela', $daneTurniejowe)
                .view('ukladanka/sg/skrypty')
                .view('typowanie/footer');
                
    }

    public function testIndex($turniejID = null) {

    if ($turniejID === null) {
        $turniejID = $this->config['activeTournamentId'];
        $turniejName = $this->config['activeTournamentName'];
        $zewnetrzneIDTurnieju = $this->config['activeCompetitionId'];
    } else {
        $turniejName = "Wit musi zmienić sposób pobierania danych turnieju";
    }

    $loggedInUserId = session()->get('loggedInUser');

    if (empty($daneUzytkownika['PlaysTheActiveTournament'])) {
    return view('typowanie/header', ['title' => $turniejName])
         . view('ukladanka/sg/brakTurnieju')
         . view('typowanie/footer');
}



    //$model = model(TabelaModel::class);
    $tabelaDanych = $this->tabelaModel->gimmeTabelaGraczy($turniejID);

    //$userModel = model(UserModel::class);
    $daneUzytkownika = $this->userModel->getGameUserData($loggedInUserId);
    $daneUzytkownika['usedGoldenBall'] = session()->get('usedGoldenBall', 0);

    $mecze4 = $this->meczService->meczeUzytkownikaWTurnieju($loggedInUserId, $turniejID, $zewnetrzneIDTurnieju, "najblizsze");

    // Fetch JSON data for each match
foreach ($mecze4 as &$mecz) {
        $jsonPath = WRITEPATH . "mecze/$turniejID/{$mecz['ApiID']}.json";
        if (file_exists($jsonPath)) {
            $mecz['details'] = json_decode(file_get_contents($jsonPath), true);
        } else {
            $mecz['details'] = null;
        }
        if($mecz['rozpoczety']){
            $jsonPath = WRITEPATH . "typy/{$mecz['Id']}.json";
            if (file_exists($jsonPath)) {
            
            
                $data = json_decode(file_get_contents($jsonPath), true);
                $mecz['typyGraczy'] = isset($data['types']) ? $data['types'] : [];
                $mecz['podsumowanieTypow'] = isset($data['summary']) ? $data['summary'] : [];
                //$mecz['naKoniec'] = isset($data['zakonczone']) ? $data['zakonczone'] : [];
                }
            else {
                $mecz['typyGraczy'] = null;
                $mecz['podsumowanieTypow'] = null;
                //$mecz['naKoniec'] = null;
            }
        }
    }

    $daneTurniejowe = [
        'tabelaDanych' => $tabelaDanych,
        'turniejID' => $turniejID,
        'userID' => $daneUzytkownika['id'],
    ];
    
    $wstep = [
        'title'=> $turniejName
    ];
    //$pytaniaModel= new PytaniaModel();
    //$odpowiedzModel = new OdpowiedziModel();
    $pytania = $this->pytaniaModel->getActiveQuestions($turniejID);

foreach ($pytania as &$pytanie) {

    $pytanie['liczbaOdpowiedzi'] = $this->odpowiedzModel->liczbaOdpowiedziNaPytanie($pytanie['id']);

    $odpowiedz = $this->odpowiedzModel->where('idPyt', $pytanie['id'])
                                ->where('uniidOdp', $loggedInUserId)
                                ->first();
    if ($odpowiedz) {
        $pytanie['dotychczasowa_odpowiedz'] = $odpowiedz['odp'];
    }
    
    // Konwersja 'wazneDo' na czas lokalny
    if (isset($pytanie['wazneDo'])) {
        $utcDateTime = new DateTime($pytanie['wazneDo'], new DateTimeZone('UTC'));
        $localTimezone = new DateTimeZone('Europe/Warsaw'); // Zastąp 'Europe/Warsaw' swoją strefą czasową
        $utcDateTime->setTimezone($localTimezone);
        $pytanie['wazneDoLocal'] = $utcDateTime->format('Y-m-d H:i:s');
    }

    // Sprawdzenie, czy czas wywołania funkcji jest po wartości 'wazneDo'
    $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
    if ($currentDateTime > $utcDateTime) {
        // Ścieżka do pliku JSON
        $jsonFilePath = WRITEPATH . "odpowiedzi/{$pytanie['id']}.json";

        if (file_exists($jsonFilePath)) {
            // Pobierz dane z pliku JSON
            $jsonData = file_get_contents($jsonFilePath);
            $pytanie['odpowiedzi'] = json_decode($jsonData, true);
        } else {
            // Wygeneruj dane i zapisz do pliku JSON
            $this->wygenerujOdpowiedziNaPytanie($pytanie['id']);
            $jsonData = file_get_contents($jsonFilePath);
            $pytanie['odpowiedzi'] = json_decode($jsonData, true);
        }
    }
}
unset($pytanie); // Unset reference







    

    return view('typowanie/header', $wstep)
           .view('ukladanka/sg/belkausera', ['daneUzytkownika' => $daneUzytkownika])
           .view('ukladanka/sg/chat')
           .view('ukladanka/sg/znowumecze', [
               'mecze' => $mecze4,
               'turniejID' => $turniejID,
               'userID' => $loggedInUserId,
               'usedGoldenBall' => $daneUzytkownika['usedGoldenBall']
           ])
           .view('ukladanka/sg/pytania', ['pytania'=>$pytania])
           .view('tabela/tabela', $daneTurniejowe)
           .view('ukladanka/sg/SkryptTypowania')
           .view('typowanie/footer');
}

        public function akordeon($turniejID = null){
        //$configPath =  WRITEPATH . 'ActiveTournament.json'; // Załóżmy, że to Twoja domyślna lokalizacja
        //$jsonString = file_get_contents($configPath);
        //$config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
            
        //$config = get_active_tournament_config();
        
        if ($turniejID === null) {
            // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
            $turniejID = $this->config['activeTournamentId'];
            $turniejName = $this->config['activeTournamentName'];
            $zewnetrzneIDTurnieju = $this->config['activeCompetitionId'];
            } else {
                $turniejName = "Wit musi zmienić sposób pobierania danych turnieju";
            }

        $loggedInUserId = session()->get('loggedInUser');


        //$model = model(TabelaModel::class);
        $tabelaDanych = $this->tabelaModel->gimmeTabelaGraczy($turniejID);

        //$userModel = model(UserModel::class);
        $daneUzytkownika = $this->userModel->getGameUserData($loggedInUserId);
        // Pobranie informacji o "GoldenBall" z sesji

        $daneUzytkownika['usedGoldenBall'] = session()->get('usedGoldenBall', 0);


#        $mecze = $this->meczService->getMeczeDnia($turniejID);
       // $mecze = $this->meczService->prepareMeczeTurnieju($turniejID);    

/*       echo "<pre>";
        print_r($mecze);
        echo "</pre>";

        $mecze2 = $this->meczService->getMeczeTurnieju($turniejID);

        echo "<p>Aaaa. bo Ty o wszystkie pytałeś:</p><pre>";
        print_r($mecze2);
        echo "</pre>";

        $mecze3 = $this->meczService->getMeczeTurniejuDoRozegrania($turniejID);

        echo "<p>A tak po prawdzie, to jeszcze bym chciał wszystkie, które jeszcze nie zostały rozegrane:</p><pre>";
        print_r($mecze3);
        echo "</pre>";
*/
        $mecze4 = $this->meczService->meczeUzytkownikaWTurnieju($loggedInUserId, $turniejID, $zewnetrzneIDTurnieju,"do_rozegrania");

/*      echo "<p>jeszcze sie okaże, że będę śpiewał hallelujah:</p><pre>";
        print_r($mecze4);
        echo "</pre>";
*/
/*
        echo "<p>Jeśli widzisz ten kod, to jest duża szansa</p><pre>";
        $this->meczService->zapiszDaneDoJson($turniejID, $zewnetrzneIDTurnieju);
        echo "</pre><p>że pojawiły sie nowe katalogi z odpowiednimi danymi... sprawdź i trzymaj kciuki, a w razie czego chwal pana i wołaj Alleluja</p>";
*/
    


        $pytania = [];
        /*
        //Przekazanie danych do widoku?*/

        $daneTurniejowe = [
            'tabelaDanych' => $tabelaDanych,
            'turniejID' => $turniejID,
            'userID' => session()->get('loggedInUser')
            //'title' => 'Wit pastwi się nad tabelą'
            ];
        
        $wstep = [
            'title'=> $turniejName
        ];

        //$userModel = model(UserModel::class);
        $daneUzytkownika = $this->userModel->getGameUserData($loggedInUserId);

        return view('typowanie/header', $wstep)
               .view('ukladanka/sg/belkausera', ['daneUzytkownika'=>$daneUzytkownika])
               .view('ukladanka/sg/znowumecze',['mecze' => $mecze4,'turniejID'=>$turniejID,'userID'=>$loggedInUserId])               .view('ukladanka/sg/meczenanowo',['mecze' => $mecze4,'turniejID'=>$turniejID,'userID'=>$loggedInUserId])
               .view('ukladanka/sg/jeszczejedenskrypt')
               .view('typowanie/footer');

        

    }


    public function wszystkieMecze($turniejID = null) {
    //$configPath = WRITEPATH . 'ActiveTournament.json'; 
    //$jsonString = file_get_contents($configPath);
    //$config = json_decode($jsonString, true);

//    $config = get_active_tournament_config();

    if ($turniejID === null) {
        $turniejID = $this->config['activeTournamentId'];
        $turniejName = $this->config['activeTournamentName'];
        $zewnetrzneIDTurnieju = $this->config['activeCompetitionId'];
    } else {
        $turniejName = "Wit musi zmienić sposób pobierania danych turnieju";
    }

    $loggedInUserId = session()->get('loggedInUser');
    
    if (empty($daneUzytkownika['PlaysTheActiveTournament'])) {
    return view('typowanie/header', ['title' => $turniejName])
         . view('ukladanka/sg/brakTurnieju')
         . view('typowanie/footer');
    }


        //$model = model(TabelaModel::class);
    $tabelaDanych = $this->tabelaModel->gimmeTabelaGraczy($turniejID);

    //$userModel = model(UserModel::class);
    $daneUzytkownika = $this->userModel->getGameUserData($loggedInUserId);
    $daneUzytkownika['usedGoldenBall'] = session()->get('usedGoldenBall', 0);

    $mecze4 = $this->meczService->meczeUzytkownikaWTurnieju($loggedInUserId, $turniejID, $zewnetrzneIDTurnieju, "do_rozegrania");

    // Fetch JSON data for each match
    foreach ($mecze4 as &$mecz) {
        $jsonPath = WRITEPATH . "mecze/$turniejID/{$mecz['ApiID']}.json";
        if (file_exists($jsonPath)) {
            $mecz['details'] = json_decode(file_get_contents($jsonPath), true);
        } else {
            $mecz['details'] = null;
        }
        if($mecz['rozpoczety']){
            $jsonPath = WRITEPATH . "typy/{$mecz['Id']}.json";
            if (file_exists($jsonPath)) {
            
            
                $data = json_decode(file_get_contents($jsonPath), true);
                $mecz['typyGraczy'] = isset($data['types']) ? $data['types'] : [];
                $mecz['podsumowanieTypow'] = isset($data['summary']) ? $data['summary'] : [];
                //$mecz['naKoniec'] = isset($data['zakonczone']) ? $data['zakonczone'] : [];
                }
            else {
                $mecz['typyGraczy'] = null;
                $mecz['podsumowanieTypow'] = null;
                //$mecz['naKoniec'] = null;
            }
        }
    }

    $daneTurniejowe = [
        'tabelaDanych' => $tabelaDanych,
        'turniejID' => $turniejID,
        'userID' => session()->get('loggedInUser')
    ];
    
    $wstep = [
        'title'=> $turniejName
    ];
    

    return view('typowanie/header', $wstep)
           .view('ukladanka/sg/belkausera', ['daneUzytkownika' => $daneUzytkownika])
           .view('ukladanka/sg/znowumecze', [
               'mecze' => $mecze4,
               'turniejID' => $turniejID,
               'userID' => $loggedInUserId,
               'usedGoldenBall' => $daneUzytkownika['usedGoldenBall']
           ])
           .view('ukladanka/sg/SkryptTypowania')
           .view('typowanie/footer');
}

       




    public function archiwum($turniejID=null){

        //$configPath =  WRITEPATH . 'ActiveTournament.json'; // Załóżmy, że to Twoja domyślna lokalizacja
        //$jsonString = file_get_contents($configPath);
        //$config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
        
        //$config = get_active_tournament_config();
            
        if ($turniejID === null) {
            // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
            $turniejID = $this->config['activeTournamentId'];
            $turniejName = $this->config['activeTournamentName'];
            $zewnetrzneIDTurnieju = $this->config['activeCompetitionId'];
            } else {
                $turniejName = "Wit musi zmienić sposób pobierania danych turnieju";
            }

        $loggedInUserId = session()->get('loggedInUser');

        $wstep = [
            'title'=> $turniejName
            ];

        //$userModel = model(UserModel::class);
        $daneUzytkownika = $this->userModel->getGameUserData($loggedInUserId);
        $daneUzytkownika['usedGoldenBall'] = session()->get('usedGoldenBall', 0);

        


        // powinniśmy przekazać wszystkie juz rozegrane mecze turnieju
        $meczeArchiwalne = $this->meczService->meczeUzytkownikaWTurnieju($loggedInUserId, $turniejID, $zewnetrzneIDTurnieju,"rozegrane");


        // Fetch JSON data for each match
foreach ($meczeArchiwalne as &$mecz) {
        $jsonPath = WRITEPATH . "mecze/$turniejID/{$mecz['ApiID']}.json";
        if (file_exists($jsonPath)) {
            $mecz['details'] = json_decode(file_get_contents($jsonPath), true);
        } else {
            $mecz['details'] = null;
        }
        if($mecz['rozpoczety']){
            $jsonPath = WRITEPATH . "typy/{$mecz['Id']}.json";
            if (file_exists($jsonPath)) {
            
            
                $data = json_decode(file_get_contents($jsonPath), true);
                $mecz['typyGraczy'] = isset($data['types']) ? $data['types'] : [];
                $mecz['podsumowanieTypow'] = isset($data['summary']) ? $data['summary'] : [];
                $mecz['naKoniec'] = isset($data['zakonczone']) ? $data['zakonczone'] : [];
                }
            else {
                $mecz['typyGraczy'] = null;
                $mecz['podsumowanieTypow'] = null;
                $mecz['naKoniec'] = null;
            }
        }
    }

        return view('typowanie/header', $wstep)
               .view('ukladanka/sg/belkausera', ['daneUzytkownika'=>$daneUzytkownika])
               .view('ukladanka/sg/zakonczoneMecze', ['mecze' => $meczeArchiwalne,'turniejID'=>$turniejID,'userID'=>$loggedInUserId,               'usedGoldenBall' => $daneUzytkownika['usedGoldenBall']])
               //.view('ukladanka/sg/skryptArchiwum')
               .view('typowanie/footer');

    }



    public function dejCookie(){
        echo "<pre>";
        print_r($_COOKIE);
        echo "</pre>";
    }
    
    public function nowyZapisTypu() {
    //$userUniId = $this->request->getPost('userUID');
    // MA BYĆ:
    $userUniId = session()->get('loggedInUser');

    $gameID = $this->request->getPost('gameID');
    $homeScore = $this->request->getPost('H');
    $awayScore = $this->request->getPost('A');
    $turniejID = $this->request->getPost('turniejID');
    $goldenGame = $this->request->getPost('goldenGame');

    if (!$goldenGame){
        $goldenGame = 0;
    }

    $data = [
        'uniID' => $userUniId,
        'GameID' => $gameID,
        'HomeTyp' => $homeScore,
        'AwayTyp' => $awayScore,
        'TurniejID' => $turniejID,
        'GoldenGame' => $goldenGame
    ];



    $typyModel = model(TypyModel::class);

    // Check if the typ can be saved based on the match time
        if (!$typyModel->canSaveTyp($gameID)) {
          session()->set('sprawdzilem godzine i wszystko gra', 'NIE');
          return $this->response->setJSON(['success' => false, 'message' => 'Nie można zapisać typu, ponieważ jest za późno']);
        } 

        session()->set('Wartość ', $typyModel->canSaveTyp($gameID));


    if ($typyModel->zapiszTyp($data)) {
        $currentGoldenGame = session()->get('usedGoldenBall');
        if ($currentGoldenGame == $gameID && $goldenGame == 0) {
            $typyModel->removeGoldenGame($userUniId, $gameID, $turniejID);
            session()->set('usedGoldenBall', 0);
        } elseif ($goldenGame == 1) {
            session()->set('usedGoldenBall', $gameID);
        }
        //przekazanie maila do kolejki do wysyłki
        (new EmailService())->queueBetSaved($userUniId, (int)$gameID, (string)$homeScore, (string)$awayScore, (int)$goldenGame);

        return $this->response->setJSON(['success' => true, 'message' => 'No i gites! Udało się zapisać dane w bazie', 'newTypText' => "Twój typ: $homeScore:$awayScore"]);
    } else {
        return $this->response->setJSON(['success' => false, 'message' => 'Nie udało się zapisać typu']);
    }
}
    
    
    
    
    
     public function zapiszOdpowiedzNaPytanie()
{
    //$odpowiedzModel = new OdpowiedziModel();

    $validationRules = [
        'odpowiedz' => [
            'rules' => 'required|max_length[255]',
            'errors' => [
                'required' => 'Treść odpowiedzi jest wymagana.',
                'max_length' => 'Treść odpowiedzi nie może przekraczać 255 znaków.'
            ]
        ],
        'pytanieID' => 'required|is_natural_no_zero',
        'uniid' => 'required'
    ];

    if ($this->validate($validationRules)) {
        $data = [
            'idPyt' => $this->request->getPost('pytanieID'),
            'odp' => $this->request->getPost('odpowiedz'),
            'uniidOdp' => $this->request->getPost('uniid'),
            'kiedyModyfikowana' => date('Y-m-d H:i:s'),
        ];

        if ($this->odpowiedzModel->saveAnswer($data)) {
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Wystąpił błąd podczas zapisywania odpowiedzi.']);
        }
    } else {
        return $this->response->setJSON(['status' => 'error', 'message' => 'Wystąpił błąd podczas walidacji odpowiedzi.']);
    }
}

     public function wygenerujOdpowiedziNaPytanie($pytanieID)
    {
        // Inicjalizacja modelu
        //$odpowiedziModel = new OdpowiedziModel();
        
        // Pobierz odpowiedzi na pytanie
        $odpowiedzi = $this->odpowiedzModel->pobierzOdpowiedziNaPytanie($pytanieID);

        // Konwersja danych do formatu JSON
        $jsonData = json_encode($odpowiedzi);

        // Bazowy katalog dla plików JSON
        $baseDir = WRITEPATH . "odpowiedzi";

        // Sprawdź, czy katalog istnieje, a jeśli nie, to go stwórz
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        // Zapisz dane JSON do pliku
        file_put_contents("{$baseDir}/{$pytanieID}.json", $jsonData);
    }
    
    public function archiwumPytan($turniejID = null) {
    //$configPath = WRITEPATH . 'ActiveTournament.json'; 
    //$jsonString = file_get_contents($configPath);
    //$config = json_decode($jsonString, true);
    
    //$config = get_active_tournament_config();
    
    $currentDateTime = new DateTime('now', new DateTimeZone('UTC'));
    $czasDoPorownania = date('Y-m-d H:i:s');
    
    if ($turniejID === null) {
        $turniejID = $this->config['activeTournamentId'];
        $turniejName = $this->config['activeTournamentName'];
        $zewnetrzneIDTurnieju = $this->config['activeCompetitionId'];
    } else {
        $turniejName = "Wit musi zmienić sposób pobierania danych turnieju";
    }

    $loggedInUserId = session()->get('loggedInUser');

    //$userModel = model(UserModel::class);
    $daneUzytkownika = $this->userModel->getGameUserData($loggedInUserId);

    $daneTurniejowe = [
        'turniejID' => $turniejID,
        'userID' => $daneUzytkownika['id'],
    ];
    
    $wstep = [
        'title'=> $turniejName
    ];
    //$pytaniaModel= new PytaniaModel();
    //$odpowiedzModel = new OdpowiedziModel();
    $pytania = $this->pytaniaModel->getQuestionsArchive($turniejID, $czasDoPorownania);

    foreach ($pytania as &$pytanie) {

    $pytanie['liczbaOdpowiedzi'] = $this->odpowiedzModel->liczbaOdpowiedziNaPytanie($pytanie['id']);

    $odpowiedz = $this->odpowiedzModel->where('idPyt', $pytanie['id'])
                                ->where('uniidOdp', $loggedInUserId)
                                ->first();
    if ($odpowiedz) {
        $pytanie['dotychczasowa_odpowiedz'] = $odpowiedz['odp'];
    }
    
    // Konwersja 'wazneDo' na czas lokalny
    if (isset($pytanie['wazneDo'])) {
        $utcDateTime = new DateTime($pytanie['wazneDo'], new DateTimeZone('UTC'));
        $localTimezone = new DateTimeZone('Europe/Warsaw'); // Zastąp 'Europe/Warsaw' swoją strefą czasową
        $utcDateTime->setTimezone($localTimezone);
        $pytanie['wazneDoLocal'] = $utcDateTime->format('Y-m-d H:i:s');
    }

    // Sprawdzenie, czy czas wywołania funkcji jest po wartości 'wazneDo'

    if ($currentDateTime > $utcDateTime) {
        // Ścieżka do pliku JSON
        $jsonFilePath = WRITEPATH . "odpowiedzi/{$pytanie['id']}.json";

        if (file_exists($jsonFilePath)) {
            // Pobierz dane z pliku JSON
            $jsonData = file_get_contents($jsonFilePath);
            $pytanie['odpowiedzi'] = json_decode($jsonData, true);
        } else {
            // Wygeneruj dane i zapisz do pliku JSON
            $this->wygenerujOdpowiedziNaPytanie($pytanie['id']);
            $jsonData = file_get_contents($jsonFilePath);
            $pytanie['odpowiedzi'] = json_decode($jsonData, true);
        }
    }
}
unset($pytanie); // Unset reference

    return view('typowanie/header', $wstep)
           .view('ukladanka/sg/belkausera', ['daneUzytkownika' => $daneUzytkownika])
           .view('ukladanka/sg/starePytania', ['pytania'=>$pytania])
           .view('typowanie/footer');
}


    public function pokazZasady(){
            $data["title"]="Zasady stosowane w typerze";
            $data["turniej"]= $this->config['activeTournamentName'];
            return  view('typowanie/header', $data)
                    . view('typowanie/zasady', $data);
        
    }    

}
?>




