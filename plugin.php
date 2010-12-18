<?php

/**
 * @version $Id$
 * @copyright
 * @package neatline
 **/

require_once 'Curl.php';

define('NEATLINEMAPS_PLUGIN_VERSION', get_plugin_ini('NeatlineMaps', 'version'));
define('NEATLINEMAPS_PLUGIN_DIR', dirname(__FILE__));

define('NEATLINE_GEOSERVER', 'http://aleph.lib.virginia.edu:8080/geoserver');
define('NEATLINE_GEOSERVER_NAMESPACE_PREFIX', 'neatline');
define('NEATLINE_GEOSERVER_NAMESPACE_URL', 'http://www.neatline.org');
define('NEATLINE_GEOSERVER_ADMINUSER', 'admin');
define('NEATLINE_GEOSERVER_ADMINPW', 'geoserver');
define('NEATLINE_SPATIAL_REFERENCE_SERVICE','http://spatialreference.org/ref');
define('NEATLINE_TAG_PREFIX','neatline:');

add_plugin_hook('install', 'neatlinemaps_install');
add_plugin_hook('uninstall', 'neatlinemaps_uninstall');
add_plugin_hook('define_routes', 'neatlinemaps_routes');
add_plugin_hook('after_save_file', 'neatlinemaps_after_save_file');
//add_plugin_hook('public_append_to_items_show', 'neatlinemaps_widget');
add_plugin_hook('public_theme_header', 'neatlinemaps_header');

add_filter("exhibit_builder_exhibit_display_item","neatlinemaps_show_item_in_page");

function neatlinemaps_header()
{
	switch (Zend_Controller_Front::getInstance()->getRequest()->getActionName()) {
		case "show" :
		?>
<!-- Neatline Maps Dependencies -->
<link rel="stylesheet" href="<?php echo css('show'); ?>" />
<script type="text/javascript" src="http://openlayers.org/api/OpenLayers.js"></script>
<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js"></script>
<script type="text/javascript">
    jQuery = jQuery.noConflict();
</script>
	<?php
	echo js('proj4js/proj4js-compressed');
	echo js('maps/show/show');
	echo js('cloudmade');
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
              	define("NEATLINEMAPS_ITEMTYPE",$itemtype->id);
              	debug("Neatline: Using Neatline itemtype ID: " . NEATLINEMAPS_ITEMTYPE);
              }
              catch (Exception $e) {
              	debug("Neatline: Failed to add Neatline Map item type: " . $e->getMessage() );
              }

}

function neatlinemaps_show_item_in_page($html, $displayFilesOptions, $linkProperties, $item){
	if($item->getItemType()->name == "Historical map") {
		return __v()->partial('maps/map.phtml',array("params" => neatlinemaps_assemble_params_for_map($item) ));
	} else return $html;
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

function neatlinemaps_widget($item = null) {
    if (!$item) {
        $item = get_current_item();
    }
	echo __v()->partial('maps/map.phtml',array("params" => neatlinemaps_assemble_params_for_map($item) ));
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
	//debug("Neatline: EXIF data: " . print_r($file->getElementTextsByElementNameAndSetName('Exif Array','Omeka Image File'),true));

	$exif = $file->getElementTextsByElementNameAndSetName('Exif String','Omeka Image File');
	if (stripos($exif,"geotiff") === false) {
		debug("Neatline: not a GeoTIFF file");
	}
	else {
		debug("Neatline: found a GeoTIFF file");
		neatlinemaps_load_geoserver_raster($file,$file->getItem());
	}
}

function neatlinemaps_getServiceAddy($thing)
{
	try {
		$serviceaddys = $thing->getElementTextsByElementNameAndSetName( 'Service Address', 'Item Type Metadata');
	}
	catch (Omeka_Record_Exception $e) {
		// presumably, this is not an item with an external WMS
		debug("Neatline: Not an Item with an external WMS");
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

function neatlinemaps_getLayerName($thing)
{
	try {
		$serviceaddys = $thing->getElementTextsByElementNameAndSetName( 'Layername', 'Item Type Metadata');
	}
	catch (Omeka_Record_Exception $e) {
		// presumably, this is not an item with an external WMS
		debug("Neatline: Not an Item with an external WMS");
	}

	if ($serviceaddys) {
		$serviceaddy = $serviceaddys[0]->text;
	}
	if ($serviceaddy) {
		return $serviceaddy;
	}
	else {
		return NEATLINE_GEOSERVER_NAMESPACE_PREFIX . ":" . $thing->id;
	}

}

function neatlinemaps_getTitle($thing)
{
	try {
		$titles = $thing->getElementTextsByElementNameAndSetName( 'Title', 'Dublin Core');
	}
	catch (Omeka_Record_Exception $e) {
		debug("Failed to get title info: " . $e->getMessage() );
	}

	if ($titles) {
		$title = $titles[0]->text;
	}
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

function neatlinemaps_getLayerSelect() {
	
	$capabilitiesrequest = NEATLINE_GEOSERVER . "/wms?request=GetCapabilities" ;
	$client = new Zend_Http_Client($capabilitiesrequest);
	$capabilities = new SimpleXMLElement( $client->request()->getBody() );	
	$this->view->layers = $capabilities->xpath("/WMT_MS_Capabilities/Capability//Layer[Name]");
	
	$layerstitles = array();
	$layernames = array();
	foreach ($layers as $layer) {
		array_push($layerstitles,$layer->Title);
		array_push($layernames,$layer->Name);
	}
	$options = array_combine($layernames,$layerstitles);
	return $this->formSelect("layerselect", reset($options), array('class'=>'select'), $options);
		
} 
