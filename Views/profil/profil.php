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

<div class="card mt-3">
    <div class="card-body">
        <h5 class="card-title">Twoje emoji</h5>
        <p class="text-muted small">Będzie widoczne przy nicku w tabeli, przy meczach i w avatarze chatu.</p>

        <div class="d-flex align-items-center gap-3 mb-3">
            <div id="emoji-preview" style="font-size:3rem;line-height:1;min-width:3rem;text-align:center;">
                <?= esc($userInfo['emoji'] ?? '') ?: '🙂' ?>
            </div>
            <div class="flex-grow-1">
                <form method="post" action="<?= base_url('profil/zapiszEmoji') ?>">
                    <?= csrf_field() ?>
                    <div class="input-group">
                        <input type="text" name="emoji" id="emoji-input" class="form-control form-control-lg"
                               maxlength="2" placeholder="np. 🦊"
                               value="<?= esc($userInfo['emoji'] ?? '') ?>"
                               oninput="document.getElementById('emoji-preview').textContent = this.value || '🙂'">
                        <button type="submit" class="btn btn-primary">Zapisz</button>
                    </div>
                    <div class="form-text">Wpisz jedno emoji z klawiatury (telefon lub Ctrl+. na Windows).</div>
                </form>
            </div>
        </div>
    </div>
</div>


<div class ="row">
    <div class = "col">
        <h3> Turnieje, w których biorę udział</h3>
    </div>
</div>
<div class="row">
    <div class="col">
    <?php if ($gdzieGram['active']): ?>
        <div class="card w-90 mb-4">
            <div class="card-header">Aktywny turniej:</div>
            <div class="card-body">
                <h5 class="card-title"><?= $gdzieGram['active']['CompetitionName']; ?></h5>
                <!--<p class="card-text">I tu by było fajnie ogarnąć jakąś logikę</p>-->
                <?php if ($gdzieGram['isActiveParticipant']): ?>
                <a href="/typowanie" class="btn btn-primary">Typuj mecze turnieju &raquo;</a> <?php else: ?> <a href="/Profil/dodajMnieDoTurnieju/<?= esc($userInfo['id']) ?>/<?= esc($gdzieGram['active']['ID'])?>" class="btn btn-primary">Chcę dołączyć</a> <?php  endif; ?> 
            </div>
        </div>
    </div>
<?php endif; ?>
</div>

<div class="row mt-4">
  <div class="col">
    <div class="card w-90">
      <div class="card-header">Powiadomienia email</div>
      <div class="card-body">
        <?php if ($sukces): ?><div class="alert alert-success"><?= $sukces ?></div><?php endif; ?>
        <form method="post" action="/profil/zapiszPreferencje">
          <?= csrf_field() ?>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="notify_bet_saved" id="notify_bet_saved"
              <?= $userInfo['notify_bet_saved'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="notify_bet_saved">
              Powiadom mnie, gdy mój typ zostanie zapisany
            </label>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="notify_reminder" id="notify_reminder"
              <?= $userInfo['notify_reminder'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="notify_reminder">
              Przypomnij mi przed meczami, gdy nie obstawiłem wyniku (codziennie o godzinie 17)
            </label>
          </div>
          <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="digest_optin" id="digest_optin"
              <?= $userInfo['digest_optin'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="digest_optin">
              Chcę otrzymywać poranny niezbędnik typera (wysyłany codziennie rano)
            </label>
          </div>
          <button type="submit" class="btn btn-primary">Zapisz</button>
        </form>
      </div>
    </div>
  </div>
</div>


<div class="row">
<div class="col">
<div class="card w-90">
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