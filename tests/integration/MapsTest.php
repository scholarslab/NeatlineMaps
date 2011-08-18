<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Maps integration tests.
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

class NeatlineMaps_MapsTest extends Omeka_Test_AppTestCase
{

    public function setUp()
    {

        parent::setUp();
        $this->helper = new NeatlineMaps_Test_AppTestCase;
        $this->helper->setUpPlugin();
        $this->db = get_db();
        $this->mapsTable = $this->db->getTable('NeatlineMapsMap');

    }

    /**
     * Test for existence and proper routing for maps browse.
     *
     * @return void.
     */
    public function testCanViewBrowseMaps()
    {

        $this->dispatch('neatline-maps/maps');
        $this->assertModule('neatline-maps');
        $this->assertController('maps');
        $this->assertAction('browse');
        $this->assertResponseCode(200);

    }

    /**
     * Test maps browse and sorting.
     *
     * @return void.
     */
    public function testBrowseAndSorting()
    {

        $this->helper->_createMaps(5);

        // No sorting.
        $this->dispatch('neatline-maps/maps');
        $this->assertQueryCount('table[class="fedora"] tbody tr', 5);

        // Map title sorting, ascending.
        $this->dispatch('neatline-maps/maps?sort_field=name');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[1]/a', 'Test Map 0');
        $this->dispatch('neatline-maps/maps?sort_field=name&sort_dir=a');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[1]/a', 'Test Map 0');

        // Map title sorting, descending.
        $this->dispatch('neatline-maps/maps?sort_field=name&sort_dir=d');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[1]/a', 'Test Map 4');

        // Server name sorting, ascending.
        $this->dispatch('neatline-maps/maps?sort_field=server');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[2]/a', 'Test Server 0');
        $this->dispatch('neatline-maps/maps?sort_field=name&sort_dir=a');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[2]/a', 'Test Server 0');

        // Server name sorting, descending.
        $this->dispatch('neatline-maps/maps?sort_field=server&sort_dir=d');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[2]/a', 'Test Server 4');

        // Namespace name sorting, ascending.
        $this->dispatch('neatline-maps/maps?sort_field=namespace');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[3]/a', 'Test_Namespace 0');
        $this->dispatch('neatline-maps/maps?sort_field=namespace&sort_dir=a');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[3]/a', 'Test_Namespace 0');

        // Namespace name sorting, descending.
        $this->dispatch('neatline-maps/maps?sort_field=namespace&sort_dir=d');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[3]/a', 'Test_Namespace 4');

        // Item name sorting, ascending.
        $this->dispatch('neatline-maps/maps?sort_field=parent_item');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[4]/a', 'Test Item 0');
        $this->dispatch('neatline-maps/maps?sort_field=parent_item&sort_dir=a');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[4]/a', 'Test Item 0');

        // Namespace name sorting, descending.
        $this->dispatch('neatline-maps/maps?sort_field=parent_item&sort_dir=d');
        $this->assertXpathContentContains('//table[@class="fedora"]/tbody/tr[1]/td[4]/a', 'Test Item 4');

    }

    /**
     * Test for redirect to server add page when no servers.
     *
     * @return void.
     */
    public function testNoServersRedirect()
    {

        $this->dispatch('neatline-maps/maps/create');
        $this->assertModule('neatline-maps');
        $this->assertController('servers');
        $this->assertAction('create');
        $this->assertResponseCode(200);

    }

    /**
     * Test for no redirect to server add page when servers exist.
     *
     * @return void.
     */
    public function testServersNoRedirect()
    {

        $this->helper->_createServer('Test Server', 'http://www.test.org', 'admin', 'password');

        $this->dispatch('neatline-maps/maps/create');
        $this->assertModule('neatline-maps');
        $this->assertController('maps');
        $this->assertAction('itemselect');
        $this->assertResponseCode(200);

    }












    /**
     * Test browse maps with no pagination.
     *
     * @return void.
     */
    // public function testMapBrowseNoPagination()
    // {

    //     $this->dispatch('items/add');
    //     $this->assertResponseCode(200);
    //     $this->assertNotQueryContentContains('ul[id="section-nav"] li a', 'Neatline Maps');

    // }

    /**
     * Test the empty tab on item edit if there are no maps for the item.
     *
     * @return void.
     */
    // public function testEmptyTabOnItemEdit()
    // {

    //     // Create an item.
    //     $item = $this->helper->_createItem('Test Item');

    //     $this->dispatch('items/edit/' . $item->id);
    //     $this->assertResponseCode(200);
    //     $this->assertQueryContentContains('ul[id="section-nav"] li a', 'Neatline Maps');
    //     $this->assertQueryContentContains('p', 'There are no maps for the item.');
    //     $this->assertXpath('//input[@type="submit"][@value="Add a Map"]');
    //     $this->assertXpath('//input[@type="hidden"][@name="item_id"][@value="' . $item->id . '"]');
    //     $this->assertXpath('//form[contains(@action, "/maps/create/selectserver")]');

