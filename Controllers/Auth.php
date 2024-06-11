<?php


namespace App\Controllers;


use App\Controllers\BaseController;
use App\Libraries\Hash;
use App\Libraries\Token;
use App\Models\UserModel;
use App\Models\TypyModel;
use App\Models\ClubMembersModel;

class Auth extends BaseController
{

    protected $_key;
    protected $_secret;

    public $connection = null;


    // Enabling features
    public function __construct()
    {
        helper(['url', 'form']);
    }



    /**
      * Responsible for login page view.
    */
    public function index()
    {
        return view('auth/login');
    }

    public function reset (){
        return view('auth/reset');
    }

    public function resetPassword(){
            $validated = $this->validate([
            'email'=> [
                'rules' => 'required|valid_email',
                'errors' => [
                    'required' => 'Podaj email, gdzieś tę linkę do resetu muszę wysłać', 
                    'valid_email' => 'Twój email musi być prawidłowy',
                ]
            ]

        ]);


         if(!$validated)
         {
             return view('auth/reset', ['validation' => $this->validator]);
         }

         else{
            //no dobra, wszystko wskazuje na to, że został podany mail to co powinniśmy zakomunikować, to że jeżeli Twoj email jest prawidłowy, to zostanie do Ciebie wysłana linka z opcją do odświeżenia hasła. No i sprawdź spam (niestety). Jeśli w ciągu kilku minut nic nie przyjdzie, pisz do Wita na wit[at]jakiwynik.com
            // co potrzebuje zrobić. Jeśli email jest prawidłowy, sprawdźmy go w bazie. 
                     
            $email = $this->request->getPost('email');
            $userModel = new \App\Models\UserModel();

            $warunki = $userModel->builder();
            //$warunki->where('Date',date("Y-m-d"));
            $warunki->where('email',$email);
            $czyJestTenMail=$warunki->get()->getResultArray();
            if ($czyJestTenMail) {
                //potrzebuję zrobić unikalny link, ale też chciałbym dowiedzieć sie co to za użytkownik
                //jeśli dobrze rozumiem, jeśli wypluję czy jest ten mail, bede mógł zobaczyć, troche danych o uzytkowniku
                
                $userName = $czyJestTenMail[0]['nick'];
                $userID = $czyJestTenMail[0]['uniID'];
                $reason = "PassChange";

                $token = Token::createToken($userID,$reason);
                
                $mesydz = "Chcesz zmieniać hasło?\n\n Tu masz link do zmiany hasła: ".base_url()."/dejnowehaslo/".$token."\n\n Kliknij, zmień, zapisz, żyj szczęśliwie. \n\n May the odds be in your flavour. \n\n\n\n Nie chciałeś nic zmieniać? Nie wiesz o co chodzi? Nie masz ochoty typować - nie przejmuj się tym mailem. Ktoś zrobił Ci psikusa, Twój mail nie jest nigdzie zapisany. Live long and prosper.";
                echo "<pre>";
                echo $mesydz;
                echo $token;
                echo "</pre>";
                
            
            
                
            $mail = \Config\Services::email();
            $mail->setFrom('wit@jakiwynik.com', 'Wit z JakiWynik.com');
            $mail->setTo($email);
            $mail->setSubject('Zmiana hasła w serwisie JakiWynik.com');
            $mail->setMessage($mesydz);
            $mail->send();
            

                session()->setFlashdata('success', 'Jak by Ci to powiedzieć... wychodzi na to, że powinieneś dostać na swojego maila w ciągu najbliższych kilku minut link do odświeżenia hasła. Link jest ważny przez 15 minut. Spiesz się powoli. Czekaaaam');
                return redirect()->to('/auth');

                
                }
                else {
                session()->setFlashdata('fail', 'Coś w przygotowanym procesie nie zadziałało i nie potrafię w tym momencie zaktualizowa Twojego hasła.. sorrri... spróbuj jeszcze raz');
                return redirect()->to('/reset');
                }
        /*
        echo "<pre>";
        print_r($czyJestTenTyp);
        echo "</pre>";
        */


    


            
         }
    }






    /**
      *Responsible for register page view.
    */  



    public function register()
    {
        return view('auth/register');
    }


    /**
     * Save new user to database.
     */


