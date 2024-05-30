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
	<div class="col">
		<h2>Zresetuj hasło</h2>
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
			<label class="bmd-label-floating">Email, który podałeś/aś przy rejestracji?</label>
			<input type="text" class="form-control" name="email" placeholder="Twój adres email"id="email" value="<?=set_value('email')?>">
    		<span class="text-danger"> 
    			<?= isset($validation) ? display_form_errors($validation, 'email') : ''; ?>	
    			</span>
		</div>
  		
  	<div class="form-row">
  		<input type="submit" class="btn btn-info" value="Chcę zmienić hasło &raquo;">
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
