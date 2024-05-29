<?php


namespace App\Controllers;


use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TerminarzModel;
use App\Models\TypyModel;
$session = \Config\Services::session();

class ExperimentalTyper extends BaseController
{

    protected $_key;
    protected $_secret;

    public $connection = null;
    protected $_baseUrl = "https://livescore-api.com/api-client/";

    public function __construct()
    {
        helper(['url', 'form']);
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

    public function getScorers($params = []) {
        $url = $this->_buildUrl('competitions/goalscorers.json', $params);
        $data = $this->_makeRequest($url);
        return $data;
    }



    public function getEvents($params = []){
        $url = $this->_buildUrl('scores/events.json', $params);
        //echo $url;
        $data = $this->_makeRequest($url);
        return $data['event'];
        }

        public function meczNaZywo(int $mecz){
        $parametry_live['competition_id']="362"; //ponieważ interesują mnie tylko mecze turnieju
        $cachedLive = "live_expmecz".$mecz;
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

        public function wyswietlMeczExpanded($przekazanymecz=1, $przekazanyuser){

        $userModel = new UserModel();
        $terminarzModel = new TerminarzModel();
        $typyModel = new TypyModel();
        $loggedInUserId = session()->get('loggedInUser');
        $przekazanyuser = $userModel->find($loggedInUserId);;
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

                //'wczesniejszeTypy' => $wczesniejszeTypy,
        ];

        // w tym miejscu będe chciał wczytać scache'owaną wartość z tego meczu 


        return $data;//view('typowanie/header',$data)
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

    public function coMowiaBookmacherzy(int $mecz=1527795){
        //$typyBookmacherow = "live_mecz".$mecz;
        
        $parametry=[
            'fixture_id'=>$mecz,
        ];

        try {
          $daneZTerminarza=$this->getLivescores($parametry);  
        }
        catch (\Exception $e) {
            return($e->getMessage());
        } 
        echo "<pre>";
        print_r($daneZTerminarza);
        echo "</pre>";

    }



       /* public function meczNaZywo(int $mecz){
        $typyBookmacherow = "live_mecz".$mecz;
        if (! $live = cache($$typyBookmacherow)){
        
            try { 
                $data['live']=$this->getLivescores($parametry_live);
                //$data['live']=$this->getLivescores();
                $live =view('live/naZywo', $data, ['cache'=>60, 'cache_name'=>$typyBookmacherow]);
                }   
            catch (\Exception $e) {
            return($e->getMessage());
                }
                }
        return $live;
    }
    */


public function theGame (){
        //W tym miejscu będzie się działo w huk, a ja zaczynam rozumieć, że potrzebuję cholernego cms'a. To nie jest dobry znak. 
        //Ale co chce zrobić... chcę mieć możliwośc wyświetlenia meczów które chce, typów które chce i całej reszty - którą chcę. 
        // potrzebujemy: Nagłówka | Widoku odpowiedzialnego za mecze | Widoku odpowiedzialnego za pytania | Widoku odpowiedzialnego za typerów. Nie zrobię dziś wszystkiego, ale moge próbować. 

        // Bez automatyzacji koniec tego pliku bedize zapewne wyglądać tak:

        // czego potrzebuję:

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

//        print_r($userInfo);

        


        $data = [
            'title' => 'Typer MŚ w Katarze',
            'userInfo' => $userInfo,
            'liczbaGraczy' =>$liczbaGraczy,
            'liczbaPkt'=>$ranking['0']['Punkty'],
             //'mecze'=>$mecze,
             //'wczesniejszeTypy' => $wczesniejszeTypy,
        ];

        // co chcę zrobić. Zmodyfikowac funkcję "wyświetl pytanie" żeby dostarczyła mi wszystkich rzeczy, które potrzebyje do odpalenia widoku 

//        $pytanie1 = $this->poprawioneWyswietlPytanie(1,$userInfo);
//        $pytanie2 = $this->poprawioneWyswietlPytanie(2,$userInfo);
//        $pytanie3 = $this->poprawioneWyswietlPytanie(3,$userInfo);
//        $pytanie4 = $this->poprawioneWyswietlPytanie(4,$userInfo);
//        $pytanie5 = $this->poprawioneWyswietlPytanie(5,$userInfo);
//        $mecz1 = $this->wyswietlMecz(1,$userInfo);
      $mecz2 = $this->wyswietlMeczExpanded(2,$userInfo);
      $mecz9 = $this->wyswietlMeczExpanded(9,$userInfo);


/*     $mecz3 = $this->wyswietlMecz(3,$userInfo);
      $mecz4 = $this->wyswietlMecz(4,$userInfo);
      $mecz5 = $this->wyswietlMecz(5,$userInfo);
      $mecz6 = $this->wyswietlMecz(6,$userInfo);
      $mecz7 = $this->wyswietlMecz(7,$userInfo);
      $mecz8 = $this->wyswietlMecz(8,$userInfo);
      $mecz9 = $this->wyswietlMecz(9,$userInfo);
      $mecz10 = $this->wyswietlMecz(10,$userInfo);
      $mecz11 = $this->wyswietlMecz(11,$userInfo);
      $mecz12 = $this->wyswietlMecz(12,$userInfo);*/

          return  view('typowanie/header',$data)
      //          .view('typowanie/wstepniak',$data)
 
                //.$this->preMecz(1527782,1443,1432)
                //.view('typowanie/mecz',$mecz9)
                //.view('typowanie/mecz',$mecz10)
                //.view('typowanie/mecz',$mecz11)
                //.view('typowanie/mecz',$mecz12)

//                .$this->preMecz(1527788,1442,1455)
                
//                .$this->preMecz(1529606,1439,1440)                
                
//                .$this->preMecz(1529373,1849,2685)
//              .view('typowanie/mecz',$mecz2)
     //           .view('typowanie/pytanieUpd',$pytanie5)
 //               .$this->tabelaGraczy()
   //             .view('typowanie/przerywnik',$data)
     //           .view('typowanie/mecz',$mecz8)
       //         .view('typowanie/mecz',$mecz7)
         //       .view('typowanie/mecz',$mecz6)

           //     .view('typowanie/mecz',$mecz5)
                  .view('typowanie/expandMecz',$mecz9)
                  .view('typowanie/expandMecz',$mecz2);
        //        .$this->preMecz(1527779,1456,1436)
        //         .view('typowanie/mecz',$mecz3)
        //         .view('typowanie/mecz',$mecz4)
   //             .view('typowanie/pytanieUpd',$pytanie3)
   //             .view('typowanie/pytanieUpd',$pytanie4)
                //.$this->preMecz(1527774,1460,1649)

                //.view('typowanie/footer',$data)
       //         .view('typowanie/skrypty');

    }

    public function mojePunkty(){
        $userModel = new UserModel();
        $terminarzModel = model(TerminarzModel::class);
        $typyModel = model(TypyModel::class);
        $pytaniaModel = model(PytaniaModel::class);
        $odpowiedziModel = model(OdpowiedziModel::class);

        $zapytanieTerminarz = $terminarzModel->builder();
        $zapytaniePytania = $pytaniaModel->builder();
        $zapytanieTypy = $typyModel->builder();
        $zapytanieOdpowiedz= $odpowiedziModel->builder();

        $loggedInUserId = session()->get('loggedInUser');
        $userInfo = $userModel->find($loggedInUserId);

        //Pobierz zakończone mecze z terminarza:
        $zapytanieTerminarz->where('zakonczony',"1");
        $terminarz=$zapytanieTerminarz->get()->getResultArray();

        $zapytanieTypy->where('UserID',$userInfo['id']);
        $typyUzytkownika=$zapytanieTypy->get()->getResultArray();

        $podrecznaTabelaTypow=[];
        foreach ($typyUzytkownika as $typ) {
            $podrecznaTabelaTypow[$typ['GameID']]=[
                'HomeTyp'=>$typ['HomeTyp'],
                'AwayTyp'=>$typ['AwayTyp'],
                'pkt'=>$typ['pkt']
            ];
        }

//        $zapytaniePytania->where('zamkniete',"1");
        $pytania=$zapytaniePytania->get()->getResultArray();

        $zapytanieOdpowiedz->where('uniidOdp',$userInfo['uniID']);
        $odpowiedziUzytkownika=$zapytanieOdpowiedz->get()->getResultArray();

        $podrecznaTabelaOdpowiedzi=[];
        foreach ($odpowiedziUzytkownika as $typ) {
                $podrecznaTabelaOdpowiedzi[$typ['idPyt']]=[
                'odp'=>$typ['odp'],
                'pkt'=>$typ['pkt']
                ];            

        }

        $punktowanieZaMecze=[];

        foreach ($terminarz as $mecz){
         //   echo "<pre>";
         //   print_r($mecz);
         //   echo "</pre>";
//            echo "<p> ".$mecz['HomeName']." - ".$mecz['AwayName']."; Wynik: ".$mecz['ScoreHome']." - ".$mecz['ScoreAway']."; Twój typ: ".$podrecznaTabelaTypow[$mecz['Id']]['HomeTyp'].":".$podrecznaTabelaTypow[$mecz['Id']]['AwayTyp']."; Zdobytych punktów:".$podrecznaTabelaTypow[$mecz['Id']]['pkt']."</p>";

            if (array_key_exists($mecz['Id'],$podrecznaTabelaTypow)){            
                $punktowanieZaMecze[$mecz['Id']]=[
                    'HomeName' => $mecz['HomeName'],
                    'AwayName' => $mecz['AwayName'],
                    'ScoreHome' => $mecz['ScoreHome'],
                    'ScoreAway' => $mecz['ScoreAway'],
                    'typUzytkownikaH'=> $podrecznaTabelaTypow[$mecz['Id']]['HomeTyp'],
                    'typUzytkownikaA'=>$podrecznaTabelaTypow[$mecz['Id']]['AwayTyp'],
                    'pkt'=>$podrecznaTabelaTypow[$mecz['Id']]['pkt']
                ];
               } else {
                $punktowanieZaMecze[$mecz['Id']]=[
                    'HomeName' => $mecz['HomeName'],
                    'AwayName' => $mecz['AwayName'],
                    'ScoreHome' => $mecz['ScoreHome'],
                    'ScoreAway' => $mecz['ScoreAway'],
                    'typUzytkownikaH'=> '-',
                    'typUzytkownikaA'=> '-',
                    'pkt'=>'0'
                ];
               } 

        }
            $punktowanieZaPytania=[];
        foreach ($pytania as $pytanie){
          /*  echo "<pre>";
            print_r($pytanie);
            echo "</pre>";*/

            if (array_key_exists($pytanie['id'],$podrecznaTabelaOdpowiedzi)){
            $punktowanieZaPytania[$pytanie['id']]=[
                'tresc'=>$pytanie['tresc'],
                'zamkniete'=>$pytanie['zamkniete'],
                'odpowiedz'=>$pytanie['odpowiedz'],
                'UserOdp'=>$podrecznaTabelaOdpowiedzi[$pytanie['id']]['odp'],
                'pkt'=>$podrecznaTabelaOdpowiedzi[$pytanie['id']]['pkt'],
                ];
//            echo "<p> ".$pytanie['tresc']."Rozstrzygniete? [1/0]".$pytanie['zamkniete']." Odpowiedz: ".$pytanie['odpowiedz']."; Twoja odpowiedz: ".$podrecznaTabelaOdpowiedzi[$pytanie['id']]['odp']."; Zdobytych punktów:".$podrecznaTabelaOdpowiedzi[$pytanie['id']]['pkt']."</p>";
            } else {
                            $punktowanieZaPytania[$pytanie['id']]=[
                'tresc'=>$pytanie['tresc'],
                'zamkniete'=>$pytanie['zamkniete'],
                'odpowiedz'=>$pytanie['odpowiedz'],
                'UserOdp'=>"Brak odpowiedzi",
                'pkt'=>"0",
                ];
            }
//            echo "<p> ".$pytanie['tresc']."Rozstrzygniete? [1/0]".$pytanie['zamkniete']." Odpowiedz: ".$pytanie['odpowiedz']."; Twoja odpowiedz: Brak; Zdobytych punktów: 0</p>";
            }



        


//        echo "<pre>";
/*        echo "terminarz";
        print_r($terminarz);*/
//                  echo "typy";
//        print_r($punktowanieZaMecze);
//                echo "pytania";
//        print_r($punktowanieZaMecze);
//               echo "odpowiedzi";
//        print_r($odpowiedziUzytkownika);

//        echo "</pre>";
/*        $data=[
            'terminarz'=>$terminarz,
            'typyUzykownika'=>$typyUzytkownika,
        ]; */
        $data=[
            'title'=>"Twoje punkty w typerze",
            'punktowanieZaMecze'=>$punktowanieZaMecze,
            'punktowanieZaPytania'=>$punktowanieZaPytania
        ];
        return view('typowanie/header',$data)
                .view('typowanie/mojePunkty',$data);




    }


    public function pokazStrzelcow($rozgrywki=362){
        $cashedStrzelcy="Strzelcy_";
        $cashedStrzelcy.=$rozgrywki;
        $parametry['competition_id']=$rozgrywki;

        if (!$strzelcy=cache($cashedStrzelcy)){
            $surowkaStrzelcow = $this->getScorers($parametry);
            $data['strzelcy'] = $surowkaStrzelcow['goalscorers'];
            $strzelcy=view('typowanie/strzelcy',$data,['cache'=>36000,'cache_name'=>$cashedStrzelcy]);
        }
        $data['title']="Najlepsi strzelcy turnieju";

        return view('typowanie/header',$data)
                .$strzelcy
                .view('typowanie/footer');
    }


    public function tabelaTylkoMecze(){
        $czasStart=gettimeofday(true);

        $cachedTabela = "TabelaTylkoMecze";
                $data = [
            'title' => 'Taka ładna tabela',
        ];
        if (! $tabela = cache($cachedTabela)){



        //to teraz potrzebuję ;] Pobrać listę wszystkich aktywnych graczy
        //dla każdego gracza policzyć liczbę punktów
        //posortować tabelę ze względu na liczbę punktów
        //nie wierzę, ze to mówię, ale kurde chyba sprawdzę co jest szybsze. Gdyż why not. 
        //a potem zróbmy cache'owanie - i jakąś flagę, czy coś się zmieniło, czy nie

        $uzytkownicy = model(UserModel::class);
        $odpowiedz = model(OdpowiedziModel::class);
        $typy = model(TypyModel::class);
        $pomocnicza = model(PomocnicaPiPModel::class);
        
        $uzytkownicyBuilder=$uzytkownicy->builder();
        $uzytkownicyBuilder->where('activated',1);
        $aktywniUzytkownicy=$uzytkownicyBuilder->get()->getResultArray();

        foreach ($aktywniUzytkownicy as $uzytkownik) {

            //na razie na sucho, czyli wypiszemy same proste rzeczy :)
            // potrzebujemy wiedzieć, ile punktów ma dany użytkownik, czyli stworzymy sobie tabele, w której bedzie:
            // nick=>punkty


            $zapytanieOTypy = $typy->builder();
            $zapytanieOTypy->where('UserId', $uzytkownik['id']);
            $zapytanieOTypy->selectSum('pkt');
            $liczbaPktZaTypy = $zapytanieOTypy->get()->getRow()->pkt;




            $zapytanieOPytania = $odpowiedz->builder();
            $zapytanieOPytania->where('uniidOdp', $uzytkownik['uniID']);
            $zapytanieOPytania->selectSum('pkt');
            $liczbaPktZaPytania = $zapytanieOPytania->get()->getRow()->pkt;
            
            //$liczbapkt = $liczbaPktZaTypy + $liczbaPktZaPytania;
            $liczbapkt = $liczbaPktZaTypy;

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

//            $pomocnicza->save($zapiszDoPomocniczej);

            $pozycje[]=$nowapozycja;

        }

            $pkt  = array_column($pozycje, 'pkt');
            array_multisort($pkt, SORT_DESC, $pozycje);

            $data['pozycje']=$pozycje;
            $data['cotozatabela']="Ranking wyłącznie wytypowanych meczów";

            $tabela =view('typowanie/tabelaGraczy', $data, ['cache'=>60, 'cache_name'=>$cachedTabela]);
            }
            $czasStop=gettimeofday(true);
            $delta = $czasStop-$czasStart;
            $data['czas']=$delta;

        return  view('typowanie/header',$data)
                .$tabela
                .view('typowanie/footer',$data);
    }

        public function tabelaTylkoPytania(){
        $czasStart=gettimeofday(true);

        $cachedTabela = "TabelaTylkoPytania";
                $data = [
            'title' => 'Taka ładna tabela',
        ];
        if (! $tabela = cache($cachedTabela)){



        //to teraz potrzebuję ;] Pobrać listę wszystkich aktywnych graczy
        //dla każdego gracza policzyć liczbę punktów
        //posortować tabelę ze względu na liczbę punktów
        //nie wierzę, ze to mówię, ale kurde chyba sprawdzę co jest szybsze. Gdyż why not. 
        //a potem zróbmy cache'owanie - i jakąś flagę, czy coś się zmieniło, czy nie

        $uzytkownicy = model(UserModel::class);
        $odpowiedz = model(OdpowiedziModel::class);
        $typy = model(TypyModel::class);
        $pomocnicza = model(PomocnicaPiPModel::class);
        
        $uzytkownicyBuilder=$uzytkownicy->builder();
        $uzytkownicyBuilder->where('activated',1);
        $aktywniUzytkownicy=$uzytkownicyBuilder->get()->getResultArray();

        foreach ($aktywniUzytkownicy as $uzytkownik) {

            //na razie na sucho, czyli wypiszemy same proste rzeczy :)
            // potrzebujemy wiedzieć, ile punktów ma dany użytkownik, czyli stworzymy sobie tabele, w której bedzie:
            // nick=>punkty


            $zapytanieOTypy = $typy->builder();
            $zapytanieOTypy->where('UserId', $uzytkownik['id']);
            $zapytanieOTypy->selectSum('pkt');
            $liczbaPktZaTypy = $zapytanieOTypy->get()->getRow()->pkt;

            $zapytanieOPytania = $odpowiedz->builder();
            $zapytanieOPytania->where('uniidOdp', $uzytkownik['uniID']);
            $zapytanieOPytania->selectSum('pkt');
            $liczbaPktZaPytania = $zapytanieOPytania->get()->getRow()->pkt;
            
            //$liczbapkt = $liczbaPktZaTypy + $liczbaPktZaPytania;
            $liczbapkt = $liczbaPktZaPytania;

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

//            $pomocnicza->save($zapiszDoPomocniczej);

            $pozycje[]=$nowapozycja;

        }

            $pkt  = array_column($pozycje, 'pkt');
            array_multisort($pkt, SORT_DESC, $pozycje);

            $data['pozycje']=$pozycje;
            $data['cotozatabela']="Ranking wyłącznie pytań dodatkowych";
            

            $tabela =view('typowanie/tabelaGraczy', $data, ['cache'=>60, 'cache_name'=>$cachedTabela]);
            }
            $czasStop=gettimeofday(true);
            $delta = $czasStop-$czasStart;
            $data['czas']=$delta;

        return  view('typowanie/header',$data)
                .$tabela
                .view('typowanie/footer',$data);
    }


    public function ileDokladnychWynikow(){
        $czasStart=gettimeofday(true);

        $cachedTabela = "TabelaDokladnychWynikow";
                $data = [
                        'title' => 'Taka ładna tabela',
        ];
        if (! $tabela = cache($cachedTabela)){



        //to teraz potrzebuję ;] Pobrać listę wszystkich aktywnych graczy
        //dla każdego gracza policzyć liczbę punktów
        //posortować tabelę ze względu na liczbę punktów
        //nie wierzę, ze to mówię, ale kurde chyba sprawdzę co jest szybsze. Gdyż why not. 
        //a potem zróbmy cache'owanie - i jakąś flagę, czy coś się zmieniło, czy nie

        $uzytkownicy = model(UserModel::class);
        $odpowiedz = model(OdpowiedziModel::class);
        $typy = model(TypyModel::class);
        $pomocnicza = model(PomocnicaPiPModel::class);
        
        $uzytkownicyBuilder=$uzytkownicy->builder();
        $uzytkownicyBuilder->where('activated',1);
        $aktywniUzytkownicy=$uzytkownicyBuilder->get()->getResultArray();

        foreach ($aktywniUzytkownicy as $uzytkownik) {

            //na razie na sucho, czyli wypiszemy same proste rzeczy :)
            // potrzebujemy wiedzieć, ile punktów ma dany użytkownik, czyli stworzymy sobie tabele, w której bedzie:
            // nick=>punkty


            $zapytanieOTypy = $typy->builder();
            $zapytanieOTypy->where('UserId', $uzytkownik['id']);
            $zapytanieOTypy->where('pkt',3);
            
            $liczbaDokladnychWynikow = $zapytanieOTypy->countAllResults();


            

//            $zapytanieOPytania = $odpowiedz->builder();
  //          $zapytanieOPytania->where('uniidOdp', $uzytkownik['uniID']);
  //          $zapytanieOPytania->selectSum('pkt');
   //         $liczbaPktZaPytania = $zapytanieOPytania->get()->getRow()->pkt;
            
            //$liczbapkt = $liczbaPktZaTypy + $liczbaPktZaPytania;
            //$liczbapkt = $liczbaPktZaTypy;

            $nowapozycja = [
            'nick'=>$uzytkownik['nick'],
            'pkt'=>$liczbaDokladnychWynikow,
            ];
 //           $primaryKey = 'UniID';
            //$zapiszDoPomocniczej=[
            //  'UniID'=> $uzytkownik['uniID'],
            //  'Punkty'=>$liczbapkt,
            //  'Zmodyfikowane'=>date("Y-m-d H:i:s"), 
           // ];

            //$pomocnicza->save($zapiszDoPomocniczej);

            $pozycje[]=$nowapozycja;

        }

            $pkt  = array_column($pozycje, 'pkt');
            array_multisort($pkt, SORT_DESC, $pozycje);

            $data['pozycje']=$pozycje;
            $data['cotozatabela']="Ile dokładnych wyników";

            $tabela =view('typowanie/tabelaGraczy', $data, ['cache'=>60, 'cache_name'=>$cachedTabela]);
            }
            $czasStop=gettimeofday(true);
            $delta = $czasStop-$czasStart;
            $data['czas']=$delta;

        return  view('typowanie/header',$data)
                .$tabela
                .view('typowanie/footer',$data);
    }

}