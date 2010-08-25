<?php

/**
 * Maps Controller for NeatlineMaps
 *
 * @author     "Scholars Lab"
 * @version    SVN: $Id$
 * @copyright  2010 The Board and Visitors of the University of Virginia
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @link       http://github.com/scholarslab/bagit
 * @package    Omeka
 * @subpackage Neatline
 **/

class NeatlineMaps_MapsController extends Omeka_Controller_Action
{
    public function init()
    {
        $writer = new Zend_Log_Writer_Stream(LOGS_DIR . DIRECTORY_SEPARATOR .
                "neatline.log");
        $this->logger = new Zend_Log($writer);
    }

    public function showAction()
    {
        $id = (!$id) ? $this->getRequest()->getParam('id') : $id;
        $item = $this->findById($id, "Item");

        $this->view->params = neatlinemaps_assemble_params_for_map($item);
    }

    public function serviceaddyAction()
    {
        $id = (!$id) ? $this->getRequest()->getParam('id') : $id;
        $item = $this->findById($id, "Item");
        $this->view->serviceaddy = neatlinemaps_getServiceAddy($item);
    }

    public function layernameAction()
    {
        $id = (!$id) ? $this->getRequest()->getParam('id') : $id;
        $item = $this->findById($id, "Item");
        $this->view->layername = neatlinemaps_getLayerName($item);
    }

}