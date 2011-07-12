<?php if ( count($maps) > 0 ): ?>
    <h3>Current Maps</h3>
    <div id="file-list">
    <table>
        <thead>
            <tr>
                <th>Map Name</th>
                <th>Edit File Metadata</th>
            </tr>
        </thead>
        <tbody>
    <?php foreach( $maps as $map ): ?>
        <?php $file = $map->getFile(); ?>
        <tr>
            <td><?php echo link_to($file, 'show', html_escape($file->original_filename), array()); ?></td>
            <td class="file-link">
                <?php echo link_to($file, 'edit', 'Edit', array('class'=>'edit')); ?>
            </td>
        </tr>

    <?php endforeach; ?>

    </tbody>
    </table>
    </div>
<?php endif; ?>

<h3>Add New Maps:</h3>

<p style="font-size: 1em; color: gray;">To add new maps, upload the georeferenced file(s) in the "Files" tab. Neatline Maps will automatically detect the file and create the map.</p>
