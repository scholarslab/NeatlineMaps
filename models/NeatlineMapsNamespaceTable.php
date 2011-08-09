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

class NeatlineMapsNamespaceTable extends Omeka_Db_Table
{

    /**
     * Returns maps for the main admin listing.
     *
     * @param string $order The constructed SQL order clause.
     * @param string $page The page.
     *
     * @return object The maps.
     */
    public function getNamespaces($page = null, $order = null)
    {

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
     * Create a new namespace in GeoServer and Omeka.
     *
     * @param array $data The field data posted from the form.
     *
     * @return string - 'added' if the namespace did not already exist
     * and was added, 'registered' if the namespace exists.
     */
    public function createNamespace($data)
    {

        $server = $this->getTable('NeatlineMapsServer')->find($data['server']);

        // Set up curl to dial out to GeoServer.
        $geoServerConfigurationAddress = $server->url . '/rest/namespaces';
        $geoServerNamespaceCheck = $geoServerConfigurationAddress . '/' . $data['name'];

        $clientCheckNamespace = new Zend_Http_Client($geoServerNamespaceCheck);
        $clientCheckNamespace->setAuth($server->username, $server->password);

        $responseBody = $clientCheckNamespace->request(Zend_Http_Client::GET)->getBody();

        // Does the namespace not exist and need to be added?
        if (strpos($responseBody, 'No such namespace:') !== false) {

            $namespaceJSON = '
                {
                    "namespace": {
                        "prefix": "' . $data['name'] . '",
                        "uri": "' . $data['url'] . '"
                    }
                }
            ';

            $ch = curl_init($geoServerConfigurationAddress);
            curl_setopt($ch, CURLOPT_POST, True);

            $authString = $server->username . ':' . $server->password;
            curl_setopt($ch, CURLOPT_USERPWD, $authString);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $namespaceJSON);

            $successCode = 201;
            $buffer = curl_exec($ch);

            $namespaceStatus = 'added';

        }

        // If the namespace already exists, does it need to be updated?
        else {

            // Query for the existing uri.
            $body = new SimpleXMLElement($responseBody);
            $uri = $body->xpath('//*[local-name()="uri"]');

            // Does the existing URI match the posted one?
            if ($uri[0]->nodeValue == $data['url']) {

                $namespaceStatus = 'registered';

            }

            else {

                $namespaceJSON = '
                    {
                        "namespace": {
                            "prefix": "' . $data['name'] . '",
                            "uri": "' . $data['url'] . '"
                        }
                    }
                ';

                $ch = curl_init($geoServerConfigurationAddress);
                curl_setopt($ch, CURLOPT_POST, True);

                $authString = $server->username . ':' . $server->password;
                curl_setopt($ch, CURLOPT_USERPWD, $authString);

                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $namespaceJSON);

                $successCode = 201;
                $buffer = curl_exec($ch);

                $namespaceStatus = 'updated';

            }

        }

        // Create the Omeka record of the namespace.
        $namespace = new NeatlineMapsNamespace;
        $namespace->name = $data['name'];
        $namespace->url = $data['url'];
        $namespace->server_id = $data['server'];
        $namespace->save();

        return $namespaceStatus;

    }

    /**
     * Save server information.
     *
     * @param array $data The field data posted from the form.
     *
     * @return boolean True if save succeeds.
     */
    // public function saveServer($data)
    // {

    //     $server = $this->find($data['id']);
    //     $server->name = $data['name'];
    //     $server->url = $data['url'];
    //     $server->username = $data['username'];
    //     $server->password = $data['password'];

    //     return $server->save() ? true : false;

    // }

    /**
     * Create a new server.
     *
     * @param array $data The field data posted from the form.
     *
     * @return boolean True if insert succeeds.
     */
    // public function createServer($data)
    // {

    //     $server = new NeatlineMapsServer;
    //     $server->name = $data['name'];
    //     $server->url = $data['url'];
    //     $server->username = $data['username'];
    //     $server->password = $data['password'];

    //     return $server->save() ? true : false;

    // }

    /**
     * Delete server by id.
     *
     * @param string $id The id of the server to delete.
     *
     * @return boolean True if delete succeeds.
     */
    // public function deleteServer($id)
    // {

    //     $server = $this->find($id);
    //     $server->delete();
    //     return true;

    // }

}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
