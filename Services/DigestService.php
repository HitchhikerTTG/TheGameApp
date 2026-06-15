<?php
namespace App\Services;

use App\Models\TerminarzModel;
use App\Models\TypyModel;
use App\Models\PytaniaModel;
use App\Models\OdpowiedziModel;

class DigestService
{
    private TerminarzModel   $terminarzModel;
    private TypyModel        $typyModel;
    private PytaniaModel     $pytaniaModel;
    private OdpowiedziModel  $odpowiedziModel;

    public function __construct()
    {
        $this->terminarzModel  = model(TerminarzModel::class);
        $this->typyModel       = model(TypyModel::class);
        $this->pytaniaModel    = model(PytaniaModel::class);
        $this->odpowiedziModel = model(OdpowiedziModel::class);
    }

    public function buildForUser(
        array  $user,
        int    $turniejID,
        string $adminKomentarz,
        string $adminKomentarz2,
        string $adminKomentarz3,
        array  $pytaniaWczorajIds = [],
        array  $pytaniaDzisiajIds = []
    ): array {
        $wczorajMecze   = $this->getWczorajszeMecze($user, $turniejID);
        $dzisiajMecze   = $this->getDzisiajszeMecze($user, $turniejID);
        $wczorajPytania = $this->getWczorajszePytania($user, $pytaniaWczorajIds);
        $dzisiajPytania = $this->getDzisiajszePytania($user, $pytaniaDzisiajIds);

        $wczorajPkt = array_sum(array_column($wczorajMecze, 'pkt'))
                    + array_sum(array_column($wczorajPytania, 'pkt'));

        return [
            'nick'            => $user['nick'],
            'wczorajMecze'    => $wczorajMecze,
            'wczorajPytania'  => $wczorajPytania,
            'dzisiajMecze'    => $dzisiajMecze,
            'dzisiajPytania'  => $dzisiajPytania,
            'wczorajPkt'      => $wczorajPkt,
            'wszystkiePkt'    => $this->getAllPoints($user['uniID'], $turniejID),
            'rankingPozycja'  => $this->getRankingPozycja($user['uniID'], $turniejID),
            'adminKomentarz'  => $adminKomentarz,
            'adminKomentarz2' => $adminKomentarz2,
            'adminKomentarz3' => $adminKomentarz3,
        ];
    }

    // ── prywatne metody ──────────────────────────────────────────────

    private function getWczorajszeMecze(array $user, int $turniejID): array
    {
        $mecze = \Config\Database::connect()
            ->table('terminarz')
            ->where('TurniejID', $turniejID)
            ->where('zakonczony', 1)
            ->where("CONCAT(Date, ' ', Time) >=", date('Y-m-d H:i:s', strtotime('-1 day')))
            ->where("CONCAT(Date, ' ', Time) <",  date('Y-m-d H:i:s'))
            ->orderBy('Date', 'ASC')->orderBy('Time', 'ASC')
            ->get()->getResultArray();

        $wynik = [];
        foreach ($mecze as $mecz) {
            $jsonPath = WRITEPATH . "mecze/{$turniejID}/{$mecz['ApiID']}.json";
            $details  = file_exists($jsonPath) ? (json_decode(file_get_contents($jsonPath), true) ?? []) : [];
            $typ      = $this->typyModel->getTypyByMeczIdAndUserId($mecz['Id'], $user['uniID']);

            $wynik[] = [
                'homeName'  => $details['home_team']['plName'] ?? $details['home_team']['name'] ?? $mecz['HomeName'],
                'awayName'  => $details['away_team']['plName'] ?? $details['away_team']['name'] ?? $mecz['AwayName'],
                'homeScore' => (int)$mecz['ScoreHome'],
                'awayScore' => (int)$mecz['ScoreAway'],
                'userHome'  => $typ['HomeTyp'] ?? null,
                'userAway'  => $typ['AwayTyp'] ?? null,
                'pkt'       => (int)($typ['pkt'] ?? 0),
                'isGolden'  => !empty($typ['GoldenGame']),
            ];
        }
        return $wynik;
    }

