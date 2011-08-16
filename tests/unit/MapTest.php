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

class NeatlineMaps_MapTest extends Omeka_Test_AppTestCase
{

    public function setUp()
    {

        parent::setUp();
        $this->helper = new NeatlineMaps_Test_AppTestCase;
        $this->helper->setUpPlugin();
        $this->db = get_db();
        $this->map = $this->helper->_createMap();

    }

    /**
     * Test getServer().
     *
     * @return void.
     */
    public function testGetServer()
    {

        // Get server.
        $server = $this->map->getServer();

        // Inspect the returned server.
        $this->assertEquals(count($server), 1);
        $this->assertEquals($server->name, 'Test Server');
        $this->assertEquals($server->url, 'http://www.test.com');
        $this->assertEquals($server->username, 'admin');
        $this->assertEquals($server->password, 'password');

    }

    /**
     * Test getItem().
     *
     * @return void.
     */
    public function testGetItem()
    {

        // Call getServer().
        $item = $this->map->getItem();
        $texts = $item->getElementText();

        // Inspect the returned item.
        $this->assertEquals(count($item), 1);

    }

    /**
     * Test namespace builder.
     *
     * @return void.
     */
    public function testGetNamespaceUrl()
    {

        // Get namespace.
        $namespace = $this->map->getNamespaceUrl();

        // Test the format.
        $this->assertEquals($namespace, 'http://www.test.com/rest/namespaces/Test_Namespace');


    }

    /**
     * Test namespace builder.
     *
     * @return void.
     */
    public function testGetOmekaFiles()
    {

        // Get namespace.
        $files = $this->map->getOmekaFiles();

        // Test the files array.
        $this->assertEquals(count($files), 5);

        // Test that the files are the files.
        $i = 0;
        foreach ($files as $file) {
            $this->assertEquals($file->original_filename, 'TestFile' . $i . '.jpg');
            $i++;
        }



    }

}
