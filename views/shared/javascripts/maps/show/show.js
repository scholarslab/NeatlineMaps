var map = new L.Map('map');

var cloudmadeURL = 'http://lat.lib.virginia.edu:8080/geoserver2/UVA94/wms?service=WMS&version=1.1.0&request=GetMap&layers=UVA94:uva94_rivers&styles=&bbox=3497434.5,1187903.375,3499808.75,1191211.625&width=367&height=512&srs=EPSG:32147&format=image/png',
    cloudmadeAttrib = 'UVA',
    cloudmade = new L.TileLayer(cloudmadeURL, { maxZoom: 18, attribution: clourmadeAttrib})
