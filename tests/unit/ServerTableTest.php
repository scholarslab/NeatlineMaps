<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Server table class tests.
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

class NeatlineMaps_ServerTableTest extends Omeka_Test_AppTestCase
{

    public function setUp()
    {

        parent::setUp();
        $this->helper = new NeatlineMaps_Test_AppTestCase;
        $this->helper->setUpPlugin();
        $this->db = get_db();
        $this->serverTable = $this->db->getTable('NeatlineMapsServer');

    }

    /**
     * Test getServers().
     *
     * @return void.
     */
    public function testGetServers()
    {

        // Create servers.
        $this->helper->_createServers(25);

        // Get servers with no parameters.
        $servers = $this->serverTable->getServers();

        // Test the number of servers returned.
        $this->assertEquals(count($servers), 25);

        $perPage = (int) get_option('per_page_admin');

        // Test paging.
        $page1Servers = $this->serverTable->getServers(1, 'name DESC', null);
        $page2Servers = $this->serverTable->getServers(2, 'name DESC', null);
        $allServers = $this->serverTable->getServers(null, 'name DESC', null);

        $this->assertEquals(count($page1Servers), $perPage);
        $this->assertEquals($page1Servers[$perPage-1]->id, $allServers[$perPage-1]->id);
        $this->assertEquals($page2Servers[$perPage-1]->id, $allServers[2*($perPage)-1]->id);

    }

    /**
     * Test saveServer().
     *
     * @return void.
     */
    public function testSaveServer()
    {

        // Create server.
        $server = $this->helper->_createServer('Test Server', 'http://www.test.org', 'admin', 'password');

        // Mock post.
        $post = array(
            'id' => $server->id,
            'name' => 'New Test Server Name',
            'url' => 'http://www.newurl.com',
            'username' => 'admin2',
            'password' => 'password2'
        );

        // Pass in the new data.
        $this->serverTable->saveServer($post);
        $updatedServer = $this->serverTable->find($server->id);

        // Check for updated values.
        $this->assertEquals($updatedServer->name, 'New Test Server Name');
        $this->assertEquals($updatedServer->url, 'http://www.newurl.com');
        $this->assertEquals($updatedServer->username, 'admin2');
        $this->assertEquals($updatedServer->password, 'password2');

        // Test that the function detects and scrubs a trailing slash
        // on the url.

        // Mock post.
        $post = array(
            'id' => $server->id,
            'name' => 'New Test Server Name',
            'url' => 'http://www.newurl.com/',
            'username' => 'admin2',
            'password' => 'password2'
        );

        // Pass in the new data.
        $this->serverTable->saveServer($post);
        $updatedServer = $this->serverTable->find($server->id);

        // Check for updated values.
        $this->assertEquals($updatedServer->name, 'New Test Server Name');
        $this->assertEquals($updatedServer->url, 'http://www.newurl.com');
        $this->assertEquals($updatedServer->username, 'admin2');
        $this->assertEquals($updatedServer->password, 'password2');

    }

    /**
     * Test createServer().
     *
     * @return void.
     */
    public function testCreateServer()
    {

        // Mock post.
        $post = array(
            'name' => 'Test Server',
            'url' => 'http://www.test.org',
            'username' => 'admin',
            'password' => 'password'
        );

        // Pass in the new data.
        $this->serverTable->createServer($post);
        $newServer = $this->serverTable->find(1);

        // Check for updated values.
        $this->assertEquals($this->serverTable->count(), 1);
        $this->assertEquals($newServer->name, 'Test Server');
        $this->assertEquals($newServer->url, 'http://www.test.org');
        $this->assertEquals($newServer->username, 'admin');
        $this->assertEquals($newServer->password, 'password');

        $newServer->delete();

        // Test that the function detects and scrubs a trailing slash
        // on the url.

        // Mock post.
        $post = array(
            'name' => 'Test Server',
            'url' => 'http://www.test.org/',
            'username' => 'admin',
            'password' => 'password'
        );

        // Pass in the new data.
        $this->serverTable->createServer($post);
        $newServer = $this->serverTable->find(2);

        // Check for updated values.
        $this->assertEquals($this->serverTable->count(), 1);
        $this->assertEquals($newServer->name, 'Test Server');
        $this->assertEquals($newServer->url, 'http://www.test.org');
        $this->assertEquals($newServer->username, 'admin');
        $this->assertEquals($newServer->password, 'password');

    }

    /**
     * Test deleteServer().
     *
     * @return void.
     */
    public function testDeleteServer()
    {

        // Create server.
        $server = $this->helper->_createServer('Test Server', 'http://www.test.org', 'admin', 'password');

        // Check count.
        $this->assertEquals($this->serverTable->count(), 1);

        // Delete it.
        $this->serverTable->deleteServer($server->id);

        // Check count.
        $this->assertEquals($this->serverTable->count(), 0);;

    }

}
