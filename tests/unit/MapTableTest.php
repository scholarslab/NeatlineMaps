<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Map table class tests.
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

class NeatlineMaps_MapTableTest extends Omeka_Test_AppTestCase
{

    public function setUp()
    {

        parent::setUp();
        $this->helper = new NeatlineMaps_Test_AppTestCase;
        $this->helper->setUpPlugin();
        $this->db = get_db();
        $this->mapTable = $this->db->getTable('NeatlineMapsMap');

    }

    /**
     * Test getServer().
     *
     * @return void.
     */
    public function testGetMaps()
    {

        // Create maps.
        $this->helper->_createMaps(25);

        // Get maps with no parameters.
        $maps = $this->mapTable->getMaps();

        // Test the number of maps returned. Need to subtract 1 because of the null
        // item created by the testing suite (??).
        $this->assertEquals(count($maps), 25);

        // Test column construction.
        $this->assertEquals($maps[1]->map_id, 2);
        $this->assertEquals($maps[1]->parent_item, 'Test Item');
        $this->assertEquals($maps[1]->server, 'Test Server');

        $perPage = (int) get_option('per_page_admin');

        // Test paging.
        $page1Maps = $this->mapTable->getMaps(1, 'name DESC', null);
        $page2Maps = $this->mapTable->getMaps(2, 'name DESC', null);
        $allMaps = $this->mapTable->getMaps(null, 'name DESC', null);

        $this->assertEquals(count($page1Maps), $perPage);
        $this->assertEquals($page1Maps[$perPage-1]->id, $allMaps[$perPage-1]->id);
        $this->assertEquals($page2Maps[$perPage-1]->id, $allMaps[2*($perPage)-1]->id);

    }

    /**
     * Test addNewMap().
     *
     * @return void.
     */
    public function testAddNewMap()
    {

        // Mock item and server.
        $item = $this->helper->_createItem('Test Item');
        $server = $this->helper->_createServer('Test Server', 'http://www.test.com', 'admin', 'password');

        // Call the create map method.
        $new_map = $this->mapTable->addNewMap($item, $server, 'Test Map', 'Test_Namespace');
        $map = $this->mapTable->find($new_map->id);

        // Test that the record was created.
        $this->assertEquals($this->mapTable->count(), 1);

        // Test that the data was inserted correctly.
        $this->assertEquals($map->name, 'Test Map');
        $this->assertEquals($map->namespace, 'Test_Namespace');
        $this->assertEquals($map->getServer()->name, 'Test Server');

    }

    /**
     * Test getMapByFile().
     *
     * @return void.
     */
    public function testGetMapByFile()
    {

        // Create a map and get one of the files associated with it.
        $map = $this->helper->_createMap();
        $file = $this->db->getTable('NeatlineMapsMapFile')->find(3);

        // Reget the map with the file.
        $retrieved_map = $this->mapTable->getMapByFile($file);

        // Examine returned map.
        $this->assertEquals(count($retrieved_map), 1);
        $this->assertEquals($retrieved_map->name, 'Test Map');

    }

    /**
     * Test getMapsByItem().
     *
     * @return void.
     */
    public function testGetMapsByItem()
    {

        // Create the items.
        $i1 = $this->helper->_createItem('Test Item 1');
        $i2 = $this->helper->_createItem('Test Item 2');

        // Create two batches of maps, each attached to a different item.
        $i = 0;
        while ($i < 10) {
            $this->helper->_createMap(
                $serverName = 'Test Server 1',
                $serverUrl = 'http://www.test.com',
                $serverUsername = 'admin',
                $serverPassword = 'password',
                $item = $i1,
                $mapName = 'Test Map' . $i,
                $mapNamespace = 'Test_Namespace');
            $i++;
        }

        while ($i < 30) {
            $this->helper->_createMap(
                $serverName = 'Test Server 2',
                $serverUrl = 'http://www.test.com',
                $serverUsername = 'admin',
                $serverPassword = 'password',
                $item = $i2,
                $mapName = 'Test Map' . $i,
                $mapNamespace = 'Test_Namespace');
            $i++;
        }

        // Fetch the items.
        $item1 = $this->db->getTable('Item')->find($i1->id);
        $item2 = $this->db->getTable('Item')->find($i2->id);

        // Get the two map batches.
        $item1Maps = $this->mapTable->getMapsByItem($item1);
        $item2Maps = $this->mapTable->getMapsByItem($item2);

        // Check.
        $this->assertEquals(count($item1Maps), 10);
        $this->assertEquals(count($item2Maps), 20);

        foreach ($item1Maps as $map) {
            $this->assertEquals($map->server, 'Test Server 1');
            $this->assertEquals($map->parent_item,
                $this->db->getTable('ElementText')->fetchObject(
                    $this->db->getTable('ElementText')->getSelect()
                    ->where('record_id = ' . $item1->id . ' AND element_id = 50'))->text
                );
        }

        foreach ($item2Maps as $map) {
            $this->assertEquals($map->server, 'Test Server 2');
            $this->assertEquals($map->parent_item,
                $this->db->getTable('ElementText')->fetchObject(
                    $this->db->getTable('ElementText')->getSelect()
                    ->where('record_id = ' . $item2->id . ' AND element_id = 50'))->text
                );
        }

    }

