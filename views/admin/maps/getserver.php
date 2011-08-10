<?php echo $this->partial('maps/admin-header.php', array('subtitle' => 'Create Map')); ?>

<div id="primary">

    <?php echo flash(); ?>

    <h2>Step 1: Enter a name for the map and select the server:</h2>

    <?php echo $form; ?>

</div>

<?php foot(); ?>

