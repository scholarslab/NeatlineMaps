<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Helpers.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */


/**
 * Since item() is broken.
 *
 * @param Omeka_record $item The item to work on.
 * @param string $elementSet The element set.
 * @param string $elementName The element name.
 *
 * @return string $text The element text content.
 */
function nlwms_getItemMetadata($item, $elementSet, $elementName)
{

    // Get the database and set the default value.
    $_db = get_db();
    $text = '';

    // Get tables.
    $elementTable = $_db->getTable('Element');
    $elementTextTable = $_db->getTable('ElementText');
    $recordTypeTable = $_db->getTable('RecordType');

    // Fetch the element record for the field.
    $element = $elementTable->findByElementSetNameAndElementName(
        $elementSet,
        $elementName
    );

    // Get the record type for Item.
    $itemTypeId = $recordTypeTable->findIdFromName('Item');

    // Try to find a text.
    $existingTexts = $elementTextTable->fetchObjects(

        $elementTextTable->getSelect()
            ->where('record_id=?',      $item->id)
            ->where('record_type_id=?', $itemTypeId)
            ->where('element_id=?',     $element->id)

    );

    if ($existingTexts != null) {
        $text = $existingTexts[0]->text;
    }

    return $text;

}

/**
 * Render the map partial.
 *
 * @param Omeka_record $item The parent item.
 *
 * @return string $text The element text content.
 */
function nlwms_renderMap($item)
{

    // Get table.
    $_db = get_db();
    $_servicesTable = $_db->getTable('NeatlineMapsService');

    // Try to get the service.
    $service = $_servicesTable->findByItem($item);

    // If a service exists, render it.
    if ($service) {

        try {

            // Create the renderer.
            $map = new GeoserverMap_WMS($service);

            if ($map->_isValid()) {

              return __v()->partial('show.php', array(
                  'mapTitle' => $map->mapTitle,
                  'wmsAddress' => $map->wmsAddress,
                  'layers' => $map->layers,
                  'boundingBox' => $map->boundingBox,
                  'epsg' => $map->epsg
              ));

            }

        } catch (Exception $e) {};

    }

}

/**
 * Construct the Geoserver layer name given a server and file.
 *
 * @param Omeka_record $server The server.
 * @param Omeka_record $file The file.
 *
 * @return string $layer The name of the Geoserver layer.
 */
function nlwms_layerName($server, $file)
{
    $layer = explode('.', $file->original_filename);
    return $layer[0];
}

/**
 * Post a file to GeoServer and see if it accepts it as a valid geotiff.
 *
 * @param Omeka_record $file The file to send.
 * @param Omeka_record $server The server to use.
 *
 * @return boolean True if GeoServer accepts the file.
 */
function _putFileToGeoserver($file, $server)
{

    // Does GeoServer recognize the file as a map?
    $zip = new ZipArchive();
    $zipFileName = ARCHIVE_DIR . '/' . $file->original_filename . '.zip';
    $zip->open($zipFileName, ZIPARCHIVE::CREATE);
    $zip->addFile(ARCHIVE_DIR . '/files/' . $file->archive_filename, $file->original_filename);
    $zip->close();

    // Get store name.
    $store = explode('.', $file->original_filename);

    // Construct coverage address.
    $coverageAddress = $server->url . '/rest/workspaces/' .
        $server->namespace . '/coveragestores/' . $store[0] .
        '/file.geotiff';

    $ch = curl_init($coverageAddress);
    curl_setopt($ch, CURLOPT_PUT, True);

    $authString = $server->username . ':' . $server->password;
    curl_setopt($ch, CURLOPT_USERPWD, $authString);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/zip'));
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($zipFileName));
    curl_setopt($ch, CURLOPT_INFILE, fopen($zipFileName, "r"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $zipFileName);

    $buffer = curl_exec($ch);
    $info = curl_getinfo($ch);

    return $info['http_code'] == 201;

}
