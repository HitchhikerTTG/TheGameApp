<?php


namespace App\Controllers;


use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TerminarzModel;
use App\Models\TypyModel;
use App\Models\TurniejeModel;
use App\Models\KtoWCoGraModel;
use App\Libraries\Common;
$session = \Config\Services::session();


class Profil extends BaseController
{

    protected $_key;
    protected $_secret;



public function gdzieGram($userID, $wszystkieTurnieje) {
    $ktoWCoGraModel = new KtoWCoGraModel();
    $userTournaments = $ktoWCoGraModel->getUserTournaments($userID);

    $userTournamentsIDs = [];
    foreach ($userTournaments as $tournament) {
        $userTournamentsIDs[] = $tournament['turniejID'];
    }

    $segregatedTournaments = [
        'active' => null,
        'isActiveParticipant' => false, // Dodana flaga informująca o udziale w aktywnym turnieju
        'participated' => [],
        'notParticipated' => [],
    ];

    foreach ($wszystkieTurnieje as $turniej) {
        if ($turniej['Active']) {
            $segregatedTournaments['active'] = $turniej;
            // Sprawdzamy, czy użytkownik bierze udział w aktywnym turnieju
            if (in_array($turniej['ID'], $userTournamentsIDs)) {
                $segregatedTournaments['isActiveParticipant'] = true;
            }
            continue;
        }

        if (in_array($turniej['ID'], $userTournamentsIDs)) {
            $segregatedTournaments['participated'][] = $turniej;
        } else {
            $segregatedTournaments['notParticipated'][] = $turniej;
        }
    }

    return $segregatedTournaments;
}

    public function dodajMnieDoTurnieju($userID, $turniejID) {
    $ktoWCoGraModel = new \App\Models\KtoWCoGraModel();
    $userModel = model(UserModel::class);


    $result = $ktoWCoGraModel->addUserTTTournament($userID, $turniejID);

    $userModel->changeActiveTournamentFlag($userID, True);

    if ($result) {
        // Jeśli użytkownik został pomyślnie dodany do turnieju
        session()->setFlashdata('success', 'Pomyślnie dołączyłeś do turnieju. Ić typować. Go! Go! Go!');
    } else {
        // Jeśli użytkownik jest już przypisany do turnieju lub wystąpił błąd
        session()->setFlashdata('error', 'Nie udało się dołączyć do turnieju. Może już w nim uczestniczysz. Albo Wit coś z ten tegesił');
    }

    // Przekierowanie użytkownika z powrotem do strony, z której przyszedł, lub do innego miejsca
    return redirect()->to('/profil'); // Zastąp '/miejscePrzekierowania' odpowiednim URL
}

    public function zapiszPreferencje()
    {
    $userModel = model(UserModel::class);
    $loggedInUserId = session()->get('loggedInUser');

    $userModel->where('uniID', $loggedInUserId)->set([
        'notify_bet_saved' => (int)($this->request->getPost('notify_bet_saved') === 'on'),
        'notify_reminder'  => (int)($this->request->getPost('notify_reminder') === 'on'),
        'digest_optin' => (int)($this->request->getPost('digest_optin') === 'on'),

    ])->update();


    session()->setFlashdata('success', 'Preferencje zapisane.');
    return redirect()->to('/profil');
    }

    public function zapiszEmoji()
    {
    $loggedInUserId = session()->get('loggedInUser');
 
        $emoji = mb_substr(trim($this->request->getPost('emoji') ?? ''), 0, 2);

        model(\App\Models\UserModel::class)
            ->where('uniID', $loggedInUserId)
            ->set(['emoji' => $emoji])
            ->update();

        session()->setFlashdata('success', 'Emoji zapisane.');
        return redirect()->to('/profil');
    }


    public function index()
    {
        $common = new Common();
        $tournaments = $common->loadTournaments();

#        $userModel = new UserModel();   
        $userModel = model(UserModel::class);

        $loggedInUserId = session()->get('loggedInUser');
        $userInfo = $userModel->getGameUserData($loggedInUserId);
 #       $userBuilder = $userModel->builder();
 #       $userBuilder->where('activated',1);
    
        $gdzieGram = $this->gdzieGram($userInfo['id'],$tournaments);

        $data=[
        'title'=>'Profil użytkownika '.$userInfo['nick'],
        'tournaments' => $tournaments,
        'userInfo' => $userInfo,
        'gdzieGram' => $gdzieGram
        ];

        return
            view('typowanie/header', $data) 
            .view('profil/profil',$data);    
    }

