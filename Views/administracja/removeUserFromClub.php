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

<h2>Usuń użytkownika z klubu</h2>

<?= session()->getFlashdata('error') ?>
<?= service('validation')->listErrors() ?>

<form action="/AdminDash/removeUserFromClub" method="post">
    <?= csrf_field() ?>

    <label for="userID">Użytkownik:</label>
    <input type="input" name="userID" value="<?= set_value('userID') ?>" />
    <br /><br />

    <label for="clubID">Klub:</label>
    <input type="input" name="clubID" value="<?= set_value('clubID') ?>" />
    <br /><br />

    <input type="submit" name="submit" value="Usuń użytkownika z klubu &raquo;" />
</form>