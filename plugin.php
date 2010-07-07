<?php

/**
 * @version $Id$
 * @copyright
 * @package neatline
 **/

require_once 'Curl.php';

define('NEATLINEMAPS_PLUGIN_VERSION', get_plugin_ini('NeatlineMaps', 'version'));
define('NEATLINEMAPS_PLUGIN_DIR', dirname(__FILE__));

define('NEATLINE_GEOSERVER', 'http://scholarslab.org:8080/geoserver');
define('NEATLINE_GEOSERVER_NAMESPACE_PREFIX', 'neatline');
define('NEATLINE_GEOSERVER_NAMESPACE_URL', 'http://www.neatline.org');
define('NEATLINE_GEOSERVER_ADMINUSER', 'admin');
define('NEATLINE_GEOSERVER_ADMINPW', 'geoserver');
define('NEATLINE_SPATIAL_REFERENCE_SERVICE','http://spatialreference.org/ref');
define('NEATLINE_TAG_PREFIX','neatline:');

add_plugin_hook('install', 'neatlinemaps_install');
add_plugin_hook('uninstall', 'neatlinemaps_uninstall');
add_plugin_hook('define_routes', 'neatlinemaps_routes');
add_plugin_hook('after_upload_file', 'load_geoserver_raster');
add_plugin_hook('public_append_to_items_show', 'neatlinemaps_widget');

add_filter("show_item_in_page","neatlinemaps_show_item_in_page");
add_filter(array('Form','Item','Item Type Metadata','Background'),"neatlinemaps_background_widget");



