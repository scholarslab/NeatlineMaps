<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Tests for helper functions.
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

class NeatlineMaps_HelpersTest extends Omeka_Test_AppTestCase
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
     * Test the item tab output.
     *
     * @return void.
     */
    public function test_doItemForm()
    {

        // Create an item.
        $item = $this->helper->_createItem('Test Item');

        // Build the form.
        $form = _doItemForm($item);
        $this->assertNotEquals(strpos($form, 'There are no maps for the item.'), false);

        // This time, create a map for the item.
        $this->helper->_createMap(
            'Test Server',
            'http://www.test.com',
            'admin',
            'password',
            $item,
            'Test Map',
            'Test_Namespace');

        // Build the form.
        $form = _doItemForm($item);
        $this->assertEquals(strpos($form, 'There are no maps for the item.'), false);



    }

    /**
     * Test colum sort processing.
     *
     * @return void.
     */
    public function test_doColumnSortProcessing()
    {

        // Test for empty string if sort field is undefined.
        $this->assertEquals(_doColumnSortProcessing('', ''), '');
        $this->assertEquals(_doColumnSortProcessing('', 'a'), '');
        $this->assertEquals(_doColumnSortProcessing('', 'd'), '');

        // Test for correctly formatted SQL string when parameters are supplied.
        $this->assertEquals(_doColumnSortProcessing('test_col', 'd'), 'test_col DESC');
        $this->assertEquals(_doColumnSortProcessing('test_col', 'a'), 'test_col ASC');

    }

    /**
     * Test item fetching in maps admin.
     *
     * @return void.
     */
    public function test_getItems()
    {

        // Create items.
        $this->helper->_createItem('Test Item 1', null, null);
        $this->helper->_createItem('Test Item 2', null, 'David McClure');
        $this->helper->_createItem('Test Item 3', 12, 'David McClure');

        $items = _getItems();

        // Test the number of items returned. Need to subtract 1 because of the null
        // item created by the testing suite (??).
        $this->assertEquals(count($items)-1, 3);

        // Test column construction.
        $this->assertEquals($items[1]->item_name, 'Test Item 1');
        $this->assertEquals($items[2]->item_name, 'Test Item 2');
        $this->assertEquals($items[3]->item_name, 'Test Item 3');

        $this->assertEquals($items[1]->Type, '');
        $this->assertEquals($items[2]->Type, '');
        $this->assertEquals($items[3]->Type, 'Person');

        $this->assertEquals($items[1]->creator, '');
        $this->assertEquals($items[2]->creator, 'David McClure');
        $this->assertEquals($items[3]->creator, 'David McClure');

        // Create a bunch of items to test sorting and paging, and search.
        $i = 0;
        while ($i < 20) {
            $this->helper->_createItem($i . ' Test Item ' . $i);
            $i++;
        }

        $perPage = (int) get_option('per_page_admin');

        // Test paging.
        $page1Items = _getItems(1, 'item_name DESC', null);
        $page2Items = _getItems(2, 'item_name DESC', null);
        $allItems = _getItems(null, 'item_name DESC', null);

        $this->assertEquals(count($page1Items), $perPage);
        $this->assertEquals($page1Items[$perPage-1]->id, $allItems[$perPage-1]->id);
        $this->assertEquals($page2Items[$perPage-1]->id, $allItems[2*($perPage)-1]->id);

        // Test search.
        $searchItems = _getItems(null, null, '14');

        $this->assertEquals(count($searchItems), 1);
        $this->assertEquals($searchItems[0]->item_name, '14 Test Item 14');

    }

    /**
     * Test single item fetch.
     *
     * @return void.
     */
    public function test_getSingleItem()
    {

        // Create item.
        $item = $this->helper->_createItem('Test Item 3', 12, 'David McClure');

        $item = _getSingleItem($item->id);

        // Test column construction.
        $this->assertEquals($item->item_name, 'Test Item 3');
        $this->assertEquals($item->Type, 'Person');
        $this->assertEquals($item->creator, 'David McClure');

    }

    /**
     * Test string previewer.
     *
     * @return void.
     */
    public function test_previewString()
    {

        // Testing string.
        $string = 'Supercalafragalisticexpialadocious';

        // Test.
        $this->assertEquals(_previewString($string, 5), 'Super...');
        $this->assertEquals(_previewString($string, 50), 'Supercalafragalisticexpialadocious');

    }

    /**
     * Test date formatter.
     *
     * @return void.
     */
    public function test_formatDate()
    {

        // Testing datetime.
        $date = '2011-08-15 15:22:47';

        // Test.
        $this->assertEquals(_formatDate($date), '<strong>August 15, 2011</strong> at 3:22 pm');

    }

}
