<style>
.form-group {
    display: flex;
    flex-direction: column;
}

.input-group {
    display: flex;
    align-items: center;
}

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

.zmien-btn,
.zapisz-btn {
    margin-left: 10px;
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
                            <div class="form-group">
                            <label class="static-label">Twoja odpowiedź</label>
                            <div class="input-group d-flex align-items-center">
                                <span class="flex-grow-1 odpowiedz-container">
                                    <label class="odpowiedz-label form-control-plaintext"><?= !empty($pytanie['dotychczasowa_odpowiedz']) ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?></label>
                                    <input type="text" class="form-control odpowiedz-input" id="odpowiedz_<?= $pytanie['id'] ?>" name="odpowiedz" value="<?= !empty($pytanie['dotychczasowa_odpowiedz']) ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>" style="<?= !empty($pytanie['dotychczasowa_odpowiedz']) ? 'display: none;' : 'display: block;' ?>" required>
                                </span>
                                <button type="button" class="btn btn-outline-secondary action-btn flex-shrink-0"><?= !empty($pytanie['dotychczasowa_odpowiedz']) ? 'Zmień' : 'Zapisz' ?></button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    Ważne do: <?= esc($pytanie['wazneDoLocal']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.question-section').on('click', '.action-btn', function() {
        var $btn = $(this);
        var $form = $btn.closest('form');
        var $input = $form.find('.odpowiedz-input');
        var $label = $form.find('.odpowiedz-label');
        var isEditing = $btn.text() === 'Zmień';

        if (isEditing) {
            $label.hide();
            $input.show().focus();
            $btn.text('Zapisz');
        } else {
            $form.submit();
        }
    });

    $('.question-section').on('submit', '.question-form', function(event) {
        event.preventDefault();
        var $form = $(this);
        $.post($form.attr('action'), $form.serialize(), function(response) {
            console.log("Response from server:", response);
            if (response.status === 'success') {
                var newAnswer = $form.find('.odpowiedz-input').val();
                var $label = $form.find('.odpowiedz-label');
                var $input = $form.find('.odpowiedz-input');
                $label.text(newAnswer).show();
                $input.hide();
                $form.find('.action-btn').text('Zmień');
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
</script>