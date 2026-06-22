<script>

function typerEscHtml(s) {
  var d = document.createElement('div');
  d.textContent = String(s);
  return d.innerHTML;
}

function typerNormalizeNick(value) {
  return String(value || '')
    .replace('← Ty', '')
    .replace(/^[^\p{L}\p{N}]+/u, '')
    .trim()
    .toLocaleLowerCase('pl-PL');
}

function typerParseScore(text) {
  var parts = String(text || '').match(/(\d+)\s*:\s*(\d+)/);
  if (!parts) return null;
  return { homeScore: parseInt(parts[1], 10), awayScore: parseInt(parts[2], 10) };
}

function typerOutcome(h, a) {
  if (h > a) return 'H';
  if (h < a) return 'A';
  return 'D';
}

function typerPointsForScore(typeText, homeScore, awayScore) {
  var score = typerParseScore(typeText);
  if (!score) return 0;
  if (score.homeScore === homeScore && score.awayScore === awayScore) return 3;
  return typerOutcome(score.homeScore, score.awayScore) === typerOutcome(homeScore, awayScore) ? 1 : 0;
}

function typerCurrentLiveMatches() {
  return Array.from(document.querySelectorAll('.match-card')).filter(function(card) {
    return card.querySelector('.status-live');
  }).map(function(card) {
    var scores = card.querySelectorAll('.score-display');
    return {
      apiId: card.dataset.apiId,
      card: card,
      homeScore: parseInt(scores[0] && scores[0].textContent, 10),
      awayScore: parseInt(scores[1] && scores[1].textContent, 10)
    };
  }).filter(function(match) {
    return match.apiId && !Number.isNaN(match.homeScore) && !Number.isNaN(match.awayScore);
  });
}

function typerResultsRows(apiId) {
  var box = document.getElementById('results-' + apiId);
  if (!box) return [];
  if (box._typerRows) return box._typerRows;
  box._typerRows = Array.from(box.querySelectorAll('.results-row')).filter(function(row) {
    return row.querySelector('.res-nick') && row.querySelector('.res-type');
  }).map(function(row) {
    return {
      nickHtml: row.querySelector('.res-nick').innerHTML,
      nickText: row.querySelector('.res-nick').textContent,
      typeText: row.querySelector('.res-type').textContent,
      isMe:     row.querySelector('.res-nick').classList.contains('res-me')
    };
  });
  return box._typerRows;
}

function typerUserPickForMatch(match) {
  var pickLine = Array.from(match.card.querySelectorAll('p')).find(function(p) {
    return p.textContent.indexOf('Twój typ:') !== -1;
  });
  return typerParseScore(pickLine ? pickLine.textContent : '');
}

function typerRankingRows(players, field) {
  var sorted = players.slice().sort(function(a, b) {
    var diff = Number(b[field]) - Number(a[field]);
    if (diff) return diff;
    return String(a.nick).localeCompare(String(b.nick), 'pl');
  });
  var pos = 1;
  return sorted.map(function(g, i) {
    if (i > 0 && Number(g[field]) !== Number(sorted[i - 1][field])) pos = i + 1;
    return Object.assign({}, g, { pozycja: pos });
  });
}

function typerProjectedRanking(scoreOverrides) {
  var data = window.typerLeaderboardData || [];
  var current = typerRankingRows(data, 'punkty');
  var currentByUid = new Map(current.map(function(g) { return [String(g.uid), g.pozycja]; }));
  var livePoints = new Map();
  scoreOverrides = scoreOverrides || {};

  typerCurrentLiveMatches().forEach(function(match) {
    var finalScore = scoreOverrides[match.apiId] || match;
    typerResultsRows(match.apiId).forEach(function(row) {
      var key = typerNormalizeNick(row.nickText);
      var pts = typerPointsForScore(row.typeText, finalScore.homeScore, finalScore.awayScore);
      livePoints.set(key, (livePoints.get(key) || 0) + pts);
    });
  });

  return typerRankingRows(data.map(function(g) {
    var extra = livePoints.get(typerNormalizeNick(g.nick)) || 0;
    return Object.assign({}, g, {
      livePoints:      extra,
      projectedPoints: Number(g.punkty) + extra,
      currentPosition: currentByUid.get(String(g.uid))
    });
  }), 'projectedPoints').map(function(g) {
    return Object.assign({}, g, { delta: g.currentPosition - g.pozycja });
  });
}

