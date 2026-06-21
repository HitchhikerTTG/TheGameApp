<?php
namespace App\Controllers;

use App\Models\StatystykiModel;

class Statystyki extends BaseController
{
    private StatystykiModel $statModel;

    public function __construct()
    {
        $this->statModel = model(StatystykiModel::class);
    }

    public function turniej()
    {
        $config    = get_active_tournament_config();
        $turniejID = (int)$config['activeTournamentId'];

        // Czytamy z cache; jeśli brak -- liczymy na żądanie
        $statystyki = $this->statModel->odczytajCache($turniejID)
                   ?? $this->statModel->przeliczIZapiszCache($turniejID);

        $wstep = ['title' => 'Statystyki · ' . ($config['activeTournamentName'] ?? '')];

        return view('typowanie/header', $wstep)
             . view('ukladanka/sg/belkausera', [
                   'daneUzytkownika' => model(\App\Models\UserModel::class)
                       ->getGameUserData(session()->get('loggedInUser'))
               ])
             . view('statystyki/turniej', [
                   'statystyki' => $statystyki,
                   'turniejName' => $config['activeTournamentName'] ?? '',
               ])
             . view('typowanie/footer');
    }

    // Wywoływane przez admina po przeliczeniu punktów
    public function przelicz()
    {
        if (!session()->get('isAdmin')) {
            return redirect()->to('/statystyki');
        }
        $config    = get_active_tournament_config();
        $turniejID = (int)$config['activeTournamentId'];
        $this->statModel->przeliczIZapiszCache($turniejID);
        session()->setFlashdata('success', 'Statystyki przeliczone.');
        return redirect()->to('/statystyki');
    }
    
    public function wszechczasy()
{
    $db = \Config\Database::connect();

    $turniejeLiczace = $db->table('turnieje')
        ->where('liczyDoWszechczasow', 1)
        ->get()->getResultArray();

    $idsLiczace = array_column($turniejeLiczace, 'Id');

    $ranking = [];
    if (!empty($idsLiczace)) {
        $in = implode(',', array_map('intval', $idsLiczace));
        $ranking = $db->query("
            SELECT u.nick, u.emoji, u.slug,
                   COALESCE(SUM(ty.pkt), 0) + COALESCE(SUM(o.pkt), 0)   AS pkt,
                   COALESCE(SUM(ty.pkt), 0)                               AS pktMecze,
                   COALESCE(SUM(o.pkt), 0)                                AS pktPytania,
                   COUNT(DISTINCT ty.TurniejID)                            AS turnieje,
                   COUNT(CASE WHEN ty.pkt = 3 THEN 1 END)                 AS dokladne
            FROM uzytkownicy u
            LEFT JOIN typy ty      ON ty.uniID    = u.uniID AND ty.TurniejID IN ({$in})
            LEFT JOIN odpowiedzi o ON o.uniidOdp  = u.uniID AND o.TurniejID  IN ({$in})
            WHERE u.activated = 1
            GROUP BY u.uniID
            HAVING turnieje > 0
            ORDER BY pkt DESC, dokladne DESC
        ")->getResultArray();
    }

    $config = get_active_tournament_config();
    $wstep  = ['title' => 'Wszech czasów'];

    return view('typowanie/header', $wstep)
         . view('ukladanka/sg/belkausera', [
               'daneUzytkownika' => model(\App\Models\UserModel::class)
                   ->getGameUserData(session()->get('loggedInUser'))
           ])
         . view('statystyki/wszechczasy', [
               'ranking'         => $ranking,
               'turniejeLiczace' => $turniejeLiczace,
               'loggedInUniID'   => session()->get('loggedInUser'),
           ])
         . view('typowanie/footer');
}
public function pojedynek()
{
    $slug1 = $this->request->getGet('g1') ?? '';
    $slug2 = $this->request->getGet('g2') ?? '';

    $config    = get_active_tournament_config();
    $turniejID = (int)$config['activeTournamentId'];
    $db        = \Config\Database::connect();
    $userModel = model(\App\Models\UserModel::class);

    $loggedInUniID = session()->get('loggedInUser');
    $loggedIn      = $userModel->where('uniID', $loggedInUniID)->first();

    if (empty($slug1) && $loggedIn) {
        $slug1 = $loggedIn['slug'] ?? '';
    }

    $gracz1 = $slug1 ? $userModel->where('slug', $slug1)->first() : null;
    $gracz2 = $slug2 ? $userModel->where('slug', $slug2)->first() : null;

    $gracze = $db->query("
        SELECT u.nick, u.emoji, u.slug
        FROM uzytkownicy u
        JOIN ktowcogra k ON k.userID = u.uniID AND k.turniejID = ?
        WHERE u.activated = 1
        ORDER BY u.nick ASC
    ", [$turniejID])->getResultArray();

    $porownanie = [];
    if ($gracz1 && $gracz2) {
        $rows = $db->query("
            SELECT t.Id, t.HomeName, t.AwayName, t.Date, t.ScoreHome, t.ScoreAway,
                   ty1.HomeTyp AS g1HomeTyp, ty1.AwayTyp AS g1AwayTyp,
                   COALESCE(ty1.pkt, 0) AS g1Pkt, ty1.GoldenGame AS g1Golden,
                   ty2.HomeTyp AS g2HomeTyp, ty2.AwayTyp AS g2AwayTyp,
                   COALESCE(ty2.pkt, 0) AS g2Pkt, ty2.GoldenGame AS g2Golden
            FROM terminarz t
            LEFT JOIN typy ty1 ON ty1.GameID = t.Id AND ty1.uniID = ?
            LEFT JOIN typy ty2 ON ty2.GameID = t.Id AND ty2.uniID = ?
            WHERE t.TurniejID = ? AND t.zakonczony = 1
            ORDER BY t.Date ASC, t.Time ASC
        ", [$gracz1['uniID'], $gracz2['uniID'], $turniejID])->getResultArray();

        $g1Sum = 0; $g2Sum = 0;
        foreach ($rows as &$row) {
            $g1Sum += (int)$row['g1Pkt'];
            $g2Sum += (int)$row['g2Pkt'];
            $row['g1Sum'] = $g1Sum;
            $row['g2Sum'] = $g2Sum;
        }
        unset($row);
        $porownanie = $rows;
    }

    $wstep = ['title' => 'Pojedynek'];
    return view('typowanie/header', $wstep)
         . view('ukladanka/sg/belkausera', [
               'daneUzytkownika' => $userModel->getGameUserData($loggedInUniID)
           ])
         . view('statystyki/pojedynek', [
               'gracz1'      => $gracz1,
               'gracz2'      => $gracz2,
               'gracze'      => $gracze,
               'porownanie'  => $porownanie,
               'turniejName' => $config['activeTournamentName'] ?? '',
               'slug1'       => $slug1,
               'slug2'       => $slug2,
           ])
         . view('typowanie/footer');
}
}