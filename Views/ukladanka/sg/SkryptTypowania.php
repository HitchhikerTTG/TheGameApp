<script>
    $(document).ready(function() {
        const tournamentID = <?= json_encode($turniejID); ?>;
        const userID = <?= json_encode($userID); ?>;
        const usedGoldenBall = <?= json_encode($usedGoldenBall); ?>;

        $('.accordion-button').on('click', function() {
            const matchID = $(this).data('id');
            const accordionBody = $(this).closest('.accordion-item').find('.accordion-body');

            if (!accordionBody.html().trim()) {
                const jsonUrl = `/mecze/${tournamentID}/${matchID}`;

                $.get(jsonUrl, function(data) {
                    const matchDate = new Date(data.date + 'T' + data.time + 'Z');
                    const formattedTime = matchDate.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: false });
                    let detailsHTML = `
                        <form action="/submit" method="post">
                            <!-- Form fields -->
                            <button type="submit" class="btn btn-primary">Typuję!</button>
                        </form>
                        <div class="odds-container">
                            <div class="odds">1: ${data.odds['1'] || 'N/A'}</div>
                            <div class="odds">X: ${data.odds['X'] || 'N/A'}</div>
                            <div class="odds">2: ${data.odds['2'] || 'N/A'}</div>
                        </div>
                    `;
                    accordionBody.html(detailsHTML);
                }).fail(function() {
                    accordionBody.html('Error loading data.');
                });
            }
        });

        $('body').on('submit', 'form', function(event) {
            event.preventDefault();
            const form = $(this);
            $.post(form.attr('action'), form.serialize(), function(response) {
                alert('Form submitted successfully');
            }).fail(function() {
                alert('Form submission failed');
            });
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