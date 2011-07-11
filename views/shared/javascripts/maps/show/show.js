jQuery(document).ready(function() {

    var map;
    OpenLayers.ProxyHost = 'proxy.cgi?url=';

    map = new OpenLayers.Map('map');

    var base = new OpenLayers.Layer.WMS('OpenLayers WMS',
        'http://lat.lib.virginia.edu:8080/geoserver2/Falmouth/wms',
        {layers: 'Falmouth:F_likelybuilds'}
    );

    map.addLayer(base);

    map.zoomToExtent(new OpenLayers.Bounds(-77.659, 18.49, -77.65, 18.497));

});
