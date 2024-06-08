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

<h2>Użytkownicy w klubach</h2>
<table>
    <thead>
        <tr>
            <th>Użytkownik</th>
            <th>Klub</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clubMembers as $member): ?>
            <tr>
                <td><?= $member['nick'] ?></td>
                <td><?= $member['clubName'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>