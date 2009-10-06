Ext.tree.TreeNodeUI.prototype.onClick = function(e) {
	if (this.dropping) {
		e.stopEvent();
		return;
	}
	if (this.fireEvent("beforeclick", this.node, e) !== false) {
		var a = e.getTarget('a');
		if (!this.disabled && this.node.attributes.href && a) {
			this.fireEvent("click", this.node, e);
			return;
		} else if (a && (e.ctrlKey || e.shiftKey)) {
			e.stopEvent();
		}
		e.preventDefault();
		if (this.disabled) {
			return;
		}
		if (this.node.attributes.singleClickExpand && !this.animating
				&& this.node.hasChildNodes()) {
			this.node.toggle();
		}
		this.fireEvent("click", this.node, e);
	} else {
		e.stopEvent();
	}
};
Ext.tree.MultiSelectionModel = function(config) {
	this.selNodes = [];
	this.selMap = {};
	this.addEvents("selectionchange");
	Ext.apply(this, config);
	Ext.tree.MultiSelectionModel.superclass.constructor.call(this);
};
Ext.extend(Ext.tree.MultiSelectionModel, Ext.util.Observable, {
	init : function(tree) {
		this.tree = tree;
		tree.getTreeEl().on("keydown", this.onKeyDown, this);
		tree.on("click", this.onNodeClick, this);
	},
	onNodeClick : function(node, e) {
		if (e.shiftKey && this.isSelected(this.lastSelNode)) {
			this.selectRange(this.lastSelNode, node, e, false);
		} else {
			this.select(node, e, e.ctrlKey);
		}
	},
	select : function(node, e, keepExisting) {
		if (!keepExisting) {
			this.clearSelections(true);
		}
		if (this.isSelected(node)) {
			if (e.ctrlKey) {
				this.unselect(node);
			} else {
				this.lastSelNode = node;
			}
		} else {
			this.selNodes.push(node);
			this.selMap[node.id] = node;
			this.lastSelNode = node;
			node.ui.onSelectedChange(true);
			this.fireEvent("selectionchange", this, this.selNodes);
		}
		return node;
	},
	unselect : function(node) {
		if (this.selMap[node.id]) {
			node.ui.onSelectedChange(false);
			var sn = this.selNodes;
			var index = sn.indexOf(node);
			if (index != -1) {
				this.selNodes.splice(index, 1);
			}
			delete this.selMap[node.id];
			this.fireEvent("selectionchange", this, this.selNodes);
		}
	},
	selectRange : function(startNode, endNode, e, keepExisting) {
		if (startNode == endNode) {
			this.select(startNode, e, keepExisting);
		} else {
			var nodes = [];
			var forwards = true;
			var inRange = false;
			var root = this.tree.getRootNode();
			root.cascade(function(node) {
				var cont = true;
				if (node == startNode) {
					if (inRange) {
						forwards = false;
						inRange = false;
						cont = false;
					} else {
						inRange = true;
					}
					nodes.push(node);
				} else if (node == endNode) {
					if (inRange) {
						inRange = false;
						cont = false;
					} else {
						forwards = false;
						inRange = true;
					}
					nodes.push(node);
				} else {
					if (inRange) {
						nodes.push(node);
					}
				}
				return cont;
			});
			if (!keepExisting) {
				this.clearSelections(true);
			}
			Ext.each(nodes, function(node) {
				this.selNodes.push(node);
				this.selMap[node.id] = node;
				node.ui.onSelectedChange(true);
			}, this);
			this.fireEvent("selectionchange", this, this.selNodes);
		}
	},
	clearSelections : function(suppressEvent) {
		var sn = this.selNodes;
		if (sn.length > 0) {
			for ( var i = 0, len = sn.length; i < len; i++) {
				sn[i].ui.onSelectedChange(false);
			}
			this.selNodes = [];
			this.selMap = {};
			if (suppressEvent !== true) {
				this.fireEvent("selectionchange", this, this.selNodes);
			}
		}
	},
	isSelected : function(node) {
		return this.selMap[node.id] ? true : false;
	},
	getSelectedNodes : function() {
		return this.selNodes;
	},
	onKeyDown : Ext.tree.DefaultSelectionModel.prototype.onKeyDown,
	selectNext : Ext.tree.DefaultSelectionModel.prototype.selectNext,
	selectPrevious : Ext.tree.DefaultSelectionModel.prototype.selectPrevious
});
Ext.namespace("GeoExt");
GeoExt.language = function() {
	var pub = {
		id : "en",
		set : function(entry, def) {
			this[this.id][entry] = def;
		},
		get : function(entry) {
			var def = this[this.id][entry];
			if (def == undefined) {
				throw "No dictionary entry found for " + this.id + ":" + entry;
			}
			return def;
		}
	};
	return pub;
}();
GeoExt.i18n = function() {
	return GeoExt.language.get.apply(GeoExt.language, arguments);
};
GeoExt.language.en = {
	"Layers" : "Layers",
	"Map" : "Map",
	"Configuration" : "Configuration",
	"Available Layers" : "Available Layers",
	"Select a layer to be added to the map" : "Select a layer to be added to the map",
	"Layer Title" : "Layer Title",
	"Layer Name" : "Layer Name",
	"Layer Options" : "Layer Options",
	"Namespace" : "Namespace",
	"Item" : "Item",
	"Items" : "Items",
	"Abstract" : "Abstract",
	"Show Details" : "Show Details",
	"Add Selected" : "Add Selected",
	"Add as Group" : "Add as Group",
	"Layer" : "Layer",
	"Group" : "Group",
	"Layer Properties" : "Layer Properties",
	"Optional" : "Optional",
	"Required" : "Required",
	"Opacity" : "Opacity",
	"Style" : "Style",
	"Style Title" : "Style Title",
	"Select a style..." : "Select a style...",
	"Zoom to Extent" : "Zoom to Extent",
	"Add New" : "Add New",
	"Remove" : "Remove",
	"Remove Layer" : "Remove Layer",
	"Remove Layers" : "Remove Layers",
	"Group Layers" : "Group Layers",
	"Properties" : "Properties",
	"Title" : "Title",
	"Type" : "Type",
	"Visible" : "Visible",
	"Active" : "Active",
	"Update" : "Update",
	"Done" : "Done",
	"Cancel" : "Cancel",
	"default" : "default",
	"GeoWebCache Configuration" : "GeoWebCache Configuration",
	"Cached" : "Cached",
	"Save Changes" : "Save Changes",
	"Reference Systems" : "Reference Systems",
	"SRS" : "SRS",
	"Zoom Start" : "Zoom Start",
	"Zoom Stop" : "Zoom Stop",
	"Vendor Parameters" : "Vendor Parameters",
	"Format" : "Format",
	"Formats" : "Formats",
	"Select format to cache..." : "Select format to cache...",
	"Add to Cache" : "Add to Cache"
};
Ext.namespace("GeoExt.widgets.layer");
GeoExt.widgets.layer.LayerTree = Ext.extend(Ext.tree.TreePanel, {
	map : null,
	expanded : true,
	selModel : null,
	autoScroll : true,
	animate : false,
	enableDD : true,
	containerScroll : true,
	initComponent : function() {
		if (!this.root) {
			this.root = new Ext.tree.TreeNode( {
				text : GeoExt.i18n("Layers"),
				singleClickExpand : true,
				expandable : true,
				expanded : this.expanded,
				draggable : false
			});
		}
		this.selModel = new Ext.tree.MultiSelectionModel();
		GeoExt.widgets.layer.LayerTree.superclass.initComponent.call(this);
		if (this.map) {
			this.setMap(this.map);
		}
	},
	onDestroy : function() {
		GeoExt.component.LayerTree.superclass.onDestroy.call(this);
		delete this.map;
	},
	setMap : function(map) {
		this.map = map;
		this.map.events.register("addlayer", this, this.redraw);
		this.map.events.register("changelayer", this, this.redraw);
		this.map.events.register("removelayer", this, this.redraw);
		this.redraw();
	},
	handleLayerChecked : function(node, visible, layer) {
		layer.setVisibility(visible);
	},
	getBaseIndex : function() {
		var base = 0;
		for ( var i = 0; i < this.map.layers.length; ++i) {
			if (this.map.layers[i].displayInLayerSwitcher) {
				base = i;
				break;
			}
		}
		return base;
	},
	handleLayerMove : function(tree, node, oldParent, newParent, index) {
		var layer = arguments[arguments.length - 1];
		var baseIdx = this.getBaseIndex();
		var layerIndex = (this.map.layers.length - 1) - index;
		if (layerIndex != this.map.getLayerIndex(layer)) {
			layer.isBaseLayer = (layerIndex == baseIdx);
			this.map.setLayerIndex(layer, layerIndex);
			if (this.map.baseLayer == layer && layerIndex != baseIdx) {
				var newBase = this.map.layers[baseIdx];
				newBase.isBaseLayer = true;
				this.map.setLayerZIndex(newBase, baseIdx);
				this.map.baseLayer = null;
				this.map.setBaseLayer(newBase);
			} else if (layerIndex == baseIdx) {
				var oldBase = this.map.baseLayer;
				oldBase.isBaseLayer = false;
				this.map.setLayerZIndex(oldBase, this.map
						.getLayerIndex(oldBase));
				this.map.baseLayer = null;
				this.map.setBaseLayer(layer);
			}
			this.redraw();
		}
	},
	removeSelected : function() {
		var removed = false;
		var nodes = this.getSelectedNodes();
		var node;
		var layers = [];
		Ext.each(nodes, function(node) {
			if (node && node.parentNode) {
				layers.push(this.getLayerFromNode(node));
			}
		}, this);
		var base = false;
		Ext.each(layers, function(layer) {
			base = base || layer.isBaseLayer;
			layer.destroy(false);
		});
		if (base) {
			var baseIdx = this.getBaseIndex();
			this.map.baseLayer = null;
			if (this.map.layers.length > baseIdx) {
				var newBase = this.map.layers[baseIdx];
				newBase.isBaseLayer = true;
				this.map.setBaseLayer(newBase);
			}
		}
		return layers.length;
	},
	getSelectedNodes : function() {
		return this.getSelectionModel().getSelectedNodes();
	},
	getLayerFromNode : function(node) {
		var layer = null;
		var index = this.root.indexOf(node);
		if (index >= 0) {
			var layerIndex = this.map.layers.length - 1 - index;
			layer = this.map.layers[layerIndex];
		}
		return layer;
	},
	redraw : function() {
		var layer;
		var expanded = this.root.expanded;
		while (this.root.firstChild) {
			this.root.removeChild(this.root.firstChild);
		}
		for ( var i = this.map.layers.length - 1; i >= 0; --i) {
			layer = this.map.layers[i];
			if (layer.displayInLayerSwitcher) {
				var node = new Ext.tree.TreeNode( {
					text : layer.name,
					draggable : true,
					checked : layer.visibility,
					allowDrop : false,
					leaf : true
				});
				node.on( {
					"checkchange" : this.handleLayerChecked.createDelegate(
							this, [ layer ], true),
					"dblclick" : function(node, evt) {
						node.ui.toggleCheck(!node.ui.isChecked());
					},
					"move" : {
						fn : this.handleLayerMove.createDelegate(this,
								[ layer ], true),
						delay : 100
					}
				});
				this.root.appendChild(node);
			}
		}
		if (expanded) {
			this.root.expand();
		}
	}
});
Ext.reg("gx_layertree", GeoExt.widgets.layer.LayerTree);
Ext.namespace("GeoExt.component");
GeoExt.component.LayerTree = Ext.extend(Ext.tree.TreePanel, {
	map : null,
	tree : null,
	expanded : true,
	treeConfig : {},
	defaultConfig : {},
	selModel : null,
	autoScroll : true,
	animate : false,
	enableDD : true,
	containerScroll : true,
	initComponent : function() {
		Ext.apply(this, this.treeConfig, this.defaultConfig);
		if (!this.root) {
			this.root = new Ext.tree.TreeNode( {
				text : GeoExt.i18n("Layers"),
				singleClickExpand : true,
				expandable : true,
				expanded : this.expanded,
				draggable : false
			});
		}
		this.selModel = new Ext.tree.MultiSelectionModel();
		GeoExt.component.LayerTree.superclass.initComponent.call(this);
		this.tree = this;
		if (this.treeConfig && this.treeConfig.listeners) {
			this.on(this.treeConfig.listeners);
		}
		if (this.map) {
			this.setMap(this.map);
		}
	},
	onDestroy : function() {
		GeoExt.component.LayerTree.superclass.onDestroy.call(this);
		delete this.tree;
		delete this.map;
	},
	setMap : function(map) {
		this.map = map;
		this.map.events.register("addlayer", this, this.redraw);
		this.map.events.register("changelayer", this, this.redraw);
		this.map.events.register("removelayer", this, this.redraw);
		this.redraw();
	},
	handleLayerChecked : function(node, visible, layer) {
		layer.setVisibility(visible);
	},
	getBaseIndex : function() {
		var base = 0;
		for ( var i = 0; i < this.map.layers.length; ++i) {
			if (this.map.layers[i].displayInLayerSwitcher) {
				base = i;
				break;
			}
		}
		return base;
	},
	handleLayerMove : function(tree, node, oldParent, newParent, index) {
		var layer = arguments[arguments.length - 1];
		var baseIdx = this.getBaseIndex();
		var layerIndex = (this.map.layers.length - 1) - index;
		if (layerIndex != this.map.getLayerIndex(layer)) {
			layer.isBaseLayer = (layerIndex == baseIdx);
			this.map.setLayerIndex(layer, layerIndex);
			if (this.map.baseLayer == layer && layerIndex != baseIdx) {
				var newBase = this.map.layers[baseIdx];
				newBase.isBaseLayer = true;
				this.map.setLayerZIndex(newBase, baseIdx);
				this.map.baseLayer = null;
				this.map.setBaseLayer(newBase);
			} else if (layerIndex == baseIdx) {
				var oldBase = this.map.baseLayer;
				oldBase.isBaseLayer = false;
				this.map.setLayerZIndex(oldBase, this.map
						.getLayerIndex(oldBase));
				this.map.baseLayer = null;
				this.map.setBaseLayer(layer);
			}
			this.redraw();
		}
	},
	removeSelected : function() {
		var removed = false;
		var nodes = this.getSelectedNodes();
		var node;
		var layers = [];
		Ext.each(nodes, function(node) {
			if (node && node.parentNode) {
				layers.push(this.getLayerFromNode(node));
			}
		}, this);
		var base = false;
		Ext.each(layers, function(layer) {
			base = base || layer.isBaseLayer;
			layer.destroy(false);
		});
		if (base) {
			var baseIdx = this.getBaseIndex();
			this.map.baseLayer = null;
			if (this.map.layers.length > baseIdx) {
				var newBase = this.map.layers[baseIdx];
				newBase.isBaseLayer = true;
				this.map.setBaseLayer(newBase);
			}
		}
		return layers.length;
	},
	getSelectedNodes : function() {
		return this.getSelectionModel().getSelectedNodes();
	},
	getLayerFromNode : function(node) {
		var layer = null;
		var index = this.root.indexOf(node);
		if (index >= 0) {
			var layerIndex = this.map.layers.length - 1 - index;
			layer = this.map.layers[layerIndex];
		}
		return layer;
	},
	redraw : function() {
		var layer;
		var expanded = this.root.expanded;
		while (this.root.firstChild) {
			this.root.removeChild(this.root.firstChild);
		}
		for ( var i = this.map.layers.length - 1; i >= 0; --i) {
			layer = this.map.layers[i];
			if (layer.displayInLayerSwitcher) {
				var node = new Ext.tree.TreeNode( {
					text : layer.name,
					draggable : true,
					checked : layer.visibility,
					allowDrop : false,
					leaf : true
				});
				node.on( {
					"checkchange" : this.handleLayerChecked.createDelegate(
							this, [ layer ], true),
					"dblclick" : function(node, evt) {
						node.ui.toggleCheck(!node.ui.isChecked());
					},
					"move" : {
						fn : this.handleLayerMove.createDelegate(this,
								[ layer ], true),
						delay : 100
					}
				});
				this.root.appendChild(node);
			}
		}
		if (expanded) {
			this.root.expand();
		}
	},
	CLASS_NAME : "GeoExt.component.LayerTree"
});
Ext.reg("layertree", GeoExt.component.LayerTree);
Ext.namespace("GeoExt.component");
GeoExt.component.GroupLayerTree = Ext
		.extend(
				GeoExt.component.LayerTree,
				{
					NODE_ID_PREFIX : "ynode-",
					layerGroups : null,
					showHeaders : true,
					showVisible : true,
					showActive : false,
					enableCrossGroupDD : false,
					enableBaseLayerDrag : false,
					activeLayerId : null,
					cls : "x-column-tree",
					initComponent : function() {
						this.rootVisible = false;
						if (!this.eventModel) {
							this.eventModel = new GeoExt.component.GroupLayerTree.TreeEventModel(
									this);
						}
						GeoExt.component.GroupLayerTree.superclass.initComponent
								.call(this);
						if (!this.enableCrossGroupDD) {
							this
									.on( {
										"nodedragover" : function(e) {
											return e.dropNode.parentNode == e.target.parentNode;
										}
									});
						}
						this.addEvents("activelayerchange");
					},
					setMap : function(map) {
						if (!this.layerGroups) {
							this.layerGroups = {};
							this.layerGroups[GeoExt.i18n("Layers")] = {
								layers : this.map.layers
							};
						}
						GeoExt.component.GroupLayerTree.superclass.setMap
								.apply(this, arguments);
						this.map.events.unregister("changelayer", this,
								this.redraw);
						this.map.events.register("changelayer", this,
								this.onChangelayer);
					},
					onRender : function(ct, position) {
						GeoExt.component.GroupLayerTree.superclass.onRender
								.call(this, ct, position);
						if (this.showHeaders) {
							var headers = this.body.insertFirst( {
								tag : "div",
								cls : "x-tree-headers"
							}, this.innerCt);
							headers.createChild( {
								cls : 'x-tree-hd x-tree-hd-leftmost',
								cn : {
									cls : 'x-tree-hd-text',
									html : GeoExt.i18n("Layer")
								}
							});
							var i = 0;
							if (this.showActive) {
								i++;
								headers.createChild( {
									cls : 'x-tree-hd x-tree-hd-extrarow-' + i,
									cn : {
										cls : 'x-tree-hd-text',
										html : GeoExt.i18n("Active")
									}
								})
							}
							if (this.showVisible) {
								i++;
								headers.createChild( {
									cls : 'x-tree-hd x-tree-hd-extrarow-' + i,
									cn : {
										cls : 'x-tree-hd-text',
										html : GeoExt.i18n("Visible")
									}
								})
							}
							headers.createChild( {
								cls : 'x-clear'
							});
							var ctWrap = this.innerCt.wrap();
							ctWrap.setStyle("overflow", "auto");
							ctWrap.setStyle("overflow-x", "hidden");
							ctWrap.setStyle("position", "relative");
							function setSize(e) {
								this.body.setStyle("overflow", "hidden");
								ctWrap.setHeight(this.getInnerHeight()
										- (Ext.isBorderBox ? 0 : 2)
										- headers.getHeight()
										- headers.getBorderWidth("tb"));
								var scrollbarWidth = ctWrap.getWidth()
										- this.innerCt.getWidth();
								headers.setWidth(ctWrap.getWidth()
										- scrollbarWidth);
								this.innerCt.repaint();
							}
							this.on( {
								"resize" : setSize,
								"expandnode" : setSize,
								"collapsenode" : setSize,
								"insert" : setSize,
								"append" : setSize,
								"remove" : setSize
							});
						}
					},
					removeGroup : function(groupNode) {
						while (groupNode.firstChild) {
							groupNode.removeChild(groupNode.firstChild);
						}
						this.root.removeChild(groupNode);
					},
					getLayerFromNode : function(node) {
						var layerId = node.id.replace(this.NODE_ID_PREFIX, "");
						var layers = this.map.getLayersBy("id", layerId);
						return layers.length ? layers[0] : null;
					},
					getNodeFromLayer : function(layer) {
						var nodeId = this.NODE_ID_PREFIX + layer.id;
						return this.tree.getNodeById(nodeId);
					},
					redraw : function() {
						var groupNode, child, expanded, layer, layers, checked;
						for ( var group in this.layerGroups) {
							layers = this.layerGroups[group].layers;
							layers.sort(function(a, b) {
								return a.map.getLayerIndex(a)
										- b.map.getLayerIndex(b);
							});
							expanded = false;
							groupNode = this.root.findChild("text", group);
							if (groupNode) {
								expanded = groupNode.isExpanded();
								while (groupNode.firstChild) {
									child = groupNode.firstChild;
									groupNode.removeChild(child);
								}
							} else {
								var initialConfig = {
									text : group,
									checked : false,
									expanded : false,
									draggable : false,
									leaf : false,
									uiProvider : GeoExt.component.GroupLayerTree.TreeNodeUI
								};
								var config = OpenLayers.Util.extend(
										initialConfig, this.layerGroups[group]);
								groupNode = new Ext.tree.TreeNode(config);
								groupNode
										.on( {
											"checkchange" : function(node) {
												var checked = node.ui
														.isChecked();
												var lr;
												Ext
														.each(
																node.childNodes,
																function(child) {
																	lr = this
																			.getLayerFromNode(child);
																	if (!lr) {
																		return;
																	}
																	if (lr.isBaseLayer === false) {
																		lr
																				.setVisibility(checked);
																	} else {
																		lr.map.baseLayer
																				.setVisibility(checked);
																		return false;
																	}
																}, this);
												this.updateGroupCheckbox(node);
											},
											scope : this
										});
								this.root.appendChild(groupNode);
							}
							for ( var i = this.layerGroups[group].layers.length - 1; i >= 0; --i) {
								layer = layers[i];
								if (layer.displayInLayerSwitcher) {
									var node = new Ext.tree.TreeNode(
											Ext
													.apply(
															{
																id : this.NODE_ID_PREFIX
																		+ layer.id,
																text : layer.name,
																expanded : expanded[this.NODE_ID_PREFIX
																		+ layer.id],
																draggable : !!layer.isBaseLayer ? this.enableBaseLayerDrag
																		: true,
																checked : layer
																		.getVisibility(),
																active : layer.notQueriable ? "hack"
																		: this.activeLayerId == layer.id,
																allowDrop : false,
																leaf : false,
																uiProvider : GeoExt.component.GroupLayerTree.TreeNodeUI
															},
															this.layerGroups[group].childConfig));
									node.on( {
										"activechange" : this.handleLayerActive
												.createDelegate(this,
														[ layer ], true),
										"checkchange" : this.handleLayerChecked
												.createDelegate(this,
														[ layer ], true),
										"dblclick" : function(node, evt) {
											node.ui.toggleCheck(!node.ui
													.isChecked());
										},
										"move" : {
											fn : this.handleLayerMove,
											scope : this,
											delay : 100
										}
									});
									this.addLegendGraphic(node, layer);
									groupNode.appendChild(node);
								}
							}
							this.updateGroupCheckbox(groupNode);
							if (expanded) {
								groupNode.expand();
							}
						}
						Ext.each(this.root.childNodes, function(node) {
							if (!this.layerGroups[node.text].layers) {
								this.removeGroup(node);
							}
						}, this);
					},
					onChangelayer : function(e) {
						if (e.property == "order") {
							return;
						} else if (e.property == "visibility") {
							var layerNode = this.getNodeFromLayer(e.layer);
							var visibility = e.layer.getVisibility();
							if (layerNode
									&& layerNode.attributes.checked != visibility) {
								layerNode.attributes.checked = visibility;
								layerNode.ui.toggleCheck(visibility);
							}
						} else {
							this.redraw();
						}
					},
					addLegendGraphic : function(node, layer) {
						var graphicUrl;
						if (layer instanceof OpenLayers.Layer.WMS) {
							if (layer.legendGraphicUrl) {
								graphicUrl = layer.legendGraphicUrl;
							} else if (layer.params) {
								graphicUrl = layer.url;
								var params = {
									"REQUEST" : "GetLegendGraphic",
									"LAYER" : layer.params.LAYERS,
									"VERSION" : layer.params.VERSION,
									"FORMAT" : layer.params.FORMAT,
									"STYLE" : layer.params.STYLES,
									"TRANSPARENT" : true,
									"WIDTH" : 16,
									"HEIGHT" : 18
								};
								graphicUrl += OpenLayers.Util
										.getParameterString(params);
							}
						}
						if (graphicUrl) {
							var image = new Image();
							image.onload = function() {
								node.ui.setIcon(graphicUrl);
							}
							image.src = graphicUrl;
						}
					},
					handleLayerChecked : function(node, visible, layer) {
						if (layer != this.map.baseLayer
								&& layer.isBaseLayer !== false) {
							this.map.baseLayer.setVisibility(false);
							this.map.setBaseLayer(layer);
						}
						layer.setVisibility(visible);
						this.updateGroupCheckbox(node.parentNode);
					},
					handleLayerActive : function(node, layer, silent) {
						if (this.activeLayerId) {
							var oldNode = this.getNodeById(this.NODE_ID_PREFIX
									+ this.activeLayerId);
							oldNode.attributes.active = false;
							if (oldNode.ui.radio) {
								oldNode.ui.radio.checked = false;
							}
						}
						if (node) {
							node.attributes.active = true;
							if (node.ui.radio) {
								node.ui.radio.checked = true;
							}
						}
						this.activeLayerId = layer.id;
						if (silent !== true) {
							this.fireEvent("activelayerchange", layer)
						}
					},
					handleLayerMove : function(tree, fromNode, oldParent,
							newParent, index, toNode) {
						var fromLayer = this.getLayerFromNode(fromNode);
						if (!toNode) {
							toNode = oldParent.childNodes[oldParent.childNodes.length - 2];
						}
						var toLayer = this.getLayerFromNode(toNode);
						var fromLayerIndex = this.map.getLayerIndex(fromLayer);
						var toLayerIndex = this.map.getLayerIndex(toLayer);
						this.map.setLayerIndex(fromLayer, toLayerIndex);
					},
					updateGroupCheckbox : function(groupNode) {
						var checked = 0;
						for ( var i = 0, len = groupNode.childNodes.length; i < len; ++i) {
							checked += (groupNode.childNodes[i].attributes.checked ? 1
									: 0);
						}
						this.setChecked(groupNode, checked > 0, checked > 0
								&& checked < i);
					},
					setChecked : function(node, checked, thirdState) {
						node.attributes.checked = checked;
						if (node.rendered) {
							node.ui.checkbox.style.opacity = (thirdState === true) ? 0.5
									: 1;
							node.ui.checkbox.style.filter = (thirdState === true) ? "alpha(opacity=50)"
									: "alpha(opacity=100)";
							node.ui.checkbox.checked = checked;
						}
					},
					CLASS_NAME : "GeoExt.component.GroupLayerTree"
				});
