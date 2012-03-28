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

class NeatlineMapsServerTable extends Omeka_Db_Table
{

    /**
     * Returns maps for the main admin listing.
     *
     * @param string $order The constructed SQL order clause.
     * @param string $page The page.
     *
     * @return object The maps.
     */
    public function getServers($page = null, $order = null)
    {

        $db = get_db();

        $select = $this->getSelect();

        if (isset($page)) {
            $select->limitPage($page, get_option('per_page_admin'));
        }
        if (isset($order)) {
            $select->order($order);
        }

        return $this->fetchObjects($select);

    }

    /**
     * Save server information.
     *
     * @param Omeka_record $server The server record.
     * @param array $data The field data posted from the form.
     *
     * @return boolean True if save succeeds.
     */
    public function saveServer($server, $data)
    {

        $server->name = $data['name'];
        $server->url = $data['url'];
        $server->username = $data['username'];
        $server->password = $data['password'];

        // If there is a trailing slash on the URL, remove it.
        if (substr($server->url, -1) == '/') {
            $server->url = substr($server->url, 0, -1);
        }

        return $server->save() ? true : false;

    }

    /**
     * Create a new server.
     *
     * @param array $data The field data posted from the form.
     *
     * @return boolean True if insert succeeds.
     */
    public function createServer($data)
    {

        $server = new NeatlineMapsServer;
        $server->name = $data['name'];
        $server->url = $data['url'];
        $server->username = $data['username'];
        $server->password = $data['password'];

        // If there is a trailing slash on the URL, remove it.
        if (substr($server->url, -1) == '/') {
            $server->url = substr($server->url, 0, -1);
        }

        return $server->save() ? true : false;

    }

    /**
     * Delete server by id.
     *
     * @param string $id The id of the server to delete.
     *
     * @return boolean True if delete succeeds.
     */
    public function deleteServer($id)
    {

        $server = $this->find($id);
        $server->delete();
        return true;

    }

    /**
     * Checks to see if there is at least one server that is online.
     *
     * @return boolean True if there is an available server.
     */
    public function isAvailableServer()
    {

        $servers = $this->findAll();
        $statuses = array();

        foreach ($servers as $server) {
            $statuses[] = true ? $server->isOnline() : false;
        }

        return in_array(true, $statuses);

    }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

