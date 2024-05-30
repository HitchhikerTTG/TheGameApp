
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zarejestruj się do Typera na Mistrzostwa Świata w Katarze</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
  </head>
  <body>

<div class="container">

<div class="row">
	<div class="col">
		<h2>Rejestracja</h2>
		<hr>

		<?php

		if (!empty(session()->getFlashData('success'))){

			?>
			<div class="alert alert-success">
				
				<?
				echo session()->getFlashData('success');
				?>

			</div>

			<?
		} else if (!empty(session()->getFlashData('fail'))){

			?>
			<div class="alert alert-danger">
				
				<?
				session()->getFlashData('fail');
				?>

			</div>

			<?
		}



		?>
    	<form action="<?= site_url('auth/registerUser') ?>" method="post" accept-charset="utf-8">
		<?= csrf_field() ?>

		<div class="form-group col-md-6 mb-3">
			<label class="bmd-label-floating">Twój nick:</label>
			<input type="text" class="form-control" name="username" id="username" value="<?=set_value('nick')?>">
    		<small id="nickHelp" class="form-text text-muted">Żeby łatwiej się do Ciebie zwracać ;-)</small>
    		<span class="text-danger"> 
    			<?= isset($validation) ? display_form_errors($validation, 'nick') : ''; ?>	
    			</span>
		</div>
		<div class="form-group col-md-6 mb-3">
			<label class="bmd-label-floating">Twój email</label>
  			<input type="email" class="form-control" name="email" placeholder="Twój email" aria-describedby="emailHelp" value="<?=set_value('nick')?>">
  			<small id="emailHelp" class="form-text text-muted">Którego nie zamierzam nikomu udostępniać, a nam może ułatwić komunikację</small>
  			<span class="text-danger"> 
    			<?= isset($validation) ? display_form_errors($validation, 'email') : ''; ?>	
    			</span>
  		</div>
  		<div class="form-group col-md-8 mb-3">
  				<label class="bmd-label-floating">Twoje hasło</label>
  			<input type="password" class="form-control" name="password" id="Password">
  			<small id="emailHelp" class="form-text text-muted">Jedyne ograniczenie to minimum 7 znaków. I zachęcam do korzystania z managera haseł. </small>
  			<span class="text-danger"> 
    			<?= isset($validation) ? display_form_errors($validation, 'password') : ''; ?>	
    			</span>
  		</div>
  	<div class="form-row">
  		<input type="submit" class="btn btn-info" value="Zapisz się">
  	</div>
 	 
</form>
<br><br>	
	<a href="<?= site_url('auth'); ?>">
	Mam już konto, chcę sie zalogować
	</a>


</div>
</div>
</div>
</body>
</html>

