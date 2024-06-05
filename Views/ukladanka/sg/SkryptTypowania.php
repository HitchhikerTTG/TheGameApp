    <div id="matchesAccordion">
        <?php foreach ($matches as $match): ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?= $match['ApiID']; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $match['ApiID']; ?>" aria-expanded="false" aria-controls="collapse<?= $match['ApiID']; ?>">
                        <?= $match['home_team']; ?> vs <?= $match['away_team']; ?> | Wytypuj
                    </button>
                </h2>
                <div id="collapse<?= $match['ApiID']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $match['ApiID']; ?>">
                    <div class="accordion-body">
                        <!-- Dynamic content loaded via AJAX -->
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        $(document).ready(function() {
            const tournamentID = <?= json_encode($tournamentID); ?>;
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
        });
    </script>