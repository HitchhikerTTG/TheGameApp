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

<h2>Troszke inaczej, czyli zapisujemy wyniki meczu</h2>

<?= session()->getFlashdata('error') ?>
<?= service('validation')->listErrors() ?>
<pre>
<? //print_r($terminarz);?>
</pre>
<? foreach ($terminarz as $mecz) { ?>


<form action="/serwisant/zapiszWynikMeczu" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="meczID" value="<?=$mecz['Id'] ?>" />
<table>
<tr>
<td>  <?=date("H:i",strtotime($mecz['Time'].'UTC'))?></td>
<td><?=$mecz['HomeName']?> vs <?=$mecz['AwayName']?></td>
<td><input type="numbers" size="1" name="H" value="<?=$mecz['ScoreHome'] ?>" class="qty form-control-lg" style="width:55px; font-size: 1.5em; border-style:none none double none;border-radius:0px"/></td>
<td><input type="numbers" size="1" name="A" value="<?=$mecz['ScoreAway'] ?>" class="qty form-control-lg" style="width:55px; font-size: 1.5em; border-style:none none double none;border-radius:0px"/></td>
<td><input type="submit" class="btn btn-info" value="Zamknij mecz"></td>
</tr>
</table>
</form>

<? } ?>

</body>
</html>