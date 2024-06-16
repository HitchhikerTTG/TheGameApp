<div class="section my-3 pt-3">
    <h4>Archiwum meczów</h4>
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
                    <div class="row"><div class="col-12"><strong> Data meczu: <?= $matchDate; ?></strong></div></div>
                    <div class="date-group">
                <?php 
                $lastDate = $matchDate;
                endif; ?>
                
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading<?= $match['ApiID']; ?>">
                        <button class="accordion-button collapsed px-1" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $match['ApiID']; ?>" aria-expanded="false" aria-controls="collapse<?= $match['ApiID']; ?>">
                           <?= $match['details']['home_team']['name'] ?? 'Unknown'; ?> vs <?= $match['details']['away_team']['name'] ?? 'Unknown'; ?> <?= $match['details']['home_team']['score'] ?? 'Unknown'; ?> vs <?= $match['details']['away_team']['score'] ?? 'Unknown'; ?> | Twoje Pkt: <?=$match['typy']['pkt']?>
                        </button>
                    </h2>
                    <div id="collapse<?= $match['ApiID']; ?>" class="accordion-collapse collapse" aria-labelledby="heading<?= $match['ApiID']; ?>">
                        <div class="accordion-body">
                            <div class="row match form-row text-center">
                                 <div class="col">
                                 <p>Twój typ: <?= $match['typy']['HomeTyp'] ?? '-'; ?> : <?= $match['typy']['AwayTyp'] ?? '-'; ?></p>
                                 <?
                                 if ($usedGoldenBall == 0) {
                                                        $labelText = 'Nie chciałeś 2x więcej punktów za ten mecz';
                                                    } elseif ($usedGoldenBall == $match['Id']) {
                                                        $labelText = 'Za ten mecz chciałeś 2x wiecej punktów';
                                                    } else {
                                                        $labelText = 'Nie chciałeś 2x więcej punktów za ten mecz';
                                                    } 
                                 ?>
                                 <p><?=$labelText?></p>
                                 <p>Ten mecz wytypowało: <?= $match['liczbaTypow']; ?> osób </p>
                                 
                                 <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#typy<?= $match['Id']; ?>">
                                                    Jak typowali?
                                                </button>
                                                <div class="modal fade" id="typy<?= $match['Id']; ?>" tabindex="-1" aria-labelledby="typy<?= $match['Id']; ?>Label" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h1 class="modal-title fs-5" id="typy<?= $match['Id']; ?>Label">Nasze typy na ten mecz:</h1>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <?php if (isset($match['rozpoczety']) && $match['rozpoczety'] == 1): ?> 
                                                                    <table class="table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th>Nick</th>
                                                                                <th>Typ</th>
                                                                                <th>Złota piłka</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php if (isset($match['typyGraczy'])): 
                                                                                foreach ($match['typyGraczy'] as $typ): ?>
                                                                                    <tr>
                                                                                        <td><?= htmlspecialchars($typ['username']); ?></td>
                                                                                        <td><?= htmlspecialchars($typ['HomeTyp']); ?>:<?= htmlspecialchars($typ['AwayTyp']); ?></td>
                                                                                            <td>
    <?php if ($typ['GoldenGame'] == 1): ?>
      🙏
    <?php endif; ?>
</td>
                                                                                    </tr>
                                                                                <?php endforeach; 
                                                                            endif; ?>
                                                                        </tbody>
                                                                    </table>
                                                                <?php endif; ?>
                                                            </div>
                                                            <div class="modal-footer"></div>
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
</div>
</div>
</div>