    /**
     * Test getMapsByItemForPublicDisplay().
     *
     * @return void.
     */
    public function testGetMapsByItemForPublicDisplay()
    {

        // Create the items.
        $i1 = $this->helper->_createItem('Test Item 1');
        $i2 = $this->helper->_createItem('Test Item 2');

        // Create two batches of maps, each attached to a different item.
        $i = 0;
        while ($i < 10) {
            $this->helper->_createMap(
                $serverName = 'Test Server 1',
                $serverUrl = 'http://www.test.com',
                $serverUsername = 'admin',
                $serverPassword = 'password',
                $item = $i1,
                $mapName = 'Test Map' . $i,
                $mapNamespace = 'Test_Namespace');
            $i++;
        }

        while ($i < 30) {
            $this->helper->_createMap(
                $serverName = 'Test Server 2',
                $serverUrl = 'http://www.test.com',
                $serverUsername = 'admin',
                $serverPassword = 'password',
                $item = $i2,
                $mapName = 'Test Map' . $i,
                $mapNamespace = 'Test_Namespace');
            $i++;
        }

        // Fetch the items.
        $item1 = $this->db->getTable('Item')->find($i1->id);
        $item2 = $this->db->getTable('Item')->find($i2->id);

        // Get the two map batches.
        $item1Maps = $this->mapTable->getMapsByItemForPublicDisplay($item1);
        $item2Maps = $this->mapTable->getMapsByItemForPublicDisplay($item2);

        // Check.
        $this->assertEquals(count($item1Maps), 10);
        $this->assertEquals(count($item2Maps), 20);

        // Confirm that the extra columns added for the admin are not present.
        foreach ($item1Maps as $map) {
            $this->assertEquals($map->server, null);
            $this->assertEquals($map->parent_item, null);
            $this->assertEquals($map->map_id, null);
        }

        foreach ($item2Maps as $map) {
            $this->assertEquals($map->server, null);
            $this->assertEquals($map->parent_item, null);
            $this->assertEquals($map->map_id, null);
        }

    }

    /**
     * Test deleteMap(), with delete Omeka files set to false.
     *
     * @return void.
     */
    public function testDeleteMapKeepOmekaFiles()
    {

        // Create a map and files and get table classes.
        $map = $this->helper->_createMap();
        $mapTable = $this->db->getTable('NeatlineMapsMap');
        $mapFiles = $this->db->getTable('NeatlineMapsMapFile');
        $files = $this->db->getTable('File');

        // Check record counts.
        $this->assertEquals($mapTable->count(), 1);
        $this->assertEquals($mapFiles->count(), 5);
        $this->assertEquals($files->count(), 5);

        // Delete the map but don't delete the files.
        $this->mapTable->deleteMap($map->id, false, false);

        // Check record counts.
        $this->assertEquals($mapTable->count(), 0);
        $this->assertEquals($mapFiles->count(), 0);
        $this->assertEquals($files->count(), 5);

    }

    /**
     * Test deleteMap(), with delete Omeka files set to true.
     *
     * @return void.
     */
    public function testDeleteMapDeleteOmekaFiles()
    {

        // Because the setup code for these tests just mocks Omeka files as
        // database objects (no real files), file delete is hard to test here
        // without actually copying dummy files into the testing install of
        // Omeka, which causes a lot of other problems. In lieu of that, this
        // code just calls the deleteMap() function with file delete set to
        // true, and listens for the expected Omeka error that gets thrown
        // when Omeka can't find a physical file to delete that corresponds
        // to the database record.
        $this->setExpectedException('InvalidArgumentException');

        // Create a map and files and get table classes.
        $map = $this->helper->_createMapWithRealFiles();
        $mapTable = $this->db->getTable('NeatlineMapsMap');
        $mapFiles = $this->db->getTable('NeatlineMapsMapFile');
        $files = $this->db->getTable('File');

        // Check record counts.
        $this->assertEquals($mapTable->count(), 1);
        $this->assertEquals($mapFiles->count(), 5);
        $this->assertEquals($files->count(), 5);

        // Delete the map but don't delete the files.
        $this->mapTable->deleteMap($map->id, true, false);

    }

}
