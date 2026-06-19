<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\MeczService;

class RefreshLiveScores extends BaseCommand
{
    protected $group       = 'Live';
    protected $name        = 'live:refresh';
    protected $description = 'Fetch live scores from external API and write to JSON cache files';

    public function run(array $params): void
    {
        $configPath = WRITEPATH . 'ActiveTournament.json';
        if (!file_exists($configPath)) {
            CLI::write('No active tournament config found.', 'yellow');
            return;
        }

        $config    = json_decode(file_get_contents($configPath), true);
        $turniejID = (int)($config['activeTournamentId'] ?? 0);
        $compID    = (string)($config['activeCompetitionId'] ?? '');

        if (!$turniejID || !$compID) {
            CLI::write('Invalid tournament config.', 'red');
            return;
        }

        $terminarz = model(\App\Models\TerminarzModel::class)
                        ->getRozpoczeteNieZakonczone($turniejID);

        if (empty($terminarz)) {
            CLI::write('No live matches in progress.', 'yellow');
            return;
        }

        (new MeczService())->odswiezLiveMecze($terminarz, $turniejID, $compID);

        CLI::write('Live scores refreshed (' . count($terminarz) . ' match(es)).', 'green');
    }
}
    