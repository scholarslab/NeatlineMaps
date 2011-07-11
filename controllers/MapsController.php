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
     * Show the map.
     *
     * @return void
     */
    public function showAction()
    {

        $id = $this->_request->id;

        // Try to fetch a file and item with the id.
        $file = $this->getTable('File')->find($id);
        $item = $this->getTable('Item')->find($id);

        if ($item && $item->getItemType()->name == NEATLINE_MAPS_MAP_ITEM_TYPE_NAME) {
            $this->view->map = $item;
            $geoserverMap = new GeoserverMap_Item($item);
            $this->view->map_params = $geoserverMap->getParams();
        }

        else if ($file) {
            $this->view->map = $file;
            $geoserverMap = new GeoserverMap_File($file);
            $this->view->map_params = $geoserverMap->getParams();
        }

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
