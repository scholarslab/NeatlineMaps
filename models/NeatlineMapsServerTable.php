<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Table class for servers.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class NeatlineMapsServerTable extends Omeka_Db_Table
{

    /**
     * Create a new server.
     *
     * @param array $post The field data posted from the form.
     *
     * @return boolean True if insert succeeds.
     */
    public function updateServer($server, $post)
    {

        // Create server.
        $server->name = $post['name'];
        $server->url = $post['url'];
        $server->namespace = $post['workspace'];
        $server->username = $post['username'];
        $server->password = $post['password'];
        $server->active = $post['active'];

        // If there is a trailing slash on the URL, remove it.
        if (substr($server->url, -1) == '/') {
            $server->url = substr($server->url, 0, -1);
        }

        // Save.
        $server->save();

        return $server;

    }

    /**
     * Get active server.
     *
     * @return Omeka_record $server The active server.
     */
    public function getActiveServer()
    {

        // Try to get a server.
        $server = $this->fetchObject(
            $this->getSelect()->where('active = 1')
        );

        return $server ? $server : false;

    }

}
