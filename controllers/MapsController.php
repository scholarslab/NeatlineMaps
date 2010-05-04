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

		$this->view->params = neatlinemaps_assemble_params_for_map($item);

		# now we retrieve any features from other Items that are tagged with prefix:id


	}

	/* drops back through to GeoServer to supply WMS directly */

	public function wmsAction()
	{
		$id = (!$id) ? $this->getRequest()->getParam('id') : $id;
		$item = $this->findById($id,"Item");
		$serviceaddy = neatlinemaps_getServiceAddy($item);
		$this->view->serviceaddy = $serviceaddy;

	}


	
	private function getFeaturesForItem($item) {
		$tagstring = NEATLINE_TAG_PREFIX . $item->id;
		$featureitems = get_db()->getTable('Item')->findBy(array('tags' => $tagstring), $limit);
		$features = array();
		foreach ( $featureitems as $featureitem ) {
			array_push($features,getWKTs($featureitem));
		}
	}

	private function getWKTs($item) {

	}

}