<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/** {{{docblock
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
 * @author      Adam Soroka <ajs6f@virginia.edu>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @author      Wayne Graham <wayne.graham@virginia.edu>
 * @author      Jeremy Boggs <jkb2b@virginia.edu>
 * @copyright   2010 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 * @category    plugins
 * @package     Omeka
 * @subpackage  NeatlineMaps
 * @link        https://github.com/scholarslab/NeatlineMaps/
 *}}} 
 */

if (!defined('NEATLINE_MAPS_PLUGIN_VERSION')) {
    define('NEATLINE_MAPS_PLUGIN_VERSION', get_plugin_ini('NeatlineMaps', 'version'));
}

if (!defined('NEATLINE_MAPS_PLUGIN_DIR')) {
    define('NEATLINE_MAPS_PLUGIN_DIR', dirname(__FILE__));
}

require_once 'NeatlineMapsPlugin.php';
require_once NEATLINE_MAPS_PLUGIN_DIR . '/helpers/NeatlineMapsFunctions.php';
require_once NEATLINE_MAPS_PLUGIN_DIR . '/forms/ServerForm.php';
require_once NEATLINE_MAPS_PLUGIN_DIR . '/forms/Validate/isUrl.php';
require_once 'Zend/Http/Client/Adapter/Curl.php';
require_once NEATLINE_MAPS_PLUGIN_DIR . '/libraries/GeoserverMap/GeoserverMap_Abstract.php';

new NeatlineMapsPlugin;

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

