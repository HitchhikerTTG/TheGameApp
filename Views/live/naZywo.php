
<div>
<?php if (isset($live) && is_array($live)) { ?>
<div id="TrwajaceMecze">
<?php foreach ($live as $_score) { ?>
      <button style="width:100%; margin-bottom: 5px;" id="mecz_<?=$_score['id'] ?? ''?>" class="collapsible">      
        <p class="h"><?=isset($_score['competition']['name']) ? $_score['competition']['name'] : 'Unknown Competition'?></p>
        <p class="a"><?=$_score['home_name'] ?? ''?> <?=$_score['score'] ?? ''?> <?=$_score['away_name'] ?? ''?></p>
      </button>  
      <? }}?>
</div>
<h3 style="margin-top:15px; margin-bottom: 15px">Na żywo:</h3>
<div>
<p align="right" style="font-size:11px; padding-right: 10px">Wyniki są aktualizowane raz na minutę. Ostatnia aktualizacja <?php echo date("H:i")?></p></div>
</div>
