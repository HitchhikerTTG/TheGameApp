
<div class="live-scores">
    <?php if (isset($live) && !empty($live)): ?>
        <?php foreach ($live as $match): ?>
            <div class="match">
                <div class="teams">
                    <span class="home"><?= $match['home_name'] ?></span>
                    <span class="score"><?= $match['score'] ?></span>
                    <span class="away"><?= $match['away_name'] ?></span>
                </div>
                <div class="details">
                    <span class="time"><?= $match['time'] ?></span>
                    <span class="status"><?= $match['status'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No live matches available</p>
    <?php endif; ?>
</div>
