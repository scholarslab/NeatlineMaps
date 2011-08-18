<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Ignition file. Just instantiates the NeatlineMaps class, which does all work.
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

define('NEATLINE_MAPS_PLUGIN_VERSION', get_plugin_ini('NeatlineMaps', 'version'));
define('NEATLINE_MAPS_PLUGIN_DIR', dirname(__FILE__));
define('NEATLINE_MAPS_MAP_ITEM_TYPE_NAME', 'Historical Map');
define('NEATLINE_MAPS_NAMESPACE_FIELD_NAME', 'Namespace');
define('NEATLINE_MAPS_NAMESPACE_URL_FIELD_NAME', 'Namespace URL');

require_once 'NeatlineMapsPlugin.php';
require_once NEATLINE_MAPS_PLUGIN_DIR . '/helpers/NeatlineMapsFunctions.php';
require_once NEATLINE_MAPS_PLUGIN_DIR . '/libraries/Curl/Curl.php';
require_once NEATLINE_MAPS_PLUGIN_DIR . '/libraries/GeoserverMap/GeoserverMap_Abstract.php';

new NeatlineMapsPlugin;
