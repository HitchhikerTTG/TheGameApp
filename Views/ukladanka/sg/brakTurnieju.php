<div class="position-relative" style="min-height: 500px; overflow: hidden;">

    <!-- tło: accordion identyczny jak prawdziwy, rozmyty -->
    <div style="filter: blur(3px); opacity: 0.35; pointer-events: none;" class="section my-3 pt-3">
        <h4>Nadchodzące mecze</h4>
        <div class="container mt-3 px-0 mx-0">
            <div class="accordion">
                <?php foreach ([
                    ['17:00', 'Polska', 'Niemcy'],
                    ['19:00', 'Francja', 'Hiszpania'],
                    ['21:00', 'Anglia', 'Włochy'],
                ] as $i => [$czas, $home, $away]): ?>
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button <?= $i > 0 ? 'collapsed' : '' ?>" type="button">
                            <?= $czas ?> | <?= $home ?> vs <?= $away ?> | Wytypuj
                        </button>
                    </h2>
                    <?php if ($i === 0): ?>
                    <div class="accordion-collapse">
                        <div class="accordion-body">
                            <div class="row text-center">
                                <div class="col">
                                    <div class="team-name"><?= $home ?></div>
                                    <div class="score-display">-</div>
                                    <div class="row mt-1">
                                        <div class="col"><button class="minus" disabled>-</button></div>
                                        <div class="col"><button class="plus" disabled>+</button></div>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="team-name"><?= $away ?></div>
                                    <div class="score-display">-</div>
                                    <div class="row mt-1">
                                        <div class="col"><button class="minus" disabled>-</button></div>
                                        <div class="col"><button class="plus" disabled>+</button></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row text-center mt-3">
                                <div class="col">
                                    <button class="btn btn-primary" disabled>Typuję</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- overlay z komunikatem -->
    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center">
        <div class="card shadow text-center p-4" style="max-width: 360px;">
            <div class="fs-1 mb-2">⚽</div>
            <h5 class="card-title">Chcesz typować?</h5>
            <p class="card-text text-muted">Dołącz do aktywnego turnieju, żeby zobaczyć mecze i oddawać typy.</p>
            <a href="/profil" class="btn btn-primary">Mój profil &raquo;</a>
        </div>
    </div>

</div>
