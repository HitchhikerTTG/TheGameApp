<style>
.form-control-plaintext {
    display: block;
    width: 100%;
    padding: 0.375rem 0;
    margin-bottom: 0;
    line-height: 1.5;
    color: #212529;
    background-color: transparent;
    border: solid transparent;
    border-width: 1px 0;
}

.odpowiedz-input {
    display: none;
}
</style>
<div class="section my-3 pt-3 question-section">
    <h4>Tu odpowiadamy na pytania</h4>

    <div class="container mt-3 px-0 mx-0">
        <?php foreach ($pytania as $pytanie): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-question-circle"></i> <?= esc($pytanie['tresc']) ?></span>
                    <span class="badge text-bg-secondary"><?= esc($pytanie['pkt']) ?> pkt</span>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('TheGame/zapiszOdpowiedzNaPytanie') ?>" class="question-form">
                        <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">
                        <input type="hidden" name="uniid" value="<?= session()->get('loggedInUser') ?>">
                        <div class="form-group">
                            <label class="static-label">Twoja odpowiedź</label>
                            <div class="input-group">
                                <label class="odpowiedz-label form-control-plaintext" style="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'display:block;' : 'display:none;' ?>">
                                    <?= isset($pytanie['dotychczasowa_odpowiedz']) ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>
                                </label>
                                <input type="text" class="form-control odpowiedz-input" id="odpowiedz_<?= $pytanie['id'] ?>" name="odpowiedz" value="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>" style="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'display:none;' : 'display:block;' ?>" required>
                                <button type="button" class="btn btn-outline-secondary zmien-btn" style="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'display:block;' : 'display:none;' ?>">Zmień</button>
                                <button type="submit" class="btn btn-outline-secondary" style="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'display:none;' : 'display:block;' ?>">Zapisz</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    Ważne do: <?= esc($pytanie['wazneDo']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

$(document).ready(function() {
    $('.question-section').on('click', '.zmien-btn', function() {
        var $form = $(this).closest('form');
        $form.find('.odpowiedz-label').hide();
        $form.find('.odpowiedz-input').show().removeClass('form-control-plaintext').addClass('form-control');
        $(this).hide();
        $form.find('[type="submit"]').show();
    });

    $('.question-section').on('submit', '.question-form', function(event) {
        event.preventDefault();
        var $form = $(this);
        $.post($form.attr('action'), $form.serialize(), function(response) {
            console.log("Response from server:", response);
            if (response.status === 'success') {
                var newAnswer = $form.find('.odpowiedz-input').val();
                $form.find('.odpowiedz-label').text(newAnswer).show();
                $form.find('.odpowiedz-input').hide().addClass('form-control-plaintext').removeClass('form-control');
                $form.find('.zmien-btn').show();
                $form.find('[type="submit"]').hide();
            } else {
                alert('Błąd przy zapisywaniu odpowiedzi.');
            }
        }, 'json').fail(function(jqXHR, textStatus, errorThrown) {
            console.log("AJAX call failed: ", textStatus, errorThrown);
            console.log("Response from server:", jqXHR.responseText);
            alert('Błąd przy zapisywaniu odpowiedzi.');
        });
    });
});