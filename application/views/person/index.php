<h2><?php echo $title ?></h2>

<?php foreach ($person as $person_item): ?>

        <h3><?php echo $person_item->titelpre ?></h3>
        <div class="main">
                <?php echo $person_item->vorname,' ',$person_item->nachname ?>
        </div>
        <p><a href="<?php echo 'person/',$person_item->person_id ?>">View Person</a></p>

<?php endforeach ?>
