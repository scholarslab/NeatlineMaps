/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/*
 * The code in this file is based on code taken from OpenLayers.
 *
 * Copyright (c) 2006-2007 MetaCarta, Inc., published under the Clear BSD
 * license.  See http://svn.openlayers.org/trunk/openlayers/license.txt for the
 * full text of the license.
 */
 
(function() {

    /**
     * Check to see if GeoExt.singleFile is true. It is true if the
     * GeoExt/SingleFile.js is included before this one, as it is
     * the case in single file builds.
     */
    var singleFile = (typeof GeoExt == "object" && GeoExt.singleFile);
    
    /**
     * The relative path of this script.
     */
    var scriptName = singleFile ? "GeoExt.js" : "lib/GeoExt.js";

    /**
     * Function returning the path of this script.
     */
    var getScriptLocation = function() {
        var scriptLocation = "";
        // If we load other scripts right before GeoExt using the same
        // mechanism to add script resources dynamically (e.g. OpenLayers), 
        // document.getElementsByTagName will not find the GeoExt script tag
        // in FF2. Using document.documentElement.getElementsByTagName instead
        // works around this issue.
        var scripts = document.documentElement.getElementsByTagName('script');
        for(var i=0, len=scripts.length; i<len; i++) {
            var src = scripts[i].getAttribute('src');
            if(src) {
                var index = src.lastIndexOf(scriptName); 
                // set path length for src up to a query string
                var pathLength = src.lastIndexOf('?');
                if(pathLength < 0) {
                    pathLength = src.length;
                }
                // is it found, at the end of the URL?
                if((index > -1) && (index + scriptName.length == pathLength)) {
                    scriptLocation = src.slice(0, pathLength - scriptName.length);
                    break;
                }
            }
        }
        return scriptLocation;
    };

    /**
     * If GeoExt.singleFile is false then the JavaScript files in the jsfiles
     * array are autoloaded.
     */
    if(!singleFile) {
        var jsfiles = new Array(
            "GeoExt/data/AttributeReader.js",
            "GeoExt/data/AttributeStore.js",
            "GeoExt/data/FeatureRecord.js",
            "GeoExt/data/FeatureReader.js",
            "GeoExt/data/FeatureStore.js",
            "GeoExt/data/LayerRecord.js",
            "GeoExt/data/LayerReader.js",
            "GeoExt/data/LayerStore.js",
            "GeoExt/data/ScaleStore.js",
            "GeoExt/data/WMSCapabilitiesReader.js",
            "GeoExt/data/WMSCapabilitiesStore.js",
            "GeoExt/data/WMSDescribeLayerReader.js",
            "GeoExt/data/WMSDescribeLayerStore.js",
            "GeoExt/widgets/Action.js",
            "GeoExt/data/ProtocolProxy.js",
            "GeoExt/widgets/MapPanel.js",
            "GeoExt/widgets/Popup.js",
            "GeoExt/widgets/form.js",
            "GeoExt/widgets/form/SearchAction.js",
            "GeoExt/widgets/form/BasicForm.js",
            "GeoExt/widgets/form/FormPanel.js",
            "GeoExt/widgets/tips/SliderTip.js",
            "GeoExt/widgets/tips/LayerOpacitySliderTip.js",
            "GeoExt/widgets/tips/ZoomSliderTip.js",
            "GeoExt/widgets/tree/LayerNode.js",
            "GeoExt/widgets/tree/LayerLoader.js",
            "GeoExt/widgets/tree/LayerContainer.js",
            "GeoExt/widgets/tree/BaseLayerContainer.js",
            "GeoExt/widgets/tree/OverlayLayerContainer.js",
            "GeoExt/widgets/LayerOpacitySlider.js",
            "GeoExt/widgets/LegendImage.js",
            "GeoExt/widgets/LegendWMS.js",
            "GeoExt/widgets/LegendPanel.js",
            "GeoExt/widgets/ZoomSlider.js",
            "GeoExt/widgets/grid/FeatureSelectionModel.js"
        );

        var agent = navigator.userAgent;
        var docWrite = (agent.match("MSIE") || agent.match("Safari"));
        if(docWrite) {
            var allScriptTags = new Array(jsfiles.length);
        }
        var host = getScriptLocation() + "lib/";    
        for (var i=0, len=jsfiles.length; i<len; i++) {
            if (docWrite) {
                allScriptTags[i] = "<script src='" + host + jsfiles[i] +
                                   "'></script>"; 
            } else {
                var s = document.createElement("script");
                s.src = host + jsfiles[i];
                var h = document.getElementsByTagName("head").length ? 
                           document.getElementsByTagName("head")[0] : 
                           document.body;
                h.appendChild(s);
            }
        }
        if (docWrite) {
            document.write(allScriptTags.join(""));
        }
    }
})();
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = Ext.lib.Ajax
 */

(function() {

    /** private: function[createComplete]
     *  ``Function``
     */
    var createComplete = function(fn, cb) {
        return function(request) {
            if(cb && cb[fn]) {
                cb[fn].call(cb.scope || window, {
                    responseText: request.responseText,
                    responseXML: request.responseXML,
                    argument: cb.argument
                });
            }
        };
    };

    Ext.apply(Ext.lib.Ajax, {
        /** private: method[request]
         */
        request: function(method, uri, cb, data, options) {
            options = options || {};
            var hs = options.headers;
            if(options.xmlData) {
                if(!hs || !hs["Content-Type"]) {
                    hs = hs || {};
                    hs["Content-Type"] = "text/xml";
                }
                method = (method ? method :
                    (options.method ? options.method : "POST"));
                data = options.xmlData;
            } else if(options.jsonData) {
                if(!hs || !hs["Content-Type"]) {
                    hs = hs || {};
                    hs["Content-Type"] = "application/json";
                }
                method = (method ? method :
                    (options.method ? options.method : "POST"));
                data = typeof options.jsonData == "object" ?
                       Ext.encode(options.jsonData) : options.jsonData;
            }
            return OpenLayers.Request.issue({
                success: createComplete("success", cb),
                failure: createComplete("failure", cb),
                headers: options.headers,
                method: method,
                headers: hs,
                data: data,
                url: uri
            });
        },

        /** private: method[isCallInProgress]
         *  :params request: ``Object`` The XHR object.
         */
        isCallInProgress: function(request) {
            // do not prevent our caller from calling abort()
            return true;
        },

        /** private: method[abort]
         *  :params request: ``Object`` The XHR object.
         */
        abort: function(request) {
            request.abort();
        }
    });
})();
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = AttributeReader
 *  base_link = `Ext.data.DataReader <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataReader>`_
 */
Ext.namespace("GeoExt.data");

/** api: constructor
 *  .. class:: AttributeReader(meta, recordType)
 *  
 *      :arg meta: ``Object`` Reader configuration.
 *      :arg recordType: ``Array or Ext.data.Record`` An array of field
 *          configuration objects or a record object.
 *
 *      Create a new attributes reader object.
 *      
 *      Valid meta properties:
 *      
 *      * format - ``OpenLayers.Format`` A parser for transforming the XHR response
 *        into an array of objects representing attributes.  Defaults to
 *        an ``OpenLayers.Format.WFSDescribeFeatureType`` parser.
 *      * ignore - ``Object`` Properties of the ignore object should be field names.
 *        Values are either arrays or regular expressions.
 */
GeoExt.data.AttributeReader = function(meta, recordType) {
    meta = meta || {};
    if(!meta.format) {
        meta.format = new OpenLayers.Format.WFSDescribeFeatureType();
    }
    GeoExt.data.AttributeReader.superclass.constructor.call(
        this, meta, recordType || meta.fields
    );
};

Ext.extend(GeoExt.data.AttributeReader, Ext.data.DataReader, {

    /** private: method[read]
     *  :arg request: ``Object`` The XHR object that contains the parsed doc.
     *  :return: ``Object``  A data block which is used by an ``Ext.data.Store``
     *      as a cache of ``Ext.data.Records``.
     *  
     *  This method is only used by a DataProxy which has retrieved data from a
     *  remote server.
     */
    read: function(request) {
        var data = request.responseXML;
        if(!data || !data.documentElement) {
            data = request.responseText;
        }
        return this.readRecords(data);
    },

    /** private: method[readRecords]
     *  :arg data: ``DOMElement or String or Array`` A document element or XHR
     *      response string.  As an alternative to fetching attributes data from
     *      a remote source, an array of attribute objects can be provided given
     *      that the properties of each attribute object map to a provided field
     *      name.
     *  :return: ``Object`` A data block which is used by an ``Ext.data.Store``
     *      as a cache of ``Ext.data.Records``.
     *  
     *  Create a data block containing Ext.data.Records from an XML document.
     */
    readRecords: function(data) {
        var attributes;
        if(data instanceof Array) {
            attributes = data;
        } else {
            // only works with one featureType in the doc
            attributes = this.meta.format.read(data).featureTypes[0].properties;
        }
        var recordType = this.recordType;
        var fields = recordType.prototype.fields;
        var numFields = fields.length;
        var attr, values, name, record, ignore, matches, value, records = [];
        for(var i=0, len=attributes.length; i<len; ++i) {
            ignore = false;
            attr = attributes[i];
            values = {};
            for(var j=0; j<numFields; ++j) {
                name = fields.items[j].name;
                value = attr[name];
                if(this.meta.ignore && this.meta.ignore[name]) {
                    matches = this.meta.ignore[name];
                    if(typeof matches == "string") {
                        ignore = (matches === value);
                    } else if(matches instanceof Array) {
                        ignore = (matches.indexOf(value) > -1);
                    } else if(matches instanceof RegExp) {
                        ignore = (matches.test(value));
                    }
                    if(ignore) {
                        break;
                    }
                }
                values[name] = attr[name];
            }
            if(!ignore) {
                records[records.length] = new recordType(values);
            }
        }

        return {
            success: true,
            records: records,
            totalRecords: records.length
        };

    }

});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/data/AttributeReader.js
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = AttributeStore
 *  base_link = `Ext.data.DataStore <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataStore>`_
 */
Ext.namespace("GeoExt.data");

/** api: constructor
 *  .. class:: AttributeStore(config)
 *  
 *      Small helper class to make creating stores for remotely-loaded attributes
 *      data easier. AttributeStore is pre-configured with a built-in
 *      ``Ext.data.HttpProxy`` and :class:`gxp.data.AttributeReader`.  The
 *      HttpProxy is configured to allow caching (disableCaching: false) and
 *      uses GET. If you require some other proxy/reader combination then you'll
 *      have to configure this with your own proxy or create a basic
 *      ``Ext.data.Store`` and configure as needed.
 */

/** api: config[format]
 *  ``OpenLayers.Format``
 *  A parser for transforming the XHR response into an array of objects
 *  representing attributes.  Defaults to an
 *  ``OpenLayers.Format.WFSDescribeFeatureType`` parser.
 */

/** api: config[fields]
 *  ``Array or Function``
 *  Either an array of field definition objects as passed to
 *  ``Ext.data.Record.create``, or a record constructor created using
 *  ``Ext.data.Record.create``.  Defaults to ``["name", "type"]``. 
 */
GeoExt.data.AttributeStore = function(c) {
    c = c || {};
    GeoExt.data.AttributeStore.superclass.constructor.call(
        this,
        Ext.apply(c, {
            proxy: c.proxy || (!c.data ?
                new Ext.data.HttpProxy({url: c.url, disableCaching: false, method: "GET"}) :
                undefined
            ),
            reader: new GeoExt.data.AttributeReader(
                c, c.fields || ["name", "type"]
            )
        })
    );
};
Ext.extend(GeoExt.data.AttributeStore, Ext.data.Store);
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/data/FeatureRecord.js
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = FeatureReader
 *  base_link = `Ext.data.DataReader <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataReader>`_
 */
Ext.namespace('GeoExt', 'GeoExt.data');

/** api: example
 *  Typical usage in a store:
 * 
 *  .. code-block:: javascript
 *     
 *      var store = new Ext.data.Store({
 *          reader: new GeoExt.data.FeatureReader({}, [
 *              {name: 'name', type: 'string'},
 *              {name: 'elevation', type: 'float'}
 *          ])
 *      });
 *      
 */

/** api: constructor
 *  .. class:: FeatureReader(meta, recordType)
 *   
 *      Data reader class to create an array of
 *      :class:`GeoExt.data.FeatureRecord` objects from an
 *      ``OpenLayers.Protocol.Response`` object for use in a
 *      :class:`GeoExt.data.FeatureStore` object.
 */
GeoExt.data.FeatureReader = function(meta, recordType) {
    meta = meta || {};
    if(!(recordType instanceof Function)) {
        recordType = GeoExt.data.FeatureRecord.create(
            recordType || meta.fields || {});
    }
    GeoExt.data.FeatureReader.superclass.constructor.call(
        this, meta, recordType);
};

