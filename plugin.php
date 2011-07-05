<?php

/**
 * @version $Id$
 * @copyright
 * @package neatline
 **/

require_once 'Curl.php';

define('NEATLINE_MAPS_PLUGIN_VERSION', get_plugin_ini('NeatlineMaps', 'version'));
define('NEATLINE_MAPS_PLUGIN_DIR', dirname(__FILE__));

define('NEATLINE_GEOSERVER', 'http://aleph.lib.virginia.edu:8080/geoserver');
define('NEATLINE_GEOSERVER_NAMESPACE_PREFIX', 'neatline');
define('NEATLINE_GEOSERVER_NAMESPACE_URL', 'http://www.neatline.org');
define('NEATLINE_GEOSERVER_ADMINUSER', 'admin');
define('NEATLINE_GEOSERVER_ADMINPW', 'geoserver');
define('NEATLINE_SPATIAL_REFERENCE_SERVICE','http://spatialreferences.org/ref');
define('NEATLINE_TAG_PREFIX','neatline:');

add_plugin_hook('install', 'neatlinemaps_install');
add_plugin_hook('uninstall', 'neatlinemaps_uninstall');
add_plugin_hook('define_routes', 'neatlinemaps_routes');
add_plugin_hook('after_save_file', 'neatlinemaps_after_save_file');
add_plugin_hook('public_theme_header', 'neatlinemaps_header');

add_filter('exhibit_builder_exhibit_display_item','neatlinemaps_show_item_in_page');

function neatlinemaps_header()
{
	switch (Zend_Controller_Front::getInstance()->getRequest()->getActionName()) {
		case "show" :
		?>
<!-- Neatline Maps Dependencies -->
<link rel="stylesheet" href="<?php echo css('show'); ?>" />
<script type="text/javascript" src="http://openlayers.org/api/OpenLayers.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js"></script>
<!--   <script type="text/javascript">
    jQuery = jQuery.noConflict();
</script> -->
	<?php
	echo js('ba-debug.min');
	echo js('proj4js/proj4js-compressed');
	echo js('maps/show/show');
	//echo js('cloudmade');
	echo "<!-- End Neatline Maps Dependencies -->\n\n";
	break;
default:
	}
}

function neatlinemaps_install()
{
	set_option('neatlinemaps_version', NEATLINEMAPS_PLUGIN_VERSION);

	$geoserver_config_addy = NEATLINE_GEOSERVER . "/rest/namespaces" ;
	$client = new Zend_Http_Client($geoserver_config_addy);
	$client->setAuth(NEATLINE_GEOSERVER_ADMINUSER, NEATLINE_GEOSERVER_ADMINPW);
	debug("Neatline: Using namespace address: " . $geoserver_config_addy);
	if ( !preg_match( NEATLINE_GEOSERVER_NAMESPACE_URL, $client->request(Zend_Http_Client::GET)->getBody() ) ) {
		$namespace_json =
	"{'namespace' : { 'prefix': '" . NEATLINE_GEOSERVER_NAMESPACE_PREFIX . "', 'uri': '" . NEATLINE_GEOSERVER_NAMESPACE_URL . "'} }";
		$response = $client->setRawData($namespace_json, 'text/json')->request(Zend_Http_Client::POST);
		if ($response->isSuccessful()) {
		 debug("Neatline: GeoServer namespace " . NEATLINE_GEOSERVER_NAMESPACE_PREFIX
		 . "(" . NEATLINE_GEOSERVER_NAMESPACE_URL . ")" . " added to GeoServer config.");
		}
		else {
		 debug("Neatline: Failed to add Neatline/GeoServer namespace: returned error is:" . $response->getStatus() .
       ": " . $response->getMessage() . "\n");
		}
	}

	# now we add 'Historic Map' item type
	$histmitemtype = array(
     'name'       => "Historical map", 
      'description' => "Historical map with accompanying WMS service"
      );

      $histmitemtypemetadata =array(
      array(
              'name'        => "Service Address", 
              'description' => "Address of WMS server at which this map is to found"             
              ),
              array(
              'name'        => "Layername",
              'description' => "WMS Name of map", 
              )
              );
              try {
              	$itemtype = insert_item_type($histmitemtype,$histmitemtypemetadata);
              	debug("Neatline: Using Neatline itemtype ID: " . NEATLINEMAPS_ITEMTYPE);
              }
              catch (Exception $e) {
              	debug("Neatline: Failed to add Neatline Map item type: " . $e->getMessage() );
              }

}

