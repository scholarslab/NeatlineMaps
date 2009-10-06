var GeoView = function(config) {
	var viewer = this;
	Ext.QuickTips.init();
	var defaultConfig = {
		lang : "en",
		servers : [ /*
					 * { url : "localhost:8080/geoserver/wms", title : "Local
					 * GeoServer" }
					 */],
		server : 0
	};
	config = GeoView.util.configFromLink( {
		config : Ext.apply(defaultConfig, config)
	});
	this.config = config;
	GeoExt.language.id = config.lang;
	var rootMenu = new Ext.menu.Menu( {
		items : [ {
			text : GeoExt.i18n("Add New"),
			handler : function() {
				this.showCatalog(config.server);
			},
			scope : this
		} ]
	});
	var nodeMenu = new Ext.menu.Menu( {
		items : [ {
			text : GeoExt.i18n("Zoom to Extent"),
			handler : function() {
				var node = this.layerTree.getSelectedNodes()[0];
				var layer = this.layerTree.getLayerFromNode(node);
				layer.map.zoomToExtent(layer.maxExtent);
			},
			scope : this
		}, {
			text : GeoExt.i18n("Add New"),
			handler : function() {
				this.showCatalog(config.server);
			},
			scope : this
		}, {
			text : GeoExt.i18n("Remove Layer"),
			handler : function() {
				this.layerTree.removeSelected();
			},
			scope : this
		}, {
			text : GeoExt.i18n("Properties"),
			handler : function() {
				var node = this.layerTree.getSelectedNodes()[0];
				var layer = this.layerTree.getLayerFromNode(node);
				this.showLayerProperties(layer);
			},
			scope : this
		} ]
	});
	var multiMenu = new Ext.menu.Menu( {
		items : [ {
			text : GeoExt.i18n("Zoom to Extent"),
			handler : function() {
				var nodes = this.layerTree.getSelectedNodes();
				var layer;
				var bounds = new OpenLayers.Bounds();
				for ( var i = 0; i < nodes.length; ++i) {
					layer = this.layerTree.getLayerFromNode(nodes[i]);
					bounds.extend(layer.maxExtent);
				}
				layer.map.zoomToExtent(bounds);
			},
			scope : this
		}, {
			text : GeoExt.i18n("Add New"),
			handler : function() {
				this.showCatalog(config.server);
			},
			scope : this
		}, {
			text : GeoExt.i18n("Remove Layers"),
			handler : function() {
				this.layerTree.removeSelected();
			},
			scope : this
		}, {
			text : GeoExt.i18n("Group Layers"),
			handler : function() {
				var nodes = this.layerTree.getSelectedNodes();
				var layer;
				var layers = new Array(nodes.length);
				var names = new Array(nodes.length);
				var bounds = new OpenLayers.Bounds();
				var index = Number.POSITIVE_INFINITY;
				for ( var i = 0; i < nodes.length; ++i) {
					layer = this.layerTree.getLayerFromNode(nodes[i]);
					layers[i] = layer;
					index = Math.min(index, layer.map.getLayerIndex(layer));
					names[i] = layer.params["LAYERS"];
					bounds.extend(layer.maxExtent);
				}
				var map = layer.map;
				var title = layer.name + " " + GeoExt.i18n("Group");
				var url = layer.url;
				var vis = layer.visibility;
				var glayer = new OpenLayers.Layer.WMS(title, url, {
					layers : names.reverse().join(","),
					format : "image/png",
					transparent : "TRUE"
				}, {
					maxExtent : bounds,
					isBaseLayer : (index == 0),
					visibility : vis,
					styles : [ {
						name : "",
						title : GeoExt.i18n("default")
					} ]
				});
				Ext.each(layers, function(layer) {
					layer.destroy(false);
				});
				map.addLayer(glayer);
				if (index == 0) {
					map.setBaseLayer(glayer);
				}
				map.setLayerIndex(glayer, index);
			},
			scope : this
		} ]
	});
	var layers;
	if (config.map) {
		var len = config.map.layers.length;
		var l, layers = new Array(len);
		for ( var i = 0; i < len; ++i) {
			l = config.map.layers[i];
			layers[i] = l;
			if (config.servers.length > 0)
				layers[i] = new OpenLayers.Layer.WMS(l.title,
						config.servers[config.server].url, {
							layers : l.name,
							transparent : "TRUE",
							format : "image/png"
						}, {
							isBaseLayer : (i == 0),
							visibility : l.visible
						});
		}
	}
	function getServerPanel(server, index) {
		var panel = new Ext.Panel( {
			layout : "border",
			border : false,
			style : "padding: 5px",
			height : 45,
			items : [ {
				border : false,
				region : "west",
				width : 25,
				bodyStyle : "padding-right: 5px;",
				items : [ {
					xtype : "radio",
					name : "default_server",
					checked : (index == config.server),
					listeners : {
						"check" : function(el, checked) {
							if (checked) {
								config.server = index;
							}
						}
					}
				} ]
			}, {
				border : false,
				region : "center",
				width : 80,
				html : server.title
			}, {
				border : false,
				region : "east",
				width : 50,
				items : [ {
					xtype : "button",
					text : "X",
					tooltip : "remove " + server.title,
					handler : function() {
						viewer.removeServer(index);
					},
					scope : viewer
				} ]
			} ]
		});
		return panel;
	}
	function getAddServerPanel() {
		var panel = new Ext.FormPanel( {
			style : "padding: 5px",
			border : false,
			defaultType : "textfield",
			labelAlign : "top",
			items : [ {
				xtype : "panel",
				border : false,
				html : "New WMS",
				style : "font-weight: bold"
			}, {
				fieldLabel : GeoExt.i18n("Title"),
				emptyText : "Title for WMS",
				name : "title",
				allowBlank : false
			}, {
				fieldLabel : "URL",
				emptyText : "WMS endpoint URL",
				name : "url",
				allowBlank : false
			}, {
				xtype : "button",
				text : GeoExt.i18n("Add New"),
				handler : function() {
					var form = panel.getForm();
					if (form.isValid()) {
						this.addServer(form.getValues());
						form.reset();
					}
				},
				scope : viewer
			} ]
		});
		return panel;
	}
	function updateServersPanel(panel) {
		var existing = panel.getComponent(0);
		while (existing.items && existing.items.length) {
			existing.remove(existing.getComponent(0));
		}
		var items = getExistingServerPanels();
		for ( var i = 0; i < items.length; ++i) {
			existing.add(items[i]);
			existing.getComponent(i).doLayout();
		}
		existing.doLayout();
	}
	function getExistingServerPanels() {
		var items = [ {
			xtype : "panel",
			border : false,
			html : "Existing WMS",
			style : "font-weight: bold"
		} ];
		for ( var i = 0; i < config.servers.length; ++i) {
			items.push(getServerPanel(config.servers[i], i));
		}
		if (config.servers.length == 0) {
			items.push( {
				xtype : "panel",
				border : false,
				html : " - none -"
			});
		}
		return items;
	}
	function getCapabilitiesURL(index) {
		var url = config.servers[index].url;
		var separator = "?";
		if (url.indexOf("?") > -1) {
			separator = "&";
		}
		url += separator + "REQUEST=GetCapabilities";
		if (config.proxy && url.indexOf("http") == 0) {
			url = config.proxy + encodeURIComponent(url);
		}
		return url;
	}
	function displayCatalogError(index) {
		var serverTitle = config.servers[index].title;
		var win = new Ext.Window( {
			title : "Capabilities parsing error",
			modal : true,
			bodyStyle : "padding: 10px",
			html : "Trouble reading " + serverTitle + " capabilities. <br/>"
					+ "Please ensure that you have entered the <br/>"
					+ "URL for a valid WMS endpoint <br/>"
					+ "(e.g. 'http://localhost/geoserver/wms').<br/><br/>"
					+ "The " + serverTitle + " entry will be removed.",
			listeners : {
				"close" : function() {
					viewer.removeServer(index);
				},
				scope : viewer
			}
		});
		win.show();
	}
	Ext.apply(this, {
		propDialogs : {},
		catalogs : [],
		addCatalog : function(index) {
			this.catalogs.push(new GeoExt.component.CatalogGrid( {
				parser : new GeoExt.Parser(OpenLayers.Format.WMSCapabilities),
				url : getCapabilitiesURL(index),
				loadOnInit : true,
				addLayersToMap : OpenLayers.Function.bind(function(layers) {
					this.mapPanel.ownerCt.setActiveTab(this.mapPanel);
					var layer;
					for ( var i = 0; i < layers.length; ++i) {
						layer = layers[i];
						layer.params["TRANSPARENT"] = "TRUE";
						layer.params["FORMAT"] = "image/png";
						if (this.map.layers.length > 0) {
							layer.isBaseLayer = false;
						}
						this.map.addLayer(layer);
					}
					if (!this.map.center) {
						this.map.zoomToMaxExtent();
					}
				}, this)
			}));
		},
		removeCatalog : function(index) {
			var tab = this.centerPanel.getItem("catalog-" + index);
			if (tab) {
				this.centerPanel.remove(tab);
			}
			this.catalogs[index].destroy();
			this.catalogs.splice(index, 1);
		},
		showLayerProperties : function(layer) {
			if (!this.propDialogs[layer.id]) {
				var lf = new GeoExt.component.LayerForm( {
					layer : layer
				});
				var win = new Ext.Window( {
					title : GeoExt.i18n("Layer Properties"),
					layout : "fit",
					closeAction : "hide",
					width : 300,
					height : 300,
					plain : true,
					items : lf.form,
					buttons : [ {
						text : GeoExt.i18n("Update"),
						handler : function() {
							this.updateLayerProperties(lf);
						},
						scope : this
					}, {
						text : GeoExt.i18n("Done"),
						handler : function() {
							this.updateLayerProperties(lf);
							win.hide();
						},
						scope : this
					} ]
				});
				this.propDialogs[layer.id] = win;
			}
			this.propDialogs[layer.id].show();
		},
		updateLayerProperties : function(layerForm) {
			var layer = layerForm.layer;
			var values = layerForm.form.getForm().getValues();
			var layerChanged = false;
			if (values.title != layer.name) {
				layer.name = values.title;
				layerChanged = true;
			}
			var opacity = parseFloat(values.opacity);
			if (!isNaN(opacity)) {
				layer.setOpacity(opacity);
			}
			var paramsChanged = false;
			var newParams = {};
			if (values.style != layer.params["STYLES"]) {
				newParams["STYLES"] = values.style;
				paramsChanged = true;
			}
			if (paramsChanged) {
				layer.mergeNewParams(newParams);
			}
			if (layerChanged) {
				layer.map.events.triggerEvent("changelayer");
			}
		},
		managePropDialogs : function() {
			var numLayers = this.map.layers.length;
			var layerIds = new Array(numLayers);
			for ( var i = 0; i < numLayers; ++i) {
				layerIds[i] = this.map.layers[i].id;
			}
			for ( var key in this.propDialogs) {
				if (OpenLayers.Util.indexOf(layerIds, key) == -1) {
					this.propDialogs[key].destroy();
					delete this.propDialogs[key];
				}
			}
		},
		showCatalog : function(index) {
			var catalog = this.catalogs[index];
			if (catalog) {
				if (catalog.parserError) {
					displayCatalogError(index);
				}
				if (!catalog.loaded) {
					catalog.load();
					catalog.callOnLoad(this.showCatalog, [ index ], this);
				} else {
					var tabId = "catalog-" + index;
					var tab = this.centerPanel.getItem(tabId);
					if (!tab) {
						catalog.createGrid();
						tab = new Ext.Panel( {
							id : tabId,
							layout : 'fit',
							title : config.servers[index].title,
							closable : true,
							autoScroll : true,
							items : [ catalog.grid ]
						});
						this.centerPanel.add(tab);
					}
					this.centerPanel.setActiveTab(tab);
				}
			}
		},
		mapPanel : new GeoExt.widgets.map.MapPanel( {
			map: config.map,
			/* title : GeoExt.i18n("Map"),
			closable : false, */
			extent : config.extent,
			layers : config.layers,
			/* center : config.map && config.map.center,
			resolution : config.map && config.map.resolution, */
			listeners : {
				"render" : function() {
					this.map = this.mapPanel.map;
					this.layerTree.setMap(this.map);
					this.map.events.register("removelayer", this,
							this.managePropDialogs);
					if (!config.map) {
						this.showCatalog(config.server);
					}
				},
				scope : this
			}
		}),
		layerTree : new GeoExt.component.LayerTree( {
			title : GeoExt.i18n("Map"),
			tools : [ {
				id : "gear",
				qtip : "show available layers",
				handler : function() {
					this.showCatalog(config.server);
				},
				scope : this
			} ],
			region : "north",
			listeners : {
				"contextmenu" : function(node, evt) {
					evt.stopEvent();
					var menu;
					if (!node.parentNode) {
						menu = rootMenu;
					} else {
						var selNodes = this.layerTree.getSelectedNodes();
						if ((selNodes.length == 1 && node == selNodes[0])
								|| selNodes.length == 0) {
							node.select();
							menu = nodeMenu;
						} else {
							menu = multiMenu;
						}
					}
					menu.showAt(evt.getXY());
				},
				"beforeclick" : function(node, evt) {
					var selNodes = this.layerTree.getSelectedNodes();
					if (selNodes.length == 1 && node == selNodes[0]
							&& selNodes[0] != this.layerTree.root) {
						var layer = this.layerTree.getLayerFromNode(node);
						this.showLayerProperties(layer);
					}
				},
				scope : this
			},
			bbar : [ {
				text : "Add Layer",
				handler : function() {
					this.showCatalog(config.server)
				},
				scope : this
			}, {
				text : GeoExt.i18n("Remove"),
				handler : function(btn, evt) {
					this.layerTree.removeSelected();
				},
				scope : this
			} ]
		}),
		serversPanel : new Ext.Panel( {
			region : "center",
			title : "Servers",
			autoScroll : true,
			items : [ {
				xtype : "panel",
				border : false,
				style : "padding: 5px"
			}, {
				xtype : "panel",
				border : false,
				items : [ getAddServerPanel() ]
			} ]
		}),
		addServer : function(server) {
			config.servers.push(server);
			var index = config.servers.length - 1;
			config.server = index;
			this.addCatalog(index);
			updateServersPanel(this.serversPanel);
		},
		removeServer : function(index) {
			this.removeCatalog(index);
			config.servers.splice(index, 1);
			if (config.server == index) {
				config.server = 0;
			}
			updateServersPanel(this.serversPanel);
		},
		centerPanel : new Ext.TabPanel( {
			id : "center-panel",
			region : "center",
			enableTabScroll : true
		})
	});
	for ( var i = 0; i < config.servers.length; ++i) {
		this.addCatalog(i);
	}
	this.viewport = new Ext.Viewport( {
		layout : "border",
		items : [ new Ext.Panel( {
			id : "west-panel",
			region : "west",
			layout : "accordion",
			split : true,
			collapsible : true,
			title : GeoExt.i18n("Configuration"),
			width : 200,
			minSize : 175,
			maxSize : 400,
			margins : '0 0 0 5',
			items : [ this.layerTree, this.serversPanel ]
		}), this.centerPanel ]
	});
	updateServersPanel(this.serversPanel);
	this.centerPanel.add(this.mapPanel);
	this.centerPanel.activate(this.mapPanel);
};
Ext.BLANK_IMAGE_URL = "theme/img/blank.gif";
OpenLayers.ImgPath = "http://svn.openlayers.org/trunk/openlayers/img/";
OpenLayers.IMAGE_RELOAD_ATTEMPTS = 3;
OpenLayers.Util.onImageLoadErrorColor = "#F0E0BC";
OpenLayers.Map.prototype.getMaxExtent = function() {
	var maxExtent = null;
	var proj = this.getProjection();
	var numLayers = this.layers.length;
	if (numLayers > 0) {
		maxExtent = this.layers[0].maxExtent.clone();
	}
	var layer;
	for ( var i = 1; i < numLayers; ++i) {
		layer = this.layers[i];
		if (proj == layer.projection) {
			maxExtent.extend(this.layers[i].maxExtent);
		}
	}
	return maxExtent;
};
GeoView.util = function() {
	var makeArray = function(obj, len) {
		var list;
		if ((obj instanceof Array) && obj.length == len) {
			list = obj;
		} else if (len == 1) {
			list = [ obj ];
		} else {
			list = new Array(len);
		}
		return list;
	};
	var pub = {
		configFromLink : function(options) {
			var url = (options.url == undefined) ? window.location.search
					: options.url;
			var commas = (options.commas == undefined) ? true
					: !!options.commas;
			var data = Ext.urlDecode(url.substring(url.lastIndexOf("?") + 1));
			var val, list;
			if (commas) {
				for ( var key in data) {
					val = data[key];
					if (typeof val == "string") {
						list = data[key].split(/\s*,\s*/);
						if (list.length > 1) {
							data[key] = list;
						}
					}
				}
			}
			if (typeof data.wms == "string") {
				data.wms = {
					"default" : data.wms
				};
			}
			var layers;
			if (data.layers) {
				if (!(data.layers instanceof Array)) {
					data.layers = [ data.layers ];
				}
				var numLayers = data.layers.length;
				data.titles = makeArray(data.titles, numLayers);
				data.visible = makeArray(data.visible, numLayers);
				layers = new Array(numLayers);
				var name;
				for ( var i = 0; i < data.layers.length; ++i) {
					layers[i] = {
						name : data.layers[i],
						title : data.titles[i] ? data.titles[i]
								: data.layers[i],
						visible : (data.visible[i] == undefined || !!data.visible[i])
					}
				}
			}
			var map;
			if (layers) {
				map = {
					layers : layers
				};
				if (data.srs) {
					map.projection = data.srs;
				}
				if (data.units) {
					map.units = data.units;
				}
				if (data.center instanceof Array) {
					map.center = data.center;
					if (data.resolution) {
						map.resolution = data.resolution;
					}
				}
			}
			var config = OpenLayers.Util.extend( {}, options.config);
			OpenLayers.Util.extend(config, {
				map : map,
				lang : data.lang,
				wms : data.wms,
				catName : data.catName
			});
			return config;
		}
	};
	return pub;
}();