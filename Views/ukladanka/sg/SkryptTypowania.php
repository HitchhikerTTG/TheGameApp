<script>
$(document).ready(function() {

    // Obsługa kliknięć przycisków "+" i "-"
    $('body').on('click', '.plus', function(event) {
        event.preventDefault();
        var $scoreDisplay = $(this).closest('.team').find('.score-display');
        var $scoreValue   = $(this).closest('.team').find('.score-value');
        var currentVal = isNaN(parseInt($scoreDisplay.text())) ? 0 : parseInt($scoreDisplay.text());
        currentVal++;
        $scoreDisplay.text(currentVal);
        $scoreValue.val(currentVal);
    });

    $('body').on('click', '.minus', function(event) {
        event.preventDefault();
        var $scoreDisplay = $(this).closest('.team').find('.score-display');
        var $scoreValue   = $(this).closest('.team').find('.score-value');
        var currentVal = parseInt($scoreDisplay.text()) || 0;
        if (currentVal > 0) { currentVal--; }
        $scoreDisplay.text(currentVal);
        $scoreValue.val(currentVal);
    });

    // Obsługa akordeonu z localStorage
    $('.accordion-collapse').on('shown.bs.collapse', function () {
        localStorage.setItem(`details-${<this.id>}`, 'true');
    });
    $('.accordion-collapse').on('hidden.bs.collapse', function () {
        localStorage.setItem(`details-${<this.id>}`, 'false');
    });
    $('.accordion-collapse').each(function () {
        if (localStorage.getItem(`details-${<this.id>}`) === 'true') {
            $(`#${<this.id>}`).addClass('show');
        }
    });

    // Obsługa wysyłania formularza AJAX
    $('body').on('submit', '.betting-form', function(event) {
        event.preventDefault();

        var form = $(this);
        var url  = form.attr('action');
        var data = form.serialize();

        $.ajax({
            type: 'POST',
            url: url,
            data: data,
            success: function(response) {
                if (response.success) {
                    var button = form.closest('.accordion-item').find('.accordion-button');
                    var score  = response.newTypText.replace('Twój typ: ', '');

                    // aktualizuj wynik w środkowej strefie
                    var $center = button.find('.flex-grow-1');
                    $center.find('.text-muted').replaceWith('<strong>' + score + '</strong>');
                    $center.find('strong').text(score);

                    // aktualizuj badge
                    button.find('.badge')
                        .removeClass('bg-warning text-dark')
                        .addClass('bg-success')
                        .text('✓ Wytypowany');

                    // animacja
                    button.css('background-color', 'lightgreen');
                    setTimeout(function() { button.css('background-color', ''); }, 1000);

                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                try {
                    var json = JSON.parse(xhr.responseText);
                    alert(json.message || 'Wystąpił nieoczekiwany błąd.');
                } catch(e) {
                    alert('Nie udało się przesłać formularza.');
                }
            }
        });

        // zwiń akordeon po zapisie
        var accordionId = form.closest('.accordion-collapse').attr('id');
        $(`#${accordionId}`).collapse('hide');
        localStorage.setItem(`details-${accordionId}`, 'false');
    });

    // Obsługa Złotej Piłki
    $('body').on('change', '.golden-game-checkbox', function() {
        var checkbox  = $(this);
        var isChecked = <checkbox.is>(':checked');

        if (isChecked) {
            // aktywna gwiazdka
            checkbox.siblings('span').css('opacity', '1');
            checkbox.siblings('small').text('To mój szczęśliwy mecz (pkt ×2)');

            // pozostałe -- wygaszone i zablokowane
            $('.golden-game-checkbox').not(checkbox)
                .prop('checked', false)
                .prop('disabled', true)
                .siblings('span').css('opacity', '0.1');
            $('.golden-game-checkbox').not(checkbox)
                .siblings('small').text('Inny mecz wybrałem jako szczęśliwy');
        } else {
            // odblokuj wszystkie
            $('.golden-game-checkbox')
                .prop('disabled', false)
                .siblings('span').css('opacity', '0.22');
            $('.golden-game-checkbox')
                .siblings('small').text('Za ten mecz chcę 2× więcej punktów');
        }
    });

});
</script>