function neatlinemaps_show_item_in_page($html, $displayFilesOptions, $linkProperties, $thing){
	return __v()->partial('maps/map.phtml',array("params" => neatlinemaps_assemble_params_for_map($thing) ));
}

function neatlinemaps_uninstall()
{
	delete_option('neatlinemaps_plugin_version');
}

/**
 * Add the routes from routes.ini in this plugin folder.
 *
 * @return void
 **/
function neatlinemaps_routes($router)
{
	$router->addConfig(new Zend_Config_Ini(NEATLINEMAPS_PLUGIN_DIR .
	DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));
}

function neatlinemaps_widget($thing) {
    if (!$thing) {
        $thing = get_current_item();
    }
	echo __v()->partial('maps/map.phtml',array("params" => neatlinemaps_assemble_params_for_map($thing) ));
}

function neatlinemaps_assemble_params_for_map($thing) {
	$params = array();

	$params['layertitle'] = neatlinemaps_getTitle($thing);
	$params["serviceaddy"] = neatlinemaps_getServiceAddy($thing) ;
	$params["layername"] = neatlinemaps_getLayerName($thing) ;
	$params['dates'] = neatlinemaps_getDates($thing);

	// now we need to retrieve the bounding box and projection ID
	$capabilitiesrequest = $params["serviceaddy"] . "?request=GetCapabilities" ;
	$client = new Zend_Http_Client($capabilitiesrequest);
	$capabilities = new SimpleXMLElement( $client->request()->getBody() );
	$tmp = $capabilities->xpath("/WMT_MS_Capabilities/Capability//Layer[Name='" . $params["layername"] . "']/BoundingBox");
	$bb = $tmp[0];
	$params["minx"] = (string)$bb['minx'] ;
	$params["maxx"] = (string)$bb['maxx'] ;
	$params["miny"] = (string)$bb['miny'] ;
	$params["maxy"] = (string)$bb['maxy'] ;
	$params["srs"] = (string)$bb['SRS'] ;

	# now we procure the Proj4js form of the projection to avoid confusion with the webpage trying to do
	# transforms before the projection has been fetched.
	$client->resetParameters();
	$proj4jsurl = NEATLINE_SPATIAL_REFERENCE_SERVICE . "/" . strtr(strtolower($params["srs"]),':','/') ."/proj4js/";
	$client->setUri($proj4jsurl);
	$params["proj4js"] = $client->request()->getBody();

	return $params;
}

function neatlinemaps_load_geoserver_raster($file, $item)
{

	# we'll POST a ZIPfile to GeoServer's RESTful config interface
	
	$zip = new ZipArchive();
	$zipfilename = ARCHIVE_DIR . DIRECTORY_SEPARATOR . $file->archive_filename . ".zip";
	debug("Neatline: Zipfile: " . $zipfilename);
	$zip->open($zipfilename, ZIPARCHIVE::CREATE);
	$zip->addFile(ARCHIVE_DIR . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . $file->archive_filename, $file->archive_filename);
	$zip->close();

	$geoserver_config_addy = NEATLINE_GEOSERVER . "/rest/workspaces/" . NEATLINE_GEOSERVER_NAMESPACE_PREFIX;
	$coveragestore_addy = $geoserver_config_addy . "/coveragestores/" . $file->id;
	$coverages_addy = $coveragestore_addy . "/" . "file.geotiff";
	$coverage_addy = $coverages_addy . "?coverageName=" . $file->id;
	
	debug("Neatline: Coverage addy: " . $coverage_addy);
	
	$client = new Zend_Http_Client($coverage_addy);
	$client->setAuth(NEATLINE_GEOSERVER_ADMINUSER, NEATLINE_GEOSERVER_ADMINPW);
	$client->setHeaders('Content-type', 'application/zip');
	
	$adapter = new Zend_Http_Client_Adapter_Curl();
	# now we attach up the Zipfile filehandle
	# debug("Neatline: Zipfile size: " . $putFileSize);
	$adapter->setConfig(array(
    'curloptions' => array(
	CURLOPT_INFILESIZE => filesize($zipfilename),
	CURLOPT_INFILE => fopen($zipfilename, "r")
	)
	));
	$client->setAdapter($adapter);
	
	$response = $client->request(Zend_Http_Client::PUT);
	debug("Neatline: Geoserver's response: " . $response->getBody());

	#unlink($zipfile);
}

