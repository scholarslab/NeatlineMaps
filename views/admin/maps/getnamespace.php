<?php echo $this->partial('maps/admin-header.php', array('subtitle' => 'Create Map')); ?>

<div id="primary">

    <?php echo flash(); ?>

    <h2>Step 2: Select a namespace and upload map files:</h2>

    <?php echo $form; ?>

</div>

<?php foot(); ?>

