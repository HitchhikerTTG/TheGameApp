<style>
  .custom-accordion-gold {
    background-color: #FFDF6E;
  }
  .custom-accordion-button {
    background-color: #0066cc;
    color: white;
  }
  .custom-accordion-body {
    background-color: #f2f2f2;
  }
</style>

<div class="accordion" id="accordionExample">
  <div class="accordion-item custom-accordion-item">
    <h2 class="accordion-header custom-accordion-gold" id="headingOne">
      <button class="accordion-button custom-accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        Accordion Item #1
      </button>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
      <div class="accordion-body custom-accordion-body">
        This is the first item's accordion body.
      </div>
    </div>
  </div>
  <!-- Kolejne elementy akordeonu -->
</div>



<div class="section my-3 pt-3">
<h4>Tu typujemy najbliższe mecze</h4>
<div class="container mt-3 px-0 mx-0">
    <div id="matchesAccordion" class="accordion">
        <?php 
        $lastDate = null;
        foreach ($mecze as $match): 
            $matchDate = date('Y-m-d', strtotime($match['details']['date']));
            $matchTime = date('H:i', strtotime($match['details']['time']));
            $naszCzas = date('H:i', strtotime($match['details']['naszCzas']));
            if ($lastDate !== $matchDate): 
                if ($lastDate !== null): ?>
                    </div> <!-- Close previous date group -->
                <?php endif; ?>
                <div class="row"><div class="col-12"><strong> Data meczu: <?= $matchDate; ?>,</strong></div></div>
                <div class="date-group">
            <?php 
            $lastDate = $matchDate;
            endif; ?>
            
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading<?= $match['ApiID']; ?>">
                    <button class="accordion-button collapsed px-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $match['ApiID']; ?>" aria-expanded="false" aria-controls="collapse<?= $match['ApiID']; ?>">
                        <?= $naszCzas; ?> | <?= $match['details']['home_team']['name'] ?? 'Unknown'; ?> vs <?= $match['details']['away_team']['name'] ?? 'Unknown'; ?> | <?= isset($match['typy']['HomeTyp']) ? "Twój typ: {$match['typy']['HomeTyp']}:{$match['typy']['AwayTyp']}" : 'Wytypuj'; ?>
                    </button>
                </h2>
                <div id="collapse<?= $match['ApiID']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $match['ApiID']; ?>">
                    <div class="accordion-body">
                        <div class="row match form-row text-center">
                            <div class="col">
                                <form action="/theGame/nowyZapisTypu" method="post" class="betting-form">
                                    <input type="hidden" name="userUID" value="<?= $userID; ?>">
                                    <input type="hidden" name="gameID" value="<?= $match['Id']; ?>">
                                    <input type="hidden" name="turniejID" value="<?= $turniejID; ?>">
                                    <div class="row">
                                        <div class="col team h_<?= $match['details']['home_team']['id']; ?>">
                                            <div class="row">
                                                <div class="col team-name">
                                                    <?= $match['details']['home_team']['name']; ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col text-center">
                                                    <div class="score-display"><?= $match['typy']['HomeTyp'] ?? '-'; ?></div>
                                                    <input type="hidden" name="H" class="score-value" value="<?= $match['typy']['HomeTyp'] ?? 0; ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col zminusem"><button type="button" class="minus">-</button></div>
                                                <div class="col zplusem"><button type="button" class="plus">+</button></div>
                                            </div>
                                        </div>
                                        <div class="col team a_<?= $match['details']['away_team']['id']; ?>">
                                            <div class="row">
                                                <div class="col team-name">
                                                    <?= $match['details']['away_team']['name']; ?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <div class="score-display"><?= $match['typy']['AwayTyp'] ?? '-'; ?></div>
                                                    <input type="hidden" name="A" class="score-value" value="<?= $match['typy']['AwayTyp'] ?? 0; ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col zminusem text-center"><button type="button" class="minus">-</button></div>
                                                <div class="col zplusem text-center"><button type="button" class="plus">+</button></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col">
                                            <?php
                                                $labelText = '';

                                                if ($usedGoldenBall == 0) {
                                                    $labelText = 'Za ten mecz chcę otrzymać 2 x więcej punktów';
                                                } elseif ($usedGoldenBall == $match['Id']) {
                                                    $labelText = 'To mój szczęśliwy mecz (pkt x2)';
                                                } else {
                                                    $labelText = 'Inny mecz wybrałem jako szczęśliwy';
                                                }
                                            ?>
                                            <div class="row">
                                                <div class="col">
                                                    <input type="checkbox" id="goldenGame<?= $match['Id']; ?>" class="golden-game-checkbox" data-game-id="<?= $match['Id']; ?>" name="goldenGame" value="1" <?= $match['Id'] == $usedGoldenBall ? 'checked' : ''; ?> <?= $usedGoldenBall !== 0 && $usedGoldenBall !== $match['Id'] ? 'disabled' : ''; ?>>
                                                    <label for="goldenGame_<?= $match['Id']; ?>"><?= $labelText; ?></label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col">
                                            <button type="submit" class="btn btn-primary">Typuję!</button>
                                        </div>
                                    </div>
                                </form>
                                <div class="row">
                                    <div class="col">
                                        Liczba typów dla tego meczu: <?= $match['liczbaTypow']; ?>
                                        <?php if (strtotime($match['details']['date'] . ' ' . $match['details']['time']) < time()): ?>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#typy<?= $match['Id']; ?>">
                                                Jak typowali?
                                            </button>
                                            <div class="modal fade" id="typy<?= $match['Id']; ?>" tabindex="-1" aria-labelledby="typy<?= $match['Id']; ?>Label" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5" id="typy<?= $match['Id']; ?>Label">Typy innych</h1>
                                                        </div>
                                                        <div class="modal-body">
                                                            Tu będą widoczne typy wszystkich, którzy wytypowali ten mecz
                                                        </div>
                                                        <div class="modal-footer"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col betting-hints">
                                        <div class="col-12">
                                            <div class="hints-title">Podpowiedź bookmacherów</div>
                                            <div class="odds-container">
                                                <div class="odds">1: <?= $match['details']['odds']['1'] ?? 'N/A'; ?></div>
                                                <div class="odds">X: <?= $match['details']['odds']['X'] ?? 'N/A'; ?></div>
                                                <div class="odds">2: <?= $match['details']['odds']['2'] ?? 'N/A'; ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div> <!-- Close the last date group -->
</div>
<div class="container mt-3 px-0 mx-0">
<div class="row">
    <div class="col">
        <button type="button" class="btn btn-outline-secondary"><a href="/wszystkieMecze">Twoje typy na wszystkie mecze &raquo;</a></button>
    </div>
    <div class="col">
        <button type="button" class="btn btn-outline-secondary"><a href="/archiwumturnieju">Wyniki rozegranych meczów &raquo;</a></button>
    </div>
</div>
</div>
</div>