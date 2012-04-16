<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/** {{{ docblock
 * Concrete class for map with multiple files/layers.
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
 * }}}
 */

class GeoserverMap_WMS extends Map_Abstract
{

    /**
     * Get the service address the map.
     *
     * @return string $title The address.
     */
    public function _getWmsAddress()
    {
        return $this->map->address;
    }

    /**
     * Fetch the XML for the datastream capabilities.
     *
     * @return string $title The address.
     */
    public function _getCapabilitiesXml()
    {

        // Get the capabilities XML, scrub out namespace for xpath query.
        $capabilitiesURL = $this->wmsAddress . '?request=GetCapabilities';
        $client = new Zend_Http_Client($capabilitiesURL, array('timeout' => 30));
        return $client->request()->getBody();

    }

    /**
     * Fetch fields for the map.
     *
     * @return string $title The title.
     */
    public function _getMapTitle()
    {
        return $this->map->getItem()->id;
    }

    /**
     * Build the layers string for the OpenLayers JavaScript invocation.
     *
     * @return string $layers The constructed string.
     */
    public function _getLayers()
    {
        return str_replace(' ', '', $this->map->layers);
    }

    /**
     * Check to see if the layers exist.
     *
     * @return string $layers The constructed string.
     */
    public function _isValid()
    {

        // Query for the layers.
        $capabilities = new SimpleXMLElement($this->capabilitiesXml);
        $capabilities->registerXPathNamespace('gis', 'http://www.opengis.net/wms');
        $layers = $capabilities->xpath('//gis:Layer[@queryable="1"]');

        $layersArray = explode(',', $this->layers);
        $activeLayers = array();

        // Query for names, filter out layers without an Omeka map file.
        foreach ($layers as $layer) {
            if (in_array($layer->Name, $layersArray)) {
                $activeLayers[] = $layer;
            }
        }

        return count($layersArray) == count($activeLayers);


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

        // Query for the layers.
        $capabilities = new SimpleXMLElement($this->capabilitiesXml);
        $capabilities->registerXPathNamespace('gis', 'http://www.opengis.net/wms');
        $layers = $capabilities->xpath('//gis:Layer[@queryable="1"]');

        $layersArray = explode(',', $this->layers);
        $activeLayers = array();

        // Query for names, filter out layers without an Omeka map file.
        foreach ($layers as $layer) {
            if (in_array($layer->Name, $layersArray)) {
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

        // Check for reverse axis order.
        $espgNumber = explode(':', $this->epsg);
        if ($espgNumber[1] >= 4000 && $espgNumber[1] <= 5000) {
            $string = implode(',', array(
                min($minys),
                min($minxes),
                max($maxys),
                max($maxxes)));
        }

        // If not between 4000 and 5000, do normal order.
        else {
            $string = implode(',', array(
                min($minxes),
                min($minys),
                max($maxxes),
                max($maxys)));
        }

        return $string;

    }

    /**
     * Get the projection format.
     *
     * @return string The EPSG.
     */
    public function _getEPSG()
    {

        // Query for the layers.
        $capabilities = new SimpleXMLElement($this->capabilitiesXml);
        $capabilities->registerXPathNamespace('gis', 'http://www.opengis.net/wms');
        $layers = $capabilities->xpath('//gis:Layer[@queryable="1"]');

        return $layers[0]->BoundingBox->attributes()->CRS;

    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

