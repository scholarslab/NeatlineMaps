<div id="map-<?php echo $mapTitle; ?>" class="neatline-maps-admin-preview"></div>

<script>

jQuery(document).ready(function() {

    OpenLayers.IMAGE_RELOAD_ATTEMTPS = 3;
    OpenLayers.Util.onImageLoadErrorColor = "transparent";
    OpenLayers.ImgPath = 'http://js.mapbox.com/theme/dark/';

    var map;
    var options = {
      displayProjection: new OpenLayers.Projection("EPSG:32617"),
      units: 'm',
      maxExtent: new OpenLayers.Bounds(<?php echo $boundingBox; ?>),
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

    var neatline_map = new OpenLayers.Layer.WMS('OpenLayers WMS',
        '<?php echo $wmsAddress; ?>',
        {layers: '<?php echo $layers; ?>', format: 'image/png'}
    );

    map.addLayer(neatline_map);

});

</script>
