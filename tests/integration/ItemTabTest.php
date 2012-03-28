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

class NeatlineMaps_ItemTabTest extends Omeka_Test_AppTestCase
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
     * Test that the Neatline Maps tab does not display for item add.
     *
     * @return void.
     */
    public function testNoTabOnItemAdd()
    {

        $this->dispatch('items/add');
        $this->assertResponseCode(200);
        $this->assertNotQueryContentContains('ul[id="section-nav"] li a', 'Neatline Maps');

    }

    /**
     * Test the empty tab on item edit if there are no maps for the item.
     *
     * @return void.
     */
    public function testEmptyTabOnItemEdit()
    {

        // Create an item.
        $item = $this->helper->_createItem('Test Item');

        $this->dispatch('items/edit/' . $item->id);
        $this->assertResponseCode(200);
        $this->assertQueryContentContains('ul[id="section-nav"] li a', 'Neatline Maps');
        $this->assertQueryContentContains('p', 'There are no maps for the item.');
        $this->assertQueryContentContains('a', 'Add a Map');

    }

    /**
     * Test the when the item has a single map.
     *
     * @return void.
     */
    public function testItemTabWithMap()
    {

        // Create a map.
        $map = $this->helper->_createMap();

        $this->dispatch('items/edit/2');
        $this->assertResponseCode(200);
        $this->assertQueryContentContains('ul[id="section-nav"] li a', 'Neatline Maps');
        $this->assertNotQueryContentContains('p', 'There are no maps for the item.');
        $this->assertQueryContentContains('a', 'Add a Map');
        $this->assertQueryCount('div[id="map-list"] tbody tr', 1);
        $this->assertQueryContentContains('a', 'Test Map');
        $this->assertQueryContentContains('a', 'Test Server');
        $this->assertQueryContentContains('a', 'Test_Namespace');
        $this->assertQueryContentContains('a', 'Test Item');
        $this->assertQueryContentContains('td', '5 files');

    }

    /**
     * Test the when the item has multiple maps.
     *
     * @return void.
     */
    public function testItemTabWithMaps()
    {

        // Create an item.
        $newItem = $this->helper->_createItem('Test Item');

        // Create maps, all associated with the same item.
        $i = 0;
        while ($i < 10) {
            $this->helper->_createMap(
                $serverName = 'Test Server',
                $serverUrl = 'http://www.test.com',
                $item = $newItem,
                $mapName = 'Test Map',
                $mapNamespace = 'Test_Namespace');
            $i++;
        }

        $this->dispatch('items/edit/2');
        $this->assertResponseCode(200);
        $this->assertQueryContentContains('ul[id="section-nav"] li a', 'Neatline Maps');
        $this->assertNotQueryContentContains('p', 'There are no maps for the item.');
        $this->assertQueryContentContains('a', 'Add a Map');
        $this->assertQueryCount('div[id="map-list"] tbody tr', 10);

    }

}
