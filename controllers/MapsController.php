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

	private function getWKTs($item) {

	}

}