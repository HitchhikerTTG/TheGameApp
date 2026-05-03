<h1>Zarządzaj Pytaniami</h1>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form method="post" action="<?= site_url('/AdminDash/updateQuestionStatus') ?>">
    <table>
        <thead>
            <tr>
                <th>Treść</th>
                <th>Punkty</th>
                <th>Ważne Do</th>
                <th>Aktywne</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($pytania as $pytanie): ?>
                <tr>
                    <td><?= $pytanie['tresc'] ?></td>
                    <td><?= $pytanie['pkt'] ?></td>
                    <td><?= $pytanie['wazneDo'] ?></td>
                    <td>
                        <input type="checkbox" name="aktywne[]" value="<?= $pytanie['id'] ?>" <?= $pytanie['aktywne'] ? 'checked' : '' ?>>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button type="submit">Zapisz zmiany</button>
</form>