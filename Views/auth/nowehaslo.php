
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Zmień swoje hasło w typerze</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
  </head>
  <body>

<div class="container">

<div class="row">
	<div class="col">
		<h2>Twoje Nowe Hasło</h2>
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
    	<form action="<?= site_url('auth/newPass') ?>" method="post" accept-charset="utf-8">
		<?= csrf_field() ?>

  		<div class="form-group col-md-8 mb-3">
  				<label class="bmd-label-floating">Twoje hasło</label>
  			<input type="password" class="form-control" name="password" id="Password">
  			<small id="titleHelp" class="form-text text-muted">Używaj managera haseł. To musi mieć przynajmniej 7 znaków. I prosze, niech to nie bedzie 1234567. To hasło już zająłem. Podobnie jak qwerasdf. Liczę na Twoją kreatywność (i nie, nie podglądam).</small>
  			<span class="text-danger"> 
    			<?= isset($validation) ? display_form_errors($validation, 'password') : ''; ?>	
    			</span>
  		</div>
  		<div class="form-group col-md-8 mb-3">
  				<label class="bmd-label-floating">Twoje hasło</label>
  			<input type="password" class="form-control" name="confirmedpassword" id="Password">
  			<small id="titleHelp" class="form-text text-muted">Wybacz, ale na wszelki wypadek prosze, abyś powtórzył. Tak, wiem, copy pase, ale... czy to nie Ty zmieniasz hasło, gdyż pamięć już nie ta?</small>
  			<span class="text-danger"> 
    			<?= isset($validation) ? display_form_errors($validation, 'password') : ''; ?>	
    			</span>
  		</div>
  	<div class="form-row">
  		<input type="submit" class="btn btn-info" value="Zmień hasło się">
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