Ext.reg("grouplayertree", GeoExt.component.GroupLayerTree);
GeoExt.component.GroupLayerTree.TreeEventModel = Ext
		.extend(
				Ext.tree.TreeEventModel,
				{
					delegateClick : function(e, t) {
						if (!this.beforeEvent(e)) {
							return;
						}
						if (e.getTarget('input[type=radio]', 1)) {
							this.onRadioClick(e, this.getNode(e));
						} else {
							GeoExt.component.GroupLayerTree.TreeEventModel.superclass.delegateClick
									.apply(this, arguments);
						}
					},
					onRadioClick : function(e, node) {
						node.ui.onActiveChange(e);
					}
				});
GeoExt.component.GroupLayerTree.TreeNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
	renderElements : function(n, a, targetNode, bulkRender) {
		var rb = typeof a.active == 'boolean';
		var sv = this.node.ownerTree.showVisible;
		var sa = this.node.ownerTree.showActive;
		GeoExt.component.GroupLayerTree.TreeNodeUI.superclass.renderElements
				.apply(this, arguments);
		var i = 0;
		if (sa) {
			i++;
			if (rb) {
				var buf = [ '<input class="x-tree-node-extrarow-', i,
						'" type="radio" name="', this.node.ownerTree.id,
						'_radio', '"',
						(a.active ? 'checked="checked" />' : '/>') ].join("");
				this.radio = Ext.DomHelper.insertBefore(this.anchor, buf);
			}
		}
		if (sv) {
			i++;
			Ext.fly(this.checkbox).replaceClass("x-tree-node-cb",
					"x-tree-node-extrarow-" + i);
		} else {
			this.checkbox.style.display = "none";
		}
	},
	setIcon : function(url) {
		this.node.attributes.icon = url;
		this.node.attributes.showIcon = true;
		if (this.rendered) {
			this.iconNode.src = url;
			var node = Ext.fly(this.iconNode);
			node.addClass("x-tree-node-inline-icon");
			node.replaceClass(this.noIconCls, this.node.attributes.iconCls);
		}
	},
	onActiveChange : function(e) {
		this.fireEvent('activechange', this.node);
	}
});
Ext.namespace("GeoExt.component");
GeoExt.component.CatalogGrid = new OpenLayers.Class(
		{
			data : null,
			parser : null,
			url : null,
			loadOnInit : false,
			grid : null,
			loading : false,
			loaded : false,
			parserError : false,
			loadQueue : null,
			addLayersToMap : function(layers) {
			},
			initialize : function(options) {
				OpenLayers.Util.extend(this, options);
				this.loadQueue = [];
				if (this.loadOnInit) {
					this.load();
				}
			},
			destroy : function() {
				delete this.data;
				if (this.parser) {
					this.parser.destroy();
				}
				delete this.parser;
				delete this.url;
				if (this.grid) {
					this.grid.destroy();
				}
				delete this.grid;
				delete this.loading;
				delete this.loadQueue;
			},
			callOnLoad : function(func, args, scope) {
				if (this.loaded) {
					func.apply(scope, [ args ]);
				} else {
					var inQueue = false;
					var listener;
					for ( var i = 0; i < this.loadQueue.length; ++i) {
						listener = this.loadQueue[i];
						if (listener.func == func && listener.args == args
								&& listener.scope == scope) {
							inQueue = true;
							break;
						}
					}
					if (!inQueue) {
						this.loadQueue.push( {
							func : func,
							args : args,
							scope : scope
						});
					}
				}
			},
			load : function() {
				if (!this.loading) {
					this.loading = true;
					function onRead(data, response) {
						if (data) {
							this.data = data;
							this.loading = false;
							this.loaded = true;
							while (this.loadQueue.length > 0) {
								var listener = this.loadQueue.shift();
								listener.func.apply(listener.scope,
										listener.args);
							}
						} else {
							this.parserError = true;
							var msg = "trouble reading capabilities"
							OpenLayers.Console.error(msg, response);
						}
					}
					this.parser.read(this.url, onRead, this);
				} else {
					OpenLayers.Console.warn("catalog already loading", this);
				}
			},
			addSelected : function() {
				var records = this.grid.getSelectionModel().getSelections();
				var layers = new Array(records.length);
				for ( var i = 0; i < records.length; ++i) {
					layers[i] = this.getLayerFromRecord(records[i]);
				}
				this.addLayersToMap(layers);
				this.grid.getSelectionModel().clearSelections();
			},
			addSelectedAsGroup : function() {
				var records = this.grid.getSelectionModel().getSelections();
				var layer;
				if (records.length == 1) {
					layer = this.getLayerFromRecord(records[0]);
				} else if (records.length > 1) {
					layer = this.getLayerFromRecords(records);
				}
				if (layer) {
					this.addLayersToMap( [ layer ])
				}
				this.grid.getSelectionModel().clearSelections();
			},
			getLayerFromRecord : function(record) {
				var info = this.getLayerInfoByName(record.data.name);
				var layer = new OpenLayers.Layer.WMS(info.title,
						this.data.capability.request.getmap.href, {
							layers : info.name
						}, {
							minScale : info.minScale,
							maxScale : info.maxScale,
							maxExtent : OpenLayers.Bounds
									.fromArray(info.llbbox),
							description : info["abstract"],
							styles : info.styles,
							formats : info.formats
						});
				return layer;
			},
			getLayerFromRecords : function(records) {
				var info = this.getLayerInfoByName(records[0].data.name);
				var title = info.title + " " + GeoExt.i18n("Group");
				var url = this.data.capability.request.getmap.href;
				var bounds = OpenLayers.Bounds.fromArray(info.llbbox);
				var layerNames = [ info.name ];
				for ( var i = 1; i < records.length; ++i) {
					info = this.getLayerInfoByName(records[i].data.name);
					layerNames.push(info.name);
					bounds.extend(OpenLayers.Bounds.fromArray(info.llbbox));
				}
				var layer = new OpenLayers.Layer.WMS(title, url, {
					layers : layerNames.join(",")
				}, {
					maxExtent : bounds,
					description : info["abstract"],
					styles : [ {
						name : "",
						title : GeoExt.i18n("default")
					} ]
				});
				return layer;
			},
			getLayerInfoByName : function(name) {
				var info = null;
				var records = this.data.capability.layers;
				for ( var i = 0; i < records.length; ++i) {
					if (records[i].name == name) {
						info = records[i];
						break;
					}
				}
				return info;
			},
			createGrid : function() {
				var Layer = Ext.data.Record.create( [ {
					name : 'name'
				}, {
					name : 'title'
				}, {
					name : 'prefix'
				}, {
					name : 'abstract'
				} ]);
				var reader = new Ext.data.JsonReader( {
					root : "layers"
				}, Layer);
				var grid = new Ext.grid.GridPanel(
						{
							store : new Ext.data.GroupingStore( {
								reader : reader,
								data : this.data.capability,
								sortInfo : {
									field : 'name',
									direction : "ASC"
								},
								groupField : 'prefix'
							}),
							columns : [ {
								id : 'name',
								header : GeoExt.i18n("Layer Name"),
								width : 60,
								sortable : true,
								dataIndex : 'name'
							}, {
								header : GeoExt.i18n("Layer Title"),
								width : 120,
								sortable : true,
								dataIndex : 'title'
							}, {
								header : GeoExt.i18n("Namespace"),
								width : 20,
								sortable : true,
								dataIndex : 'prefix'
							} ],
							view : new Ext.grid.GroupingView(
									{
										forceFit : true,
										groupTextTpl : '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "'
												+ GeoExt.i18n('Items')
												+ '" : "'
												+ GeoExt.i18n('Item')
												+ '"]})',
										enableRowBody : true,
										showAbstract : false,
										getRowClass : function(record, index,
												rowParams, store) {
											if (this.showAbstract) {
												var img = '<img src="theme/images/custom/details.gif" style="vertical-align: text-bottom;" />';
												rowParams.body = '<p>'
														+ img
														+ '&nbsp;'
														+ GeoExt
																.i18n("Abstract")
														+ ": "
														+ record.data.abstract
														+ '</p>';
												return 'x-grid3-row-expanded';
											}
											return 'x-grid3-row-collapsed';
										}
									}),
							sm : new Ext.grid.RowSelectionModel( {
								singleSelect : false
							}),
							frame : false,
							width : 700,
							height : 450,
							title : GeoExt
									.i18n("Select a layer to be added to the map"),
							iconCls : 'icon-grid',
							bbar : new Ext.Toolbar( {
								items : [ '->', {
									text : GeoExt.i18n("Add Selected"),
									handler : this.addSelected,
									scope : this
								}, {
									pressed : false,
									enableToggle : true,
									text : GeoExt.i18n("Show Details"),
									cls : 'x-btn-text-icon details',
									toggleHandler : toggleAbstract
								} ]
							})
						});
				var onDblclick = function(grid, rowIndex, evt) {
					var record = grid.store.getAt(rowIndex);
					var layer = this.getLayerFromRecord(record);
					this.addLayersToMap( [ layer ]);
					this.grid.getSelectionModel().clearSelections();
				}
				var rowMenu = new Ext.menu.Menu( {
					items : [ {
						text : GeoExt.i18n("Add Selected"),
						handler : this.addSelected,
						scope : this
					} ]
				});
				var rowsMenu = new Ext.menu.Menu( {
					items : [ {
						text : GeoExt.i18n("Add Selected"),
						handler : this.addSelected,
						scope : this
					}, {
						text : GeoExt.i18n("Add as Group"),
						handler : this.addSelectedAsGroup,
						scope : this
					} ]
				});
				grid
						.on( {
							"rowdblclick" : onDblclick,
							"rowcontextmenu" : function(grid, rowIndex, evt) {
								var records = this.grid.getSelectionModel()
										.getSelections();
								if (records.length > 0) {
									var record = this.grid.getStore().getAt(
											rowIndex);
									if (OpenLayers.Util
											.indexOf(records, record) != -1) {
										evt.stopEvent();
										if (records.length == 1) {
											rowMenu.showAt(evt.getXY());
										} else {
											rowsMenu.showAt(evt.getXY());
										}
									}
								}
							},
							scope : this
						});
				function toggleAbstract(btn, pressed) {
					var view = grid.getView();
					view.showAbstract = pressed;
					view.refresh();
				}
				this.grid = grid;
			},
			CLASS_NAME : "GeoExt.component.CatalogGrid"
		});