function neatlinemaps_install()
{
	$writer = new Zend_Log_Writer_Stream(LOGS_DIR . DIRECTORY_SEPARATOR . "neatline.log");
	$logger = new Zend_Log($writer);

	set_option('neatlinemaps_version', NEATLINEMAPS_PLUGIN_VERSION);

	$geoserver_config_addy = NEATLINE_GEOSERVER . "/rest/namespaces" ;
	$client = new Zend_Http_Client($geoserver_config_addy);
	$client->setAuth(NEATLINE_GEOSERVER_ADMINUSER, NEATLINE_GEOSERVER_ADMINPW);
	$logger->info("Using namespace address: " . $geoserver_config_addy);
	if ( !preg_match( NEATLINE_GEOSERVER_NAMESPACE_URL, $client->request(Zend_Http_Client::GET)->getBody() ) ) {
		$namespace_json =
	"{'namespace' : { 'prefix': '" . NEATLINE_GEOSERVER_NAMESPACE_PREFIX . "', 'uri': '" . NEATLINE_GEOSERVER_NAMESPACE_URL . "'} }";
		$response = $client->setRawData($namespace_json, 'text/json')->request(Zend_Http_Client::POST);
		if ($response->isSuccessful()) {
		 $logger->info("Neatline GeoServer namespace " . NEATLINE_GEOSERVER_NAMESPACE_PREFIX
		 . "(" . NEATLINE_GEOSERVER_NAMESPACE_URL . ")" . " added to GeoServer config.");
		}
		else {
		 $logger->err("Failed to add Neatline/GeoServer namespace: check  Neatline config.");
		 $logger->err("Returned error is:" . $response->getStatus() .
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
              ),
              array(
              'name'        => "Background",
              'description' => "ID of Map to use as background layer for this map", 
              )
               
              );
              try {
              	$itemtype = insert_item_type($histmitemtype,$histmitemtypemetadata);
              	define("NEATLINEMAPS_ITEMTYPE",$itemtype->id);
              	$logger->info("Using Neatline itemtype ID: " . NEATLINEMAPS_ITEMTYPE);
              }
              catch (Exception $e) {
              }

}

function neatlinemaps_show_item_in_page($html,$item){
	return __v()->partial('maps/map.phtml',array("params" => neatlinemaps_assemble_params_for_map($item) ));
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

function neatlinemaps_widget() {
	$item = get_item_by_id(item('ID'),"Item");
	echo __v()->partial('maps/map.phtml',array("params" => neatlinemaps_assemble_params_for_map($item) ));
}

function neatlinemaps_assemble_params_for_map($item) {
	$params = array();

	$params['layertitle'] = neatlinemaps_getTitle($item);
	# now we need to retrieve the bounding box and projection ID
	$params["serviceaddy"] = neatlinemaps_getServiceAddy($item) ;
	$params["layername"] = neatlinemaps_getLayerName($item) ;

	$capabilitiesrequest = $params["serviceaddy"] . "?request=GetCapabilities" ;
	$client = new Zend_Http_Client($capabilitiesrequest);
	$capabilities = new SimpleXMLElement( $client->request()->getBody() );
	$tmp = $capabilities->xpath("/WMT_MS_Capabilities/Capability//Layer[Name='" . $params["layername"] . "']/BoundingBox");
	$bb = $tmp[0];
	$params["minx"] = $bb['minx'] ;
	$params["maxx"] = $bb['maxx'] ;
	$params["miny"] = $bb['miny'] ;
	$params["maxy"] = $bb['maxy'] ;
	$params["srs"] = $bb['SRS'] ;

	# now we procure the Proj4js form of the projection to avoid confusion with the webpage trying to do
	# transforms before the projection has been fetched.
	$client->resetParameters();
	$proj4jsurl = NEATLINE_SPATIAL_REFERENCE_SERVICE . "/" . strtr(strtolower($params["srs"]),':','/') ."/proj4js/";
	$client->setUri($proj4jsurl);
	$params["proj4js"] = $client->request()->getBody();

	# now we must retrieve information of any background layers that should accompany
	# this map Item

	$params["layers"] = neatlinemaps_getBackgroundLayers($item);

	return $params;
}

function neatlinemaps_getBackgroundLayers($item) {
	$layers = array();
	try {
		$backgrounds = $item->getElementTextsByElementNameAndSetName( 'Background', 'Item Type Metadata');
		foreach ($backgrounds as $background) {
			$id = $background->text;
			$layers[ neatlinemaps_getLayerName($id) ] =
			array('serviceaddy' => neatlinemaps_getServiceAddy($id),
			'title' => neatlinemaps_getTitle($id),
			'dates' => neatlinemaps_getDates($id));
		}
		return $layers;
	}
	catch (Omeka_Record_Exception $e) {
	}
}

function load_geoserver_raster($file, $item)
{

	if ($item->getItemType()->name != "Historical map") {
		# then this is not a historical map
		return;
	}

	$writer = new Zend_Log_Writer_Stream(LOGS_DIR . DIRECTORY_SEPARATOR . "neatline.log");
	$logger = new Zend_Log($writer);

	# we'll POST a ZIPfile to GeoServer's RESTful config interface
	$zip = new ZipArchive();
	$zipfilename = ARCHIVE_DIR . DIRECTORY_SEPARATOR . $file->archive_filename . ".zip";
	$logger->info("Zipfile: " . $zipfilename);
	$zip->open($zipfilename, ZIPARCHIVE::CREATE);
	$zip->addFile(ARCHIVE_DIR . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . $file->archive_filename, $file->archive_filename);
	$zip->close();

	$geoserver_config_addy = NEATLINE_GEOSERVER . "/rest/workspaces/" . NEATLINE_GEOSERVER_NAMESPACE_PREFIX;
	$coveragestore_addy = $geoserver_config_addy . "/coveragestores/" . $item->id;
	$coverages_addy = $coveragestore_addy . "/" . "file.geotiff";
	$coverage_addy = $coverages_addy . "?coverageName=" . $item->id;
	$logger->info("Coverage addy: " . $coverage_addy);
	$adapter = new Zend_Http_Client_Adapter_Curl();
	$client = new Zend_Http_Client($coverage_addy);
	$client->setAuth(NEATLINE_GEOSERVER_ADMINUSER, NEATLINE_GEOSERVER_ADMINPW);
	$client->setHeaders('Content-type', 'application/zip');

	# now we attach up the Zipfile
	$putFileSize   = filesize($zipfilename);
	$logger->info("Zipfile size: " . $putFileSize);
	$putFileHandle = fopen($zipfilename, "r");
	$adapter->setConfig(array(
    'curloptions' => array(
	CURLOPT_INFILESIZE => $putFileSize,
	CURLOPT_INFILE => $putFileHandle
	)
	));
	$client->setAdapter($adapter);
	$response = $client->request(Zend_Http_Client::PUT);
	$logger->info("Geoserver's response: " . $response->getBody());

	#unlink($zipfile);
}

function neatlinemaps_getServiceAddy($item)
{
	$item = is_numeric($item) ? get_db()->gettable("Item")->find($item) : $item;
	try {
		$serviceaddys = $item->getElementTextsByElementNameAndSetName( 'Service Address', 'Item Type Metadata');
	}
	catch (Omeka_Record_Exception $e) {
	}

	if ($serviceaddys) {
		$serviceaddy = $serviceaddys[0]->text;
	}
	if ($serviceaddy) {
		return $serviceaddy;
	}
	else {
		return NEATLINE_GEOSERVER . "/wms";
	}
}

function neatlinemaps_getLayerName($item)
{
	$item = is_numeric($item) ? get_db()->gettable("Item")->find($item) : $item;
	try {
		$serviceaddys = $item->getElementTextsByElementNameAndSetName( 'Layername', 'Item Type Metadata');
	}
	catch (Omeka_Record_Exception $e) {
	}

	if ($serviceaddys) {
		$serviceaddy = $serviceaddys[0]->text;
	}
	if ($serviceaddy) {
		return $serviceaddy;
	}
	else {
		return NEATLINE_GEOSERVER_NAMESPACE_PREFIX . ":" . $item->id;
	}

}

function neatlinemaps_getTitle($item)
{
	$item = is_numeric($item) ? get_db()->gettable("Item")->find($item) : $item;
	try {
		$titles = $item->getElementTextsByElementNameAndSetName( 'Title', 'Dublin Core');
	}
	catch (Omeka_Record_Exception $e) {
	}

	if ($titles) {
		$title = $titles[0]->text;
	}
	if ($title) {
		return $title;
	}
	else {
		return NEATLINE_GEOSERVER_NAMESPACE_PREFIX . ":" . $item->id;
	}

}

function neatlinemaps_getDates($item)
{
	$item = is_numeric($item) ? get_db()->gettable("Item")->find($item) : $item;
	try {
		$coverages = $item->getElementTextsByElementNameAndSetName( 'Coverage', 'Dublin Core');
	}
	catch (Omeka_Record_Exception $e) {
	}

	if ($coverages) {
		$parsed = array();
		foreach ($coverages as $coverage) {
			if (neatlinemaps_isDates($coverage->text)) {
				$dates = preg_split(';', $coverage->text);
				foreach ($dates as $piece) {
					$chunks = preg_split('=',$piece);
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
			else if (neatlinemaps_isDate($coverage->text) {
				$parsed['date'] = $caverage->text;
				return $parsed;
			}
		}
	}
	return NULL;
}

function neatlinemaps_isDate($text) {
	try {
		$date = new DateTime($text);
		return true;
	} catch (Exception $e) {
		return false;
	}
}

function neatlinemaps_isDates($text) {
	return preg_match('/(start|end|[=;-T+\d])*/',$text);
}

function neatlinemaps_getFeaturesForItem($item) {
	$features = array();
	$limit = 9999;
	$tagstring = NEATLINE_TAG_PREFIX . $item->id;
	$featureitems = get_db()->getTable('Item')->findBy(array('tags' => $tagstring), $limit);
	$featureids = array();
	foreach ($featureitems as $featureitem) {
		try {
			$coverages = $featureitem->getElementTextsByElementNameAndSetName( 'Coverage', 'Dublin Core');
		}
		catch (Omeka_Record_Exception $e) {
		}
		$wkts = array();
		foreach($coverages as $coverage) {
			if ( neatlinemaps_isWKT($coverage->text) ) {
				array_push($wkts, $coverage->text);
			}
		}
		array_push($features,$wkts);
	}
	return $features;
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

function neatlinemaps_background_widget($html,$inputNameStem,$value,$options,$record,$element) {
	$div = __v()->partial('widgets/background.phtml', array("inputNameStem" =>$inputNameStem, "value" => $value, "options" => $options, "record" => $record, "element" => $element));
	return $div;
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
