<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
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
     * @return void.
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
     * Get all maps associated with an item.
     *
     * @param Omeka_Db_Record $item The item.
     *
     * @return array of Omeka_Db_Record objects The maps.
     */
    public function getMapsByItem($item)
    {

        return $this->findBySql('item_id = ?', array($item->id));

    }

    /**
     * Get all maps associated with an item.
     *
     * @param Omeka_Db_Record $item The item.
     *
     * @return array of Omeka_Db_Record objects The maps.
     */
    public function getMapFilesByItem($item)
    {

        $maps = $this->findBySql('item_id = ?', array($item->id));
        $files = array();

        foreach ($maps as $map) {

            $files[] = $map->getFile();

        }

        return $files;

    }

    /**
     * See whether there is a NeatlineMaps record for a given file.
     *
     * @param Omeka_record $file The file.
     *
     * @return boolean True if there is a NeatlineMaps record associated
     * with the file.
     */
    public function fileHasNeatlineMap($file)
    {

        return (count($this->findBySql('file_id = ?', array($file->id))) > 0);

    }

    /**
     * See whether there is a NeatlineMaps record for a given item.
     *
     * @param Omeka_record $item The item.
     *
     * @return boolean True if there is a NeatlineMaps record associated
     * with the item.
     */
    public function itemHasNeatlineMap($item)
    {

        return (count($this->findBySql('item_id = ?', array($item->id))) > 0);

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
