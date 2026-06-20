<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\TerminarzModel;
use App\Controllers\LiveScore;

class UpdateLivescores extends BaseCommand
{
    protected $group       = 'Live';
    protected $name        = 'live:update';
    protected $description = 'Pobierz wyniki live z API i zaktualizuj writable/live/*.json + flagę zakonczony w DB';

    public function run(array $params): void
    {
        $config = get_active_tournament_config();

        if (empty($config['activeTournamentId']) || empty($config['activeCompetitionId'])) {
            CLI::write('Brak aktywnego turnieju.', 'yellow');
            return;
        }

        $turniejID = (int)$config['activeTournamentId'];
        $compID    = (string)$config['activeCompetitionId'];

        $terminarzModel = model(TerminarzModel::class);
        $terminarz      = $terminarzModel->getRozpoczeteNieZakonczone($turniejID);

        if (empty($terminarz)) {
            CLI::write('Brak meczów live do sprawdzenia.', 'cyan');
            return;
        }

        $liveDir = WRITEPATH . 'live/';
        if (!is_dir($liveDir)) {
            mkdir($liveDir, 0755, true);
        }

        $liveController = new LiveScore();

        // --- Pobierz dane z API ---
$liveMecze    = [];
$historyMecze = [];

try {
    $liveMecze = $liveController->getLivescoresSimple(['competition_id' => $compID]);  // ← ZMIANA
    //CLI::write('Live mecze z API: ' . count($liveMecze), 'cyan');                      // ← DODAĆ
    //CLI::write(json_encode($liveMecze, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'white');
} catch (\Throwable $e) {
    log_message('error', '[live:update] getLivescoresSimple: ' . $e->getMessage());
    CLI::write('BŁĄD live: ' . $e->getMessage(), 'red');                               // ← DODAĆ
}

try {
    $historyMecze = $liveController->getHistory([                                      // ← ZMIANA
        'competition_id' => $compID,
        'from'           => date('Y-m-d'),
        'to'             => date('Y-m-d'),
    ]);
    //CLI::write('History mecze z API: ' . count($historyMecze), 'cyan');               // ← DODAĆ
    //CLI::write(json_encode($historyMecze, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'white');  // ← DODA
} catch (\Throwable $e) {
    log_message('error', '[live:update] getHistory: ' . $e->getMessage());
    
    CLI::write('BŁĄD history: ' . $e->getMessage(), 'red');                           // ← DODAĆ
}
        // --- Indeksy po fixture_id (= nasz ApiID) ---
        $liveIndex    = [];
        foreach ($liveMecze as $lm) {
            if (!empty($lm['fixture_id'])) {
                $liveIndex[(string)$lm['fixture_id']] = $lm;
            }
        }

        $historyIndex = [];
        foreach ($historyMecze as $hm) {
            if (!empty($hm['fixture_id'])) {
                $historyIndex[(string)$hm['fixture_id']] = $hm;
            }
        }

        // --- Pętla po meczach z DB ---
        foreach ($terminarz as $mecz) {
            $key      = (string)$mecz['ApiID'];
            $livePath = $liveDir . $key . '.json';

            // Case A: Mecz zakończony – pojawił się w history
if (isset($historyIndex[$key])) {
    $hm     = $historyIndex[$key];
    $finalScore = $hm['score']    ?? '0 - 0';
    $ht_score   = $hm['ht_score'] ?? '';
    $ft_score   = $hm['ft_score'] ?? '';
    $et_score   = $hm['et_score'] ?? '';
    $ps_score   = $hm['ps_score'] ?? '';

    // Ustal znacznik czasu końca meczu
    $timeLabel = 'Zakończony';
    if (!empty($ps_score)) { $timeLabel = 'Karne'; }
    elseif (!empty($et_score)) { $timeLabel = 'Po dogrywce'; }

    $this->writeLiveJson($livePath, [
        'fixture_id'   => $key,
        'match_id'     => (string)($hm['id'] ?? ''),
        'status'       => 'FINISHED',
        'time'         => $timeLabel,
        'score'        => $finalScore,       // wynik ostateczny
        'ht_score' => $ht_score,
        'ft_score' => $ft_score,
        'et_score' => $et_score,
        'ps_score' => $ps_score,
        'last_changed' => date('Y-m-d H:i:s'),
        'home_score'   => $homeScore,
        'away_score'   => $awayScore,
        'goals'        => $this->fetchGoals((string)($hm['id'] ?? '')),
    ]);

    $terminarzModel->setZakonczony((int)$mecz['Id']);
    CLI::write("✓ Zamknięto [{$key}]: {$finalScore} ({$timeLabel})", 'green');
    continue;
}

            // Case B: Mecz trwa – jest w live feed
            if (isset($liveIndex[$key])) {
                $lm     = $liveIndex[$key];
                $raw      = $lm['score']    ?? '0 - 0';
                [$homeScore, $awayScore] = $this->parseScore($raw);
                $ht_score = $lm['ht_score'] ?? '';

                $this->writeLiveJson($livePath, [
                    'fixture_id'   => $key,
                    'match_id'     => (string)($lm['id'] ?? ''),
                    'status'       => $lm['status'] ?? 'IN PLAY',
                    'time'         => (string)($lm['time'] ?? ''),
                    'score'        => $raw,
                    'ht_score'     => $lm['ht_score'] ?? '',
                    'last_changed' => date('Y-m-d H:i:s'),
                    'home_score'   => $homeScore,
                    'away_score'   => $awayScore,
                    'goals'        => $this->fetchGoals((string)($lm['id'] ?? '')),
                ]);

                CLI::write("↻ Live [{$key}] {$lm['time']}' {$raw}", 'cyan');
                continue;
            }

            // Case C: Brak w obu feedach – sprawdź timeout
            $matchTime = strtotime($mecz['Date'] . ' ' . $mecz['Time'] . ' UTC');
            if ($matchTime && time() > $matchTime + 6600) { // 110 minut
                // Odczytaj ostatni znany wynik z live pliku jeśli istnieje
                $existingLive = file_exists($livePath)
                    ? (json_decode(file_get_contents($livePath), true) ?? [])
                    : [];

                $this->writeLiveJson($livePath, [
                    'fixture_id'   => $key,
                    'match_id'     => $existingLive['match_id'] ?? '',
                    'status'       => 'FINISHED_FALLBACK',
                    'time'         => 'FT',
                    'score'        => $existingLive['score'] ?? '? - ?',
                    'ht_score'     => $existingLive['ht_score'] ?? '',
                    'last_changed' => date('Y-m-d H:i:s'),
                    'home_score'   => $existingLive['home_score'] ?? 0,
                    'away_score'   => $existingLive['away_score'] ?? 0,
                    'goals'   => $existingLive['goals'] ?? [],  // ← przepisz bez request do API
                ]);

                $terminarzModel->setZakonczony((int)$mecz['Id']);
                CLI::write("⚠ Fallback-zamknięto [{$key}] (>110 min bez danych)", 'yellow');
            }
        }

        // --- Czyszczenie: usuń live JSON starsze niż 24h ---
        foreach (glob($liveDir . '*.json') as $file) {
            if (filemtime($file) < time() - 86400) {
                unlink($file);
                CLI::write('Usunięto stary plik: ' . basename($file), 'dark_gray');
            }
        }

        CLI::write('live:update zakończony.', 'white');
    }

