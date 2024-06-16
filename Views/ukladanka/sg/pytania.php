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

.disabled {
    pointer-events: none;
    opacity: 0.5;
}
</style>

<div class="section my-3 pt-3 question-section">
    <h4>Tu odpowiadamy na pytania</h4>

    <div class="container mt-3 px-0 mx-0">
        <?php foreach ($pytania as $pytanie): ?>
            <?php
            // Konwersja daty na timestamp
            $wazneDoTimestamp = strtotime($pytanie['wazneDo']);
            $currentTimestamp = time();
            $isPast = $currentTimestamp > $wazneDoTimestamp;
            $hasAnswer = !empty($pytanie['dotychczasowa_odpowiedz']);
            ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-question-circle"></i> <?= esc($pytanie['tresc']) ?></span>
                    <span class="badge text-bg-secondary"><?= esc($pytanie['pkt']) ?> pkt</span>
                </div>
                <div class="card-body">
                    <p><?=$wazneDoTimestamp?> | <?=$currentTimestamp?></p>
                    <form method="post" action="<?= site_url('TheGame/zapiszOdpowiedzNaPytanie') ?>" class="question-form">
                        <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">
                        <input type="hidden" name="uniid" value="<?= session()->get('loggedInUser') ?>">
                        <div class="form-group">
                            <label class="static-label">Twoja odpowiedź</label>
                            <div class="input-group d-flex align-items-center">
                                <span class="flex-grow-1 odpowiedz-container">
                                    <label class="odpowiedz-label form-control-plaintext" style="display: <?= $hasAnswer ? 'block' : 'none' ?>;">
                                        <?= $hasAnswer ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>
                                    </label>
                                    <input type="text" class="form-control odpowiedz-input" id="odpowiedz_<?= $pytanie['id'] ?>" name="odpowiedz" value="<?= $hasAnswer ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>" style="<?= $hasAnswer ? 'display: none;' : 'display: inline-block;' ?>" required>
                                </span>
                                <button type="button" class="btn btn-outline-secondary action-btn flex-shrink-0 <?= $isPast ? 'disabled' : '' ?>" <?= $isPast ? 'disabled' : '' ?>>
                                    <?= $hasAnswer ? 'Zmień' : 'Zapisz' ?>
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php if ($isPast): ?>
                        <div class="row mt-3">
                            <div class="col text-center">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#odpowiedziModal<?= $pytanie['id']; ?>">
                                    Pokaż odpowiedzi użytkowników
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-muted">
                    <div class="row">
                        <div class="col">
                            Ważne do: <?= esc($pytanie['wazneDoLocal']) ?>
                        </div>
                        <div class="col">
                            Udzielonych odpowiedzi: <?= esc($pytanie['liczbaOdpowiedzi']) ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($isPast): ?>
                <div class="modal fade" id="odpowiedziModal<?= $pytanie['id']; ?>" tabindex="-1" aria-labelledby="odpowiedziModalLabel<?= $pytanie['id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="odpowiedziModalLabel<?= $pytanie['id']; ?>">Odpowiedzi użytkowników na pytanie: <?= esc($pytanie['tresc']); ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <?php if (!empty($pytanie['odpowiedzi'])): ?>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Nick</th>
                                                <th>Odpowiedź</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($pytanie['odpowiedzi'] as $odpowiedz): ?>
                                                <tr>
                                                    <td><?= esc($odpowiedz['nick']); ?></td>
                                                    <td><?= esc($odpowiedz['odpowiedz']); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php else: ?>
                                    <p>Brak odpowiedzi.</p>
                                <?php endif; ?>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.question-section').on('click', '.action-btn', function() {
        var $btn = $(this);
        if ($btn.hasClass('disabled')) {
            return;
        }
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