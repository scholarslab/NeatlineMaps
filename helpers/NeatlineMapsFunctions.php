<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/** {{{ docblock
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
 * }}}
 */

/**
 * Do the Item tab form.
 *
 * @return void.
 */
function _doItemForm($item)
{

    $db = get_db();
    $maps = $db->getTable('NeatlineMapsMap')->getMapsByItem($item);
    $filesTable = $db->getTable('NeatlineMapsMapFile');

    ob_start();
    include NEATLINE_MAPS_PLUGIN_DIR . '/forms/neatline-maps-form.php';
    return ob_get_clean();

}

/**
 * Returns a size value as a byte integer in the format 8M, 2G, or 32k
 *
 * @param integer $val Value to convert
 * 
 * @return integer $val in bytes
 */
function return_bytes($val) 
{
    $val = trim($val);
    $last = strtolower($val[strlen($val) - 1]);
    switch($last) {
    case 'g':
      $val *= 1024;
    case 'm':
      $val *= 1024;
    case 'k':
      $val *= 1024;
    }
    return $val;
}

/**
 * Byte decorator to display in MB
 * 
 * @param integer $val Bytes to format in MB
 *
 * @return bytes formatted in Megabytes
 */
function return_mb($val) {
  return round(($val / 1048576), 2) . "MB";
}

/**
 * Include the OpenLayers.js library.
 *
 * @return void.
 */
function _queueOpenLayers()
{

    ?>

    <!-- Neatline Maps Dependencies -->
    <script type="text/javascript" src="http://openlayers.org/api/OpenLayers.js"></script>
    <!-- End Neatline Maps Dependencies -->

    <?php

}

/**
 * Include the neatline-admin.css stylesheet.
 *
 * @return void.
 */
function _queueAdminCss()
{

    queue_css('neatline-maps-admin');

}

/**
 * Create a new GeoServer namespace.
 *
 * @param string $geoserver_url The location of the GeoServer.
 * @param string $geoserver_namespace_prefix The name of the namespace.
 * @param string $geoserver_user The admin username.
 * @param string $geoserver_password The admin password.
 * @param string $geoserver_namespace_url The URL attached to the namespace.
 *
 * @return boolean True if GeoServer accepts the file.
 */
