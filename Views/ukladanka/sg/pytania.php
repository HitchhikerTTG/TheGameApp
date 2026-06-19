<?php if (!empty($pytania)): ?>
<hr class="my-3" style="border-color:var(--bs-border-color);">
<p class="section-label mb-2">Pytanie dnia</p>

<?php foreach ($pytania as $pytanie):
    $wazneDoTimestamp = strtotime($pytanie['wazneDo']);
    $isPast    = (time() > $wazneDoTimestamp);
    $odp = $pytanie['dotychczasowa_odpowiedz'] ?? null;
    $hasAnswer = ($odp !== null && $odp !== '');
?>
<div class="card match-card mb-3">
  <div class="card-body px-3 py-3">
    <div class="question-badge mb-3">⚡ <?= (int)$pytanie['pkt'] ?> pkt</div>
    <p style="font-size:16px;font-weight:500;line-height:1.4;" class="mb-3"><?= esc($pytanie['tresc']) ?></p>
    <div class="d-flex gap-2 align-items-baseline mb-3">
    <?php if (!empty($pytanie['opis'])): ?>
        <p style="font-size:13px;color:var(--bs-secondary-color);line-height:1.4;" class="mb-0">
            <?= esc($pytanie['opis']) ?>
        </p>
    <?php endif; ?>

    <?php if (!empty($pytanie['zrodlo'])): ?>
        <p style="font-size:12px;color:var(--bs-tertiary-color);" class="mb-0 ms-auto">
            Weryfikujemy na podstawie: <?= esc($pytanie['zrodlo']) ?>
        </p>
     <?php endif; ?>
        </div>
    <form method="post" action="<?= site_url('TheGame/zapiszOdpowiedzNaPytanie') ?>" class="question-form">
      <input type="hidden" name="pytanieID" value="<?= $pytanie['id'] ?>">
      <input type="hidden" name="uniid"     value="<?= session()->get('loggedInUser') ?>">

      <label class="odpowiedz-label shout-input w-100 d-flex align-items-center justify-content-between mb-2"
             style="border-radius:10px;line-height:2.4;padding:6px 12px;<?= $hasAnswer ? '' : 'display:none!important;' ?>">
        <span><?= $hasAnswer ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?></span>
        <?php if (!$isPast): ?>
          <span class="edit-answer-btn ms-2" style="cursor:pointer;opacity:0.5;flex-shrink:0;"
                title="Edytuj odpowiedź">✏</span>
        <?php endif; ?>
      </label>

      <input type="text" name="odpowiedz"
             class="shout-input odpowiedz-input w-100 mb-2 <?= $hasAnswer ? 'd-none' : 'd-block' ?>"
             style="border-radius:10px;"
             value="<?= $hasAnswer ? esc($pytanie['dotychczasowa_odpowiedz']) : '' ?>"
             <?= !$hasAnswer ? 'required' : '' ?>>

      <button type="button"
              class="btn-type ff-bebas action-btn <?= $hasAnswer ? 'done' : '' ?> <?= $isPast ? 'disabled-golden' : '' ?>"
              style="font-size:17px;padding:12px;"
              <?= $isPast ? 'disabled' : '' ?>>
        <?= $hasAnswer ? '✓ Zapisano' : 'Zapisuję' ?>
      </button>
    </form>

    <?php if ($hasAnswer && !$isPast): ?>
      <p class="social-proof text-center mt-2 mb-0">
        ✏ Możesz edytować · termin: <?= esc($pytanie['wazneDoLocal']) ?>
        &nbsp;·&nbsp; Udzielono: <?= (int)$pytanie['liczbaOdpowiedzi'] ?>
      </p>
    <?php elseif (!$hasAnswer && $isPast): ?>
      <p class="social-proof text-center mt-2 mb-0" style="color:var(--ty-red);">
        ⏱ Czas na odpowiedź minął · Udzielono: <?= (int)$pytanie['liczbaOdpowiedzi'] ?>
      </p>
    <?php else: ?>
      <p class="social-proof mt-2 mb-0">
        Odpowiedzi przed: <?= esc($pytanie['wazneDoLocal']) ?>
        &nbsp;·&nbsp; Udzielono: <?= (int)$pytanie['liczbaOdpowiedzi'] ?>
      </p>
    <?php endif; ?>

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

<div class="d-flex gap-2 mt-3">
  <a href="/archiwalnePytania" class="btn btn-outline-secondary btn-sm flex-fill text-center">Wcześniejsze pytania &raquo;</a>
</div>

<script>
$(document).on('click', '.edit-answer-btn', function() {
    var $form  = $(this).closest('form');
    var $label = $form.find('.odpowiedz-label');
    var $input = $form.find('.odpowiedz-input');
    $label.addClass('d-none');
    $input.removeClass('d-none');
    $input[0].focus();
    $form.find('.action-btn').removeClass('done').addClass('pending').text('Zapisz zmiany →');
});

$(document).on('click', '.action-btn', function() {
    if ($(this).prop('disabled')) return;
    $(this).closest('form').submit();
});

$(document).on('submit', '.question-form', function(e) {
    e.preventDefault();
    var $form = $(this);
    $.post($form.attr('action'), $form.serialize(), function(response) {
        if (response.status === 'success') {
            var newAnswer = $form.find('.odpowiedz-input').val();
            $form.find('.odpowiedz-label').find('span:first').text(newAnswer);
            $form.find('.odpowiedz-label').removeClass('d-none').css('display', '');    // odsłoń w OBU ścieżkach
            $form.find('.odpowiedz-input').addClass('d-none');
            $form.find('.action-btn').removeClass('pending').addClass('done').text('✓ Zapisano');
        } else {
            alert('Błąd przy zapisywaniu odpowiedzi.');
        }
    }, 'json').fail(function() { alert('Błąd przy zapisywaniu odpowiedzi.'); });
});
</script>
