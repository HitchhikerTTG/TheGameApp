<?php
    $session = \Config\Services::session();
    $sukces = $session->getFlashData("success");
    $fail = $session->getFlashData("fail");
 

    if ($sukces){

      ?>
      <div class="alert alert-success">
        
        <?
        echo $sukces;
        ?>

      </div>

      <?
    } else if ($fail){

      ?>
      <div class="alert alert-danger">
        <?
        echo $fail;
        ?>
      </div>

      <?
    }



    ?>

    <h1>Dodaj Pytanie</h1>
    
    <?php if (session()->getFlashData('sukces')): ?>
        <p><?= session()->getFlashData('sukces') ?></p>
    <?php endif; ?>
    <?php if (session()->getFlashData('error')): ?>
        <p><?= session()->getFlashData('error') ?></p>
    <?php endif; ?>
    <form method="post" action="<?= site_url('/hell/dodajPytanie') ?>">
        <label for="tresc">Treść:</label>
        <input type="text" id="tresc" name="tresc" required><br>
        <label for="pkt">Punkty:</label>
        <input type="number" id="pkt" name="pkt" required><br>
        <label for="wazneDo">Ważne do (format: YYYY-MM-DD HH:MM:SS):</label>
        <input type="text" id="wazneDo" name="wazneDo" required><br>
        <label for="TurniejID">Turniej ID:</label>
        <input type="number" id="TurniejID" name="TurniejID"><br>
        <button type="submit">Dodaj Pytanie</button>
</form>