     public function registerUser()
     {
         // Validate user input.


        //  $validated = $this->validate([
        //     'name'=> 'required',
        //     'email' => 'required|valid_email',
        //     'password' => 'required|min_length[5]|max_length[20]',
        //     'passwordConf'=> 'required|min_length[5]|max_length[20]|matches[password]'
        //  ]);


        $validated = $this->validate([
            'username' => [
                'rules' => 'required|is_unique[uzytkownicy.nick]',
                'errors' => [
                    'required' => 'Twój Nick jest niezbędny.',
                    'is_unique' => 'Jest już gracz o takim nicku',
                ]
            ],
            'email'=> [
                'rules' => 'required|valid_email|is_unique[uzytkownicy.email]',
                'errors' => [
                    'required' => 'Twój email jest potrzebny', 
                    'valid_email' => 'Twój email musi być prawidłowy',
                    'is_unique' => 'Mamy już konto przypisane do tego emaila',
                ]
            ],
            'password'=> [
                'rules' => 'required|min_length[7]',
                'errors' => [
                    'required' => 'Musisz podać hasło', 
                    'min_length' => 'Hasło musi mieć co najmniej {param} znaków',
                ]
            ],
            /*'passwordConf'=> [
                'rules' => 'required|min_length[5]|max_length[20]|matches[password]',
                'errors' => [
                    'required' => 'Your confirm password is required', 
                    'min_length' => 'Password must be 5 charectars long',
                    'max_length' => 'Password cannot be longer than 20 charectars',
                    'matches' => 'Confirm password must match the password',
                ]
            ],*/
        ]);


         if(!$validated)
         {
             return view('auth/register', ['validation' => $this->validator]);
         }


         // Here we save the user.


         $nick = $this->request->getPost('username');
         $email = $this->request->getPost('email');
         $password = $this->request->getPost('password');
         $preuniid = $nick.$email;
         $uniid = md5($preuniid);
         //$passwordConf = $this->request->getPost('passwordConf');


         $data = [
            'nick' => $nick,
            'email' => $email,
            'passhash' => Hash::encrypt($password),
            'uniID'=> $uniid,
         ];


         // Storing data


         $userModel = new \App\Models\UserModel();
         $query = $userModel->insert($data);


        if(!$query)
        {
            session()->setFlashdata('fail', 'Nie udało się zapisać użytkownika');
            return redirect()->back();
        }
        else
        {
            $mail = \Config\Services::email();
            $mail->setFrom('wit@jakiwynik.com', 'Wit z JakiWynik.com');
            $mail->setTo($email);
            $mail->setSubject('Potwierdź rejestrację w Typerze JakiWynik.com');
            $mail->setMessage("A więc chcesz sprawdzić swoje szczęście i swoją piłkarska wiedzę? Brrrrawo Ty! \n\n Aktywuj swoje konto klikając w link: \n\n ".base_url()."/aktywuj/".$uniid."\n\n To tyle ode mnie, dziękować. \n\n May the odds be in your flavour; :) \n\n\n\n Nie chciałeś się rejestrować? Nie wiesz o co chodzi? Nie masz ochoty typować - nie przejmuj się tym mailem. Ktoś zrobił Ci psikusa, Twój mail nie jest nigdzie zapisany. Live long and prosper.");
            $mail->send();

            session()->setFlashdata('success', 'Nowy użytkownik został zarejestrowany, a ja uczę się wysyłaś maila z potwierdzeniem. Jak klikniesz w link, możesz się tu potem zalogować.');
            return redirect()->to('auth');
        }
     }

    public function confirm($token){
        if (!$token){
            session()->setFlashdata('fail', 'Nie udało się aktywować użytkownika');
            return redirect()->to('auth');
        } else {
            // Czy token dotyczy użytkownika istniejącego w bazie? Jeśli tak - aktywuj, jeśli nie wyświetl komunikat o błędzie;
            $userModel = new \App\Models\UserModel();
            $warunki = $userModel->builder();
            $warunki->where('uniID',$token);
            $czyJestTenUser=$warunki->get()->getResultArray();

            if ($czyJestTenUser){
                $data=[
                    'activated'=>"1",
                    'activatedON'=>date("Y-m-d H:i:s"),
                ];
 /*                       echo "<pre>";
                        print_r($czyJestTenUser);
                        echo "</pre>";
*/
                        $aktualizowanyUserID=$czyJestTenUser['0']['id'];
//                        echo $czyJestTenUser['0']['id'];
                        $userModel->update($aktualizowanyUserID,$data); 
                                session()->setFlashdata('success', 'Nowy użytkownik został aktywowany. Możesz się zalogować i zacząć typować. May the odds be in your flavour');
                               return redirect()->to('auth');  
                
            } else {
                session()->setFlashdata('fail', 'Błąd w linku aktywacyjnym, sprawdź go proszę');
                return redirect()->to('auth');
                
                                }
            }

        /*
        echo "<pre>";
        print_r($czyJestTenTyp);
        echo "</pre>";


    if ($czyJestTenTyp) { 
        session()->setFlashdata('success', 'Twoje konto zostało pomyślnie aktywowane, możesz się zalogować.');
        return redirect()->to('auth');          
        }*/
    }

