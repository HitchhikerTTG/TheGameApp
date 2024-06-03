<?php


namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TerminarzModel;
use App\Models\TypyModel;
use App\Models\TabelaModel;
use App\Models\KtoWCoGraModel;
use App\Services\MeczService;
$session = \Config\Services::session();

class TheGame extends BaseController
{

    //protected $_key;
    //protected $_secret;

    protected $meczService;

    public function __construct()
    {
        $this->meczService = new MeczService();
    }
 

    public function index($turniejID = null){

        $configPath =  WRITEPATH . 'ActiveTournament.json'; // Załóżmy, że to Twoja domyślna lokalizacja
        $jsonString = file_get_contents($configPath);
        $config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
            
        if ($turniejID === null) {
            // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
            $turniejID = $config['activeTournamentId'];
            $turniejName = $config['activeTournamentName'];
            } else {
                $turniejName = "Wit musi zmienić sposób pobierania danych turnieju";
            }

        $loggedInUserId = session()->get('loggedInUser');


        $model = model(TabelaModel::class);
        $tabelaDanych = $model->gimmeTabelaGraczy($turniejID);

        $userModel = model(UserModel::class);
        $daneUzytkownika = $userModel->getGameUserData($loggedInUserId);

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

    public function testIndex($turniejID = null){
        $configPath =  WRITEPATH . 'ActiveTournament.json'; // Załóżmy, że to Twoja domyślna lokalizacja
        $jsonString = file_get_contents($configPath);
        $config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
            
        if ($turniejID === null) {
            // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
            $turniejID = $config['activeTournamentId'];
            $turniejName = $config['activeTournamentName'];
            $zewnetrzneIDTurnieju = $config['activeCompetitionId'];
            } else {
                $turniejName = "Wit musi zmienić sposób pobierania danych turnieju";
            }

        $loggedInUserId = session()->get('loggedInUser');


        $model = model(TabelaModel::class);
        $tabelaDanych = $model->gimmeTabelaGraczy($turniejID);

        $userModel = model(UserModel::class);
        $daneUzytkownika = $userModel->getGameUserData($loggedInUserId);
        $daneUzytkownika['usedGoldenBall'] = session()->get('usedGoldenBall', 0);

        $mecze = $this->meczService->getMeczeDnia($turniejID);
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


        return view('typowanie/header', $wstep)
               .view('ukladanka/sg/belkausera', ['daneUzytkownika'=>$daneUzytkownika])
               .view('ukladanka/sg/znowumecze',['mecze' => $mecze4,'turniejID'=>$turniejID,'userID'=>$loggedInUserId, 'usedGoldenBall'=>$daneUzytkownika['usedGoldenBall']])
               .view('ukladanka/sg/pytania',$pytania)
               .view('ukladanka/sg/chat')
               .view('tabela/tabela', $daneTurniejowe)
               .view('ukladanka/sg/SkryptTypowania')
               .view('typowanie/footer');

        

    }

        public function akordeon($turniejID = null){
        $configPath =  WRITEPATH . 'ActiveTournament.json'; // Załóżmy, że to Twoja domyślna lokalizacja
        $jsonString = file_get_contents($configPath);
        $config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
            
        if ($turniejID === null) {
            // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
            $turniejID = $config['activeTournamentId'];
            $turniejName = $config['activeTournamentName'];
            $zewnetrzneIDTurnieju = $config['activeCompetitionId'];
            } else {
                $turniejName = "Wit musi zmienić sposób pobierania danych turnieju";
            }

        $loggedInUserId = session()->get('loggedInUser');


        $model = model(TabelaModel::class);
        $tabelaDanych = $model->gimmeTabelaGraczy($turniejID);

        $userModel = model(UserModel::class);
        $daneUzytkownika = $userModel->getGameUserData($loggedInUserId);
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

        $userModel = model(UserModel::class);
        $daneUzytkownika = $userModel->getGameUserData($loggedInUserId);

        return view('typowanie/header', $wstep)
               .view('ukladanka/sg/belkausera', ['daneUzytkownika'=>$daneUzytkownika])
               .view('ukladanka/sg/znowumecze',['mecze' => $mecze4,'turniejID'=>$turniejID,'userID'=>$loggedInUserId])               .view('ukladanka/sg/meczenanowo',['mecze' => $mecze4,'turniejID'=>$turniejID,'userID'=>$loggedInUserId])
               .view('ukladanka/sg/jeszczejedenskrypt')
               .view('typowanie/footer');

        

    }


    public function archiwum($turniejID=null){

        $configPath =  WRITEPATH . 'ActiveTournament.json'; // Załóżmy, że to Twoja domyślna lokalizacja
        $jsonString = file_get_contents($configPath);
        $config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
            
        if ($turniejID === null) {
            // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
            $turniejID = $config['activeTournamentId'];
            $turniejName = $config['activeTournamentName'];
            $zewnetrzneIDTurnieju = $config['activeCompetitionId'];
            } else {
                $turniejName = "Wit musi zmienić sposób pobierania danych turnieju";
            }

        $loggedInUserId = session()->get('loggedInUser');

        $wstep = [
            'title'=> $turniejName
            ];

        $userModel = model(UserModel::class);
        $daneUzytkownika = $userModel->getGameUserData($loggedInUserId);

        


        // powinniśmy przekazać wszystkie juz rozegrane mecze turnieju
        $meczeArchiwalne = $this->meczService->meczeUzytkownikaWTurnieju($loggedInUserId, $turniejID, $zewnetrzneIDTurnieju,"rozegrane");


        return view('typowanie/header', $wstep)
               .view('ukladanka/sg/belkausera', ['daneUzytkownika'=>$daneUzytkownika])
               .view('ukladanka/sg/zakonczoneMecze', ['mecze' => $meczeArchiwalne,'turniejID'=>$turniejID,'userID'=>$loggedInUserId])
               .view('ukladanka/sg/skryptArchiwum')
               .view('typowanie/footer');

    }

    // Controller w PHP
    public function wygenerujTypyDlaMeczu($matchId) {
        $typyModel = new \App\Models\TypyModel();
        $types = $typyModel->ktoTypujeTenMecz($matchId);

        $jsonData = json_encode($types);

        // Opcja 1: Zapisz jako plik
        $baseDir = WRITEPATH . "typy/"; // Bazowy katalog dla plików JSON
        file_put_contents("{$baseDir}/{$matchId}.json", $jsonData);

    
    }

    public function dejCookie(){
        echo "<pre>";
        print_r($_COOKIE);
        echo "</pre>";
    }

    public function nowyZapisTypu() {
    $userUniId = $this->request->getPost('userUID');
    $gameID = $this->request->getPost('gameID');
    $homeScore = $this->request->getPost('H');
    $awayScore = $this->request->getPost('A');
    $turniejID = $this->request->getPost('turniejID');
    $goldenGame = $this->request->getPost('goldenGame');

    if (!$goldenGame){
        $goldenGame=0;
    }

    $data = [
        'uniID' => $userUniId,
        'GameID' => $gameID,
        'HomeTyp' => $homeScore,
        'AwayTyp' => $awayScore,
        'TurniejID' => $turniejID,
        'GoldenGame' => $goldenGame
    ];

    // Logowanie odbieranych danych
    log_message('info', 'Odbierane dane: ' . print_r($data, true));


//    $typyModel = new \App\Models\TypyModel();
    $typyModel = model(TypyModel::class);
    if ($typyModel->zapiszTyp($data)) {
        return $this->response->setJSON(['success' => true, 'message' => 'No i gites! Udało się zapisać dane w bazie', 'newTypText' => "Twój typ: $homeScore:$awayScore"]);
    } else {
        return $this->response->setJSON(['success' => false, 'message' => 'Nie udało się zapisać typu']);
    }
}


}
?>