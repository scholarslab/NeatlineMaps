<?php

/**
 * Maps Controller for NeatlineMaps
 *
 * @author     "A. Soroka"
 * @version    SVN: $Id$
 * @copyright  2010 A. Soroka
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @package    Omeka
 * @subpackage Neatline
 **/

class NeatlineMaps_MapsController extends Omeka_Controller_Action
{

	public function init()
	{
		$id = (!$id) ? $this->getRequest()->getParam('id') : $id;
		try {
			$thing = $this->findById($id,"Item");
		} catch (Exception $e) {
			try {
				$thing = $this->findById($id,"File");
			}
			catch (Exception $e) {
				debug("Neatline: No such id as: " . $id . "?\nException: " . $e->getMessage());
			}
		}
		$this->view->thing = $thing;
	}

	public function showAction()
	{
		$this->view->params = neatlinemaps_assemble_params_for_map($this->view->thing);
	}

	public function serviceaddyAction()
	{
		$this->view->serviceaddy = neatlinemaps_getServiceAddy($this->view->thing);
	}

	public function layernameAction()
	{
		$this->view->layername = neatlinemaps_getLayerName($this->view->thing);
	}
	
	public function layersAction()
	{
		$capabilitiesrequest = NEATLINE_GEOSERVER . "/wms?request=GetCapabilities" ;
		$client = new Zend_Http_Client($capabilitiesrequest);
		$capabilities = new SimpleXMLElement( $client->request()->getBody() );	
		$layers = $capabilities->xpath("/WMT_MS_Capabilities/Capability//Layer[Name]");
		foreach ($layers as $layer) {
			echo ($layer["Name"] . "\n");
		}
	}

}