Ext.namespace("GeoExt.component");
GeoExt.component.LayerForm = new OpenLayers.Class(
		{
			layer : null,
			form : null,
			formConfig : null,
			initialize : function(options) {
				OpenLayers.Util.extend(this, options);
				if (!this.layer.styles) {
					this.layer.styles = [ {
						name : "",
						title : GeoExt.i18n("default")
					} ];
				}
				var opacityField = new Ext.form.Hidden( {
					name : "opacity",
					minValue : 0,
					maxValue : 1,
					allowBlank : true
				});
				var config = {
					labelWidth : 50,
					bodyStyle : 'padding: 10px',
					defaultType : 'textfield',
					onSubmit : Ext.emptyFn,
					items : [
							{
								xtype : "fieldset",
								title : GeoExt.i18n("Required"),
								collapsible : false,
								autoHeight : true,
								defaults : {
									width : 150
								},
								defaultType : 'textfield',
								items : [ {
									fieldLabel : GeoExt.i18n("Title"),
									name : "title",
									value : this.layer.name,
									allowBlank : false
								} ]
							},
							{
								xtype : "fieldset",
								title : GeoExt.i18n("Optional"),
								collapsible : true,
								autoHeight : true,
								defaults : {
									width : 150
								},
								defaultType : 'textfield',
								items : [
										opacityField,
										{
											xtype : "panel",
											layout : "border",
											height : 22,
											width : 185,
											border : false,
											defaults : {
												border : false
											},
											items : [
													{
														region : "west",
														html : "Opacity:",
														bodyStyle : "font-size: 12px;",
														width : 50
													},
													{
														region : "center",
														bodyStyle : "padding-left: 20px;",
														items : [ {
															xtype : "slider",
															width : 115,
															minValue : 0,
															maxValue : 100,
															value : (typeof this.layer.opacity == "number") ? this.layer.opacity * 100
																	: 100,
															listeners : {
																"change" : function(
																		el,
																		value) {
																	var opacity = value / 100;
																	opacityField
																			.setValue(opacity);
																	this.layer
																			.setOpacity(opacity);
																},
																scope : this
															}
														} ]
													} ]
										},
										new Ext.form.ComboBox( {
											fieldLabel : GeoExt.i18n("Style"),
											hiddenName : 'style',
											store : new Ext.data.JsonStore( {
												data : this.layer,
												root : "styles",
												fields : [ "name", "title",
														"abstract" ]
											}),
											valueField : 'name',
											displayField : 'title',
											typeAhead : true,
											mode : 'local',
											triggerAction : 'all',
											emptyText : GeoExt
													.i18n("Select a style..."),
											selectOnFocus : true,
											width : 190
										}),
										new Ext.form.ComboBox(
												{
													fieldLabel : "Format",
													hiddenName : 'format',
													store : new Ext.data.SimpleStore(
															{
																data : this.layer.formats
																		|| [],
																expandData : true,
																fields : [ "format" ]
															}),
													valueField : "format",
													displayField : "format",
													typeAhead : true,
													mode : 'local',
													triggerAction : 'all',
													emptyText : "Select a format...",
													selectOnFocus : true,
													width : 190
												}) ]
							} ]
				};
				OpenLayers.Util.extend(config, this.defaultConfig);
				OpenLayers.Util.extend(config, this.formConfig);
				this.form = new Ext.FormPanel(config);
			},
			destroy : function() {
				delete this.layer;
				delete this.map;
				this.panel.remove(this.tab);
				delete this.panel;
				delete this.tab;
			},
			update : function(config) {
				var params = {
					layers : config.name,
					styles : config.style,
					transparent : "TRUE"
				};
				var opacity = parseFloat(config.opacity);
				if (isNaN(opacity)) {
					opacity = undefined;
				}
				var options = {
					maxExtent : new OpenLayers.Bounds.fromArray(config.extent),
					opacity : opacity,
					isBaseLayer : (!config.type || config.type == "baselayer"),
					description : config.description,
					visibility : !!config.visible
				};
				this.layer.name = config.title;
				OpenLayers.Util.extend(this.layer, options);
				OpenLayers.Util.extend(this.layer.params, params);
			},
			CLASS_NAME : "GeoExt.component.LayerForm"
		});
