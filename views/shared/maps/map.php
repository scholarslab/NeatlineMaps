<div id="map-<?php echo $mapTitle; ?>" class="neatline-maps-admin-preview"></div>

<script>

jQuery(document).ready(function() {

    OpenLayers.IMAGE_RELOAD_ATTEMTPS = 3;
    OpenLayers.Util.onImageLoadErrorColor = "transparent";
    OpenLayers.ImgPath = 'http://js.mapbox.com/theme/dark/';


    var map;
    var untiled;
    var tiled;
    var pureCoverage = true;
    var options = {
      projection: "<?php echo $epsg; ?>",
      maxExtent: new OpenLayers.Bounds(<?php echo $boundingBox; ?>),
      units: 'm',
      controls: [
          new OpenLayers.Control.PanZoomBar(),
          new OpenLayers.Control.Permalink('permalink'),
          new OpenLayers.Control.MousePosition(),
          new OpenLayers.Control.LayerSwitcher({'ascending': false}),
          new OpenLayers.Control.Navigation(),
          new OpenLayers.Control.ScaleLine(),
      ]
    }

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


    untiled = new OpenLayers.Layer.WMS('OpenLayers WMS',
        '<?php echo $wmsAddress; ?>',
        {
            layers: '<?php echo $layers; ?>', format: 'image/jpeg'
        },
            {singleTile: true, ratio: 1, isBaseLayer: true}
    );

    map.addLayers([untiled, tiled]);

        map.zoomToExtent(new OpenLayers.Bounds(<?php echo $boundingBox; ?>));

});

</script>
