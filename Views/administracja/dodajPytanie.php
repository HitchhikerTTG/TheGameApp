<?php
$session = \Config\Services::session();
$sukces = $session->getFlashdata("success");
$fail = $session->getFlashdata("error");

if ($sukces) {
    echo '<div class="alert alert-success">' . $sukces . '</div>';
} elseif ($fail) {
    echo '<div class="alert alert-danger">' . $fail . '</div>';
}
?>

<h1>Dodaj Pytanie</h1>

<form method="post" action="<?= site_url('/AdminDash/dodajPytanie') ?>">
    <label for="tresc">Treść:</label>
    <input type="text" id="tresc" name="tresc" required><br>
    <label for="pkt">Punkty:</label>
    <input type="number" id="pkt" name="pkt" required><br>
    <label for="wazneDo">Ważne do (format: YYYY-MM-DD HH:MM:SS):</label>
    <input type="text" id="wazneDo" name="wazneDo" required><br>
    <label for="TurniejID">Turniej ID:</label>
    <input type="number" id="TurniejID" name="TurniejID" required><br>
    <button type="submit">Dodaj Pytanie</button>
</form>