function typerDeltaClass(delta) {
  if (delta > 0) return 'up';
  if (delta < 0) return 'down';
  return '';
}

function typerDeltaText(delta) {
  var abs = Math.abs(delta);
  var suffix = abs === 1 ? 'miejsce' : (abs > 1 && abs < 5 ? 'miejsca' : 'miejsc');
  if (delta > 0) return 'awans +' + delta + ' ' + suffix;
  if (delta < 0) return '-' + abs + ' ' + suffix;
  return 'bez zmian';
}

function typerMetricHtml(label, player, points) {
  var cls = typerDeltaClass(player ? player.delta : 0);
  return '<div class="typer-live-projection-box">'
    + '<div class="typer-live-projection-label">' + typerEscHtml(label) + '</div>'
    + '<div class="ff-bebas typer-live-projection-pos">' + (player ? player.pozycja + '.' : '-') + '</div>'
    + '<div class="typer-live-projection-delta ' + cls + '">' + (player ? typerDeltaText(player.delta) : 'brak danych') + '</div>'
    + '<div class="typer-live-projection-points">' + (player && points !== null ? '+' + points + ' pkt · razem ' + player.projectedPoints : 'pkt: -') + '</div>'
    + '</div>';
}

function typerRenderLiveRanking() {
  var userID = String(window.typerCurrentUserID || '');
  typerCurrentLiveMatches().forEach(function(match) {
    var resultBox    = document.getElementById('results-' + match.apiId);
    var projectionBox = document.getElementById('typer-live-projection-' + match.apiId);
    if (!resultBox || !projectionBox) return;

    var projected        = typerProjectedRanking();
    var projectedByNick  = new Map(projected.map(function(g) { return [typerNormalizeNick(g.nick), g]; }));
    var me               = projected.find(function(g) { return String(g.uid) === userID; });
    var meScore          = typerUserPickForMatch(match);
    var hitMe            = null;
    var currentPoints    = meScore ? typerPointsForScore(meScore.homeScore + ':' + meScore.awayScore, match.homeScore, match.awayScore) : null;
    var hitPoints        = meScore ? 3 : null;

    if (meScore) {
      var hitOverrides = {};
      hitOverrides[match.apiId] = meScore;
      hitMe = typerProjectedRanking(hitOverrides).find(function(g) { return String(g.uid) === userID; });
    }

    projectionBox.innerHTML = typerMetricHtml('Pozycja dla aktualnego wyniku', me, currentPoints)
      + typerMetricHtml('Pozycja gdy trafisz z wynikiem:', hitMe, hitPoints);

    var rows = typerResultsRows(match.apiId).map(function(row) {
      var player = projectedByNick.get(typerNormalizeNick(row.nickText));
      return Object.assign({}, row, {
        player:   player,
        matchPts: typerPointsForScore(row.typeText, match.homeScore, match.awayScore)
      });
    }).sort(function(a, b) {
      var pa = a.player ? a.player.pozycja : 9999;
      var pb = b.player ? b.player.pozycja : 9999;
      if (pa !== pb) return pa - pb;
      if (b.matchPts !== a.matchPts) return b.matchPts - a.matchPts;
      return typerNormalizeNick(a.nickText).localeCompare(typerNormalizeNick(b.nickText), 'pl');
    });

    var html = '<div class="typer-live-ranking-title mt-3 mb-2">Ranking i punkty dla bieżącego stanu meczu</div>'
      + '<div class="results-row typer-results-row" style="font-size:11px;color:var(--bs-tertiary-color);font-weight:700;text-transform:uppercase;">'
      + '<div>Rank</div><div>Nick</div><div>Typ</div><div>PKT / total</div><div>Zmiana</div></div>';

    rows.forEach(function(row) {
      var player = row.player || {};
      var delta  = player.delta || 0;
      var total  = player.projectedPoints !== undefined ? player.projectedPoints : '-';
      html += '<div class="results-row typer-results-row">'
        + '<div class="res-pos">' + (player.pozycja || '-') + '</div>'
        + '<div class="res-nick ' + (row.isMe ? 'res-me' : '') + '">' + row.nickHtml + (row.isMe && row.nickText.indexOf('← Ty') === -1 ? ' ← Ty' : '') + '</div>'
        + '<div class="res-type">' + typerEscHtml(row.typeText) + '</div>'
        + '<div class="typer-results-points"><span class="res-pts ff-bebas">+' + row.matchPts + '</span><span class="typer-results-total"> / ' + total + '</span></div>'
        + '<div><span class="typer-rank-delta ' + typerDeltaClass(delta) + '">' + (delta > 0 ? '↑ ' + delta : delta < 0 ? '↓ ' + Math.abs(delta) : '→ 0') + '</span></div>'
        + '</div>';
    });

    resultBox.innerHTML = html;
  });
}


