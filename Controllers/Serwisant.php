<?php


namespace App\Controllers;


use App\Controllers\BaseController;
use App\Models\TerminarzModel;



class Serwisant extends BaseController
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

	public function getFixtures($params = []) {
        $url = $this->_buildUrl('fixtures/matches.json', $params);
        $data = $this->_makeRequest($url);
        return $data;
    }

    public function getGroups($params = []) {
        $url = $this->_buildUrl('competitions/groups.json', $params);
        $data = $this->_makeRequest($url);
        return $data;
    }

    public function getTeams ($params = []){
        $url = $this->_buildUrl('competitions/participants.json', $params);
        $data = $this->_makeRequest($url);
        return $data;
    }

    public function captureTheFlag($params = []){
        $url = $this->_buildUrl('countries/flag.json', $params);
        $data = $this->_makeRequestForTheFlag($url);
        return $data;
    }

    	protected function _makeRequestForTheFlag($url) {
        #$json = $this->_useCache($url);

        #if ($json) {
        #   $data = json_decode($json, true);
        #} else {
            $json = file_get_contents($url);
            $data = json_decode($json, true);

        #   if (!$data['success']) {
        #       throw new RuntimeException($data['error']);
        #   }
        #
        #   $this->_saveCache($url, $json);
        #}

        return $data;
    }


	protected function _makeRequest($url) {
        #$json = $this->_useCache($url);

        #if ($json) {
        #   $data = json_decode($json, true);
        #} else {
            $json = file_get_contents($url);
            $data = json_decode($json, true);

        #   if (!$data['success']) {
        #       throw new RuntimeException($data['error']);
        #   }
        #
        #   $this->_saveCache($url, $json);
        #}

        return $data['data'];
    }

    public function zapiszGrupy(){
        $parametry_zapytania['competition_id']="362";
        $grupy=$this->getGroups($parametry_zapytania);
        //echo "<pre>";
        //print_r($grupy);
        //echo "</pre>";

        $grupModel = new \App\Models\GrupyModel();

        $zapisaneGrupy = [];
        $zapisaneGrupy = $grupModel->FindColumn('ApiID');

        foreach ($grupy as $grupa){
            $grupaDoZapisu = [
                'ApiID' => $grupa['id'],
                'Name'=> $grupa['name'],
                'Stage' => $grupa['stage'],
                'Stage_PL'=> 'Faza grupowa'
                ];    

            if (!$zapisaneGrupy) {
                $mesydz = "<p> ( ◡̀_◡́)ᕤ:  Tej grupy jeszcze nie było, zatem ZOSTAŁA DODANY ".$grupa['name']." </p>";
                $query = $grupModel->save($grupaDoZapisu);
                } else if (!in_array($grupa['id'], $zapisaneGrupy)) {
                        $mesydz = "<p> ( ◡̀_◡́)ᕤ:  Tej grupy jeszcze nie było, zatem ZOSTAŁA DODANY ".$grupa['name']." </p>";
                        $query = $grupModel->save($grupaDoZapisu);
                        } else {
                                $kluczyk = array_search($grupa['id'], $zapisaneGrupy);
                                //echo "ten mecz juz mamy, jego id to ".++$kluczyk;
                                $kluczyk++;
                                $mesydz = "<p> ( ◡̀_◡́)ᕤ:  Grupa o indeksie ".$kluczyk." czyli GRUPA ".$grupa['name']." została zaktualizowana.</p>";
                                $query = $grupModel->update($kluczyk,$grupaDoZapisu);
                                }
                            
        if(!$query)
        {
            echo "<p>¯\_(ツ)_/¯: NIE ZOSTAŁA DODANA GRUPA ".$grupa['name']." </p>";
        }
        else
        {
            echo $mesydz;
        }

    }


        

    }


        public function zapiszDruzyny(){
        $parametry_zapytania['competition_id']="362";
        $parametry_zapytania['season']="2022";
        $druzyny=$this->getTeams($parametry_zapytania);
        //echo "<pre>";
        //print_r($grupy);
        //echo "</pre>";

        $teamsModel = new \App\Models\TeamsModel();

        $zapisaneDruzyny = [];
        $zapisaneDruzyny = $teamsModel->FindColumn('ApiID');

        foreach ($druzyny as $druzyna){
            $flaga_kraju['team_id']=$druzyna['id'];
            $url_to_image = "https://livescore-api.com/api-client/countries/flag.json?team_id=".$druzyna['id']."&key=uaoUdyX7J75xWOBp&secret=kIVcpQpNWTZjZphtzFoNelUYt0vpO7P9";
            echo $url_to_image;
              $directory = getcwd()."/logos/";
            //$basename = basename($url_to_image);
            //$ext = pathinfo($basename, PATHINFO_EXTENSION);
            $nazwaPliku = $druzyna['name'].".png";
            $complete_save_loc = $directory.$nazwaPliku;
            $upload = file_put_contents($complete_save_loc, file_get_contents($url_to_image));

            if ($upload) {
                echo "<p>zaladowano flagę</p>";
                }
            else {
                echo "<p>nic nie zaladowano i popelniono bledy</p>";
                }
             
            $DruzynaDoZapisu = [
                'ApiID' => $druzyna['id'],
                'Name'=> $druzyna['name'],
                'Flag' => $nazwaPliku,
                ];    

            if (!$zapisaneDruzyny) {
                $mesydz = "<p> ( ◡̀_◡́)ᕤ:  Tej druzyny jeszcze nie było, zatem ZOSTAŁA DODANY ".$druzyna['name']." </p>";
                $query = $teamsModel->save($DruzynaDoZapisu);
                } else if (!in_array($druzyna['id'], $zapisaneDruzyny)) {
                        $mesydz = "<p> ( ◡̀_◡́)ᕤ:  Tej grupy jeszcze nie było, zatem ZOSTAŁA DODANY ".$druzyna['name']." </p>";
                        $query = $teamsModel->save($DruzynaDoZapisu);
                        } else {
                                $kluczyk = array_search($druzyna['id'], $zapisaneDruzyny);
                                //echo "ten mecz juz mamy, jego id to ".++$kluczyk;
                                $kluczyk++;
                                $mesydz = "<p> ( ◡̀_◡́)ᕤ:  DrużynA o indeksie ".$kluczyk." czyli GRUPA ".$druzyna['name']." została zaktualizowana.</p>";
                                $query = $teamsModel->update($kluczyk,$DruzynaDoZapisu);
                                }
                            
        if(!$query)
        {
            echo "<p>¯\_(ツ)_/¯: NIE ZOSTAŁA DODANA GRUPA ".$druzyna['name']." </p>";
        }
        else
        {
            echo $mesydz;
        }

    }


        

    }

    public function zapiszMeczeTurnieju($page=1){

     $parametry_turniejowe['competition_id']="362";
     
     if ($page==1) {
        echo "<p>--- sprawdzam stronę nr ".$page." ---</p>";
     $data['turniejowe']=$this->getFixtures($parametry_turniejowe);   
     } else {
        echo "<p>--- sprawdzam stronę nr ".$page." ---</p>";
        $parametry_turniejowe['page']=$page;
        $data['turniejowe']=$this->getFixtures($parametry_turniejowe);
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
    


    //$query = $terminarzModel->insert($meczDoZapisu);


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

/*
    if ($czyJestTenMecz) {
        echo "<p>Mam ten mecz</p>";
//      $aktualizowanyMecz=$czyJestTenMecz['0']['Id'];
        $query = $terminarzModel->update($aktualizowanyMecz,$meczDoZapisu); 
    } else {
        echo "<p>Nie mam Twego miecza</p>";
        $query = $terminarzModel->save($meczDoZapisu);        
    }

*/


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
        $this->zapiszMeczeTurnieju($page);
    } else {
        echo "<p>Zakończyłem prace ręczne </p>";

    }


    }

	public function zapiszDzisiejszeMeczeDoTerminarzaTypow()
    {
        $parametry_dzisiejsze['competition_id']="363,387,274,271,227,244,245,1,2,3,4,60,209,349,350,446,370,371,149,150,151,152,153,167,169,179,178,333,334,111,205";
        //Pamiętaj, że jesli bedziesz chcial robic mistrzostwa, to musisz ponownie wprowadzić kategorie 362

			$parametry_dzisiejsze['date']="today";
//        $cachedFixtures = "rozgrywki_dzisiejsze";
			$data['zaplanowaneNaDzis']=$this->getFixtures($parametry_dzisiejsze);
    
	//Co mamy to tabele z terminarzem. Teraz - chcę każdy element tej tabeli odpowiednio zapisać.
	$terminarzModel = new \App\Models\TerminarzModel();

	//echo "<pre>";
	//print_r($data['zaplanowaneNaDzis']);
	//echo "</pre>";
	
	foreach ($data['zaplanowaneNaDzis'] as $zaplanowanyMecz){
	
	$meczDoZapisu = [
	'ApiID'=>$zaplanowanyMecz['id'],
	'HomeID'=>$zaplanowanyMecz['home_id'],
	'HomeName'=>$zaplanowanyMecz['home_name'],
	'AwayID'=>$zaplanowanyMecz['away_id'],
	'AwayName'=>$zaplanowanyMecz['away_name'],
	'CompetitionID'=>$zaplanowanyMecz['competition']['id'],
	'CompetitionName'=>$zaplanowanyMecz['competition']['name'],
	'Date'=>$zaplanowanyMecz['date'],
	'Time'=>$zaplanowanyMecz['time']
	];
	
	$query = $terminarzModel->insert($meczDoZapisu);
	$licznik=1;
	if(!$query)
        {
            echo "<p>Nie udało się zapisać meczu nr ".$licznik++."</p>";
        }
        else
        {
            echo "<p>Gratuluję Ci Wicie, zapisałeś mecz ".$licznik++." jak najprawdziwsze złoto</p>";
        }

	}

	/*
	
	$data = [
            'nick' => $nick,
            'email' => $email,
            'passhash' => Hash::encrypt($password)
         ];


         // Storing data


         $userModel = new \App\Models\UserModel();
         $query = $userModel->insert($data);


        if(!$query)
        {
            return redirect()->back()->with('fail', 'Nie udało się zapisać użytkownika');
        }
        else
        {
            return redirect()->back()->with('success', 'Nowy użytkownik został zarejestrowany');
        }
	
	
	*/



    
    //return $zaplanowane;
    }


    public function index()
    {


        $userModel = new UserModel();
        $loggedInUserId = session()->get('loggedInUser');
        $userInfo = $userModel->find($loggedInUserId);


        $data = [
            'title' => 'Dashboard',
            'userInfo' => $userInfo,
        ];
        return view('typowanie/index', $data);
    }

    public function dodajPytanie()
    {
        $pytanie = model(PytaniaModel::class);

        if ($this->request->getMethod() === 'post' && $this->validate([
            'tresc' => 'required|min_length[3]|max_length[255]',
        ])) {
            $pytanie->save([
                'tresc' => $this->request->getPost('tresc'),
                'pkt'  => $this->request->getPost('pkt'),
                'wazneDo'  => $this->request->getPost('wazneDo'),
            ]);
            
            session()->setFlashData('sukces', 'Dodane poprawnie. <br> Czujesz moc? Chcesz dodać kolejne?');
            return redirect()->to('serwisant/dodajPytanie');
        }

        return view('serwisant/czarymary');
    }

    public function ktoJeszczeNieOdpowiedzial(int $mecz=64){
        $typyModel = model(TypyModel::class);
        $uzytkownicyModel = model(UserModel::class);
        $zapytanieUzytkownicy = $uzytkownicyModel->builder();
        $zapytanieUzytkownicy->where('activated','1');
        $wszyscyAktywniUzytkownicy=$zapytanieUzytkownicy->get()->getResultArray();

        $zapytanieModel = $typyModel->builder();
        $zapytanieModel->where('GameID',$mecz);
        $wszystkieTypyDlaMeczu = $zapytanieModel->get()->getResultArray();

        //wez mi tabele [id]=>uzytkownik
        //wez mi tabele [id]=>typ
        //polec po tabeli id => uzytkownik wytypowal \ nie wytypowal
        $ktoTypowal = array_column($wszystkieTypyDlaMeczu,'GameID','UserID');
        $wszystkieAktwyneNicki = array_column($wszyscyAktywniUzytkownicy, 'nick', 'id');

           // echo "<p>Bujakasha</p><pre>";
           // print_r($wszystkieAktwyneNicki);
           // echo "<pre><p>Koniec bujakasha</p>";
        $mesydz = "Drogi Typerowiczu.\n\n Dziś jest ta ostatnia niedziela. Jest finał mistrzostw, jest finałowe pytanie, są finałowe rozstrzygnięcia. Ponieważ do dzisiejszego meczu zostalo mniej niż godzina, a Ty nie masz zapisanego dla niego typu, pozwalam sobie wysłać Ci tę przypominajkę. \n\n Na stronie jest też pytanie dnia, które zapewne czeka na Twoją odpowiedź. \n\nTo przez przypadek? A może działasz z premedytacja?\n\n Jeśli to przez niedopatrzenie, zachęcam Cię do zerkniecia na https://jakiwynik.com/typowanie i uzupełnienie swoich typów. \n\n pozdrawiam, Wit\n\n -- May the odds be in your flavour";
        foreach ($wszyscyAktywniUzytkownicy as $user){

//            echo "<p>".$user['id']."=>".$user['nick']."</p><pre>";
            
            if (array_key_exists($user['id'],$ktoTypowal)) {
               // echo "<p>".$user['nick']." typował(a)</p>";
            } else {
                echo "<p>".$user['nick']."(".$user['email'].") nie typował(a). Wysyłam maila z przypominajką.</p>";
                $mail = \Config\Services::email();
                  $mail->setFrom('wit@jakiwynik.com', 'Wit z JakiWynik.com');
                  $mail->setTo($user['email']);
                  $mail->setSubject('Przyjacielskie przypomnienie');
                  $mail->setMessage($mesydz);
                 //$mail->send();
        
            
            }

        } 
        
                               
 
        echo "<pre>";
 //       print_r($wszystkieTypyDlaMeczu);
 //       print_r($wszyscyAktywniUzytkownicy);

        echo "</pre>";


    }

    public function policzPunktyDlaMeczu(int $mecz=1){

        //Co ta funkcja ma zrobić? Ma pobrać wynik meczu wskazanego w parametrze, sprawdzić, czy jest zakończony, jeśli tak, to a następnie sprawdzić, jakie punkty przypisać do typu każdego gracza, który obstawiał. 

         $terminarzModel = model(TerminarzModel::class);
         $typyModel = model(TypyModel::class);
    
         /*$zapytanieTerminarz = $terminarzModel->builder();
         $zapytanieTerminarz->where('Id',$mecz); */
         $daneMeczu = $terminarzModel->getMeczById($mecz);
        if (!$daneMeczu) {
            echo "Nie znaleziono meczu";
            return;
        }
         $wynikHome = $daneMeczu['ScoreHome'];
         $wynikAway = $daneMeczu['ScoreAway'];

         /*$zapytanieTypy = $typyModel->builder();
         $zapytanieTypy->where('GameID',$mecz);*/
         $WszystkieTypy=$typyModel->getTypyByMeczId($mecz);
         if (empty($WszystkieTypy)) {
            echo "brak typów dla tego meczu";
            return;
         }
        
        $daneMeczu['liczbaTypow']=count($WszystkieTypy);

         if ($daneMeczu['zakonczony']){
            echo "<p>Wynik gospodarzy: ".$daneMeczu['ScoreHome']."</p>";
            echo "<p>Wynik gości: ".$daneMeczu['ScoreAway']."</p>";

         } else {
            echo "Nie mogę tego zrobić, ponieważ mecz się jeszcze nie skończył";
         }

         foreach ($WszystkieTypy as $typ) {
            
            $punkty = 0; 
            if (($typ['HomeTyp']==$wynikHome)and($typ['AwayTyp']==$wynikAway)) { $punkty = 3;
            } else if ( $typ['HomeTyp']>$typ['AwayTyp']and($wynikHome>$wynikAway)) { $punkty=1;
            } else if ( $typ['HomeTyp']<$typ['AwayTyp']and($wynikHome<$wynikAway)) { $punkty=1;
            } else if ( $typ['HomeTyp']==$typ['AwayTyp']and($wynikHome==$wynikAway)) { $punkty=1;    
            }

//            echo "<p>Typ nr ".$typ['Id']." przewidywał ".$typ['HomeTyp']." : ".$typ['AwayTyp']." i powinnien zostać przyznane ".$punkty." punkt</p>";
            
            $punktyDoZapisania=[
            'pkt'=>$punkty,
            ];

            if ($typyModel->update($typ['Id'],$punktyDoZapisania)) {
                echo "<p>Zaktualizowałem typ nr ".$typ['Id']."</p>";
                };

         }


         echo "<pre>";
         print_r($WszystkieTypy);
         echo "</pre>";

        $this->updateJsonFile($daneMeczu);

    }

    private function updateJsonFile($daneMeczu) {
    // Ścieżka do pliku JSON
    $filePath = WRITEPATH . '/mecze/'.$daneMeczu['TurniejID'].'/'.$daneMeczu['ApiID'] . '.json';

    // Sprawdzenie, czy plik JSON istnieje
    if (file_exists($filePath)) {
        // Odczytanie zawartości pliku JSON
        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);

        // Aktualizacja pola w danych JSON
        $data['status'] = 'Zakonczony';
        $data['home_team']['score'] = $daneMeczu['ScoreHome'];
        $data['away_team']['score'] = $daneMeczu['ScoreAway'];
        $data['liczbaTypow']=$daneMeczu['liczbaTypow'];

        // Zapisanie zmodyfikowanych danych z powrotem do pliku JSON
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
        echo "<p>Plik JSON został zaktualizowany</p>";
    } else {
        echo "<p>Plik JSON nie istnieje</p>";
    }
    //print_r($daneMeczu);
}


    public function zapiszWynikMeczu(int $mecz = 1) {
    $configPath = WRITEPATH . 'ActiveTournament.json';
    $config = file_exists($configPath) ? json_decode(file_get_contents($configPath), true) : [
        'activeTournamentId' => 'Brak danych',
        'activeCompetitionId' => 'Brak danych',
        'activeTournamentName' => 'Brak danych'
    ];

    // Potrzebuje... pokaż mi dzisiejsze mecze
    $dzis = date("Y-m-d"); 
    $terminarzModel = model(TerminarzModel::class);
    $terminarzZapytanie = $terminarzModel->builder();
    $terminarzZapytanie->where("Date", $dzis);
    $terminarz = $terminarzZapytanie->get()->getResultArray();
    
    echo "<pre>";
    print_r($terminarz);
    echo "</pre>";

    $terminarz = $terminarzModel->getRozpoczeteNieZakonczone($config['activeTournamentId']);
    
    echo "<pre>";
    print_r($terminarz);
    echo "</pre>";
    
    $validation = \Config\Services::validation();
    
    $validationRules = [
        'H' => [
            'rules' => 'required|is_natural',
            'errors' => [
                'required' => 'Musisz podać wynik',
                'is_natural' => 'Musi być liczbą naturalną'
            ]
        ],
        'A' => [
            'rules' => 'required|is_natural',
            'errors' => [
                'required' => 'Musisz podać wynik',
                'is_natural' => 'Musi być liczbą naturalną'
            ]
        ]
    ];

    if (!$this->validate($validationRules)) {
        session()->setFlashdata('error', $validation->listErrors());
        return redirect()->back()->withInput();
    }
    
    echo "<p>Będziemy się bawić tym, co przesłałeś, a przesłałeś:</p>";
    
    $ktoryMecz = $this->request->getPost('meczID');
    $wynikGospodarzy = $this->request->getPost('H');
    $wynikGosci = $this->request->getPost('A');
    
    echo "<p>Chcesz zapisać mecz: {$ktoryMecz}. Gospodarzom chcesz dać wynik: {$wynikGospodarzy}, a gościom: {$wynikGosci}. Zgadza się?</p>";

    $data = [
        'Id' => $ktoryMecz,
        'ScoreHome' => $wynikGospodarzy,
        'ScoreAway' => $wynikGosci,
        'zakonczony' => 1
    ];

    if ($terminarzModel->update($ktoryMecz, $data)) {
        session()->setFlashData('sukces', 'Wynik meczu został zapisany, uruchamiam przeliczenie punktów dla tego meczu');
        $urlPrzelicz = site_url('/przeliczMecz/' . $ktoryMecz);
        return redirect()->to($urlPrzelicz);
    }

    $data['terminarz'] = $terminarz;

    return view('serwisant/meczoweCzaryMary', $data);
}

}