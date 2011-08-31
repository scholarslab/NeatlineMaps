<?php echo $this->partial('maps/admin-header.php', array('subtitle' => 'Delete File')); ?>

<div id="primary">

    <?php echo flash(); ?>

    <h2>Are you sure you want to delete file "<?php echo $file->getFile()->original_filename; ?>" from map "<?php echo $file->getMap()->name; ?>"?</h2>

    <form enctype="application/x-www-form-urlencoded" method="post">

        <label for="delete_omeka_files">
            <input type="checkbox" name="delete_omeka_files" id="delete_omeka_files" checked> Delete Omeka file?
        </label>

        <input type="submit" name="deleteconfirm_submit" id="delete_submit" class="neatline-delete" value="Delete">

    </form>

</div>

<?php foot(); ?>

