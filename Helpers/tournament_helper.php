<?php

function get_active_tournament_config(): array
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
