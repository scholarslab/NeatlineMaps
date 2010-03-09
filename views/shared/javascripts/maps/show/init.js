var init = function() {

	wgs84 = new OpenLayers.Projection("EPSG:4326");
	myproj = new OpenLayers.Projection(srs);
	// baseproj = new OpenLayers.Projection("EPSG:900913");
	
	map = new OpenLayers.Map('map', {
		projection : myproj,
		displayProjection : myproj,
		units: 'm',
		numZoomLevels : 128,
		// 'maxResolution': 156543.0339,
	    // 'maxExtent': new OpenLayers.Bounds(-20037508.34, -20037508.34,
		// 20037508.34, 20037508.34)
	});
	
	

	layer = new OpenLayers.Layer.WMS(layername, serviceaddy, {
		layers: layername
		}, {
			projection: wgs84,
		// 'transparent': true,
		gutter: 5
	});

	map.addLayers([layer]);
	/*
	 * /*
	 * var base = new OpenLayers.Layer.CloudMade("CloudMade", { 'key':
	 * 'BC9A493B41014CAABB98F0471D759707', 'styleId': 9202, 'sphericalMercator':
	 * 'true' });
	 * 
	 * var gsat = new OpenLayers.Layer.Google("Google Satellite", { 'type':
	 * G_SATELLITE_MAP, 'sphericalMercator': true, 'maxExtent': new
	 * OpenLayers.Bounds( -20037508.34, -20037508.34, 20037508.34, 20037508.34)
	 * });
	 */
	 * 
	 * // style the sketch fancy var sketchSymbolizers = { "Point": {
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
	
	map.zoomToExtent(bbox.transform(myproj,wgs84));
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
