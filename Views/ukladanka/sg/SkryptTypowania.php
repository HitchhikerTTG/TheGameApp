<script>
$(document).ready(function () {

  /* ── Stan złotej piłki ─────────────────────────────────────────── */
  // savedGoldenGameID = mecz, na którym złota piłka jest ZAPISANA w DB
  // (odczytujemy z DOM -- który golden-row ma klasę 'active' przy ładowaniu)
  var $initActive       = $('.golden-row.active').first();
  var savedGoldenGameID = $initActive.length
    ? parseInt($initActive.attr('id').replace('golden-row-', ''))
    : 0;
  var pendingGoldenGameID = 0; // zaznaczona, ale jeszcze NIE zapisana

  /* ── STEPPER ────────────────────────────────────────────────────── */
  $('body').on('click', '.step-btn.plus', function (e) {
    e.preventDefault();
    var $team  = $(this).closest('.team');
    var $val   = $team.find('.step-val');
    var $input = $team.find('.score-value');
    var n = (parseInt($val.text()) || 0) + 1;
    $val.text(n); $input.val(n); $(this).blur();
    var gameId = $team.closest('form').find('[name="gameID"]').val();
    $('#btn-submit-' + gameId).removeClass('done').addClass('pending').text('Zapisz zmiany →');
  });

  $('body').on('click', '.step-btn.minus', function (e) {
    e.preventDefault();
    var $team  = $(this).closest('.team');
    var $val   = $team.find('.step-val');
    var $input = $team.find('.score-value');
    var n = Math.max(0, (parseInt($val.text()) || 0) - 1);
    $val.text(n); $input.val(n); $(this).blur();
    var gameId = $team.closest('form').find('[name="gameID"]').val();
    $('#btn-submit-' + gameId).removeClass('done').addClass('pending').text('Zapisz zmiany →');
  });

  /* ── GOLDEN BALL ─────────────────────────────────────────────────── */
  function resetGoldenRow(id) {
    $('#golden-row-' + id)
      .removeClass('active disabled-golden')
      .attr('onclick', 'typerToggleGolden(' + id + ')');
    $('#goldenGame' + id).prop({ checked: false, disabled: false });
    $('#golden-row-' + id + ' .golden-label').text('⚽ Złota piłka: punkty x2');
  }

  window.typerToggleGolden = function (id) {
    var $row = $('#golden-row-' + id);
    var $chk = $('#goldenGame' + id);
    $row.toggleClass('active');
    $chk.prop('checked', $row.hasClass('active'));
    $chk.trigger('change');
    $('#btn-submit-' + id).removeClass('done').addClass('pending').text('Zapisz zmiany →');
  };

  $('body').on('change', '.golden-game-checkbox', function () {
    var $chk      = $(this);
    var id        = parseInt($chk.data('game-id'));
    var isChecked = $chk.is(':checked');

    if (isChecked) {
      // Jeśli jest NIEZAPISANA złota piłka na innym meczu → zresetuj ją
      if (pendingGoldenGameID && pendingGoldenGameID !== id) {
        resetGoldenRow(pendingGoldenGameID);
      }
      pendingGoldenGameID = id;

      // Zablokuj wszystkie pozostałe wiersze
      $('.golden-game-checkbox').not($chk).each(function () {
        var oid = parseInt($(this).data('game-id'));
        $(this).prop({ checked: false, disabled: true });
        $('#golden-row-' + oid)
          .removeClass('active')
          .addClass('disabled-golden')
          .removeAttr('onclick');
        $('#golden-row-' + oid + ' .golden-label').text('Złota piłka użyta na inny mecz');
      });

    } else {
      // Odznaczenie
      if (pendingGoldenGameID === id) pendingGoldenGameID = 0;

      // Odblokuj wszystkie
      $('.golden-game-checkbox').each(function () {
        var oid = parseInt($(this).data('game-id'));
        if (oid !== id) {
          $(this).prop({ disabled: false });
          $('#golden-row-' + oid)
            .removeClass('disabled-golden')
            .attr('onclick', 'typerToggleGolden(' + oid + ')');
          if (oid !== savedGoldenGameID) {
            $('#golden-row-' + oid + ' .golden-label').text('⚽ Złota piłka: punkty x 2');
          }
        }
      });
    }
  });

  /* ── BETTING FORM AJAX ───────────────────────────────────────────── */
  $('body').on('submit', '.betting-form', function (e) {
    e.preventDefault();
    var $form = $(this);
    $.ajax({
      type: 'POST',
      url:  $form.attr('action'),
      data: $form.serialize(),
      success: function (response) {
        if (response.success) {
          var gameId = parseInt($form.find('[name="gameID"]').val());
          $('#btn-submit-' + gameId).removeClass('pending').addClass('done').text('✓ Wytypowano');

          if (response.goldenBallSetOn) {
            savedGoldenGameID   = response.goldenBallSetOn;
            pendingGoldenGameID = 0;

            // Wyczyść poprzedni zapisany mecz jeśli był przeniesiony
            if (response.previousGoldenGameID) {
              resetGoldenRow(response.previousGoldenGameID);
            }
            // Zablokuj wszystkie inne
            $('.golden-game-checkbox').not('#goldenGame' + gameId).each(function () {
              var oid = parseInt($(this).data('game-id'));
              $(this).prop({ checked: false, disabled: true });
              $('#golden-row-' + oid)
                .removeClass('active').addClass('disabled-golden')
                .removeAttr('onclick');
              $('#golden-row-' + oid + ' .golden-label').text('Złota piłka użyta na inny mecz');
            });

          } else if (response.goldenBallRemoved) {
            savedGoldenGameID   = 0;
            pendingGoldenGameID = 0;
            // Odblokuj wszystkie
            $('.golden-game-checkbox').each(function () {
              var oid = parseInt($(this).data('game-id'));
              if (oid !== gameId) {
                $(this).prop({ disabled: false });
                $('#golden-row-' + oid)
                  .removeClass('disabled-golden')
                  .attr('onclick', 'typerToggleGolden(' + oid + ')');
                $('#golden-row-' + oid + ' .golden-label').text('⚽ Złota piłka: punkty x 2');
              }
            });
          }

        } else {
          alert(response.message);
        }
      },
      error: function (xhr) {
        try { alert(JSON.parse(xhr.responseText).message || 'Błąd.'); }
        catch (e) { alert('Nie udało się przesłać formularza.'); }
      }
    });
  });

  /* ── COLLAPSE WYNIKÓW ────────────────────────────────────────────── */
  window.typerToggleResults = function (apiId) {
    var $el    = $('#results-' + apiId);
    var $arrow = $('#arrow-' + apiId);
    var open   = $el.is(':visible');
    $el.toggle(!open);
    $arrow.text(open ? '›' : '‹');
  };

  /* ── LIVE POLL (co 60s gdy jest mecz na żywo) ────────────────────── */
  if ($('.status-live').length > 0) {
    function refreshLiveScores() {
      $.getJSON('/livepoll', function(data) {
        if (!data || !data.length) return;
        data.forEach(function(match) {
          var $card = $('[data-api-id="' + match.apiId + '"]');
          if (!$card.length) return;

          var $scores = $card.find('.score-display.score-live');
          if (match.homeScore !== null) $scores.eq(0).text(parseInt(match.homeScore));
          if (match.awayScore !== null) $scores.eq(1).text(parseInt(match.awayScore));

          if (match.minute) {
            $card.find('.match-minute').text(parseInt(match.minute));
          }
        });
      });
    }
    setInterval(refreshLiveScores, 60000);
  }

});
</script>