     /**
      * User login method.
      */
     public function loginUser()
{
    // Validating user input.
    $validated = $this->validate([
        'username'=> [
            'rules' => 'required',
            'errors' => [
                'required' => 'Musisz podać nazwę użytkownika.', 
            ]
        ],
        'password'=> [
            'rules' => 'required',
            'errors' => [
                'required' => 'Musisz podać hasło.', 
            ]
        ],
    ]);

    if(!$validated)
    {
        return view('auth/login', ['validation' => $this->validator]);
    }
    else
    {
        // Checking user details in database.
        $nick = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $userModel = new UserModel();
        $userInfo = $userModel->getUserByNick($nick);

        if(!$userInfo) {
            session()->setFlashData('fail', 'Podajesz błędny nick lub hasło.');
            return redirect()->to('auth');
        }

        $checkPassword = Hash::check($password, $userInfo['passhash']);

        if(!$checkPassword)
        {
            session()->setFlashData('fail', 'Podajesz błędny nick lub hasło.');
            return redirect()->to('auth');
        }
        else
        {
            // Sprawdzenie aktywacji konta
            if (!$userInfo['activated']) {
                session()->setFlashData('fail', 'Musisz najpierw aktywować konto - sprawdź skrzynkę i kliknij w link.');
                return redirect()->to('auth');
            } else {
                $userId = $userInfo['uniID'];
                session()->set('loggedInUser', $userId);
                session()->set('username', $userInfo['nick']);
                // Sprawdzenie, czy użytkownik użył "GoldenBall"
                $typyModel = new TypyModel();
                $usedGoldenBall = $typyModel->usedGoldenBall($userId);

                $clubMembers = new ClubMembersModel();
                $userClub = $clubMembers->getClubsByUser($userInfo['uniID']);

                if(!$userClub) {
                    $userClub = "Poczekalnia";
                }

                // Zapisanie informacji w sesji
                session()->set('usedGoldenBall', $usedGoldenBall);
                session()->set('club_hash', hash('sha256', $userClub));
                session()->set('club', $userClub);

                if ($this->request->getPost('remember') == '1') {
                    $response = $this->setRememberMeCookie($userInfo['uniID'], $this->request->getUserAgent());
                    if ($userInfo['PlaysTheActiveTournament'] == 1) {
                        return $response->redirect('typowanie'); // Zwróć odpowiedź z przekierowaniem
                    } else {
                        return $response->redirect('/profil'); // Zwróć odpowiedź z przekierowaniem
                    }
                }

                // Przekierowanie w zależności od wartości PlaysTheActiveTournament
                if ($userInfo['PlaysTheActiveTournament'] == 1) {
                    return redirect()->to('typowanie');
                } else {
                    return redirect()->to('/profil');
                }
            }
        }
    }
}
      /**
       * Log out the user.
       */
        public function logout()
        {
            // Sprawdzanie, czy użytkownik jest zalogowany
            if (session()->has('loggedInUser'))
            {
                // Pobierz token ciasteczka remember_me
                $token = $this->request->getCookie('remember_me');
                
                // Jeśli token istnieje, usuń wpis z bazy danych
                if ($token) {
                    $model = new \App\Models\RecuerdaModel();
                    $model->removeRememberMeToken($token); // Usuwamy token z bazy
                    
                    // Usuwamy ciasteczko
                    $response = service('response');
                    $response->setCookie('remember_me', '', time()-3600);
                }
                
                // Usuń sesję użytkownika
                session()->remove('loggedInUser');
            }
            
            session()->setFlashData('success', 'Zostałeś wylogowany');
            
            // Przekieruj na stronę logowania z parametrem informującym o wylogowaniu
            return redirect()->to('/auth?access=loggedout')->withCookies();
        }

      //Muszę tu zrobić porządek, bo sprawdzanie czegokolwiek w tym pliku to głupi pomysł. 


      /*
      *Obsługa tego, co przyjdzie mailem, czyli 
      */

