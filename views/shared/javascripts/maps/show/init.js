var init = function() {

	wgs84 = new OpenLayers.Projection("EPSG:4326");
	myproj = new OpenLayers.Projection(srs);
	
	map = new OpenLayers.Map('map', {
		projection : myproj,
		//displayProjection : myproj,
		numZoomLevels : 128
	});
	layer = new OpenLayers.Layer.WMS(layername, serviceaddy, {
		layers : layername
	}, {
		projection : wgs84
	});
	map.addLayer(layer);
	/*
	// style the sketch fancy
    var sketchSymbolizers = {
        "Point": {
            pointRadius: 4,
            graphicName: "square",
            fillColor: "white",
            fillOpacity: 1,
            strokeWidth: 1,
            strokeOpacity: 1,
            strokeColor: "#333333"
        },
        "Line": {
            strokeWidth: 3,
            strokeOpacity: 1,
            strokeColor: "#666666",
            strokeDashstyle: "dash"
        },
        "Polygon": {
            strokeWidth: 2,
            strokeOpacity: 1,
            strokeColor: "#666666",
            fillColor: "white",
            fillOpacity: 0.3
        }
    };
    var style = new OpenLayers.Style();
    style.addRules([
        new OpenLayers.Rule({symbolizer: sketchSymbolizers})
    ]);
    var styleMap = new OpenLayers.StyleMap({"default": style});

	
    measureControls = {
            line: new OpenLayers.Control.Measure(
                OpenLayers.Handler.Path, {
                    persist: true,
                    geodesic : true,
                    handlerOptions: {
                        layerOptions: {styleMap: styleMap}
                    }
                }
            ),
            polygon: new OpenLayers.Control.Measure(
                OpenLayers.Handler.Polygon, {
                    persist: true,
                    
                    handlerOptions: {
                        layerOptions: {styleMap: styleMap}
                    }
                }
            )
        };

    var control;
    for(var key in measureControls) {
        control = measureControls[key];
        control.events.on({
            "measure": handleMeasurements,
            "measurepartial": handleMeasurements
        });
        map.addControl(control);
    } */

    

	
	bbox.transform(myproj,wgs84);
	map.addControl(new OpenLayers.Control.MousePosition());
	map.addControl(new OpenLayers.Control.Scale());
	map.addControl(new OpenLayers.Control.ScaleLine());
	
	map.zoomToExtent(bbox); map.zoomIn();
	if (!this.isInitialized) {
		this.isInitialized = true;
	}
}
/*
function toggleControl(element) {
    for(key in measureControls) {
        var control = measureControls[key];
        if(element.value == key && element.checked) {
            control.activate();
        } else {
            control.deactivate();
        }
    }
}

function handleMeasurements(event) {
    var geometry = event.geometry;
    var units = event.units;
    var order = event.order;
    var measure = event.measure;
    var element = document.getElementById('output');
    var out = "";
    if(order == 1) {
        out += "measure: " + measure.toFixed(3) + " " + units;
    } else {
        out += "measure: " + measure.toFixed(3) + " " + units + "<sup>2</" + "sup>";
    }
    element.innerHTML = out;
}
*/
