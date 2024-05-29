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

<h2>Dodaj Turniej do bazy</h2>

<?= session()->getFlashdata('error') ?>
<?= service('validation')->listErrors() ?>

<form action="/AdminDash/dodajTurniej" method="post">
    <?= csrf_field() ?>

    <label for="nazwa">Nazwa turnieju</label>
    <input type="input" width=150 name="nazwa" value="<?=set_value('nazwa')?>" />
                <?= isset($validation) ? display_form_errors($validation, 'nazwa') : ''; ?>  

    <br /><br />
    <label for="body">Numer ID z serwisu zewnętrznego</label>
    <input type="number" name="CompetitionID" id="CompetitionID">
    <br /><br />

    <input type="submit" name="submit" value="Dodaj turniej &raquo;" />
</form>
