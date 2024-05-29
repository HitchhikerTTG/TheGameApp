<?php


namespace App\Controllers;


use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TerminarzModel;
use App\Models\TypyModel;
use App\Models\TabelaModel;
use App\Midels\KtoWCoGraModel;
$session = \Config\Services::session();

class Typer extends BaseController
{

    protected $_key;
    protected $_secret;

    public $connection = null;
    protected $_baseUrl = "https://livescore-api.com/api-client/";

    public function __construct()
    {
        helper(['url', 'form']);
        $configPath = WRITEPATH . 'ActiveTournament.json';
        $jsonString = file_get_contents($configPath);
        $this->config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
    }

    protected function _buildUrl($endpoint, $params) {
        $params['key'] =  $_ENV['lskey'];
        $params['secret'] = $_ENV['lsscr'];
        return $this->_baseUrl . $endpoint . '?' . http_build_query($params);
    }

public function getLivescores($params = []) {
        $url = $this->_buildUrl('scores/live.json', $params);
        $data = $this->_makeRequest($url);
            foreach ($data['match'] as &$mecz){ // & przy zmiennej oznacza, że edytujesz oryginał a nie kopię tabeli
                $parametry['id']=$mecz['id'];
                $eventurl=$this->_buildUrl('scores/events.json', $parametry);
                $wydarzenia['wydarzenia'] = $this->_makeRequest($eventurl);
                $mecz+=$wydarzenia; // to jest połączenie tabeli związanej z meczem, z tabelą z wydarzeniami
            }
        return $data['match'];
    }

    public function getFixtures($params = []) {
        $url = $this->_buildUrl('fixtures/matches.json', $params);
        $data = $this->_makeRequest($url);
        return $data['fixtures'];
    }

    public function getEvents($params = []){
        $url = $this->_buildUrl('scores/events.json', $params);
        //echo $url;
        $data = $this->_makeRequest($url);
        return $data['event'];
        }

        public function meczNaZywo(int $mecz){
        $parametry_live['fixture_id']=$mecz; //ponieważ interesują mnie tylko mecze turnieju
        $cachedLive = "live_mecz".$mecz;
        if (! $live = cache($cachedLive)){
        
            try { 
                $data['live']=$this->getLivescores($parametry_live);
                //$data['live']=$this->getLivescores();
                $live =view('live/naZywo', $data, ['cache'=>60, 'cache_name'=>$cachedLive]);
                }   
            catch (\Exception $e) {
            return($e->getMessage());
                }
                }
        return $live;
    }



        public function getHTH($params = []){
        $url = $this->_buildUrl('teams/head2head.json', $params);
        //echo $url;
        $data = $this->_makeRequest($url);
        return $data;
        }    

    protected function _makeRequest($url) {
        #$json = $this->_useCache($url);
        $arrContextOptions=array( //this is the workaround i've found. Not sure the smartest way to play, but lets try.
                "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
                ),
                );      
        //file_get_contents("https://domain.tld/path/script.php", false, stream_context_create($arrContextOptions));

        #if ($json) {
        #   $data = json_decode($json, true);
        #} else {
            $json = file_get_contents($url,false, stream_context_create($arrContextOptions));
            $data = json_decode($json, true);

        #   if (!$data['success']) {
        #       throw new RuntimeException($data['error']);
        #   }
        #
        #   $this->_saveCache($url, $json);
        #}

