<script>
$(document).ready(function() {

  /* ── STEPPER ── */
  $('body').on('click', '.step-btn.plus', function(e) {
    e.preventDefault();
    var $team  = $(this).closest('.team');
    var $val   = $team.find('.step-val');
    var $input = $team.find('.score-value');
    var n = (parseInt($val.text()) || 0) + 1;
    $val.text(n); $input.val(n); $(this).blur();
  });

  $('body').on('click', '.step-btn.minus', function(e) {
    e.preventDefault();
    var $team  = $(this).closest('.team');
    var $val   = $team.find('.step-val');
    var $input = $team.find('.score-value');
    var n = Math.max(0, (parseInt($val.text()) || 0) - 1);
    $val.text(n); $input.val(n); $(this).blur();
  });

  /* ── GOLDEN BALL TOGGLE ── */
  window.typerToggleGolden = function(id) {
    var $row = $('#golden-row-' + id);
    var $chk = $('#goldenGame' + id);
    $row.toggleClass('active');
    $chk.prop('checked', $row.hasClass('active'));
  };

  $('body').on('change', '.golden-game-checkbox', function() {
    var $chk      = $(this);
    var isChecked = $<chk.is>(':checked');
    if (isChecked) {
      $('.golden-game-checkbox').not($chk).prop('checked', false).prop('disabled', true);
      $('.golden-game-checkbox').not($chk).each(function() {
        var otherId = $(this).data('game-id');
        $('#golden-row-' + otherId).removeClass('active').addClass('disabled-golden');
      });
    } else {
      $('.golden-game-checkbox').prop('disabled', false);
      $('.golden-game-checkbox').each(function() {
        var otherId = $(this).data('game-id');
        $('#golden-row-' + otherId).removeClass('disabled-golden');
      });
    }
  });

  /* ── BETTING FORM AJAX ── */
  $('body').on('submit', '.betting-form', function(e) {
    e.preventDefault();
    var $form = $(this);
    $.ajax({
      type: 'POST',
      url:  $form.attr('action'),
      data: $form.serialize(),
      success: function(response) {
        if (response.success) {
          var gameId = $form.find('[name="gameID"]').val();
          var $btn   = $('#btn-submit-' + gameId);
          var score  = response.newTypText.replace('Twój typ: ', '');
          $btn.addClass('done').text('✓ Wytypowano');
        } else {
          alert(response.message);
        }
      },
      error: function(xhr) {
        try { alert(JSON.parse(xhr.responseText).message || 'Błąd.'); }
        catch(e) { alert('Nie udało się przesłać formularza.'); }
      }
    });
  });

  /* ── COLLAPSE WYNIKÓW ── */
  window.typerToggleResults = function(apiId) {
    var $el    = $('#results-' + apiId);
    var $arrow = $('#arrow-' + apiId);
    var open   = $<el.is>(':visible');
    $el.toggle(!open);
    $arrow.text(open ? '›' : '‹');
  };

});
</script>
