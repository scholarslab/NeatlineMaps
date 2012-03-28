<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Servers integration tests.
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

class NeatlineMaps_ServersTest extends Omeka_Test_AppTestCase
{

    public function setUp()
    {

        parent::setUp();
        $this->helper = new NeatlineMaps_Test_AppTestCase;
        $this->helper->setUpPlugin();
        $this->db = get_db();
        $this->serversTable = $this->db->getTable('NeatlineMapsServer');

    }

    /**
     * Test for existence and proper routing for servers browse.
     *
     * @return void.
     */
    public function testCanBrowseServers()
    {

        $this->dispatch('neatline-maps/servers');
        $this->assertModule('neatline-maps');
        $this->assertController('servers');
        $this->assertAction('browse');
        $this->assertResponseCode(200);

    }

    /**
     * Test for existence and proper routing for add server page.
     *
     * @return void.
     */
    public function testCanViewAddServerPage()
    {

        $this->dispatch('neatline-maps/servers/create');
        $this->assertModule('neatline-maps');
        $this->assertController('servers');
        $this->assertAction('create');
        $this->assertResponseCode(200);

    }

    /**
     * Test for correct form correction on failed server add attempt.
     *
     * @return void.
     */
    public function testAddServerEmptyFields()
    {

        // Test that the validation rejects the submission unless all
        // of the fields are filled out.
        $this->request->setMethod('POST')
            ->setPost(array(
                'name' => '',
                'url' => ''
            )
        );

        // Test that the form posts back to the same view function.
        $this->dispatch('neatline-maps/servers/create');
        $this->assertModule('neatline-maps');
        $this->assertController('servers');
        $this->assertAction('create');
        $this->assertResponseCode(200);

        $this->assertQueryCount('ul.errors', 2);

        $this->assertQueryContentContains('ul.errors li',
            'Enter a name.');
        $this->assertQueryContentContains('ul.errors li',
            'Enter a URL.');

    }

    /**
     * Test for correct form correction on failed server add attempt.
     *
     * @return void.
     */
    public function testAddServerInvalidUrl()
    {

        // Test that the validation rejects the submission unless all
        // of the fields are filled out.
        $this->request->setMethod('POST')
            ->setPost(array(
                'name' => 'Test Server',
                'url' => 'invalid'
            )
        );

        // Test that the form posts back to the same view function.
        $this->dispatch('neatline-maps/servers/create');
        $this->assertModule('neatline-maps');
        $this->assertController('servers');
        $this->assertAction('create');
        $this->assertResponseCode(200);

        $this->assertQueryCount('ul.errors', 1);

        $this->assertQueryContentContains('ul.errors li',
            'Enter a valid URL.');

    }

    /**
     * Test that the add server flow automatically scrubs off trailing slashes
     * at the end of the server URL.
     *
     * @return void.
     */
    public function testAddServerTrailingUrlSlashScrub()
    {

        // Enter valid data, with a trailing slash on the URL.
        $this->request->setMethod('POST')
            ->setPost(array(
                'name' => 'Test Server',
                'url' => 'http://www.geoserver.com/test/'
            )
        );

        $this->dispatch('neatline-maps/servers/create');
        $this->assertRedirectTo('/neatline-maps/servers/browse');

        $serverCount = $this->serversTable->count();
        $server = $this->serversTable->find(1);

        // Test that the server was created.
        $this->assertEquals($serverCount, 1);
        $this->assertEquals($server->name, 'Test Server');
        $this->assertEquals($server->url, 'http://www.geoserver.com/test');

        $this->resetRequest()->resetResponse();

        // For now, since the tests don't follow the redirect..
        $this->dispatch('neatline-maps/servers');
        $this->assertQueryContentContains('td', 'Test Server');
        $this->assertQueryContentContains('td a', 'http://www.geoserver.com/test');

    }

    /**
     * Test for successful server add.
     *
     * @return void.
     */
    public function testAddServerSuccess()
    {

        // Enter valid data.
        $this->request->setMethod('POST')
            ->setPost(array(
                'name' => 'Test Server',
                'url' => 'http://www.geoserver.com/test'
            )
        );

        $this->dispatch('neatline-maps/servers/create');
        $this->assertRedirectTo('/neatline-maps/servers/browse');

        $serverCount = $this->serversTable->count();
        $server = $this->serversTable->find(1);

        // Test that the server was created.
        $this->assertEquals($serverCount, 1);
        $this->assertEquals($server->name, 'Test Server');
        $this->assertEquals($server->url, 'http://www.geoserver.com/test');

        $this->resetRequest()->resetResponse();

        // For now, since the tests don't follow the redirect..
        $this->dispatch('neatline-maps/servers');
        $this->assertQueryContentContains('td', 'Test Server');
        $this->assertQueryContentContains('td a', 'http://www.geoserver.com/test');

    }

    /**
     * Test server edit field population.
     *
     * @return void.
     */
    public function testServerEditFieldPopulate()
    {

        // Create a server.
        $server = $this->helper->_createServer(
            'Test Server',
            'http://www.geoserver.com/test'
        );

        // Test the edit page.
        $this->dispatch('neatline-maps/servers/edit/' . $server->id);
        $this->assertModule('neatline-maps');
        $this->assertController('servers');
        $this->assertAction('edit');
        $this->assertResponseCode(200);

        // Test to confirm that form fields are populating.
        $this->assertXpath('//input[@id="name"][@value="Test Server"]');
        $this->assertXpath('//input[@id="url"][@value="http://www.geoserver.com/test"]');

        $this->assertQueryContentContains('h2', 'Edit Server "Test Server"');

    }

