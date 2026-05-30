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

    var gameId = $team.closest('form').find('[name="gameID"]').val();
    $('#btn-submit-' + gameId).removeClass('done').addClass('pending').text('Zapisz zmiany →');
  });

  $('body').on('click', '.step-btn.minus', function(e) {
    e.preventDefault();
    var $team  = $(this).closest('.team');
    var $val   = $team.find('.step-val');
    var $input = $team.find('.score-value');
    var n = Math.max(0, (parseInt($val.text()) || 0) - 1);
    $val.text(n); $input.val(n); $(this).blur();

    var gameId = $team.closest('form').find('[name="gameID"]').val();
    $('#btn-submit-' + gameId).removeClass('done').addClass('pending').text('Zapisz zmiany →');
  });

  /* ── GOLDEN BALL TOGGLE ── */
/* ── GOLDEN BALL TOGGLE ── */
window.typerToggleGolden = function(id) {
  var $row = $('#golden-row-' + id);
  var $chk = $('#goldenGame' + id);
  $row.toggleClass('active');
  $chk.prop('checked', $row.hasClass('active'));
  $chk.trigger('change');   // ← to było brakujące
  $('#btn-submit-' + id).removeClass('done').addClass('pending').text('Zapisz zmiany →');
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
/* ── BETTING FORM AJAX ── */
success: function(response) {
  if (response.success) {
    var gameId = $form.find('[name="gameID"]').val();
    $('#btn-submit-' + gameId).removeClass('pending').addClass('done').text('✓ Wytypowano');

    // Synchronizacja złotej piłki po zapisie
    if (response.goldenBallSetOn) {
      // Wyczyść poprzednią kartę (jeśli serwer ją przeniósł)
      if (response.previousGoldenGameID) {
        var prev = response.previousGoldenGameID;
        $('#golden-row-' + prev)
          .removeClass('active disabled-golden')
          .attr('onclick', 'typerToggleGolden(' + prev + ')');
        $('#goldenGame' + prev).prop({ checked: false, disabled: false });
        $('#golden-row-' + prev + ' .golden-label').text('⚽ Złota piłka -- 2× punkty');
      }
      // Zablokuj wszystkie pozostałe
      $('.golden-game-checkbox').not('#goldenGame' + gameId).each(function() {
        var oid = $(this).data('game-id');
        $(this).prop({ checked: false, disabled: true });
        $('#golden-row-' + oid)
          .removeClass('active')
          .addClass('disabled-golden')
          .removeAttr('onclick');
        $('#golden-row-' + oid + ' .golden-label').text('Złota piłka użyta na inny mecz');
      });

    } else if (response.goldenBallRemoved) {
      // Odblokuj wszystkie
      $('.golden-game-checkbox').each(function() {
        var oid = $(this).data('game-id');
        if (oid != gameId) {
          $(this).prop({ disabled: false });
          $('#golden-row-' + oid)
            .removeClass('disabled-golden')
            .attr('onclick', 'typerToggleGolden(' + oid + ')');
          $('#golden-row-' + oid + ' .golden-label').text('⚽ Złota piłka -- 2× punkty');
        }
      });
    }
  } else {
    alert(response.message);
  }
},


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
