<h2>Przypisz użytkownika do klubu</h2>

<?= session()->getFlashdata('error') ?>
<?= service('validation')->listErrors() ?>

<form action="/AdminDash/assignUserToClub" method="post">
    <?= csrf_field() ?>

    <label for="userID">Użytkownik:</label>
    <select name="userID">
        <?php foreach ($users as $user): ?>
            <option value="<?= $user['uniID'] ?>"><?= $user['nick'] ?></option>
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