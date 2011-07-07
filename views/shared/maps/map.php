<div id="NLtmp" class="themap"></div>
<div id="mappanel" class="olControlEditingToolbar"></div>
<div id="addlayerdialog"></div>
<div id="mappreview"></div>
<?php
echo neatlinemaps_getLayerSelect($this);
?>
</div>

<script type="text/javascript">

		// insert our projection
		<?php print $map_params['proj4js']; unset($map_params['proj4js']);?>

		mapdiv = "neatlinemap" + Omeka.NeatlineMaps.history.length;
		document.getElementById('NLtmp').id = mapdiv;
		Omeka.NeatlineMaps.history.push(
				jQuery.extend(true, {"mapdiv": mapdiv}, <?php print json_encode($map_params);?>));
		jQuery(document).ready(function () { Omeka.NeatlineMaps.createMap(Omeka.NeatlineMaps.history.slice(-1)[0])} );	
		delete(mapdiv);
</script>
