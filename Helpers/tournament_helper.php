<?php

/* function get_active_tournament_config(): array
{
    $path = WRITEPATH . 'ActiveTournament.json';
    if (file_exists($path)) {
        $data = json_decode(file_get_contents($path), true);
        if (is_array($data)) {
            return $data;
        }
    }
    $defaults = config('ActiveTournament');
    return [
        'activeTournamentId'   => $defaults->activeTournamentId,
        'activeTournamentName' => $defaults->activeTournamentNAME,
        'activeCompetitionId'  => null,
    ];
}
*/


function get_active_tournament_config(): array
{
    $db  = \Config\Database::connect();
    $row = $db->table('turnieje')
              ->where('Active', 1)
              ->get()
              ->getRowArray();

    if ($row) {
        return [
            'activeTournamentId'   => (int)$row['id'],
            'activeTournamentName' => $row['CompetitionName'],
            'activeCompetitionId'  => $row['CompetitionID'] ?? null,
            'okno24h'              => (bool)($row['okno_24h'] ?? false),  // ← nowe
        ];
    }

    $defaults = config('ActiveTournament');
    return [
        'activeTournamentId'   => $defaults->activeTournamentId,
        'activeTournamentName' => $defaults->activeTournamentNAME,
        'activeCompetitionId'  => null,
        'okno24h'              => (bool)($row['okno_24h'] ?? false),  // ← nowe
    ];
}
