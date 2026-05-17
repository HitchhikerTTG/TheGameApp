<?php if (!empty($pytania)): ?>
<hr class="my-3" style="border-color:var(--bs-border-color);">
<p class="section-label mb-2">Pytanie dnia</p>

<?php foreach ($pytania as $pytanie):
    $wazneDoTimestamp = strtotime($pytanie['wazneDo']);
    $isPast    = (time() > $wazneDoTimestamp);
    $hasAnswer = !empty($pytanie['dotychczasowa_odpowiedz']);
?>
<div class="card match-card mb-3">
  <div class="card-body px-3 py-3">
    <div class="question-badge mb-3">⚡ <?= (int)$pytanie['pkt'] ?> pkt</div>
    <p style="font-size:16px;font-weight:500;line-height:1.4;" class="mb-3"><?= esc($pytanie['tresc']) ?></p>

    <form method="post" action="<?= site_url('TheGame/zapiszOdpowiedzNaPytanie') ?>" class="question-form">
      <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">
      <input type="hidden" name="uniid"     value="<?= session()->get('loggedInUser') ?>">

      <label class="odpowiedz-label shout-input w-100 d-block mb-2"
             style="border-radius:10px;line-height:2.4;<?= $hasAnswer ? '' : 'display:none!important;' ?>">
        <?= $hasAnswer ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>
      </label>
      <input type="text" name="odpowiedz"
             class="shout-input odpowiedz-input w-100 d-block mb-2"
             style="border-radius:10px;<?= $hasAnswer ? 'display:none;' : '' ?>"
             value="<?= $hasAnswer ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>"
             <?= !$hasAnswer ? 'required' : '' ?>>

      <button type="button"
              class="btn-type ff-bebas action-btn <?= $hasAnswer ? 'done' : '' ?> <?= $isPast ? 'disabled-golden' : '' ?>"
              style="font-size:17px;padding:12px;"
              <?= $isPast ? 'disabled' : '' ?>>
        <?= $hasAnswer ? '✓ Zapisano' : 'Zapisuję' ?>
      </button>
    </form>

    <p class="social-proof mt-2 mb-0">
      Odpowiedzi przed: <?= esc($pytanie['wazneDoLocal']) ?>
      &nbsp;·&nbsp; Udzielono: <?= (int)$pytanie['liczbaOdpowiedzi'] ?>
    </p>

    <?php if ($isPast && !empty($pytanie['odpowiedzi'])): ?>
      <button type="button" class="btn btn-outline-secondary btn-sm mt-3"
              data-bs-toggle="modal" data-bs-target="#odpowiedziModal<?= $pytanie['id'] ?>">
        Pokaż odpowiedzi użytkowników
      </button>
      <div class="modal fade" id="odpowiedziModal<?= $pytanie['id'] ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"><?= esc($pytanie['tresc']) ?></h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <table class="table table-sm">
                <thead><tr><th>Nick</th><th>Odpowiedź</th></tr></thead>
                <tbody>
                  <?php foreach ($pytanie['odpowiedzi'] as $o): ?>
                    <tr><td><?= esc($o['nick']) ?></td><td><?= esc($o['odp']) ?></td></tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zamknij</button>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<script>
$(document).ready(function() {
  $(document).on('click', '.action-btn', function() {
    if ($(this).prop('disabled')) return;
    var $form  = $(this).closest('form');
    var $input = $form.find('.odpowiedz-input');
    var $label = $form.find('.odpowiedz-label');
    if ($(this).hasClass('done')) {
      $label.hide(); $input.show().focus();
      $(this).removeClass('done').text('Zapisuję');
    } else {
      $form.submit();
    }
  });

  $(document).on('submit', '.question-form', function(e) {
    e.preventDefault();
    var $form = $(this);
    $.post($form.attr('action'), $form.serialize(), function(response) {
      if (response.status === 'success') {
        var newAnswer = $form.find('.odpowiedz-input').val();
        $form.find('.odpowiedz-label').text(newAnswer).show();
        $form.find('.odpowiedz-input').hide();
        $form.find('.action-btn').addClass('done').text('✓ Zapisano');
      } else {
        alert('Błąd przy zapisywaniu odpowiedzi.');
      }
    }, 'json').fail(function() { alert('Błąd przy zapisywaniu odpowiedzi.'); });
  });
});
</script>
