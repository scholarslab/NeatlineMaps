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
?>

<?php

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

        // Do.

    }

    /**
     * Test for OpenLayers.js.
     *
     * @return void.
     */
    public function test_doHeaderJsAndCss()
    {

        // Do.

    }

    /**
     * Test that the neatline_maps_main.css is getting included in neatline-maps admin.
     *
     * @return void.
     */
    public function test_doTabAdminHeaderJsAndCss()
    {

        // Dispatch to maps browse.
        $this->dispatch('neatline-maps/maps');

        // Check that the css is there.
        $this->assertXpath('//link[@rel="stylesheet"][contains(@href, "neatline_maps_main.css")]');

    }

    /**
     * Test that the neatline_maps_admin.css is getting included in neatline-maps admin.
     *
     * @return void.
     */
    public function test_doItemAdminHeaderJsAndCss()
    {

        // Create an item.
        $item = $this->helper->_createItem('Test Item');

        // Test edit.
        $this->dispatch('items/edit/' . $item->id);
        $this->assertXpath('//link[@rel="stylesheet"][contains(@href, "neatline-maps-admin.css")]');

        // Test add.
        $this->dispatch('items/add');
        $this->assertXpath('//link[@rel="stylesheet"][contains(@href, "neatline-maps-admin.css")]');

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
    public function testGetItems()
    {

        // Create items.

    }

}