Ext.extend(GeoExt.data.FeatureReader, Ext.data.DataReader, {

    /**
     * APIProperty: totalRecords
     * {Integer}
     */
    totalRecords: null,

    /** private: method[read]
     *  :param response: ``OpenLayers.Protocol.Response``
     *  :return: ``Object`` An object with two properties. The value of the
     *      ``records`` property is the array of records corresponding to
     *      the features. The value of the ``totalRecords" property is the
     *      number of records in the array.
     *      
     *  This method is only used by a DataProxy which has retrieved data.
     */
    read: function(response) {
        return this.readRecords(response.features);
    },

    /** api: method[readReacords]
     *  :param features: ``Array(OpenLayers.Feature.Vector)`` List of
     *      features for creating records
     *  :return: ``Object``  An object with ``records`` and ``totalRecords``
     *      properties.
     *  
     *  Create a data block containing :class:`GeoExt.data.FeatureRecord`
     *  objects from an array of features.
     */
    readRecords : function(features) {
        var records = [];

        if (features) {
            var recordType = this.recordType, fields = recordType.prototype.fields;
            var i, lenI, j, lenJ, feature, values, field, v;
            for (i = 0, lenI = features.length; i < lenI; i++) {
                feature = features[i];
                values = {};
                if (feature.attributes) {
                    for (j = 0, lenJ = fields.length; j < lenJ; j++){
                        field = fields.items[j];
                        if (/[\[\.]/.test(field.mapping)) {
                            try {
                                v = new Function("obj", "return obj." + field.mapping)(feature.attributes);
                            } catch(e){
                                v = field.defaultValue;
                            }
                        }
                        else {
                            v = feature.attributes[field.mapping || field.name] || field.defaultValue;
                        }
                        v = field.convert(v);
                        values[field.name] = v;
                    }
                }
                values.feature = feature;
                values.state = feature.state;
                values.fid = feature.fid;

                // newly inserted features need to be made into phantom records
                var id = (feature.state === OpenLayers.State.INSERT) ? undefined : feature.id;
                records[records.length] = new recordType(values, id);
            }
        }

        return {
            records: records,
            totalRecords: this.totalRecords != null ? this.totalRecords : records.length
        };
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = FeatureRecord
 *  base_link = `Ext.data.Record <http://extjs.com/deploy/dev/docs/?class=Ext.data.Record>`_
 */
Ext.namespace("GeoExt.data");

/** api: constructor
 *  .. class:: FeatureRecord
 *  
 *      A record that represents an ``OpenLayers.Feature.Vector``. This record
 *      will always have at least the following fields:
 *
 *      * feature ``OpenLayers.Feature.Vector``
 *      * state ``String``
 *      * fid ``String``
 *
 */
GeoExt.data.FeatureRecord = Ext.data.Record.create([
    {name: "feature"}, {name: "state"}, {name: "fid"}
]);

/** api: classmethod[create]
 *  :param o: ``Array`` Field definition as in ``Ext.data.Record.create``. Can
 *      be omitted if no additional fields are required.
 *  :return: ``Function`` A specialized :class:`GeoExt.data.FeatureRecord`
 *      constructor.
 *  
 *  Creates a constructor for a :class:`GeoExt.data.FeatureRecord`, optionally
 *  with additional fields.
 */
GeoExt.data.FeatureRecord.create = function(o) {
    var f = Ext.extend(GeoExt.data.FeatureRecord, {});
    var p = f.prototype;

    p.fields = new Ext.util.MixedCollection(false, function(field) {
        return field.name;
    });

    GeoExt.data.FeatureRecord.prototype.fields.each(function(f) {
        p.fields.add(f);
    });

    if(o) {
        for(var i = 0, len = o.length; i < len; i++){
            p.fields.add(new Ext.data.Field(o[i]));
        }
    }

    f.getField = function(name) {
        return p.fields.get(name);
    };

    return f;
};
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/data/FeatureReader.js
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = FeatureStore
 *  base_link = `Ext.data.DataStore <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataStore>`_
 */
Ext.namespace("GeoExt.data");

/** api: constructor
 *  .. class:: FeatureStore
 *
 *      A store containing :class:`GeoExt.data.FeatureRecord` entries that
 *      optionally synchronizes with an ``OpenLayers.Layer.Vector``.
 */

/** api: example
 *  Sample code to create a store with features from a vector layer:
 *  
 *  .. code-block:: javascript
 *
 *      var store = new GeoExt.data.FeatureStore({
 *          layer: myLayer,
 *          features: myFeatures
 *      });
 */

/**
 * Class: GeoExt.data.FeatureStoreMixin
 * A store that synchronizes a features array of an {OpenLayers.Layer.Vector} with a
 * feature store holding {<GeoExt.data.FeatureRecord>} entries.
 * 
 * This class can not be instantiated directly. Instead, it is meant to extend
 * {Ext.data.Store} or a subclass of it:
 * (start code)
 * var store = new (Ext.extend(Ext.data.Store, GeoExt.data.FeatureStoreMixin))({
 *     layer: myLayer,
 *     features: myFeatures
 * });
 * (end)
 * 
 * For convenience, a {<GeoExt.data.FeatureStore>} class is available as a
 * shortcut to the Ext.extend sequence in the above code snippet. The above
 * is equivalent to:
 * (start code)
 * var store = new GeoExt.data.FeatureStore({
 *     layer: myLayer,
 *     features: myFeatures
 * });
 * (end)
 */
GeoExt.data.FeatureStoreMixin = {
    
    /** api: config[layer]
     *  ``OpenLayers.Layer.Vector``  Layer to synchronize the store with.
     */
    layer: null,
    
    /** api: config[features]
     *  ``Array(OpenLayers.Feature.Vector)``  Features that will be added to the
     *  store (and the layer if provided).
     */

    /** api: config[reader]
     *  ``Ext.data.DataReader`` The reader used to produce records from objects
     *  features.  Default is :class:`GeoExt.data.FeatureReader`.
     */
    reader: null,

    /** api: config[addFeatureFilter]
     *  ``Function`` This function is called before a feature record is added to
     *  the store, it receives the feature from which a feature record is to be
     *  created, if it returns false then no record is added.
     */
    addFeatureFilter: null,
    
    /** api: config[addRecordFilter]
     *  ``Function`` This function is called before a feature is added to the
     *  layer, it receives the feature record associated with the feature to be
     *  added, if it returns false then no feature is added.
     */
    addRecordFilter: null,
    
    /** api: config[initDir]
     *  ``Number``  Bitfields specifying the direction to use for the
     *  initial sync between the layer and the store, if set to 0 then no
     *  initial sync is done. Default is
     *  ``GeoExt.data.FeatureStore.LAYER_TO_STORE|GeoExt.data.FeatureStore.STORE_TO_LAYER``.
     */

    /** private */
    constructor: function(config) {
        config = config || {};
        config.reader = config.reader ||
                        new GeoExt.data.FeatureReader({}, config.fields);
        var layer = config.layer;
        delete config.layer;
        // 'features' option - is an alias 'data' option
        if (config.features) {
            config.data = config.features;
        }
        delete config.features;
        // "initDir" option
        var options = {initDir: config.initDir};
        delete config.initDir;
        arguments.callee.superclass.constructor.call(this, config);
        if(layer) {
            this.bind(layer, options);
        }
    },

    /** api: method[bind]
     *  :param layer: ``OpenLayers.Layer`` Layer that the store should be
     *      synchronized with.
     *  
     *  Bind this store to a layer instance, once bound the store
     *  is synchronized with the layer and vice-versa.
     */ 
    bind: function(layer, options) {
        if(this.layer) {
            // already bound
            return;
        }
        this.layer = layer;
        options = options || {};

        var initDir = options.initDir;
        if(options.initDir == undefined) {
            initDir = GeoExt.data.FeatureStore.LAYER_TO_STORE |
                      GeoExt.data.FeatureStore.STORE_TO_LAYER;
        }

        // create a snapshot of the layer's features
        var features = layer.features.slice(0);

        if(initDir & GeoExt.data.FeatureStore.STORE_TO_LAYER) {
            var records = this.getRange();
            for(var i=records.length - 1; i>=0; i--) {
                this.layer.addFeatures([records[i].get("feature")]);
            }
        }

        if(initDir & GeoExt.data.FeatureStore.LAYER_TO_STORE) {
            this.loadData(features, true /* append */);
        }

        layer.events.on({
            "featuresadded": this.onFeaturesAdded,
            "featuresremoved": this.onFeaturesRemoved,
            "featuremodified": this.onFeatureModified,
            scope: this
        });
        this.on({
            "load": this.onLoad,
            "clear": this.onClear,
            "add": this.onAdd,
            "remove": this.onRemove,
            "update": this.onUpdate,
            scope: this
        });
    },

    /** api: method[unbind]
     *  Unbind this store from the layer it is currently bound.
     */
    unbind: function() {
        if(this.layer) {
            this.layer.events.un({
                "featuresadded": this.onFeaturesAdded,
                "featuresremoved": this.onFeaturesRemoved,
                "featuremodified": this.onFeatureModified,
                scope: this
            });
            this.un("load", this.onLoad, this);
            this.un("clear", this.onClear, this);
            this.un("add", this.onAdd, this);
            this.un("remove", this.onRemove, this);
            this.un("update", this.onUpdate, this);

            this.layer = null;
        }
    },
   
    /** api: method[getRecordFromFeature]
     *  :arg feature: ``OpenLayers.Vector.Feature``
     *  :returns: :class:`GeoExt.data.FeatureRecord` The record corresponding
     *      to the given feature.  Returns null if no record matches.
     *
     *  Get the record corresponding to a feature.
     */
    getRecordFromFeature: function(feature) {
        var record = null;
        if(feature.state !== OpenLayers.State.INSERT) {
            record = this.getById(feature.id);
        } else {
            var index = this.findBy(function(r) {
                return r.get("feature") === feature;
            });
            if(index > -1) {
                record = this.getAt(index);
            }
        }
        return record;
    },
   
    /** private: method[onFeaturesAdded]
     *  Handler for layer featuresadded event
     */
    onFeaturesAdded: function(evt) {
        if(!this._adding) {
            var features = evt.features, toAdd = features;
            if(typeof this.addFeatureFilter == "function") {
                toAdd = [];
                var i, len, feature;
                for(var i=0, len=features.length; i<len; i++) {
                    feature = features[i];
                    if(this.addFeatureFilter(feature) !== false) {
                        toAdd.push(feature);
                    }
                }
            }
            // add feature records to the store, when called with
            // append true loadData triggers an "add" event and
            // then a "load" event
            this._adding = true;
            this.loadData(toAdd, true /* append */);
            delete this._adding;
        }
    },
    
    /** private: method[onFeaturesRemoved]
     *  Handler for layer featuresremoved event
     */
    onFeaturesRemoved: function(evt){
        if(!this._removing) {
            var features = evt.features, feature, record, i;
            for(i=features.length - 1; i>=0; i--) {
                feature = features[i];
                record = this.getRecordFromFeature(feature);
                if(record !== undefined) {
                    this._removing = true;
                    this.remove(record);
                    delete this._removing;
                }
            }
        }
    },
    
    /** private: method[onFeatureModified]
     *  Handler for layer featuremodified event
     */
    onFeatureModified: function(evt) {
        if(!this._updating) {
            var feature = evt.feature;
            var record = this.getRecordFromFeature(feature);
            if(record !== undefined) {
                record.beginEdit();
                attributes = feature.attributes;
                if(attributes) {
                    var fields = this.recordType.prototype.fields;
                    for(var i=0, len=fields.length; i<len; i++) {
                        var field = fields.items[i];
                        var key = field.mapping || field.name;
                        if(key in attributes) {
                            record.set(field.name, field.convert(attributes[key]));
                        }
                    }
                }
                // the calls to set below won't trigger "update"
                // events because we called beginEdit to start a
                // "transaction", "update" will be triggered by
                // endEdit
                record.set("state", feature.state);
                record.set("fid", feature.fid);
                // Ext 3.0 does not allow circular references in objects passed
                // to record.set
                record.data["feature"] = feature;
                this._updating = true;
                record.endEdit();
                delete this._updating;
            }
        }
    },

    /** private: method[addFeaturesToLayer]
     *  Given an array of records add features to the layer. This
     *  function is used by the onLoad and onAdd handlers.
     */
    addFeaturesToLayer: function(records) {
        var i, len, features, record;
        if(typeof this.addRecordFilter == "function") {
            features = []
            for(i=0, len=records.length; i<len; i++) {
                record = records[i];
                if(this.addRecordFilter(record) !== false) {
                    features.push(record.get("feature"));
                }
            }
        } else {
            features = new Array((len=records.length));
            for(i=0; i<len; i++) {
                features[i] = records[i].get("feature");
            }
        }
        if(features.length > 0) {
            this._adding = true;
            this.layer.addFeatures(features);
            delete this._adding;
        }
    },
   
    /** private: method[onLoad]
     *  :param store: ``Ext.data.Store``
     *  :param records: ``Array(Ext.data.Record)``
     *  :param options: ``Object``
     * 
     *  Handler for store load event
     */
    onLoad: function(store, records, options) {
        // if options.add is true an "add" event was already
        // triggered, and onAdd already did the work of 
        // adding the features to the layer.
        if(!options || options.add !== true) {
            this._removing = true;
            this.layer.removeFeatures(this.layer.features);
            delete this._removing;

            this.addFeaturesToLayer(records);
        }
    },
    
    /** private: method[onClear]
     *  :param store: ``Ext.data.Store``
     *      
     *  Handler for store clear event
     */
    onClear: function(store) {
        this._removing = true;
        this.layer.removeFeatures(this.layer.features);
        delete this._removing;
    },
    
    /** private: method[onAdd]
     *  :param store: ``Ext.data.Store``
     *  :param records: ``Array(Ext.data.Record)``
     *  :param index: ``Number``
     * 
     *  Handler for store add event
     */
    onAdd: function(store, records, index) {
        if(!this._adding) {
            // addFeaturesToLayer takes care of setting
            // this._adding to true and deleting it
            this.addFeaturesToLayer(records);
        }
    },
    
    /** private: method[onRemove]
     *  :param store: ``Ext.data.Store``
     *  :param records: ``Array(Ext.data.Record)``
     *  :param index: ``Number``
     *      
     *  Handler for store remove event
     */
    onRemove: function(store, record, index){
        if(!this._removing) {
            var feature = record.get("feature");
            if (this.layer.getFeatureById(feature.id) != null) {
                this._removing = true;
                this.layer.removeFeatures([record.get("feature")]);
                delete this._removing;
            }
        }
    },

    /** private: method[onUpdate]
     *  :param store: ``Ext.data.Store``
     *  :param record: ``Ext.data.Record``
     *  :param operation: ``String``
     *
     *  Handler for update.
     */
    onUpdate: function(store, record, operation) {
        if(!this._updating) {
            /**
              * TODO: remove this if the FeatureReader adds attributes
              * for all fields that map to feature.attributes.
              * In that case, it would be sufficient to check (key in feature.attributes). 
              */
            var defaultFields = new GeoExt.data.FeatureRecord().fields;
            var feature = record.get("feature");
            if(record.fields) {
                var cont = this.layer.events.triggerEvent(
                    "beforefeaturemodified", {feature: feature}
                );
                if(cont !== false) {
                    var attributes = feature.attributes;
                    record.fields.each(
                        function(field) {
                            var key = field.mapping || field.name;
                            if (!defaultFields.containsKey(key)) {
                                attributes[key] = record.get(field.name);
                            }
                        }
                    );
                    this._updating = true;
                    this.layer.events.triggerEvent(
                        "featuremodified", {feature: feature}
                    );
                    delete this._updating;
                    if (this.layer.getFeatureById(feature.id) != null) {
                        this.layer.drawFeature(feature);
                    }
                }
            }
        }
    }
};

GeoExt.data.FeatureStore = Ext.extend(
    Ext.data.Store,
    GeoExt.data.FeatureStoreMixin
);

/**
 * Constant: GeoExt.data.FeatureStore.LAYER_TO_STORE
 * {Integer} Constant used to make the store be automatically updated
 * when changes occur in the layer.
 */
GeoExt.data.FeatureStore.LAYER_TO_STORE = 1;

/**
 * Constant: GeoExt.data.FeatureStore.STORE_TO_LAYER
 * {Integer} Constant used to make the layer be automatically updated
 * when changes occur in the store.
 */
GeoExt.data.FeatureStore.STORE_TO_LAYER = 2;
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/data/LayerRecord.js
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = LayerReader
 *  base_link = `Ext.data.DataReader <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataReader>`_
 */
Ext.namespace("GeoExt", "GeoExt.data");

/** api: example
 *  Sample using a reader to create records from an array of layers:
 * 
 *  .. code-block:: javascript
 *     
 *      var reader = new GeoExt.data.LayerReader();
 *      var layerData = reader.readRecords(map.layers);
 *      var numRecords = layerData.totalRecords;
 *      var layerRecords = layerData.records;
 */

/** api: constructor
 *  .. class:: LayerReader(meta, recordType)
 *  
 *      Data reader class to create an array of
 *      :class:`GeoExt.data.LayerRecord` objects from an array of 
 *      ``OpenLayers.Layer`` objects for use in a
 *      :class:`GeoExt.data.LayerStore` object.
 */
GeoExt.data.LayerReader = function(meta, recordType) {
    meta = meta || {};
    if(!(recordType instanceof Function)) {
        recordType = GeoExt.data.LayerRecord.create(
            recordType || meta.fields || {});
    }
    GeoExt.data.LayerReader.superclass.constructor.call(
        this, meta, recordType);
};

Ext.extend(GeoExt.data.LayerReader, Ext.data.DataReader, {

    /** private: property[totalRecords]
     *  ``Integer``
     */
    totalRecords: null,

    /** api: method[readRecords]
     *  :param layers: ``Array(OpenLayers.Layer)`` List of layers for creating
     *      records.
     *  :return: ``Object``  An object with ``records`` and ``totalRecords``
     *      properties.
     *  
     *  From an array of ``OpenLayers.Layer`` objects create a data block
     *  containing :class:`GeoExt.data.LayerRecord` objects.
     */
    readRecords : function(layers) {
        var records = [];
        if(layers) {
            var recordType = this.recordType, fields = recordType.prototype.fields;
            var i, lenI, j, lenJ, layer, values, field, v;
            for(i = 0, lenI = layers.length; i < lenI; i++) {
                layer = layers[i];
                values = {};
                for(j = 0, lenJ = fields.length; j < lenJ; j++){
                    field = fields.items[j];
                    v = layer[field.mapping || field.name] ||
                        field.defaultValue;
                    v = field.convert(v);
                    values[field.name] = v;
                }
                values.layer = layer;
                records[records.length] = new recordType(values, layer.id);
            }
        }
        return {
            records: records,
            totalRecords: this.totalRecords != null ? this.totalRecords : records.length
        };
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = LayerRecord
 *  base_link = `Ext.data.Record <http://extjs.com/deploy/dev/docs/?class=Ext.data.Record>`_
 */
Ext.namespace("GeoExt.data");

/** api: constructor
 *  .. class:: LayerRecord
 *  
 *      A record that represents an ``OpenLayers.Layer``. This record
 *      will always have at least the following fields:
 *
 *      * layer ``OpenLayers.Layer``
 *      * title ``String``
 */
GeoExt.data.LayerRecord = Ext.data.Record.create([
    {name: "layer"},
    {name: "title", type: "string", mapping: "name"}
]);

/** api: method[clone]
 *  :param id: ``String`` (optional) A new Record id.
 *  :return: ``GeoExt.data.LayerRecord`` A new layer record.
 *  
 *  Creates a clone of this LayerRecord. 
 */
GeoExt.data.LayerRecord.prototype.clone = function(id) { 
    var layer = this.get("layer") && this.get("layer").clone(); 
    return new this.constructor( 
        Ext.applyIf({layer: layer}, this.data), 
        id || layer.id
    );
}; 

/** api: classmethod[create]
 *  :param o: ``Array`` Field definition as in ``Ext.data.Record.create``. Can
 *      be omitted if no additional fields are required.
 *  :return: ``Function`` A specialized :class:`GeoExt.data.LayerRecord`
 *      constructor.
 *  
 *  Creates a constructor for a :class:`GeoExt.data.LayerRecord`, optionally
 *  with additional fields.
 */
GeoExt.data.LayerRecord.create = function(o) {
    var f = Ext.extend(GeoExt.data.LayerRecord, {});
    var p = f.prototype;

    p.fields = new Ext.util.MixedCollection(false, function(field) {
        return field.name;
    });

    GeoExt.data.LayerRecord.prototype.fields.each(function(f) {
        p.fields.add(f);
    });

    if(o) {
        for(var i = 0, len = o.length; i < len; i++){
            p.fields.add(new Ext.data.Field(o[i]));
        }
    }

    f.getField = function(name) {
        return p.fields.get(name);
    };

    return f;
};
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/data/LayerReader.js
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = LayerStore
 *  base_link = `Ext.data.DataStore <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataStore>`_
 */
Ext.namespace("GeoExt.data");

/** private: constructor
 *  .. class:: LayerStoreMixin
 *      A store that synchronizes a layers array of an {OpenLayers.Map} with a
 *      layer store holding {<GeoExt.data.LayerRecord>} entries.
 * 
 *      This class can not be instantiated directly. Instead, it is meant to
 *      extend ``Ext.data.Store`` or a subclass of it.
 */

/** private: example
 *  Sample code to extend a store with the LayerStoreMixin.
 *
 *  .. code-block:: javascript
 *  
 *      var store = new (Ext.extend(Ext.data.Store, GeoExt.data.LayerStoreMixin))({
 *          map: myMap,
 *          layers: myLayers
 *      });
 * 
 *  For convenience, a :class:`GeoExt.data.LayerStore` class is available as a
 *  shortcut to the ``Ext.extend`` sequence in the above code snippet.
 */

GeoExt.data.LayerStoreMixin = {

    /** api: config[map]
     *  ``OpenLayers.Map``
     *  Map that this store will be in sync with.
     */
    
    /** api: property[map]
     *  ``OpenLayers.Map``
     *  Map that the store is synchronized with.
     */
    map: null,
    
    /** api: config[layers]
     *  ``Array(OpenLayers.Layer)``
     *  Layers that will be added to the store (and the map, depending on the
     *  value of the ``initDir`` option.
     */
    
    /** api: config[initDir]
     *  ``Number``
     *  Bitfields specifying the direction to use for the initial sync between
     *  the map and the store, if set to 0 then no initial sync is done.
     *  Defaults to ``GeoExt.data.LayerStore.MAP_TO_STORE|GeoExt.data.LayerStore.STORE_TO_MAP``
     */

    /** api: config[fields]
     *  ``Array``
     *  If provided a custom layer record type with additional fields will be
     *  used. Default fields for every layer record are `layer`
     *  (``OpenLayers.Layer``) `title` (``String``). The value of this option is
     *  either a field definition objects as passed to the
     *  :meth:`GeoExt.data.LayerRecord.create` function or a
     *  :class:`GeoExt.data.LayerRecord` constructor created using
     *  :meth:`GeoExt.data.LayerRecord.create`.
     */

    /** api: config[reader]
     *  ``Ext.data.DataReader`` The reader used to produce
     *  :class:`GeoExt.data.LayerRecord` objects from ``OpenLayers.Layer``
     *  objects.  If not provided, a :class:`GeoExt.data.LayerReader` will be
     *  used.
     */
    reader: null,

    /** private: method[constructor]
     */
    constructor: function(config) {
        config = config || {};
        config.reader = config.reader ||
                        new GeoExt.data.LayerReader({}, config.fields);
        delete config.fields;
        // "map" option
        var map = config.map instanceof GeoExt.MapPanel ?
                  config.map.map : config.map;
        delete config.map;
        // "layers" option - is an alias to "data" option
        if(config.layers) {
            config.data = config.layers;
        }
        delete config.layers;
        // "initDir" option
        var options = {initDir: config.initDir};
        delete config.initDir;
        arguments.callee.superclass.constructor.call(this, config);
        if(map) {
            this.bind(map, options);
        }
    },

    /** private: method[bind]
     *  :param map: ``OpenLayers.Map`` The map instance.
     *  :param options: ``Object``
     *  
     *  Bind this store to a map instance, once bound the store
     *  is synchronized with the map and vice-versa.
     */
    bind: function(map, options) {
        if(this.map) {
            // already bound
            return;
        }
        this.map = map;
        options = options || {};

        var initDir = options.initDir;
        if(options.initDir == undefined) {
            initDir = GeoExt.data.LayerStore.MAP_TO_STORE |
                      GeoExt.data.LayerStore.STORE_TO_MAP;
        }

        // create a snapshot of the map's layers
        var layers = map.layers.slice(0);

        if(initDir & GeoExt.data.LayerStore.STORE_TO_MAP) {
            this.each(function(record) {
                this.map.addLayer(record.get("layer"));
            }, this);
        }
        if(initDir & GeoExt.data.LayerStore.MAP_TO_STORE) {
            this.loadData(layers, true);
        }

        map.events.on({
            "changelayer": this.onChangeLayer,
            "addlayer": this.onAddLayer,
            "removelayer": this.onRemoveLayer,
            scope: this
        });
        this.on({
            "load": this.onLoad,
            "clear": this.onClear,
            "add": this.onAdd,
            "remove": this.onRemove,
            "update": this.onUpdate,
            scope: this
        });
        this.data.on({
            "replace" : this.onReplace,
            scope: this
        });
    },

    /** private: method[unbind]
     *  Unbind this store from the map it is currently bound.
     */
    unbind: function() {
        if(this.map) {
            this.map.events.un({
                "changelayer": this.onChangeLayer,
                "addlayer": this.onAddLayer,
                "removelayer": this.onRemoveLayer,
                scope: this
            });
            this.un("load", this.onLoad, this);
            this.un("clear", this.onClear, this);
            this.un("add", this.onAdd, this);
            this.un("remove", this.onRemove, this);

            this.data.un("replace", this.onReplace, this);

            this.map = null;
        }
    },
    
    /** private: method[onChangeLayer]
     *  :param evt: ``Object``
     * 
     *  Handler for layer changes.  When layer order changes, this moves the
     *  appropriate record within the store.
     */
    onChangeLayer: function(evt) {
        var layer = evt.layer;
        var recordIndex = this.findBy(function(rec, id) {
            return rec.get("layer") === layer;
        });
        if(recordIndex > -1) {
            var record = this.getAt(recordIndex);
            if(evt.property === "order") {
                if(!this._adding && !this._removing) {
                    var layerIndex = this.map.getLayerIndex(layer);
                    if(layerIndex !== recordIndex) {
                        this._removing = true;
                        this.remove(record);
                        delete this._removing;
                        this._adding = true;
                        this.insert(layerIndex, [record]);
                        delete this._adding;
                    }
                }
            } else if(evt.property === "name") {
                record.set("title", layer.name);
            } else {
                this.fireEvent("update", this, record, Ext.data.Record.EDIT);
            }
        }
    },
   
    /** private: method[onAddLayer]
     *  :param evt: ``Object``
     *  
     *  Handler for a map's addlayer event
     */
    onAddLayer: function(evt) {
        if(!this._adding) {
            var layer = evt.layer;
            this._adding = true;
            this.loadData([layer], true);
            delete this._adding;
        }
    },
    
    /** private: method[onRemoveLayer]
     *  :param evt: ``Object``
     * 
     *  Handler for a map's removelayer event
     */
    onRemoveLayer: function(evt){
        //TODO replace the check for undloadDestroy with a listener for the
        // map's beforedestroy event, doing unbind(). This can be done as soon
        // as http://trac.openlayers.org/ticket/2136 is fixed.
        if(this.map.unloadDestroy) {
            if(!this._removing) {
                var layer = evt.layer;
                this._removing = true;
                this.remove(this.getById(layer.id));
                delete this._removing;
            }
        } else {
            this.unbind();
        }
    },
    
    /** private: method[onLoad]
     *  :param store: ``Ext.data.Store``
     *  :param records: ``Array(Ext.data.Record)``
     *  :param options: ``Object``
     * 
     *  Handler for a store's load event
     */
    onLoad: function(store, records, options) {
        if (!Ext.isArray(records)) {
            records = [records];
        }
        if (options && !options.add) {
            this._removing = true;
            for (var i = this.map.layers.length - 1; i >= 0; i--) {
                this.map.removeLayer(this.map.layers[i]);
            }
            delete this._removing;

            // layers has already been added to map on "add" event
            var len = records.length;
            if (len > 0) {
                var layers = new Array(len);
                for (var j = 0; j < len; j++) {
                    layers[j] = records[j].get("layer");
                }
                this._adding = true;
                this.map.addLayers(layers);
                delete this._adding;
            }
        }
    },
    
    /** private: method[onClear]
     *  :param store: ``Ext.data.Store``
     * 
     *  Handler for a store's clear event
     */
    onClear: function(store) {
        this._removing = true;
        for (var i = this.map.layers.length - 1; i >= 0; i--) {
            this.map.removeLayer(this.map.layers[i]);
        }
        delete this._removing;
    },
    
    /** private: method[onAdd]
     *  :param store: ``Ext.data.Store``
     *  :param records: ``Array(Ext.data.Record)``
     *  :param index: ``Number``
     * 
     *  Handler for a store's add event
     */
    onAdd: function(store, records, index) {
        if(!this._adding) {
            this._adding = true;
            var layer;
            for(var i=records.length-1; i>=0; --i) {
                layer = records[i].get("layer");
                this.map.addLayer(layer);
                if(index !== this.map.layers.length-1) {
                    this.map.setLayerIndex(layer, index);
                }
            }
            delete this._adding;
        }
    },
    
    /** private: method[onRemove]
     *  :param store: ``Ext.data.Store``
     *  :param record: ``Ext.data.Record``
     *  :param index: ``Number``
     * 
     *  Handler for a store's remove event
     */
    onRemove: function(store, record, index){
        if(!this._removing) {
            var layer = record.get("layer");
            if (this.map.getLayer(layer.id) != null) {
                this._removing = true;
                this.removeMapLayer(record);
                delete this._removing;
            }
        }
    },
    
    /** private: method[onUpdate]
     *  :param store: ``Ext.data.Store``
     *  :param record: ``Ext.data.Record``
     *  :param operation: ``Number``
     * 
     *  Handler for a store's update event
     */
    onUpdate: function(store, record, operation) {
        if(operation === Ext.data.Record.EDIT) {
            var layer = record.get("layer");
            var title = record.get("title");
            if(title !== layer.name) {
                layer.setName(title);
            }
        }
    },

    /** private: method[removeMapLayer]
     *  :param record: ``Ext.data.Record``
     *  
     *  Removes a record's layer from the bound map.
     */
    removeMapLayer: function(record){
        this.map.removeLayer(record.get("layer"));
    },

    /** private: method[onReplace]
     *  :param key: ``String``
     *  :param oldRecord: ``Object`` In this case, a record that has been
     *      replaced.
     *  :param newRecord: ``Object`` In this case, a record that is replacing
     *      oldRecord.

     *  Handler for a store's data collections' replace event
     */
    onReplace: function(key, oldRecord, newRecord){
        this.removeMapLayer(oldRecord);
    },
    
    /** private: method[destroy]
     */
    destroy: function() {
        this.unbind();
        GeoExt.data.LayerStore.superclass.destroy.call(this);
    }
};

/** api: example
 *  Sample to create a new store containing a cache of
 *  :class:`GeoExt.data.LayerRecord` instances derived from map layers.
 *
 *  .. code-block:: javascript
 *  
 *      var store = new GeoExt.data.LayerStore({
 *          map: myMap,
 *          layers: myLayers
 *      });
 */

/** api: constructor
 *  .. class:: LayerStore
 *
 *      A store that contains a cache of :class:`GeoExt.data.LayerRecord`
 *      objects.
 */
GeoExt.data.LayerStore = Ext.extend(
    Ext.data.Store,
    GeoExt.data.LayerStoreMixin
);

/**
 * Constant: GeoExt.data.LayerStore.MAP_TO_STORE
 * {Integer} Constant used to make the store be automatically updated
 * when changes occur in the map.
 */
GeoExt.data.LayerStore.MAP_TO_STORE = 1;

/**
 * Constant: GeoExt.data.LayerStore.STORE_TO_MAP
 * {Integer} Constant used to make the map be automatically updated
 * when changes occur in the store.
 */
GeoExt.data.LayerStore.STORE_TO_MAP = 2;
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = ProtocolProxy
 *  base_link = `Ext.data.DataProxy <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataProxy>`_
 */
Ext.namespace('GeoExt', 'GeoExt.data');

GeoExt.data.ProtocolProxy = function(config) {
    Ext.apply(this, config);
    GeoExt.data.ProtocolProxy.superclass.constructor.apply(this, arguments);
};

/** api: constructor
 *  .. class:: ProtocolProxy
 *   
 *      A data proxy for use with ``OpenLayers.Protocol`` objects.
 */
Ext.extend(GeoExt.data.ProtocolProxy, Ext.data.DataProxy, {

    /** api: config[protocol]
     *  ``OpenLayers.Protocol``
     *  The protocol used to fetch features.
     */
    protocol: null,

    /** api: config[abortPrevious]
     *  ``Boolean``
     *  Abort any previous request before issuing another.  Default is ``true``.
     */
    abortPrevious: true,

    /** private: property[response]
     *  ``OpenLayers.Protocol.Response``
     *  The response returned by the read call on the protocol.
     */
    response: null,

    /** private: method[load]
     *  :param params: ``Object`` An object containing properties which are to
     *      be used as HTTP parameters for the request to the remote server.
     *  :param reader: ``Ext.data.DataReader`` The Reader object which converts
     *      the data object into a block of ``Ext.data.Records``.
     *  :param callback: ``Function`` The function into which to pass the block
     *      of ``Ext.data.Records``. The function is passed the Record block
     *      object, the ``args`` argument passed to the load function, and a
     *      boolean success indicator.
     *  :param scope: ``Object`` The scope in which to call the callback.
     *  :param arg: ``Object`` An optional argument which is passed to the
     *      callback as its second parameter.
     *
     *  Calls ``read`` on the protocol.
     */
    load: function(params, reader, callback, scope, arg) {
        if (this.fireEvent("beforeload", this, params) !== false) {
            var o = {
                params: params || {},
                request: {
                    callback: callback,
                    scope: scope,
                    arg: arg
                },
                reader: reader
            };
            var cb = OpenLayers.Function.bind(this.loadResponse, this, o);
            if (this.abortPrevious) {
                this.abortRequest();
            }
            var options = {
                params: params,
                callback: cb,
                scope: this
            };
            Ext.applyIf(options, arg);
            this.response = this.protocol.read(options);
        } else {
           callback.call(scope || this, null, arg, false);
        }
    },

    /** private: method[abortRequest]
     *  Called to abort any ongoing request.
     */
    abortRequest: function() {
        // FIXME really we should rely on the protocol itself to
        // cancel the request, the Protocol class in OpenLayers
        // 2.7 does not expose a cancel() method
        if (this.response) {
            var response = this.response;
            if (response.priv &&
                typeof response.priv.abort == "function") {
                response.priv.abort();
                this.response = null;
            }
        }
    },

    /** private: method[loadResponse]
     *  :param o: ``Object``
     *  :param response: ``OpenLayers.Protocol.Response``
     *  
     *  Handle response from the protocol
     */
    loadResponse: function(o, response) {
        if (response.success()) {
            var result = o.reader.read(response);
            this.fireEvent("load", this, o, o.request.arg);
            o.request.callback.call(
               o.request.scope, result, o.request.arg, true);
        } else {
            this.fireEvent("loadexception", this, o, response);
            o.request.callback.call(
                o.request.scope, null, o.request.arg, false);
        }
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = ScaleStore
 *  base_link = `Ext.data.DataStore <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataStore>`_
 */
Ext.namespace("GeoExt.data");

/** api: constructor
 *  .. class:: ScaleStore
 *
 *      A store that contains a cache of available zoom levels.  The store can
 *      optionally be kept synchronized with an ``OpenLayers.Map`` or
 *      :class:`GeoExt.MapPanel` object.
 *
 *      Records have the following fields:
 *
 *      * zoom - ``Number``  The zoom level.
 *      * scale - ``Number`` The scale denominator.
 *      * resolution - ``Number`` The map units per pixel.
 */
GeoExt.data.ScaleStore = Ext.extend(Ext.data.Store, {

    /** api: config[map]
     *  ``OpenLayers.Map`` or :class:`GeoExt.MapPanel`
     *  Optional map or map panel from which to derive scale values.
     */
    map: null,

    /** private: method[constructor]
     *  Construct a ScaleStore from a configuration.  The ScaleStore accepts
     *  some custom parameters addition to the fields accepted by Ext.Store.
     */
    constructor: function(config) {
        var map = (config.map instanceof GeoExt.MapPanel ? config.map.map : config.map);
        delete config.map;
        config = Ext.applyIf(config, {reader: new Ext.data.JsonReader({}, [
            "level",
            "resolution",
            "scale"
        ])});

        GeoExt.data.ScaleStore.superclass.constructor.call(this, config);

        if (map) {
            this.bind(map);
        }
    },

    /** api: method[bind]
     *  :param map: :class`GeoExt.MapPanel` or ``OpenLayers.Map`` Panel or map
     *      to which we should bind.
     *  
     *  Bind this store to a map; that is, maintain the zoom list in sync with
     *  the map's current configuration.  If the map does not currently have a
     *  set scale list, then the store will remain empty until the map is
     *  configured with one.
     */
    bind: function(map, options) {
        this.map = (map instanceof GeoExt.MapPanel ? map.map : map);
        this.map.events.register('changebaselayer', this, this.populateFromMap);
        if (this.map.baseLayer) {
            this.populateFromMap();
        } else {
            this.map.events.register('addlayer', this, this.populateOnAdd);
        }
    },

    /** api: method[unbind]
     *  Un-bind this store from the map to which it is currently bound.  The
     *  currently stored zoom levels will remain, but no further changes from
     *  the map will affect it.
     */
    unbind: function() {
        if (this.map) {
            this.map.events.unregister('addlayer', this, this.populateOnAdd);
            this.map.events.unregister('changebaselayer', this, this.populateFromMap);
            delete this.map;
        }
    },

    /** private: method[populateOnAdd]
     *  :param evt: ``Object``
     *  
     *  This method handles the case where we have bind() called on a
     *  not-fully-configured map so that the zoom levels can be detected when a
     *  baselayer is finally added.
     */
    populateOnAdd: function(evt) {
        if (evt.layer.isBaseLayer) {
            this.populateFromMap();
            this.map.events.unregister('addlayer', this, this.populateOnAdd);
        }
    },

    /** private: method[populateFromMap]
     *  This method actually loads the zoom level information from the
     *  OpenLayers.Map and converts it to Ext Records.
     */
    populateFromMap: function() {
        var zooms = [];
        var resolutions = this.map.baseLayer.resolutions;
        var units = this.map.baseLayer.units;

        for (var i=resolutions.length-1; i >= 0; i--) {
            var res = resolutions[i];
            zooms.push({
                level: i,
                resolution: res,
                scale: OpenLayers.Util.getScaleFromResolution(res, units)
            });
        }

        this.loadData(zooms);
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/data/LayerRecord.js
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = WMSCapabilitiesReader
 *  base_link = `Ext.data.DataReader <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataReader>`_
 */
Ext.namespace("GeoExt.data");

/** api: constructor
 *  .. class:: WMSCapabilitiesReader(meta, recordType)
 *  
 *      :param meta: ``Object`` Reader configuration.
 *      :param recordType: ``Array | Ext.data.Record`` An array of field
 *          configuration objects or a record object.  Default is
 *          :class:`GeoExt.data.LayerRecord`.
 *   
 *      Data reader class to create an array of
 *      :class:`GeoExt.data.LayerRecord` objects from a WMS GetCapabilities
 *      response.
 */
GeoExt.data.WMSCapabilitiesReader = function(meta, recordType) {
    meta = meta || {};
    if(!meta.format) {
        meta.format = new OpenLayers.Format.WMSCapabilities();
    }
    if(!(typeof recordType === "function")) {
        recordType = GeoExt.data.LayerRecord.create(
            recordType || meta.fields || [
                {name: "name", type: "string"},
                {name: "abstract", type: "string"},
                {name: "queryable", type: "boolean"},
                {name: "formats"},
                {name: "styles"},
                {name: "llbbox"},
                {name: "minScale"},
                {name: "maxScale"},
                {name: "prefix"},
                {name: "attribution"},
                {name: "keywords"},
                {name: "metadataURLs"}
            ]
        );
    }
    GeoExt.data.WMSCapabilitiesReader.superclass.constructor.call(
        this, meta, recordType
    );
};

Ext.extend(GeoExt.data.WMSCapabilitiesReader, Ext.data.DataReader, {


    /** api: config[attributionCls]
     *  ``String`` CSS class name for the attribution DOM elements.
     *  Element class names append "-link", "-image", and "-title" as
     *  appropriate.  Default is "gx-attribution".
     */
    attributionCls: "gx-attribution",

    /** private: method[read]
     *  :param request: ``Object`` The XHR object which contains the parsed XML
     *      document.
     *  :return: ``Object`` A data block which is used by an ``Ext.data.Store``
     *      as a cache of ``Ext.data.Record`` objects.
     */
    read: function(request) {
        var data = request.responseXML;
        if(!data || !data.documentElement) {
            data = request.responseText;
        }
        return this.readRecords(data);
    },

    /** private: method[readRecords]
     *  :param data: ``DOMElement | Strint | Object`` A document element or XHR
     *      response string.  As an alternative to fetching capabilities data
     *      from a remote source, an object representing the capabilities can
     *      be provided given that the structure mirrors that returned from the
     *      capabilities parser.
     *  :return: ``Object`` A data block which is used by an ``Ext.data.Store``
     *      as a cache of ``Ext.data.Record`` objects.
     *  
     *  Create a data block containing Ext.data.Records from an XML document.
     */
    readRecords: function(data) {
        
        if(typeof data === "string" || data.nodeType) {
            data = this.meta.format.read(data);
        }
        var url = data.capability.request.getmap.href;
        var records = [], layer;        

        for(var i=0, len=data.capability.layers.length; i<len; i++){
            layer = data.capability.layers[i];

            if(layer.name) {
                records.push(new this.recordType(Ext.apply(layer, {
                    layer: new OpenLayers.Layer.WMS(
                        layer.title || layer.name,
                        url,
                        {layers: layer.name}, {
                            attribution: layer.attribution ?
                                this.attributionMarkup(layer.attribution) :
                                undefined
                        }
                    )
                })));
            }
        }
        
        return {
            totalRecords: records.length,
            success: true,
            records: records
        };

    },

    /** private: method[attributionMarkup]
     *  :param attribution: ``Object`` The attribution property of the layer
     *      object as parsed from a WMS Capabilities document
     *  :return: ``String`` HTML markup to display attribution
     *      information.
     *  
     *  Generates attribution markup using the Attribution metadata
     *      from WMS Capabilities
     */
    attributionMarkup : function(attribution){
        var markup = [];
        
        if (attribution.logo){
            markup.push("<img class='"+this.attributionCls+"-image' "
                        + "src='" + attribution.logo.href + "' />");
        }
        
        if (attribution.title) {
            markup.push("<span class='"+ this.attributionCls + "-title'>"
                        + attribution.title
                        + "</span>");
        }
        
        if(attribution.href){
            for(var i = 0; i < markup.length; i++){
                markup[i] = "<a class='"
              + this.attributionCls + "-link' "
                    + "href="
                    + attribution.href
                    + ">"
                    + markup[i]
                    + "</a>";
            }
        }

        return markup.join(" ");
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/data/WMSCapabilitiesReader.js
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = WMSCapabilitiesStore
 *  base_link = `Ext.data.DataStore <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataStore>`_
 */
Ext.namespace("GeoExt.data");

/** api: constructor
 *  .. class:: WMSCapabilitiesStore
 *  
 *      Small helper class to make creating stores for remote WMS layer data
 *      easier.  The store is pre-configured with a built-in
 *      ``Ext.data.HttpProxy`` and :class:`GeoExt.data.WMSCapabilitiesReader`.
 *      The proxy is configured to allow caching and issues requests via GET.
 *      If you require some other proxy/reader combination then you'll have to
 *      configure this with your own proxy or create a basic
 *      :class:`GeoExt.data.LayerStore` and configure as needed.
 */

/** api: config[format]
 *  ``OpenLayers.Format``
 *  A parser for transforming the XHR response into an array of objects
 *  representing attributes.  Defaults to an ``OpenLayers.Format.WMSCapabilities``
 *  parser.
 */

/** api: config[fields]
 *  ``Array | Function``
 *  Either an Array of field definition objects as passed to
 *  ``Ext.data.Record.create``, or a record constructor created using
 *  ``Ext.data.Record.create``.  Defaults to ``["name", "type"]``. 
 */

GeoExt.data.WMSCapabilitiesStore = function(c) {
    c = c || {};
    GeoExt.data.WMSCapabilitiesStore.superclass.constructor.call(
        this,
        Ext.apply(c, {
            proxy: c.proxy || (!c.data ?
                new Ext.data.HttpProxy({url: c.url, disableCaching: false, method: "GET"}) :
                undefined
            ),
            reader: new GeoExt.data.WMSCapabilitiesReader(
                c, c.fields
            )
        })
    );
};
Ext.extend(GeoExt.data.WMSCapabilitiesStore, Ext.data.Store);
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = WMSDescribeLayerReader
 *  base_link = `Ext.data.DataReader <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataReader>`_
 */
Ext.namespace("GeoExt.data");

/** api: constructor
 *  .. class:: WMSDescribeLayerReader(meta, recordType)
 *  
 *      :param meta: ``Object`` Reader configuration.
 *      :param recordType: ``Array | Ext.data.Record`` An array of field
 *          configuration objects or a record object.  Default has
 *          fields for owsType, owsURL, and typeName.
 *   
 *      Data reader class to create an array of
 *      layer description objects from a WMS DescribeLayer
 *      response.
 */
GeoExt.data.WMSDescribeLayerReader = function(meta, recordType) {
    meta = meta || {};
    if(!meta.format) {
        meta.format = new OpenLayers.Format.WMSDescribeLayer();
    }
    if(!(typeof recordType === "function")) {
        recordType = Ext.data.Record.create(
            recordType || meta.fields || [
                {name: "owsType", type: "string"},
                {name: "owsURL", type: "string"},
                {name: "typeName", type: "string"}
            ]
        );
    }
    GeoExt.data.WMSDescribeLayerReader.superclass.constructor.call(
        this, meta, recordType
    );
};

Ext.extend(GeoExt.data.WMSDescribeLayerReader, Ext.data.DataReader, {

    /** private: method[read]
     *  :param request: ``Object`` The XHR object which contains the parsed XML
     *      document.
     *  :return: ``Object`` A data block which is used by an ``Ext.data.Store``
     *      as a cache of ``Ext.data.Record`` objects.
     */
    read: function(request) {
        var data = request.responseXML;
        if(!data || !data.documentElement) {
            data = request.responseText;
        }
        return this.readRecords(data);
    },

    /** private: method[readRecords]
     *  :param data: ``DOMElement | Strint | Object`` A document element or XHR
     *      response string.  As an alternative to fetching layer description data
     *      from a remote source, an object representing the layer descriptions can
     *      be provided given that the structure mirrors that returned from the
     *      layer description parser.
     *  :return: ``Object`` A data block which is used by an ``Ext.data.Store``
     *      as a cache of ``Ext.data.Record`` objects.
     *  
     *  Create a data block containing Ext.data.Records from an XML document.
     */
    readRecords: function(data) {
        
        if(typeof data === "string" || data.nodeType) {
            data = this.meta.format.read(data);
        }
        var records = [], description;        
        for(var i=0, len=data.length; i<len; i++){
            description = data[i];
            if(description) {
                records.push(new this.recordType(description));
            }
        }

        return {
            totalRecords: records.length,
            success: true,
            records: records
        };

    }
});
/**x
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/data/WMSDescribeLayerReader.js
 */

/** api: (define)
 *  module = GeoExt.data
 *  class = WMSDescribeLayerStore
 *  base_link = `Ext.data.DataStore <http://extjs.com/deploy/dev/docs/?class=Ext.data.DataStore>`_
 */
Ext.namespace("GeoExt.data");

/** api: constructor
 *  .. class:: WMSDescribeLayerStore
 *  
 *      Small helper class to make creating stores for remote WMS layer description
 *      easier.  The store is pre-configured with a built-in
 *      ``Ext.data.HttpProxy`` and :class:`GeoExt.data.WMSDescribeLayerReader`.
 *      The proxy is configured to allow caching and issues requests via GET.
 *      If you require some other proxy/reader combination then you'll have to
 *      configure this with your own proxy or create a basic
 *      store and configure as needed.
 */

/** api: config[format]
 *  ``OpenLayers.Format``
 *  A parser for transforming the XHR response into an array of objects
 *  representing attributes.  Defaults to an ``OpenLayers.Format.WMSCapabilities``
 *  parser.
 */

/** api: config[fields]
 *  ``Array | Function``
 *  Either an Array of field definition objects as passed to
 *  ``Ext.data.Record.create``, or a record constructor created using
 *  ``Ext.data.Record.create``.  Defaults to ``["name", "type"]``. 
 */

GeoExt.data.WMSDescribeLayerStore = function(c) {
    c = c || {};
    GeoExt.data.WMSDescribeLayerStore.superclass.constructor.call(
        this,
        Ext.apply(c, {
            proxy: c.proxy || (!c.data ?
                new Ext.data.HttpProxy({url: c.url, disableCaching: false, method: "GET"}) :
                undefined
            ),
            reader: new GeoExt.data.WMSDescribeLayerReader(
                c, c.fields
            )
        })
    );
};
Ext.extend(GeoExt.data.WMSDescribeLayerStore, Ext.data.Store);
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

Ext.namespace("GeoExt");

GeoExt.singleFile = true;
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt
 *  class = Action
 *  base_link = `Ext.Action <http://extjs.com/deploy/dev/docs/?class=Ext.Action>`_
 */
Ext.namespace("GeoExt");

/** api: example
 *  Sample code to create a toolbar with an OpenLayers control into it.
 * 
 *  .. code-block:: javascript
 *  
 *      var action = new GeoExt.Action({
 *          text: "max extent",
 *          control: new OpenLayers.Control.ZoomToMaxExtent(),
 *          map: map
 *      });
 *      var toolbar = new Ext.Toolbar([action]);
 */

/** api: constructor
 *  .. class:: Action(config)
 *  
 *      Create a GeoExt.Action instance. A GeoExt.Action is created
 *      to insert an OpenLayers control in a toolbar as a button or
 *      in a menu as a menu item. A GeoExt.Action instance can be
 *      used like a regular Ext.Action, look at the Ext.Action API
 *      doc for more detail.
 */
GeoExt.Action = Ext.extend(Ext.Action, {

    /** api: config[control]
     *  ``OpenLayers.Control`` The OpenLayers control wrapped in this action.
     */
    control: null,

    /** api: config[map]
     *  ``OpenLayers.Map`` The OpenLayers map that the control should be added
     *  to.  For controls that don't need to be added to a map or have already
     *  been added to one, this config property may be omitted.
     */
    map: null,

    /** private: property[uScope]
     *  ``Object`` The user-provided scope, used when calling uHandler,
     *  uToggleHandler, and uCheckHandler.
     */
    uScope: null,

    /** private: property[uHandler]
     *  ``Function`` References the function the user passes through
     *  the "handler" property.
     */
    uHandler: null,

    /** private: property[uToggleHandler]
     *  ``Function`` References the function the user passes through
     *  the "toggleHandler" property.
     */
    uToggleHandler: null,

    /** private: property[uCheckHandler]
     *  ``Function`` References the function the user passes through
     *  the "checkHandler" property.
     */
    uCheckHandler: null,

    /** private */
    constructor: function(config) {
        
        // store the user scope and handlers
        this.uScope = config.scope;
        this.uHandler = config.handler;
        this.uToggleHandler = config.toggleHandler;
        this.uCheckHandler = config.checkHandler;

        config.scope = this;
        config.handler = this.pHandler;
        config.toggleHandler = this.pToggleHandler;
        config.checkHandler = this.pCheckHandler;

        // set control in the instance, the Ext.Action
        // constructor won't do it for us
        var ctrl = this.control = config.control;
        delete config.control;

        // register "activate" and "deactivate" listeners
        // on the control
        if(ctrl) {
            // If map is provided in config, add control to map.
            if(config.map) {
                config.map.addControl(ctrl);
                delete config.map;
            }
            if((config.pressed || config.checked) && ctrl.map) {
                ctrl.activate();
            }
            ctrl.events.on({
                activate: this.onCtrlActivate,
                deactivate: this.onCtrlDeactivate,
                scope: this
            });
        }

        arguments.callee.superclass.constructor.call(this, config);
    },

    /** private: method[pHandler]
     *  :param cmp: ``Ext.Component`` The component that triggers the handler.
     *
     *  The private handler.
     */
    pHandler: function(cmp) {
        var ctrl = this.control;
        if(ctrl &&
           ctrl.type == OpenLayers.Control.TYPE_BUTTON) {
            ctrl.trigger();
        }
        if(this.uHandler) {
            this.uHandler.apply(this.uScope, arguments);
        }
    },

    /** private: method[pTogleHandler]
     *  :param cmp: ``Ext.Component`` The component that triggers the toggle handler.
     *  :param state: ``Boolean`` The state of the toggle.
     *
     *  The private toggle handler.
     */
    pToggleHandler: function(cmp, state) {
        this.changeControlState(state);
        if(this.uToggleHandler) {
            this.uToggleHandler.apply(this.uScope, arguments);
        }
    },

    /** private: method[pCheckHandler]
     *  :param cmp: ``Ext.Component`` The component that triggers the check handler.
     *  :param state: ``Boolean`` The state of the toggle.
     *
     *  The private check handler.
     */
    pCheckHandler: function(cmp, state) {
        this.changeControlState(state);
        if(this.uCheckHandler) {
            this.uCheckHandler.apply(this.uScope, arguments);
        }
    },

    /** private: method[changeControlState]
     *  :param state: ``Boolean`` The state of the toggle.
     *
     *  Change the control state depending on the state boolean.
     */
    changeControlState: function(state) {
        if(state) {
            if(!this._activating) {
                this._activating = true;
                this.control.activate();
                this._activating = false;
            }
        } else {
            if(!this._deactivating) {
                this._deactivating = true;
                this.control.deactivate();
                this._deactivating = false;
            }
        }
    },

    /** private: method[onCtrlActivate]
     *  
     *  Called when this action's control is activated.
     */
    onCtrlActivate: function() {
        var ctrl = this.control;
        if(ctrl.type == OpenLayers.Control.TYPE_BUTTON) {
            this.enable();
        } else {
            // deal with buttons
            this.safeCallEach("toggle", [true]);
            // deal with check items
            this.safeCallEach("setChecked", [true]);
        }
    },

    /** private: method[onCtrlDeactivate]
     *  
     *  Called when this action's control is deactivated.
     */
    onCtrlDeactivate: function() {
        var ctrl = this.control;
        if(ctrl.type == OpenLayers.Control.TYPE_BUTTON) {
            this.disable();
        } else {
            // deal with buttons
            this.safeCallEach("toggle", [false]);
            // deal with check items
            this.safeCallEach("setChecked", [false]);
        }
    },

    /** private: method[safeCallEach]
     *
     */
    safeCallEach: function(fnName, args) {
        var cs = this.items;
        for(var i = 0, len = cs.length; i < len; i++){
            if(cs[i][fnName]) {
                cs[i][fnName].apply(cs[i], args);
            }
        }
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/widgets/form/SearchAction.js
 */

/** api: (define)
 *  module = GeoExt.form
 *  class = BasicForm
 *  base_link = `Ext.form.BasicForm <http://extjs.com/deploy/dev/docs/?class=Ext.form.BasicForm>`_
 */

Ext.namespace("GeoExt.form");

/** api: constructor
 *  .. class:: BasicForm(config)
 *
 *      A specific ``Ext.form.BasicForm`` whose doAction method creates
 *      a :class:`GeoExt.form.SearchAction` if it is passed the string
 *      "search" as its first argument.
 *
 *      In most cases one would not use this class directly, but
 *      :class:`GeoExt.form.FormPanel` instead.
 */
GeoExt.form.BasicForm = Ext.extend(Ext.form.BasicForm, {
    /** private: property[protocol]
     *  ``OpenLayers.Protocol`` The protocol configured in this
     *  instance.
     */
    protocol: null,

    /**
     * private: property[prevResponse]
     * ``OpenLayers.Protocol.Response`` The response return by a call to
     *  protocol.read method.
     */
    prevResponse: null,

    /**
     * api: config[autoAbort]
     * ``Boolean`` Tells if pending requests should be aborted
     *      when a new action is performed.
     */
    autoAbort: true,

    /** api: method[doAction]
     *  :param action: ``String or Ext.form.Action`` Either the name
     *      of the action or a ``Ext.form.Action`` instance.
     *  :param options: ``Object`` The options passed to the Action
     *      constructor.
     *  :return: :class:`GeoExt.form.BasicForm` This form.
     *
     *  Performs the action, if the string "search" is passed as the
     *  first argument then a :class:`GeoExt.form.SearchAction` is created.
     */
    doAction: function(action, options) {
        if(action == "search") {
            options = Ext.applyIf(options || {}, {
                protocol: this.protocol,
                abortPrevious: this.autoAbort
            });
            action = new GeoExt.form.SearchAction(this, options);
        }
        return GeoExt.form.BasicForm.superclass.doAction.call(
            this, action, options
        );
    },

    /** api: method[search]
     *  :param options: ``Object`` The options passed to the Action
     *      constructor.
     *  :return: :class:`GeoExt.form.BasicForm` This form.
     *  
     *  Shortcut to do a search action.
     */
    search: function(options) {
        return this.doAction("search", options);
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt.form
 *  class = FormPanel
 *  base_link = `Ext.form.Action <http://extjs.com/deploy/dev/docs/?class=Ext.form.FormPanel>`_
 */

/**
 * @include GeoExt/widgets/form/BasicForm.js
 */

Ext.namespace("GeoExt.form");

/** api: example
 *  Sample code showing how to use a GeoExt form panel.
 *
 *  .. code-block:: javascript
 *
 *      var formPanel = new GeoExt.form.Panel({
 *          renderTo: "formpanel",
 *          protocol: new OpenLayers.Protocol.WFS({
 *              url: "http://publicus.opengeo.org/geoserver/wfs",
 *              featureType: "tasmania_roads",
 *              featureNS: "http://www.openplans.org/topp"
 *          })
 *          items: [{
 *              xtype: "textfield",
 *              name: "name__ilike",
 *              value: "mont"
 *          }, {
 *              xtype: "textfield",
 *              name: "elevation__ge",
 *              value: "2000"
 *          }],
 *          listeners: {
 *              actioncomplete: function(form, action) {
 *                  // this listener triggers when the search request
 *                  // is complete, the OpenLayers.Protocol.Response
 *                  // resulting from the request is available
 *                  // in "action.response"
 *              }
 *          }
 *      });
 *
 *      formPanel.addButton({
 *          text: "search",
 *          handler: function() {
 *              this.search();
 *          },
 *          scope: formPanel
 *      });
 */

/** api: constructor
 *  .. class:: FormPanel(config)
 *
 *      A specific ``Ext.form.FormPanel`` whose internal form is a
 *      :class:`GeoExt.form.BasicForm` instead of ``Ext.form.BasicForm``.
 *      One would use this form to do search requests through
 *      an ``OpenLayers.Protocol`` object (``OpenLayers.Protocol.WFS``
 *      for example).
 *
 *      Look at :class:`GeoExt.form.SearchAction` to understand how
 *      form fields must be named for appropriate filters to be
 *      passed to the protocol.
 */
GeoExt.form.FormPanel = Ext.extend(Ext.form.FormPanel, {
    /** api: config[protocol]
     *  ``OpenLayers.Protocol`` The protocol instance this form panel
     *  is configured with, actions resulting from this form
     *  will be performed through the protocol.
     */
    protocol: null,

    /** private: method[createForm]
     *  Create the internal :class:`GeoExt.form.BasicForm` instance.
     */
    createForm: function() {
        delete this.initialConfig.listeners;
        return new GeoExt.form.BasicForm(null, this.initialConfig);
    },

    /** api: method[search]
     *  :param options: ``Object`` The options passed to the
     *      :class:`GeoExt.form.SearchAction` constructor.
     *
     *  Shortcut to the internal form's search method.
     */
    search: function(options) {
        this.getForm().search(options);
    }
});

/** api: xtype = gx_formpanel */
Ext.reg("gx_formpanel", GeoExt.form.FormPanel);
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt.form
 *  class = SearchAction
 *  base_link = `Ext.form.Action <http://extjs.com/deploy/dev/docs/?class=Ext.form.Action>`_
 */

/**
 * @include GeoExt/widgets/form.js
 */

Ext.namespace("GeoExt.form");
 
/** api: example
 *  Sample code showing how to use a GeoExt SearchAction with an Ext form panel:
 *  
 *  .. code-block:: javascript
 *
 *      var formPanel = new Ext.form.Panel({
 *          renderTo: "formpanel",
 *          items: [{
 *              xtype: "textfield",
 *              name: "name__like",
 *              value: "mont"
 *          }, {
 *              xtype: "textfield",
 *              name: "elevation__ge",
 *              value: "2000"
 *          }]
 *      });
 *
 *      var searchAction = new GeoExt.form.SearchAction(formPanel.getForm(), {
 *          protocol: new OpenLayers.Protocol.WFS({
 *              url: "http://publicus.opengeo.org/geoserver/wfs",
 *              featureType: "tasmania_roads",
 *              featureNS: "http://www.openplans.org/topp"
 *          }),
 *          abortPrevious: true
 *      });
 *
 *      formPanel.getForm().doAction(searchAction, {
 *          callback: function(response) {
 *              // response.features includes the features read
 *              // from the server through the protocol
 *          }
 *      });
 */

/** api: constructor
 *  .. class:: SearchAction(form, options)
 *
 *      A specific ``Ext.form.Action`` to be used when using a form to do
 *      trigger search requests througn an ``OpenLayers.Protocol``.
 *
 *      Arguments:
 *
 *      * form ``Ext.form.BasicForm`` A basic form instance.
 *      * options ``Object`` Options passed to the protocol'read method
 *            One can add an abortPrevious property to these options, if set
 *            to true, the abort method will be called on the protocol if
 *            there's a pending request.
 *
 *      When run this action builds an ``OpenLayers.Filter`` from the form
 *      and passes this filter to its protocol's read method. The form fields
 *      must be named after a specific convention, so that an appropriate 
 *      ``OpenLayers.Filter.Comparison`` filter is created for each
 *      field.
 *
 *      For example a field with the name ``foo__like`` would result in an
 *      ``OpenLayers.Filter.Comparison`` of type
 *      ``OpenLayers.Filter.Comparison.LIKE`` being created.
 *
 *      Here is the convention:
 *
 *      * ``<name>__eq: OpenLayers.Filter.Comparison.EQUAL_TO``
 *      * ``<name>__ne: OpenLayers.Filter.Comparison.NOT_EQUAL_TO``
 *      * ``<name>__lt: OpenLayers.Filter.Comparison.LESS_THAN``
 *      * ``<name>__le: OpenLayers.Filter.Comparison.LESS_THAN_OR_EQUAL_TO``
 *      * ``<name>__gt: OpenLayers.Filter.Comparison.GREATER_THAN``
 *      * ``<name>__ge: OpenLayers.Filter.Comparison.GREATER_THAN_OR_EQUAL_TO``
 *      * ``<name>__like: OpenLayers.Filter.Comparison.LIKE``
 *
 *      In most cases your would not directly create ``GeoExt.form.SearchAction``
 *      objects, but use :class:`GeoExt.form.FormPanel` instead.
 */
GeoExt.form.SearchAction = Ext.extend(Ext.form.Action, {
    /** private: property[type]
     *  ``String`` The action type string.
     */
    type: "search",

    /** api: property[response]
     *  ``OpenLayers.Protocol.Response`` A reference to the response
     *  resulting from the search request. Read-only.
     */
    response: null,

    /** private */
    constructor: function(form, options) {
        GeoExt.form.SearchAction.superclass.constructor.call(this, form, options);
    },

    /** private: method[run]
     *  Run the action.
     */
    run: function() {
        var o = this.options;
        var f = GeoExt.form.toFilter(this.form);
        if(o.clientValidation === false || this.form.isValid()){

            if (o.abortPrevious && this.form.prevResponse) {
                o.protocol.abort(this.form.prevResponse);
            }

            this.form.prevResponse = o.protocol.read(
                Ext.applyIf({
                    filter: f,
                    callback: this.handleResponse,
                    scope: this
                }, o)
            );

        } else if(o.clientValidation !== false){
            // client validation failed
            this.failureType = Ext.form.Action.CLIENT_INVALID;
            this.form.afterAction(this, false);
        }
    },

    /** private: method[handleResponse]
     *  :param response: ``OpenLayers.Protocol.Response`` The response
     *  object.
     *
     *  Handle the response to the search query.
     */
    handleResponse: function(response) {
        this.form.prevResponse = null;
        this.response = response;
        if(response.success()) {
            this.form.afterAction(this, true);
        } else {
            this.form.afterAction(this, false);
        }
        var o = this.options;
        if(o.callback) {
            o.callback.call(o.scope, response);
        }
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

Ext.namespace("GeoExt.form");

/** private: function[toFilter]
 *  :param form: ``Ext.form.BasicForm|Ext.form.FormPanel``
 *  :param logicalOp: ``String`` Either ``OpenLayers.Filter.Logical.AND`` or
 *      ``OpenLayers.Filter.Logical.OR``, set to
 *      ``OpenLayers.Filter.Logical.AND`` if null or undefined
 *      
 *  :return: ``OpenLayers.Filter``
 *  
 *  Create an {OpenLayers.Filter} object from a {Ext.form.BasicForm}
 *      or a {Ext.form.FormPanel} instance.
 */
GeoExt.form.toFilter = function(form, logicalOp) {
    if(form instanceof Ext.form.FormPanel) {
        form = form.getForm();
    }
    var filters = [], values = form.getValues(false);
    for(var prop in values) {
        var s = prop.split("__");

        var value = values[prop], type;

        if(s.length > 1 && 
           (type = GeoExt.form.toFilter.FILTER_MAP[s[1]]) !== undefined) {
            prop = s[0];
        } else {
            type = OpenLayers.Filter.Comparison.EQUAL_TO;
        }

        filters.push(
            new OpenLayers.Filter.Comparison({
                type: type,
                value: value,
                property: prop
            })
        );
    }

    return new OpenLayers.Filter.Logical({
        type: logicalOp || OpenLayers.Filter.Logical.AND,
        filters: filters
    });
};

/** private: constant[FILTER_MAP]
 *  An object mapping operator strings as found in field names to
 *      ``OpenLayers.Filter.Comparison`` types.
 */
GeoExt.form.toFilter.FILTER_MAP = {
    "eq": OpenLayers.Filter.Comparison.EQUAL_TO,
    "ne": OpenLayers.Filter.Comparison.NOT_EQUAL_TO,
    "lt": OpenLayers.Filter.Comparison.LESS_THAN,
    "le": OpenLayers.Filter.Comparison.LESS_THAN_OR_EQUAL_TO,
    "gt": OpenLayers.Filter.Comparison.GREATER_THAN,
    "ge": OpenLayers.Filter.Comparison.GREATER_THAN_OR_EQUAL_TO,
    "like": OpenLayers.Filter.Comparison.LIKE
};
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt.grid
 *  class = FeatureSelectionModel
 *  base_link = `Ext.grid.RowSelectionModel <http://extjs.com/deploy/dev/docs/?class=Ext.grid.RowSelectionModel>`_
 */

Ext.namespace('GeoExt.grid');

/** api: constructor
 *  .. class:: FeatureSelectionModel
 *
 *      A row selection model which enables automatic selection of features
 *      in the map when rows are selected in the grid and vice-versa.
 */

/** api: example
 *  Sample code to create a feature grid with a feature selection model:
 *  
 *  .. code-block:: javascript
 *
 *       var gridPanel = new Ext.grid.GridPanel({
 *          title: "Feature Grid",
 *          region: "east",
 *          store: store,
 *          width: 320,
 *          columns: [{
 *              header: "Name",
 *              width: 200,
 *              dataIndex: "name"
 *          }, {
 *              header: "Elevation",
 *              width: 100,
 *              dataIndex: "elevation"
 *          }],
 *          sm: new GeoExt.grid.FeatureSelectionModel() 
 *      });
 */

GeoExt.grid.FeatureSelectionModelMixin = {

    /** api: config[autoActivateControl]
     *  ``Boolean`` If true the select feature control is activated and
     *  deactivated when binding and unbinding. Defaults to true.
     */
    autoActivateControl: true,

    /** api: config[layerFromStore]
     *  ``Boolean`` If true, and if the constructor is passed neither a
     *  layer nor a select feature control, a select feature control is
     *  created using the layer found in the grid's store. Set it to
     *  false if you want to manually bind the selection model to a
     *  layer. Defaults to true.
     */
    layerFromStore: true,

    /** api: config[selectControl]
     *
     *  ``OpenLayers.Control.SelectFeature`` A select feature control. If not
     *  provided one will be created.  If provided any "layer" config option
     *  will be ignored, and its "multiple" option will be used to configure
     *  the selectionModel.  If an ``Object`` is provided here, it will be
     *  passed as config to the SelectFeature constructor, and the "layer"
     *  config option will be used for the layer.
     */

    /** private: property[selectControl] 
     *  ``OpenLayers.Control.SelectFeature`` The select feature control 
     *  instance. 
     */ 
    selectControl: null, 
    
    /** api: config[layer]
     *  ``OpenLayers.Layer.Vector`` The vector layer used for the creation of
     *  the select feature control, it must already be added to the map. If not
     *  provided, the layer bound to the grid's store, if any, will be used.
     */

    /** private: property[bound]
     *  ``Boolean`` Flag indicating if the selection model is bound.
     */
    bound: false,
    
    /** private: property[superclass]
     *  ``Ext.grid.AbstractSelectionModel`` Our superclass.
     */
    superclass: null,

    /** private */
    constructor: function(config) {
        config = config || {};
        if(config.selectControl instanceof OpenLayers.Control.SelectFeature) { 
            if(!config.singleSelect) {
                var ctrl = config.selectControl;
                config.singleSelect = !(ctrl.multiple || !!ctrl.multipleKey);
            }
        } else if(config.layer instanceof OpenLayers.Layer.Vector) {
            this.selectControl = this.createSelectControl(
                config.layer, config.selectControl
            );
            delete config.layer;
            delete config.selectControl;
        }
        this.superclass = arguments.callee.superclass;
        this.superclass.constructor.call(this, config);
    },
    
    /** private: method[initEvents]
     *
     *  Called after this.grid is defined
     */
    initEvents: function() {
        this.superclass.initEvents.call(this);
        if(this.layerFromStore) {
            var layer = this.grid.getStore() && this.grid.getStore().layer;
            if(layer &&
               !(this.selectControl instanceof OpenLayers.Control.SelectFeature)) {
                this.selectControl = this.createSelectControl(
                    layer, this.selectControl
                );
            }
        }
        if(this.selectControl) {
            this.bind(this.selectControl);
        }
    },

    /** private: createSelectControl
     *  :param layer: ``OpenLayers.Layer.Vector`` The vector layer.
     *  :param config: ``Object`` The select feature control config.
     *
     *  Create the select feature control.
     */
    createSelectControl: function(layer, config) {
        config = config || {};
        var singleSelect = config.singleSelect !== undefined ?
                           config.singleSelect : this.singleSelect;
        config = OpenLayers.Util.extend({
            toggle: true,
            multipleKey: singleSelect ? null :
                (Ext.isMac ? "metaKey" : "ctrlKey")
        }, config);
        var selectControl = new OpenLayers.Control.SelectFeature(
            layer, config
        );
        layer.map.addControl(selectControl);
        return selectControl;
    },
    
    /** api: method[bind]
     *
     *  :param obj: ``OpenLayers.Layer.Vector`` or
     *  ``OpenLayers.Control.SelectFeature`` The object this selection model
     *      should be bound to, either a vector layeer or a select feature
     *      control.
     *  :param options: ``Object`` An object with a "controlConfig"
     *      property referencing the configuration object to pass to the
     *      ``OpenLayers.Control.SelectFeature`` constructor.
     *  :return: ``OpenLayers.Control.SelectFeature`` The select feature
     *  control this selection model uses.
     *
     *  Bind the selection model to a layer or a SelectFeature control.
     */
    bind: function(obj, options) {
        if(!this.bound) {
            options = options || {};
            this.selectControl = obj;
            if(obj instanceof OpenLayers.Layer.Vector) {
                this.selectControl = this.createSelectControl(
                    obj, options.controlConfig
                );
            }
            if(this.autoActivateControl) {
                this.selectControl.activate();
            }
            var layers = this.getLayers();
            for(var i = 0, len = layers.length; i < len; i++) {
                layers[i].events.on({
                    featureselected: this.featureSelected,
                    featureunselected: this.featureUnselected,
                    scope: this
                });
            }
            this.on("rowselect", this.rowSelected, this);
            this.on("rowdeselect", this.rowDeselected, this);
            this.bound = true;
        }
        return this.selectControl;
    },
    
    /** api: method[unbind]
     *  :return: ``OpenLayers.Control.SelectFeature`` The select feature
     *      control this selection model used.
     *
     *  Unbind the selection model from the layer or SelectFeature control.
     */
    unbind: function() {
        var selectControl = this.selectControl;
        if(this.bound) {
            var layers = this.getLayers();
            for(var i = 0, len = layers.length; i < len; i++) {
                layers[i].events.un({
                    featureselected: this.featureSelected,
                    featureunselected: this.featureUnselected,
                    scope: this
                });
            }
            this.un("rowselect", this.rowSelected, this);
            this.un("rowdeselect", this.rowDeselected, this);
            if(this.autoActivateControl) {
                selectControl.deactivate();
            }
            this.selectControl = null;
            this.bound = false;
        }
        return selectControl;
    },
    
    /** private: method[featureSelected]
     *  :param evt: ``Object`` An object with a feature property referencing
     *                         the selected feature.
     */
    featureSelected: function(evt) {
        if(!this._selecting) {
            var store = this.grid.store;
            var row = store.findBy(function(record, id) {
                return record.data.feature == evt.feature;
            });
            if(row != -1 && !this.isSelected(row)) {
                this._selecting = true;
                this.selectRow(row, !this.singleSelect);
                this._selecting = false;
                // focus the row in the grid to ensure it is visible
                this.grid.getView().focusRow(row);
            }
        }
    },
    
    /** private: method[featureUnselected]
     *  :param evt: ``Object`` An object with a feature property referencing
     *                         the unselected feature.
     */
    featureUnselected: function(evt) {
        if(!this._selecting) {
            var store = this.grid.store;
            var row = store.findBy(function(record, id) {
                return record.data.feature == evt.feature;
            });
            if(row != -1 && this.isSelected(row)) {
                this._selecting = true;
                this.deselectRow(row); 
                this._selecting = false;
                this.grid.getView().focusRow(row);
            }
        }
    },
    
    /** private: method[rowSelected]
     *  :param model: ``Ext.grid.RowSelectModel`` The row select model.
     *  :param row: ``Integer`` The row index.
     *  :param record: ``Ext.data.Record`` The record.
     */
    rowSelected: function(model, row, record) {
        var feature = record.data.feature;
        if(!this._selecting && feature) {
            var layers = this.getLayers();
            for(var i = 0, len = layers.length; i < len; i++) {
                if(layers[i].selectedFeatures.indexOf(feature) == -1) {
                    this._selecting = true;
                    this.selectControl.select(feature);
                    this._selecting = false;
                    break;
                }
            }
         }
    },
    
    /** private: method[rowDeselected]
     *  :param model: ``Ext.grid.RowSelectModel`` The row select model.
     *  :param row: ``Integer`` The row index.
     *  :param record: ``Ext.data.Record`` The record.
     */
    rowDeselected: function(model, row, record) {
        var feature = record.data.feature;
        if(!this._selecting && feature) {
            var layers = this.getLayers();
            for(var i = 0, len = layers.length; i < len; i++) {
                if(layers[i].selectedFeatures.indexOf(feature) != -1) {
                    this._selecting = true;
                    this.selectControl.unselect(feature);
                    this._selecting = false;
                    break;
                }
            }
        }
    },

    /** private: method[getLayers]
     *  Return the layers attached to the select feature control.
     */
    getLayers: function() {
        return this.selectControl.layers || [this.selectControl.layer];
    }
};

GeoExt.grid.FeatureSelectionModel = Ext.extend(
    Ext.grid.RowSelectionModel,
    GeoExt.grid.FeatureSelectionModelMixin
);
/* Copyright (C) 2008-2009 The Open Source Geospatial Foundation
 * Published under the BSD license.
 * See http://geoext.org/svn/geoext/core/trunk/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/widgets/tips/LayerOpacitySliderTip.js
 */

/** api: (define)
 *  module = GeoExt
 *  class = LayerOpacitySlider
 *  base_link = `Ext.Slider <http://extjs.com/deploy/dev/docs/?class=Ext.Slider>`_
 */
Ext.namespace("GeoExt");

/** api: example
 *  Sample code to render a slider outside the map viewport:
 *
 *  .. code-block:: javascript
 *
 *      var slider = new GeoExt.LayerOpacitySlider({
 *          renderTo: document.body,
 *          width: 200,
 *          layer: layer
 *      });
 *
 *  Sample code to add a slider to a map panel:
 *
 *  .. code-block:: javascript
 *
 *      var layer = new OpenLayers.Layer.WMS(
 *          "Global Imagery",
 *          "http://demo.opengeo.org/geoserver/wms",
 *          {layers: "bluemarble"}
 *      );
 *      var panel = new GeoExt.MapPanel({
 *          renderTo: document.body,
 *          height: 300,
 *          width: 400,
 *          map: {
 *              controls: [new OpenLayers.Control.Navigation()]
 *          },
 *          layers: [layer],
 *          extent: [-5, 35, 15, 55],
 *          items: [{
 *              xtype: "gx_opacityslider",
 *              layer: layer,
 *              aggressive: true,
 *              vertical: true,
 *              height: 100,
 *              x: 10,
 *              y: 20
 *          }]
 *      });
 */

/** api: constructor
 *  .. class:: LayerOpacitySlider(config)
 *
 *      Create a slider for controlling a layer's opacity.
 */
GeoExt.LayerOpacitySlider = Ext.extend(Ext.Slider, {

    /** api: config[layer]
     *  ``OpenLayers.Layer`` or :class:`GeoExt.data.LayerRecord`
     *  The layer this slider changes the opacity of. (required)
     */
    /** private: property[layer]
     *  ``OpenLayers.Layer``
     */
    layer: null,

    /** api: config[complementaryLayer]
     *  ``OpenLayers.Layer`` or :class:`GeoExt.data.LayerRecord` 
     *  If provided, a layer that will be made invisible (its visibility is
     *  set to false) when the slider value is set to its max value. If this
     *  slider is used to fade visibility between to layers, setting
     *  ``complementaryLayer`` and ``changeVisibility`` will make sure that
     *  only visible tiles are loaded when the slider is set to its min or max
     *  value. (optional)
     */
    complementaryLayer: null,

    /** api: config[delay]
     *  ``Number`` Time in milliseconds before setting the opacity value to the
     *  layer. If the value change again within that time, the original value
     *  is not set. Only applicable if aggressive is true.
     */
    delay: 5,

    /** api: config[changeVisibilityDelay]
     *  ``Number`` Time in milliseconds before changing the layer's visibility.
     *  If the value changes again within that time, the layer's visibility
     *  change does not occur. Only applicable if changeVisibility is true.
     *  Defaults to 5.
     */
    changeVisibilityDelay: 5,

    /** api: config[aggressive]
     *  ``Boolean``
     *  If set to true, the opacity is changed as soon as the thumb is moved.
     *  Otherwise when the thumb is released (default).
     */
    aggressive: false,

    /** api: config[changeVisibility]
     *  ``Boolean``
     *  If set to true, the layer's visibility is handled by the
     *  slider, the slider makes the layer invisible when its
     *  value is changed to the min value, and makes the layer
     *  visible again when its value goes from the min value
     *  to some other value. The layer passed to the constructor
     *  must be visible, as its visibility is fully handled by
     *  the slider. Defaults to false.
     */
    changeVisibility: false,

    /** api: config[value]
     *  ``Number``
     *  The value to initialize the slider with. This value is
     *  taken into account only if the layer's opacity is null.
     *  If the layer's opacity is null and this value is not
     *  defined in the config object then the slider initializes
     *  it to the max value.
     */
    value: null,

    /** private: method[constructor]
     *  Construct the component.
     */
    constructor: function(config) {
        if (config.layer) {
            if (config.layer instanceof OpenLayers.Layer) {
                this.layer = config.layer;
            } else if (config.layer instanceof GeoExt.data.LayerRecord) {
                this.layer = config.layer.get('layer');
            }

            if (config.complementaryLayer instanceof OpenLayers.Layer) {
                this.complementaryLayer = config.complementaryLayer;
            } else if (config.complementaryLayer instanceof
                       GeoExt.data.LayerRecord) {
                this.complementaryLayer =
                    config.complementaryLayer.get('layer');
            }

            delete config.layer;
            delete config.complementaryLayer;
        }
        GeoExt.LayerOpacitySlider.superclass.constructor.call(this, config);
    },

    /** private: method[initComponent]
     *  Initialize the component.
     */
    initComponent: function() {
        // set the slider initial value
        if (this.layer && this.layer.opacity !== null) {
            this.value = parseInt(
                this.layer.opacity * (this.maxValue - this.minValue)
            );
        } else if (this.value == null) {
            this.value = this.maxValue;
        }

        GeoExt.LayerOpacitySlider.superclass.initComponent.call(this);

        if (this.changeVisibility && this.layer &&
            (this.layer.opacity == 0 || this.value == this.minValue)) {
            this.layer.setVisibility(false);
        }

        if (this.complementaryLayer &&
            ((this.layer && this.layer.opacity == 1) ||
             (this.value == this.maxValue))) {
            this.complementaryLayer.setVisibility(false);
        }

        if (this.aggressive === true) {
            this.on('change', this.changeLayerOpacity, this, {
                buffer: this.delay
            });
        } else {
            this.on('changecomplete', this.changeLayerOpacity, this);
        }

        if (this.changeVisibility === true) {
            this.on('change', this.changeLayerVisibility, this, {
                buffer: this.changeVisibilityDelay
            });
        }

        if (this.complementaryLayer) {
            this.on('change', this.changeComplementaryLayerVisibility, this, {
                buffer: this.changeVisibilityDelay
            });
        }
    },

    /** private: method[changeLayerOpacity]
     *  :param slider: :class:`GeoExt.LayerOpacitySlider`
     *  :param value: ``Number`` The slider value
     *
     *  Updates the ``OpenLayers.Layer`` opacity value.
     */
    changeLayerOpacity: function(slider, value) {
        if (this.layer) {
            this.layer.setOpacity(value / 100.0);
        }
    },

    /** private: method[changeLayerVisibility]
     *  :param slider: :class:`GeoExt.LayerOpacitySlider`
     *  :param value: ``Number`` The slider value
     *
     *  Updates the ``OpenLayers.Layer`` visibility.
     */
    changeLayerVisibility: function(slider, value) {
        var currentVisibility = this.layer.getVisibility();
        if (value == this.minValue &&
            currentVisibility === true) {
            this.layer.setVisibility(false);
        } else if (value > this.minValue &&
                   currentVisibility == false) {
            this.layer.setVisibility(true);
        }
    },

    /** private: method[changeComplementaryLayerVisibility]
     *  :param slider: :class:`GeoExt.LayerOpacitySlider`
     *  :param value: ``Number`` The slider value
     *
     *  Updates the complementary ``OpenLayers.Layer`` visibility.
     */
    changeComplementaryLayerVisibility: function(slider, value) {
        var currentVisibility = this.complementaryLayer.getVisibility();
        if (value == this.maxValue &&
            currentVisibility === true) {
            this.complementaryLayer.setVisibility(false);
        } else if (value < this.maxValue &&
                   currentVisibility == false) {
            this.complementaryLayer.setVisibility(true);
        }
    },

    /** private: method[addToMapPanel]
     *  :param panel: :class:`GeoExt.MapPanel`
     *
     *  Called by a MapPanel if this component is one of the items in the panel.
     */
    addToMapPanel: function(panel) {
        this.on({
            render: function() {
                var el = this.getEl();
                el.setStyle({
                    position: "absolute",
                    zIndex: panel.map.Z_INDEX_BASE.Control
                });
                el.on({
                    mousedown: this.stopMouseEvents,
                    click: this.stopMouseEvents
                });
            },
            scope: this
        });
    },

    /** private: method[removeFromMapPanel]
     *  :param panel: :class:`GeoExt.MapPanel`
     *
     *  Called by a MapPanel if this component is one of the items in the panel.
     */
    removeFromMapPanel: function(panel) {
        var el = this.getEl();
        el.un({
            mousedown: this.stopMouseEvents,
            click: this.stopMouseEvents,
            scope: this
        });
    },

    /** private: method[stopMouseEvents]
     *  :param e: ``Object``
     */
    stopMouseEvents: function(e) {
        e.stopEvent();
    }
});

/** api: xtype = gx_opacityslider */
Ext.reg('gx_opacityslider', GeoExt.LayerOpacitySlider);
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt
 *  class = LegendImage
 *  base_link = `Ext.Panel <http://extjs.com/deploy/dev/docs/?class=Ext.Panel>`_
 */

Ext.namespace('GeoExt');

/** api: constructor
 *  .. class:: LegendImage(config)
 *
 *  Show a legend image in a BoxComponent and make sure load errors are dealt
 *  with.
 */
GeoExt.LegendImage = Ext.extend(Ext.BoxComponent, {

    /** api: config[url]
     *  ``String``  The url of the image to load
     */
    url: null,
    
    /** api: config[defaultImgSrc]
     *  ``String`` Path to image that will be used if the legend image fails
     *  to load.  Default is Ext.BLANK_IMAGE_URL.
     */
    defaultImgSrc: null,

    /** api: config[imgCls]
     *  ``String``  Optional css class to apply to img tag
     */
    imgCls: null,
    
    /** private: method[initComponent]
     *  Initializes the legend image component. 
     */
    initComponent: function() {
        GeoExt.LegendImage.superclass.initComponent.call(this);
        if(this.defaultImgSrc === null) {
            this.defaultImgSrc = Ext.BLANK_IMAGE_URL;
        }
        this.autoEl = {
            tag: "img",
            "class": (this.imgCls ? this.imgCls : ""),
            src: this.defaultImgSrc
        };
    },

    /** api: method[setUrl]
     *  :param url: ``String`` The new url of the image.
     *  
     *  Sets the url of the image.
     */
    setUrl: function(url) {
        this.url = url;
        var el = this.getEl();
        if (el) {
            el.un("error", this.onImageLoadError, this);
            el.on("error", this.onImageLoadError, this, {single: true});
            el.dom.src = url;
        }
    },

    /** private: method[onRender]
     *  Private method called when the legend image component is being
     *  rendered.
     */
    onRender: function(ct, position) {
        GeoExt.LegendImage.superclass.onRender.call(this, ct, position);
        if(this.url) {
            this.setUrl(this.url);
        }
    },

    /** private: method[onDestroy]
     *  Private method called during the destroy sequence.
     */
    onDestroy: function() {
        var el = this.getEl();
        if(el) {
            el.un("error", this.onImageLoadError, this);
        }
        GeoExt.LegendImage.superclass.onDestroy.apply(this, arguments);
    },
    
    /** private: method[onImageLoadError]
     *  Private method called if the legend image fails loading.
     */
    onImageLoadError: function() {
        this.getEl().dom.src = this.defaultImgSrc;
    }

});

/** api: xtype = gx_legendimage */
Ext.reg('gx_legendimage', GeoExt.LegendImage);
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt
 *  class = LegendPanel
 *  base_link = `Ext.Panel <http://extjs.com/deploy/dev/docs/?class=Ext.Panel>`_
 */

Ext.namespace('GeoExt');

/** api: constructor
 *  .. class:: LegendPanel(config)
 *
 *  A panel showing legends of all layers in a layer store.
 *  Depending on the layer type, a legend renderer will be chosen.
 */
GeoExt.LegendPanel = Ext.extend(Ext.Panel, {

    /** api: config[dynamic]
     *  ``Boolean``
     *  If false the LegendPanel will not listen to the add, remove and change 
     *  events of the LayerStore. So it will load with the initial state of
     *  the LayerStore and not change anymore. 
     */
    dynamic: true,
    
    /** api: config[showTitle]
     *  ``Boolean``
     *  Whether or not to show the title of a layer. This can be a global
     *  setting for the whole panel, or it can be overridden on the LayerStore 
     *  record using the hideInLegend property.
     */
    showTitle: true,

    /** api: config[labelCls]
     *  ``String``
     *  Optional css class to use for the layer title labels.
     */
    labelCls: null,

    /** api: config[bodyStyle]
     *  ``String``
     *  Optional style to apply to the body of the legend panels.
     */
    bodyStyle: '',

    /** api: config[layerStore]
     *  ``GeoExt.data.LayerStore``
     *  The layer store containing layers to be displayed in the legend 
     *  container. If not provided it will be taken from the MapPanel.
     */
    layerStore: null,
    
    /** api: config[legendOptions]
     *  ``Object``
     *  Config options for the legend generator, i.e. the panel that provides
     *  the legend image.
     */

    /** api: config[filter]
     *  ``Function``
     *  A function, called in the scope of the legend panel, with a layer record
     *  as argument. Is expected to return true for layers to be displayed, false
     *  otherwise. By default, all layers will be displayed.
     *
     *  .. code-block:: javascript
     *
     *      filter: function(record) {
     *          return record.get("layer").isBaseLayer;
     *      }
     */
    filter: function(record) {
        return true;
    },

    /** private: method[initComponent]
     *  Initializes the legend panel.
     */
    initComponent: function() {
        GeoExt.LegendPanel.superclass.initComponent.call(this);
    },
    
    /** private: method[onRender]
     *  Private method called when the legend panel is being rendered.
     */
    onRender: function() {
        GeoExt.LegendPanel.superclass.onRender.apply(this, arguments);
        if(!this.layerStore) {
            this.layerStore = GeoExt.MapPanel.guess().layers;
        }
        this.layerStore.each(function(record) {
                this.addLegend(record);
            }, this);
        if (this.dynamic) {
            this.layerStore.on({
                "add": this.onStoreAdd,
                "remove": this.onStoreRemove,
                "update": this.onStoreUpdate,
                scope: this
            });
        }
        this.doLayout();
    },

    /** private: method[recordIndexToPanelIndex]
     *  Private method to get the panel index for a layer represented by a
     *  record.
     *
     *  :param index ``Integer`` The index of the record in the store.
     *
     *  :return: ``Integer`` The index of the sub panel in this panel.
     */
    recordIndexToPanelIndex: function(index) {
        var store = this.layerStore;
        var count = store.getCount();
        var panelIndex = -1;
        var legendCount = this.items ? this.items.length : 0;
        for(var i=count-1; i>=0; --i) {
            var layer = store.getAt(i).get("layer");
            var legendGenerator = GeoExt[
                "Legend" + layer.CLASS_NAME.split(".").pop()
            ];
            if(layer.displayInLayerSwitcher && legendGenerator &&
                (store.getAt(i).get("hideInLegend") !== true)) {
                    ++panelIndex;
                    if(index === i || panelIndex > legendCount-1) {
                        break;
                    }
            }
        }
        return panelIndex;
    },

    /** private: method[onStoreUpdate]
     *  Update a layer within the legend panel. Gets called when the store
     *  fires the update event. This usually means the visibility of the layer
     *  has changed.
     *
     *  :param store: ``Ext.data.Store`` The store in which the record was
     *      changed.
     *  :param record: ``Ext.data.Record`` The record object corresponding
     *      to the updated layer.
     *  :param operation: ``String`` The type of operation.
     */
    onStoreUpdate: function(store, record, operation) {
        var layer = record.get('layer');
        var legend = this.items ? this.getComponent(layer.id) : null;
        if ((this.showTitle && !record.get('hideTitle')) && 
            (legend && legend.items.get(0).text !== record.get('title'))) {
                // we need to update the title
                legend.items.get(0).setText(record.get('title'));
        }
        if (legend) {
            legend.setVisible(layer.getVisibility() && 
                layer.displayInLayerSwitcher && !record.get('hideInLegend'));
            if (record.get('legendURL')) {
                var items = legend.findByType('gx_legendimage');
                for (var i=0, len=items.length; i<len; i++) {
                    items[i].setUrl(record.get('legendURL'));
                }
            }
        }
    },

    /** private: method[onStoreAdd]
     *  Private method called when a layer is added to the store.
     *
     *  :param store: ``Ext.data.Store`` The store to which the record(s) was 
     *      added.
     *  :param record: ``Ext.data.Record`` The record object(s) corresponding
     *      to the added layers.
     *  :param index: ``Integer`` The index of the inserted record.
     */
    onStoreAdd: function(store, records, index) {
        var panelIndex = this.recordIndexToPanelIndex(index+records.length-1);
        for (var i=0, len=records.length; i<len; i++) {
            this.addLegend(records[i], panelIndex);
        }
        this.doLayout();
    },

    /** private: method[onStoreRemove]
     *  Private method called when a layer is removed from the store.
     *
     *  :param store: ``Ext.data.Store`` The store from which the record(s) was
     *      removed.
     *  :param record: ``Ext.data.Record`` The record object(s) corresponding
     *      to the removed layers.
     *  :param index: ``Integer`` The index of the removed record.
     */
    onStoreRemove: function(store, record, index) {
        this.removeLegend(record);
    },

    /** private: method[removeLegend]
     *  Remove the legend of a layer.
     *  :param record: ``Ext.data.Record`` The record object from the layer 
     *      store to remove.
     */
    removeLegend: function(record) {
        var legend = this.getComponent(record.get('layer').id);
        if (legend) {
            this.remove(legend, true);
            this.doLayout();
        }
    },

    /** private: method[createLegendSubpanel]
     *  Create a legend sub panel for the layer.
     *
     *  :param record: ``Ext.data.Record`` The record object from the layer
     *      store.
     *
     *  :return: ``Ext.Panel`` The created panel per layer
     */
    createLegendSubpanel: function(record) {
        var layer = record.get('layer');
        var mainPanel = this.createMainPanel(record);
        if (mainPanel !== null) {
            // the default legend can be overridden by specifying a
            // legendURL property
            var legend;
            if (record.get('legendURL')) {
                legend = new GeoExt.LegendImage({url: record.get('legendURL')});
                mainPanel.add(legend);
            } else {
                var legendGenerator = GeoExt[
                    "Legend" + layer.CLASS_NAME.split(".").pop()
                ];
                if (legendGenerator) {
                    legend = new legendGenerator(Ext.applyIf({
                        layer: layer,
                        record: record
                    }, this.legendOptions));
                    mainPanel.add(legend);
                }
            }
        }
        return mainPanel;
    },

    /** private: method[addLegend]
     *  Add a legend for the layer.
     *
     *  :param record: ``Ext.data.Record`` The record object from the layer 
     *      store.
     *  :param index: ``Integer`` The position at which to add the legend.
     */
    addLegend: function(record, index) {
        if (this.filter(record) === true) {
            index = index || 0;
            var layer = record.get('layer');
            var legendSubpanel = this.createLegendSubpanel(record);
            if (legendSubpanel !== null) {
                legendSubpanel.setVisible(layer.getVisibility());
                this.insert(index, legendSubpanel);
            }
        }
    },

    /** private: method[createMainPanel]
     *  Creates the main panel with a title for the layer.
     *
     *  :param record: ``Ext.data.Record`` The record object from the layer
     *      store.
     *
     *  :return: ``Ext.Panel`` The created main panel with a label.
     */
    createMainPanel: function(record) {
        var layer = record.get('layer');
        var panel = null;
        var legendGenerator = GeoExt[
            "Legend" + layer.CLASS_NAME.split(".").pop()
        ];
        if (layer.displayInLayerSwitcher && !record.get('hideInLegend') &&
            legendGenerator) {
            var panelConfig = {
                id: layer.id,
                border: false,
                bodyBorder: false,
                bodyStyle: this.bodyStyle,
                items: [
                    new Ext.form.Label({
                        text: (this.showTitle && !record.get('hideTitle')) ? 
                            layer.name : '',
                        cls: 'x-form-item x-form-item-label' +
                            (this.labelCls ? ' ' + this.labelCls : '')
                    })
                ]
            };
            panel = new Ext.Panel(panelConfig);
        }
        return panel;
    },

    /** private: method[onDestroy]
     *  Private method called during the destroy sequence.
     */
    onDestroy: function() {
        if(this.layerStore) {
            this.layerStore.un("add", this.onStoreAdd, this);
            this.layerStore.un("remove", this.onStoreRemove, this);
            this.layerStore.un("update", this.onStoreUpdate, this);
        }
        GeoExt.LegendPanel.superclass.onDestroy.apply(this, arguments);
    }
    
});

/** api: xtype = gx_legendpanel */
Ext.reg('gx_legendpanel', GeoExt.LegendPanel);
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/widgets/LegendImage.js
 */

/** api: (define)
 *  module = GeoExt
 *  class = LegendWMS
 *  base_link = `Ext.Panel <http://extjs.com/deploy/dev/docs/?class=Ext.Panel>`_
 */
Ext.namespace('GeoExt');

/** api: constructor
 *  .. class:: LegendWMS(config)
 *
 *  Show a legend image for a WMS layer.
 */
GeoExt.LegendWMS = Ext.extend(Ext.Panel, {

    /** api: config[imageFormat]
     *  ``String``  
     *  The image format to request the legend image in if the url cannot be
     *  determined from the styles field of the layer record. Defaults to
     *  image/gif.
     */
    imageFormat: "image/gif",
    
    /** api: config[defaultStyleIsFirst]
     *  ``String``
     *  The WMS spec does not say if the first style advertised for a layer in
     *  a Capabilities document is the default style that the layer is
     *  rendered with. We make this assumption by default. To be strictly WMS
     *  compliant, set this to false, but make sure to configure a STYLES
     *  param with your WMS layers, otherwise LegendURLs advertised in the
     *  GetCapabilities document cannot be used.
     */
    defaultStyleIsFirst: true,

    /** api: config[layer]
     *  ``OpenLayers.Layer.WMS``
     *  The WMS layer to request the legend for. Not required if record is
     *  provided.
     */
    layer: null,
    
    /** api: config[record]
     *  ``Ext.data.Record``
     *  optional record containing the layer 
     */
    record: null,

    /** api: config[bodyBorder]
     *  ``Boolean``
     *  Show a border around the legend image or not. Default is false.
     */
    bodyBorder: false,

    /** private: method[initComponent]
     *  Initializes the WMS legend. For group layers it will create multiple
     *  image box components.
     */
    initComponent: function() {
        GeoExt.LegendWMS.superclass.initComponent.call(this);
        if(!this.layer) {
            this.layer = this.record.get("layer");
        }
        this.createLegend();
    },

    /** private: method[getLegendUrl]
     *  :param layer: ``OpenLayers.Layer.WMS`` The OpenLayers WMS layer object
     *  :param layerName: ``String`` The name of the layer 
     *      (used in the LAYERS parameter)
     *  :return: ``String`` The url of the SLD WMS GetLegendGraphic request.
     *
     *  Get the url for the SLD WMS GetLegendGraphic request.
     */
    getLegendUrl: function(layerName) {
        return this.layer.getFullRequestString({
            REQUEST: "GetLegendGraphic",
            WIDTH: null,
            HEIGHT: null,
            EXCEPTIONS: "application/vnd.ogc.se_xml",
            LAYER: layerName,
            LAYERS: null,
            SRS: null,
            FORMAT: this.imageFormat
        });
    },

    /** private: method[createLegend]
     *  Add one BoxComponent per sublayer to this panel.
     */
    createLegend: function() {
        var layers = (this.layer.params.LAYERS instanceof Array) ? 
            this.layer.params.LAYERS : this.layer.params.LAYERS.split(",");
        var styleNames = this.layer.params.STYLES &&
            this.layer.params.STYLES.split(",");
        var styles = this.record && this.record.get("styles");
        var url, layerName, styleName;
        for (var i = 0, len = layers.length; i < len; i++){
            layerName = layers[i];
            if(styles && styles.length > 0) {
                styleName = styleNames && styleNames[i];
                if(styleName) {
                    Ext.each(styles, function(s) {
                        url = (s.name == styleName && s.legend) && s.legend.href;
                        return !url;
                    })
                } else if(this.defaultStyleIsFirst === true){
                    url = styles[0].legend && styles[0].legend.href;
                }
            }
            var legend = new GeoExt.LegendImage({url:
                url || this.getLegendUrl(layerName)});
            this.add(legend);
        }
    }

});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/data/LayerStore.js
 */

/** api: (define)
 *  module = GeoExt
 *  class = MapPanel
 *  base_link = `Ext.Panel <http://extjs.com/deploy/dev/docs/?class=Ext.Panel>`_
 */
Ext.namespace("GeoExt");

/** api: example
 *  Sample code to create a panel with a new map:
 * 
 *  .. code-block:: javascript
 *     
 *      var mapPanel = new GeoExt.MapPanel({
 *          border: false,
 *          renderTo: "div-id",
 *          map: {
 *              maxExtent: new OpenLayers.Bounds(-90, -45, 90, 45)
 *          }
 *      });
 *     
 *  Sample code to create a map panel with a bottom toolbar in a Window:
 * 
 *  .. code-block:: javascript
 * 
 *      var win = new Ext.Window({
 *          title: "My Map",
 *          items: [{
 *              xtype: "gx_mappanel",
 *              bbar: new Ext.Toolbar()
 *          }]
 *      });
 */

/** api: constructor
 *  .. class:: MapPanel(config)
 *   
 *      Create a panel container for a map.
 */
GeoExt.MapPanel = Ext.extend(Ext.Panel, {

    /** api: config[map]
     *  ``OpenLayers.Map or Object``  A configured map or a configuration object
     *  for the map constructor.  A configured map will be available after
     *  construction through the :attr:`map` property.
     */

    /** api: property[map]
     *  ``OpenLayers.Map``  A configured map object.
     */
    map: null,
    
    /** api: config[layers]
     *  ``GeoExt.data.LayerStore or GeoExt.data.GroupingStore or Array(OpenLayers.Layer)``
     *  A store holding records. If not provided, an empty
     *  :class:`GeoExt.data.LayerStore` will be created.
     */
    
    /** api: property[layers]
     *  :class:`GeoExt.data.LayerStore`  A store containing
     *  :class:`GeoExt.data.LayerRecord` objects.
     */
    layers: null,

    
    /** api: config[center]
     *  ``OpenLayers.LonLat or Array(Number)``  A location for the map center.  If
     *  an array is provided, the first two items should represent x & y coordinates.
     */
    center: null,

    /** api: config[zoom]
     *  ``Number``  An initial zoom level for the map.
     */
    zoom: null,

    /** api: config[extent]
     *  ``OpenLayers.Bounds or Array(Number)``  An initial extent for the map (used
     *  if center and zoom are not provided.  If an array, the first four items
     *  should be minx, miny, maxx, maxy.
     */
    extent: null,
    
    /** private: method[initComponent]
     *  Initializes the map panel. Creates an OpenLayers map if
     *  none was provided in the config options passed to the
     *  constructor.
     */
    initComponent: function(){
        if(!(this.map instanceof OpenLayers.Map)) {
            this.map = new OpenLayers.Map(
                Ext.applyIf(this.map || {}, {allOverlays: true})
            );
        }
        var layers = this.layers;
        if(!layers || layers instanceof Array) {
            this.layers = new GeoExt.data.LayerStore({
                layers: layers,
                map: this.map
            });
        }
        
        if(typeof this.center == "string") {
            this.center = OpenLayers.LonLat.fromString(this.center);
        } else if(this.center instanceof Array) {
            this.center = new OpenLayers.LonLat(this.center[0], this.center[1]);
        }
        if(typeof this.extent == "string") {
            this.extent = OpenLayers.Bounds.fromString(this.extent);
        } else if(this.extent instanceof Array) {
            this.extent = OpenLayers.Bounds.fromArray(this.extent);
        }
        
        GeoExt.MapPanel.superclass.initComponent.call(this);       
    },
    
    /** private: method[updateMapSize]
     *  Tell the map that it needs to recalculate its size and position.
     */
    updateMapSize: function() {
        if(this.map) {
            this.map.updateSize();
        }
    },

    /** private: method[renderMap]
     *  Private method called after the panel has been rendered or after it
     *  has been laid out by its parent's layout.
     */
    renderMap: function() {
        var map = this.map;
        map.render(this.body.dom);
        if(map.layers.length > 0) {
            if(this.center || this.zoom != null) {
                // both do not have to be defined
                map.setCenter(this.center, this.zoom);
            } else if(this.extent) {
                map.zoomToExtent(this.extent);
            } else {
                map.zoomToMaxExtent();
            }
        }
    },
    
    /** private: method[afterRender]
     *  Private method called after the panel has been rendered.
     */
    afterRender: function() {
        GeoExt.MapPanel.superclass.afterRender.apply(this, arguments);
        if(!this.ownerCt) {
            this.renderMap();
        } else {
            this.ownerCt.on("move", this.updateMapSize, this);
            this.ownerCt.on({
                "afterlayout": {
                    fn: this.renderMap,
                    scope: this,
                    single: true
                }
            });
        }
    },

    /** private: method[onResize]
     *  Private method called after the panel has been resized.
     */
    onResize: function() {
        GeoExt.MapPanel.superclass.onResize.apply(this, arguments);
        this.updateMapSize();
    },
    
    /** private: method[onBeforeAdd]
     *  Private method called before a component is added to the panel.
     */
    onBeforeAdd: function(item) {
        if(typeof item.addToMapPanel === "function") {
            item.addToMapPanel(this);
        }
        GeoExt.MapPanel.superclass.onBeforeAdd.apply(this, arguments);
    },
    
    /** private: method[remove]
     *  Private method called when a component is removed from the panel.
     */
    remove: function(item, autoDestroy) {
        if(typeof item.removeFromMapPanel === "function") {
            item.removeFromMapPanel(this);
        }
        GeoExt.MapPanel.superclass.remove.apply(this, arguments);
    },

    /** private: method[beforeDestroy]
     *  Private method called during the destroy sequence.
     */
    beforeDestroy: function() {
        if(this.ownerCt) {
            this.ownerCt.un("move", this.updateMapSize, this);
        }
        /**
         * If this container was passed a map instance, it is the
         * responsibility of the creator to destroy it.
         */
        if(!this.initialConfig.map ||
           !(this.initialConfig.map instanceof OpenLayers.Map)) {
            // we created the map, we destroy it
            if(this.map && this.map.destroy) {
                this.map.destroy();
            }
        }
        delete this.map;
        GeoExt.MapPanel.superclass.beforeDestroy.apply(this, arguments);
    }
    
});

/** api: function[guess]
 *  :return: ``GeoExt.MapPanel`` The first map panel found by the Ext
 *      component manager.
 *  
 *  Convenience function for guessing the map panel of an application. This
 *     can reliably be used for all applications that just have one map panel
 *     in the viewport.
 */
GeoExt.MapPanel.guess = function() {
    return Ext.ComponentMgr.all.find(function(o) { 
        return o instanceof GeoExt.MapPanel; 
    }); 
};


/** api: xtype = gx_mappanel */
Ext.reg('gx_mappanel', GeoExt.MapPanel); 
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt
 *  class = Popup
 *  base_link = `Ext.Window <http://extjs.com/deploy/dev/docs/?class=Ext.Window>`_
 */
Ext.namespace("GeoExt");

/** api: example
 *  Sample code to create a popup anchored to a feature:
 * 
 *  .. code-block:: javascript
 *     
 *      var popup = new GeoExt.Popup({
 *          title: "My Popup",
 *          feature: feature,
 *          width: 200,
 *          html: "<div>Popup content</div>",
 *          collapsible: true
 *      });
 */

/** api: constructor
 *  .. class:: Popup(config)
 *   
 *      Popups are a specialized Window that supports anchoring
 *      to a particular feature in a MapPanel.  When a popup
 *      is anchored to a feature, that means that the popup
 *      will visibly point to the feature on the map, and move
 *      accordingly when the map is panned or zoomed.
 */
GeoExt.Popup = Ext.extend(Ext.Window, {

    /** api: config[anchored]
     *  ``Boolean``  The popup begins anchored to its feature.  Default is
     *  ``true``.
     */
    anchored: true,

    /** api: config[map]
     *  ``OpenLayers.Map`` or :class:`GeoExt.MapPanel`
     *  The map this popup will be anchored to (only required if ``anchored``
     *  is set to true and the map cannot be derived from the ``feature``'s
     *  layer.
     */
    map: null,

    /** api: config[panIn]
     *  ``Boolean`` The popup should pan the map so that the popup is
     *  fully in view when it is rendered.  Default is ``true``.
     */
    panIn: true,

    /** api: config[unpinnable]
     *  ``Boolean`` The popup should have a "unpin" tool that unanchors it from
     *  its feature.  Default is ``true``.
     */
    unpinnable: true,

    /** api: config[feature]
     *  ``OpenLayers.Feature`` A location for this popup's anchor.  One of
     *  ``feature`` or ``lonlat`` must be provided.
     */
    feature: null,

    /** api: config[lonlat]
     *  ``OpenLayers.LonLat`` A location for this popup's anchor.  One of
     *  ``feature`` or ``lonlat`` must be provided.
     */
    lonlat: null,

    /**
     * Some Ext.Window defaults need to be overriden here
     * because some Ext.Window behavior is not currently supported.
     */    

    /** private: config[animCollapse]
     *  ``Boolean`` Animate the transition when the panel is collapsed.
     *  Default is ``false``.  Collapsing animation is not supported yet for
     *  popups.
     */
    animCollapse: false,

    /** private: config[draggable]
     *  ``Boolean`` Enable dragging of this Panel.  Defaults to ``false``
     *  because the popup defaults to being anchored, and anchored popups
     *  should not be draggable.
     */
    draggable: false,

    /** private: config[shadow]
     *  ``Boolean`` Give the popup window a shadow.  Defaults to ``false``
     *  because shadows are not supported yet for popups (the shadow does
     *  not look good with the anchor).
     */
    shadow: false,

    /** api: config[popupCls]
     *  ``String`` CSS class name for the popup DOM elements.  Default is
     *  "gx-popup".
     */
    popupCls: "gx-popup",

    /** api: config[ancCls]
     *  ``String``  CSS class name for the popup's anchor.
     */
    ancCls: null,

    /** private: method[initComponent]
     *  Initializes the popup.
     */
    initComponent: function() {
        if(this.map instanceof GeoExt.MapPanel) {
            this.map = this.map.map;
        }
        if(!this.map && this.feature && this.feature.layer) {
            this.map = this.feature.layer.map;
        }
        if (!this.feature && this.lonlat) {
            this.feature = new OpenLayers.Feature.Vector(new OpenLayers.Geometry.Point(this.lonlat.lon, this.lonlat.lat));
        }
        if(this.anchored) {
            this.addAnchorEvents();
        }

        this.baseCls = this.popupCls + " " + this.baseCls;

        this.elements += ',anc';

        GeoExt.Popup.superclass.initComponent.call(this);
    },

    /** private: method[onRender]
     *  Executes when the popup is rendered.
     */
    onRender: function(ct, position) {
        GeoExt.Popup.superclass.onRender.call(this, ct, position);
        this.ancCls = this.popupCls + "-anc";

        //create anchor dom element.
        this.createElement("anc", this.el.dom);
    },

    /** private: method[initTools]
     *  Initializes the tools on the popup.  In particular,
     *  it adds the 'unpin' tool if the popup is unpinnable.
     */
    initTools : function() {
        if(this.unpinnable) {
            this.addTool({
                id: 'unpin',
                handler: this.unanchorPopup.createDelegate(this, [])
            });
        }

        GeoExt.Popup.superclass.initTools.call(this);
    },

    /** private: method[show]
     *  Override.
     */
    show: function() {
        GeoExt.Popup.superclass.show.apply(this, arguments);
        if(this.anchored) {
            this.position();
            if(this.panIn && !this._mapMove) {
                this.panIntoView();
            }
        }
    },
    
    /** api: method[setSize]
     *  :param w: ``Integer``
     *  :param h: ``Integer``
     *  
     *  Sets the size of the popup, taking into account the size of the anchor.
     */
    setSize: function(w, h) {
        if(this.anc) {
            var ancSize = this.anc.getSize();
            if(typeof w == 'object') {
                h = w.height - ancSize.height;
                w = w.width;
            } else if(!isNaN(h)){
                h = h - ancSize.height;
            }
        }
        GeoExt.Popup.superclass.setSize.call(this, w, h);
    },

    /** private: method[position]
     *  Positions the popup relative to its feature
     */
    position: function() {
        var centerLonLat = this.feature.geometry.getBounds().getCenterLonLat();

        if(this._mapMove === true) {
            var visible = this.map.getExtent().containsLonLat(centerLonLat);
            if(visible !== this.isVisible()) {
                this.setVisible(visible);
            }
        }

        if(this.isVisible()) {
            var centerPx = this.map.getViewPortPxFromLonLat(centerLonLat);
            var mapBox = Ext.fly(this.map.div).getBox(); 
    
            //This works for positioning with the anchor on the bottom.
            
            var anc = this.anc;
            var dx = anc.getLeft(true) + anc.getWidth() / 2;
            var dy = this.el.getHeight();
    
            //Assuming for now that the map viewport takes up
            //the entire area of the MapPanel
            this.setPosition(centerPx.x + mapBox.x - dx, centerPx.y + mapBox.y - dy);
        }
    },

    /** private: method[unanchorPopup]
     *  Unanchors a popup from its feature.  This removes the popup from its
     *  MapPanel and adds it to the page body.
     */
    unanchorPopup: function() {
        this.removeAnchorEvents();
        
        //make the window draggable
        this.draggable = true;
        this.header.addClass("x-window-draggable");
        this.dd = new Ext.Window.DD(this);

        //remove anchor
        this.anc.remove();
        this.anc = null;

        //hide unpin tool
        this.tools.unpin.hide();
    },

    /** private: method[panIntoView]
     *  Pans the MapPanel's map so that an anchored popup can come entirely
     *  into view, with padding specified as per normal OpenLayers.Map popup
     *  padding.
     */ 
    panIntoView: function() {
        var centerLonLat = this.feature.geometry.getBounds().getCenterLonLat();
        var centerPx = this.map.getViewPortPxFromLonLat(centerLonLat);
        var mapBox = Ext.fly(this.map.div).getBox(); 

        //assumed viewport takes up whole body element of map panel
        var popupPos =  this.getPosition(true);
        popupPos[0] -= mapBox.x;
        popupPos[1] -= mapBox.y;
       
        var panelSize = [mapBox.width, mapBox.height]; // [X,Y]

        var popupSize = this.getSize();

        var newPos = [popupPos[0], popupPos[1]];

        //For now, using native OpenLayers popup padding.  This may not be ideal.
        var padding = this.map.paddingForPopups;

        // X
        if(popupPos[0] < padding.left) {
            newPos[0] = padding.left;
        } else if(popupPos[0] + popupSize.width > panelSize[0] - padding.right) {
            newPos[0] = panelSize[0] - padding.right - popupSize.width;
        }

        // Y
        if(popupPos[1] < padding.top) {
            newPos[1] = padding.top;
        } else if(popupPos[1] + popupSize.height > panelSize[1] - padding.bottom) {
            newPos[1] = panelSize[1] - padding.bottom - popupSize.height;
        }

        var dx = popupPos[0] - newPos[0];
        var dy = popupPos[1] - newPos[1];

        this.map.pan(dx, dy);
    },
    
    /** private: method[onMapMove]
     */
    onMapMove: function() {
        this._mapMove = true;
        this.position();
        delete this._mapMove;
    },
    
    /** private: method[addAnchorEvents]
     */
    addAnchorEvents: function() {
        this.map.events.on({
            "move" : this.onMapMove,
            scope : this            
        });
        
        this.on({
            "resize": this.position,
            "collapse": this.position,
            "expand": this.position,
            scope: this
        });
    },
    
    /** private: method[removeAnchorEvents]
     */
    removeAnchorEvents: function() {
        //stop position with feature
        this.map.events.un({
            "move" : this.onMapMove,
            scope : this
        });

        this.un("resize", this.position, this);
        this.un("collapse", this.position, this);
        this.un("expand", this.position, this);

    },

    /** private: method[beforeDestroy]
     *  Cleanup events before destroying the popup.
     */
    beforeDestroy: function() {
        if(this.anchored) {
            this.removeAnchorEvents();
        }
        GeoExt.Popup.superclass.beforeDestroy.call(this);
    }
});

/** api: xtype = gx_popup */
Ext.reg('gx_popup', GeoExt.Popup); 
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 *
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @requires GeoExt/widgets/tips/SliderTip.js
 */

/** api: (extends)
 *  GeoExt/widgets/tips/SliderTip.js
 */

/** api: (define)
 *  module = GeoExt
 *  class = LayerOpacitySliderTip
 *  base_link = `Ext.Tip <http://extjs.com/deploy/dev/docs/?class=Ext.Tip>`_
 */
Ext.namespace("GeoExt");

/** api: example
 *  Sample code to create a slider tip to display scale and resolution:
 *
 *  .. code-block:: javascript
 *
 *      var slider = new GeoExt.LayerOpacitySlider({
 *          renderTo: document.body,
 *          width: 200,
 *          layer: layer,
 *          plugins: new GeoExt.LayerOpacitySliderTip({
 *              template: "Opacity: {opacity}%"
 *          })
 *      });
 */

/** api: constructor
 *  .. class:: LayerOpacitySliderTip(config)
 *
 *      Create a slider tip displaying :class:`GeoExt.LayerOpacitySlider` values.
 */
GeoExt.LayerOpacitySliderTip = Ext.extend(GeoExt.SliderTip, {

    /** api: config[template]
     *  ``String``
     *  Template for the tip. Can be customized using the following keywords in
     *  curly braces:
     *
     *  * ``opacity`` - the opacity value in percent.
     */
    template: '<div>{opacity}%</div>',

    /** private: property[compiledTemplate]
     *  ``Ext.Template``
     *  The template compiled from the ``template`` string on init.
     */
    compiledTemplate: null,

    /** private: method[init]
     *  Called to initialize the plugin.
     */
    init: function(slider) {
        this.compiledTemplate = new Ext.Template(this.template);
        GeoExt.LayerOpacitySliderTip.superclass.init.call(this, slider);
    },

    /** private: method[getText]
     *  :param slider: ``Ext.Slider`` The slider this tip is attached to.
     */
    getText: function(slider) {
        var data = {
            opacity: slider.getValue()
        };
        return this.compiledTemplate.apply(data);
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/** api: (define)
 *  module = GeoExt
 *  class = SliderTip
 *  base_link = `Ext.Tip <http://extjs.com/deploy/dev/docs/?class=Ext.Tip>`_
 */
Ext.namespace("GeoExt");

/** api: example
 *  Sample code to create a slider tip to display slider value on hover:
 * 
 *  .. code-block:: javascript
 *     
 *      var slider = new Ext.Slider({
 *          renderTo: document.body,
 *          width: 200,
 *          plugins: new GeoExt.SliderTip()
 *      });
 */

/** api: constructor
 *  .. class:: SliderTip(config)
 *   
 *      Create a slider tip displaying ``Ext.Slider`` values over slider thumbs.
 */
GeoExt.SliderTip = Ext.extend(Ext.Tip, {

    /** api: config[hover]
     *  ``Boolean``
     *  Display the tip when hovering over the thumb.  If ``false``, tip will
     *  only be displayed while dragging.  Default is ``true``.
     */
    hover: true,
    
    /** api: config[minWidth]
     *  ``Number``
     *  Minimum width of the tip.  Default is 10.
     */
    minWidth: 10,

    /** api: config[minWidth]
     *  ``Number``
     *  Minimum width of the tip.  Default is 10.
     */
    minWidth: 10,
    
    /** api: config[offsets]
     *  ``Array(Number)``
     *  A two item list that provides x, y offsets for the tip.  Default is
     *  [0, -10].
     */
    offsets : [0, -10],
    
    /** private: property[dragging]
     *  ``Boolean``
     *  The thumb is currently being dragged.
     */
    dragging: false,

    /** private: method[init]
     *  :param slider: ``Ext.Slider``
     *  
     *  Called when the plugin is initialized.
     */
    init: function(slider) {
        slider.on({
            dragstart: this.onSlide,
            drag: this.onSlide,
            dragend: this.hide,
            destroy: this.destroy,
            scope: this
        });
        if(this.hover) {
            slider.on("render", this.registerThumbListeners, this);
        }
        this.slider = slider;
    },

    /** private: method[registerThumbListeners]
     *  Set as a listener for 'render' if hover is true.
     */
    registerThumbListeners: function() {
        this.slider.thumb.on({
            "mouseover": function() {
                this.onSlide(this.slider);
                this.dragging = false;
            },
            "mouseout": function() {
                if(!this.dragging) {
                    this.hide.apply(this, arguments);
                }
            },
            scope: this
        });
    },

    /** private: method[onSlide]
     *  :param slider: ``Ext.Slider``
     *
     *  Listener for dragstart and drag.
     */
    onSlide: function(slider) {
        this.dragging = true;
        this.show();
        this.body.update(this.getText(slider));
        this.doAutoWidth();
        this.el.alignTo(slider.thumb, 'b-t?', this.offsets);
    },

    /** api: config[getText]
     *  :param slider: ``Ext.Slider``
     *  ``Function``
     *  Function that generates the string value to be displayed in the tip.  By
     *  default, the return from slider.getValue() is displayed.
     */
    getText : function(slider) {
        return slider.getValue();
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @requires GeoExt/widgets/tips/SliderTip.js
 */

/** api: (extends)
 *  GeoExt/widgets/tips/SliderTip.js
 */

/** api: (define)
 *  module = GeoExt
 *  class = ZoomSliderTip
 *  base_link = `Ext.Tip <http://extjs.com/deploy/dev/docs/?class=Ext.Tip>`_
 */
Ext.namespace("GeoExt");

/** api: example
 *  Sample code to create a slider tip to display scale and resolution:
 * 
 *  .. code-block:: javascript
 *     
 *      var slider = new GeoExt.ZoomSlider({
 *          renderTo: document.body,
 *          width: 200,
 *          map: map,
 *          plugins: new GeoExt.ZoomSliderTip({
 *              template: "Scale: 1 : {scale}<br>Resolution: {resolution}"
 *          })
 *      });
 */

/** api: constructor
 *  .. class:: ZoomSliderTip(config)
 *   
 *      Create a slider tip displaying :class:`GeoExt.ZoomSlider` values.
 */
GeoExt.ZoomSliderTip = Ext.extend(GeoExt.SliderTip, {
    
    /** api: config[template]
     *  ``String``
     *  Template for the tip. Can be customized using the following keywords in
     *  curly braces:
     *  
     *  * ``zoom`` - the zoom level
     *  * ``resolution`` - the resolution
     *  * ``scale`` - the scale denominator
     */
    template: '<div>Zoom Level: {zoom}</div>' +
        '<div>Resolution: {resolution}</div>' +
        '<div>Scale: 1 : {scale}</div>',
    
    /** private: property[compiledTemplate]
     *  ``Ext.Template``
     *  The template compiled from the ``template`` string on init.
     */
    compiledTemplate: null,
    
    /** private: method[init]
     *  Called to initialize the plugin.
     */
    init: function(slider) {
        this.compiledTemplate = new Ext.Template(this.template);
        GeoExt.ZoomSliderTip.superclass.init.call(this, slider);
    },
    
    /** private: method[getText]
     *  :param slider: ``Ext.Slider`` The slider this tip is attached to.
     */
    getText: function(slider) {
        var data = {
            zoom: slider.getZoom(),
            resolution: slider.getResolution(),
            scale: Math.round(slider.getScale()) 
        };
        return this.compiledTemplate.apply(data);
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @requires GeoExt/widgets/tree/LayerContainer.js
 */
Ext.namespace("GeoExt.tree");

/** api: (define)
 *  module = GeoExt.tree
 *  class = BaseLayerContainer
 */

/** api: (extends)
 * GeoExt/widgets/tree/LayerContainer.js
 */

/** api: constructor
 *  .. class:: BaseLayerContainer
 * 
 *     A layer container that will collect all base layers of an OpenLayers
 *     map. Only layers that have displayInLayerSwitcher set to true will be
 *     included. The childrens' iconCls defaults to "gx-tree-baselayer-icon".
 *     
 *     Children will be rendered with a radio button instead of a checkbox,
 *     showing the user that only one base layer can be active at a time.
 * 
 *     To use this node type in ``TreePanel`` config, set nodeType to
 *     "gx_baselayercontainer".
 */
GeoExt.tree.BaseLayerContainer = Ext.extend(GeoExt.tree.LayerContainer, {

    /** private: method[constructor]
     *  Private constructor override.
     */
    constructor: function(config) {
        config = Ext.applyIf(config || {}, {
            text: "Base Layer",
            loader: {}
        });
        config.loader = Ext.applyIf(config.loader, {
            baseAttrs: Ext.applyIf(config.loader.baseAttrs || {}, {
                iconCls: 'gx-tree-baselayer-icon',
                checkedGroup: 'baselayer'
            }),
            filter: function(record) {
                var layer = record.get("layer");
                return layer.displayInLayerSwitcher === true &&
                    layer.isBaseLayer === true;
            }
        });

        GeoExt.tree.BaseLayerContainer.superclass.constructor.call(this,
            config);
    }
});

/**
 * NodeType: gx_baselayercontainer
 */
Ext.tree.TreePanel.nodeTypes.gx_baselayercontainer = GeoExt.tree.BaseLayerContainer;
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/widgets/tree/LayerLoader.js
 */
Ext.namespace("GeoExt.tree");

/** api: (define)
 *  module = GeoExt.tree
 *  class = LayerContainer
 *  base_link = `Ext.tree.AsyncTreeNode <http://extjs.com/deploy/dev/docs/?class=Ext.tree.AsyncTreeNode>`_
 */

/** api: constructor
 *  .. class:: LayerContainer
 * 
 *      A subclass of ``Ext.tree.AsyncTreeNode`` that will collect all layers of an
 *      OpenLayers map. Only layers that have displayInLayerSwitcher set to true
 *      will be included. The childrens' iconCls defaults to
 *      "gx-tree-layer-icon".
 *      
 *      Note: if this conatiner is loaded by an ``Ext.tree.TreeLoader``, the
 *      ``applyLoader`` config option of that loader needs to be set to
 *      "false". Also note that the list of available uiProviders will be
 *      taken from the ownerTree if this container's loader is configured
 *      without one.
 * 
 *      To use this node type in ``TreePanel`` config, set nodeType to
 *      "gx_layercontainer".
 */
GeoExt.tree.LayerContainer = Ext.extend(Ext.tree.AsyncTreeNode, {
    
    /** api: config[loader]
     *  :class:`GeoExt.tree.LayerLoader` or ``Object`` The loader to use with
     *  this container. If an ``Object`` is provided, a
     *  :class:`GeoExt.tree.LayerLoader`, configured with the the properties
     *  from the provided object, will be created. 
     */
    
    /** api: config[layerStore]
     *  :class:`GeoExt.data.LayerStore` The layer store containing layers to be
     *  displayed in the container. If loader is not provided or provided as
     *  ``Object``, this property will be set as the store option of the
     *  loader. Otherwise it will be ignored.
     */
    
    /** private: method[constructor]
     *  Private constructor override.
     */
    constructor: function(config) {
        config = Ext.applyIf(config || {}, {
            text: "Layers"
        });
        this.loader = config.loader instanceof GeoExt.tree.LayerLoader ?
            config.loader :
            new GeoExt.tree.LayerLoader(Ext.applyIf(config.loader || {}, {
                store: config.layerStore
            }));
        
        GeoExt.tree.LayerContainer.superclass.constructor.call(this, config);
    },
    
    /** private: method[recordIndexToNodeIndex]
     *  :param index: ``Number`` The record index in the layer store.
     *  :return: ``Number`` The appropriate child node index for the record.
     */
    recordIndexToNodeIndex: function(index) {
        var store = this.loader.store;
        var count = store.getCount();
        var nodeCount = this.childNodes.length;
        var nodeIndex = -1;
        for(var i=count-1; i>=0; --i) {
            if(this.loader.filter(store.getAt(i)) === true) {
                ++nodeIndex;
                if(index === i || nodeIndex > nodeCount-1) {
                    break;
                }
            }
        }
        return nodeIndex;
    },
    
    /** private: method[destroy]
     */
    destroy: function() {
        delete this.layerStore;
        GeoExt.tree.LayerContainer.superclass.destroy.apply(this, arguments);
    }
});
    
/**
 * NodeType: gx_layercontainer
 */
Ext.tree.TreePanel.nodeTypes.gx_layercontainer = GeoExt.tree.LayerContainer;
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/widgets/tree/LayerNode.js
 */
Ext.namespace("GeoExt.tree");

/** api: (define)
 *  module = GeoExt.tree
 *  class = LayerLoader
 *  base_link = `Ext.util.Observable <http://extjs.com/deploy/dev/docs/?class=Ext.util.Observable>`_
 */

/** api: constructor
 *  .. class:: LayerLoader
 * 
 *      A loader that will load all layers of a :class:`GeoExt.data.LayerStore`
 *      By default, only layers that have displayInLayerSwitcher set to true
 *      will be included. The childrens' iconCls defaults to
 *      "gx-tree-layer-icon".
 */
GeoExt.tree.LayerLoader = function(config) {
    Ext.apply(this, config);
    this.addEvents(
    
        /** api: events[beforeload]
         *  Triggered before loading children. Return false to avoid
         *  loading children.
         *  
         *  Listener arguments:
         *  * loader - :class:`GeoExt.tree.LayerLoader` this loader
         *  * node - ``Ex.tree.TreeNode`` the node that this loader is
         *      configured with
         */
        "beforeload",
        
        /** api: events[load]
         *  Triggered after children wer loaded.
         *  
         *  Listener arguments:
         *  * loader - :class:`GeoExt.tree.LayerLoader` this loader
         *  * node - ``Ex.tree.TreeNode`` the node that this loader is
         *      configured with
         */
        "load"
    );

    GeoExt.tree.LayerLoader.superclass.constructor.call(this);
};

Ext.extend(GeoExt.tree.LayerLoader, Ext.util.Observable, {

    /** api: config[store]
     *  :class:`GeoExt.data.LayerStore`
     *  The layer store containing layers to be added by this loader.
     */
    store: null,
    
    /** api: config[filter]
     *  ``Function``
     *  A function, called in the scope of this loader, with a layer record
     *  as argument. Is expected to return true for layers to be loaded, false
     *  otherwise. By default, the filter checks for displayInLayerSwitcher:
     *  
     *  .. code-block:: javascript
     *  
     *      filter: function(record) {
     *          return record.get("layer").displayInLayerSwitcher == true
     *      }
     */
    filter: function(record) {
        return record.get("layer").displayInLayerSwitcher == true;
    },
    
    /** api: config[uiProviders]
     *  ``Object``
     *  An optional object containing properties which specify custom
     *  GeoExt.tree.LayerNodeUI implementations. If the optional uiProvider
     *  attribute for child nodes is a string rather than a reference to a
     *  TreeNodeUI implementation, then that string value is used as a
     *  property name in the uiProviders object. If not provided, the
     *  uiProviders object will be taken from the ownerTree.
     */
    uiProviders: null,
    
    /** private: method[load]
     *  :param node: ``Ext.tree.TreeNode`` The node to add children to.
     *  :param callback: ``Function``
     */
    load: function(node, callback) {
        if(this.fireEvent("beforeload", this, node)) {
            this.removeStoreHandlers();
            while (node.firstChild) {
                node.removeChild(node.firstChild);
            }
            
            if(!this.uiProviders) {
                this.uiProviders = node.getOwnerTree().getLoader().uiProviders;
            }
    
            if(!this.store) {
                this.store = GeoExt.MapPanel.guess().layers;
            }
            this.store.each(function(record) {
                this.addLayerNode(node, record);
            }, this);
            this.addStoreHandlers(node);
    
            if(typeof callback == "function"){
                callback();
            }
            
            this.fireEvent("load", this, node);
        }
    },
    
    /** private: method[onStoreAdd]
     *  :param store: ``Ext.data.Store``
     *  :param records: ``Array(Ext.data.Record)``
     *  :param index: ``Number``
     *  :param node: ``Ext.tree.TreeNode``
     *  
     *  Listener for the store's add event.
     */
    onStoreAdd: function(store, records, index, node) {
        if(!this._reordering) {
            var nodeIndex = node.recordIndexToNodeIndex(index+records.length-1);
            for(var i=0; i<records.length; ++i) {
                this.addLayerNode(node, records[i], nodeIndex);
            }
        }
    },
    
    /** private: method[onStoreRemove]
     *  :param store: ``Ext.data.Store``
     *  :param record: ``Ext.data.Record``
     *  :param index: ``Number``
     *  :param node: ``Ext.tree.TreeNode``
     *  
     *  Listener for the store's remove event.
     */
    onStoreRemove: function(store, record, index, node) {
        if(!this._reordering) {
            this.removeLayerNode(node, record);
        }
    },

    /** private: method[addLayerNode]
     *  :param node: ``Ext.tree.TreeNode`` The node that the layer node will
     *      be added to as child.
     *  :param layerRecord: ``Ext.data.Record`` The layer record containing the
     *      layer to be added.
     *  :param index: ``Number`` Optional index for the new layer.  Default is 0.
     *  
     *  Adds a child node representing a layer of the map
     */
    addLayerNode: function(node, layerRecord, index) {
        index = index || 0;
        if (this.filter(layerRecord) === true) {
            var child = this.createNode({
                nodeType: 'gx_layer',
                layer: layerRecord.get("layer"),
                layerStore: this.store
            });
            var sibling = node.item(index);
            if(sibling) {
                node.insertBefore(child, sibling);
            } else {
                node.appendChild(child);
            }
            child.on("move", this.onChildMove, this);
        }
    },

    /** private: method[removeLayerNode]
     *  :param node: ``Ext.tree.TreeNode`` The node that the layer node will
     *      be removed from as child.
     *  :param layerRecord: ``Ext.data.Record`` The layer record containing the
     *      layer to be removed.
     * 
     *  Removes a child node representing a layer of the map
     */
    removeLayerNode: function(node, layerRecord) {
        if (this.filter(layerRecord) === true) {
            var child = node.findChildBy(function(node) {
                return node.layer == layerRecord.get("layer");
            });
            if(child) {
                child.un("move", this.onChildMove, this);
                child.remove();
                node.reload();
            }
    	}
    },
    
    /** private: method[onChildMove]
     *  :param tree: ``Ext.data.Tree``
     *  :param node: ``Ext.tree.TreeNode``
     *  :param oldParent: ``Ext.tree.TreeNode``
     *  :param newParent: ``Ext.tree.TreeNode``
     *  :param index: ``Number``
     *  
     *  Listener for child node "move" events.  This updates the order of
     *  records in the store based on new node order if the node has not
     *  changed parents.
     */
    onChildMove: function(tree, node, oldParent, newParent, index) {
        this._reordering = true;
        var oldRecordIndex = this.store.findBy(function(record) {
            return record.get("layer") === node.layer;
        });
        // remove the record and re-insert it at the correct index
        var record = this.store.getAt(oldRecordIndex);

        if(newParent instanceof GeoExt.tree.LayerContainer && 
                                    this.store === newParent.loader.store) {
            newParent.loader._reordering = true;
            this.store.remove(record);
            var newRecordIndex;
            if(newParent.childNodes.length > 1) {
                // find index by neighboring node in the same container
                var searchIndex = (index === 0) ? index + 1 : index - 1;
                newRecordIndex = this.store.findBy(function(r) {
                    return newParent.childNodes[searchIndex].layer === r.get("layer");
                });
                index === 0 && newRecordIndex++;
            } else if(oldParent.parentNode === newParent.parentNode){
                // find index by last node of a container above
                var prev = newParent;
                do {
                    prev = prev.previousSibling;
                } while (prev && !(prev instanceof GeoExt.tree.LayerContainer && prev.lastChild));
                if(prev) {
                    newRecordIndex = this.store.findBy(function(r) {
                        return prev.lastChild.layer === r.get("layer");
                    });
                } else {
                    // find indext by first node of a container below
                    var next = newParent;
                    do {
                        next = next.nextSibling;
                    } while (next && !(next instanceof GeoExt.tree.LayerContainer && next.firstChild));
                    if(next) {
                        newRecordIndex = this.store.findBy(function(r) {
                            return next.firstChild.layer === r.get("layer");
                        });
                    }
                    newRecordIndex++;
                }
            }
            if(newRecordIndex !== undefined) {
                this.store.insert(newRecordIndex, [record]);
                window.setTimeout(function() {
                    newParent.reload();
                    oldParent.reload();
                });
            } else {
                this.store.insert(oldRecordIndex, [record]);
            }
            delete newParent.loader._reordering;
        }
        delete this._reordering;
    },
    
    /** private: method[addStoreHandlers]
     *  :param node: :class:`GeoExt.tree.LayerNode`
     */
    addStoreHandlers: function(node) {
        if(!this._storeHandlers) {
            this._storeHandlers = {
                "add": this.onStoreAdd.createDelegate(this, [node], true),
                "remove": this.onStoreRemove.createDelegate(this, [node], true)
            };
            for(var evt in this._storeHandlers) {
                this.store.on(evt, this._storeHandlers[evt], this);
            }
        }
    },
    
    /** private: method[removeStoreHandlers]
     */
    removeStoreHandlers: function() {
        if(this._storeHandlers) {
            for(var evt in this._storeHandlers) {
                this.store.un(evt, this._storeHandlers[evt], this);
            }
            delete this._storeHandlers;
        }
    },

    /** private: method[createNode]
     *  :param attr: ``Object`` attributes for the new node
     */
    createNode: function(attr){
        if(this.baseAttrs){
            Ext.apply(attr, this.baseAttrs);
        }
        if(typeof attr.uiProvider == 'string'){
           attr.uiProvider = this.uiProviders[attr.uiProvider] || eval(attr.uiProvider);
        }
        attr.nodeType = attr.nodeType || "gx_layer";

        return new Ext.tree.TreePanel.nodeTypes[attr.nodeType](attr);
    },

    /** private: method[destroy]
     */
    destroy: function() {
        this.removeStoreHandlers();
    }
});
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

Ext.namespace("GeoExt.tree");

/** private: constructor
 *  .. class:: LayerNodeUI
 *
 *      Place in a separate file if this should be documented.
 */
GeoExt.tree.LayerNodeUI = Ext.extend(Ext.tree.TreeNodeUI, {
    
    /** private: property[radio]
     *  ``Ext.Element``
     */
    radio: null,
    
    /** private: method[constructor]
     */
    constructor: function(config) {
        GeoExt.tree.LayerNodeUI.superclass.constructor.apply(this, arguments);
    },
    
    /** private: method[render]
     *  :param bulkRender: ``Boolean``
     */
    render: function(bulkRender) {
        var a = this.node.attributes;
        if (a.checked === undefined) {
            a.checked = this.node.layer.getVisibility();
        }
        GeoExt.tree.LayerNodeUI.superclass.render.apply(this, arguments);
        var cb = this.checkbox;
        if (a.radioGroup && this.radio === null) {
            this.radio = Ext.DomHelper.insertAfter(cb,
                ['<input type="radio" class="gx-tree-layer-radio" name="',
                a.radioGroup, '_radio"></input>'].join(""));
        }
        if(a.checkedGroup) {
            // replace the checkbox with a radio button
            var radio = Ext.DomHelper.insertAfter(cb,
                ['<input type="radio" name="', a.checkedGroup,
                '_checkbox" class="', cb.className,
                cb.checked ? '" checked="checked"' : '',
                '"></input>'].join(""));
            radio.defaultChecked = cb.defaultChecked;
            Ext.get(cb).remove();
            this.checkbox = radio;
        }
        this.enforceOneVisible();
    },
    
    /** private: method[onClick]
     *  :param e: ``Object``
     */
    onClick: function(e) {
        if (e.getTarget('.gx-tree-layer-radio', 1)) {
            this.radio.defaultChecked = this.radio.checked;
            this.fireEvent("radiochange", this.node);
        } else if(e.getTarget('.x-tree-node-cb', 1)) {
            this.onCheckChange();
        } else {
            GeoExt.tree.LayerNodeUI.superclass.onClick.apply(this, arguments);
        }
    },
    
    /** private: method[toggleCheck]
     * :param value: ``Boolean``
     */
    toggleCheck: function(value) {
        if(!this._visibilityChanging) {
            this._visibilityChanging = true;
            
            // make sure we do not hide the checked layer from a checkedGroup
            value = (value === undefined ? !this.isChecked() : value) ||
                    (this.isChecked() && !!this.node.attributes.checkedGroup);
            GeoExt.tree.LayerNodeUI.superclass.toggleCheck.call(this, value);
            
            this.enforceOneVisible();

            delete this._visibilityChanging;
        }
    },
    
    /** private: method[enforceOneVisible]
     * 
     *  Makes sure that only one layer is visible if checkedGroup is set.
     *  This can only work when ``layer.setVisibility()`` does not trigger
     *  ``this.toggleCheck()``. If it does, ``this._visibilityChanging`` has
     *  to be set to true before calling this method.
     */
    enforceOneVisible: function() {
        var attributes = this.node.attributes;
        var group = attributes.checkedGroup;
        if(group) {
            var layer = this.node.layer;
            var checkedNodes = this.node.getOwnerTree().getChecked();
            var checkedCount = 0;
            // enforce "not more than one visible"
            Ext.each(checkedNodes, function(n){
                var ui = n.getUI();
                var l = n.layer
                if(!n.hidden && n.attributes.checkedGroup === group) {
                    checkedCount++;
                    if(l != layer && attributes.checked) {
                        // toggleCheck won't be called (_visibilityChanging
                        // set to true when we are called from toggleCheck(),
                        // and layer visibility handler is not yet set when we
                        // are called from render()), so we synchronize the
                        // button state manually
                        ui.checkbox.defaultChecked = false;
                        ui.checkbox.checked = false;
                        l.setVisibility(false);
                    }
                }
            });
            // enforce "at least one visible"
            if(checkedCount === 0 && attributes.checked == false) {
                var ui = this.node.getUI();
                // toggleCheck won't be called (_visibilityChanging set to
                // true when we are called from toggleCheck(), and layer
                // visibility handler is not yet set when we are called from
                // render()), so we synchronize the button state manually
                ui.checkbox.defaultChecked = true;
                ui.checkbox.checked = true;
                layer.setVisibility(true);
            }
        }
    },
    
    /** private: method[appendDDGhost]
     *  :param ghostNode ``DOMElement``
     *  
     *  For radio buttons, makes sure that we do not use the option group of
     *  the original, otherwise only the original or the clone can be checked 
     */
    appendDDGhost : function(ghostNode){
        var n = this.elNode.cloneNode(true);
        var radio = Ext.DomQuery.select("input[type='radio']", n);
        Ext.each(radio, function(r) {
            r.name = r.name + "_clone";
        });
        ghostNode.appendChild(n);
    },

    /** private: method[destroy]
     */
    destroy: function() {
        delete this.radio;
        GeoExt.tree.LayerNodeUI.superclass.destroy.apply(this, arguments);
    }
});


/** api: (define)
 *  module = GeoExt.tree
 *  class = LayerNode
 *  base_link = `Ext.tree.TreeNode <http://extjs.com/deploy/dev/docs/?class=Ext.tree.TreeNode>`_
 */

/** api: constructor
 *  .. class:: LayerNode(config)
 * 
 *      A subclass of ``Ext.tree.TreeNode`` that is connected to an
 *      ``OpenLayers.Layer`` by setting the node's layer property. Checking or
 *      unchecking the checkbox of this node will directly affect the layer and
 *      vice versa. The default iconCls for this node's icon is
 *      "gx-tree-layer-icon", unless it has children.
 * 
 *      Setting the node's layer property to a layer name instead of an object
 *      will also work. As soon as a layer is found, it will be stored as layer
 *      property in the attributes hash.
 * 
 *      The node's text property defaults to the layer name.
 *      
 *      If the node has a checkedGroup attribute configured, it will be
 *      rendered with a radio button instead of the checkbox. The value of
 *      the checkedGroup attribute is a string, identifying the options group
 *      for the node.
 * 
 *      If the node has a radioGroup attribute configured, the node will be
 *      rendered with a radio button next to the checkbox. This works like the
 *      checkbox with the checked attribute, but radioGroup is a string that
 *      identifies the options group. Clicking the radio button will fire a
 *      radioChange event.
 * 
 *      To use this node type in a ``TreePanel`` config, set ``nodeType`` to
 *      "gx_layer".
 */
GeoExt.tree.LayerNode = Ext.extend(Ext.tree.TreeNode, {
    
    /** api: config[layer]
     *  ``OpenLayers.Layer or String``
     *  The layer that this layer node will
     *  be bound to, or the name of the layer (has to match the layer's
     *  name property). If a layer name is provided, ``layerStore`` also has
     *  to be provided.
     */

    /** api: property[layer]
     *  ``OpenLayers.Layer``
     *  The layer this node is bound to.
     */
    layer: null,
    
    /** api: config[layerStore]
     *  :class:`GeoExt.data.LayerStore` ``or "auto"``
     *  The layer store containing the layer that this node represents.  If set
     *  to "auto", the node will query the ComponentManager for a
     *  :class:`GeoExt.MapPanel`, take the first one it finds and take its layer
     *  store. This property is only required if ``layer`` is provided as a
     *  string.
     */
    layerStore: null,
    
    /** api: config[childNodeType]
     *  ``Ext.tree.Node or String``
     *  Node class or nodeType of childnodes for this node. A node type provided
     *  here needs to have an add method, with a scope argument. This method
     *  will be run by this node in the context of this node, to create child nodes.
     */
    childNodeType: null,
    
    /** private: method[constructor]
     *  Private constructor override.
     */
    constructor: function(config) {
        config.leaf = config.leaf || !config.children;
        
        if(!config.iconCls && !config.children) {
            config.iconCls = "gx-tree-layer-icon";
        }
        
        this.defaultUI = this.defaultUI || GeoExt.tree.LayerNodeUI;
        this.addEvents(
            /** api: event[radiochange]
             *  Notifies listener when a differnt radio button was selected.
             *  Will be called with the currently selected node as argument.
             */
            "radiochange"
        );
        
        Ext.apply(this, {
            layer: config.layer,
            layerStore: config.layerStore,
            childNodeType: config.childNodeType
        });
        GeoExt.tree.LayerNode.superclass.constructor.apply(this, arguments);
    },

    /** private: method[render]
     *  :param bulkRender: ``Boolean``
     */
    render: function(bulkRender) {
        var layer = this.layer instanceof OpenLayers.Layer && this.layer;
        if(!layer) {
            // guess the store if not provided
            if(!this.layerStore || this.layerStore == "auto") {
                this.layerStore = GeoExt.MapPanel.guess().layers;
            }
            // now we try to find the layer by its name in the layer store
            var i = this.layerStore.findBy(function(o) {
                return o.get("title") == this.layer;
            }, this);
            if(i != -1) {
                // if we found the layer, we can assign it and everything
                // will be fine
                layer = this.layerStore.getAt(i).get("layer");
            }
        }
        if (!this.rendered || !layer) {
            var ui = this.getUI();
            
            if(layer) {
                this.layer = layer;
                // no DD and radio buttons for base layers
                if(layer.isBaseLayer) {
                    this.draggable = false;
                    Ext.applyIf(this.attributes, {
                        checkedGroup: "gx_baselayer"
                    });
                }
                if(!this.text) {
                    this.text = layer.name;
                }
                
                if(this.childNodeType) {
                    this.addChildNodes();
                }
                
                ui.show();
                this.addVisibilityEventHandlers();
            } else {
                ui.hide();
            }
            
            if(this.layerStore instanceof GeoExt.data.LayerStore) {
                this.addStoreEventHandlers(layer);
            }            
        }
        GeoExt.tree.LayerNode.superclass.render.apply(this, arguments);
    },
    
    /** private: method[addVisibilityHandlers]
     *  Adds handlers that sync the checkbox state with the layer's visibility
     *  state
     */
    addVisibilityEventHandlers: function() {
        this.layer.events.on({
            "visibilitychanged": this.onLayerVisibilityChanged,
            scope: this
        }); 
        this.on({
            "checkchange": this.onCheckChange,
            scope: this
        });
    },
    
    /** private: method[onLayerVisiilityChanged
     *  handler for visibilitychanged events on the layer
     */
    onLayerVisibilityChanged: function() {
        this.getUI().toggleCheck(this.layer.getVisibility());
    },
    
    /** private: method[onCheckChange]
     *  :param node: ``GeoExt.tree.LayerNode``
     *  :param checked: ``Boolean``
     *
     *  handler for checkchange events 
     */
    onCheckChange: function(node, checked) {
        if(checked != this.layer.getVisibility()) {
            var layer = this.layer;
            if(checked && layer.isBaseLayer && layer.map) {
                layer.map.setBaseLayer(layer);
            } else {
                layer.setVisibility(checked);
            }
        }
    },
    
    /** private: method[addStoreEventHandlers]
     *  Adds handlers that make sure the node disappeares when the layer is
     *  removed from the store, and appears when it is re-added.
     */
    addStoreEventHandlers: function() {
        this.layerStore.on({
            "add": this.onStoreAdd,
            "remove": this.onStoreRemove,
            "update": this.onStoreUpdate,
            scope: this
        });
    },
    
    /** private: method[onStoreAdd]
     *  :param store: ``Ext.data.Store``
     *  :param records: ``Array(Ext.data.Record)``
     *  :param index: ``Number``
     *
     *  handler for add events on the store 
     */
    onStoreAdd: function(store, records, index) {
        var l;
        for(var i=0; i<records.length; ++i) {
            l = records[i].get("layer");
            if(this.layer == l) {
                this.getUI().show();
                break;
            } else if (this.layer == l.name) {
                // layer is a string, which means the node has not yet
                // been rendered because the layer was not found. But
                // now we have the layer and can render.
                this.render();
                break;
            }
        }
    },
    
    /** private: method[onStoreRemove]
     *  :param store: ``Ext.data.Store``
     *  :param record: ``Ext.data.Record``
     *  :param index: ``Number``
     *
     *  handler for remove events on the store 
     */
    onStoreRemove: function(store, record, index) {
        if(this.layer == record.get("layer")) {
            this.getUI().hide();
        }
    },

    /** private: method[onStoreUpdate]
     *  :param store: ``Ext.data.Store``
     *  :param record: ``Ext.data.Record``
     *  :param operation: ``String``
     *  
     *  Listener for the store's update event.
     */
    onStoreUpdate: function(store, record, operation) {
    	var layer = record.get("layer");
        if(this.layer == layer && record.isModified("title") &&
                                    record.modified["title"] == this.text) {
            this.setText(record.get("title"));
        }
    },

    /** private: method[addChildNodes]
     *  Calls the add method of a node type configured as ``childNodeType``
     *  to add children.
     */
    addChildNodes: function() {
        if(typeof this.childNodeType == "string") {
            Ext.tree.TreePanel.nodeTypes[this.childNodeType].add(this);
        } else if(typeof this.childNodeType.add === "function") {
            this.childNodeType.add(this);
        }
    },
    
    /** private: method[destroy]
     */
    destroy: function() {
        var layer = this.layer;
        if (layer instanceof OpenLayers.Layer) {
            layer.events.un({
                "visibilitychanged": this.onLayerVisibilityChanged,
                scope: this
            });
        }
        delete this.layer;
        var layerStore = this.layerStore;
        if(layerStore) {
            layerStore.un("add", this.onStoreAdd, this);
            layerStore.un("remove", this.onStoreRemove, this);
            layerStore.un("update", this.onStoreUpdate, this);
        }
        delete this.layerStore;
        this.un("checkchange", this.onCheckChange, this);

        GeoExt.tree.LayerNode.superclass.destroy.apply(this, arguments);
    }
});

/**
 * NodeType: gx_layer
 */
Ext.tree.TreePanel.nodeTypes.gx_layer = GeoExt.tree.LayerNode;
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @requires GeoExt/widgets/tree/LayerContainer.js
 */
Ext.namespace("GeoExt.tree");

/** api: (define)
 *  module = GeoExt.tree
 *  class = OverlayLayerContainer
 */

/** api: (extends)
 * GeoExt/widgets/tree/LayerContainer.js
 */

/** api: constructor
 * .. class:: OverlayLayerContainer
 * 
 *     A layer container that will collect all overlay layers of an OpenLayers
 *     map. Only layers that have displayInLayerSwitcher set to true will be
 *     included.
 * 
 *     To use this node type in ``TreePanel`` config, set nodeType to
 *     "gx_overlaylayerontainer".
 */
GeoExt.tree.OverlayLayerContainer = Ext.extend(GeoExt.tree.LayerContainer, {

    /** private: method[constructor]
     *  Private constructor override.
     */
    constructor: function(config) {
        config = Ext.applyIf(config || {}, {
            text: "Overlays"
        });
        config.loader = Ext.applyIf(config.loader || {}, {
            filter: function(record){
                var layer = record.get("layer");
                return layer.displayInLayerSwitcher === true &&
                layer.isBaseLayer === false;
            }
        });
        
        GeoExt.tree.OverlayLayerContainer.superclass.constructor.call(this,
            config);
    }
});

/**
 * NodeType: gx_overlaylayercontainer
 */
Ext.tree.TreePanel.nodeTypes.gx_overlaylayercontainer = GeoExt.tree.OverlayLayerContainer;
/**
 * Copyright (c) 2008-2009 The Open Source Geospatial Foundation
 * 
 * Published under the BSD license.
 * See http://svn.geoext.org/core/trunk/geoext/license.txt for the full text
 * of the license.
 */

/**
 * @include GeoExt/widgets/tips/ZoomSliderTip.js
 */

/** api: (define)
 *  module = GeoExt
 *  class = ZoomSlider
 *  base_link = `Ext.Slider <http://extjs.com/deploy/dev/docs/?class=Ext.Slider>`_
 */
Ext.namespace("GeoExt");

/** api: example
 *  Sample code to render a slider outside the map viewport:
 * 
 *  .. code-block:: javascript
 *     
 *      var slider = new GeoExt.ZoomSlider({
 *          renderTo: document.body,
 *          width: 200,
 *          map: map
 *      });
 *     
 *  Sample code to add a slider to a map panel:
 * 
 *  .. code-block:: javascript
 * 
 *      var panel = new GeoExt.MapPanel({
 *          renderTo: document.body,
 *          height: 300,
 *          width: 400,
 *          map: {
 *              controls: [new OpenLayers.Control.Navigation()]
 *          },
 *          layers: [new OpenLayers.Layer.WMS(
 *              "Global Imagery",
 *              "http://demo.opengeo.org/geoserver/wms",
 *              {layers: "bluemarble"}
 *          )],
 *          extent: [-5, 35, 15, 55],
 *          items: [{
 *              xtype: "gx_zoomslider",
 *              aggressive: true,
 *              vertical: true,
 *              height: 100,
 *              x: 10,
 *              y: 20
 *          }]
 *      });
 */

/** api: constructor
 *  .. class:: ZoomSlider(config)
 *   
 *      Create a slider for controlling a map's zoom level.
 */
GeoExt.ZoomSlider = Ext.extend(Ext.Slider, {
    
    /** api: config[map]
     *  ``OpenLayers.Map`` or :class:`GeoExt.MapPanel`
     *  The map that the slider controls.
     */
    map: null,
    
    /** api: config[baseCls]
     *  ``String``
     *  The CSS class name for the slider elements.  Default is "gx-zoomslider".
     */
    baseCls: "gx-zoomslider",

    /** api: config[aggressive]
     *  ``Boolean``
     *  If set to true, the map is zoomed as soon as the thumb is moved. Otherwise 
     *  the map is zoomed when the thumb is released (default).
     */
    aggressive: false,
    
    /** private: property[updating]
     *  ``Boolean``
     *  The slider position is being updated by itself (based on map zoomend).
     */
    updating: false,
    
    /** private: method[initComponent]
     *  Initialize the component.
     */
    initComponent: function() {
        GeoExt.ZoomSlider.superclass.initComponent.call(this);
        
        if(this.map) {
            if(this.map instanceof GeoExt.MapPanel) {
                this.map = this.map.map;
            }
            this.bind(this.map);
        }

        if (this.aggressive === true) {
            this.on('change', this.changeHandler, this);
        } else {
            this.on('changecomplete', this.changeHandler, this);
        }
        this.on("beforedestroy", this.unbind, this);        
    },
    
    /** private: method[onRender]
     *  Override onRender to set base css class.
     */
    onRender: function() {
        GeoExt.ZoomSlider.superclass.onRender.apply(this, arguments);
        this.el.addClass(this.baseCls);
    },

    /** private: method[afterRender]
     *  Override afterRender because the render event is fired too early
     *  to call update.
     */
    afterRender : function(){
        Ext.Slider.superclass.afterRender.apply(this, arguments);
        this.update();
    },
    
    /** private: method[addToMapPanel]
     *  :param panel: :class:`GeoExt.MapPanel`
     *  
     *  Called by a MapPanel if this component is one of the items in the panel.
     */
    addToMapPanel: function(panel) {
        /**
         * TODO: Remove this when we drop support for Ext 2.
         * We need special treatment for Ext 2 because components don't have
         * the "afterrender" event.  Here we wait until the render sequence
         * finishes before binding the component to the map.
         */
        // START SPECIAL TREATMENT FOR EXT 2
        if (!this.events.afterrender) {
            this.on({
                render: function() {
                    window.setTimeout(
                        this.bind.createDelegate(this, [panel.map]), 0
                    );
                },
                scope: this
            });
        }
        // END SPECIAL TREATMENT FOR EXT 2
        this.on({
            render: function() {
                var el = this.getEl();
                el.setStyle({
                    position: "absolute",
                    zIndex: panel.map.Z_INDEX_BASE.Control
                });
                el.on({
                    mousedown: this.stopMouseEvents,
                    click: this.stopMouseEvents
                });
            },
            afterrender: function() {
                this.bind(panel.map);
            },
            scope: this
        });
    },
    
    /** private: method[stopMouseEvents]
     *  :param e: ``Object``
     */
    stopMouseEvents: function(e) {
        e.stopEvent();
    },
    
    /** private: method[removeFromMapPanel]
     *  :param panel: :class:`GeoExt.MapPanel`
     *  
     *  Called by a MapPanel if this component is one of the items in the panel.
     */
    removeFromMapPanel: function(panel) {
        var el = this.getEl();
        el.un("mousedown", this.stopMouseEvents, this);
        el.un("click", this.stopMouseEvents, this);
        this.unbind();
    },
    
    /** private: method[bind]
     *  :param map: ``OpenLayers.Map``
     */
    bind: function(map) {
        this.map = map;
        this.map.events.on({
            zoomend: this.update,
            changebaselayer: this.initZoomValues,
            scope: this
        });
        if(this.map.baseLayer) {
            this.initZoomValues();
            this.update();
        }
    },
    
    /** private: method[unbind]
     */
    unbind: function() {
        if(this.map) {
            this.map.events.un({
                zoomend: this.update,
                changebaselayer: this.initZoomValues,
                scope: this
            });
        }
    },
    
    /** private: method[initZoomValues]
     *  Set the min/max values for the slider if not set in the config.
     */
    initZoomValues: function() {
        var layer = this.map.baseLayer;
        if(this.initialConfig.minValue === undefined) {
            this.minValue = layer.minZoomLevel || 0;
        }
        if(this.initialConfig.maxValue === undefined) {
            this.maxValue = layer.maxZoomLevel || layer.numZoomLevels - 1;
        }
    },
    
    /** api: method[getZoom]
     *  :return: ``Number`` The map zoom level.
     *  
     *  Get the zoom level for the associated map based on the slider value.
     */
    getZoom: function() {
        return this.getValue();
    },
    
    /** api: method[getScale]
     *  :return: ``Number`` The map scale denominator.
     *  
     *  Get the scale denominator for the associated map based on the slider value.
     */
    getScale: function() {
        return OpenLayers.Util.getScaleFromResolution(
            this.map.getResolutionForZoom(this.getValue()),
            this.map.getUnits()
        );
    },
    
    /** api: method[getResolution]
     *  :return: ``Number`` The map resolution.
     *  
     *  Get the resolution for the associated map based on the slider value.
     */
    getResolution: function() {
        return this.map.getResolutionForZoom(this.getValue());
    },
    
    /** private: method[changeHandler]
     *  Registered as a listener for slider changecomplete.  Zooms the map.
     */
    changeHandler: function() {
        if(this.map && !this.updating) {
            this.map.zoomTo(this.getValue());
        }
    },
    
    /** private: method[update]
     *  Registered as a listener for map zoomend.  Updates the value of the slider.
     */
    update: function() {
        if(this.rendered && this.map) {
            this.updating = true;
            this.setValue(this.map.getZoom());
            this.updating = false;
        }
    }

});

/** api: xtype = gx_zoomslider */
Ext.reg('gx_zoomslider', GeoExt.ZoomSlider);
