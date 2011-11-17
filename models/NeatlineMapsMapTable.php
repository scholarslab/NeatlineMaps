<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/** {{{ docblock
 * Table class for NeatlineMaps.
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
?>

<?php

class NeatlineMapsMapTable extends Omeka_Db_Table
{

    /**
     * Returns maps for the main admin listing.
     *
     * @param string $order The constructed SQL order clause.
     * @param string $page The page.
     *
     * @return object The maps.
     */
    public function getMaps($page = null, $order = null)
    {

        $db = get_db();

        // Chaotic query constructs, so as to be able to do column sorting without undue hassle.
        // Is there a better way to do this?

        $select = $this->select()
            ->from(array('m' => $db->prefix . 'neatline_maps_maps'))
            ->joinLeft(array('i' => $db->prefix . 'items'), 'm.item_id = i.id')
            ->columns(array(
                'map_id' => 'm.id',
                'parent_item' => "(SELECT text from `$db->ElementText` WHERE record_id = m.item_id AND element_id = 50 LIMIT 1)",
                'server' => "(SELECT name from `$db->NeatlineMapsServer` WHERE id = m.server_id)"
            )
        );

        if (isset($page)) {
            $select->limitPage($page, get_option('per_page_admin'));
        }
        if (isset($order)) {
            $select->order($order);
        }

        return $this->fetchObjects($select);

    }

    /**
     * Inserts a new map.
     *
     * @param Omeka_record $item The parent item.
     * @param Omeka_record $server The parent server.
     * @param string $name The name of the map.
     *
     * @return Omeka_record The new map.
     */
    public function addNewMap($item, $server, $name, $namespace)
    {

        $neatlineMap = new NeatlineMapsMap();
        $neatlineMap->item_id = $item->id;
        $neatlineMap->server_id = $server->id;
        $neatlineMap->name = $name;
        $neatlineMap->namespace = $namespace;
        $neatlineMap->save();

        return $neatlineMap;

    }

    /**
     * Get map by the id of one of its files.
     *
     * @param Omeka_record $file The file.
     *
     * @return array of Omeka_records The maps.
     */
    public function getMapByFile($file)
    {

        return $this->find($file->map_id);

    }

    /**
     * Get all maps associated with a given item.
     *
     * @param Omeka_record $item The item.
     *
     * @return void.
     */
    public function getMapsByItem($item)
    {

        $db = get_db();

        // Chaotic query constructs, so as to be able to do column sorting without undue hassle.
        // Is there a better way to do this?

        $select = $this->select()
            ->from(array('m' => $db->prefix . 'neatline_maps_maps'))
            ->where('m.item_id = ' . $item->id)
            ->joinLeft(array('i' => $db->prefix . 'items'), 'm.item_id = i.id')
            ->columns(array(
                'map_id' => 'm.id',
                'parent_item' => "(SELECT text from `$db->ElementText` WHERE record_id = m.item_id AND element_id = 50 LIMIT 1)",
                'server' => "(SELECT name from `$db->NeatlineMapsServer` WHERE id = m.server_id)"
            )
        );

        return $this->fetchObjects($select);

    }

    /**
     * Get all maps associated with a given item, without the column adds that are necessary for the admin screens.
     *
     * @param Omeka_record $item The item.
     *
     * @return void.
     */
    public function getMapsByItemForPublicDisplay($item)
    {

        $db = get_db();

        // Chaotic query constructs, so as to be able to do column sorting without undue hassle.
        // Is there a better way to do this?

        $select = $this->select()
            ->from(array('m' => $db->prefix . 'neatline_maps_maps'))
            ->where('m.item_id = ' . $item->id);

        return $this->fetchObjects($select);

    }


    /**
     * Delete a map and all of its component file records.
     *
     * @param integer $id The id of the map to delete;
     * @param boolean $deleteFiles True if the native file records should be deleted.
     * @param boolean $deleteLayers True if the native file records should be deleted.
     *
     * @return void.
     */
    public function deleteMap($id, $deleteFiles, $deleteLayers)
    {

        // Delete the map.
        $map = $this->find($id);
        $server = $map->getServer();

        // Delete the file records.
        $mapFiles = $this->getTable('NeatlineMapsMapFile')->fetchObjects(
            $this->getTable('NeatlineMapsMapFile')->getSelect()->where('map_id = ' . $id)
        );

        $fileTable = $this->getTable('File');

        foreach($mapFiles as $mapFile) {

            // Delete the native file record.
            if ($deleteFiles) {

                if($file = $fileTable->find($mapFile->file_id)) {
                    $file->delete();
                }

                // Delete the GeoServer layer.
                if ($deleteLayers) {
                    _deleteFileFromGeoserver($file, $map, $server);
                }

            }

            // Delete the NeatlineMapsMapFile record.
            $mapFile->delete();

        }

        $map->delete();

    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

