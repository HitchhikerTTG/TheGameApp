<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
/* rzekomo, te linie też juz są gdzie indziej
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}
*/

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
/* Te linie już są rzekomo gdzieś indziej
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);
*/

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
/*$routes->get('zegarek', 'LiveScore::zegarek');
$routes->get('live/(:num)', 'LiveScore::naZywo/$1');
$routes->get('live', 'LiveScore::naZywo');
$routes->get('test', 'LiveScore::test');*/
//$routes->get('wydarzenia','LiveScore::wydarzeniaMeczu');
//$routes->get('wydarzenia/(:num)','LiveScore::wydarzeniaMeczu/$1');
//$routes->get('/komentarz', 'LiveScore::komentarz');
$routes->get('/komentarzDoTypera', 'Typer::komentarz');
$routes->get('/nowykomentarz', 'Komentarz::post');
//$routes->get('/', 'LiveScore::index');
//$routes->get('archiwum', 'theGame::archiwum');
//$routes->get('eksperyment', 'LiveScore::eksperyment');
//$routes->get('premecz/(:num)/(:num)/(:num)','LiveScore::preMecz/$1/$2/$3');
$routes->get('/cookie', 'Kalkulator::dejCookie');
$routes->get('aktywuj/(:any)', 'Auth::confirm/$1');
$routes->get('dejnowehaslo/(:any)','Auth::newPassStart/$1');
$routes->get('przeliczMecz/(:num)','Serwisant::policzPunktyDlaMeczu/$1');
$routes->get('/mecze/(:num)/(:num)', 'JsonFileController::serveJson/$1/$2');
$routes->get('/typy/(:num)', 'JsonFileController::serveTypy/$1');
$routes->get('/zasady', 'TheGame::pokazZasady');
$routes->get('auth', 'Auth::index');
$routes->post('auth/loginUser', 'Auth::loginUser');
$routes->get('/livepoll', 'TheGame::livePoll');


// routing bardzo explicite (związane z formularzami)

$routes->get('auth/register', 'Auth::register');
$routes->post('auth/registerUser', 'Auth::registerUser');
$routes->get('auth/reset', 'Auth::reset');
$routes->post('auth/resetPassword', 'Auth::resetPassword');
$routes->post('auth/newPass', 'Auth::newPass');
$routes->post('auth/newPassSave', 'Auth::newPassSave');

//$routes->get('/', 'TheGame::testIndex');

/*
* Routing związany z byciem zalogowanym użytkownikiem
*/
    
