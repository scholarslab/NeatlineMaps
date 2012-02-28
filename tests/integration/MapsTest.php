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
        $this->assertQueryCount('table tbody tr', 5);

        // Map title sorting, ascending.
        $this->dispatch('neatline-maps/maps?sort_field=name');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[1]/a', 'Test Map 0');
        $this->dispatch('neatline-maps/maps?sort_field=name&sort_dir=a');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[1]/a', 'Test Map 0');

        // Map title sorting, descending.
        $this->dispatch('neatline-maps/maps?sort_field=name&sort_dir=d');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[1]/a', 'Test Map 4');

        // Server name sorting, ascending.
        $this->dispatch('neatline-maps/maps?sort_field=server');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[2]/a', 'Test Server 0');
        $this->dispatch('neatline-maps/maps?sort_field=name&sort_dir=a');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[2]/a', 'Test Server 0');

        // Server name sorting, descending.
        $this->dispatch('neatline-maps/maps?sort_field=server&sort_dir=d');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[2]/a', 'Test Server 4');

        // Namespace name sorting, ascending.
        $this->dispatch('neatline-maps/maps?sort_field=namespace');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[3]/a', 'Test_Namespace 0');
        $this->dispatch('neatline-maps/maps?sort_field=namespace&sort_dir=a');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[3]/a', 'Test_Namespace 0');

        // Namespace name sorting, descending.
        $this->dispatch('neatline-maps/maps?sort_field=namespace&sort_dir=d');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[3]/a', 'Test_Namespace 4');

        // Item name sorting, ascending.
        $this->dispatch('neatline-maps/maps?sort_field=parent_item');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[4]/a', 'Test Item 0');
        $this->dispatch('neatline-maps/maps?sort_field=parent_item&sort_dir=a');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[4]/a', 'Test Item 0');

        // Namespace name sorting, descending.
        $this->dispatch('neatline-maps/maps?sort_field=parent_item&sort_dir=d');
        $this->assertXpathContentContains('//table/tbody/tr[1]/td[4]/a', 'Test Item 4');

    }

    /**
     * Test maps browse and sorting.
     *
     * @return void.
     */
    public function testBrowsePagination()
    {

        set_option('per_page_admin', 10);
        $this->helper->_createMaps(25);

        // Page 1.
        $this->dispatch('neatline-maps/maps');
        $this->assertQueryCount('tbody tr', 10);
        $this->assertQueryCount('div[class="pagination"]', 1);

        $this->resetRequest()->resetResponse();

        // Page 2.
        $this->dispatch('neatline-maps/maps/2');
        $this->assertQueryCount('tbody tr', 10);
        $this->assertQueryCount('div[class="pagination"]', 1);

        $this->resetRequest()->resetResponse();

        // Page 3.
        $this->dispatch('neatline-maps/maps/3');
        $this->assertQueryCount('tbody tr', 5);
        $this->assertQueryCount('div[class="pagination"]', 1);

    }

    /**
     * Test for redirect to server add page when no servers.
     *
     * @return void.
     */
    // public function testNoServersAddMapRedirect()
    // {

    //     $this->dispatch('neatline-maps/maps/create');
    //     $this->assertModule('neatline-maps');
    //     $this->assertController('servers');
    //     $this->assertAction('create');
    //     $this->assertResponseCode(200);
    //     $this->assertQueryContentContains('div.error', 'You have to create a server before you can add a map.');

    // }

    /**
     * Test for no redirect to server add page when servers exist.
     *
     * @return void.
     */
    // public function testServersAddMapNoRedirect()
    // {

    //     $this->helper->_createServer('Test Server', 'http://www.test.org', 'admin', 'password');

    //     $this->dispatch('neatline-maps/maps/create');
    //     $this->assertModule('neatline-maps');
    //     $this->assertController('maps');
    //     $this->assertAction('itemselect');
    //     $this->assertResponseCode(200);

    // }

    /**
     * Test item select item browse and sorting.
     *
     * @return void.
     */
    // public function testItemSelectBrowseAndSorting()
    // {

    //     // Create server and maps.
    //     $this->helper->_createServer('Test Server', 'http://www.test.org', 'admin', 'password');
    //     $this->helper->_createItems(5);

    //     // No sorting.
    //     $this->dispatch('neatline-maps/maps/create');
    //     $this->assertQueryCount('table tbody tr', 6);

    //     // Map title sorting, ascending.
    //     $this->dispatch('neatline-maps/maps/create?sort_field=item_name');
    //     $this->assertXpathContentContains('//table/tbody/tr[2]/td[1]/a', 'Test Item 0');
    //     $this->dispatch('neatline-maps/maps/create?sort_field=item_name&sort_dir=a');
    //     $this->assertXpathContentContains('//table/tbody/tr[2]/td[1]/a', 'Test Item 0');

    //     // Map title sorting, descending
    //     $this->dispatch('neatline-maps/maps/create?sort_field=item_name&sort_dir=d');
    //     $this->assertXpathContentContains('//table/tbody/tr[1]/td[1]/a', 'Test Item 4');

    // }

    /**
     * Test item select item search.
     *
     * @return void.
     */
    // public function testItemSelectSearch()
    // {

    //     // Create server and items.
    //     $this->helper->_createServer('Test Server', 'http://www.test.org', 'admin', 'password');
    //     $this->helper->_createItems(5);

    //     // No sorting.
    //     $this->dispatch('neatline-maps/maps/create?search=3&submit_search=Search+Items');
    //     $this->assertQueryCount('table tbody tr', 1);

    //     $this->assertXpathContentContains('//table/tbody/tr[1]/td[1]/a', 'Test Item 3');

    // }

}
