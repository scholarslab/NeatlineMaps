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

class NeatlineMapsMap extends Omeka_record
{

    public $server_id;
    public $item_id;
    public $name;
    public $namespace;

    /**
     * Get the map's parent server.
     *
     * @return Omeka_record The server object.
     */
    public function getServer()
    {

        return $this->getTable('NeatlineMapsServer')->find($this->server_id);

    }

    /**
     * Get the map's parent item.
     *
     * @return Omeka_record The server object.
     */
    public function getItem()
    {

        return $this->getTable('Item')->find($this->item_id);

    }

    /**
     * Get the namespace url.
     *
     * @return string The url.
     */
    public function getNamespaceUrl()
    {

        return $this->getServer()->url . '/rest/namespaces/' . $this->namespace;

    }

    /**
     * Get the native Omeka files that are associated with the map.
     *
     * @return array of Omeka_record The files.
     */
    public function getOmekaFiles()
    {

        $mapFiles = $this->getTable('NeatlineMapsMapFile')->fetchObjects(
            $this->getTable('NeatlineMapsMapFile')->getSelect()->where('map_id = ' . $this->id)
        );

        $files = array();
        $filesTable = $this->getTable('File');

        foreach($mapFiles as $mapFile) {
            $files[] = $filesTable->find($mapFile->file_id);
        }

        return $files;

    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

