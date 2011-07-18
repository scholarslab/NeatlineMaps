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

class NeatlineMapTable extends Omeka_Db_Table
{

    /**
     * Inserts a new map.
     *
     * @param Omeka_record $item The parent item.
     * @param Omeka_record $file The parent file.
     *
     * @return void.
     */
    public function addNewMap($item, $file)
    {

        $neatlineMap = new NeatlineMap();
        $neatlineMap->item_id = $item->id;
        $neatlineMap->file_id = $file->id;
        $neatlineMap->save();

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

    /**
     * Get a comma-delimited list of the layer names for the OpenLayers JavaScript.
     *
     * @param Omeka_record $item The item.
     *
     * @return string $list The comma-delimited list.
     */
    public function getCommaDelimitedLayers($item)
    {

        $list = array();
        $neatlineMaps = $this->findBySql('item_id = ?', array($item->id));

        foreach ($neatlineMaps as $map) {

            $list[] = get_option('neatlinemaps_geoserver_namespace_prefix') . ':' . $map->getLayerName();

        }

        return implode(',', $list);

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
