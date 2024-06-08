<?php
$session = \Config\Services::session();
$sukces = $session->getFlashData("success");
$fail = $session->getFlashData("fail");

if ($sukces) {
    ?>
    <div class="alert alert-success">
        <?php echo $sukces; ?>
    </div>
    <?php
} else if ($fail) {
    ?>
    <div class="alert alert-danger">
        <?php echo $fail; ?>
    </div>
    <?php
}
?>

<h2>Przypisz użytkownika do klubu</h2>

<?= session()->getFlashdata('error') ?>
<?= service('validation')->listErrors() ?>

<form action="/AdminDash/assignUserToClub" method="post">
    <?= csrf_field() ?>

    <label for="userID">Użytkownik:</label>
    <select name="userID">
        <?php foreach ($users as $user): ?>
            <option value="<?= $user['id'] ?>"><?= $user['username'] ?></option>
        <?php endforeach; ?>
    </select>
    <br /><br />

    <label for="clubID">Klub:</label>
    <select name="clubID">
        <?php foreach ($clubs as $club): ?>
            <option value="<?= $club['id'] ?>"><?= $club['Nazwa'] ?></option>
        <?php endforeach; ?>
    </select>
    <br /><br />

    <input type="submit" name="submit" value="Przypisz użytkownika do klubu &raquo;" />
</form>