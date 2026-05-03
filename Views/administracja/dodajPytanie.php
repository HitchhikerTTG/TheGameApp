<?php
$session = \Config\Services::session();
$sukces = $session->getFlashdata("success");
$fail = $session->getFlashdata("error");

if ($sukces) {
    echo '<div class="alert alert-success">' . $sukces . '</div>';
} elseif ($fail) {
    echo '<div class="alert alert-danger">' . $fail . '</div>';
}

$validation = \Config\Services::validation();
?>

<h1>Dodaj Pytanie</h1>

<form method="post" action="<?= site_url('/AdminDash/dodajPytanie') ?>">
    <label for="tresc">Treść:</label>
    <input type="text" id="tresc" name="tresc" required value="<?= old('tresc') ?>">
    <?php if ($validation->hasError('tresc')): ?>
        <div class="alert alert-danger"><?= $validation->getError('tresc') ?></div>
    <?php endif; ?>
    <br>

    <label for="pkt">Punkty:</label>
    <input type="number" id="pkt" name="pkt" required value="<?= old('pkt') ?>">
    <?php if ($validation->hasError('pkt')): ?>
        <div class="alert alert-danger"><?= $validation->getError('pkt') ?></div>
    <?php endif; ?>
    <br>

    <label for="wazneDo">Ważne do (format: YYYY-MM-DD HH:MM:SS):</label>
    <input type="text" id="wazneDo" name="wazneDo" required value="<?= old('wazneDo') ?>">
    <?php if ($validation->hasError('wazneDo')): ?>
        <div class="alert alert-danger"><?= $validation->getError('wazneDo') ?></div>
    <?php endif; ?>
    <br>

    <label for="TurniejID">Turniej ID:</label>
    <input type="number" id="TurniejID" name="TurniejID" required value="<?= old('TurniejID') ?>">
    <?php if ($validation->hasError('TurniejID')): ?>
        <div class="alert alert-danger"><?= $validation->getError('TurniejID') ?></div>
    <?php endif; ?>
    <br>

    <button type="submit">Dodaj Pytanie</button>
</form>