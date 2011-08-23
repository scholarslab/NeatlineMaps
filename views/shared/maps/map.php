<div id="map-<?php echo $mapTitle; ?>" style="height: 400px;"></div>

<script>

jQuery(document).ready(function() {

    OpenLayers.IMAGE_RELOAD_ATTEMTPS = 3;
    OpenLayers.Util.onImageLoadErrorColor = "transparent";
    OpenLayers.ImgPath = 'http://js.mapbox.com/theme/dark/';

    var map;
    var untiled;
    var tiled;
    var pureCoverage = true;
    // pink tile avoidance
    OpenLayers.IMAGE_RELOAD_ATTEMPTS = 5;
    // make OL compute scale according to WMS spec
    OpenLayers.DOTS_PER_INCH = 25.4 / 0.28;

    format = 'image/png';
    if(pureCoverage) {
        format = "image/jpeg";
    }

    var bounds = new OpenLayers.Bounds(<?php echo $boundingBox; ?>);
    var options = {
        controls: [
          new OpenLayers.Control.PanZoomBar(),
          new OpenLayers.Control.Permalink('permalink'),
          new OpenLayers.Control.MousePosition(),
          new OpenLayers.Control.LayerSwitcher({'ascending': false}),
          new OpenLayers.Control.Navigation(),
          new OpenLayers.Control.ScaleLine(),
        ],
        maxExtent: bounds,
        maxResolution: 64.70332499999859,
        projection: "<?php echo $epsg; ?>",
        units: 'm'
    };
    map = new OpenLayers.Map('map-<?php echo $mapTitle; ?>', options);

    tiled = new OpenLayers.Layer.WMS(
        "Tiled", "<?php echo $wmsAddress; ?>",
        {
            LAYERS: '<?php echo $layers; ?>',
            STYLES: '',
            format: 'image/jpeg',
            tiled: !pureCoverage,
            tilesOrigin : map.maxExtent.left + ',' + map.maxExtent.bottom
        },
        {
            buffer: 0,
            displayOutsideMaxExtent: true,
            isBaseLayer: true
        }
    );

    map.addLayers([tiled]);
    map.zoomToExtent(bounds);

});

</script>
