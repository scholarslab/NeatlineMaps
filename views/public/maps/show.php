<?php echo $this->partial('maps/public-header.php', array('subtitle' => '')); ?>

<div id="primary">

    <?php echo $this->partial('maps/map.php', array('map_params' => $map_params)); ?>

</div>

<?php foot(); ?>
