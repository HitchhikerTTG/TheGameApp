<div class="section my-3 pt-3">
<h4>Tu odpowiadamy na pytania</h4>

<div class="container mt-3 px-0 mx-0">


    <?php foreach ($pytania as $pytanie): ?>
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="fas fa-question-circle"></i> <?= esc($pytanie['tresc']) ?></span>
                <span class="badge badge-primary"><?= esc($pytanie['pkt']) ?> pkt</span>
            </div>
            <div class="card-body">
                <form method="post" action="<?= site_url('TheGame/zapiszOdpowiedzNaPytanie') ?>">
                    <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">
                    <input type="hidden" name="uniid" value="<?= session()->get('loggedInUser') ?>">
                    <div class="form-group">
                        <label for="odpowiedz_<?= $pytanie['id'] ?>">Twoja odpowiedź:</label>
                        <input type="text" class="form-control" id="odpowiedz_<?= $pytanie['id'] ?>" name="odpowiedz" value="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'Zmień' : 'Zapisz' ?></button>
                </form>
            </div>
            <div class="card-footer text-muted">
                Ważne do: <?= esc($pytanie['wazneDo']) ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://kit.fontawesome.com/a076d05399.js"></script>

<div class="container mt-3 px-0 mx-0">
<div class="row">
    <div class="col">
        <p><a href="/archiwumPytan">Wszystkie dotychczasowe pytania &raquo;</a></p>
    </div>
    
</div>
</div>
</div>