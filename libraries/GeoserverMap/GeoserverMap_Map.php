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

class GeoserverMap_Map extends GeoserverMap_Abstract
{

    /**
     * Get the service address the map.
     *
     * @return string $title The address.
     */
    public function _getWmsAddress() {

        $server = $this->map->getServer();
        return $server->url . '/' . $map->namespace . '/wms';

    }

    /**
     * Fetch fields for the map.
     *
     * @return string $title The title.
     */
    public function _getMapTitle()
    {

        return $this->map->name;

    }

    /**
     * Build the layers string for the OpenLayers JavaScript invocation.
     *
     * @return string $layers The constructed string.
     */
    public function _getLayers()
    {

        $layers = array();
        $files = $this->map->getOmekaFiles();
        $namespace = $this->map->namespace;

        foreach ($files as $file) {

            $fileName = explode('.', $file->original_filename);
            $layers[] = $namespace . ':' . $fileName[0];

        }

        return implode(',', $layers);

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

        // Get the capabilities XML, scrub out namespace for xpath query.
        $capabilitiesURL = $this->wmsAddress . '?request=GetCapabilities';
        $client = new Zend_Http_Client($capabilitiesURL);
        $body = str_replace('xmlns', 'ns', $client->request()->getBody());

        // Query for the layers.
        $capabilities = new SimpleXMLElement($body);
        $layers = $capabilities->xpath('//*[local-name()="Layer"][@queryable=1]');

        $layersArray = array();
        $activeLayers = array();

        // Get the maps/files associated with the item, pull out the base filenames.
        $files = $this->map->getOmekaFiles($this->map);
        foreach ($files as $file) {
            $fileName = explode('.', $file->original_filename);
            $layersArray[] = $fileName[0];
        }

        // Query for names, filter out layers without an Omeka map file.
        foreach ($layers as $layer) {
            if (in_array($layer->Title, $layersArray)) {
                $activeLayers[] = $layer;
            }
        }

        $minxes = array();
        $minys = array();
        $maxxes = array();
        $maxys = array();

        foreach ($activeLayers as $layer) {

            $minxes[] = (float) $layer->BoundingBox->attributes()->minx;
            $minys[] = (float) $layer->BoundingBox->attributes()->miny;
            $maxxes[] = (float) $layer->BoundingBox->attributes()->maxx;
            $maxys[] = (float) $layer->BoundingBox->attributes()->maxy;

        }

        return implode(',', array(
            min($minxes),
            min($minys),
            max($maxxes),
            max($maxys)));

    }

    /**
     * Get the projection format.
     *
     * @return string The EPSG.
     */
    public function _getEPSG()
    {

        // Get the capabilities XML, scrub out namespace for xpath query.
        $capabilitiesURL = $this->wmsAddress . '?request=GetCapabilities';
        $client = new Zend_Http_Client($capabilitiesURL);
        $body = str_replace('xmlns', 'ns', $client->request()->getBody());

        // Query for the layers.
        $capabilities = new SimpleXMLElement($body);
        $layers = $capabilities->xpath('//*[local-name()="Layer"][@queryable=1]');

        $layersArray = array();

        $files = $this->map->getOmekaFiles();
        $fileName = explode('.', $files[0]->original_filename);

        // What if different files on the map have different projections??

        // Query for names, filter out layers without an Omeka map file.
        foreach ($layers as $layer) {
            if ($layer->Title == $fileName[0]) {
                $activeLayer = $layer;
            }
        }

        return $activeLayer->BoundingBox->attributes()->CRS;

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