$(document).ready(function () {

  /* ── Stan złotej piłki ─────────────────────────────────────────── */
  // savedGoldenGameID = mecz, na którym złota piłka jest ZAPISANA w DB
  // (odczytujemy z DOM -- który golden-row ma klasę 'active' przy ładowaniu)
  var $initActive       = $('.golden-row.active').first();
  var savedGoldenGameID   = <?= (int)($goldenBallGameID ?? 0) ?>;   // z bazy
  var goldenLocked        = <?= !empty($goldenBallLocked) ? 'true' : 'false' ?>;
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
    $('#golden-row-' + id).removeClass('active');
    $('#goldenGame' + id).prop('checked', false);
  }

  window.typerToggleGolden = function (id) {
    if (goldenLocked) return;
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
      // tylko JEDNA złota naraz -- wyłącz pozostałe (OFF, nie disabled)
      $('.golden-game-checkbox').not($chk).each(function () {
        var oid = parseInt($(this).data('game-id'));
        $(this).prop('checked', false);
        $('#golden-row-' + oid).removeClass('active');
      });
      pendingGoldenGameID = id;
    } else {
      if (pendingGoldenGameID === id) pendingGoldenGameID = 0;
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
            if (response.previousGoldenGameID) {
              resetGoldenRow(response.previousGoldenGameID);
            }
            // tylko ten mecz ma złotą zaznaczoną
            $('.golden-game-checkbox').not('#goldenGame' + gameId).each(function () {
              var oid = parseInt($(this).data('game-id'));
              $(this).prop('checked', false);
              $('#golden-row-' + oid).removeClass('active');
            });

          } else if (response.goldenBallRemoved) {
            savedGoldenGameID   = 0;
            pendingGoldenGameID = 0;
            resetGoldenRow(gameId);
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
  
    /* ── LIVE POLL (co 60s gdy jest mecz na żywo) ──────────────── */
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
          typerRenderLiveRanking();

          if (match.minute) {
              var $minuteSpan = $card.find('.match-minute');
              $minuteSpan.text(parseInt(match.minute));
              $card.find('.live-minute-wrapper').show(); // pokaż ukryty wrapper
          }
          if (match.status === 'FINISHED' || match.status === 'FINISHED_FALLBACK') {
           // Usuń live indykatory
            $card.find('.status-badge').removeClass('status-live').addClass('status-done').text('Zakończony');
            $card.find('.match-minute').closest('span').remove();
            $card.find('.score-display').removeClass('score-live');
            return;
        }

        // NOWE: strzelcy bramek
        if (Array.isArray(match.goals) && match.goals.length > 0) {
          var homeHtml = '', awayHtml = '';
          match.goals.forEach(function(g) {
            var ball = (g.type === 'owngoal') ? '⚽(og)' : '⚽';
            var min  = parseInt(g.minute) + '\'';
            if (g.home_away === 'home') {
              homeHtml += '<div>' + ball + ' ' + min + ' ' + g.player + '</div>';
            } else {
              awayHtml += '<div>' + g.player + ' ' + min + ' ' + ball + '</div>';
            }
          });
          var $scorers = $('#scorers-' + match.apiId);
          if ($scorers.length) {
            $scorers.find('div:first-child').html(homeHtml);
            $scorers.find('.text-end').html(awayHtml);
          }
        }
      });
    });
  }
    setInterval(refreshLiveScores, 60000);
    refreshLiveScores(); // od razu przy ładowaniu
    typerRenderLiveRanking();

  }

});
</script>