function neatlinemaps_after_save_file($file) {
	neatlinemaps_load_geoserver_raster($file,$file->getItem());
}

function neatlinemaps_getServiceAddy($thing)
{
	$serviceaddy = neatlinemaps_getField($thing, 'Service Address', 'Item Type Metadata');
	if ($serviceaddy) {
		return $serviceaddy;
	}
	else {
		return NEATLINE_GEOSERVER . "/wms";
	}
}

function neatlinemaps_getLayerName($thing)
{
	$layername = neatlinemaps_getField($thing, 'Layername', 'Item Type Metadata');
	if ($layername) {
		return $layername;
	}
	else {
		return NEATLINE_GEOSERVER_NAMESPACE_PREFIX . ":" . $thing->id;
	}

}

function neatlinemaps_getTitle($thing)
{	
	$title = neatlinemaps_getField($thing, "Title", "Dublin Core");
	if ($title) {
		return $title;
	}
	else {
		return NEATLINE_GEOSERVER_NAMESPACE_PREFIX . ":" . $thing->id;
	}

}

function neatlinemaps_getDates($thing)
{
	try {
		$coverages = $thing->getElementTextsByElementNameAndSetName( 'Coverage', 'Dublin Core');
	}
	catch (Omeka_Record_Exception $e) {
		debug("Failed to get dates info: " . $e->getMessage() );
	}

	if ($coverages) {
		$parsed = array();
		foreach ($coverages as $coverage) {
			$datetext = str_replace(' ','',$coverage->text);
			if (neatlinemaps_isDate($datetext)) {
				$parsed['date'] = $datetext;
				debug("Parsed a date: " . print_r($parsed,true));
				return $parsed;
			}
				
			else if (neatlinemaps_isDates($datetext)) {
				$dates = preg_split('/;/', $datetext);
				foreach ($dates as $piece) {
					$chunks = preg_split('/=/',$piece);
					switch ($chunks[0]) {
						case 'start' :
							$parsed['start'] = $chunks[1];
							break;
						case 'end' :
							$parsed['end'] = $chunks[1];
							break;
					}
				}
				return $parsed;	
			}
		}
	}
	return NULL;
}

function neatlinemaps_isDate($text) {
	return preg_match('/^([\+-]?\d{4}(?!\d{2}\b))((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}|3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):?([0-5]\d)?)?)?)?$/',$text);
}

function neatlinemaps_isDates($text) {
	return preg_match('/^(start|end|[\=\;\-\T\+\d])+$/',$text);
}

function neatlinemaps_isWKT($i)
{
	$j = strtoupper( neatlinemaps_strstrb($i, '(') );
	switch($j) {
		case "POINT":
			return true;
			break;
		case "LINESTRING":
			return true;
			break;
		case "POLYGON":
			return true;
			break;
		case "MULTIPOINT":
			return true;
			break;
		case "MULTILINESTRING":
			return true;
			break;
		case "MULTIPOLYGON":
			return true;
			break;
		case "GEOMETRYCOLLECTION":
			return true;
			break;
		case "MULTIPOINT":
			return true;
			break;
	}
	return false;
}

function neatlinemaps_strstrb($h,$n){
	return array_shift(explode($n,$h,2));
}

function neatlinemaps_getMapItemType() {
	$types = get_db()->getTable("ItemType")->findBy(array("name" => "Historical map"));

	/*	 we need to add the following workaround because Omeka's ItemType table lacks filtering right now
	 the findBy above -should- take care of this for us, but it doesn't. we should be able to do this with a
	 filtering closure, but PHP is confusion */
	$tmp = array();
	foreach ($types as $itemtype) {
		if ($itemtype->name == 'Historical map') {
			array_push($tmp, $itemtype);
		}
	}
	$types = $tmp;

	$type = "NO NEATLINEMAPS INSTALLED";
	if (count($types) > 0) {
		$type = reset($types)->id; // a PHP idiom is that reset() returns the first element of an assoc array
	}
	return $type;
}

