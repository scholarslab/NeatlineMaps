<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Record class for NeatlineMaps Server.
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

class NeatlineMapsServer extends Omeka_record
{

    public $name;
    public $url;
    public $username;
    public $password;

    /**
     * Checks to see if the server is online.
     *
     * @return boolean True if the server is online.
     */
    public function isOnline()
    {

        $ch = curl_init($this->url . '/rest/workspaces');
        // curl_setopt($ch, CURLOPT_GET, True);

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
     * Get namespaces for the server from GeoServer.
     *
     * @return array The namespaces.
     */
    public function getWorkspaceNames()
    {

        // Set up curl to dial out to GeoServer.
        $workspacesAddress = $this->url . '/rest/workspaces.xml';

        $workspaceList = new Zend_Http_Client($workspacesAddress);
        $workspaceList->setAuth($this->username, $this->password);
        $responseBody = str_replace('xmlns', 'ns', $workspaceList->request(Zend_Http_Client::GET)->getBody());

        // Query for the namespaces.
        $body = new SimpleXMLElement($responseBody);
        $workspaces = $body->xpath('//*[local-name()="workspace"]/*[local-name()="name"]');

        // Sort alphabetically.
        usort($workspaces, array('NeatlineMapsServer', '_compareWorkspaceNames'));

        return $workspaces;

    }

    /**
     * Compare workspace names to see which comes first alphabetically. Used by the sorting step
     * in getWorkspaceNames().
     *
     * @param xml nodes $workspaces The workspaces.
     *
     * @return xml nodes The sorted workspaces.
     */
    static function _compareWorkspaceNames($workspace1, $workspace2) {

        return strcmp($workspace1, $workspace2);

    }

    /**
     * Check to see if the server has child maps.
     *
     * @return boolean True if there are child maps.
     */
    public function hasChildMaps()
    {

        $childMaps = $this->getTable('NeatlineMapsMap')->fetchObjects(
            $this->getTable('NeatlineMapsMap')->getSelect()->where('server_id = ' . $this->id)
        );

        return (bool) $childMaps;

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
