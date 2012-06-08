# Neatline Maps

Working with geospatial data can be difficult. The NeatlineMaps plugin allows you to upload georeferenced maps to the popular open source [Geoserver][geoserver], allowing you to not only easily use your data in Omeka, but share it with others.


GeoServer is a Java-based software server that implements to the open standards developed by the [Open Geospatial Consortium (OGC)][ogc]. 
GeoServer not only integrates with Omeka with the NeatlineMaps plugin, but also works with many of the popular mapping applications like 
[Google Maps][gmaps], [Google Earth][gearth], [Yahoo Maps][ymaps], and [Microsoft Virtual Earth][msve]. Additionally, GeoServer can be used more traditional GIS architectures like [ESRI ArcGIS][arcgis].

NeatlineMaps allows you to connect to one, or more, GeoServer instance to easily create “slippy” maps that you can use with Omeka items and exhibits.

## Installation

** Note: ** You must have access to a GeoServer instance to use this plugin. You can download the appropriate [installation package from the GeoServer site][geodownload]. This plage also contains [documentation for installing and configuring][geodocs] the software. This plugin also requires that the PHP 
**Zip** and **Curl** modules be installed on your server. The plugin
will notify you if you are missing these modules.


  * Upload the ‘NeatlineMaps’ plugin directory to your Omeka installation’s ‘plugins’ directory. See [Installing a Plugin][plugininstall].
  * Activate the plugin from the admin → Settings → Plugins page.

## Usage

### Adding a Server
You can add as many GeoServer instances as you have access to. This is particularly useful in a situation where you have a testing and production environment, or working with colleagues from different institutions with a centralized, shared GeoServer instance.

  1. Log on to the Omeka Admin panel
  2. Click on the Neatline Maps tab
  3. Select the Servers tab and click on Add Server
![Add a NeatlineMaps server](http://23.21.98.97/wp-content/uploads/2011/05/maps_server1.png)
  4. Fill out the information to connect to your GeoServer instance. If you have a default installation of GeoServer, the username/password combination is ‘admin/geoserver’. [We recommend you change this setting][geopassword].
![Geoserver Password](http://23.21.98.97/wp-content/uploads/2011/05/maps_server2.png)
  5. After clicking on the Create button, you will be presented with a list of servers which you have defined, including the status of the server (green Online or red Offline).
![Servers](http://23.21.98.97/wp-content/uploads/2011/05/maps_server31.png)

## Adding Web Map Services to Items 

To connect an item with a web map service, you can either (a) link the item _directly_ to an existing service by entering a WMS address and list of layers or (b) connect the plugin with a Geoserver instance that you have administrative access to and create new layers by uploading geoencoded .tiff files through the Omeka administrative interface.

### Use an existing web map service

To link an item to an existing service:

  1. Go to the "Browse Items" view in the Omeka administrative interface.
  2. Open the Item add/edit form by either (a) clicking the "Add an Item" button at the top right or clicking the "Edit" button under the listing of the item that you want to link the WMS to.
  3. In the Item edit form, click on the "Web Map Service" in the vertical stack of tabs on the left.
  4. Enter the WMS address of the server and a comma-delimited list of layers in the two fields.
  5. Click the "Save Changes" button (or "Add Item," if you're creating a new Item).

If the WMS address and layers point to a valid web map service, the map will be displayed along with the regular metadata on all item-specific views throughout the site.

### Upload new GeoTIFFs

If you have administrative access to a Geoserver instance and want to upload a new georeferenced .tiff file that does not already exist on the server, you can connect Neatline Maps with Geoserver and generate the new coverage store by way of the Omeka interface.

#### Preparing and formatting GeoTIFFs

**todo** - instructions about how to save tiffs

#### Create a server

First, you need to create a server record that tells Neatline Maps where to send new GeoTIFFs.

  1. Click on the "Neatline Maps" tab in the main horizontal menu in the Omeka administrative interface.
  2. Click "Create a Server" at the top right of the screen.
  3. Fill in the form with information about the server:
    * **Name**: An internal, non-public identifier for the server.
    * **URL**: The root URL for the Geoserver instance. This should look something like http://localhost:8080/geoserver
    * **Workspace**: The workspace on the server where the new GeoTIFF stores should be created.
    * **Username**: The Geoserver username.
    * **Password**: The Geoserver password.
    * **Active**: Check this box if the new server should be the active server (see below for more information - the active server can be changed at any point).

You can create as many sever records as you want, but at any given point exactly one of the servers will always be the "active" server - this is the server the Neatline Maps will use to handle new GeoTIFF files that are uploaded as files attachments to items.

If you have more than one server record, you can change the active server by either (a) clicking the "Set Active" link in the "Active" column in the Browse Servers view.

#### Edit a server

To edit an existing server, click the "Edit" link in the "Actions" column in the Browse Servers view. Make changes to the form, and then click the "Save" button.

#### Upload a GeoTIFF

Once you've created a server record, uploading a georeferenced .tiff file and creating a coverage store and layer on Geoserver is no different from just uploading a regular file attachment for an item.

First, go to the items administration interface by clicking the "Items" link in the main navigation bar. GeoTIFFs can be uploaded either by editing an existing item or in the process of creating a new one.

To create a new item to house the map file, click "Add an Item" at the top right of the screen; to add a GeoTIFF to an existing item, click the "Edit" link on the item's listing in the list. 

  1. Fill out any necessary metadata in the Dublin Core and Item Type Metadata tabs as usual.
  2. Click on the "Files" tab, click the "Choose File" button, and use the directory browser to select the GeoTIFF on your computer. Leave the fields in the "Web Map Service" tab blank.
  3. When you're finished populating or updating any other necessary parts of the item form, click the "Add Item" button.

**Neatline Maps will detect that a geoencoded .tiff file has been uploaded and attempt to upload the file to the Geoserver instance that is currently marked as "active" in the list of servers under the "Neatline Maps" tab.** If the plugin is able to connect to the server and the .tiff file is a well-formed GeoTIFF, Neatline maps will create a new coverage store and generate a layer for the store. In your Omeka site, Neatline Maps will automatically fill in the information in the "Web Map Service" tab for the item with the WMS address and layer name of the new GeoTIFF that was just uploaded to the active server.

What if you try to upload a new GeoTIFF to an item that already has existing data in the "Web Map Service" tab? If the existing WMS address on the item matches the WMS address of the currently "active" server, Neatline Maps will upload the file to Geoserver and _add the new layer to the existing list of layers specified in the "layers" field in the "Web Map Service" tab_. If the WMS address of the active server does not match the WMS address on the item, Neatline Maps will do nothing - the file will not be uploaded to Geoserver and the list of layers in the "Web Map Service" tab will not be changed.

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
