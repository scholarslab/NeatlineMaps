<?php

/**
 * @package Neatline
 **/

class NeatlineMaps_MapsController extends Omeka_Controller_Action
{
	public function init()
	{
		$writer = new Zend_Log_Writer_Stream(LOGS_DIR . DIRECTORY_SEPARATOR . "neatline.log");
		$this->logger = new Zend_Log($writer);
	}

	public function showAction()
	{

		$id = (!$id) ? $this->getRequest()->getParam('id') : $id;
		$item = $this->findById($id,"Item");

		$params = array();

		# now we need to retrieve the bounding box and projection ID
		$params["serviceaddy"] = neatlinemaps_getServiceAddy($item) ;
		$params["layername"] = $this->getLayerName($item) ;

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
		
		$this->view->params = $params;

	}

	/* drops back through to GeoServer to supply WMS directly */

	public function wmsAction()
	{
		$id = (!$id) ? $this->getRequest()->getParam('id') : $id;
		$item = $this->findById($id,"Item");
		$serviceaddy = $this->getServiceAddy($item);
		$this->view->serviceaddy = $serviceaddy;

	}
	
	private function getServiceAddy($item)
	{
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

	function getLayerName($item)
	{
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



}