    public function pokaz(string $slug)
{
    helper('slug');
    $config    = get_active_tournament_config();
    $turniejID = (int)$config['activeTournamentId'];

    $userModel = model(\App\Models\UserModel::class);
    $gracz     = $userModel->where('slug', $slug)->first();

    if (!$gracz) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    $typyModel      = model(\App\Models\TypyModel::class);
    $odpModel       = model(\App\Models\OdpowiedziModel::class);
    $statModel      = model(\App\Models\StatystykiModel::class);

    // ── Statystyki w aktywnym turnieju ──
    $pktMecze   = $typyModel->punktyZaMecze($gracz['uniID'], $turniejID);
    $pktPytania = $odpModel->punktyZaPytania($gracz['uniID'], $turniejID);
    $dokladne   = $typyModel->dokladneTrafienia($gracz['uniID'], $turniejID);
    $serie      = $statModel->obliczSerie($gracz['uniID'], $turniejID);

    // Ulubiony wynik (najczęściej trafiony typ)
    $db = \Config\Database::connect();
    $ulubionyWynik = $db->query("
        SELECT ty.HomeTyp, ty.AwayTyp, COUNT(*) AS liczba
        FROM typy ty
        JOIN terminarz t ON t.Id = ty.GameID
        WHERE ty.uniID = ? AND t.TurniejID = ? AND ty.pkt > 0
        GROUP BY ty.HomeTyp, ty.AwayTyp
        ORDER BY liczba DESC LIMIT 1
    ", [$gracz['uniID'], $turniejID])->getRow();

    // Złota piłka: użyta / trafiona
    $goldenUzyta   = (int)($db->query(
        "SELECT COUNT(*) AS n FROM typy ty JOIN terminarz t ON t.Id=ty.GameID
         WHERE ty.uniID=? AND t.TurniejID=? AND ty.GoldenGame=1 AND t.zakonczony=1",
        [$gracz['uniID'], $turniejID]
    )->getRow()->n ?? 0);
    $goldenTrafiona = (int)($db->query(
        "SELECT COUNT(*) AS n FROM typy ty JOIN terminarz t ON t.Id=ty.GameID
         WHERE ty.uniID=? AND t.TurniejID=? AND ty.GoldenGame=1 AND ty.pkt>0",
        [$gracz['uniID'], $turniejID]
    )->getRow()->n ?? 0);

    // Liczba typów łącznie (zakończone mecze)
    $liczbaTypow = (int)($db->query(
        "SELECT COUNT(*) AS n FROM typy ty JOIN terminarz t ON t.Id=ty.GameID
         WHERE ty.uniID=? AND t.TurniejID=? AND t.zakonczony=1",
        [$gracz['uniID'], $turniejID]
    )->getRow()->n ?? 0);

    // Trafienia kierunkowe (wygrał/remis/przegrał właściwie)
    $trafieniaKierunkowe = (int)($db->query(
        "SELECT COUNT(*) AS n FROM typy ty
         JOIN terminarz t ON t.Id=ty.GameID
         WHERE ty.uniID=? AND t.TurniejID=? AND t.zakonczony=1 AND ty.pkt>0",
        [$gracz['uniID'], $turniejID]
    )->getRow()->n ?? 0);

    // ── Mini wszech czasów ──
    $turniejeLiczace = $db->table('turnieje')
        ->where('liczyDoWszechczasow', 1)
        ->get()->getResultArray();
    $idsLiczace = array_column($turniejeLiczace, 'Id');

    $pktAllTime = 0;
    $turniejeGracza = 0;
    if (!empty($idsLiczace)) {
        $pktAllTime = (int)($db->query(
            "SELECT COALESCE(SUM(t.pkt),0) + COALESCE(SUM(o.pkt),0) AS suma
             FROM uzytkownicy u
             LEFT JOIN typy t ON t.uniID=u.uniID AND t.TurniejID IN (" . implode(',', $idsLiczace) . ")
             LEFT JOIN odpowiedzi o ON o.uniidOdp=u.uniID AND o.TurniejID IN (" . implode(',', $idsLiczace) . ")
             WHERE u.uniID=?",
            [$gracz['uniID']]
        )->getRow()->suma ?? 0);

        $turniejeGracza = (int)($db->query(
            "SELECT COUNT(DISTINCT TurniejID) AS n FROM typy
             WHERE uniID=? AND TurniejID IN (" . implode(',', $idsLiczace) . ")",
            [$gracz['uniID']]
        )->getRow()->n ?? 0);
    }

    // ── Pozycja w rankingu aktywnego turnieju ──
    $rankingPozycja = $statModel->getRankingPozycja($gracz['uniID'], $turniejID);

    // ── Lista meczów z punktami (za co dostał ile) ──
    $szczegolyMeczow = $db->query("
        SELECT t.Id, t.HomeName, t.AwayName, t.Date, t.ScoreHome, t.ScoreAway,
               ty.HomeTyp, ty.AwayTyp, ty.pkt, ty.GoldenGame
        FROM typy ty
        JOIN terminarz t ON t.Id = ty.GameID
        WHERE ty.uniID = ? AND t.TurniejID = ? AND t.zakonczony = 1
        ORDER BY t.Date ASC, t.Time ASC
    ", [$gracz['uniID'], $turniejID])->getResultArray();

    // ── Lista pytań z punktami ──
    $szczegolyPytan = $db->query("
        SELECT p.id, p.tresc, p.odpowiedz AS poprawna, p.pkt AS pktMax,
               o.odp AS mojaOdp, o.pkt AS pktZdobyte
        FROM odpowiedzi o
        JOIN pytania p ON p.id = o.idPyt
        WHERE o.uniidOdp = ? AND o.TurniejID = ? AND p.zamkniete = 1
        ORDER BY p.wazneDo ASC
    ", [$gracz['uniID'], $turniejID])->getResultArray();

    // ── Skuteczność / średnia punktów na mecz ──
    $skutecznoscKierunkowa = $liczbaTypow > 0 ? round($trafieniaKierunkowe / $liczbaTypow * 100, 1) : 0;
    $skutecznoscDokladna   = $liczbaTypow > 0 ? round($dokladne / $liczbaTypow * 100, 1) : 0;
    $srednioPktNaMecz      = $liczbaTypow > 0 ? round($pktMecze / $liczbaTypow, 2) : 0;

    // ── Najlepszy i najgorszy mecz (po punktach zdobytych) ──
    $najlepszyMecz = null;
    $najgorszyMecz = null;
    foreach ($szczegolyMeczow as $m) {
        if ($najlepszyMecz === null || $m['pkt'] > $najlepszyMecz['pkt']) {
            $najlepszyMecz = $m;
        }
        if ($najgorszyMecz === null || $m['pkt'] < $najgorszyMecz['pkt']) {
            $najgorszyMecz = $m;
        }
    }

    // ── Porównanie ze średnią turnieju ──
    $srednieTurnieju = $statModel->getSrednieTurnieju($turniejID);

    // ── Najczęściej wpisywany wynik gracza (niezależnie od trafienia) ──
    $najczestszyWynikGracza = $db->query("
        SELECT ty.HomeTyp, ty.AwayTyp, COUNT(*) AS liczba
        FROM typy ty
        JOIN terminarz t ON t.Id = ty.GameID
        WHERE ty.uniID = ? AND t.TurniejID = ?
        GROUP BY ty.HomeTyp, ty.AwayTyp
        ORDER BY liczba DESC LIMIT 1
    ", [$gracz['uniID'], $turniejID])->getRow();

    // ── Trend punktowy (suma narastająco, mecz po meczu) ──
    $trendPunktowy = [];
    $sumaNarastajaca = 0;
    foreach ($szczegolyMeczow as $m) {
        $sumaNarastajaca += (int)$m['pkt'];
        $trendPunktowy[] = $sumaNarastajaca;
    }


    $jaMojeKonto = (session()->get('loggedInUser') === $gracz['uniID']);

    $wstep = ['title' => esc($gracz['nick'])];

    return view('typowanie/header', $wstep)
         . view('ukladanka/sg/belkausera', [
               'daneUzytkownika' => model(\App\Models\UserModel::class)
                   ->getGameUserData(session()->get('loggedInUser'))
           ])
         . view('profil/pokaz', [
               'gracz'               => $gracz,
               'pktMecze'            => $pktMecze,
               'pktPytania'          => $pktPytania,
               'pktLacznie'          => $pktMecze + $pktPytania,
               'dokladne'            => $dokladne,
               'serie'               => $serie,
               'ulubionyWynik'       => $ulubionyWynik,
               'goldenUzyta'         => $goldenUzyta,
               'goldenTrafiona'      => $goldenTrafiona,
               'liczbaTypow'         => $liczbaTypow,
               'trafieniaKierunkowe' => $trafieniaKierunkowe,
               'rankingPozycja'      => $rankingPozycja,
               'pktAllTime'          => $pktAllTime,
               'turniejeGracza'      => $turniejeGracza,
               'turniejName'         => $config['activeTournamentName'] ?? '',
               'jaMojeKonto'         => $jaMojeKonto,
               'szczegolyMeczow'        => $szczegolyMeczow,
               'szczegolyPytan'         => $szczegolyPytan,
               'skutecznoscKierunkowa'  => $skutecznoscKierunkowa,
               'skutecznoscDokladna'    => $skutecznoscDokladna,
               'srednioPktNaMecz'       => $srednioPktNaMecz,
               'najlepszyMecz'          => $najlepszyMecz,
               'najgorszyMecz'          => $najgorszyMecz,
               'srednieTurnieju'        => $srednieTurnieju,
               'najczestszyWynikGracza' => $najczestszyWynikGracza,
               'trendPunktowy'          => $trendPunktowy,
           ])
         . view('typowanie/footer');
}

}
