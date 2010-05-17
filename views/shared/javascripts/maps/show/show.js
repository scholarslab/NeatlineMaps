if (typeof (Omeka) == 'undefined') {
	Omeka = new Object();
}

if (!Omeka.NeatlineMaps) {
	Omeka.NeatlineMaps = new Array();
}
Omeka.NeatlineMaps.createMap = function(event, config) {

	var wgs84 = new OpenLayers.Projection("EPSG:4326");
	var myproj = new OpenLayers.Projection(config.srs);
	// var baseproj = new OpenLayers.Projection("EPSG:900913");

	var map = new OpenLayers.Map(config.mapdiv, {
		'maxResolution' : 'auto',
		'numZoomLevels' : 20,
		'projection' : wgs84
	});

	var layer = new OpenLayers.Layer.WMS(config.layername, config.serviceaddy,
			{
				'layers' : config.layername
			}, {
				'buffer' : 0,
				'gutter' : 5
			});

	map.addLayers( [ layer ]);

	if (config.backgroundlayers) {
		map.addLayers(config.backgroundlayers);
	}
	Omeka.NeatlineMaps.push(map);
	config.bbox.transform(myproj, wgs84);
	map.zoomToExtent(config.bbox);

	/*
	 * /* var base = new OpenLayers.Layer.CloudMade("CloudMade", { 'key':
	 * 'BC9A493B41014CAABB98F0471D759707', 'styleId': 9202, 'sphericalMercator':
	 * 'true' });
	 * 
	 * var gsat = new OpenLayers.Layer.Google("Google Satellite", { 'type':
	 * G_SATELLITE_MAP, 'sphericalMercator': true, 'maxExtent': new
	 * OpenLayers.Bounds( -20037508.34, -20037508.34, 20037508.34, 20037508.34)
	 * });
	 *  // style the sketch fancy var sketchSymbolizers = { "Point": {
	 * pointRadius: 4, graphicName: "square", fillColor: "white", fillOpacity:
	 * 1, strokeWidth: 1, strokeOpacity: 1, strokeColor: "#333333" }, "Line": {
	 * strokeWidth: 3, strokeOpacity: 1, strokeColor: "#666666",
	 * strokeDashstyle: "dash" }, "Polygon": { strokeWidth: 2, strokeOpacity: 1,
	 * strokeColor: "#666666", fillColor: "white", fillOpacity: 0.3 } }; var
	 * style = new OpenLayers.Style(); style.addRules([ new
	 * OpenLayers.Rule({symbolizer: sketchSymbolizers}) ]); var styleMap = new
	 * OpenLayers.StyleMap({"default": style});
	 * 
	 * 
	 * measureControls = { line: new OpenLayers.Control.Measure(
	 * OpenLayers.Handler.Path, { persist: true, geodesic : true,
	 * handlerOptions: { layerOptions: {styleMap: styleMap} } } ), polygon: new
	 * OpenLayers.Control.Measure( OpenLayers.Handler.Polygon, { persist: true,
	 * 
	 * handlerOptions: { layerOptions: {styleMap: styleMap} } } ) };
	 * 
	 * var control; for(var key in measureControls) { control =
	 * measureControls[key]; control.events.on({ "measure": handleMeasurements,
	 * "measurepartial": handleMeasurements }); map.addControl(control); }
	 */

	map.addControl(new OpenLayers.Control.MousePosition());
	map.addControl(new OpenLayers.Control.Scale());
	map.addControl(new OpenLayers.Control.ScaleLine());
	map.addControl(new OpenLayers.Control.LayerSwitcher());

	if (!this.isInitialized) {
		this.isInitialized = true;
	}
}
/*
 * function toggleControl(element) { for(key in measureControls) { var control =
 * measureControls[key]; if(element.value == key && element.checked) {
 * control.activate(); } else { control.deactivate(); } } }
 * 
 * function handleMeasurements(event) { var geometry = event.geometry; var units =
 * event.units; var order = event.order; var measure = event.measure; var
 * element = document.getElementById('output'); var out = ""; if(order == 1) {
 * out += "measure: " + measure.toFixed(3) + " " + units; } else { out +=
 * "measure: " + measure.toFixed(3) + " " + units + "<sup>2</" + "sup>"; }
 * element.innerHTML = out; }
 */
