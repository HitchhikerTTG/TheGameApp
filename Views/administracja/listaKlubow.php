<?php
?>
<h2>Lista klubów w bazie:</h2>

    <div class="kluby">
        <?php foreach ($kluby as $klub): ?>
    <div class="klub">
     <p><?=esc($klub['ID'])?>: <?= esc($klub['Nazwa']) ?> </p>
    </div>
    <?php endforeach; ?>

<hr>