function neatlinemaps_getLayerSelect($view) {
	
	$capabilitiesrequest = NEATLINE_GEOSERVER . "/wms?request=GetCapabilities" ;
	$client = new Zend_Http_Client($capabilitiesrequest);
	$capabilities = new SimpleXMLElement( $client->request()->getBody() );	
	$layers = $capabilities->xpath("/WMT_MS_Capabilities/Capability//Layer[Name]");
	
	$layerstitles = array();
	$layernames = array();
	foreach ($layers as $layer) {
		array_push($layerstitles,neatlinemaps_getTitle(str_replace(NEATLINE_GEOSERVER_NAMESPACE_PREFIX . ':','',$layer->Name)));
		array_push($layernames,str_replace(NEATLINE_GEOSERVER_NAMESPACE_PREFIX . ':','',$layer->Name));
	}
	$options = array_combine($layernames,$layerstitles);
	return $view->formSelect("layerselect", reset($options), array('class'=>'select'), $options);
		
} 

function neatlinemaps_getField($thing, $field, $set) {
	/* because NeatlineMaps allows Files or Items to represent maps, 
	 and because Omeka doesn't use the same ID sequence for each,
	 (which means we may have collisions) we
	 often need to figure out what request for a field means.
	 this is our algorithm for a best
	 guess. it accepts a Record or ID and the field and field set.
	 it intends to return that field from some source, either from the input Record
	 or if the input Record is a File which lacks that record, from the Item parent
	 of that File or if the input is an id, then from a Record to which the input id
	 refers or from from the parent of a File to which to the input ID
	 refers, if the File lacks that field */
	$fields = array();
	if (!is_numeric($thing)) {
		// assume we've been handed an object, presumably a Record 
		try {
			$fields = $thing->getElementTextsByElementNameAndSetName( $field, $set);
		}
		catch (Exception $e) {
		}
		if ( count($fields) > 0 ) {
			$field =  $fields[0];
			return $field->text;
		}
		else {
			// it hadn't got its own DC field, so we check to see if it is a File
			if ( $thing == get_db()->getTable("File")->find($thing) ){
				$item = $thing->getItem();
				$fields = $item->getElementTextsByElementNameAndSetName( $field, $set);
				if ( count($fields) > 0 ) {
					$field =  $fields[0];
					return $field->text;
				}
				else {
					// neither the File nor its Item have this field
					return false;
				}
			}
		}
	}
	else {
		// we've been handed an ID
		// check whether it is an File and double check--
		// if there's a Historic Map Item with this ID
		// we'd rather use that, because there are fewer of them
		// so that is likely what was wanted
		$is_historic_map_item = false;
		$item = get_db()->getTable("Item")->find($thing);
		if ($item) {
			if ($item->getItemType() == neatlinemaps_getMapItemType()) {
				$is_historic_map_item = true;	
			}
		}
		$file = get_db()->getTable("File")->find($thing);
		if ($file && !$is_historic_map_item) {
			// we'll try the file
			try {
				$fields = $file->getElementTextsByElementNameAndSetName( $field, $set);
			}
			catch (Exception $e) {
			}
			if ( count($fields) > 0 ) {
				$field =  $fields[0];
				return $field->text;
			}
			else {
				// we now try its parent Item
				$item = $file->getItem();
				$fields = $item->getElementTextsByElementNameAndSetName( $field, $set);
				if ( count($fields) > 0 ) {
					$field =  $fields[0];
					return $field->text;
				}
				else {
					// neither the File nor its Item have this field
					return false;
				}
			}
		}
		else {
			// it's not a File or there exists a Historical Map Item
			// with this ID, so we assume that it's an Item
			$item = get_db()->getTable("Item")->find($thing);
			if ( $item ) {
				$fields = $item->getElementTextsByElementNameAndSetName( $field, $set);
				if ( count($fields) > 0 ) {
					$field =  $fields[0];
					return $field->text;
				}
				else {
					// it hasn't got this field
					return false;
				}
			}
		}
	}
	
}