    // }

    /**
     * Test the when the item has a single map.
     *
     * @return void.
     */
    // public function testItemTabWithMap()
    // {

    //     // Create a map.
    //     $map = $this->helper->_createMap();

    //     $this->dispatch('items/edit/2');
    //     $this->assertResponseCode(200);
    //     $this->assertQueryContentContains('ul[id="section-nav"] li a', 'Neatline Maps');
    //     $this->assertNotQueryContentContains('p', 'There are no maps for the item.');
    //     $this->assertXpath('//input[@type="submit"][@value="Add a Map"]');
    //     $this->assertXpath('//input[@type="hidden"][@name="item_id"][@value="2"]');
    //     $this->assertXpath('//form[contains(@action, "/maps/create/selectserver")]');
    //     $this->assertQueryCount('div[id="map-list"] tbody tr', 1);
    //     $this->assertQueryContentContains('a', 'Test Map');
    //     $this->assertQueryContentContains('a', 'Test Server');
    //     $this->assertQueryContentContains('a', 'Test_Namespace');
    //     $this->assertQueryContentContains('a', 'Test Item');
    //     $this->assertQueryContentContains('td', '5 files');

    // }

    /**
     * Test the when the item has multiple maps.
     *
     * @return void.
     */
    // public function testItemTabWithMaps()
    // {

    //     // Create an item.
    //     $newItem = $this->helper->_createItem('Test Item');

    //     // Create maps, all associated with the same item.
    //     $i = 0;
    //     while ($i < 10) {
    //         $this->helper->_createMap(
    //             $serverName = 'Test Server',
    //             $serverUrl = 'http://www.test.com',
    //             $serverUsername = 'admin',
    //             $serverPassword = 'password',
    //             $item = $newItem,
    //             $mapName = 'Test Map',
    //             $mapNamespace = 'Test_Namespace');
    //         $i++;
    //     }

    //     $this->dispatch('items/edit/2');
    //     $this->assertResponseCode(200);
    //     $this->assertQueryContentContains('ul[id="section-nav"] li a', 'Neatline Maps');
    //     $this->assertNotQueryContentContains('p', 'There are no maps for the item.');
    //     $this->assertXpath('//input[@type="submit"][@value="Add a Map"]');
    //     $this->assertXpath('//input[@type="hidden"][@name="item_id"][@value="2"]');
    //     $this->assertXpath('//form[contains(@action, "/maps/create/selectserver")]');
    //     $this->assertQueryCount('div[id="map-list"] tbody tr', 10);

    // }

    /**
     * Test for existence and proper routing for add server page.
     *
     * @return void.
     */
    // public function testCanViewAddServerPage()
    // {

    //     $this->dispatch('neatline-maps/servers/create');
    //     $this->assertModule('neatline-maps');
    //     $this->assertController('servers');
    //     $this->assertAction('create');
    //     $this->assertResponseCode(200);

    // }

    /**
     * Test for correct form correction on failed server add attempt.
     *
     * @return void.
     */
    // public function testAddServerFail()
    // {

    //     // Test that the validation rejects the submission unless all
    //     // of the fields are filled out.
    //     $this->request->setMethod('POST')
    //         ->setPost(array(
    //             'name' => '',
    //             'url' => '',
    //             'username' => '',
    //             'password' => ''
    //         )
    //     );

    //     // Test that the form posts back to the same view function.
    //     $this->dispatch('neatline-maps/servers/create');
    //     $this->assertModule('neatline-maps');
    //     $this->assertController('servers');
    //     $this->assertAction('create');
    //     $this->assertResponseCode(200);

    //     $this->assertQueryCount('ul.errors', 4);

    //     $this->assertQueryContentContains('dd#name-element ul.errors li',
    //         'Value is required and can\'t be empty');
    //     $this->assertQueryContentContains('dd#url-element ul.errors li',
    //         'Value is required and can\'t be empty');
    //     $this->assertQueryContentContains('dd#username-element ul.errors li',
    //         'Value is required and can\'t be empty');
    //     $this->assertQueryContentContains('dd#password-element ul.errors li',
    //         'Value is required and can\'t be empty');

    // }

    /**
     * Test that the add server flow automatically scrubs off trailing slashes
     * at the end of the server URL.
     *
     * @return void.
     */
    // public function testAddServerTrailingUrlSlashScrub()
    // {

