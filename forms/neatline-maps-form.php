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
    <?php foreach( $maps as $map ): ?>
        <?php $file = $map->getFile(); ?>
        <tr>
            <td><a href="<?php echo uri('maps/show/' . $map->id); ?>"><?php echo $file->original_filename; ?></a></td>
            <td class="file-link">
                <?php echo link_to($file, 'edit', 'Edit', array('class'=>'edit')); ?>
            </td>
            <td class="delete-link">
                <?php echo checkbox(array('name'=>'delete_maps[]'),false,$map->id); ?>
            </td>
        </tr>

    <?php endforeach; ?>

    </tbody>
    </table>
    </div>

<?php else: ?>

<p style="font-size: 1em; color: gray;">There are no maps for the item. Add some!</p>

<?php endif; ?>

<h3>Add New Maps:</h3>

<div id="add-more-maps">
<label for="add_num_maps">Find a File</label>
    <div class="files">
    <?php $numFiles = (int)@$_REQUEST['add_num_maps'] or $numFiles = 1; ?>
    <?php 
    echo text(array('name'=>'add_num_maps','size'=>2),$numFiles);
    echo submit('add_more_maps', 'Add this many maps'); 
    ?>
    </div>
</div>

<div class="field" id="map-inputs">
    <label>Find a Map File</label>

    <?php for($i=0;$i<$numFiles;$i++): ?>
    <div class="maps inputs">
        <input name="map[<?php echo $i; ?>]" id="file-<?php echo $i; ?>" type="file" class="fileinput" />
    </div>
    <?php endfor; ?>
</div>

<script>

/**
 * Allow adding an arbitrary number of file input elements to the items form so that
 * more than one file can be uploaded at once.
 */
Omeka.Items.enableAddMaps = function () {
    var filesDiv = jQuery('#map-inputs .maps').first();
    var filesDivWrap = jQuery('#map-inputs');

    var link = jQuery('<a href="#" id="add-map" class="add-map">Add Another Map</a>');
    link.click(function (event) {
        event.preventDefault();
        var inputs = filesDiv.find('input');
        var inputCount = inputs.length;
        var fileHtml = '<div id="mapinput' + inputCount + '" class="mapinput"><input name="map[' + inputCount + ']" id="map[' + inputCount + ']" type="file" class="mapinput" /></div>';
        jQuery(fileHtml).insertAfter(inputs.last()).hide().slideDown(200, function () {
            // Extra show fixes IE bug.
            jQuery(this).show();
        });
    });

    jQuery('#add-more-maps').html('');
    filesDivWrap.append(link);
};

jQuery(window).load(function () {
    Omeka.Items.enableAddMaps();
});

</script>
