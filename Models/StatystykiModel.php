<?php
namespace App\Models;

use CodeIgniter\Model;

class StatystykiModel extends Model
{
    protected $table = 'terminarz';

    // ────────────────────────────────────────────────────────────────
    // STATYSTYKI TURNIEJU -- wszystkie naraz, zwraca tablicę do cache
    // ────────────────────────────────────────────────────────────────
    public function obliczStatystykiTurnieju(int $turniejID): array
    {
        $db = \Config\Database::connect();

        // 1. Mecz z największą liczbą typów
        $meczNajwiecejTypow = $db->query("
            SELECT t.Id, t.HomeName, t.AwayName, t.ScoreHome, t.ScoreAway,
                   COUNT(ty.id) AS liczba
            FROM terminarz t
            JOIN typy ty ON ty.GameID = t.Id
            WHERE t.TurniejID = ? AND t.zakonczony = 1
            GROUP BY t.Id
            ORDER BY liczba DESC LIMIT 1
        ", [$turniejID])->getRow();

        // 2. Mecz z największą liczbą dokładnych trafień
        $meczNajwiecejTrafien = $db->query("
            SELECT t.Id, t.HomeName, t.AwayName, t.ScoreHome, t.ScoreAway,
                   COUNT(ty.id) AS liczba
            FROM terminarz t
            JOIN typy ty ON ty.GameID = t.Id AND ty.pkt > 0
            WHERE t.TurniejID = ? AND t.zakonczony = 1
            GROUP BY t.Id
            ORDER BY liczba DESC LIMIT 1
        ", [$turniejID])->getRow();

        // 3. Najpopularniejszy trafiony wynik (across wszystkie mecze)
        $najpopularniejszyTrafiony = $db->query("
            SELECT ty.HomeTyp, ty.AwayTyp, COUNT(*) AS liczba
            FROM typy ty
            JOIN terminarz t ON t.Id = ty.GameID
            WHERE t.TurniejID = ? AND t.zakonczony = 1 AND ty.pkt > 0
            GROUP BY ty.HomeTyp, ty.AwayTyp
            ORDER BY liczba DESC LIMIT 1
        ", [$turniejID])->getRow();

        // 4. Najpopularniejszy nie trafiony wynik
        $najpopularniejszyNieTrafiony = $db->query("
            SELECT ty.HomeTyp, ty.AwayTyp, COUNT(*) AS liczba
            FROM typy ty
            JOIN terminarz t ON t.Id = ty.GameID
            WHERE t.TurniejID = ? AND t.zakonczony = 1 AND ty.pkt = 0
            GROUP BY ty.HomeTyp, ty.AwayTyp
            ORDER BY liczba DESC LIMIT 1
        ", [$turniejID])->getRow();

        // 5. Wynik który zastosowany do wszystkich meczów dałby najwięcej pkt
        // = wynik końcowy który pojawił się najczęściej w historii meczów
        $najskuteczniejszyWynik = $db->query("
            SELECT ScoreHome, ScoreAway, COUNT(*) AS liczba
            FROM terminarz
            WHERE TurniejID = ? AND zakonczony = 1
            GROUP BY ScoreHome, ScoreAway
            ORDER BY liczba DESC LIMIT 1
        ", [$turniejID])->getRow();

        // 6. Rozkład: ile meczów skończyło się z 0/1/2+ dokładnymi trafieniami
        $rozkładTrafien = $db->query("
            SELECT hits, COUNT(*) AS meczow
            FROM (
                SELECT t.Id, COUNT(ty.id) AS hits
                FROM terminarz t
                LEFT JOIN typy ty ON ty.GameID = t.Id AND ty.pkt > 0
                WHERE t.TurniejID = ? AND t.zakonczony = 1
                GROUP BY t.Id
            ) sub
            GROUP BY hits
            ORDER BY hits ASC
        ", [$turniejID])->getResultArray();

        $rozkładMap = [];
        foreach ($rozkładTrafien as $r) {
            $rozkładMap[(int)$r['hits']] = (int)$r['meczow'];
        }

        // 7. Mecz który przyznał łącznie najwięcej punktów graczom
        $meczNajwiecejPkt = $db->query("
            SELECT t.Id, t.HomeName, t.AwayName, t.ScoreHome, t.ScoreAway,
                   SUM(ty.pkt) AS suma
            FROM terminarz t
            JOIN typy ty ON ty.GameID = t.Id
            WHERE t.TurniejID = ? AND t.zakonczony = 1
            GROUP BY t.Id
            ORDER BY suma DESC LIMIT 1
        ", [$turniejID])->getRow();

        // 8. Mecz w którym użyto najwięcej złotych piłek
        $meczNajwiecejGoldenUzytych = $db->query("
            SELECT t.Id, t.HomeName, t.AwayName, t.ScoreHome, t.ScoreAway,
                   COUNT(ty.id) AS liczba
            FROM terminarz t
            JOIN typy ty ON ty.GameID = t.Id AND ty.GoldenGame = 1
            WHERE t.TurniejID = ? AND t.zakonczony = 1
            GROUP BY t.Id
            ORDER BY liczba DESC LIMIT 1
        ", [$turniejID])->getRow();

        // 9. Mecz w którym trafiono najwięcej złotych piłek
        $meczNajwiecejGoldenTrafiony = $db->query("
            SELECT t.Id, t.HomeName, t.AwayName, t.ScoreHome, t.ScoreAway,
                   COUNT(ty.id) AS liczba
            FROM terminarz t
            JOIN typy ty ON ty.GameID = t.Id AND ty.GoldenGame = 1 AND ty.pkt > 0
            WHERE t.TurniejID = ? AND t.zakonczony = 1
            GROUP BY t.Id
            ORDER BY liczba DESC LIMIT 1
        ", [$turniejID])->getRow();

        // 10. Pytanie na które poprawnie odpowiedziało najwięcej graczy
        $pytanieNajwiecejPoprawnych = $db->query("
            SELECT p.id, p.tresc, p.odpowiedz,
                   COUNT(o.id) AS poprawnych,
                   (SELECT COUNT(*) FROM odpowiedzi WHERE idPyt = p.id) AS wszystkich
            FROM pytania p
            JOIN odpowiedzi o ON o.idPyt = p.id AND o.pkt > 0
            WHERE p.TurniejID = ?
            GROUP BY p.id
            ORDER BY poprawnych DESC LIMIT 1
        ", [$turniejID])->getRow();

        // 11. Pytanie na które poprawnie odpowiedziało najmniej graczy
        // (tylko pytania z minimum 1 odpowiedzią łącznie, czyli zamknięte)
        $pytanieNajmniejPoprawnych = $db->query("
            SELECT p.id, p.tresc, p.odpowiedz,
                   COUNT(CASE WHEN o.pkt > 0 THEN 1 END) AS poprawnych,
                   COUNT(o.id) AS wszystkich
            FROM pytania p
            JOIN odpowiedzi o ON o.idPyt = p.id
            WHERE p.TurniejID = ? AND p.aktywne = 0
            GROUP BY p.id
            HAVING wszystkich > 0
            ORDER BY poprawnych ASC LIMIT 1
        ", [$turniejID])->getRow();

        return [
            'meczNajwiecejTypow'           => $this->rowToArray($meczNajwiecejTypow),
            'meczNajwiecejTrafien'         => $this->rowToArray($meczNajwiecejTrafien),
            'najpopularniejszyTrafiony'    => $this->rowToArray($najpopularniejszyTrafiony),
            'najpopularniejszyNieTrafiony' => $this->rowToArray($najpopularniejszyNieTrafiony),
            'najskuteczniejszyWynik'       => $this->rowToArray($najskuteczniejszyWynik),
            'rozkładTrafien'              => $rozkładMap,
            'meczNajwiecejPkt'            => $this->rowToArray($meczNajwiecejPkt),
            'meczNajwiecejGoldenUzytych'  => $this->rowToArray($meczNajwiecejGoldenUzytych),
            'meczNajwiecejGoldenTrafiony' => $this->rowToArray($meczNajwiecejGoldenTrafiony),
            'pytanieNajwiecejPoprawnych'  => $this->rowToArray($pytanieNajwiecejPoprawnych),
            'pytanieNajmniejPoprawnych'   => $this->rowToArray($pytanieNajmniejPoprawnych),
            'obliczoneAt'                 => date('Y-m-d H:i:s'),
        ];
    }

    // ────────────────────────────────────────────────────────────────
    // SERIE GRACZA w turnieju
    // ────────────────────────────────────────────────────────────────
    public function obliczSerie(string $userUniID, int $turniejID): array
    {
        $db = \Config\Database::connect();

        $wyniki = $db->query("
            SELECT ty.pkt
            FROM typy ty
            JOIN terminarz t ON t.Id = ty.GameID
            WHERE ty.uniID = ? AND t.TurniejID = ? AND t.zakonczony = 1
            ORDER BY t.Date ASC, t.Time ASC
        ", [$userUniID, $turniejID])->getResultArray();

        $maxSeria    = 0;
        $obecnaSeria = 0;
        $biezaca     = 0;

        foreach ($wyniki as $w) {
            if ((int)$w['pkt'] > 0) {
                $biezaca++;
                if ($biezaca > $maxSeria) $maxSeria = $biezaca;
            } else {
                $biezaca = 0;
            }
        }
        $obecnaSeria = $biezaca;

        return [
            'najdluzsza' => $maxSeria,
            'obecna'     => $obecnaSeria,
        ];
    }

    // ────────────────────────────────────────────────────────────────
    // CACHE: zapis i odczyt
    // ────────────────────────────────────────────────────────────────
    public function zapiszCache(int $turniejID, array $dane): void
    {
        $dir = WRITEPATH . 'statystyki/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        file_put_contents($dir . $turniejID . '.json', json_encode($dane, JSON_UNESCAPED_UNICODE));
    }

    public function odczytajCache(int $turniejID): ?array
    {
        $path = WRITEPATH . 'statystyki/' . $turniejID . '.json';
        if (!file_exists($path)) return null;
        return json_decode(file_get_contents($path), true);
    }

    public function przeliczIZapiszCache(int $turniejID): array
    {
        $dane = $this->obliczStatystykiTurnieju($turniejID);
        $this->zapiszCache($turniejID, $dane);
        return $dane;
    }

    private function rowToArray($row): ?array
    {
        return $row ? (array)$row : null;
    }
    
    // w StatystykiModel.php
public function getRankingPozycja(string $userUniID, int $turniejID): int
{
    $db    = \Config\Database::connect();
    $myPkt = $this->getAllPoints($userUniID, $turniejID);

    $result = $db->query("
        SELECT COUNT(*) AS lepszyGraczy
        FROM (
            SELECT u.uniID,
                   COALESCE(SUM(ty.pkt), 0) + COALESCE(SUM(o.pkt), 0) AS suma
            FROM uzytkownicy u
            LEFT JOIN typy ty        ON ty.uniID    = u.uniID AND ty.TurniejID    = ?
            LEFT JOIN odpowiedzi o   ON o.uniidOdp  = u.uniID AND o.TurniejID     = ?
            WHERE u.PlaysTheActiveTournament = 1
            GROUP BY u.uniID
        ) ranking
        WHERE ranking.suma > ?
    ", [$turniejID, $turniejID, $myPkt])->getRow();

    return (int)($result->lepszyGraczy ?? 0) + 1;
}

private function getAllPoints(string $userUniID, int $turniejID): int
{
    $db = \Config\Database::connect();
    $pktMecze   = (int)($db->table('typy')->selectSum('pkt')
        ->where('uniID', $userUniID)->where('TurniejID', $turniejID)
        ->get()->getRow()->pkt ?? 0);
    $pktPytania = (int)($db->table('odpowiedzi')->selectSum('pkt')
        ->where('uniidOdp', $userUniID)->where('TurniejID', $turniejID)
        ->get()->getRow()->pkt ?? 0);
    return $pktMecze + $pktPytania;
}
}