      public function newPassStart($token){
        $CzasZmianyHasła = date("Y-m-d H:i:s");
        if (empty($token)){ // nie ma żadnego parametru
            session()->setFlashData('fail', 'Błąd, spróbuj się zalogować, albo zmienić swoje hasło');
            return redirect()->to('auth');
        } else {
                // mamy jakiś parametr, trzeba by coś z nim zrobić
                // sprawdzamy token czy jest w bazie. Jeśli go nie ma, informujemy o błędzie. Czyli szukamy.
        
            $tokenModel = new \App\Models\TokenModel();
            $warunki = $tokenModel->builder();
            $warunki->where('Token',$token);
            $czyJestTenToken=$warunki->get()->getResultArray();

            if (empty($czyJestTenToken)){ //dostaliśmy błędny token, trzeba wróćić i powiedzieć, sorry gregory, ale błąd w linku. spróbuj jeszcze raz. 

                session()->setFlashData('fail', 'Nie uda się zmienić hasła - Twój token zmiany jest niewłaściwy. Sprawdź, czy dobrze skopiowałeś link, albo przejdź proces odzyskiwania hasła ponownie.');
                return redirect()->to('auth/reset');

            } else {
                // tu obsługujemy token

                // mamy token. Potrzebujemy sprawdzić jego ważność i czy został już użyty. Jeśli ważny i do użycia - pozwól zmieńić hasło, jesli coś nie teges - wyrzuć do ponownego sprawdzenia
                    if ($CzasZmianyHasła<$czyJestTenToken['0']['ValidUntil']) {
                        $tokenOCzasie = 1;
                    }else {
                        $tokenOCzasie =0;
                    }

                    $tokenWazny = $czyJestTenToken['0']['Valid'];
                    

                if ($tokenOCzasie&&$tokenWazny){ //Wszystko gra, aktualizujemy;

                    $data = [

                        'uniid' => $czyJestTenToken['0']['RequestorUNIID'],

                    ];

                    // zaktualizuje token

                    $aktualizacjaTokena = [
                        'id'=> $czyJestTenToken['0']['id'],
                        'Valid' => 0,
                    ];    

                    $tokenModel->save($aktualizacjaTokena);


                    return view('auth/noweHaslo',$data);


                }  else { //wypluj informację - sorry przyjacielu, token jest przeterminowany, musisz od nowa. 
                    session()->setFlashData('fail', 'Nie uda się zmienić hasła - Twój token zmiany stracił ważność. Chcesz zmienić hasło? Musisz ponownie przejść proces odzyskiwania hasła ponownie.');
                    return redirect()->to('auth/reset');
                }


            }


        } 




      }


      public function newPassSave(){
        echo "jak będziesz grzeczny, to zapiszę Twoje hasło";

        $validated = $this->validate([
        
            'password'=> [
                'rules' => 'required|min_length[7]',
                'errors' => [
                    'required' => 'Musisz podać hasło', 
                    'min_length' => 'Hasło musi mieć co najmniej {param} znaków',
                ]
            ],
            'confirmedpassword'=> [
                'rules' => 'required|min_length[7]|matches[password]',
                'errors' => [
                    'required' => 'Tak, naprawdę trzeba dwa razy', 
                    'min_length' => 'Hasło musi mieć co najmniej {param} znaków',
                    'matches' => 'Nie no, ale hasła się muszą zgadzać...',
                ]
            ],
        ]);


         if(!$validated)
         {
             return view('auth/register', ['validation' => $this->validator]);
         }


         // Storing data


         $userModel = new \App\Models\UserModel();
         $password = $this->request->getPost('password');
         $uniid= $this->request->getPost('uniid');

        $warunki = $userModel->builder();
            $warunki->where('uniID',$uniid);
            $ktoryToUser=$warunki->get()->getResultArray();

          $data = [
            'id'=>$ktoryToUser['0']['id'],
            'passhash' => Hash::encrypt($password),
            
         ];

         $query=$userModel->save($data);

         if(!$query)
        {
            session()->setFlashdata('fail', 'Nie udało się zaktualizować Twojego hasła.. sorrri...');
            return redirect()->to('/reset');
        }
        else
        {
            /*$mail = \Config\Services::email();
            $mail->setFrom('wit@jakiwynik.com', 'Wit z JakiWynik.com');
            $mail->setTo($email);
            $mail->setSubject('Potwierdź rejestrację w Typerze JakiWynik.com');
            $mail->setMessage("A więc chcesz sprawdzić swoje szczęście i swoją piłkarska wiedzę? Brrrrawo Ty! \n\n Aktywuj swoje konto klikając w link: \n\n ".base_url()."/aktywuj/".$uniid."\n\n To tyle ode mnie, dziękować. \n\n May the odds be in your flavour; :)");
            $mail->send();
            */    
            session()->setFlashdata('success', 'Twoje hasło zostało zaktualizowane, możesz się już zalogować, z _nowym_hasłem_.');
            return redirect()->to('auth');
        }
        

      }


      /* TU CHCĘ DOPRACOWYWAĆ AKTUALIZACJĘ COOOKIE */

      public function setRememberMeCookie($userUniId, $userAgent) {
            $token = bin2hex(random_bytes(16)); // Generowanie bezpiecznego tokena
            $expiresAt = date('Y-m-d H:i:s', time() + 604800); // Ustaw ważność na tydzień

            $model = new \App\Models\RecuerdaModel();
            $model->setRememberMeToken($userUniId, $token, $expiresAt, $userAgent);//setRememberMeToken($userUniId, $token, $expiresAt, $userAgent)

            // Ustaw ciasteczko
            $response = service('response');
            $response->setCookie('remember_me', $token, 604800); // Ważność ciasteczka (604800 sekund = 1 tydzień)
            return $response;
            }

}


