<?php echo $this->partial('maps/admin-header.php', array('subtitle' => 'Create Map')); ?>

<div id="primary" class="neatline-maps-getfiles">

    <?php echo flash(); ?>

    <h2>Step 2: Upload files and select a namespace:</h2>

    <div class="field" id="map-inputs">
        <label>Find a Map File</label>
        <div class="maps inputs">
            <input name="map[1]" id="file-1" type="file" class="fileinput" />
        </div>
    </div>

    <script>

    /**
     * Allow adding an arbitrary number of file input elements to the items form so that
     * more than one file can be uploaded at once.
     */
    Omeka.enableAddMaps = function () {
        var filesDiv = jQuery('#map-inputs .maps').first();
        var filesDivWrap = jQuery('#map-inputs');

        var link = jQuery('<a href="#" id="add-map" class="add-map tab">Add Another Map</a>');
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
        Omeka.enableAddMaps();
    });

    </script>

    <?php echo $form; ?>

</div>

<?php foot(); ?>