Ext.namespace("GeoExt.widgets.map");
GeoExt.widgets.map.MapPanel = Ext.extend(Ext.Panel, {
	initComponent : function() {
		var defConfig = {
			plain : true,
			border : false
		};
		//console.log("GeoExt.widgets.map.MapPanel.initComponent()");
		Ext.applyIf(this, defConfig);
		GeoExt.widgets.map.MapPanel.superclass.initComponent.call(this);
	},
	onRender : function() {
		GeoExt.widgets.map.MapPanel.superclass.onRender.apply(this, arguments);
		this.map = new OpenLayers.Map(this.body.dom, this.mapOptions);
	},
	afterRender : function() {
		var size = this.ownerCt.getSize();
		Ext.applyIf(this, size);
		GeoExt.widgets.map.MapPanel.superclass.afterRender.call(this);
		if (this.controls instanceof Array) {
			this.addControls(this.controls);
		}
		if (this.layers instanceof Array) {
			this.addLayers(this.layers);
			if (this.center) {
				var location = new OpenLayers.LonLat(center[0], center[1]);
				var zoom;
				if (this.resolution) {
					zoom = this.map.getZoomForResolution(this.resolution);
				}
				this.map.setCenter(location, zoom);
			} else {
				this.map.zoomToMaxExtent();
			}
		}
		this.ownerCt.on( {
			"move" : this.updateMapSize,
			scope : this
		});
	},
	updateMapSize : function() {
		if (this.map) {
			this.map.updateSize();
		}
	},
	onResize : function(w, h) {
		this.updateMapSize();
		GeoExt.widgets.map.MapPanel.superclass.onResize.call(this, w, h);
	},
	setSize : function(width, height, animate) {
		this.updateMapSize();
		GeoExt.widgets.map.MapPanel.superclass.setSize.call(this, width,
				height, animate);
	},
	getCenter : function() {
		return this.map.getCenter();
	},
	getZoom : function() {
		return this.map.getZoom();
	},
	getResolution : function() {
		return this.map.getResolution();
	},
	getExtent : function() {
		return this.map.getExtent();
	},
	addControls : function(controls) {
		for ( var i = 0, len = controls.length; i < len; ++i) {
			this.map.addControl(controls[i]);
		}
	},
	addLayers : function(layers) {
		this.map.addLayers(layers);
	}
});
Ext.reg('gx_mappanel', GeoExt.widgets.map.MapPanel);
Ext.namespace("GeoExt");
GeoExt.Parser = new OpenLayers.Class( {
	format : null,
	reading : false,
	initialize : function(format) {
		this.format = new format();
	},
	destroy : function() {
		delete this.format;
		delete this.reading;
	},
	read : function(url, callback, scope) {
		var success = function(request) {
			this.reading = false;
			var doc = request.responseXML;
			if (!doc || !doc.documentElement) {
				doc = request.responseText;
			}
			try {
				data = this.format.read(doc);
			} catch (err) {
				OpenLayers.Console.error(err);
			}
			callback.apply(scope, [ data, {
				request : request
			} ]);
		};
		var failure = function(request) {
			this.reading = false;
			callback.apply(scope, [ false, {
				request : request,
				message : "read failed"
			} ]);
		};
		if (!this.reading) {
			this.reading = true;
			OpenLayers.Request.GET( {
				url : url,
				success : success,
				failure : failure,
				scope : this
			});
		} else {
			OpenLayers.Console.warn("still reading capabilities");
		}
	},
	CLASS_NAME : "GeoExt.Parser"
});