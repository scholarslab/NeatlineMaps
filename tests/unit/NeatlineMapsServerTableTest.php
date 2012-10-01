<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Server table tests.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class NLMAPS_NeatlineMapsServerTableTest extends NLMAPS_Test_AppTestCase
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
     * updateServer() should update a server record.
     *
     * @return void.
     */
    public function testUpdateServer()
    {

        // Create server.
        $server = $this->__server();

        // Mock post.
        $post = array(
            'name' => 'New Title',
            'url' => 'http://new.org/geoserver',
            'workspace' => 'newworkspace',
            'username' => 'username',
            'password' => 'password',
            'active' => 1
        );

        // Pass in the new data.
        $this->serversTable->updateServer($server, $post);
        $newServer = $this->serversTable->find($server->id);

        // Check for updated values.
        $this->assertEquals($newServer->name, 'New Title');
        $this->assertEquals($newServer->url, 'http://new.org/geoserver');
        $this->assertEquals($newServer->namespace, 'newworkspace');
        $this->assertEquals($newServer->username, 'username');
        $this->assertEquals($newServer->password, 'password');
        $this->assertEquals($newServer->active, 1);

    }

    /**
     * updateServer() should remove a trailing slash off the URL.
     *
     * @return void.
     */
    public function testUpdateServerTrailingSlashRemoval()
    {

        // Create server.
        $server = $this->__server();

        // Mock post.
        $post = array(
            'name' => 'Test Server',
            'url' => 'http://localhost:8080/geoserver/',
            'workspace' => 'workspace',
            'username' => 'admin',
            'password' => 'geoserver',
            'active' => 1
        );

        // Pass in the new data.
        $this->serversTable->updateServer($server, $post);
        $newServer = $this->serversTable->find($server->id);

        // Check for updated values.
        $this->assertEquals($newServer->url, 'http://localhost:8080/geoserver');

    }

    /**
     * getActiveServer() should return false when no servers exist.
     *
     * @return void.
     */
    public function testGetActiveServerWhenNoServerExists()
    {
        // Get active server.
        $this->assertFalse($this->serversTable->getActiveServer());
    }

    /**
     * getActiveServer() should return the active server when at least
     * one server exists.
     *
     * @return void.
     */
    public function testGetActiveServerWhenServerExists()
    {

        // Create server.
        $server = $this->__server();

        // Get active server.
        $retrieved = $this->serversTable->getActiveServer();
        $this->assertEquals($retrieved->id, $server->id);

    }

}
