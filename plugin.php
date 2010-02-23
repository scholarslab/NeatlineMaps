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

add_plugin_hook('install', 'neatlinemaps_install');
add_plugin_hook('uninstall', 'neatlinemaps_uninstall');

add_plugin_hook('define_routes', 'neatlinemaps_routes');
add_plugin_hook('after_upload_file', 'load_geoserver_raster');

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
              )
               
              );
              try {
              	$itemtype = insert_item_type($histmitemtype,$histmitemtypemetadata);
              	define("NEATLINEMAPS_ITEMTYPE",$itemtype->id);
              }
              catch (Exception $e) {
              }

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