    /**
     * Test for correct form correction on failed server edit attempt.
     *
     * @return void.
     */
    public function testServerEditEmptyFields()
    {

        // Create a server.
        $server = $this->helper->_createServer(
            'Test Server',
            'http://www.geoserver.com/test'
        );

        // Test that the validation rejects the submission unless all
        // of the fields are filled out.
        $this->request->setMethod('POST')
            ->setPost(array(
                'name' => '',
                'url' => ''
            )
        );

        // Test that the form posts back to the same view function.
        $this->dispatch('neatline-maps/servers/edit/' . $server->id);
        $this->assertModule('neatline-maps');
        $this->assertController('servers');
        $this->assertAction('edit');

        $this->assertQueryCount('ul.errors', 2);

        $this->assertQueryContentContains('ul.errors li',
            'Enter a name.');
        $this->assertQueryContentContains('ul.errors li',
            'Enter a URL.');

    }

    /**
     * Test for correct form correction for invalid url.
     *
     * @return void.
     */
    public function testServerEditInvalidUrl()
    {

        // Create a server.
        $server = $this->helper->_createServer(
            'Test Server',
            'http://www.geoserver.com/test'
        );

        // Test that the validation rejects the submission unless all
        // of the fields are filled out.
        $this->request->setMethod('POST')
            ->setPost(array(
                'name' => 'New Name',
                'url' => 'invalid'
            )
        );

        // Test that the form posts back to the same view function.
        $this->dispatch('neatline-maps/servers/edit/' . $server->id);
        $this->assertModule('neatline-maps');
        $this->assertController('servers');
        $this->assertAction('edit');

        $this->assertQueryCount('ul.errors', 1);

        $this->assertQueryContentContains('ul.errors li',
            'Enter a valid URL.');

    }

    /**
     * Test for successfull server edit.
     *
     * @return void.
     */
    public function testServerEditSucceed()
    {

        // Create a server.
        $server = $this->helper->_createServer(
            'Test Server',
            'http://www.geoserver.com/test'
        );

        $serverCount = $this->serversTable->count();
        $server = $this->serversTable->find(1);

        // Test that the server was created.
        $this->assertEquals($serverCount, 1);
        $this->assertEquals($server->name, 'Test Server');
        $this->assertEquals($server->url, 'http://www.geoserver.com/test');

        $this->request->setMethod('POST')
            ->setPost(array(
                'name' => 'New Server Name',
                'url' => 'http://www.newurl.com'
            )
        );

        // Test that the form posts back to the same view function.
        $this->dispatch('neatline-maps/servers/edit/' . $server->id);
        $this->assertModule('neatline-maps');
        $this->assertController('servers');
        $this->assertAction('edit');
        $this->assertRedirectTo('/neatline-maps/servers/browse');

        $serverCount = $this->serversTable->count();
        $server = $this->serversTable->find(1);

        // Test that the server was created.
        $this->assertEquals($serverCount, 1);
        $this->assertEquals($server->name, 'New Server Name');
        $this->assertEquals($server->url, 'http://www.newurl.com');

        $this->resetRequest()->resetResponse();

        // For now, since the tests don't follow the redirect..
        $this->dispatch('neatline-maps/servers');
        $this->assertQueryContentContains('td strong', 'New Server Name');
        $this->assertQueryContentContains('td a', 'http://www.newurl.com');

    }

    /**
     * Test for failed server delete if the server has maps.
     *
     * @return void.
     */
    public function testServerDeleteFail()
    {

        // Create a server.
        $server = $this->helper->_createServer(
            'Test Server',
            'http://www.geoserver.com/test'
        );

        // Create a map
        $this->helper->_createMapForServer($server);

        // Mock a post request initiated by clicking on the "Delete" button on
        // the delete confirm page.
        $this->request->setMethod('POST')
            ->setPost(array(
                'id' => $server->id,
                'deleteconfirm_submit' => 'Delete',
            )
        );

        // Test that the controller routes to the delete action.
        $this->dispatch('neatline-maps/servers/delete/' . $server->id);

        // Confirm that the server is gone from the database.
        $serverCount = $this->serversTable->count();
        $this->assertEquals($serverCount, 1);

    }

    /**
     * Test for successful server delete.
     *
     * @return void.
     */
    public function testServerDelete()
    {

        // Create a server.
        $server = $this->helper->_createServer(
            'Test Server',
            'http://www.geoserver.com/test'
        );

        $serverCount = $this->serversTable->count();
        $server = $this->serversTable->find(1);

        // Mock a post request initiated by clicking on the "Delete" button on
        // either the browse or edit pages.
        $this->request->setMethod('POST')
            ->setPost(array(
                'id' => $server->id
            )
        );

        // Test that the controller routes to the delete action.
        $this->dispatch('neatline-maps/servers/delete/' . $server->id . '?confirm=false');
        $this->assertModule('neatline-maps');
        $this->assertController('servers');
        $this->assertAction('delete');

        // Test that the server was created.
        $this->assertEquals($serverCount, 1);

        // Mock a post request initiated by clicking on the "Delete" button on
        // the delete confirm page.
        $this->request->setMethod('POST')
            ->setPost(array(
                'id' => $server->id,
                'deleteconfirm_submit' => 'Delete',
            )
        );

        // Test that the controller routes to the delete action.
        $this->dispatch('neatline-maps/servers/delete/' . $server->id);

        // Confirm that the server is gone from the database.
        $serverCount = $this->serversTable->count();
        $this->assertEquals($serverCount, 0);

    }

}