    private function parseScore(string $score): array
    {
        $parts     = explode(' - ', $score);
        $homeScore = isset($parts[0]) ? (int)trim($parts[0]) : 0;
        $awayScore = isset($parts[1]) ? (int)trim($parts[1]) : 0;
        return [$homeScore, $awayScore];
    }
    
    private function fetchGoals(string $matchId): array
{
    if (empty($matchId)) return [];
    try {
        $liveController = new LiveScore();
        $events = $liveController->getEvents(['id' => $matchId]);
        // getEvents() zwraca $data['event'] -- tablicę zdarzeń
        CLI::write("fetchGoals(id={$matchId}) → " . gettype($events) . ' count=' . (is_array($events) ? count($events) : 'n/a'), 'dark_gray');  // ← DODAĆ
        CLI::write(json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 'white');  // ← DODAĆ
        $goals = [];
        foreach ($events as $event) {
            if (in_array($event['type'] ?? '', ['goal', 'owngoal'])) {
                $goals[] = [
                    'minute'    => $event['time']      ?? '',
                    'player'    => $event['player']    ?? '',
                    'home_away' => $event['home_away'] ?? '',
                    'type'      => $event['type']      ?? 'goal',
                ];
            }
        }
        CLI::write("Goals zapisanych: " . count($goals), 'green');  // ← DODAĆ
        return $goals;
    } catch (\Throwable $e) {
        log_message('error', '[live:update] getEvents: ' . $e->getMessage());
        CLI::write('fetchGoals błąd: ' . $e->getMessage(), 'red');  // 
        return [];
    }
}


    private function writeLiveJson(string $path, array $data): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}