    private function getDzisiajszeMecze(array $user, int $turniejID): array
    {
        $mecze = \Config\Database::connect()
            ->table('terminarz')
            ->where('TurniejID', $turniejID)
            ->where('zakonczony', 0)
            ->where('Rozpoczety', 0)
            ->where("CONCAT(Date, ' ', Time) >=", date('Y-m-d H:i:s'))
            ->where("CONCAT(Date, ' ', Time) <=", date('Y-m-d H:i:s', strtotime('+24 hours')))
            ->orderBy('Date', 'ASC')->orderBy('Time', 'ASC')
            ->get()->getResultArray();

        $wynik = [];
        foreach ($mecze as $mecz) {
            $jsonPath = WRITEPATH . "mecze/{$turniejID}/{$mecz['ApiID']}.json";
            $details  = file_exists($jsonPath) ? (json_decode(file_get_contents($jsonPath), true) ?? []) : [];
            $typ      = $this->typyModel->getTypyByMeczIdAndUserId($mecz['Id'], $user['uniID']);
            $naszCzas = isset($details['naszCzas']) ? substr($details['naszCzas'], 0, 5) : substr($mecz['Time'], 0, 5) . ' UTC';

            $wynik[] = [
                'homeName' => $details['home_team']['plName'] ?? $details['home_team']['name'] ?? $mecz['HomeName'],
                'awayName' => $details['away_team']['plName'] ?? $details['away_team']['name'] ?? $mecz['AwayName'],
                'naszCzas' => $naszCzas,
                'userHome' => $typ['HomeTyp'] ?? null,
                'userAway' => $typ['AwayTyp'] ?? null,
                'hasTyp'   => $typ !== null,
                'isGolden' => !empty($typ['GoldenGame']),
            ];
        }
        return $wynik;
    }

    private function getWczorajszePytania(array $user, array $ids): array
    {
        if (empty($ids)) return [];

        $pytania = $this->pytaniaModel->whereIn('id', $ids)->findAll();
        $wynik   = [];

        foreach ($pytania as $p) {
            $odp = $this->odpowiedziModel
                ->where('idPyt', $p['id'])
                ->where('uniidOdp', $user['uniID'])
                ->first();

            $wynik[] = [
                'tresc'      => $p['tresc'],
                'odpowiedz'  => $p['odpowiedz'] ?? null,   // prawidłowa
                'userOdp'    => $odp['odp'] ?? null,        // odpowiedź gracza
                'pkt'        => (int)($odp['pkt'] ?? 0),
            ];
        }
        return $wynik;
    }

    private function getDzisiajszePytania(array $user, array $ids): array
    {
        if (empty($ids)) return [];

        $pytania = $this->pytaniaModel->whereIn('id', $ids)->findAll();
        $wynik   = [];

        foreach ($pytania as $p) {
            $odp = $this->odpowiedziModel
                ->where('idPyt', $p['id'])
                ->where('uniidOdp', $user['uniID'])
                ->first();

            $wynik[] = [
                'tresc'   => $p['tresc'],
                'opis'    => $p['opis'] ?? null,
                'zrodlo'  => $p['zrodlo'] ?? null,
                'pkt'     => (int)$p['pkt'],
                'userOdp' => $odp['odp'] ?? null,
                'hasOdp'  => $odp !== null,
                'wazneDo' => $p['wazneDo'],
            ];
        }
        return $wynik;
    }

    private function getAllPoints(string $userUniID, int $turniejID): int
    {
        $db = \Config\Database::connect();

        $pktMecze = (int)($db->table('typy')
            ->selectSum('pkt')
            ->where('uniID', $userUniID)
            ->where('TurniejID', $turniejID)
            ->get()->getRow()->pkt ?? 0);

        $pktPytania = (int)($db->table('odpowiedzi')
            ->selectSum('pkt')
            ->where('uniidOdp', $userUniID)
            ->where('TurniejID', $turniejID)
            ->get()->getRow()->pkt ?? 0);

        return $pktMecze + $pktPytania;
    }

    private function getRankingPozycja(string $userUniID, int $turniejID): int
    {
        $db = \Config\Database::connect();

        // Policz ilu graczy ma więcej punktów łącznie
        $myPkt = $this->getAllPoints($userUniID, $turniejID);

        // Podzapytanie: suma punktów dla każdego gracza
        $sql = "
            SELECT COUNT(*) AS lepszyGraczy
            FROM (
                SELECT u.uniID,
                       COALESCE(SUM(t.pkt), 0) + COALESCE(SUM(o.pkt), 0) AS suma
                FROM uzytkownicy u
                LEFT JOIN typy     t ON t.uniID    = u.uniID AND t.TurniejID    = ?
                LEFT JOIN odpowiedzi o ON o.uniidOdp = u.uniID AND o.TurniejID = ?
                WHERE u.PlaysTheActiveTournament = 1
                GROUP BY u.uniID
            ) AS ranking
            WHERE ranking.suma > ?
        ";

        $result = $db->query($sql, [$turniejID, $turniejID, $myPkt])->getRow();
        return (int)($result->lepszyGraczy ?? 0) + 1;
    }
}