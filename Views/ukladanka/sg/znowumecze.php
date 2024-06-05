<div class="container mt-3 px-0 mx-0">
        <div id="matchesAccordion">
    <?php foreach ($mecze as $match): ?>
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading<?= $match['ApiID']; ?>">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $match['ApiID']; ?>" aria-expanded="false" aria-controls="collapse<?= $match['ApiID']; ?>">
                    <?= $match['details']['home_team']['name'] ?? 'Unknown'; ?> vs <?= $match['details']['away_team']['name'] ?? 'Unknown'; ?>
                </button>
            </h2>
            <div id="collapse<?= $match['ApiID']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $match['ApiID']; ?>">
                <div class="accordion-body">
                    <p>Date: <?= $match['details']['date'] ?? 'N/A'; ?></p>
                    <p>Time: <?= $match['details']['time'] ?? 'N/A'; ?></p>
                    <!-- Add more details as needed -->
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
    </div>