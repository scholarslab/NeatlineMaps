<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Server row class.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class NeatlineMapsServer extends Omeka_record
{

    /**
     * The name of the server [string].
     */
    public $name;

    /**
     * The Geoserver URL [string].
     */
    public $url;

    /**
     * The Geoserver username [string].
     */
    public $username;

    /**
     * The Geoserver password [string].
     */
    public $password;

    /**
     * The Geoserver namespace [string].
     */
    public $namespace;

    /**
     * Whether the server is active [0/1].
     */
    public $active;


    /**
     * Checks to see if the server is online.
     *
     * @return boolean True if the server is online.
     */
    public function isOnline()
    {

        $ch = curl_init($this->url . '/rest/workspaces');

        $authString = $this->username . ':' . $this->password;
        curl_setopt($ch, CURLOPT_USERPWD, $authString);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $successCode = 200;
        $buffer = curl_exec($ch);
        $info = curl_getinfo($ch);

        return ($info['http_code'] == $successCode);

    }

    /**
     * Construct the WMS address for the server.
     *
     * @return boolean True if the server is online.
     */
    public function getWmsAddress()
    {
        return $this->url . '/' . $this->namespace . '/wms';
    }

    /**
     * Manage unique `active` field on save.
     *
     * @return void.
     */
    public function save()
    {

        // Get the current active server.
        $serversTable = $this->getTable('NeatlineMapsServer');
        $active = $serversTable->getActiveServer();

        // Is active set to true?
        if ($this->active == 1) {

            // Is the current active non-self?
            if ($active && $active->id !== $this->id) {

                // Switch active server.
                $active->active = 0;
                $active->parentSave();

            }

        }

        // If there is no current active server, set self active.
        else if (!$active) {
            $this->active = 1;
        }

        // Call parent.
        parent::save();

    }

    /**
     * Raw save.
     *
     * @return void.
     */
    public function parentSave()
    {
        parent::save();
    }

}
