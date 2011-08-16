<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Table class for NeatlineMapsMapFile.
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

class NeatlineMapsMapFileTable extends Omeka_Db_Table
{

    /**
     * Returns files for the map edit listing.
     *
     * @param string $order The constructed SQL order clause.
     * @param string $page The page.
     *
     * @return object The maps.
     */
    public function getFiles($map_id, $page = null, $order = null)
    {

        $db = get_db();

        // Chaotic query constructs, so as to be able to do column sorting without undue hassle.
        // Is there a better way to do this?

        $select = $this->select()
            ->from(array('m' => $db->prefix . 'neatline_maps_map_files'))
            ->where('m.map_id = ' . $map_id);

        return $this->fetchObjects($select);

    }

    /**
     * Inserts a new map file.
     *
     * @param Omeka_record $map The parent map.
     * @param Omeka_record $file The parent file.
     *
     * @return void.
     */
    public function addNewMapFile($map, $file)
    {

        $neatlineMap = new NeatlineMapsMapFile();
        $neatlineMap->file_id = $file->id;
        $neatlineMap->map_id = $map->id;
        $neatlineMap->save();

    }

    /**
     * Returns the number of files associated with a map.
     *
     * @param integer $map_id The id of the parent map.
     *
     * @return void.
     */
    public function numberOfFilesInMap($map_id)
    {

        $numberOfFiles = (int)count(
            $this->fetchObjects(
                $this->getSelect()->where('map_id = ' . $map_id)
            )
        );

        return $numberOfFiles;

    }

    /**
     * Delete a file.
     *
     * @param integer $id The id of the file to delete;
     * @param boolean $deleteFiles True if the native file records should be deleted.
     * @param boolean $deleteLayers True if the native file records should be deleted.
     *
     * @return void.
     */
    public function deleteFile($id, $deleteFiles, $deleteLayers)
    {

        // Get the file.
        $mapFile = $this->find($id);
        $fileTable = $this->getTable('File');

        // Delete the native file record.
        if ($deleteFiles) {

            $file = $fileTable->find($mapFile->file_id);
            $file->delete();


            // Delete the GeoServer layer.
            if ($deleteLayers) {
                _deleteFileFromGeoserver($file, $map, $server);
            }

        }

        // Delete the NeatlineMapsMapFile record.
        $mapFile->delete();

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
