Tu chce zobaczyc listę meczów aktywnego turnieju;
<table class="table">
<?php
//print_r($mecze);

foreach ($mecze as $mecz) {
//    $url=$site_url.'/przeliczMecz/'.$mecz['iD'];
    $url=site_url('przeliczMecz/' . $mecz['Id']);
    
    echo "<tr><td>".$mecz['HomeName']."</td><td>".$mecz['AwayName']."</td><td><a href=\"".$url."\">Przelicz mecz&raquo;</a></td></tr>";
}

?>
</table>