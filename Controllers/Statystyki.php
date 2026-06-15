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
}