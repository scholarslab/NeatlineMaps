<div id="map-title"><?php echo $mapTitle; ?></div>

<!-- The inline styling on this element is for development purposes only.
This should be decoupled from the plugin code and contained fully in the theme. -->

<div id="map" style="width: 800px; height: 512px;"></div>
<!-- <div id="map"></div> -->

<script>

jQuery(document).ready(function() {

    var map;
    OpenLayers.ProxyHost = 'proxy.cgi?url=';

    map = new OpenLayers.Map('map');

    var base = new OpenLayers.Layer.WMS('OpenLayers WMS',
        '<?php echo $wmsAddress; ?>',
        {layers: '<?php echo $layers; ?>'}
    );

    map.addLayer(base);

    map.zoomToExtent(new OpenLayers.Bounds(<?php echo $boundingBox; ?>));

});

</script>
