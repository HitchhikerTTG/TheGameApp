		<?php
    $session = \Config\Services::session();
    $sukces = $session->getFlashData("success");
    $fail = $session->getFlashData("fail");
 ?>

    

<? /*
echo "<pre>";

print_r($userInfo);
echo "</pre>";
//echo "<p>---</p>";
//print_r($gdzieGram);
//echo "</pre>";
*/
?>


<div class="containter gy-2">
<div class ="row">
    <div class = "col">
        <h3> Turnieje, w których biorę udział</h3>
    </div>
</div>
<div class="row">
    <div class="col">
    <?php if ($gdzieGram['active']): ?>
        <div class="card w-50">
            <div class="card-header">Aktywny turniej:</div>
            <div class="card-body">
                <h5 class="card-title"><?= $gdzieGram['active']['CompetitionName']; ?></h5>
                <p class="card-text">I tu by było fajnie ogarnąć jakąś logikę</p>
                <?php if ($gdzieGram['isActiveParticipant']): ?>
                <a href="/nowytest" class="btn btn-primary">Ić typować &raquo;</a> <?php else: ?> <a href="/Profil/dodajMnieDoTurnieju/<?= esc($userInfo['id']) ?>/<?= esc($gdzieGram['active']['ID'])?>" class="btn btn-primary">Chcę dołączyć</a> <?php  endif; ?> 
            </div>
        </div>
    </div>
<?php endif; ?>
</div>
<div class="row">
<div class="col">
<div class="card w-50">
  <div class="card-header">Wcześniejsze turnieje:</div>
<div class="card-body">

<?php foreach ($gdzieGram['participated'] as $turniej): ?>
    <p class="card-text">Turniej <?= $turniej['CompetitionName']; ?> | brałem udział {tutaj by można wpisać liczbę zdobytych punktów i zajęte miejsce)</p>
<?php endforeach; ?>

<ul class="list-group list-group-flush">
    
<?php foreach ($gdzieGram['notParticipated'] as $turniej): ?>
    <li class="list-group-item">Turniej <?= $turniej['CompetitionName']; ?> | nie brałem udziału</li>
<?php endforeach; ?>
</ul>
</div>
</div></div>
</div>