    //     // Enter valid data, with a trailing slash on the URL.
    //     $this->request->setMethod('POST')
    //         ->setPost(array(
    //             'name' => 'Test Server',
    //             'url' => 'http://www.geoserver.com/test/',
    //             'username' => 'test',
    //             'password' => 'test'
    //         )
    //     );

    //     $this->dispatch('neatline-maps/servers/create');
    //     $this->assertRedirectTo('/neatline-maps/servers/browse');

    //     $serverCount = $this->serversTable->count();
    //     $server = $this->serversTable->find(1);

    //     // Test that the server was created.
    //     $this->assertEquals($serverCount, 1);
    //     $this->assertEquals($server->name, 'Test Server');
    //     $this->assertEquals($server->url, 'http://www.geoserver.com/test');
    //     $this->assertEquals($server->username, 'test');
    //     $this->assertEquals($server->password, 'test');

    //     // Test redirect back to browse.
    //     // Why does this not work?
    //     // $this->assertAction('browse');

    //     $this->resetRequest()->resetResponse();

    //     // For now, since the tests don't follow the redirect..
    //     $this->dispatch('neatline-maps/servers');
    //     $this->assertQueryContentContains('td a strong', 'Test Server');
    //     $this->assertQueryContentContains('td a', 'http://www.geoserver.com/test');
    //     $this->assertQueryContentContains('td span', 'Offline or inaccessible');

    // }

    /**
     * Test for successful server add.
     *
     * @return void.
     */
    // public function testAddServerSuccess()
    // {

    //     // Enter valid data.
    //     $this->request->setMethod('POST')
    //         ->setPost(array(
    //             'name' => 'Test Server',
    //             'url' => 'http://www.geoserver.com/test',
    //             'username' => 'test',
    //             'password' => 'test'
    //         )
    //     );

    //     $this->dispatch('neatline-maps/servers/create');
    //     $this->assertRedirectTo('/neatline-maps/servers/browse');

    //     $serverCount = $this->serversTable->count();
    //     $server = $this->serversTable->find(1);

    //     // Test that the server was created.
    //     $this->assertEquals($serverCount, 1);
    //     $this->assertEquals($server->name, 'Test Server');
    //     $this->assertEquals($server->url, 'http://www.geoserver.com/test');
    //     $this->assertEquals($server->username, 'test');
    //     $this->assertEquals($server->password, 'test');

    //     // Test redirect back to browse.
    //     // Why does this not work?
    //     // $this->assertAction('browse');

    //     $this->resetRequest()->resetResponse();

    //     // For now, since the tests don't follow the redirect..
    //     $this->dispatch('neatline-maps/servers');
    //     $this->assertQueryContentContains('td a strong', 'Test Server');
    //     $this->assertQueryContentContains('td a', 'http://www.geoserver.com/test');
    //     $this->assertQueryContentContains('td span', 'Offline or inaccessible');

    // }

    /**
     * Test server edit field population.
     *
     * @return void.
     */
    // public function testServerEditFieldPopulate()
    // {

    //     // Create a server.
    //     $server = $this->helper->_createServer(
    //         'Test Server',
    //         'http://www.geoserver.com/test',
    //         'test',
    //         'test'
    //     );

    //     // Test the edit page.
    //     $this->dispatch('neatline-maps/servers/edit/' . $server->id);
    //     $this->assertModule('neatline-maps');
    //     $this->assertController('servers');
    //     $this->assertAction('edit');
    //     $this->assertResponseCode(200);

    //     // Test to confirm that form fields are populating.
    //     $this->assertXpath('//input[@id="name"][@value="Test Server"]');
    //     $this->assertXpath('//input[@id="url"][@value="http://www.geoserver.com/test"]');
    //     $this->assertXpath('//input[@id="username"][@value="test"]');
    //     $this->assertXpath('//input[@id="password"][@value="test"]');

    //     $this->assertQueryContentContains('h2', 'Edit Server "Test Server"');

    // }

    /**
     * Test for correct form correction on failed server edit attempt.
     *
     * @return void.
     */
    // public function testServerEditFail()
    // {

    //     // Create a server.
    //     $server = $this->helper->_createServer(
    //         'Test Server',
    //         'http://www.geoserver.com/test',
    //         'test',
    //         'test'
    //     );

    //     // Test that the validation rejects the submission unless all
    //     // of the fields are filled out.
    //     $this->request->setMethod('POST')
    //         ->setPost(array(
    //             'name' => '',
    //             'url' => '',
    //             'username' => '',
    //             'password' => '',
    //             'id' => 1,
    //             'edit_submit' => 'Save'
    //         )
    //     );

    //     // Test that the form posts back to the same view function.
    //     $this->dispatch('neatline-maps/servers/edit/' . $server->id);
    //     $this->assertModule('neatline-maps');
    //     $this->assertController('servers');
    //     $this->assertAction('edit');

