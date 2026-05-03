	<div class="row">
		<div class="col">
		</div>
	</div>
	<div class="row">
		<div class="col">
		<p>Ile razy już sobą grali?<b><? echo (count($h2h['h2h'])); ?></b></p>
		</div>
	</div>
	<div class="row">
		<div class="col">
		<table class="table">
  		<? foreach ($h2h['h2h'] as $mecz ) { ?>
    			<tr>
      			<th scope="row"><?=$mecz['date']?></th>
      			<td><?= $mecz['home_name']?> <?= $mecz['ft_score']?> <?= $mecz['away_name']?></td>
      			<td><?= $mecz['competition']['name']?></td>
    		</tr>
    	<? }?>
    </table>
		</div>
	</div>
	<div class="row">
		<div class="col"><h5>W jakiej są formie?</h5>[Na podstawie ostatnich rozegranych meczów]</div>
	</div>
	<div class="row">
		<div class="col">
			<h2><?=$h2h['team1']['name']?></h2>
			<div class="buttons">
  			<? 
  			$index=0;
  			foreach ($h2h['team1']['overall_form'] as $mecz ) { ?>
  				<button target="<?=$h2h['team1']['id']?><?=$index++?>" druzyna="<?=$h2h['team1']['id']?>"class="button showSingle"><?= $mecz ?></button>
  				
  			<?} ?>
  			</div>
  			<?
  			$index=0;
  			foreach ($h2h['team1_last_6'] as $mecz ) { ?>
				<div class="panel targetDiv<?=$h2h['team1']['id']?>" id="mecz<?=$h2h['team1']['id']?><?=$index++?>"><p><?=$mecz['date']?> | <?= $mecz['home_name']?> <?= $mecz['ft_score']?> <?= $mecz['away_name']?> | <?= $mecz['competition']['name']?></p></div>
  				
  			<?} ?>
		</div>

		<div class="col">
			<h2><?=$h2h['team2']['name']?></h2>
			<div class="buttons">
  			<? 
  			$index=0;
  			foreach ($h2h['team2']['overall_form'] as $mecz ) { ?>
  				<button target="<?=$h2h['team2']['id']?><?=$index++?>" druzyna="<?=$h2h['team2']['id']?>"class="button showSingle"><?= $mecz ?></button>
  				
  			<?} ?>
  			</div>
  			<?
  			$index=0;
  			foreach ($h2h['team2_last_6'] as $mecz ) { ?>
				<div class="panel targetDiv<?=$h2h['team2']['id']?>" id="mecz<?=$h2h['team2']['id']?><?=$index++?>"><p><?=$mecz['date']?> | <?= $mecz['home_name']?> <?= $mecz['ft_score']?> <?= $mecz['away_name']?> | <?= $mecz['competition']['name']?></p></div>
  				
  			<?} ?>
		</div>
</div>



<script>


$(function() {
  $('.showSingle').click(function() {
    $('.targetDiv'+$(this).attr('druzyna')).not('#mecz' + $(this).attr('target')).hide(); //a to jest odpowiedzialne za to, ktre 
    $('#mecz' + $(this).attr('target')).toggle(); // zmień stan diva, którego ID + numer targetu
  });
});



</script>
