    <?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('zegarek', 'LiveScore::zegarek');
$routes->get('live/(:num)', 'LiveScore::naZywo/$1');
$routes->get('aktywuj/(:any)', 'Auth::confirm/$1');
$routes->get('dejnowehaslo/(:any)','Auth::newPassStart/$1');
$routes->get('live', 'LiveScore::naZywo');
$routes->get('test', 'LiveScore::test');
$routes->get('wydarzenia','LiveScore::wydarzeniaMeczu');
$routes->get('wydarzenia/(:num)','LiveScore::wydarzeniaMeczu/$1');
$routes->get('/komentarz', 'LiveScore::komentarz');
$routes->get('/komentarzDoTypera', 'Typer::komentarz');
$routes->get('/nowykomentarz', 'Komentarz::post');
$routes->get('/zasady', 'Typer::pokazZasady');
$routes->get('/', 'LiveScore::index');
$routes->get('archiwum', 'Archiwum::index');
$routes->get('eksperyment', 'LiveScore::eksperyment');
$routes->get('premecz/(:num)/(:num)/(:num)','LiveScore::preMecz/$1/$2/$3');
$routes->get('przeliczMecz/(:num)','Serwisant::policzPunktyDlaMeczu/$1');
$routes->get('/mecze/(:num)/(:num)', 'JsonFileController::serveJson/$1/$2');
$routes->get('/typy/(:num)', 'JsonFileController::serveTypy/$1');
$routes->get('/cookie', 'Kalkulator::dejCookie');




/*
* Routing związany z byciem zalogowanym użytkownikiem
*/
    
$routes->group('', ['filter'=>'authcheck'],function($routes){
    $routes->get('typowanie', 'Typer::theGame');
    $routes->get('wszystkieMecze', 'Typer::wszystkieMecze');
    $routes->get('pytanie/(:num)', 'Typer::wyswietlPytanie/$1');
    $routes->get('theGame', 'Typer::theGame');
    $routes->get('mojepunkty','ExperimentalTyper::mojePunkty');
    $routes->get('fazaGrupowa','Typer::fazaGrupowa');
    $routes->get('strzelcy','ExperimentalTyper::pokazStrzelcow');
    $routes->get('tabelaMecze','ExperimentalTyper::tabelaTylkoMecze');
    $routes->get('tabelaPytania','ExperimentalTyper::tabelaTylkoPytania');
    $routes->get('ileDokladnychTypow','ExperimentalTyper::ileDokladnychWynikow');
    $routes->get('hell','AdminDash::index');
    $routes->get('profil','Profil::index');
    $routes->get('profil/dolaczDoTurnieju/(:num)/(:num)', 'Profil::dolaczDoTurnieju/$1/$2');
    $routes->get('/przeliczTabele/(:num)','Tabela::tabelaGraczy/$1');
    $routes->get('/tabela', 'Tabela::index');
    $routes->get('/tabela/(:num)', 'Tabela::index/$1');
    $routes->get('testujemy', 'TheGame::index');
    $routes->get('nowytest', 'TheGame::testIndex');
    $routes->get('akordeon', 'TheGame::akordeon');
    $routes->get('archiwumturnieju', 'TheGame::archiwum');
    $routes->post('/jaktypowali/(:num)', 'TheGame::wygenerujTypyDlaMeczu/$1');
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