    //     $this->assertQueryCount('ul.errors', 4);

    //     $this->assertQueryContentContains('dd#name-element ul.errors li',
    //         'Value is required and can\'t be empty');
    //     $this->assertQueryContentContains('dd#url-element ul.errors li',
    //         'Value is required and can\'t be empty');
    //     $this->assertQueryContentContains('dd#username-element ul.errors li',
    //         'Value is required and can\'t be empty');
    //     $this->assertQueryContentContains('dd#password-element ul.errors li',
    //         'Value is required and can\'t be empty');

    // }

    /**
     * Test for successfull server edit.
     *
     * @return void.
     */
    // public function testServerEditSucceed()
    // {

    //     // Create a server.
    //     $server = $this->helper->_createServer(
    //         'Test Server',
    //         'http://www.geoserver.com/test',
    //         'test',
    //         'test'
    //     );

    //     $serverCount = $this->serversTable->count();
    //     $server = $this->serversTable->find(1);

    //     // Test that the server was created.
    //     $this->assertEquals($serverCount, 1);
    //     $this->assertEquals($server->name, 'Test Server');
    //     $this->assertEquals($server->url, 'http://www.geoserver.com/test');
    //     $this->assertEquals($server->username, 'test');
    //     $this->assertEquals($server->password, 'test');

    //     $this->request->setMethod('POST')
    //         ->setPost(array(
    //             'name' => 'New Server Name',
    //             'url' => 'http://www.newurl.url',
    //             'username' => 'differentpass',
    //             'password' => 'differentpass',
    //             'id' => 1,
    //             'edit_submit' => 'Save'
    //         )
    //     );

    //     // Test that the form posts back to the same view function.
    //     $this->dispatch('neatline-maps/servers/edit/' . $server->id);
    //     $this->assertModule('neatline-maps');
    //     $this->assertController('servers');
    //     $this->assertAction('edit');
    //     $this->assertRedirectTo('/neatline-maps/servers/browse');

    //     $serverCount = $this->serversTable->count();
    //     $server = $this->serversTable->find(1);

    //     // Test that the server was created.
    //     $this->assertEquals($serverCount, 1);
    //     $this->assertEquals($server->name, 'New Server Name');
    //     $this->assertEquals($server->url, 'http://www.newurl.url');
    //     $this->assertEquals($server->username, 'differentpass');
    //     $this->assertEquals($server->password, 'differentpass');

    //     // Test redirect back to browse.
    //     // Why does this not work?
    //     // $this->assertAction('browse');

    //     $this->resetRequest()->resetResponse();

    //     // For now, since the tests don't follow the redirect..
    //     $this->dispatch('neatline-maps/servers');
    //     $this->assertQueryContentContains('td a strong', 'New Server Name');
    //     $this->assertQueryContentContains('td a', 'http://www.newurl.url');
    //     $this->assertQueryContentContains('td span', 'Offline or inaccessible');

    // }

    /**
     * Test for successfull server delete.
     *
     * @return void.
     */
    // public function testServerDelete()
    // {

    //     // Create a server.
    //     $server = $this->helper->_createServer(
    //         'Test Server',
    //         'http://www.geoserver.com/test',
    //         'test',
    //         'test'
    //     );

    //     $serverCount = $this->serversTable->count();
    //     $server = $this->serversTable->find(1);

    //     // Mock a post request initiated by clicking on the "Delete" button on
    //     // either the browse or edit pages.
    //     $this->request->setMethod('POST')
    //         ->setPost(array(
    //             'id' => $server->id,
    //             'confirm' => 'false'
    //         )
    //     );

    //     // Test that the controller routes to the delete action.
    //     $this->dispatch('neatline-maps/servers/delete/' . $server->id);
    //     $this->assertModule('neatline-maps');
    //     $this->assertController('servers');
    //     $this->assertAction('delete');

    //     $serverCount = $this->serversTable->count();
    //     $server = $this->serversTable->find(1);

    //     // Test that the server was created.
    //     $this->assertEquals($serverCount, 1);

    //     // Test that the server is deleted on confirmation.

    //     // Mock a post request initiated by clicking on the "Delete" button on
    //     // the delete confirm page.
    //     $this->request->setMethod('POST')
    //         ->setPost(array(
    //             'id' => $server->id,
    //             'delete_confirm' => 'Delete',
    //         )
    //     );

    //     // Test that the controller routes to the delete action.
    //     $this->dispatch('neatline-maps/servers/delete/' . $server->id);

    //     // Confirm that the server is gone from the database.
    //     $serverCount = $this->serversTable->count();
    //     $this->assertEquals($serverCount, 1);

    // }

}