$routes->group('', ['filter'=>'authcheck'],function($routes){
    
    $routes->get('/', 'TheGame::testIndex');
    //$routes->get('typowanie', 'Typer::theGame');
    $routes->get('wszystkieMecze', 'TheGame::wszystkieMecze');
    //$routes->get('pytanie/(:num)', 'Typer::wyswietlPytanie/$1');
    //$routes->get('theGame', 'Typer::theGame');
    $routes->get('mojepunkty','ExperimentalTyper::mojePunkty');
    //$routes->get('fazaGrupowa','Typer::fazaGrupowa');
    //$routes->get('strzelcy','ExperimentalTyper::pokazStrzelcow');
    //$routes->get('tabelaMecze','ExperimentalTyper::tabelaTylkoMecze');
    //$routes->get('tabelaPytania','ExperimentalTyper::tabelaTylkoPytania');
    //$routes->get('ileDokladnychTypow','ExperimentalTyper::ileDokladnychWynikow');
    $routes->get('hell','AdminDash::index');
    $routes->get('profil','Profil::index');
    $routes->get('profil/dolaczDoTurnieju/(:num)/(:num)', 'Profil::dolaczDoTurnieju/$1/$2');
    $routes->get('/przeliczTabele/(:num)','Tabela::tabelaGraczy/$1');
    $routes->get('/tabela', 'Tabela::index');
    $routes->get('/tabela/(:num)', 'Tabela::index/$1');
    //$routes->get('testujemy', 'TheGame::index');
    $routes->get('typowanie', 'TheGame::testIndex');
    
    //$routes->get('akordeon', 'TheGame::akordeon');
    $routes->get('archiwumturnieju', 'TheGame::archiwum');
    $routes->get('archiwalnePytania', 'TheGame::archiwumPytan');
    
    $routes->post('/jaktypowali/(:num)', 'TheGame::wygenerujTypyDlaMeczu/$1');
    $routes->get('shoutbox', 'ShoutboxController::index');
    $routes->get('shoutbox/getMessages', 'ShoutboxController::getMessages');
    $routes->post('shoutbox/postMessage', 'ShoutboxController::postMessage');  
    $routes->match(['GET', 'POST'], 'hell/przypiszUdoK', 'AdminDash::assignUserToClub');
    $routes->match(['GET', 'POST'], 'hell/usunUzK', 'AdminDash::removeUserFromClub'); 
    
    // Bo wszystko musi być jawne
    $routes->post('AdminDash/zmienAktywnyTurniej', 'AdminDash::zmienAktywnyTurniej');

    $routes->post('AdminDash/updateQuestionStatus', 'AdminDash::updateQuestionStatus');
    //$routes->get('AdminDash/zapiszMeczeTurnieju/(:num)', 'AdminDash::zapiszMeczeTurnieju/$1');
    $routes->get('AdminDash/zapiszMeczeTurnieju/(:num)/(:num)', 'AdminDash::zapiszMeczeTurnieju/$1/$2');
    $routes->post('AdminDash/dodajTurniej', 'AdminDash::dodajTurniej');
    $routes->post('AdminDash/dodajKlub', 'AdminDash::dodajKlub');
    $routes->post('AdminDash/dodajPytanie', 'AdminDash::dodajPytanie');
    $routes->post('AdminDash/assignUserToClub', 'AdminDash::assignUserToClub');
    $routes->get('AdminDash/assignUserToClubView', 'AdminDash::assignUserToClubView');
    $routes->post('AdminDash/removeUserFromClub', 'AdminDash::removeUserFromClub');
    $routes->get('AdminDash/removeUserFromClub', 'AdminDash::removeUserFromClub'); 

    $routes->post('TheGame/zapiszOdpowiedzNaPytanie', 'TheGame::zapiszOdpowiedzNaPytanie');
    $routes->post('theGame/nowyZapisTypu', 'TheGame::nowyZapisTypu');

    $routes->get('Profil/dodajMnieDoTurnieju/(:num)/(:num)', 'Profil::dodajMnieDoTurnieju/$1/$2');
    $routes->match(['GET', 'POST'], 'serwisant/zapiszWynikMeczu', 'Serwisant::zapiszWynikMeczu');
    
    
    $routes->get('hell/digest',         'AdminDash::digest');
    $routes->post('hell/digest/wyslij', 'AdminDash::wyslijDigest');
    $routes->post('hell/digest/szkic',    'AdminDash::zapiszSzkicDigest');
    $routes->post('hell/digest/podglad',  'AdminDash::podgladDigest');
    $routes->post('hell/digest/test',     'AdminDash::wyslijDigestTest');

    
    //zapisywanie preferencji
    $routes->post('profil/zapiszPreferencje', 'Profil::zapiszPreferencje');

    
    //wylogowywanie?
    $routes->get('auth/logout', 'Auth::logout');

    // a bo chcę mieć swoje maile
    $routes->get('hell/kampanie', 'AdminDash::kampanie');
    $routes->post('hell/kampanie/test', 'AdminDash::testKampania');
    $routes->post('hell/kampanie/wyslij', 'AdminDash::wyslijKampanie');
    
    // notatki
    $routes->post('AdminDash/dodajNotatke',      'AdminDash::dodajNotatke');
    $routes->post('AdminDash/ukryjNotatke/(:num)', 'AdminDash::ukryjNotatke/$1');
    
    // porządki administracyjne
    $routes->get('hell/mecze',                          'AdminDash::mecze');
    $routes->get('hell/pytania',                        'AdminDash::pytania');
    $routes->get('hell/pytania/odpowiedzi/(:num)',       'AdminDash::odpowiedziNaPytanie/$1');
    $routes->post('hell/pytania/zapiszPunkty',          'AdminDash::zapiszPunktyOdpowiedzi');
    $routes->get('hell/gracze',                         'AdminDash::gracze');
    $routes->get('hell/turnieje',                       'AdminDash::turnieje');
    $routes->match(['GET','POST'], 'wyniki',            'Serwisant::wyniki');
    $routes->post('hell/mecze/zapisz',               'AdminDash::zapiszIPrezelicz');
    // porządki w terminarzu
    $routes->get('hell/terminarz/porownaj',             'AdminDash::porownajTerminarz'); 
    $routes->post('hell/terminarz/aktualizujMecz/(:num)', 'AdminDash::aktualizujMecz/$1');
    $routes->post('hell/pytania/edytuj/(:num)', 'AdminDash::edytujPytanie/$1');

    // aktualizacja
    
    $routes->post('hell/mecze/zapisz', 'AdminDash::zapiszIPrezelicz');

    $routes->post('hell/turniej/okno24h', 'AdminDash::ustawOkno24h');

    $routes->post('profil/zapiszEmoji', 'Profil::zapiszEmoji');

    // statystyki?
    $routes->get('statystyki',          'Statystyki::turniej');
    $routes->get('statystyki/przelicz', 'Statystyki::przelicz');
    
    $routes->get('hell/generujSlug', 'AdminDash::generujSlugiBrakujace');
    $routes->get('profil/(:segment)',       'Profil::pokaz/$1');        // publiczny profil gracza
    $routes->get('wszechczasy',             'Statystyki::wszechczasy'); // tabela wszech czasów
    $routes->post('hell/turnieje/toggleWszechczasy/(:num)', 'AdminDash::toggleWszechczasy/$1');

});


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}