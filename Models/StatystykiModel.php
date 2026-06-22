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
        $rowsTypow = $db->query("
            SELECT t.Id, t.HomeName, t.AwayName, t.ScoreHome, t.ScoreAway,
                   COUNT(ty.id) AS liczba
            FROM terminarz t
            JOIN typy ty ON ty.GameID = t.Id
            WHERE t.TurniejID = ? AND t.zakonczony = 1
            GROUP BY t.Id
            ORDER BY liczba DESC
        ", [$turniejID])->getResultArray();

        $maxTypow = !empty($rowsTypow) ? (int)$rowsTypow[0]['liczba'] : 0;
        $meczNajwiecejTypow = [
            'mecze'  => array_values(array_filter($rowsTypow, fn($r) => (int)$r['liczba'] === $maxTypow)),
            'liczba' => $maxTypow,
        ];

        // 2a. Trafienia 1X2 (jakikolwiek pkt > 0)
        $rowsKierunkowe = $db->query("
            SELECT t.Id, t.HomeName, t.AwayName, t.ScoreHome, t.ScoreAway,
                   COUNT(ty.id) AS liczba
            FROM terminarz t
            JOIN typy ty ON ty.GameID = t.Id AND ty.pkt > 0
            WHERE t.TurniejID = ? AND t.zakonczony = 1
            GROUP BY t.Id
            ORDER BY liczba DESC
        ", [$turniejID])->getResultArray();

        $maxKierunkowych = !empty($rowsKierunkowe) ? (int)$rowsKierunkowe[0]['liczba'] : 0;
        $meczNajwiecejTrafien1X2 = [
            'mecze'  => array_values(array_filter($rowsKierunkowe, fn($r) => (int)$r['liczba'] === $maxKierunkowych)),
            'liczba' => $maxKierunkowych,
        ];

        // 2b. Dokładne trafienia (pkt = 3 lub 6)
        $rowsDokladne = $db->query("
            SELECT t.Id, t.HomeName, t.AwayName, t.ScoreHome, t.ScoreAway,
                   COUNT(ty.id) AS liczba
            FROM terminarz t
            JOIN typy ty ON ty.GameID = t.Id AND ty.pkt IN (3, 6)
            WHERE t.TurniejID = ? AND t.zakonczony = 1
            GROUP BY t.Id
            ORDER BY liczba DESC
        ", [$turniejID])->getResultArray();

        $maxDokladnych = !empty($rowsDokladne) ? (int)$rowsDokladne[0]['liczba'] : 0;
        $meczNajwiecejDokladnychTrafien = [
            'mecze'  => array_values(array_filter($rowsDokladne, fn($r) => (int)$r['liczba'] === $maxDokladnych)),
            'liczba' => $maxDokladnych,
        ];

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
            SELECT k.ScoreHome, k.ScoreAway,
                   SUM(CASE
                       WHEN k.ScoreHome = m.ScoreHome AND k.ScoreAway = m.ScoreAway THEN 3
                       WHEN (k.ScoreHome > k.ScoreAway AND m.ScoreHome > m.ScoreAway)
                         OR (k.ScoreHome < k.ScoreAway AND m.ScoreHome < m.ScoreAway)
                         OR (k.ScoreHome = k.ScoreAway AND m.ScoreHome = m.ScoreAway)
                       THEN 1
                       ELSE 0
                   END) AS totalPkt
            FROM (SELECT DISTINCT ScoreHome, ScoreAway FROM terminarz WHERE TurniejID = ? AND zakonczony = 1) k
            CROSS JOIN (SELECT ScoreHome, ScoreAway FROM terminarz WHERE TurniejID = ? AND zakonczony = 1) m
            GROUP BY k.ScoreHome, k.ScoreAway
            ORDER BY totalPkt DESC LIMIT 1
        ", [$turniejID, $turniejID])->getRow();

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
        WHERE p.TurniejID = ? AND p.zamkniete = 1
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
            WHERE p.TurniejID = ? AND p.zamkniete = 1
            GROUP BY p.id
            HAVING wszystkich > 0
            ORDER BY poprawnych ASC LIMIT 1
        ", [$turniejID])->getRow();
        
        // Typ oddany najczęściej (spośród wszystkich typów na zakończone mecze)
$typNajczesciejOddawany = $db->query("
    SELECT ty.HomeTyp, ty.AwayTyp, COUNT(*) AS ile, SUM(ty.pkt) AS sumapt
    FROM typy ty
    JOIN terminarz t ON t.Id = ty.GameID
    WHERE t.TurniejID = ? AND t.zakonczony = 1
    GROUP BY ty.HomeTyp, ty.AwayTyp
    ORDER BY ile DESC LIMIT 1
", [$turniejID])->getRow();

// Typ który dał graczom łącznie najwięcej punktów
$typNajwiecejPkt = $db->query("
    SELECT ty.HomeTyp, ty.AwayTyp, SUM(ty.pkt) AS sumapt, COUNT(*) AS ile
    FROM typy ty
    JOIN terminarz t ON t.Id = ty.GameID
    WHERE t.TurniejID = ? AND t.zakonczony = 1
    GROUP BY ty.HomeTyp, ty.AwayTyp
    ORDER BY sumapt DESC LIMIT 1
", [$turniejID])->getRow();

// 12. Mapa wyników: ile razy padł każdy wynik końcowy
$mapaWynikowRaw = $db->query("
    SELECT ScoreHome, ScoreAway, COUNT(*) AS ile
    FROM terminarz
    WHERE TurniejID = ? AND zakonczony = 1
      AND ScoreHome IS NOT NULL AND ScoreAway IS NOT NULL
    GROUP BY ScoreHome, ScoreAway
", [$turniejID])->getResultArray();

$mapaWynikow = [];
foreach ($mapaWynikowRaw as $r) {
    $mapaWynikow[(int)$r['ScoreHome']][(int)$r['ScoreAway']] = (int)$r['ile'];
}

// 13. Mapa typów: ile razy gracze wytypowali każdy wynik
$mapaTypowRaw = $db->query("
    SELECT ty.HomeTyp, ty.AwayTyp, COUNT(*) AS ile
    FROM typy ty
    JOIN terminarz t ON t.Id = ty.GameID
    WHERE t.TurniejID = ? AND t.zakonczony = 1
    GROUP BY ty.HomeTyp, ty.AwayTyp
", [$turniejID])->getResultArray();

$mapaTypow = [];
foreach ($mapaTypowRaw as $r) {
    $mapaTypow[(int)$r['HomeTyp']][(int)$r['AwayTyp']] = (int)$r['ile'];
}

        return [
        'meczNajwiecejTypow'              => $meczNajwiecejTypow,
        'meczNajwiecejTrafien1X2'         => $meczNajwiecejTrafien1X2,
        'meczNajwiecejDokladnychTrafien'  => $meczNajwiecejDokladnychTrafien,
        'najpopularniejszyTrafiony'       => $this->rowToArray($najpopularniejszyTrafiony),
        'najpopularniejszyNieTrafiony'    => $this->rowToArray($najpopularniejszyNieTrafiony),
        'najskuteczniejszyWynik'          => $this->rowToArray($najskuteczniejszyWynik),
        'rozkładTrafien'                  => $rozkładMap,
        'meczNajwiecejPkt'                => $this->rowToArray($meczNajwiecejPkt),
        'meczNajwiecejGoldenUzytych'      => $this->rowToArray($meczNajwiecejGoldenUzytych),
        'meczNajwiecejGoldenTrafiony'     => $this->rowToArray($meczNajwiecejGoldenTrafiony),
        'typNajczesciejOddawany'          => $this->rowToArray($typNajczesciejOddawany),
        'typNajwiecejPkt'                 => $this->rowToArray($typNajwiecejPkt),
        'pytanieNajwiecejPoprawnych'      => $this->rowToArray($pytanieNajwiecejPoprawnych),
        'pytanieNajmniejPoprawnych'       => $this->rowToArray($pytanieNajmniejPoprawnych),
        'obliczoneAt'                     => date('Y-m-d H:i:s'),
        'mapaWynikow'                   => $mapaWynikow,
        'mapaTypow'                     => $mapaTypow,
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
    // Pozycja w rankingu i suma punktów liczone na podstawie tabelaGraczy_{turniejID}.json
    // - tej samej, gotowej, cache'owanej tabeli, którą TabelaModel::przeliczTabeleGraczy()
    // odświeża po każdym przeliczeniu punktów za mecz/pytanie. Jedno źródło prawdy dla
    // rankingu, zero dodatkowych zapytań SQL.
    public function getRankingPozycja(string $userUniID, int $turniejID): int
    {
        return model(TabelaModel::class)->getPozycjaGracza($turniejID, $userUniID);
    }
    
    
    public function getAllPoints(string $userUniID, int $turniejID): int
    {
        $wiersz = model(TabelaModel::class)->getWierszGracza($turniejID, $userUniID);
        return (int)($wiersz['punkty'] ?? 0);
    }

    // ────────────────────────────────────────────────────────────────
    // ŚREDNIE TURNIEJU -- do porównania gracza ze średnią
    // ────────────────────────────────────────────────────────────────
    public function getSrednieTurnieju(int $turniejID): array
    {
        $db = \Config\Database::connect();

        $row = $db->query("
            SELECT
                COALESCE(AVG(ty.pkt), 0) AS sredniaPkt,
                COALESCE(SUM(CASE WHEN ty.pkt > 0 THEN 1 ELSE 0 END) / COUNT(*) * 100, 0) AS sredniaSkutecznosc
            FROM typy ty
            JOIN terminarz t ON t.Id = ty.GameID
            WHERE t.TurniejID = ? AND t.zakonczony = 1
        ", [$turniejID])->getRow();

        return [
            'sredniaPktNaMecz'   => round((float)($row->sredniaPkt ?? 0), 2),
            'sredniaSkutecznosc' => round((float)($row->sredniaSkutecznosc ?? 0), 1),
        ];
    }
}

