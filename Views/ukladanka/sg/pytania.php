<?php foreach ($pytania as $pytanie): ?>
    <div class="pytanie">
        <p><?= esc($pytanie['tresc']) ?></p>
        <p>Punkty za pytanie: <?= esc($pytanie['pkt']) ?></p>
        <p>Ważne do: <?= esc($pytanie['wazneDo']) ?></p>
        <form method="post" action="<?= site_url('TheGame/zapiszOdpowiedzNaPytanie') ?>">
            <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">
            <input type="hidden" name="uniid" value="<?= session()->get('loggedInUser') ?>">
            <input type="text" name="odpowiedz" value="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>" required>
            <button type="submit"><?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'Zmień' : 'Zapisz'?></button>
        </form>
    </div>
<?php endforeach; ?>

<div class="container mt-3 px-0 mx-0">
<div class="row">
    <div class="col">
        <p><a href="/archiwumPytan">Wszystkie dotychczasowe pytania &raquo;</a></p>
    </div>
    
</div>
</div>