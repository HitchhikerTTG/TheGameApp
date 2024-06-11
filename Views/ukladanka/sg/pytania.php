<div class="section my-3 pt-3 question-section">
    <h4>Tu odpowiadamy na pytania</h4>

    <div class="container mt-3 px-0 mx-0">
        <?php foreach ($pytania as $pytanie): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-question-circle"></i> <?= esc($pytanie['tresc']) ?></span>
                    <span class="badge badge-primary"><?= esc($pytanie['pkt']) ?> pkt</span>
                    <span class="badge badge-pill badge-info">Info</span>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('TheGame/zapiszOdpowiedzNaPytanie') ?>" class="question-form">
                        <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">
                        <input type="hidden" name="uniid" value="<?= session()->get('loggedInUser') ?>">
                        <div class="form-group">
                            <label class="odpowiedz-label" for="odpowiedz_<?= $pytanie['id'] ?>" style="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'display:block;' : 'display:none;' ?>">
                                Twoja odpowiedź: <?= isset($pytanie['dotychczasowa_odpowiedz']) ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>
                            </label>
                            <input type="text" class="form-control odpowiedz-input" id="odpowiedz_<?= $pytanie['id'] ?>" name="odpowiedz" value="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>" style="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'display:none;' : 'display:block;' ?>" required>
                        </div>
                        <button type="button" class="btn btn-primary zmien-btn" style="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'display:block;' : 'display:none;' ?>">Zmień</button>
                        <button type="submit" class="btn btn-primary" style="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'display:none;' : 'display:block;' ?>"><?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'Zapisz' : 'Zapisz' ?></button>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    Ważne do: <?= esc($pytanie['wazneDo']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.question-section').on('click', '.zmien-btn', function() {
            var $form = $(this).closest('form');
            $form.find('.odpowiedz-label').hide();
            $form.find('.odpowiedz-input').show();
            $(this).hide();
            $form.find('[type="submit"]').show();
        });

        $('.question-section').on('submit', '.question-form', function(event) {
            event.preventDefault();
            var $form = $(this);
            $.post($form.attr('action'), $form.serialize(), function(response) {
                console.log("Response from server:", response); // Dodajemy logowanie odpowiedzi z serwera
                if (response.status === 'success') {
                    var newAnswer = $form.find('.odpowiedz-input').val();
                    $form.find('.odpowiedz-label').text('Twoja odpowiedź: ' + newAnswer).show();
                    $form.find('.odpowiedz-input').hide();
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
</script>