<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Abstract class for map prep. Concrete classes define the _getField() method,
 * which determines the process for getting fields for a map type (item, file).
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

require_once 'GeoserverMap_Item.php';
require_once 'GeoserverMap_File.php';

abstract class GeoserverMap_Abstract
{

    /**
     * Set map, fire prep methods.
     *
     * @param object $map The suffix to use for identifying plugin classes.
     *
     * @return void.
     */
    public function __construct($map)
    {

        $this->map = $map;

        $this->mapTitle = $this->_getMapTitle();
        $this->serviceAddress = $this->_getServiceAddress();
        $this->layerName = $this->_getLayerName('Layername', 'Item Type Metadata');
        $this->dates = $this->_getDates();



    }

    /**
     * Get the title of the map.
     *
     * @return string $title The title.
     */
    protected function _getMapTitle() {

        $title = $this->_getField('Title', 'Dublin Core');

        return $title ? $title :
            get_option('neatlinemaps_geoserver_namespace_prefix') . ':' . $this->map->id;

    }

    /**
     * Get the service address of the server.
     *
     * @return string $address The address.
     */
    protected function _getServiceAddress() {

        $address = $this->_getField('Service Address', 'Item Type Metadata');

        return $address ? $address :
            get_option('neatlinemaps_geoserver_url') . '/wms';

    }

    /**
     * Get the name of the layer.
     *
     * @return $layername The layer name.
     */
    protected function _getLayerName() {

        $layername = $this->_getField('Layer Name', 'Item Type Metadata');

        return $layername ? $layername :
            get_option('neatlinemaps_geoserver_namespace_prefix') . ':' . $this->map->id;

    }

    /**
     * Get dates for the map.
     *
     * @return $dates The parsed dates array.
     */
    protected function _getDates() {

        $coverages = $this->map
            ->getElementTextsByElementNameAndSetName( 'Coverage', 'Dublin Core');

        $dates = null;

        // Ugly. But how to condense?
        if ($coverages) {

            $dates = array();
            foreach ($coverages as $coverage) {

                $text = str_replace(' ', '', $coverage->text);

                if ($this->_parseDate($text) == 'date') {
                    $dates['date'] = $text;
                }

                else if ($this->_parseDate($text) == 'dates') {

                    $dates = explode(';', $text);
                    foreach ($dates as $chunk) {

                        $chunk = explode('=', $chunk);
                        switch ($chunk[0]) {
                            case 'start':
                                $dates['start'] == $chunk[1];
                            break;
                            case 'end':
                                $dates['end'] == $chunk[1];
                            break;
                        }

                    }

                }

            }

        }

        return $dates;

    }

    /**
     * Parse a coverage text element and see if it is a date,
     * a date range, or not a date.
     *
     * @param string $string The text to evaluate.
     *
     * @return string 'date' if the text is a single date;
     * @return string 'dates' if the text is a date range;
     * @return boolean false if the text is not a date.
     */
    protected function _parseDate($string) {

        // Good grief.
        $result = preg_match('/^([\+-]?\d{4}(?!\d{2}\b))
            ((-?)((0[1-9]|1[0-2])(\3([12]\d|0[1-9]|3[01]))
            ?|W([0-4]\d|5[0-2])(-?[1-7])?|(00[1-9]|0[1-9]\d|[12]\d{2}
            |3([0-5]\d|6[1-6])))([T\s]((([01]\d|2[0-3])((:?)[0-5]\d)?|24\:?00)
            ([\.,]\d+(?!:))?)?(\17[0-5]\d([\.,]\d+)?)?([zZ]|([\+-])([01]\d|2[0-3]):
            ?([0-5]\d)?)?)?)?$/', $string) ? 'date' : false;

        $result = preg_match('/^(start|end|[\=\;\-\T\+\d])+$/', $string) ? 'dates' : false;

        return $result;

    }

    /**
     * Fetch fields for the map.
     *
     * @return $field The field.
     */
    abstract function _getField($field, $set);

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
