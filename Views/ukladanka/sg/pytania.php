<?php foreach ($pytania as $pytanie): ?>
    <div class="pytanie">
        <p><?= esc($pytanie['tresc']) ?></p>
        <p>Punkty: <?= esc($pytanie['pkt']) ?></p>
        <p>Ważne do: <?= esc($pytanie['wazneDo']) ?></p>
        <form method="post" action="<?= site_url('TheGame/zapiszOdpowiedzNaPytanie') ?>">
            <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">
            <input type="hidden" name="uniid" value="<?= session()->get('loggedInUser') ?>">
            <input type="text" name="odpowiedz" value="<?= isset($pytanie['user_answer']) ? esc($pytanie['user_answer']) : '' ?>" required>
            <button type="submit"><?= isset($pytanie['user_answer']) ? 'Zmień' : 'Zapisz' ?></button>
        </form>
    </div>
<?php endforeach; ?>