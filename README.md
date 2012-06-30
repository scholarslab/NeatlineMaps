# Neatline Maps

Working with geospatial data can be difficult. The NeatlineMaps plugin allows you to upload georeferenced maps to the popular open source [Geoserver][geoserver], allowing you to not only easily use your data in Omeka, but share it with others.


GeoServer is a Java-based software server that implements to the open standards developed by the [Open Geospatial Consortium (OGC)][ogc]. 
GeoServer not only integrates with Omeka with the NeatlineMaps plugin, but also works with many of the popular mapping applications like 
[Google Maps][gmaps], [Google Earth][gearth], [Yahoo Maps][ymaps], and [Microsoft Virtual Earth][msve]. Additionally, GeoServer can be used more traditional GIS architectures like [ESRI ArcGIS][arcgis].

NeatlineMaps allows you to connect to one, or more, GeoServer instance to easily create “slippy” maps that you can use with Omeka items and exhibits.

## Installation

**Note:** You must have access to a GeoServer instance to use this plugin. You can download the appropriate [installation package from the GeoServer site][geodownload]. This plage also contains [documentation for installing and configuring][geodocs] the software. This plugin also requires that the PHP 
**Zip** and **Curl** modules be installed on your server. The plugin
will notify you if you are missing these modules.

  * Upload the ‘NeatlineMaps’ plugin directory to your Omeka installation’s ‘plugins’ directory. See [Installing a Plugin][plugininstall].

  * Activate the plugin from the admin → Settings → Plugins page.

## Usage

### Adding a Server
You can add as many GeoServer instances as you have access to. This is particularly useful in a situation where you have a testing and production environment, or working with colleagues from different institutions with a centralized, shared GeoServer instance.

  1. Log on to the Omeka Admin panel

  2. Click on the Neatline Maps tab

  3. Click on the Create Server link or button

![Neatline Maps Tab](http://23.21.98.97/wp-content/uploads/2012/06/maps-tab.png)

  4. Fill out the information to connect to your GeoServer instance. If you have a default installation of GeoServer, the username/password combination is ‘admin/geoserver’. [We recommend you change this setting][geopassword].

    * **Name**: An internal, non-public identifier for the server.

    * **URL**: The root URL for the Geoserver instance. This should look something like http://localhost:8080/geoserver

    * **Workspace**: The workspace on the server where the new GeoTIFF stores should be created.

    * **Username**: The Geoserver username.

    * **Password**: The Geoserver password.

    * **Active**: Check this box if the new server should be the active server (see below for more information - the active server can be changed at any point).

![Geoserver Password](http://23.21.98.97/wp-content/uploads/2012/06/add-server.png)

  5. After clicking on the Create button, you will be presented with a list of servers which you have defined, including the status of the server (green Online or red Offline).

![Servers](http://23.21.98.97/wp-content/uploads/2012/06/add-server.png)

You may create as many sever records as you wish, but at any given point exactly one of the servers will always be the "active" server - this is the server the Neatline Maps will use to handle new GeoTIFF files that are uploaded as files attachments to items.

If you have more than one server record, you can change the active server by either (a) clicking the "Set Active" link in the "Active" column in the Browse Servers view.


## Adding Web Map Services to Items 

There are two ways to add a map on a Geoserver instance. 

1. Link to the map _directly_ by adding a [WMS][wms] address and a list of layers to include

2. Upload a [Geotiff][geotiff] as a file

### Using an existing WMS (web map service)

To link an item to an existing service:

  1. In the Add/Edit view of an item, click on the "Web Map Service" in the vertical stack of tabs on the left.

  2. Enter the WMS address of the server and a comma-delimited list of layers in the two fields.

![Add WMS](http://23.21.98.97/wp-content/uploads/2012/06/wms-item.png)

  3. Click the "Save Changes" button (or "Add Item," if you're creating a new Item).

If the WMS address and layers point to a valid web map service, the map will be displayed along with the regular metadata on all item-specific views throughout the site.

### Upload GeoTIFFs

You will need to ensure you have set up a server to post your GeoTiff images to. See [Adding a Server](#adding-a-server).  You will also need administrative access to a [Geoserver][geoserver] instance in order to use this feature. With this option, you will upload a GeoTiff as you would any file, and the Neatline Maps plugin will
communicate with the Geoserver to set up the web map service for you.

1. Create a new Omeka item, filling out pertinant metadata fields

2. Click on the *Files* tab 

![Add File](http://23.21.98.97/wp-content/uploads/2012/06/add-file.png)

3. Browse to where you have stored your GeoTiff

4. Click Add/Edit Item

![Save file](http://23.21.98.97/wp-content/uploads/2012/06/file-save.png)

**Neatline Maps will detect that a GeoTiff file has been uploaded and attempt to upload the file to the Geoserver instance that is currently marked as "active" in the list of servers under the "Neatline Maps" tab.** If the plugin is able to connect to the server and the .tiff file is a well-formed GeoTIFF, Neatline Maps will create a new coverage store and generate a layer for the store. In your Omeka site, Neatline Maps will automatically fill in the information in the "Web Map Service" tab for the item with the WMS address and layer name of the new GeoTIFF that was just uploaded to the active server.

What if you try to upload a new GeoTIFF to an item that already has existing data in the "Web Map Service" tab? If the existing WMS address on the item matches the WMS address of the currently "active" server, Neatline Maps will upload the file to Geoserver and _add the new layer to the existing list of layers specified in the "layers" field in the "Web Map Service" tab_. If the WMS address of the active server does not match the WMS address on the item, Neatline Maps will do nothing - the file will not be uploaded to Geoserver and the list of layers in the "Web Map Service" tab will not be changed.

#### Preparing and formatting GeoTIFFs

For an excellent introduction to georectifying maps for use with the Neatline Maps plugin, see [Creating GIS Dataset from Historic Maps][georectify].


### Integration with Neatline exhibits

Once you have an item or a collection of items with WMS services attached to them (it doesn't matter if you upload new GeoTIFFs or just enter in the information for existing services - the system doesn't differentiate between them), a map can be added to a Neatline exhibit just by activating the item in the "Map" column in the editor.

In the Neatline editor, find the listing for the item in the items browser panel on the left of the screen (depending on how the items query for the exhibit is configured, you might have to update the query or add tag/collection metadata to the item to make it appear in the list) and click the middle of the three activation checkboxes next to the item (in the "Map" column).

Neatline will automatically render the map on the exhibit. In this way, you can configure any combination of WMS maps in the exhibit by activating or deactivating their parent items.


[geoserver]: http://geoserver.org
[neatline-maps-download]: http://neatline.scholarslab.org/plugins/neatline-maps
[ogc]: http://www.opengeospatial.org/
[gmaps]: http://maps.google.com/
[gearth]: http://earth.google.com/
[ymaps]: http://maps.yahoo.com/
[msve]: http://www.microsoft.com/VIRTUALEARTH
[arcgis]: http://www.esri.com/arcgis
[geodownload]: http://geoserver.org/display/GEOS/Stable
[geodocs]: http://docs.geoserver.org/stable/en/user/
[plugininstall]: http://omeka.org/codex/Installing_a_Plugin
[geopassword]: http://docs.geoserver.org/latest/en/user/gettingstarted/web-admin-quickstart/index.html#logging-in

[wms]: http://www.opengeospatial.org/standards/wms
[geotiff]: http://trac.osgeo.org/geotiff/
[georectify]: http://spatial.scholarslab.org/stepbystep/creating-gis-datasets-from-historic-maps/


