    <h1>Witaj <?= esc($daneUzytkownika['nick']) ?></h1>
    <ul>
        <?php foreach ($daneUzytkownika as $klucz => $wartosc): ?>
            <li><strong><?= esc($klucz) ?>:</strong> <?= esc($wartosc) ?></li>
        <?php endforeach; ?>
    </ul>