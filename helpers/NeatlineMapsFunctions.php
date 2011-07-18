<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Procedural helper functions.
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

/**
 * Do the Item tab form.
 *
 * @return void.
 */
function _doItemForm($item)
{

    $db = get_db();
    $maps = $db->getTable('NeatlineMap')->getMapsByItem($item);

    ob_start();
    include NEATLINE_MAPS_PLUGIN_DIR . '/forms/neatline-maps-form.php';
    return ob_get_clean();

}

/**
 * Include the GeoServer .js and .css dependencies in the public theme header.
 *
 * @return void.
 */
function _doHeaderJsAndCss()
{

    ?>

    <!-- Neatline Maps Dependencies -->

    <?php
        queue_css('leaflet', null, null, 'javascripts/leaflet/dist');
    ?>

    <script type="text/javascript" src="http://openlayers.org/api/OpenLayers.js"></script>

    <?php
        queue_js('maps/show/show');
    ?>

    <!-- End Neatline Maps Dependencies -->

    <?php

}

/**
 * Include the GeoServer .js and .css dependencies in the public theme header.
 *
 * @return void.
 */
function _doAdminHeaderJsAndCss()
{

    ?>

    <!-- Neatline Maps Dependencies -->

    <link rel="stylesheet" href="<?php echo css('neatline-maps-admin'); ?>" />

    <!-- End Neatline Maps Dependencies -->

    <?php

}

/**
 * Create a new GeoServer namespace.
 *
 * @param Omeka_record $file The file to send.
 *
 * @return boolean True if GeoServer accepts the file.
 */
function _createGeoServerNamespace(
            $geoserver_url,
            $geoserver_namespace_prefix,
            $geoserver_user,
            $geoserver_password,
            $geoserver_namespace_prefix,
            $geoserver_namespace_url)
{

    // Set up curl to dial out to GeoServer.
    $geoServerConfigurationAddress = $geoserver_url . '/rest/namespaces';
    $geoServerNamespaceCheck = $geoServerConfigurationAddress . '/' . $geoserver_namespace_prefix;

    $clientCheckNamespace = new Zend_Http_Client($geoServerNamespaceCheck);
    $clientCheckNamespace->setAuth($geoserver_user, $geoserver_password);

    // Does the namespace already exist?
    if (strpos(
            $clientCheckNamespace->request(Zend_Http_Client::GET)->getBody(),
            'No such namespace:'
    ) !== false) {

        $namespaceJSON = '
            {
                "namespace": {
                    "prefix": "' . $geoserver_namespace_prefix . '",
                    "uri": "' . $geoserver_namespace_url . '"
                }
            }
        ';

        $ch = curl_init($geoServerConfigurationAddress);
        curl_setopt($ch, CURLOPT_POST, True);

        $authString = $geoserver_user . ':' . $geoserver_password;
        curl_setopt($ch, CURLOPT_USERPWD, $authString);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $namespaceJSON);

        $successCode = 201;
        $buffer = curl_exec($ch);

    }

}

/**
 * Post a file to GeoServer and see if it accepts it as a valid geotiff.
 *
 * @param Omeka_record $file The file to send.
 *
 * @return boolean True if GeoServer accepts the file.
 */
function _putFileToGeoServer($file)
{

    // Does GeoServer recognize the file as a map?
    $zip = new ZipArchive();
    $zipFileName = ARCHIVE_DIR . '/' . $file->original_filename . '.zip';
    $zip->open($zipFileName, ZIPARCHIVE::CREATE);
    $zip->addFile(ARCHIVE_DIR . '/files/' . $file->archive_filename, $file->original_filename);
    $zip->close();

    $coverageAddress = get_option('neatlinemaps_geoserver_url') . '/rest/workspaces/' .
        get_option('neatlinemaps_geoserver_namespace_prefix') . '/coveragestores/' . $file->original_filename .
        '/file.geotiff';

    $ch = curl_init($coverageAddress);
    curl_setopt($ch, CURLOPT_PUT, True);

    $authString = get_option('neatlinemaps_geoserver_user') . ':' . get_option('neatlinemaps_geoserver_password');
    curl_setopt($ch, CURLOPT_USERPWD, $authString);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/zip'));
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($zipFileName));
    curl_setopt($ch, CURLOPT_INFILE, fopen($zipFileName, "r"));
    curl_setopt($ch, CURLOPT_PUTFIELDS, $zipFileName);

    $successCode = 201;
    $buffer = curl_exec($ch);
    $info = curl_getinfo($ch);

    return ($info['http_code'] == $successCode);

}
