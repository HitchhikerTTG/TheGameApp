// In your view file (pytania.php)
<?php foreach ($pytania as $pytanie): ?>
    <div class="pytanie">
        <p><?= esc($pytanie['tresc']) ?></p>
        <form method="post" action="<?= site_url('TheGame/zapiszOdpowiedzNaPytanie') ?>">
            <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">
            <input type="hidden" name="uniid" value="<?= session()->get('loggedInUser') ?>">
            <input type="text" name="odpowiedz" required>
            <button type="submit">Zapisz</button>
        </form>
    </div>
<?php endforeach; ?>