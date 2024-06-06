<script>
    $(document).ready(function() {
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

$('body').on('submit', 'form', function(event) {
    const form = $(this);
    const accordionId = form.closest('.accordion-collapse').attr('id');
    $(`#collapse${accordionId}`).collapse('hide'); // Zwijanie akordeonu
    localStorage.setItem(`details-${accordionId}`, 'false'); // Aktualizacja localStorage
});

$('body').on('submit', 'form', function(event) {
    event.preventDefault(); // Zapobiega standardowej wysyłce formularza

    var form = $(this);
    var url = form.attr('action'); // URL z atrybutu action formularza

    // Wypisanie danych formularza w konsoli
    var formData = new FormData(form[0]);
    // console.log("Dane formularza:");
    for (var pair of formData.entries()) {
        console.log(pair[0]+ ': ' + pair[1]);
    }

    $.ajax({
        type: "POST",
        url: url,
        data: form.serialize(), // Serializacja danych formularza
        success: function(response) {
            var newTypText = response.newTypText;
            var button = form.closest('.accordion-item').find('.accordion-button');
            var oldText = button.text();
            var newText = oldText.replace(/Twój typ: [^|]+|Wytypuj/, newTypText); // Zakładając, że format tekstu to "Drużyna 1 vs Drużyna 2 | Twój typ: x:x"
            button.text(newText);

            // Animacja podświetlenia na zielono
            button.css('background-color', 'lightgreen');
            setTimeout(function() { button.css('background-color', ''); }, 1000); // Reset koloru tła po 3 sekundach
        },
        error: function(xhr, status, error) {
            console.error('Wystąpił błąd: ', error);
            console.error('Status: ', status);
            console.error('Odpowiedź serwera: ', xhr.responseText);
//            alert('Nie udało się przesłać formularza. Sprawdź konsolę dla szczegółów.');
            
            var newTypText = 'Nie udało się przesłać formularza :(';
            var button = form.closest('.accordion-item').find('.accordion-button');
            var oldText = button.text();
            var newText = oldText.replace(/Twój typ: [^|]+|Wytypuj/, newTypText); // Zakładając, że format tekstu to "Drużyna 1 vs Drużyna 2 | Twój typ: x:x"
            button.text(newText);
            
            button.css('background-color', 'lightred');
            setTimeout(function() { button.css('background-color', ''); }, 1000); // Reset koloru tła po 3 sekundach
        }
    });

    // Dodatkowo zwinąć akordeon po przesłaniu formularza
    var accordionId = form.closest('.accordion-collapse').attr('id');
    $(`#${accordionId}`).collapse('hide');
    localStorage.setItem(`details-${accordionId}`, 'false');
});


        $('body').on('click', '.plus', function(event) {
            event.preventDefault(); // Prevent form submission
            // Find the nearest score display and hidden input
            var $scoreDisplay = $(this).closest('.team').find('.score-display');
            var $scoreValue = $(this).closest('.team').find('.score-value');
            var currentVal = isNaN(parseInt($scoreDisplay.text())) ? 0 : parseInt($scoreDisplay.text());
            currentVal++;
            $scoreDisplay.text(currentVal);
            $scoreValue.val(currentVal);
        });

        $('body').on('click', '.minus', function(event) {
            event.preventDefault(); // Prevent form submission
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
    });
</script>