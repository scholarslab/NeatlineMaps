jQuery(document).ready(function() {

    var map = new L.Map('map', {
        center: new L.LatLng(-78.510, 38.040),
        zoom: 16
    });

    var testURL = 'http://lat.lib.virginia.edu:8080/geoserver2/Norfolk/wms?service=WMS&version=1.1.0&request=GetMap&layers=Norfolk:natural_polygon&styles=&bbox=1.2113974082130713E7,3464143.274647398,1.2165437412338302E7,3522169.1732959356&width=454&height=512&srs=EPSG:2284&format=application/openlayers';
    var test = new L.TileLayer.WMS(cloudmadeURL, { maxZoom: 18});

    map.addLayer(cloudmade);

});
