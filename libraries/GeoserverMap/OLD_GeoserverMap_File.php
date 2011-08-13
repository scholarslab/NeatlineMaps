<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Concrete class for maps of Item type.
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

class GeoserverMap_File extends GeoserverMap_Abstract
{

    /**
     * Get the service address the map.
     *
     * @return string $title The address.
     */
    public function _getWmsAddress() {

        return _getWmsAddress($this->getItem());

    }

    /**
     * Fetch fields for the map.
     *
     * @return string $title The title.
     */
    public function _getMapTitle()
    {

        $fileName = explode('.', $file->original_filename);
        return $fileName[0];

    }

    /**
     * Build the layers string for the OpenLayers JavaScript invocation.
     *
     * @return string $layers The constructed string.
     */
    public function _getLayers()
    {

        $fileName = explode('.', $file->original_filename);
        return $namespace . ':' . $fileName[0];

    }

    /**
     * Calculate a bounding box based on the individual bounding boxes for each of the layers
     * that will show all layers at once. This is more difficult than just choosing the box for
     * the first layer, but it makes it possible to puts dozens of layers on a map without having
     * to worry about viewport chaos.
     *
     * @return array $boundngBox The constructed string, formatted according to the requirements
     * of the OpenLayers.Bounds constructor.
     */
    public function _getBoundingBox()
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