function _createGeoServerWorkspace(
            $geoserver_url,
            $geoserver_workspace_prefix,
            $geoserver_user,
            $geoserver_password,
            $geoserver_workspace_url)
{

    // Set up curl to dial out to GeoServer.
    $geoServerConfigurationAddress = $geoserver_url . '/rest/workspaces';
    $geoServerNamespaceCheck = $geoServerConfigurationAddress . '/' . $geoserver_workspace_prefix;

    $clientCheckNamespace = new Zend_Http_Client($geoServerNamespaceCheck);
    $clientCheckNamespace->setAuth($geoserver_user, $geoserver_password);

    // Does the namespace already exist?
    if (strpos(
            $clientCheckNamespace->request(Zend_Http_Client::GET)->getBody(),
            'No such workspace:'
    ) !== false) {

        $namespaceJSON = '
            {
                "workspace": {
                    "name": "' . $geoserver_workspace_prefix . '"
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
 * @param Omeka_record $server The server to use.
 * @param string $namespace The namespace to add the file to.
 *
 * @return boolean True if GeoServer accepts the file.
 */
function _putFileToGeoServer($file, $server, $workspace)
{

    // Does GeoServer recognize the file as a map?
    $zip = new ZipArchive();
    $zipFileName = ARCHIVE_DIR . '/' . $file->original_filename . '.zip';
    $zip->open($zipFileName, ZIPARCHIVE::CREATE);
    $zip->addFile(ARCHIVE_DIR . '/files/' . $file->archive_filename, $file->original_filename);
    $zip->close();

    $coverageAddress = $server->url . '/rest/workspaces/' .
        $workspace . '/coveragestores/' . $file->original_filename .
        '/file.geotiff';

    $ch = curl_init($coverageAddress);
    curl_setopt($ch, CURLOPT_PUT, True);

    $authString = $server->username . ':' . $server->password;
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

/**
 * Deletes a coveragestore from GeoServer.
 *
 * @param Omeka_record $file The file corresponding to the coveragestore.
 * @param Omeka_record $map The parent map.
 * @param Omeka_record $server The parent server.
 *
 * @return boolean True if GeoServer accepts the file.
 */

// DOES NOT WORK.

// function _deleteFileFromGeoserver($file, $map, $server)
// {

//     $coverageAddress = $server->url . '/rest/workspaces/' .
//         $namespace . '/coveragestores/' . $file->original_filename .
//         '/file.geotiff';

//     $ch = curl_init($coverageAddress);
//     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

//     $authString = $server->username . ':' . $server->password;
//     curl_setopt($ch, CURLOPT_USERPWD, $authString);

//     $successCode = 405;
//     $buffer = curl_exec($ch);
//     $info = curl_getinfo($ch);

//     return ($info['http_code'] == $successCode);

// }

/**
 * A homebrew colum sorter, implemented so as to keep more control
 * over how the record loop is handled in the view.
 *
 * @param object $request The incoming request dispatched by the 
 * front controller.
 *
 * @return string $order The sorting parameter for the query.
 */
function _doColumnSortProcessing($sort_field, $sort_dir)
{

    if (isset($sort_dir)) {
        $sort_dir = ($sort_dir == 'a') ? 'ASC' : 'DESC';
    }

    return ($sort_field != '') ? trim(implode(' ', array($sort_field, $sort_dir))) : '';

}

/**
 * Retrieves items to populate the listings in the itemselect view.
 *
 * @param string $page The page to fetch.
 * @param string $order The constructed SQL order clause.
 * @param string $search The string to search for.
 *
 * @return array $items The items.
 */
function _getItems($page = null, $order = null, $search = null)
{

    $db = get_db();
    $itemTable = $db->getTable('Item');

    // Wretched query. Fallback from weird issue with left join where item id was
    // getting overwritten. Fix.
    $select = $db->select()
        ->from(array('item' => $db->prefix . 'items'))
        ->columns(array('item_id' => 'item.id', 
            'Type' =>
            "(SELECT name from `$db->ItemType` WHERE id = item.item_type_id)",
            'item_name' =>
            "(SELECT text from `$db->ElementText` WHERE record_id = item.id AND element_id = 50 LIMIT 1)",
            'creator' =>
            "(SELECT text from `$db->ElementText` WHERE record_id = item.id AND element_id = 39)"
            ));

    if (isset($page)) {
        $select->limitPage($page, get_option('per_page_admin'));
    }
    if (isset($order)) {
        $select->order($order);
    }
    if (isset($search)) {
        $select->where("(SELECT text from `$db->ElementText` WHERE record_id = item.id AND element_id = 50 LIMIT 1) like '%" . $search . "%'");
    }

    return $itemTable->fetchObjects($select);

}

/**
 * Retrieves a single item with added columns with name, etc.
 *
 * @param $id The id of the item.
 *
 * @return object $item The item.
 */
function _getSingleItem($id)
{

    $db = get_db();
    $itemTable = $db->getTable('Item');

    $select = $db->select()
        ->from(array('item' => $db->prefix . 'items'))
        ->columns(array('item_id' => 'item.id', 
            'Type' =>
            "(SELECT name from `$db->ItemType` WHERE id = item.item_type_id)",
            'item_name' =>
            "(SELECT text from `$db->ElementText` WHERE record_id = item.id AND element_id = 50 LIMIT 1)",
            'creator' =>
            "(SELECT text from `$db->ElementText` WHERE record_id = item.id AND element_id = 39)"
            ))
        ->where('item.id = ' . $id);

    return $itemTable->fetchObject($select);

}

/**
 * Truncate a string to a given length and add '...'.
 *
 * @param string $string The string to be truncated.
 * @param int $length The length to truncate to.
 *
 * @return string The truncated and formatted string.
 */
function _previewString($string, $length)
{

    if (strlen($string) > $length) {
        return substr($string, 0, $length) . '...';
    }

    else {
        return $string;
    }

}

/**
 * Format item add date for map create workflow.
 *
 * @param string $date The date in datetime.
 *
 * @return string $date The formatted date.
 */
function _formatDate($date)
{

    $date = new DateTime($date);
    return '<strong>' . $date->format('F j, Y') . '</strong> at ' .
       $date->format('g:i a');

}

/**
 * Displays a specific NeatlineMap.
 *
 * @param NeatlineMapsMap
 * @return string HTML
 */
function neatline_maps_display_map(NeatlineMapsMap $map)
{
    $html = '';

    if ($map) {
        $map = new GeoserverMap_Map($map);

        $html = __v()->partial('maps/map.php', array(
            'mapTitle' => $map->mapTitle,
            'wmsAddress' => $map->wmsAddress,
            'layers' => $map->layers,
            'boundingBox' => $map->boundingBox,
            'epsg' => $map->epsg
        ));
    }

    return $html;
}

/**
 * Display all the maps for a given Item
 *
 * @param Item
 * @return string HTML
 */
function neatline_maps_display_maps_for_item($item = null)
{
    if (!$item) {
        $item = get_current_item();
    }

    $html = '';

    $maps = get_db()->getTable('NeatlineMapsMap')->getMapsByItemForPublicDisplay($item);
    
    if ($maps) {
        foreach ($maps as $map) {
            $html = neatline_maps_display_map($map);
        }
    }

    return $html;
}

/**
 * Displays a specific NeatlineMapsMapFile.
 *
 * @param NeatlineMapsMap
 * @return string HTML
 */
function neatline_maps_display_map_file(NeatlineMapsMapFile $mapFile)
{
    $html = '';

    if ($mapFile) {
        $mapFile = new GeoserverMap_File($mapFile);

        $html = __v()->partial('maps/map.php', array(
            'mapTitle' => $mapFile->mapTitle,
            'wmsAddress' => $mapFile->wmsAddress,
            'layers' => $mapFile->layers,
            'boundingBox' => $mapFile->boundingBox,
            'epsg' => $mapFile->epsg
        ));
    }

    return $html;
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

