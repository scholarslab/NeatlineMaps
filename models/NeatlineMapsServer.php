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
        return ($this->_getHttpCode() == 200);
    }

    /**
     * Tries to contact the GeoServer and authenticate and returns the HTTP 
     * status code.
     *
     * @return int $code The HTTP status code.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    protected function _getHttpCode()
    {
        $ch     = curl_init($this->url . '/rest/workspaces');

        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/xml'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        $buffer = curl_exec($ch);
        $info   = curl_getinfo($ch);
        $code   = $info['http_code'];

        return $code;
    }

    /**
     * This returns a string indicating the server's status and a CSS class for 
     * the wrapping element.
     *
     * Currently, this is one of these values:
     *
     * * 'Online': Everything's just great;
     * * 'Offline': I can't even talk to the server; or
     * * 'Authentication Error': The server doesn't recognize me or my
     *   password.
     *
     * Actually, it returns those values after they've been translated for the 
     * viewer's current locale.
     *
     * @return array An array with two keys: 'class' for the class for the span 
     * and 'message' for the translated string.
     * @author Eric Rochester <erochest@virginia.edu>
     **/
    public function getStatusDisplay()
    {
        $online  = __('Online');
        $offline = __('Offline');
        $authErr = __('Authentication Error');

        switch ($this->_getHttpCode()) {
        case 200:
            $status = $online;
            $class  = 'online';
            break;

        case 401:
        case 403:
            $status = $authErr;
            $class  = 'offline';
            break;

        default:
            $status = $offline;
            $class  = 'offline';
            break;
        }

        return array(
            'class'   => $class,
            'message' => $status
        );
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
