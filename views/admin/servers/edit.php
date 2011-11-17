<?php echo $this->partial('maps/admin-header.php', array('subtitle' => 'Edit Server')); ?>

<div id="primary">

    <?php echo flash(); ?>

    <h2>Edit Server "<?php echo $server->name; ?>":</h2>

    <?php echo $this->form; ?>

</div>

<?php foot(); ?>
