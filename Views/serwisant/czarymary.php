<!doctype html>
<html>

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Typer Mistrzostw Świata w Katarze</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>-->


<!--<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<meta name="viewport" content="width=device-width, initial-scale=1">

</head>
<body>
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

<h2>Dodaj pytanie</h2>

<?= session()->getFlashdata('error') ?>
<?= service('validation')->listErrors() ?>

<form action="/serwisant/dodajPytanie" method="post">
    <?= csrf_field() ?>

    <label for="tresc">Treść pytania</label>
    <input type="input" width=150 name="tresc" value="<?=set_value('tresc')?>" />
                <?= isset($validation) ? display_form_errors($validation, 'tresc') : ''; ?>  

    <br /><br />

    <label for="body">Ile punktów?</label>
    <label><input type="radio" name="pkt" value="1"> 1 pkt </label>
    <label><input type="radio" name="pkt" value="3"> 3 pkt</label>
    <br /><br />

    <label for="body">Ile punktów?</label>
    <input type="datetime-local" name="wazneDo" value="2022-11-20T19:30"
       min="2022-11-20T11:30" max="2022-12-31T19:30">
    <br /><br />

    <input type="submit" name="submit" value="Zapisz pytanie" />
</form>
</body>
</html>