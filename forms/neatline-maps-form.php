<?php if ( count($maps) > 0 ): ?>
    <h3>Current Maps</h3>
    <div id="file-list">
    <table>
        <thead>
            <tr>
                <th>Map Name</th>
                <th>Edit File Metadata</th>
                <th>Delete?</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach( $maps as $map ):
        $file = $map->getFile(); ?>
        <tr>
            <td><?php echo link_to($file, 'show', html_escape($file->original_filename), array()); ?></td>
            <td class="file-link">
                <?php echo link_to($file, 'edit', 'Edit', array('class'=>'edit')); ?>
            </td>
            <td class="delete-link">
                <?php echo checkbox(array('name'=>'delete_files[]'),false,$file->id); ?>
            </td>
        </tr>

    <?php endforeach; ?>

    </tbody>
    </table>
    </div>
<?php endif; ?>

<h3>Add New Maps</h3>

<div id="add-more-files">
<label for="add_num_files">Find a File</label>
    <div class="files">
    <?php $numFiles = (int)@$_REQUEST['add_num_files'] or $numFiles = 1; ?>
    <?php 
    echo text(array('name'=>'add_num_files','size'=>2),$numFiles);
    echo submit('add_more_files', 'Add this many files'); 
    ?>
    </div>
</div>

<div class="field" id="file-inputs">
    <label>Find a File</label>

    <?php for($i=0;$i<$numFiles;$i++): ?>
    <div class="files inputs">
        <input name="file[<?php echo $i; ?>]" id="file-<?php echo $i; ?>" type="file" class="fileinput" />
    </div>
    <?php endfor; ?>
</div>
