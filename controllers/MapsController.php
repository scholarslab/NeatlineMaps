<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Maps controller.
 *
 * PHP version 5
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package     omeka
 * @subpackage  neatlinemaps
 * @author      Scholars' Lab <>
 * @author      Bethany Nowviskie <bethany@virginia.edu>
 * @author      Adam Soroka <ajs6f@virginia.edu>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2010 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 * @version     $Id$
 */
?>

<?php

class NeatlineMaps_MapsController extends Omeka_Controller_Action
{

    /**
     * Get params, figure out item type.
     *
     * @return void
     */
    public function init()
    {

        $id = $this->_request->id;

        // Get the Historial Map item type.
        $mapItemType = $this->getTable('ItemType')
            ->findBySql('name = ?', array(NEATLINE_MAPS_MAP_ITEM_TYPE_NAME));

        // Try to fetch a file and item with the id.
        $file = $this->getTable('File')->find($id);
        $item = $this->getTable('Item')->find($id);

        $isMap = false;

        if ($item && $item->getItemType() == $mapItemType->name) {
            $this->view->map = $item;
        }

        else if ($file) {
            $this->view->map = $file;
        }

    }

    /**
     * Show the map.
     *
     * @return void
     */
    public function showAction()
    {



    }

}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
