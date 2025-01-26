<h3 style="margin-top:15px; margin-bottom: 15px">Jeszcze dziś zagrają (<?=date("Y-m-d")?>):</h3>
<table>
<?php foreach ($zaplanowaneNaDzis as $_score) { 
$ZassanaData = $_score['date']." ".$_score['time'];
$KiedyGraja = strtotime($ZassanaData.'UTC');?>

<tr style="padding-bottom: 7px; border-bottom: 1px gray solid;">
<td style="padding-right: 12px;" class="rozgrywki_<?=$_score['competition']['id']?>" title="<?=$_score['competition']['name']?>"></td><td><?=date("H:i", $KiedyGraja)?></td><Td style="padding-right: 12px;"><?=$_score['home_name']?> - <?=$_score['away_name']?></td></tr>
<tr><td>&nbsp;</td></tr>

      <?	} ?> 
</table>

<!--<p>Taaaak, tu będą wrzucane mecze turnieju w Katarze... ale jeśli szukasz typera, to znajdziesz go na <a href="https://jakiwynik.com/typowanie">https://jakiwynik.com/typowanie</a></p> -->