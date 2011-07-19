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

class GeoserverMap_Item extends GeoserverMap_Abstract
{

    /**
     * Fetch fields for the map.
     *
     * @return string $title The title.
     */
    public function _getMapTitle()
    {

        $name = $this->map->getElementTextsByElementNameAndSetName('Title', 'Dublin Core');
        return $name[0]->text;

    }

    /**
     * Build the layers string for the OpenLayers JavaScript invocation.
     *
     * @return string $layers The constructed string.
     */
    public function _getLayers()
    {

        $layers = array();
        $files = $this->map->getFiles();

        $namespace = $this->map->getElementTextsByElementNameAndSetName('Namespace', 'Item Type Metadata');
        $namespace = $namespace[0]->text;

        foreach ($files as $file) {

            $fileName = explode('.', $file->original_filename);
            $layers[] = $namespace . ':' . $fileName[0];

        }

        return implode(',', $layers);

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
