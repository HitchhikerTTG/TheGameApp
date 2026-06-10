<?php
namespace App\Services;

use App\Models\TerminarzModel;
use App\Models\TypyModel;
use App\Models\PytaniaModel;

class DigestService
{
    private TerminarzModel $terminarzModel;
    private TypyModel $typyModel;
    private PytaniaModel $pytaniaModel;

    public function __construct()
    {
        $this->terminarzModel = model(TerminarzModel::class);
        $this->typyModel      = model(TypyModel::class);
        $this->pytaniaModel   = model(PytaniaModel::class);
    }

    public function buildForUser(array $user, int $turniejID, string $adminKomentarz, string $adminKomentarz2, string $adminKomentarz3 ): array
    {
        $wczorajMecze = $this->getWczorajszeMecze($user, $turniejID);
        $dzisiajMecze = $this->getDzisiajszeMecze($user, $turniejID);
        $pytanie      = $this->getAktywnePytanie($turniejID);

        return [
            'nick'          => $user['nick'],
            'wczorajMecze'  => $wczorajMecze,
            'wczorajPkt'    => array_sum(array_column($wczorajMecze, 'pkt')),
            'dzisiajMecze'  => $dzisiajMecze,
            'pytanie'       => $pytanie,
            'adminKomentarz'=> $adminKomentarz,
            'adminKomentarz2'=> $adminKomentarz2,
            'adminKomentarz3'=> $adminKomentarz3,
        ];
    }

    private function getWczorajszeMecze(array $user, int $turniejID): array
    {
        // zakonczony=1 i data z ostatniej doby (UTC)
        $mecze = \Config\Database::connect()
            ->table('terminarz')
            ->where('TurniejID', $turniejID)
            ->where('zakonczony', 1)
            ->where('Date >=', date('Y-m-d', strtotime('-1 day')))
            ->where('Date <=', date('Y-m-d'))
            ->orderBy('Date', 'ASC')
            ->orderBy('Time', 'ASC')
            ->get()->getResultArray();

        $wynik = [];
        foreach ($mecze as $mecz) {
            $jsonPath = WRITEPATH . "mecze/{$turniejID}/{$mecz['ApiID']}.json";
            $details  = file_exists($jsonPath)
                ? (json_decode(file_get_contents($jsonPath), true) ?? [])
                : [];

            $typ = $this->typyModel->getTypyByMeczIdAndUserId($mecz['Id'], $user['uniID']);

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
        // Mecze z kickoffem w ciągu najbliższych 24h (UTC)
        $mecze = \Config\Database::connect()
            ->table('terminarz')
            ->where('TurniejID', $turniejID)
            ->where('zakonczony', 0)
            ->where('Rozpoczety', 0)
            ->where("CONCAT(Date, ' ', Time) >=", date('Y-m-d H:i:s'))
            ->where("CONCAT(Date, ' ', Time) <=", date('Y-m-d H:i:s', strtotime('+24 hours')))
            ->orderBy('Date', 'ASC')
            ->orderBy('Time', 'ASC')
            ->get()->getResultArray();

        $wynik = [];
        foreach ($mecze as $mecz) {
            $jsonPath = WRITEPATH . "mecze/{$turniejID}/{$mecz['ApiID']}.json";
            $details  = file_exists($jsonPath)
                ? (json_decode(file_get_contents($jsonPath), true) ?? [])
                : [];

            $typ = $this->typyModel->getTypyByMeczIdAndUserId($mecz['Id'], $user['uniID']);

            // naszCzas z JSON = czas w Europe/Warsaw (H:i:s)
            $naszCzas = isset($details['naszCzas'])
                ? substr($details['naszCzas'], 0, 5)
                : substr($mecz['Time'], 0, 5) . ' UTC';

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

    private function getAktywnePytanie(int $turniejID): ?array
    {
        $pytania = $this->pytaniaModel->getActiveQuestions($turniejID);
        return !empty($pytania) ? $pytania[0] : null;
    }
}
