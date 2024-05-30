<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zaloguj się - Typer Mistrzostw Świata 2022</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
  </head>
  <body>

<div class="container">

  <div class="row">
  <div class ="col">
  <P>Serio, jeszcze nie działa. Słodki miś też się popsuł. Sorri </p>
  <p>Co tu tak sam będziesz siedział(a), <a href="<?=site_url('auth/')?>"> wróć do logowania &raquo; </a></p>
    </div>
    </div>

<div class="row">
	<div class="col">
		<h2>Zresetuj hasło/h2>
		<hr>

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

    	<form action="<?= site_url('auth/resetPassword') ?>" method="post" accept-charset="utf-8">
		<?= csrf_field() ?>

		<div class="form-group col-md-6 mb-3">
			<label class="bmd-label-floating">Twój nick:</label>
			<input type="text" class="form-control" name="nick" id="Nick" value="<?=set_value('nick')?>">
    		<small id="nickHelp" class="form-text text-muted">Twój login w typerze</small>
    		<span class="text-danger"> 
    			<?= isset($validation) ? display_form_errors($validation, 'nick') : ''; ?>	
    			</span>
		</div>
  		<div class="form-group col-md-8 mb-3">
  				<label class="bmd-label-floating">Twoje hasło</label>
  			<input type="password" class="form-control" name="password" id="Password">
  			<small id="titleHelp" class="form-text text-muted"></small>
  			<span class="text-danger"> 
    			<?= isset($validation) ? display_form_errors($validation, 'password') : ''; ?>	
    			</span>
  		</div>
  	<div class="form-row">
  		<input type="submit" class="btn btn-info" value="Zaloguj się">
  	</div>
 	 
</form>
</div>
</div>
<div class="row mt-5">
	
<div class="col-md-4 offset-4">
  	<p><a href="<?= site_url('auth/register'); ?>">
	Tu nic nie działa jak powinno. To ja się jednak może zarejestruję. AGAAIN.
	</a></p>
  </div>

</div>


</div>
</body>
</html>



// Created on iPad.

