Neatline = typeof (Neatline) == "undefined" ? new Object : Neatline;

Neatline.Maps = Class.create( {
});

Neatline.Maps.showSimple = function() {
	Ext.QuickTips.init();

	wgs84 = new OpenLayers.Projection("EPSG:4326");
	myproj = new OpenLayers.Projection(srs);
	
	map = new OpenLayers.Map( {
		projection : myproj,
		displayProjection : myproj,
		numZoomLevels : 128
	});
	layer = new OpenLayers.Layer.WMS(layername, serviceaddy, {
		layers : layername
	}, {
		projection : wgs84
	});
	map.addLayer(layer);
	
	var ctrl, toolbarItems = [], action ;

    // Navigation control and DrawFeature controls
    // in the same toggle group
    action = new GeoExt.Action({
        text: "nav",
        iconCls: 'hand-icon',
        control: new OpenLayers.Control.Navigation(),
        map: map,
        // button options
        toggleGroup: "tog",
        allowDepress: true,
        pressed: true,
        tooltip: "navigate",
        // check item options
        group: "tools",
        checked: true
    });
    toolbarItems.push(action);
    
    var length = new OpenLayers.Control.Measure(OpenLayers.Handler.Path, {
		eventListeners : {
			measure : function(evt) {
				alert("The length was " + evt.measure + evt.units);
			}
		}
	});

	var area = new OpenLayers.Control.Measure(OpenLayers.Handler.Polygon, {
		eventListeners : {
			measure : function(evt) {
				alert("The area was " + evt.measure + evt.units);
			}
		}
	});
	
	action = new GeoExt.Action({
        text: "length",
        iconCls: 'ruler-icon',
        control: length,
        map: map,
        // button options
        toggleGroup: "tog",
        allowDepress: true,
        tooltip: "measure length",
        // check item options
        group: "tools"
    });
    toolbarItems.push(action);

    action = new GeoExt.Action({
        text: "area",
        iconCls: 'area-icon',
        control: area,
        map: map,
        // button options
        toggleGroup: "tog",
        allowDepress: true,
        tooltip: "measure area",
        // check item options
        group: "tools"
    });
    toolbarItems.push(action);

    // Navigation history - two "button" controls
    ctrl = new OpenLayers.Control.NavigationHistory();
    map.addControl(ctrl);

    action = new GeoExt.Action({
        text: "previous",
        control: ctrl.previous,
        disabled: true,
        tooltip: "previous in history"
    });
    toolbarItems.push(action);

    action = new GeoExt.Action({
        text: "next",
        control: ctrl.next,
        disabled: true,
        tooltip: "next in history"
    });
    toolbarItems.push(action);
    toolbarItems.push("->");

	mapPanel = new GeoExt.MapPanel( {
		renderTo : 'map',
		height : 512,
		width : 1400,
		map : map,
		title : layername,
		zoom: 4,
		tbar: toolbarItems
	});
	
	bbox.transform(myproj,wgs84);
	map.addControl(new OpenLayers.Control.MousePosition());
	map.addControl(new OpenLayers.Control.Scale());
	map.addControl(new OpenLayers.Control.ScaleLine());
	
	map.zoomToExtent(bbox); map.zoomIn();
	if (!this.isInitialized) {
		this.isInitialized = true;
	}
}
