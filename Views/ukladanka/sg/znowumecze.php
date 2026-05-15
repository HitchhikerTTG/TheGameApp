<div class="section my-3 pt-3">
<!-- <h4>Faza pucharowa</h4>
<p>Ważne</p><p><i>w meczach fazy pucharowej punktujemy wynik po umownych 90minutach (plus to co doliczy sędzia), jeśli wtedy skończy się mecz, lub wynik po dogrywce. Jeśli dogrywka skończy się remisem, to punktujemy remis, a nie zwycięstwo jednej z drużyn po karnych.</i></p>-->
<h4> Najbliższe mecze </h4>
<div class="container mt-3 px-0 mx-0">
    <div id="matchesAccordion" class="accordion">
        <?php 
        $lastDate = null;
        foreach ($mecze as $match): 
            $matchDate = date('Y-m-d', strtotime($match['details']['date']));
            $naszCzas  = date('H:i', strtotime($match['details']['naszCzas']));

            $homeTeamName = is_array($match['details']) && isset($match['details']['home_team']['plName']) 
                ? $match['details']['home_team']['plName'] 
                : ($match['details']['home_team']['name'] ?? 'szukam...');
            $awayTeamName = is_array($match['details']) && isset($match['details']['away_team']['plName']) 
                ? $match['details']['away_team']['plName'] 
                : ($match['details']['away_team']['name'] ?? 'szukam...');

            if ($lastDate !== $matchDate):
                if ($lastDate !== null): ?>
                    </div>
                <?php endif; ?>
                <div class="row"><div class="col-12"><strong>Data meczu: <?= $matchDate ?></strong></div></div>
                <div class="date-group">
            <?php $lastDate = $matchDate;
            endif; ?>

            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?= $match['ApiID'] ?>">

                    <!-- === NAGŁÓWEK: 3 strefy === -->
                    <button class="accordion-button collapsed px-2" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse<?= $match['ApiID'] ?>"
                            aria-expanded="false"
                            aria-controls="collapse<?= $match['ApiID'] ?>">
                        <div class="d-flex align-items-center w-100 gap-2">

                            <div class="text-muted small fw-semibold" style="min-width:42px">
                                <?= $naszCzas ?>
                            </div>

                            <div class="flex-grow-1 text-center">
                                <span class="fw-semibold"><?= $homeTeamName ?></span>
                                <span class="mx-2">
                                    <?php if (isset($match['typy']['HomeTyp'])): ?>
                                        <strong><?= $match['typy']['HomeTyp'] ?>:<?= $match['typy']['AwayTyp'] ?></strong>
                                    <?php else: ?>
                                        <span class="text-muted">· -- ·</span>
                                    <?php endif; ?>
                                </span>
                                <span class="fw-semibold"><?= $awayTeamName ?></span>
                            </div>

                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                <?php if ($match['Id'] == $usedGoldenBall): ?>
                                    <span title="Złota Piłka aktywna">⭐</span>
                                <?php endif; ?>
                                <?php if ($match['rozpoczety']): ?>
                                    <span class="badge bg-secondary">Zamknięty</span>
                                <?php elseif (isset($match['typy']['HomeTyp'])): ?>
                                    <span class="badge bg-success">✓ Wytypowany</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Wytypuj</span>
                                <?php endif; ?>
                            </div>

                        </div>
                    </button>
                </h2>

                <div id="collapse<?= $match['ApiID'] ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $match['ApiID'] ?>">
                    <div class="accordion-body">
                        <div class="row match text-center">
                            <div class="col">
                                <form action="/theGame/nowyZapisTypu" method="post" class="betting-form">
                                    <input type="hidden" name="userUID"   value="<?= $userID ?>">
                                    <input type="hidden" name="gameID"    value="<?= $match['Id'] ?>">
                                    <input type="hidden" name="turniejID" value="<?= $turniejID ?>">

                                    <!-- === SCORE INPUT === -->
                                    <div class="row mb-3">
                                        <div class="col team h_<?= $match['details']['home_team']['id'] ?>">
                                            <div class="team-name fw-semibold mb-2"><?= $homeTeamName ?></div>
                                            <div class="d-flex align-items-center justify-content-center gap-3">
                                                <button type="button" class="btn btn-outline-secondary rounded-circle minus"
                                                        style="width:48px;height:48px;font-size:1.4rem;line-height:1;padding:0;">−</button>
                                                <div class="score-display fw-bold" style="font-size:2rem;min-width:2.5rem;text-align:center;">
                                                    <?= $match['typy']['HomeTyp'] ?? '0' ?>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary rounded-circle plus"
                                                        style="width:48px;height:48px;font-size:1.4rem;line-height:1;padding:0;">+</button>
                                            </div>
                                            <input type="hidden" name="H" class="score-value" value="<?= $match['typy']['HomeTyp'] ?? 0 ?>">
                                        </div>

                                        <div class="col team a_<?= $match['details']['away_team']['id'] ?>">
                                            <div class="team-name fw-semibold mb-2"><?= $awayTeamName ?></div>
                                            <div class="d-flex align-items-center justify-content-center gap-3">
                                                <button type="button" class="btn btn-outline-secondary rounded-circle minus"
                                                        style="width:48px;height:48px;font-size:1.4rem;line-height:1;padding:0;">−</button>
                                                <div class="score-display fw-bold" style="font-size:2rem;min-width:2.5rem;text-align:center;">
                                                    <?= $match['typy']['AwayTyp'] ?? '0' ?>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary rounded-circle plus"
                                                        style="width:48px;height:48px;font-size:1.4rem;line-height:1;padding:0;">+</button>
                                            </div>
                                            <input type="hidden" name="A" class="score-value" value="<?= $match['typy']['AwayTyp'] ?? 0 ?>">
                                        </div>
                                    </div>

                                    <!-- === ZŁOTA PIŁKA === -->
                                    <?php
                                        if ($usedGoldenBall == 0) {
                                            $labelText = 'Za ten mecz chcę 2× więcej punktów';
                                        } elseif ($usedGoldenBall == $match['Id']) {
                                            $labelText = 'To mój szczęśliwy mecz (pkt ×2)';
                                        } else {
                                            $labelText = 'Inny mecz wybrałem jako szczęśliwy';
                                        }
                                        $goldenDisabled = $usedGoldenBall !== 0 && $usedGoldenBall != $match['Id'];
                                    ?>
                                    <div class="text-center mb-3">
                                        <label for="goldenGame<?= $match['Id'] ?>"
                                               class="d-inline-flex flex-column align-items-center"
                                               style="cursor:<?= $goldenDisabled ? 'default' : 'pointer' ?>">
                                            <input type="checkbox"
                                                   id="goldenGame<?= $match['Id'] ?>"
                                                   class="golden-game-checkbox d-none"
                                                   name="goldenGame" value="1"
                                                   data-game-id="<?= $match['Id'] ?>"
                                                   <?= $match['Id'] == $usedGoldenBall ? 'checked' : '' ?>
                                                   <?= $goldenDisabled ? 'disabled' : '' ?>>
                                            <span style="font-size:2.2rem;
                                                         opacity:<?= $match['Id'] == $usedGoldenBall ? '1' : '0.22' ?>;
                                                         transition:opacity .2s,transform .2s;">⭐</span>
                                            <small class="text-muted mt-1"><?= $labelText ?></small>
                                        </label>
                                    </div>

                                    <!-- ZAPISZ -->
                                    <div class="row text-center">
                                        <div class="col">
                                            <button type="submit" class="btn btn-primary"
                                                    <?= $match['rozpoczety'] == 1 ? 'disabled' : '' ?>>
                                                <?= $match['rozpoczety'] == 1 ? 'Typowanie zakończone' : 'Typuję' ?>
                                            </button>
                                        </div>
                                    </div>
                                </form>

                                <!-- statystyki po otwarciu meczu -->
                                <?php if ($match['rozpoczety']): ?>
                                <div class="row mt-3">
                                    <div class="col">
                                        <table class="table table-sm">
                                        <tbody>
                                            <tr><td>Liczba typów:</td><td><?= $match['liczbaTypow'] ?></td></tr>
                                            <tr><td>Zwycięstwo <?= $homeTeamName ?></td><td><?= $match['podsumowanieTypow']['countWin1'] ?> typy(ów)</td></tr>
                                            <tr><td>Zwycięstwo <?= $awayTeamName ?></td><td><?= $match['podsumowanieTypow']['countWin2'] ?> typy(ów)</td></tr>
                                            <tr><td>Remis</td><td><?= $match['podsumowanieTypow']['countDraw'] ?> typy(ów)</td></tr>
                                            <tr><td>Najpopularniejszy typ:</td><td><?= $match['podsumowanieTypow']['mostPopularType'] ?> (<?= $match['podsumowanieTypow']['mostPopularTypeCount'] ?>×)</td></tr>
                                            <tr><td>Złota piłka:</td><td><?= $match['podsumowanieTypow']['goldenBallCount'] ?> raz(y)</td></tr>
                                        </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- Jak typowali? -->
                                <?php if (strtotime($match['details']['date'] . ' ' . $match['details']['time']) < time()): ?>
                                <div class="text-center mb-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#typy<?= $match['Id'] ?>">
                                        Jak typowali?
                                    </button>
                                </div>
                                <div class="modal fade" id="typy<?= $match['Id'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Nasze typy: <?= $homeTeamName ?> vs <?= $awayTeamName ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php if (isset($match['rozpoczety']) && $match['rozpoczety'] == 1): ?>
                                                <table class="table table-sm">
                                                    <thead><tr><th>Nick</th><th>Typ</th><th></th></tr></thead>
                                                    <tbody>
                                                        <?php foreach ($match['typyGraczy'] ?? [] as $typ): ?>
                                                        <tr>
                                                            <td><?= htmlspecialchars($typ['username']) ?></td>
                                                            <td><?= htmlspecialchars($typ['HomeTyp']) ?>:<?= htmlspecialchars($typ['AwayTyp']) ?></td>
                                                            <td><?= $typ['GoldenGame'] == 1 ? '⭐' : '' ?></td>
                                                        </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                                <?php endif; ?>
                                            </div>
                                            <div class="modal-footer"></div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <!-- kursy bukmacherów -->
                                <div class="betting-hints mt-2">
                                    <div class="hints-title">Podpowiedź bukmacherów</div>
                                    <div class="odds-container">
                                        <div class="odds">1: <?= $match['details']['odds']['1'] ?? 'N/A' ?></div>
                                        <div class="odds">X: <?= $match['details']['odds']['X'] ?? 'N/A' ?></div>
                                        <div class="odds">2: <?= $match['details']['odds']['2'] ?? 'N/A' ?></div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="container mt-3 px-0 mx-0">
    <div class="row">
        <div class="col">
            <a href="/wszystkieMecze" class="btn btn-outline-secondary">Twoje typy na wszystkie mecze &raquo;</a>
        </div>
        <div class="col">
            <a href="/archiwumturnieju" class="btn btn-outline-secondary">Wyniki rozegranych meczów &raquo;</a>
        </div>
    </div>
</div>
</div>
