<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Edition row tests.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class NLMAPS_NeatlineMapsServerTest extends NLMAPS_Test_AppTestCase
{

    /**
     * Install the plugin.
     *
     * @return void.
     */
    public function setUp()
    {
        $this->setUpPlugin();
    }

    /**
     * Test get and set on columns.
     *
     * @return void.
     */
    public function testAttributeAccess()
    {

        // Create a record.
        $server = new NeatlineMapsServer();

        // Set.
        $server->name = 'Test Server';
        $server->url = 'http://localhost:8080/geoserver';
        $server->username = 'admin';
        $server->password = 'geoserver';
        $server->namespace = 'namespace';
        $server->active = 1;
        $server->save();

        // Re-get the edition object.
        $wms = $this->serversTable->find($server->id);

        // Get.
        $this->assertEquals($server->name, 'Test Server');
        $this->assertEquals($server->url, 'http://localhost:8080/geoserver');
        $this->assertEquals($server->username, 'admin');
        $this->assertEquals($server->password, 'geoserver');
        $this->assertEquals($server->namespace, 'namespace');
        $this->assertEquals($server->active, 1);

    }

    /**
     * getWmsAddress() should return the correctly-formed WMS address.
     *
     * @return void.
     */
    public function testGetWmsAddress()
    {

        // Create a record.
        $server = new NeatlineMapsServer();

        // Set.
        $server->name = 'Test Server';
        $server->url = 'http://localhost:8080/geoserver';
        $server->namespace = 'namespace';

        $this->assertEquals(
            $server->getWmsAddress(),
            'http://localhost:8080/geoserver/namespace/wms'
        );

    }

    /**
     * When there are no servers and the saved server is not set to
     * active, set active.
     *
     * @return void.
     */
    public function testSaveInactiveServerWithNoServers()
    {

        // Create a record.
        $server = new NeatlineMapsServer();
        $server->active = 0;
        $server->save();

        // Check for active.
        $this->assertEquals($server->active, 1);

    }

    /**
     * When there is an existing active server and a new server is saved
     * with active = 1, toggle off old active server.
     *
     * @return void.
     */
    public function testSaveActiveServerWithExistingActiveServer()
    {

        // Create active server.
        $server1 = new NeatlineMapsServer();
        $server1->active = 1;
        $server1->save();

        // Create new active server.
        $server2 = new NeatlineMapsServer();
        $server2->active = 1;
        $server2->save();

        // Re-get and check for active.
        $server1 = $this->serversTable->find($server1->id);
        $server2 = $this->serversTable->find($server2->id);
        $this->assertEquals($server1->active, 0);
        $this->assertEquals($server2->active, 1);

    }

}
