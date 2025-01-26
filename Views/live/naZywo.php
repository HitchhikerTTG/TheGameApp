<div id="TrwajaceMecze">

<?php 
foreach ($live as $_score){
$tabelaWydarzen=$_score['wydarzenia'];

if ($tabelaWydarzen['event']){

 ?>
<button class="collapsible rozgrywki_<?=$_score['competition_id']?>" id="m_<?=$_score['id']?>"><?=$_score['home_name']?> <?=$_score['score']?> <?=$_score['away_name']?> | <?=$_score['time']?>'
      </button>
      <div class="content" id="<?=$_score['id']?>">
<p align="left" style="font-size:11px; padding-left: 10px; margin:5px">Rozgrywki: <?=$_score['competition_name']?> (<?=$_score['competition_id']?> )</p>
            <?php 

      foreach ($tabelaWydarzen['event'] as $wydarzenie){

        switch($wydarzenie['event']){
          case "GOAL":
                  echo "<p class=".$wydarzenie['home_away']."><span alt=\"GOL GOL GOL dla Mlodych Szakali\" title=\"GOL GOL GOL dla Mlodych Szakali\">⚽️ ".$wydarzenie['time']."': <span class=\"gracz goal\">".strtolower($wydarzenie['player'])."</span></p>";          
                  break;
          case "YELLOW_CARD":
                  echo "<p class=".$wydarzenie['home_away']."><span alt=\"Żółta kartka\">🟡 ".$wydarzenie['time']."': <span class=\"gracz\">".strtolower($wydarzenie['player'])."</span></p>";          
                  break;
          case "RED_CARD":
                  echo "<p class=".$wydarzenie['home_away'].">🔴 ".$wydarzenie['time']."': <span class=\"gracz\">".strtolower($wydarzenie['player'])."</span></p>";          
                  break;
          case "YELLOW_RED_CARD":
                  echo "<p class=".$wydarzenie['home_away'].">🟡🔴 ".$wydarzenie['time']."': <span class=\"gracz\">".strtolower($wydarzenie['player'])."</span></p>";          
                  break;
                    case "GOAL_PENALTY":
                  echo "<p class=".$wydarzenie['home_away'].">⚽️<sup>K</sup> ".$wydarzenie['time']."': <span class=\"gracz goal\">".strtolower($wydarzenie['player'])."</span></p>";          
                  break;
          case "OWN_GOAL":
                  echo "<p class=".$wydarzenie['home_away'].">🤦🏻‍♂️⚽️‍ ".$wydarzenie['time']."': <span class=\"gracz goal\">".strtolower($wydarzenie['player'])."</span></p>";          
                  break;
          case "SUBSTITUTION":
                  echo "<p class=".$wydarzenie['home_away'].">🔄 ".$wydarzenie['time']."': ⬆ <span class=\"gracz subin\" style=\"color:#116b22;\">".$wydarzenie['player']."</span> - <span class=\"gracz subout\" style=\"color:#911919;\">".$wydarzenie['info']." </span> ⬇</p>";          
                  break;


          default:
                echo "<p>🤯".$wydarzenie['time']."' : ".$wydarzenie['event']." - ".$wydarzenie['player']."</p>";          


        }

       // echo "<p>".$wydarzenie['time']."' : ".$wydarzenie['event']." - ".$wydarzenie['player']."</p>";
      }
            ?>

      </div>
<?}

      else {?>
      <button class="notyetcollapsible rozgrywki_<?=$_score['competition_id']?>"><?=$_score['home_name']?> <?=$_score['score']?> <?=$_score['away_name']?> | <?=$_score['time']?>'
      </button>  
      <? }}?>
<div>
<p align="right" style="font-size:11px; padding-right: 10px">Wyniki są aktualizowane raz na minutę. Ostatnia aktualizacja <?php echo date("H:i")?></p></div>
</div>
<div id="result">

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const collapsibles = document.getElementsByClassName("collapsible");

    // Restore states from session storage
    Array.from(collapsibles).forEach(element => {
        if(sessionStorage.getItem(element.id) === "active") {
            element.classList.add("active");
            const content = element.nextElementSibling;
            content.style.maxHeight = content.scrollHeight + "px";
            content.style.transitionDuration = "0s";
        }

        // Add click listeners
        element.addEventListener("click", function() {
            this.classList.toggle("active");
            const content = this.nextElementSibling;
            const isActive = this.classList.contains("active");

            content.style.maxHeight = isActive ? content.scrollHeight + "px" : null;
            content.style.transitionDuration = "0.2s";
            sessionStorage.setItem(this.id, isActive ? "active" : "inactive");
        });
    });
});
</script>