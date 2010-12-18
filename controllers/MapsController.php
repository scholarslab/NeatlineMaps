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
		$thing = 0;
		$file = $this->findById($id,"File");
		$is_historic_map = false;
		$item = $this->findById($id,"Item");
		if ($item && $item->getItemType() == neatlinemaps_getMapItemType() ) { 
			$is_historic_map = true;
		}
		if ($file && !$is_historic_map) {
			$thing = $this->findById($id,"File");
		}
		else {
			$thing = $this->findById($id,"Item");
		}
		debug("NeatlineMaps: Show Thing from Id is \n" . print_r($thing,false));
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
	
}