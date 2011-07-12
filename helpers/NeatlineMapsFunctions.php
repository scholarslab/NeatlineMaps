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

    // $db = get_db();
    // $select = $db->getTable('FedoraConnectorDatastream')->select()
    //     ->from(array('d' => $db->prefix . 'fedora_connector_datastreams'))
    //     ->joinLeft(array('s' => $db->prefix . 'fedora_connector_servers'), 'd.server_id = s.id')
    //     ->columns(array('server_name' => 's.name', 'datastream_id' => 'd.id', 'parent_item' =>
    //         "(SELECT text from `$db->ElementText` WHERE record_id = d.item_id AND element_id = 50 LIMIT 1)"))
    //     ->where('d.item_id = ' . $item->id);

    // $datastreams = $db->getTable('FedoraConnectorDatastream')->fetchObjects($select);

    $form = '';

    // if ($maps) {
    //     // show existing map files
    // }

    // $form .= '<table><thead><th>Datastream</th><th>PID</th><th>Server</th><th>Metadata Format</th><th>Actions</th>';
    // foreach ($datastreams as $datastream) {
    //     $form .= '<tr>
    //         <td><strong>' . $datastream->getNode()->getAttribute('label') . '</strong></td>
    //         <td>' . $datastream->pid . '</td>
    //         <td><a href="' . uri('/fedora-connector/servers/edit/' . $datastream->server_id) . '">' . $datastream->server_name . '</a></td>
    //         <td>' . $datastream->metadata_stream . '</td>
    //         <td><a href="' . uri('/fedora-connector/datastreams/' . $datastream->datastream_id . '/import') . '"><strong>Import</strong></a></td>
    //         </tr>';
    // }
    // $form .= '</table>';
    // $form .= '<p><strong><a href="' . uri('/fedora-connector/datastreams/create/item/' . $item->id . '/pid') . '">Add another datastream -></a></strong></p>';

    return $form;

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
