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

	}

	public function serviceaddyAction()
	{
		$id = (!$id) ? $this->getRequest()->getParam('id') : $id;
		$item = $this->findById($id,"Item");
		$serviceaddy = neatlinemaps_getServiceAddy($item);
		$this->view->serviceaddy = $serviceaddy;

	}
	
	public function layernameAction()
	{
		$id = (!$id) ? $this->getRequest()->getParam('id') : $id;
		$item = $this->findById($id,"Item");
		$serviceaddy = neatlinemaps_getLayerName($item);
		$this->view->layername = $layername;

	}
	


}