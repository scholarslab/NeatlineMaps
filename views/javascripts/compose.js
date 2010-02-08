Neatline = typeof (Neatline) == "undefined" ? new Object : Neatline;
Neatline.Maps = Class.create( {
});

Neatline.Maps.showComposed = function() {
	wgs84 = new OpenLayers.Projection("EPSG:4326");
	myproj = new OpenLayers.Projection(srs);
	bbox.transform(myproj,wgs84);
	map = new OpenLayers.Map( {
		projection : myproj,
		numZoomLevels : 128
	});
	//slideritems = Array();
	//sliders = Array();
	layers = new Array();
	var baselayertoggle = true;
	layernames.split(',').each( function(layername) {
		layernumber = layernames.split(',').indexOf(layername);
		var serviceaddy = serviceaddys.split(',')[layernumber];
		var layer = new OpenLayers.Layer.WMS(layername, serviceaddy, {
			layers : layername,
			SRS : "EPSG:4326"
		}, {isBaseLayer : baselayertoggle, transparent:true });
		map.addLayer(layer);
		layers.push(layer);
		/* slideritems.push({
            xtype: "gx_opacityslider",
            layer: layer,
            vertical: true,
            height: 120,
            x: 10 * layernumber,
            plugins: new GeoExt.LayerOpacitySliderTip(),
            y: 10 
        });
		var sliderdiv = document.createElement("div");
		sliderdiv.id = "slider" + layernumber;
		$(sliderdiv).setStyle({clear:"both"});
		$('sliders').appendChild( sliderdiv );
		new GeoExt.LayerOpacitySlider({
	        layer: layer,
	        aggressive: true, 
	        width: 200,
	        isFormField: true,
	        fieldLabel: "opacity",
	        renderTo: "slider" + layernumber
	    }); */
		baselayertoggle = false;
	});
    

	map.addControl(new OpenLayers.Control.LayerSwitcher() );
/*	mapPanel = new GeoExt.MapPanel({
        renderTo: 'map',
        height: 500,
        width: 1200,
        map: map,
//        items: slideritems,
        title: layernames
    });*/
	
	/* var tree = new Ext.tree.TreePanel({
        width: 145,
        height: 300,
        renderTo: "tree",
        root: new GeoExt.tree.LayerContainer({
            layerStore: mapPanel.layers,
            expanded: true
        })
    }); */
	//console.log(layers);
	
	//console.log(bbox);
	viewer = new GeoView({
        //proxy: "/proxyml/?url=",
        map: map,
        extent : bbox
    });

	map.addControl(new OpenLayers.Control.MousePosition());
	map.addControl(new OpenLayers.Control.Scale());
	map.addControl(new OpenLayers.Control.ScaleLine());
	
	map.zoomToExtent(bbox); map.zoomIn();
	if (!this.isInitialized) {
		this.isInitialized = true;
	}
}
