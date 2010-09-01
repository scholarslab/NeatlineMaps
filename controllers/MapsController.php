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
		$this->_modelClass = 'Item';
		$this->view->item = $this->findById();
	}

	public function showAction()
	{
		$this->view->params = neatlinemaps_assemble_params_for_map($this->view->item);
	}

    public function showAction()
    {
        $id = (!$id) ? $this->getRequest()->getParam('id') : $id;
        $item = $this->findById($id, "Item");

        $this->view->params = neatlinemaps_assemble_params_for_map($item);
    }
    
	public function serviceaddyAction()
	{
		$this->view->serviceaddy = neatlinemaps_getServiceAddy($this->view->item);
	}
	
	public function layernameAction()
	{
		$this->view->layername = neatlinemaps_getLayerName($this->view->item);
	}

}