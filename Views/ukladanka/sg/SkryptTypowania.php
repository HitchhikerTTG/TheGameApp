<script>
$(document).ready(function() {
    // Obsługa kliknięć przycisków "+" i "-"
    $('body').on('click', '.plus', function(event) {
        event.preventDefault();
        var $scoreDisplay = $(this).closest('.team').find('.score-display');
        var $scoreValue = $(this).closest('.team').find('.score-value');
        var currentVal = isNaN(parseInt($scoreDisplay.text())) ? 0 : parseInt($scoreDisplay.text());
        currentVal++;
        $scoreDisplay.text(currentVal);
        $scoreValue.val(currentVal);
    });

    $('body').on('click', '.minus', function(event) {
        event.preventDefault();
        var $scoreDisplay = $(this).closest('.team').find('.score-display');
        var $scoreValue = $(this).closest('.team').find('.score-value');
        var currentVal = parseInt($scoreDisplay.text()) || 0;
        if (currentVal > 0) {
            currentVal--;
        } else if ($scoreDisplay.text().trim() === '') {
            currentVal = 0;
        }
        $scoreDisplay.text(currentVal);
        $scoreValue.val(currentVal);
    });

    // Obsługa rozwijania i zwijania akordeonu z zachowaniem stanu w localStorage
    $('.accordion-collapse').on('shown.bs.collapse', function () {
        let id = this.id;
        localStorage.setItem(`details-${id}`, 'true');
    });

    $('.accordion-collapse').on('hidden.bs.collapse', function () {
        let id = this.id;
        localStorage.setItem(`details-${id}`, 'false');
    });

    $('.accordion-collapse').each(function () {
        let id = this.id;
        let isOpen = localStorage.getItem(`details-${id}`) === 'true';
        if (isOpen) {
            $(`#${id}`).addClass('show');
        } else {
            $(`#${id}`).removeClass('show');
        }
    });

    // Obsługa przesyłania formularza AJAX
    $('body').on('submit', '.betting-form', function(event) {
        event.preventDefault(); // Zapobiega standardowej wysyłce formularza

        var form = $(this);
        var url = form.attr('action'); // URL z atrybutu action formularza
        var data = form.serialize(); // Serializacja danych formularza

        $.ajax({
            type: "POST",
            url: url,
            data: data,
            success: function(response) {
                if (response.success) {
                    var newTypText = response.newTypText;
                    var button = form.closest('.accordion-item').find('.accordion-button');
                    var oldText = button.text();
                    var newText = oldText.replace(/Twój typ: [^|]+|Wytypuj/, newTypText);
                    button.text(newText);

                    // Animacja podświetlenia na zielono
                    button.css('background-color', 'lightgreen');
                    setTimeout(function() { button.css('background-color', ''); }, 1000);
                } else {
                    alert('Błąd przy zapisywaniu typu.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Wystąpił błąd: ', error);
                alert('Nie udało się przesłać formularza. Sprawdź konsolę dla szczegółów.');
            }
        });

        // Zwinąć akordeon po przesłaniu formularza
        var accordionId = form.closest('.accordion-collapse').attr('id');
        $(`#${accordionId}`).collapse('hide');
        localStorage.setItem(`details-${accordionId}`, 'false');
    });

    // Obsługa zmiany statusu "Golden Ball"
    $('body').on('change', '.golden-game-checkbox', function() {
        var checkbox = $(this);
        var isChecked = checkbox.is(':checked');
        var gameId = checkbox.data('game-id');

        if (isChecked) {
            // Odznaczenie innych checkboxów
            $('.golden-game-checkbox').not(checkbox).prop('checked', false).prop('disabled', true);

            // Ustawienie etykiet
            $('.golden-game-checkbox').not(checkbox).siblings('label').text('Inny mecz wybrałem jako szczęśliwy');
            checkbox.siblings('label').text('To mój szczęśliwy mecz (pkt x2)');
        } else {
            // Przywrócenie możliwości zaznaczenia innych checkboxów
            $('.golden-game-checkbox').prop('disabled', false);

            // Ustawienie etykiet
            $('.golden-game-checkbox').siblings('label').text('Za ten mecz chcę otrzymać 2 x więcej punktów');
        }
    });
});
</script>