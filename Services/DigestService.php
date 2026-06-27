<?php
namespace App\Services;

use App\Models\TerminarzModel;
use App\Models\TypyModel;
use App\Models\PytaniaModel;
use App\Models\OdpowiedziModel;
use App\Models\StatystykiModel;

class DigestService
{
    private TerminarzModel   $terminarzModel;
    private TypyModel        $typyModel;
    private PytaniaModel     $pytaniaModel;
    private OdpowiedziModel  $odpowiedziModel;
    private StatystykiModel  $statystykiModel;

    public function __construct()
    {
        $this->terminarzModel  = model(TerminarzModel::class);
        $this->typyModel       = model(TypyModel::class);
        $this->pytaniaModel    = model(PytaniaModel::class);
        $this->odpowiedziModel = model(OdpowiedziModel::class);
        $this->statystykiModel = model(StatystykiModel::class);
    }

    public function buildForUser(
        array  $user,
        int    $turniejID,
        string $adminKomentarz,
        string $adminKomentarz2,
        string $adminKomentarz3,
        array  $pytaniaWczorajIds = [],
        array  $pytaniaDzisiajIds = [],
        array  $najlepszyTyper = [],
        array  $najwiekszySkokGracza = []   // ← nowe
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
            'wszystkiePkt'    => $this->statystykiModel->getAllPoints($user['uniID'], $turniejID),
            'rankingPozycja'  => $this->statystykiModel->getRankingPozycja($user['uniID'], $turniejID),
            'adminKomentarz'  => $adminKomentarz,
            'adminKomentarz2' => $adminKomentarz2,
            'adminKomentarz3' => $adminKomentarz3,
            'najlepszyTyper'  => $najlepszyTyper,
            'najwiekszySkokGracza' => $najwiekszySkokGracza,
            
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
    
public function getNajlepszyTyper(int $turniejID, array $pytaniaWczorajIds): array
{
    $meczIds = array_column(
        $this->terminarzModel->getMeczeZakonczone24h($turniejID),
        'Id'
    );

    $punktyGraczy = [];

    foreach ($this->typyModel->punktyZaMeczeGraczy($meczIds) as $row) {
        $id = $row['uniID'];
        $punktyGraczy[$id] = [
            'nick'       => $row['nick'] ?? $id,
            'pktMecze'   => (int)$row['pkt'],
            'pktPytania' => 0,
        ];
    }

    foreach ($this->odpowiedziModel->punktyZaPytanGraczy($pytaniaWczorajIds) as $row) {
        $id = $row['uniID'];
        if (isset($punktyGraczy[$id])) {
            $punktyGraczy[$id]['pktPytania'] = (int)$row['pkt'];
        } else {
            $punktyGraczy[$id] = ['nick' => $id, 'pktMecze' => 0, 'pktPytania' => (int)$row['pkt']];
        }
    }

    if (empty($punktyGraczy)) return [];

    $maxPkt = max(array_map(fn($g) => $g['pktMecze'] + $g['pktPytania'], $punktyGraczy));

    $winners = array_values(array_filter(
        $punktyGraczy,
        fn($g) => ($g['pktMecze'] + $g['pktPytania']) === $maxPkt
    ));

    return [
        'nicki'      => array_column($winners, 'nick'),
        'pkt'        => $maxPkt,
        'pktMecze'   => $winners[0]['pktMecze'],
        'pktPytania' => $winners[0]['pktPytania'],
    ];
}

public function getNajwiekszySkokPozycji(int $turniejID): array
{
    $histPath = WRITEPATH . "gracze/historia_pozycji_{$turniejID}.json";
    if (!file_exists($histPath)) return [];

    $historia = json_decode(file_get_contents($histPath), true) ?? [];
    if (empty($historia)) return [];

    // ── Wczorajsze mecze: to samo okno 24h co reszta digestu ──
    $wczorajszeMecze = $this->terminarzModel->getMeczeZakonczone24h($turniejID);
    if (empty($wczorajszeMecze)) return [];

    // ── Snapshot PO: ostatni wczorajszy mecz, który ma snapshot ──
    $snapPo = null;
    for ($i = count($wczorajszeMecze) - 1; $i >= 0; $i--) {
        $id = $wczorajszeMecze[$i]['Id'];
        if (isset($historia[$id])) {
            $snapPo = $historia[$id];
            break;
        }
    }
    if ($snapPo === null) return [];

    // ── Snapshot SPRZED: ostatni zakończony mecz przed pierwszym wczorajszym ──
    $pierwszy   = $wczorajszeMecze[0];
    $przedMecze = \Config\Database::connect()
        ->table('terminarz')
        ->select('Id')
        ->where('TurniejID', $turniejID)
        ->where('zakonczony', 1)
        ->where("CONCAT(Date, ' ', Time) <", $pierwszy['Date'] . ' ' . $pierwszy['Time'])
        ->orderBy('Date', 'DESC')->orderBy('Time', 'DESC')
        ->get()->getResultArray();

    $snapSprzed = null;
    foreach ($przedMecze as $m) {
        if (isset($historia[$m['Id']])) {
            $snapSprzed = $historia[$m['Id']];
            break;
        }
    }

    // ── Pozycje bazowe (brak punktu odniesienia → wszyscy z ostatniego miejsca) ──
    $pozPo = $snapPo['pozycje'];
    if ($snapSprzed !== null && !empty($snapSprzed['pozycje'])) {
        $bazaPozycje         = $snapSprzed['pozycje'];
        $ostatnieMiejsceBaza = max($bazaPozycje);
    } else {
        $bazaPozycje         = [];
        $ostatnieMiejsceBaza = max($pozPo ?: [1]);
    }

    // ── Największy awans w górę tabeli ──
    $maxSkok   = 0;
    $zwyciezca = null;
    foreach ($pozPo as $uid => $pozAktualna) {
        $pozSprzed = $bazaPozycje[$uid] ?? $ostatnieMiejsceBaza;
        $skok      = $pozSprzed - $pozAktualna;     // dodatni = awans
        if ($skok > $maxSkok) {
            $maxSkok   = $skok;
            $zwyciezca = [
                'uniID'       => $uid,
                'skok'        => $skok,
                'pozAktualna' => $pozAktualna,
                'pozSprzed'   => $pozSprzed,
            ];
        }
    }

    if (!$zwyciezca) return [];

    // ── Nick/emoji z leaderboard cache (szybsze niż DB) ──
    $tabelaPath = WRITEPATH . "tabelaGraczy_{$turniejID}.json";
    if (file_exists($tabelaPath)) {
        foreach (json_decode(file_get_contents($tabelaPath), true) ?? [] as $w) {
            if (($w['uniID'] ?? '') === $zwyciezca['uniID']) {
                $zwyciezca['nick']  = $w['nick'];
                $zwyciezca['emoji'] = $w['emoji'] ?? '';
                break;
            }
        }
    }
    if (!isset($zwyciezca['nick'])) {
        $g = model(\App\Models\UserModel::class)->select('nick,emoji')->where('uniID', $zwyciezca['uniID'])->first();
        $zwyciezca['nick']  = $g['nick']  ?? $zwyciezca['uniID'];
        $zwyciezca['emoji'] = $g['emoji'] ?? '';
    }

    return $zwyciezca;
}

}