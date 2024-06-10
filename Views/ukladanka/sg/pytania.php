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
                            <label for="odpowiedz_<?= $pytanie['id'] ?>" class="odpowiedz-label">
                                Twoja odpowiedź: <?= isset($pytanie['dotychczasowa_odpowiedz']) ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>
                            </label>
                            <input type="text" class="form-control odpowiedz-input" id="odpowiedz_<?= $pytanie['id'] ?>" name="odpowiedz" value="<?= isset($pytanie['dotychczasowa_odpowiedz']) ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>" style="display: none;" required>
                        </div>
                        <button type="button" class="btn btn-secondary zmien-btn"><?= isset($pytanie['dotychczasowa_odpowiedz']) ? 'Zmień' : 'Edytuj' ?></button>
                        <button type="submit" class="btn btn-primary" style="display: none;">Zapisz</button>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    Ważne do: <?= esc($pytanie['wazneDo']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.zmien-btn').click(function() {
                var $form = $(this).closest('form');
                $form.find('.odpowiedz-label').hide();
                $form.find('.odpowiedz-input').show();
                $(this).hide();
                $form.find('[type="submit"]').show();
            });

            $('form').submit(function(event) {
                event.preventDefault();
                var $form = $(this);
                $.post($form.attr('action'), $form.serialize(), function(response) {
                    if (response.status === 'success') {
                        var newAnswer = $form.find('.odpowiedz-input').val();
                        $form.find('.odpowiedz-label').text('Twoja odpowiedź: ' + newAnswer).show();
                        $form.find('.odpowiedz-input').hide();
                        $form.find('.zmien-btn').text('Zmień').show();
                        $form.find('[type="submit"]').hide();
                    } else {
                        // Handle error case
                        alert('Błąd przy zapisywaniu odpowiedzi.');
                    }
                }, 'json');
            });
        });
    </script>
</div>