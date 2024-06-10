<div class="section my-3 pt-3">
    <h4>Tu odpowiadamy na pytania</h4>
    <div class="container mt-3 px-0 mx-0 question-section">
        <?php foreach ($pytania as $pytanie): ?>
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-question-circle"></i> <?= esc($pytanie['tresc']) ?></span>
                    <span class="badge badge-primary"><?= esc($pytanie['pkt']) ?> pkt</span>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('TheGame/zapiszOdpowiedzNaPytanie') ?>" class="question-form">
                        <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">
                        <input type="hidden" name="uniid" value="<?= session()->get('loggedInUser') ?>">
                        <div class="form-group">
                            <?php if (isset($pytanie['dotychczasowa_odpowiedz']) && $pytanie['dotychczasowa_odpowiedz'] != ''): ?>
                                <label for="odpowiedz_<?= $pytanie['id'] ?>" class="odpowiedz-label">
                                    Twoja odpowiedź: <?= esc($pytanie['dotychczasowa_odpowiedz']) ?>
                                </label>
                                <input type="text" class="form-control odpowiedz-input" id="odpowiedz_<?= $pytanie['id'] ?>" name="odpowiedz" value="<?= esc($pytanie['dotychczasowa_odpowiedz']) ?>" style="display: none;" required>
                                <button type="button" class="btn btn-secondary zmien-btn">Edytuj</button>
                                <button type="submit" class="btn btn-primary" style="display: none;">Zapisz</button>
                            <?php else: ?>
                                <label for="odpowiedz_<?= $pytanie['id'] ?>" class="odpowiedz-label" style="display: none;">Twoja odpowiedź:</label>
                                <input type="text" class="form-control odpowiedz-input" id="odpowiedz_<?= $pytanie['id'] ?>" name="odpowiedz" value="" required>
                                <button type="submit" class="btn btn-primary">Zapisz</button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    Ważne do: <?= esc($pytanie['wazneDo']) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
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
                    if (response.status === 'success') {
                        var newAnswer = $form.find('.odpowiedz-input').val();
                        $form.find('.odpowiedz-label').text('Twoja odpowiedź: ' + newAnswer).show();
                        $form.find('.odpowiedz-input').hide();
                        $form.find('.zmien-btn').show();
                        $form.find('[type="submit"]').hide();
                    } else {
                        alert('Błąd przy zapisywaniu odpowiedzi.');
                    }
                }, 'json');
            });
        });
    </script>
</div>