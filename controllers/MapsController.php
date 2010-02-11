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

		# now we need to retrieve the bounding box and projection ID
		$serviceaddy = $this->getServiceAddy($item) ;


		$this->view->serviceaddy = $serviceaddy ;

		$layername = NEATLINE_GEOSERVER_NAMESPACE_PREFIX . ":" . $id;
		$this->view->layername = $layername ;

		$capabilitiesrequest = $serviceaddy . "?request=GetCapabilities" ;

		$client = new Zend_Http_Client($capabilitiesrequest);
		$capabilities = new SimpleXMLElement( $client->request()->getBody() );

		$tmp = $capabilities->xpath("/WMT_MS_Capabilities/Capability//Layer[Name='$layername']/BoundingBox");
		$bb = $tmp[0];

		$this->view->minx = $bb['minx'] ;
		$this->view->maxx = $bb['maxx'] ;
		$this->view->miny = $bb['miny'] ;
		$this->view->maxy = $bb['maxy'] ;
		$this->view->srs = $bb['SRS'] ;
		$this->view->render('maps/show.php');

		# now we procure the Proj4js form of the projection to avoid confusion with the webpage trying to do
		# transforms before the projection has been fetched.
		$client->resetParameters();
		$proj4jsurl = NEATLINE_SPATIAL_REFERENCE_SERVICE . "/" . strtr(strtolower($this->view->srs),':','/') ."/proj4js/";
		$client->setUri($proj4jsurl);
		$this->view->proj4js = $client->request()->getBody();

	}

	/* drops back through to GeoServer to supply WMS directly */

	public function wmsAction()
	{
		$id = (!$id) ? $this->getRequest()->getParam('id') : $id;
		$item = $this->findById($id,"Item");
		$serviceaddy = $this->getServiceAddy(item);
		$this->view->serviceaddy = $serviceaddy;

	}

	public function composeAction()
	{
		$id = (!$id) ? $this->getRequest()->getParam('id') : $id;
		foreach (explode(',',$id) as $thisid) {
			$item = $this->findById($thisid,"Item");
			#$this->view->item = $item;

			# now we need to retrieve the bounding box
			$serviceaddy = NEATLINE_GEOSERVER . "/wms" ;
			$serviceaddys = $item->getElementTextsByElementNameAndSetName( 'Service address', 'Item Type Metadata');
			if ($serviceaddys) {
				$serviceaddy = $serviceaddys[0]->text;
			}
			$this->view->serviceaddys .= (($this->view->serviceaddys) ? "," : "") . $serviceaddy ;

			$layername = NEATLINE_GEOSERVER_NAMESPACE_PREFIX . ":" . $item->id;
			$this->view->layernames .= (($this->view->layernames) ? "," : "") . $layername ;

			$capabilitiesrequest = $serviceaddy . "?request=GetCapabilities" ;

			$client = new Zend_Http_Client($capabilitiesrequest);
			$capabilities = new SimpleXMLElement( $client->request()->getBody() );

			$tmp = $capabilities->xpath("/WMT_MS_Capabilities/Capability//Layer[Name='$layername']/BoundingBox");
			$bb = $tmp[0];
			# we only want to expand the bbox, not replace it
			$this->view->minx = ( $this->view->minx and $this->view->minx < $bb['minx'] ) ?  $this->view->minx : $bb['minx'] ;
			$this->view->maxx = ( $this->view->maxx and $this->view->maxx > $bb['maxx'] ) ?  $this->view->maxx : $bb['maxx'] ;
			$this->view->miny = ( $this->view->miny and $this->view->miny < $bb['miny'] ) ?  $this->view->miny : $bb['miny'] ;
			$this->view->maxy = ( $this->view->maxy and $this->view->maxy > $bb['maxy'] ) ?  $this->view->maxy : $bb['maxy'] ;
			$this->view->srs = $bb['SRS'] ;
		}
		#$tmp = $capabilities->xpath("/WMT_MS_Capabilities/Capability//Layer[Name='$layername']/BoundingBox/@SRS") ;
		#$this->view->srs = $tmp[0] ;
		#$this->view->render('maps/compose.php');

		# now we procure the Proj4js form of the projection to avoid confusion with the webpage trying to do
		# transforms before the projection has been fetched.
		$client->resetParameters();
		$proj4jsurl = NEATLINE_SPATIAL_REFERENCE_SERVICE . "/" . strtr(strtolower($this->view->srs),':','/') ."/proj4js/";
		$client->setUri($proj4jsurl);
		$this->view->proj4js = $client->request()->getBody();

	}

	private function getServiceAddy($item)
	{
		try {
			$serviceaddys = $item->getElementTextsByElementNameAndSetName( 'Service address', 'Item Type Metadata');
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



}