        return $data['data'];
    }


    public function wszystkieMecze(){
    
    $loggedInUserId = session()->get('loggedInUser');
    $userModel = new UserModel();    
    $userInfo = $userModel->find($loggedInUserId);
    $terminarzModel = new TerminarzModel();
    $typyModel = new TypyModel();
    $isUserActiveParticipant = $userInfo['PlaysTheActiveTournament'] ?? false; // 
     
    
    $warunki = $terminarzModel->builder();
        //$warunki->where('Date',date("Y-m-d"));
        $warunki->where('TurniejID',$this->config['activeTournamentId']);

        //print_r($this->config);
        //$warunki->orWhere('CompetitionID','371');
        $mecze=$warunki->get()->getResultArray();

            foreach ($mecze as &$mecz){
            //chce sprawdzić, czy dany użytkownik ma typy dla tego poszczególnych meczów

            $zapytanieOTyp = $typyModel->builder();
            $zapytanieOTyp->where('gameID',$mecz['Id']);
            $zapytanieOTyp->where('userID',$userInfo['id']);
            $typ=$zapytanieOTyp->get()->getResultArray();
            if ($typ) {
            $mecz['typUzytkownikaH']=$typ[0]['HomeTyp'];
            $mecz['typUzytkownikaA']=$typ[0]['AwayTyp'];
 //         $mecz['typUzytkownika'] = $typ;
            }

            }

    $data = [
            'title' => 'Wszystkie mecze Mistrzostw ',
            'userInfo' => $userInfo,
            'isUserActiveParticipant' => $isUserActiveParticipant,

            'mecze'=>$mecze,
            'config'=>$this->config,
            //'wczesniejszeTypy' => $wczesniejszeTypy,
        ];
        return view('typowanie/header',$data)
                . view('typowanie/wszystkieMecze', $data);
    }



    public function theGame ($turniejID=null){
        // W tym miejscu będzie się działo w huk, a ja zaczynam rozumieć, że potrzebuję cholernego cms'a. To nie jest dobry znak. 
        // Ale co chce zrobić... chcę mieć możliwośc wyświetlenia meczów które chce, typów które chce i całej reszty - którą chcę. 
        // potrzebujemy: Nagłówka | Widoku odpowiedzialnego za mecze | Widoku odpowiedzialnego za pytania | Widoku odpowiedzialnego za typerów. Nie zrobię dziś wszystkiego, ale moge próbować. 

        // Bez automatyzacji koniec tego pliku bedize zapewne wyglądać tak:

        // czego potrzebuję:

        $userModel = new UserModel();
        $loggedInUserId = session()->get('loggedInUser');
        // $userInfo = $userModel->find($loggedInUserId); // ZMIEIAM SPOSÓB DOBIERANIA DANYCH UŻYTKOWNIKA
        $userInfo = $userModel->getGameUserData($loggedInUserId);
        $userBuilder = $userModel->builder();
        $userBuilder->where('activated',1);
        $liczbaGraczy=$userBuilder->countAllResults();
        $pomocnicza = model(PomocnicaPiPModel::class);
        $zapytanieOPunkty = $pomocnicza->builder();
        $zapytanieOPunkty->where('UniID', $userInfo['uniID']);
        $ranking = $zapytanieOPunkty->get()->getResultArray();



        $data = [
            'title' => 'Typer '.$this->config['activeTournamentName'],
            'userInfo' => $userInfo,
            'liczbaGraczy' =>$liczbaGraczy,
            'liczbaPkt'=>$ranking['0']['Punkty'],
            //'mecze'=>$mecze,
                //'wczesniejszeTypy' => $wczesniejszeTypy,
        ];


        // TUTAJ TESTOWO PRZYGOTOWUJĘ SOBIE COŚ :) CZYLI NOWY SPOSÓB PREZENTOWANIA TABELI 

            $configPath = WRITEPATH . 'ActiveTournament.json'; // Załóżmy, że to Twoja domyślna lokalizacja
            $jsonString = file_get_contents($configPath);
            $config = json_decode($jsonString, true); // true konwertuje na tablicę asocjacyjną
            
            if ($turniejID === null) {
                // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
                $turniejID = $this->config['activeTournamentId'];
                }

        $model = model(TabelaModel::class);
        $tabelaDanych = $model->gimmeTabelaGraczy($turniejID);

        // Przekazanie danych do widoku
        $daneTurniejowe = [
            'tabelaDanych' => $tabelaDanych,
            //'turniejID' => $turniejID,
            'userID' => session()->get('loggedInUser')
            //'title' => 'Wit pastwi się nad tabelą'
            ];

//    return view('typowanie/header',$data)
//
//           .view('typowanie/footer',$data);

        // co chcę zrobić. Zmodyfikowac funkcję "wyświetl pytanie" żeby dostarczyła mi wszystkich rzeczy, które potrzebyje do odpalenia widoku 

        $pytanie1 = $this->poprawioneWyswietlPytanie(1,$userInfo);
        $pytanie2 = $this->poprawioneWyswietlPytanie(2,$userInfo);
        $pytanie4 = $this->poprawioneWyswietlPytanie(4,$userInfo);
        $pytanie15 = $this->poprawioneWyswietlPytanie(15,$userInfo);

        $pytanie16 = $this->poprawioneWyswietlPytanie(16,$userInfo);
        $pytanie17 = $this->poprawioneWyswietlPytanie(17,$userInfo);
        $pytanie18 = $this->poprawioneWyswietlPytanie(18,$userInfo);
        $pytanie19 = $this->poprawioneWyswietlPytanie(19,$userInfo);
        $pytanie20 = $this->poprawioneWyswietlPytanie(20,$userInfo);
        $pytanie21 = $this->poprawioneWyswietlPytanie(21,$userInfo);
        $pytanie22 = $this->poprawioneWyswietlPytanie(22,$userInfo);
        $pytanie23 = $this->poprawioneWyswietlPytanie(23,$userInfo);
        $pytanie24 = $this->poprawioneWyswietlPytanie(24,$userInfo);

        $mecz49 = $this->wyswietlMeczExpanded(49,$userInfo);
        $mecz50 = $this->wyswietlMeczExpanded(50,$userInfo);
        $mecz51 = $this->wyswietlMeczExpanded(51,$userInfo);
        $mecz52 = $this->wyswietlMeczExpanded(52,$userInfo);
        $mecz53 = $this->wyswietlMeczExpanded(53,$userInfo);
        $mecz54 = $this->wyswietlMeczExpanded(54,$userInfo);
        $mecz55 = $this->wyswietlMeczExpanded(55,$userInfo);
        $mecz56 = $this->wyswietlMeczExpanded(56,$userInfo);
        $mecz57 = $this->wyswietlMeczExpanded(57,$userInfo);
        $mecz58 = $this->wyswietlMeczExpanded(58,$userInfo);
        $mecz59 = $this->wyswietlMeczExpanded(59,$userInfo);
        $mecz60 = $this->wyswietlMeczExpanded(60,$userInfo);
        $mecz61 = $this->wyswietlMeczExpanded(61,$userInfo);
        $mecz62 = $this->wyswietlMeczExpanded(62,$userInfo);
        $mecz63 = $this->wyswietlMeczExpanded(63,$userInfo);
        $mecz64 = $this->wyswietlMeczExpanded(64,$userInfo);
        $mecz101 = $this->wyswietlMeczExpanded(101,$userInfo);
        return  view('typowanie/header',$data)
                .view('typowanie/wstepniak',$data)
                .view('typowanie/expandMecz',$mecz101)
                .view('typowanie/expandMecz',$mecz64)
                .view('typowanie/pytanieUpd',$pytanie24)

                // .$this->preMecz(1527782,1443,1432)
                // .$this->preMecz(1527788,1442,1455)
                // .$this->preMecz(1529606,1439,1440)                
                // .$this->preMecz(1529373,1849,2685)
                // .view('typowanie/mecz',$mecz2)



                //.$this->tabelaGraczy()
                .view('tabela/tabela.php', $daneTurniejowe)
                .view('typowanie/przerywnik',$data)
                .view('typowanie/expandMecz',$mecz63)

                .view('typowanie/expandMecz',$mecz61)
                .view('typowanie/expandMecz',$mecz62)

                .view('typowanie/expandMecz',$mecz57)
                .view('typowanie/expandMecz',$mecz58)
                .view('typowanie/expandMecz',$mecz59)
                .view('typowanie/expandMecz',$mecz60)

                .view('typowanie/expandMecz',$mecz49)
                .view('typowanie/expandMecz',$mecz50)
                .view('typowanie/expandMecz',$mecz51)
                .view('typowanie/expandMecz',$mecz52)
                .view('typowanie/expandMecz',$mecz53)
                .view('typowanie/expandMecz',$mecz54)
                .view('typowanie/expandMecz',$mecz55)
                .view('typowanie/expandMecz',$mecz56)


                .view('typowanie/wczesniejsze',$data)

                .view('typowanie/pytanieUpd',$pytanie1)
                .view('typowanie/pytanieUpd',$pytanie2)
                .view('typowanie/pytanieUpd',$pytanie4)
                .view('typowanie/pytanieUpd',$pytanie15)   
                .view('typowanie/pytanieUpd',$pytanie16)                
                .view('typowanie/pytanieUpd',$pytanie17)
                .view('typowanie/pytanieUpd',$pytanie18) 
                .view('typowanie/pytanieUpd',$pytanie19)
                .view('typowanie/pytanieUpd',$pytanie20)
                .view('typowanie/pytanieUpd',$pytanie21)
                .view('typowanie/pytanieUpd',$pytanie22)
                .view('typowanie/pytanieUpd',$pytanie23)

                

                //.$this->preMecz(1527774,1460,1649)

                /*.view('typowanie/footer',$data)*/
                .view('typowanie/skrypty');

    }

    public function fazaGrupowa(){
        $userModel = new UserModel();
        $loggedInUserId = session()->get('loggedInUser');
        $userInfo = $userModel->find($loggedInUserId);
        $userBuilder = $userModel->builder();
        $userBuilder->where('activated',1);
        $liczbaGraczy=$userBuilder->countAllResults();
        $pomocnicza = model(PomocnicaPiPModel::class);
        $zapytanieOPunkty = $pomocnicza->builder();
        $zapytanieOPunkty->where('UniID', $userInfo['uniID']);
        $ranking = $zapytanieOPunkty->get()->getResultArray();



        $data = [
            'title' => 'Wyniki fazy grupowej Mistrzostw Świata Katarze',
            'userInfo' => $userInfo,
            'liczbaGraczy' =>$liczbaGraczy,
            'liczbaPkt'=>$ranking['0']['Punkty'],
            //'mecze'=>$mecze,
                //'wczesniejszeTypy' => $wczesniejszeTypy,
        ];

        // co chcę zrobić. Zmodyfikowac funkcję "wyświetl pytanie" żeby dostarczyła mi wszystkich rzeczy, które potrzebyje do odpalenia widoku 

        $pytanie1 = $this->poprawioneWyswietlPytanie(1,$userInfo);
        $pytanie2 = $this->poprawioneWyswietlPytanie(2,$userInfo);
        $pytanie3 = $this->poprawioneWyswietlPytanie(3,$userInfo);
        $pytanie4 = $this->poprawioneWyswietlPytanie(4,$userInfo);
        $pytanie5 = $this->poprawioneWyswietlPytanie(5,$userInfo);
        $pytanie6 = $this->poprawioneWyswietlPytanie(6,$userInfo);
        $pytanie7 = $this->poprawioneWyswietlPytanie(7,$userInfo);
        $pytanie8 = $this->poprawioneWyswietlPytanie(8,$userInfo);
        $pytanie9 = $this->poprawioneWyswietlPytanie(9,$userInfo);
        $pytanie10 = $this->poprawioneWyswietlPytanie(10,$userInfo);
        $pytanie11 = $this->poprawioneWyswietlPytanie(11,$userInfo);
        $pytanie12 = $this->poprawioneWyswietlPytanie(12,$userInfo);
        $pytanie13 = $this->poprawioneWyswietlPytanie(13,$userInfo);
        $pytanie14 = $this->poprawioneWyswietlPytanie(14,$userInfo);
        $pytanie15 = $this->poprawioneWyswietlPytanie(15,$userInfo);

        $mecz1 = $this->wyswietlMeczExpanded(1,$userInfo);
        $mecz2 = $this->wyswietlMeczExpanded(2,$userInfo);
        $mecz3 = $this->wyswietlMeczExpanded(3,$userInfo);
        $mecz4 = $this->wyswietlMeczExpanded(4,$userInfo);
        $mecz5 = $this->wyswietlMeczExpanded(5,$userInfo);
        $mecz6 = $this->wyswietlMeczExpanded(6,$userInfo);
        $mecz7 = $this->wyswietlMeczExpanded(7,$userInfo);
        $mecz8 = $this->wyswietlMeczExpanded(8,$userInfo);
        $mecz9 = $this->wyswietlMeczExpanded(9,$userInfo);
      $mecz10 = $this->wyswietlMeczExpanded(10,$userInfo);
      $mecz11 = $this->wyswietlMeczExpanded(11,$userInfo);
      $mecz12 = $this->wyswietlMeczExpanded(12,$userInfo);
      $mecz13 = $this->wyswietlMeczExpanded(13,$userInfo);
      $mecz14 = $this->wyswietlMeczExpanded(14,$userInfo);
      $mecz15 = $this->wyswietlMeczExpanded(15,$userInfo);
      $mecz16 = $this->wyswietlMeczExpanded(16,$userInfo);
      $mecz17 = $this->wyswietlMeczExpanded(17,$userInfo);
      $mecz18 = $this->wyswietlMeczExpanded(18,$userInfo);
      $mecz19 = $this->wyswietlMeczExpanded(19,$userInfo);
      $mecz20 = $this->wyswietlMeczExpanded(20,$userInfo);
      $mecz21 = $this->wyswietlMeczExpanded(21,$userInfo);
      $mecz22 = $this->wyswietlMeczExpanded(22,$userInfo);
      $mecz23 = $this->wyswietlMeczExpanded(23,$userInfo);
      $mecz24 = $this->wyswietlMeczExpanded(24,$userInfo);
      $mecz25 = $this->wyswietlMeczExpanded(25,$userInfo);
      $mecz26 = $this->wyswietlMeczExpanded(26,$userInfo);
      $mecz27 = $this->wyswietlMeczExpanded(27,$userInfo);
      $mecz28 = $this->wyswietlMeczExpanded(28,$userInfo);
      $mecz29 = $this->wyswietlMeczExpanded(29,$userInfo);
      $mecz30 = $this->wyswietlMeczExpanded(30,$userInfo);
      $mecz31 = $this->wyswietlMeczExpanded(31,$userInfo);
      $mecz32 = $this->wyswietlMeczExpanded(32,$userInfo);
      $mecz33 = $this->wyswietlMeczExpanded(33,$userInfo);
      $mecz34 = $this->wyswietlMeczExpanded(34,$userInfo);
      $mecz35 = $this->wyswietlMeczExpanded(35,$userInfo);
      $mecz36 = $this->wyswietlMeczExpanded(36,$userInfo);
      $mecz37 = $this->wyswietlMeczExpanded(37,$userInfo);
      $mecz38 = $this->wyswietlMeczExpanded(38,$userInfo);
      $mecz39 = $this->wyswietlMeczExpanded(39,$userInfo);
      $mecz40 = $this->wyswietlMeczExpanded(40,$userInfo);
      $mecz41 = $this->wyswietlMeczExpanded(41,$userInfo);
      $mecz42 = $this->wyswietlMeczExpanded(42,$userInfo);
      $mecz43 = $this->wyswietlMeczExpanded(43,$userInfo);
      $mecz44 = $this->wyswietlMeczExpanded(44,$userInfo);
      $mecz45 = $this->wyswietlMeczExpanded(45,$userInfo);
      $mecz46 = $this->wyswietlMeczExpanded(46,$userInfo);
      $mecz47 = $this->wyswietlMeczExpanded(47,$userInfo);
      $mecz48 = $this->wyswietlMeczExpanded(48,$userInfo);



        return  view('typowanie/header',$data)
                .view('typowanie/wstepniak_grupowa',$data)
                
                .view('typowanie/expandMecz',$mecz1)
                .view('typowanie/expandMecz',$mecz2)
                .view('typowanie/expandMecz',$mecz3)
                .view('typowanie/expandMecz',$mecz4)
                .view('typowanie/expandMecz',$mecz5)
                .view('typowanie/expandMecz',$mecz6)
                .view('typowanie/expandMecz',$mecz7)
                .view('typowanie/expandMecz',$mecz8)
                .view('typowanie/expandMecz',$mecz9)
                .view('typowanie/expandMecz',$mecz10)
                .view('typowanie/expandMecz',$mecz11)
                .view('typowanie/expandMecz',$mecz12)
                .view('typowanie/expandMecz',$mecz13)
                .view('typowanie/expandMecz',$mecz14)
                .view('typowanie/expandMecz',$mecz15)
                .view('typowanie/expandMecz',$mecz16)
                .view('typowanie/expandMecz',$mecz17)
                .view('typowanie/expandMecz',$mecz18)
                .view('typowanie/expandMecz',$mecz19)
                .view('typowanie/expandMecz',$mecz20)
                .view('typowanie/expandMecz',$mecz21)
                .view('typowanie/expandMecz',$mecz22)
                .view('typowanie/expandMecz',$mecz23)
                .view('typowanie/expandMecz',$mecz24)
                .view('typowanie/expandMecz',$mecz25)
                .view('typowanie/expandMecz',$mecz26)
                .view('typowanie/expandMecz',$mecz27)
                .view('typowanie/expandMecz',$mecz28)
                .view('typowanie/expandMecz',$mecz29)
                .view('typowanie/expandMecz',$mecz30)
                .view('typowanie/expandMecz',$mecz31)
                .view('typowanie/expandMecz',$mecz32)
                .view('typowanie/expandMecz',$mecz33)
                .view('typowanie/expandMecz',$mecz34)
                .view('typowanie/expandMecz',$mecz35)
                .view('typowanie/expandMecz',$mecz36)                
                .view('typowanie/expandMecz',$mecz37)
                .view('typowanie/expandMecz',$mecz38)                                
                .view('typowanie/expandMecz',$mecz39)
                .view('typowanie/expandMecz',$mecz40)
                .view('typowanie/expandMecz',$mecz41)
                .view('typowanie/expandMecz',$mecz42)
                .view('typowanie/expandMecz',$mecz43)
                .view('typowanie/expandMecz',$mecz44)
                .view('typowanie/expandMecz',$mecz45)
                .view('typowanie/expandMecz',$mecz46)
                .view('typowanie/expandMecz',$mecz47)
                .view('typowanie/expandMecz',$mecz48)

                
                .view('typowanie/przerywnik_grupowa',$data)
                .view('typowanie/pytanieUpd',$pytanie3)
                .view('typowanie/pytanieUpd',$pytanie4)
                .view('typowanie/pytanieUpd',$pytanie5)
                .view('typowanie/pytanieUpd',$pytanie6)
                .view('typowanie/pytanieUpd',$pytanie7)
                .view('typowanie/pytanieUpd',$pytanie8)
                .view('typowanie/pytanieUpd',$pytanie9)
                .view('typowanie/pytanieUpd',$pytanie10) 
                .view('typowanie/pytanieUpd',$pytanie11)
                .view('typowanie/pytanieUpd',$pytanie12)
                .view('typowanie/pytanieUpd',$pytanie13)
                .view('typowanie/pytanieUpd',$pytanie14)


                                
                //.$this->preMecz(1527774,1460,1649)

                /*.view('typowanie/footer',$data)*/
                .view('typowanie/skrypty');
    }




    public function index()
    {

        $userModel = new UserModel();
        $terminarzModel = new TerminarzModel();
        $typyModel = new TypyModel();
        $loggedInUserId = session()->get('loggedInUser');
        $userInfo = $userModel->find($loggedInUserId);
        $warunki = $terminarzModel->builder();
            //$warunki->where('Date',date("Y-m-d"));
        $warunki->where('CompetitionID','362');
        $warunki->orWhere('CompetitionID','371');
        $mecze=$warunki->get()->getResultArray();
        //$wczesniejszeTypy = [];
        foreach ($mecze as &$mecz){
            //chce sprawdzić, czy dany użytkownik ma typy dla tego poszczególnych meczów

            $zapytanieOTyp = $typyModel->builder();
            $zapytanieOTyp->where('gameID',$mecz['Id']);
            $zapytanieOTyp->where('userID',$userInfo['id']);
            $typ=$zapytanieOTyp->get()->getResultArray();
            if ($typ) {
            $mecz['typUzytkownikaH']=$typ[0]['HomeTyp'];
            $mecz['typUzytkownikaA']=$typ[0]['AwayTyp'];
 //         $mecz['typUzytkownika'] = $typ;
            }

        }




        
        
            //$zapytanieTypy = $typyModel->builder();
            //$zapytanieTypy->where('UserID',$userInfo['id']);


            //$wczesniejszeTypy = $zapytanieTypy->get()->getResultArray();
            
            $data = [
            'title' => 'Typer MŚ w Katarze',
            'userInfo' => $userInfo,
                'mecze'=>$mecze,
                //'wczesniejszeTypy' => $wczesniejszeTypy,
            ];

            return view('typowanie/header', $data)
                .view('typowanie/index', $data);
    }


    //Funkcja której rolą bedzie zapisanie / zaktualizowanie typów w tabeli typów


    public function zapiszTypMeczu(){

            /*
            * Potrzebuję wiedzieć - kto, jaki mecz, ile dla gości, ile dla gospodarzy, kiedy
            *
            *
            */


            $userID = $this->request->getPost('userID');
            $gameID = $this->request->getPost('gameID');
            $homeScore = $this->request->getPost('H');
            $awayScore = $this->request->getPost('A');
            $turniejID = $this->request->getPost('turniejID');

            $data = [

                'UserID' => $userID,
                'GameID' => $gameID,
                'HomeTyp' => $homeScore,
                'AwayTyp' => $awayScore,
                'TurniejID' => $turniejID

            ];

            // Zanim zaczniemy majstrować, spróbujmy sprawdzić, czy czas skłądania typu nie jest > czas startu meczu. Próba:
            $terminarzModel = new \App\Models\TerminarzModel();
            $meczInfo = $terminarzModel->getMeczById($gameID);

            // Konwersja czasu z UTC do lokalnej strefy czasowej serwera
            $utcTime = new \DateTime($meczInfo['Date'] . ' ' . $meczInfo['Time'], new \DateTimeZone('UTC'));
            $localTime = clone $utcTime;
            $localTime->setTimeZone(new \DateTimeZone(date_default_timezone_get()));


            // Sprawdzenie, czy aktualny czas serwera jest przed lokalnym czasem rozpoczęcia meczu
            $currentTime = new \DateTime('now');
            if ($currentTime >= $localTime) {
                return $this->response->setJSON(['success' => false, 'message' => 'Za późno, Twój typ nie został zmieniony']);
            }


            //To teraz jak juz wiem kto, jaki mecz, to teraz trzeba to zapisać. 
            //Ale najpierw - sprawdźmy, czy mamy już tę informację w bazie danych. Jeśli mamy, wtedy bedziemy potrzebować


            $typyModel = new \App\Models\TypyModel();
            //$primaryKey = 'Id';
           $warunki = $typyModel->builder();
                //$warunki->where('Date',date("Y-m-d"));
                $warunki->where('userID',$userID);
                $warunki->Where('gameID',$gameID);
                $czyJestTenTyp=$warunki->get()->getResultArray();




            if ($czyJestTenTyp) {
                
                $aktualizowanyID=$czyJestTenTyp['0']['Id'];
                $query = $typyModel->update($aktualizowanyID,$data); 
            } else {
                $query = $typyModel->save($data);        
            } 
            
            


/*            if (!$query) {
                return redirect()->back()->with('fail', 'Nie udalo sie zapisac uzytkownika');
                }

                else {
                return redirect()->back()->with('success', 'No i gites! Udało się zapisać dane w bazie');
                }
*/

            if (!$query) {
               return $this->response->setJSON(['success' => false, 'message' => 'Nie udało się zapisać typu']);
                } else {
                return $this->response->setJSON(['success' => true, 'message' => 'No i gites! Udało się zapisać dane w bazie', 'newTypText' => "Twój typ: $homeScore:$awayScore"]);
                }

    }

    public function zapiszTypy(){
            $typyModel = new \App\Models\TypyModel();
            $gameCount = $this->request->getPost('gamesCount');
            $userID = $this->request->getPost('userID');
            $turniejID = $this->request->getPost('TurniejID');
            $teraz=date("Y-m-d H:i:s");
        //    echo "Do przerobienia jest ".$gameCount." gier";

            if ($this->request->getMethod() === 'post') {
               $mecze = $this->request->getPost('mecze'); // Pobranie tablicy meczy
                foreach ($mecze as $idMeczu => $wyniki) {
                    $typH = $wyniki['H']; // Wynik gospodarzy
                    $typA = $wyniki['A']; // Wynik gości

                    // Tutaj logika zapisywania do bazy danych...
                    //echo "Mecz: $idMeczu, Wynik gospodarzy: $typH, Wynik gości: $typA<br>";
                /*log_message('info', 'Próbuję zaktualizować/zapisać typ: ' . print_r([
                'TurniejID' => $this->request->getPost('TurniejID'),
                'ID meczu'=>$idMeczu,
                'TypH' => $typH,
                'TypA' => $typA
                ], true));*/

                if (is_numeric($typH)&&is_numeric($typA)) {
                
                    //echo "<p>I'm spinning, i'm spinning </p>";   
                    $data=[
                    'UserID' => $userID,
                    'GameID' => $idMeczu,
                    'HomeTyp' => $typH,
                    'AwayTyp' => $typA,
                    'ModifiedAt' =>$teraz,
                    'TurniejID' =>$turniejID
                    ];
                    log_message('info', 'Próbuję zaktualizować/zapisać typ: ' . print_r($data, true));
                    //$primaryKey = 'Id';
                    $warunki = $typyModel->builder();
                    //$warunki->where('Date',date("Y-m-d"));
                    $warunki->where('userID',$userID);
                    $warunki->Where('gameID',$idMeczu);
                    $czyJestTenTyp=$warunki->get()->getResultArray();
                    
                    echo "<pre>";
                    print_r($czyJestTenTyp);
                    echo "</pre>";
                    
                         if ($czyJestTenTyp) {
                                $aktualizowanyID=$czyJestTenTyp['0']['Id'];
                                $typyModel->update($aktualizowanyID,$data); 
                                } else  {
                                        $typyModel->save($data);        
                                         } 

                }



                    }
            }


            for ($i = 1; $i <= $gameCount; $i++) {         
                $homeTypIndex=$i."_H";
                $awayTypIndex=$i."_A";
                $typH = $this->request->getPost($homeTypIndex);
                $typA = $this->request->getPost($awayTypIndex);
                
                 


                }
    session()->setFlashData('success', 'Wedle mojej wiedzy, zapisane zostały wszystkie Twoje typy.');
    return redirect()->to('/wszystkieMecze'); 
}

    public function komentarz(){
        $data["title"]="Komentarz od autorski a zarazem coś na kształt dziennika";
        return view('typowanie/komentarz', $data);
        
    }

    public function pokazZasady(){
            $data["title"]="Zasady stosowane w typerze";
            return  view('typowanie/header', $data)
                    . view('typowanie/zasady', $data);
        
    }    

    public function poprawioneWyswietlPytanie(int $parametr, $przekazanyuser){

        $pytanie = model(PytaniaModel::class);
        $OdpowiedzModel = model(OdpowiedziModel::class);
        
        $tresc = $pytanie->where(['id' => $parametr])->first();

        $odpowiedziNaPytanie =$this->pokazOdpowiedziGraczy($parametr);

        $data=[
            'pytanie' => $tresc,
            'title' => 'Wszystkie mecze Mistrzostw ',
            'userInfo' => $przekazanyuser,
            'odpowiedzi'=>$odpowiedziNaPytanie['tabelaOdpowiedzi'],
            'licznikOdpowiedzi' => $odpowiedziNaPytanie['licznikOdpowiedzi'],
        ];


        //Sprawdzam, czy klient na to pytanie już udzielił odpowiedzi?
        $warunki = $OdpowiedzModel->builder();
        //$warunki->where('Date',date("Y-m-d"));
        $warunki->where('uniidOdp',$przekazanyuser['uniID']);
        $warunki->Where('idPyt',$parametr);
        $czyJestOdpowiedz=$warunki->get()->getResultArray();        

        if($czyJestOdpowiedz) {
            //jeśli jest odpowiedź, dopisz ją do formularza i przekaż informację o tym, jakie było jej ID, żeby móc ją zapisać
            $data['dotychczasowa_odpowiedz'] = $czyJestOdpowiedz[0]['odp']; 
            $data['idDotychczasowej_odpowiedzi'] = $czyJestOdpowiedz[0]['id'];
        }

        return $data;

    }

    

    public function wyswietlMecz(int $przekazanymecz, $przekazanyuser){

        $terminarzModel = new TerminarzModel();
        $typyModel = new TypyModel();
        
        // nie wiem czym jest mecz, a z kolei mecz to jest dana z terminarza. Kwila
        $mecz = $terminarzModel->where(['id' => $przekazanymecz])->first();


        $typyDlaMeczu = $this->pokazTypyGraczy($przekazanymecz);


        //Czy użytkownik ma już typ dla meczu $przekazanymecz
 
            $zapytanieOTyp = $typyModel->builder();
            $zapytanieOTyp->where('gameID',$przekazanymecz);
            $zapytanieOTyp->where('userID',$przekazanyuser['id']);
            $typ=$zapytanieOTyp->get()->getResultArray();
            if ($typ) {
            $mecz['typUzytkownikaH']=$typ[0]['HomeTyp'];
            $mecz['typUzytkownikaA']=$typ[0]['AwayTyp'];
 //         $mecz['typUzytkownika'] = $typ;
            }
        
            //$zapytanieTypy = $typyModel->builder();
            //$zapytanieTypy->where('UserID',$userInfo['id']);

            //$wczesniejszeTypy = $zapytanieTypy->get()->getResultArray();
            


        $data = [
            'title' => 'Typer MŚ w Katarze',
            'userInfo' => $przekazanyuser,
            'mecz'=>$mecz,
            'typyGraczy'=>$typyDlaMeczu['tabelaTypowDlaMeczu'],
            'licznikTypow'=>$typyDlaMeczu['licznikTypow'],
            //'wczesniejszeTypy' => $wczesniejszeTypy,
        ];

        return $data;

    }

        public function wyswietlMeczExpanded($przekazanymecz=1, $przekazanyuser){

        //$userModel = new UserModel();
        $terminarzModel = new TerminarzModel();
        $typyModel = new TypyModel();
        //$loggedInUserId = session()->get('loggedInUser');
        //$przekazanyuser = $userModel->find($loggedInUserId);;
        // nie wiem czym jest mecz, a z kolei mecz to jest dana z terminarza. Kwila
        $mecz = $terminarzModel->where(['id' => $przekazanymecz])->first();

        $typyDlaMeczu = $this->pokazTypyGraczy($przekazanymecz);

        //Czy użytkownik ma już typ dla meczu $przekazanymecz
 
            $zapytanieOTyp = $typyModel->builder();
            $zapytanieOTyp->where('GameID',$przekazanymecz);
            $zapytanieOTyp->where('UserID',$przekazanyuser['id']);
            $typ=$zapytanieOTyp->get()->getResultArray();
            if ($typ) {
            $mecz['typUzytkownikaH']=$typ[0]['HomeTyp'];
            $mecz['typUzytkownikaA']=$typ[0]['AwayTyp'];

 //         $mecz['typUzytkownika'] = $typ;
            }

// upewnij się kiedy dane ściagasz, czyli nie targaj ich niepotrzebnie. Nie targaj ich przed meczem i kiedy zaznaczone jako zakończony.

            if (date("Y-m-d H:i")>date("Y-m-d H:i",strtotime($mecz['Date'].$mecz['Time'].'UTC'))and$mecz['zakonczony']==0) {

            try {
                $daneLive=$this->meczNaZywo($mecz['ApiID']);
                } 
            catch (\Exception $e) {
                $daneLive=[
                    'error'=>1,
                    'komunikat'=>"Niestety, nie udało się pobrać danych z live",
                ];
                }
            }

                else {
                    $daneLive=[
                    'komunikat'=>"Wszystko gra, ale ten mecz już się skończył",
                ];
                    }

            $preMecz= $this->preMecz($mecz['ApiID'],$mecz['HomeID'],$mecz['AwayID']);
            //$zapytanieTypy = $typyModel->builder();
            //$zapytanieTypy->where('UserID',$userInfo['id']);

            //$wczesniejszeTypy = $zapytanieTypy->get()->getResultArray();
            
        $data = [
            'title' => 'Typer MŚ w Katarze',
            'userInfo' => $przekazanyuser,
            'mecz'=>$mecz,
            'typyGraczy'=>$typyDlaMeczu['tabelaTypowDlaMeczu'],
            'licznikTypow'=>$typyDlaMeczu['licznikTypow'],
            'daneLive'=>$daneLive,
            'preMecz'=>$preMecz,


                //'wczesniejszeTypy' => $wcjn,zesniejszeTypy,
        ];

        // w tym miejscu będe chciał wczytać scache'owaną wartość z tego meczu 


        return  $data;
                //view('typowanie/header',$data)
                //. view('typowanie/expandMecz', $data);
                //. $this->preMecz($mecz['ApiID'],$mecz['HomeID'],$mecz['AwayID']);
    }

    public function preMecz($mecz,$druzyna1,$druzyna2){  
        $cashedPreMecz = "PreMecz_";
        $cashedPreMecz.=$mecz;
        $parametry=[
            'team1_id'=>$druzyna1,
            'team2_id'=>$druzyna2,
        ];
    
        if (!$preMecz = cache($cashedPreMecz)){
            $data['h2h'] = $this->getHTH($parametry);

            $preMecz = view('live/preMecz',$data,['cache'=>36000,'cache_name'=>$cashedPreMecz]);
        }

        return $preMecz;

    }

    public function wyswietlPytanie(int $parametr) {
        //potrzebuję wiedzieć które pytanie wyświetlić

        if (empty($parametr)) {
            echo "Coś poszło nie teges";
        } else {
         $pytanie = model(PytaniaModel::class);
         $tresc = $pytanie->where(['id' => $parametr])->first();
         /*$loggedInUserId = session()->get('loggedInUser');
         $userModel = new UserModel();    
         $userInfo = $userModel->find($loggedInUserId);*/
   

        $data=[

            'pytanie' => $tresc,
            'title' => 'Wszystkie mecze Mistrzostw ',
            'userInfo' => $userInfo
        ];


        //Sprawdzam, czy klient na to pytanie już udzielił odpowiedzi?
         $OdpowiedzModel = model(OdpowiedziModel::class);
        $warunki = $OdpowiedzModel->builder();
        //$warunki->where('Date',date("Y-m-d"));
        $warunki->where('uniidOdp',$userInfo['uniID']);
        $warunki->Where('idPyt',$parametr);
        $czyJestOdpowiedz=$warunki->get()->getResultArray();        

        if($czyJestOdpowiedz) {
            //jeśli jest odpowiedź, dopisz ją do formularza i przekaż informację o tym, jakie było jej ID, żeby móc ją zapisać
            $data['dotychczasowa_odpowiedz'] = $czyJestOdpowiedz[0]['odp']; 
            $data['idDotychczasowej_odpowiedzi'] = $czyJestOdpowiedz[0]['id'];
        }

     


         return /*view('typowanie/header', $data)
                . */ view('typowanie/pytanie', $data);

        }

    }

    public function zapiszOdpowiedzNaPytanie(){
        //echo "Na razie tylko tak sobie zartuję";
        $odpowiedz = model(OdpowiedziModel::class);

        if ($this->request->getMethod() === 'post' && $this->validate([
            'odpowiedz' => 'required|max_length[255]',
        ])) {


            //wracamy do sytuacji, w której musimy rozkminić, czy ta odpowiedź jest aktualizowana czy nie 

            $OdpowiedzDoZapisu = [
                'idPyt' => $this->request->getPost('pytanieID'),
                'odp' => $this->request->getPost('odpowiedz'),
                'uniidOdp'  => $this->request->getPost('uniid'),
                'kiedyModyfikowana'  => date("Y-m-d H:i:s"),
            ];

            if ($ktoraOdpowiedz = $this->request->getPost('idOdpowiedzi')){
                $OdpowiedzDoZapisu['id']=$ktoraOdpowiedz;
            }

            $odpowiedz->save($OdpowiedzDoZapisu);
            
            session()->setFlashData('succes', 'Twoja odpowiedz została zapisana. Jej!');
            return redirect()->back();
        }

        return redirect()->to('/typowanie');

    }

    public function tabelaGraczy($turniejID=null){
        if ($turniejID === null) {
        // Zakładamy, że funkcja pobierzIDAktywnegoTurnieju() zwraca ID aktywnego turnieju
        $turniejID = $this->config['activeTournamentId'];
        }
        
        
        
        $czasStart=gettimeofday(true);

        $cachedTabela = "TabelaGraczy_".$turniejID;
                $data = [
            'title' => 'Tabela graczy dla turnieju',
        ];
        if (! $tabela = cache($cachedTabela)){



        //to teraz potrzebuję ;] Pobrać listę wszystkich aktywnych graczy
        //dla każdego gracza policzyć liczbę punktów
        //posortować tabelę ze względu na liczbę punktów
        //nie wierzę, ze to mówię, ale kurde chyba sprawdzę co jest szybsze. Gdyż why not. 
        //a potem zróbmy cache'owanie - i jakąś flagę, czy coś się zmieniło, czy nie

        $ktoWCoGraModel = model(KtoWCoGraModel::class); 
#        $uzytkownicy = model(UserModel::class);
         $uzytkownicy = model(UserModel::class);
        $odpowiedz = model(OdpowiedziModel::class);
        $typy = model(TypyModel::class);
        $pomocnicza = model(PomocnicaPiPModel::class);
        
        $uzytkownicyBuilder=$uzytkownicy->builder();
        $uzytkownicyBuilder->where('activated',1);
        $aktywniUzytkownicy=$uzytkownicyBuilder->get()->getResultArray();

        $userIdsInTournament = $ktoWCoGraModel->getUsersOfTournament($turniejID);

        // Teraz masz listę ID użytkowników uczestniczących w turnieju, możesz zrobić kolejne zapytanie
        // do modelu użytkowników (lub innego modelu), aby pobrać szczegółowe informacje o tych użytkownikach.
        // Na przykład:
        $users = model(UserModel::class);

        $aktywniUzytkownicyWTurnieju = [];
            foreach ($userIdsInTournament as $userId) {
                $userInfo = $users->find($userId);
                    if ($userInfo && $userInfo['activated']) {
                    $aktywniUzytkownicyWTurnieju[] = $userInfo;
            }
        }
       #$uzytkownicyBuilder=$uzytkownicy->builder();
       #$uzytkownicyBuilder->where('activated',1);
       #$aktywniUzytkownicy=$ktoWCoGraModel->getUsersOfTournament($turniejID);

        foreach ($aktywniUzytkownicyWTurnieju as $uzytkownik) {

            //na razie na sucho, czyli wypiszemy same proste rzeczy :)
            // potrzebujemy wiedzieć, ile punktów ma dany użytkownik, czyli stworzymy sobie tabele, w której bedzie:
            // nick=>punkty


            $zapytanieOTypy = $typy->builder();
            $zapytanieOTypy->where('UserId', $uzytkownik['id']);
            $zapytanieOTypy->where('TurniejID', $turniejID);
            $zapytanieOTypy->selectSum('pkt');
            $liczbaPktZaTypy = $zapytanieOTypy->get()->getRow()->pkt;

            $zapytanieOPytania = $odpowiedz->builder();
            $zapytanieOPytania->where('uniidOdp', $uzytkownik['uniID']);
            $zapytanieOPytania->where('TurniejID', $turniejID);
            $zapytanieOPytania->selectSum('pkt');
            $liczbaPktZaPytania = $zapytanieOPytania->get()->getRow()->pkt;
            
            $liczbapkt = $liczbaPktZaTypy + $liczbaPktZaPytania;

            $nowapozycja = [
            'nick'=>$uzytkownik['nick'],
            'pkt'=>$liczbapkt,
            ];
 //           $primaryKey = 'UniID';
            $zapiszDoPomocniczej=[
              'UniID'=> $uzytkownik['uniID'],
              'Punkty'=>$liczbapkt,
              'Zmodyfikowane'=>date("Y-m-d H:i:s"), 
            ];

            $pomocnicza->save($zapiszDoPomocniczej);

            $pozycje[]=$nowapozycja;

        }

            $pkt  = array_column($pozycje, 'pkt');
            array_multisort($pkt, SORT_DESC, $pozycje);

            $data['pozycje']=$pozycje;
            $data['cotozatabela']="Ranking generalny turnieju ".$turniejID;            

            $tabela =view('typowanie/tabelaGraczy', $data, ['cache'=>60, 'cache_name'=>$cachedTabela]);
            }
            $czasStop=gettimeofday(true);
            $delta = $czasStop-$czasStart;
            $data['czas']=$delta;

        return  $tabela;
    }

    public function pokazTypyGraczy(int $mecz=1){
        //echo "poka poka";

        $typyModel = model(TypyModel::class);
        $uzytkownicyModel = model(UserModel::class);

        $typyBulider= $typyModel->builder();
        $typyBulider->where('GameID',$mecz);
        $wszystkieTypyDlaMeczu=$typyBulider->get()->getResultArray();

        $uzytkownicyBuilder = $uzytkownicyModel->builder();
        $uzytkownicyBuilder->where('activated','1');
        $wszyscyAktwyniUzytkownicy = $uzytkownicyBuilder->get()->getResultArray();

        $typyHomeByID = array_column($wszystkieTypyDlaMeczu, 'HomeTyp', 'UserID');
        $typyAwayByID = array_column($wszystkieTypyDlaMeczu, 'AwayTyp', 'UserID');
        $punktyByID = array_column($wszystkieTypyDlaMeczu, 'pkt', 'UserID');
        $wszystkieAktwyneNicki = array_column($wszyscyAktwyniUzytkownicy, 'nick', 'id');


        $licznik=0;

        foreach ($wszyscyAktwyniUzytkownicy as $aktywnyUzytkownik) {
            
            $index = $aktywnyUzytkownik['id'];

            if (array_key_exists($index, $typyAwayByID)) {
                $licznik++;
                $tabelaTypowDlaMeczu[$aktywnyUzytkownik['nick']]=[
                    'nick' => $aktywnyUzytkownik['nick'],
                    'HomeTyp'=>$typyHomeByID[$aktywnyUzytkownik['id']],
                    'AwayTyp'=>$typyAwayByID[$aktywnyUzytkownik['id']],
                    'zdobycz'=>$punktyByID[$aktywnyUzytkownik['id']],
                  ];
            } else {
                $tabelaTypowDlaMeczu[$aktywnyUzytkownik['nick']]=[
                'nick'=>$aktywnyUzytkownik['nick'],
                'HomeTyp'=>"-",
                'AwayTyp'=>"-",
                'zdobycz'=>"-",
                  ];
            }


//            echo "<p>".$aktywnyUzytkownik['nick']."( ".$index." )</p>";
/*
            if (array_key_exists($index, $typyAwayByID)) {
                echo "$typyAwayByID[$index]";
            } else {
                echo "brak";
            }

//            echo "<p>".$typyAwayByID[]." )</p>";
 */       }

            ksort($tabelaTypowDlaMeczu);

/*          echo "<pre>";
            print_r($tabelaTypowDlaMeczu);
            echo "</pre>";*/

            $data =[
            'tabelaTypowDlaMeczu'=>$tabelaTypowDlaMeczu,
            'licznikTypow'=>$licznik,

            ];

            return $data;
    }

   public function pokazOdpowiedziGraczy(int $pytanie=1){
        //echo "poka poka";
 
        $odpowiedziModel = model(OdpowiedziModel::class);
        $uzytkownicyModel = model(UserModel::class);

        $odpowiedziBulider= $odpowiedziModel->builder();
        $odpowiedziBulider->where('idPyt',$pytanie);
        $wszystkieOdpowiedziDlaPytania=$odpowiedziBulider->get()->getResultArray();

        $uzytkownicyBuilder = $uzytkownicyModel->builder();
        $uzytkownicyBuilder->where('activated','1');
        $wszyscyAktwyniUzytkownicy = $uzytkownicyBuilder->get()->getResultArray();

        $odpowiedziByID = array_column($wszystkieOdpowiedziDlaPytania, 'odp', 'uniidOdp');
        $wszystkieAktwyneNicki = array_column($wszyscyAktwyniUzytkownicy, 'nick', 'uniID');

/*          echo "<pre>";
            print_r($wszyscyAktwyniUzytkownicy);
            echo "</pre>";
*/      $licznik=0;
        foreach ($wszyscyAktwyniUzytkownicy as $aktywnyUzytkownik) {
            
            $index = $aktywnyUzytkownik['uniID'];
            
            /*echo $index."<br";

            echo "<p>";
            echo $aktywnyUzytkownik['uniID']."--".$aktywnyUzytkownik['nick'];

            echo "</p>"; */

            
            if (array_key_exists($index, $odpowiedziByID)) {
                $tabelaOdpowiedzi[$aktywnyUzytkownik['nick']]=[
                    'nick' => $aktywnyUzytkownik['nick'],
                    'odp'=>$odpowiedziByID[$aktywnyUzytkownik['uniID']],
                  ];
                  $licznik++;
            } else {
                $tabelaOdpowiedzi[$aktywnyUzytkownik['nick']]=[
                    'nick' => $aktywnyUzytkownik['nick'],
                    'odp'=>'-',
                  ];
            }
            
     }

            ksort($tabelaOdpowiedzi);

/*          echo "<pre>";
            print_r($tabelaOdpowiedzi);
            echo "</pre>";
*/
            $data=[
            'tabelaOdpowiedzi'=> $tabelaOdpowiedzi,
            'licznikOdpowiedzi' => $licznik,
            ];

            return $data;
    }


}