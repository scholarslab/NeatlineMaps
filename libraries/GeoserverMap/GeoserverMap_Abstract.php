<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Abstract class to prepare the map for rendering.
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

require_once NEATLINE_MAPS_PLUGIN_DIR . '/libraries/GeoserverMap/GeoserverMap_File.php';
require_once NEATLINE_MAPS_PLUGIN_DIR . '/libraries/GeoserverMap/GeoserverMap_Map.php';

abstract class GeoserverMap_Abstract
{

    /**
     * Get the service address the map.
     *
     * @return string $title The address.
     */
    abstract function _getWmsAddress();

    /**
     * Get the title of the map.
     *
     * @return string $title The title.
     */
    abstract function _getMapTitle();

    /**
     * Get the string for the 'layers' parameter in the OpenLayers initialization.
     *
     * @return string $layers The layers.
     */
    abstract function _getLayers();

    /**
     * Get the parameters for the starting bounding box.
     *
     * @return array $boundngBox The four-part array.
     */
    abstract function _getBoundingBox();

    /**
     * Get the projection.
     *
     * @return string $epsg The EPSG.
     */
    abstract function _getEPSG();

    /**
     * Set map, fire prep methods.
     *
     * @param object $map The suffix to use for identifying plugin classes.
     *
     * @return void.
     */
    public function __construct($map)
    {

        // Set the map.
        $this->map = $map;

        // Build out parameters.
        $this->getParams();

    }

    /**
     * Fire off getter functions, build the params for OpenLayers.
     *
     * @return void.
     */
    public function getParams() {

        // Fire off class methods to get parameters.
        $this->wmsAddress = $this->_getWmsAddress();
        $this->capabilitiesXml = $this->_getCapabilitiesXml();
        $this->mapTitle = $this->_getMapTitle();
        $this->layers = $this->_getLayers();
        $this->boundingBox = $this->_getBoundingBox();
        $this->epsg = $this->_getEPSG();

        // What to do with this?

        // // Get the the proj4js params.
        // $client->resetParameters();
        // $proj4jsURL = get_option('neatlinemaps_geoserver_spatial_reference_service') . '/' .
        //     str_replace(':', '/', strtolower($params['crs'])) . '/proj4js/';
        // $client->setUri($proj4jsURL);
        // $params['proj4js'] = $client->request()->getBody();

    }

    public function display()
    {

        echo __v()->partial('maps/map.php', array(
            'mapTitle' => $this->mapTitle,
            'wmsAddress' => $this->wmsAddress,
            'layers' => $this->layers,
            'boundingBox' => $this->boundingBox,
            'epsg' => $this->epsg
        ));

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
