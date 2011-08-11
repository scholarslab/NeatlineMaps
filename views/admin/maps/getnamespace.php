<?php echo $this->partial('maps/admin-header.php', array('subtitle' => 'Create Map')); ?>

<div id="primary" class="neatline-maps-getfiles">

    <?php echo flash(); ?>

    <h2>Step 2: Upload files and select a namespace:</h2>

    <form enctype="application/x-www-form-urlencoded" action="addmap" method="post">

    <div class="field" id="map-inputs">
        <label>Upload map files:</label>
        <div class="maps inputs">
            <input name="map[0]" id="file-1" type="file" class="fileinput" />
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

      <dl class="zend_form">

        <dt id="existing_namespace-label">
          <label for="existing_namespace" class="optional">Use existing namespace:</label>
        </dt>

        <dd id="existing_namespace-element">
          <select name="existing_namespace" id="existing_namespace">

            <option>-</option>
            <?php foreach ($namespaces as $namespace): ?>
              <option><?php echo $namespace; ?></option>
            <?php endforeach; ?>

          </select>
        </dd>

        <dt id="new_namespace-label">
          <label for="new_namespace" class="optional">Create a new namespace:</label>
        </dt>

        <dd id="new_namespace-element">
          <input type="text" name="new_namespace" id="new_namespace" value="" size="55">
        </dd>

        <dt id="new_url-label">
          <label for="new_url" class="optional">Url for new namespace:</label>
        </dt>

        <dd id="new_url-element">
          <input type="text" name="new_url" id="new_url" value="" size="55">
        </dd>

        <dd id="create_map-element">
          <input type="submit" name="create_map" id="create_map" value="Create">
        </dd>

        <dd id="item_id-element">
          <input type="hidden" name="item_id" value="<?php echo $item_id; ?>" id="item_id">
        </dd>

        <dd id="server_id-element">
          <input type="hidden" name="server_id" value="<?php echo $server_id; ?>" id="server_id">
        </dd>

        <dd id="map_name-element">
          <input type="hidden" name="map_name" value="<?php echo $map_name; ?>" id="map_name">
        </dd>

      </dl>

    </form>

</div>

<?php foot(); ?>

