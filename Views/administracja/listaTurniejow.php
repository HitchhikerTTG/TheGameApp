<?php
?>
<h2>Lista turniejów w bazie:</h2>

<form action="/AdminDash/zmienAktywnyTurniej" method="post">
    <?= csrf_field() ?>    
    <div class="turniej">
        <?php foreach ($turnieje as $turniej): ?>
    <div class="turniej">
        <label for="<?=esc($turniej['ID'])?>" style="<?= $turniej['Active'] ? 'background-color: lime;' : '' ?>"><?= esc($turniej['CompetitionName']) ?> <span class="details">[Api ID:<?=esc($turniej['CompetitionID'])?>] </span></label>
        <input type="radio" id="<?= esc($turniej['ID']) ?>" name="aktywnyTurniej" value="<?= esc($turniej['ID']) ?>" <?= $turniej['Active'] ? 'checked' : '' ?>> <a href="<?= site_url('/AdminDash/zapiszMeczeTurnieju/' . $turniej['CompetitionID']) ?>">wczytaj mecze do bazy&raquo;</a>
    </div>
    <?php endforeach; ?>

    <button type="submit">Potwierdź wybór aktywnego turnieju &raquo;</button>
</form>

<p><span>Weź uważaj, to może napytać biedy</span></p>

<hr>
<h4>kolejna chwila prawdy, czyli wczytanie pliku konfiguracyjnego:</h4>
<p>Numer aktywnego turnieju: <?= esc($config['activeTournamentId'] ?? 'Brak danych') ?></p>
<p>Numer aktywnego turnieju: <?= esc($config['activeCompetitionId'] ?? 'Brak danych') ?></p>
<p>Nazwa aktywnego turnieju: <?= esc($config['activeTournamentName'] ?? 'Brak danych') ?></p>

<?
?>