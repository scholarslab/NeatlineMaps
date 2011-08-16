<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Map file table class tests.
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

class NeatlineMaps_MapFileTableTest extends Omeka_Test_AppTestCase
{

    public function setUp()
    {

        parent::setUp();
        $this->helper = new NeatlineMaps_Test_AppTestCase;
        $this->helper->setUpPlugin();
        $this->db = get_db();
        $this->mapFileTable = $this->db->getTable('NeatlineMapsMapFile');

    }

    /**
     * Test getServer().
     *
     * @return void.
     */
    public function testGetFiles()
    {

        // Create map.
        $map = $this->helper->_createMap();

        // Get files with no parameters.
        $files = $this->mapFileTable->getFiles($map->id, null, null);

        // Test the number of maps returned.
        $this->assertEquals(count($files), 5);

        // Confirm that the files belong to the map.
        foreach ($files as $file) {
            $this->assertEquals($file->map_id, $map->id);
        }

    }

    /**
     * Test addNewMapFile().
     *
     * @return void.
     */
    public function testAddNewMapFile()
    {

        // Create map.
        $map = $this->helper->_createMap();

        // Check map file count.
        $this->assertEquals(count(
            $this->mapFileTable->findBySql('map_id = ?', array($map->id))
        ), 5);

        // Mock an Omeka file.
        $omekaFile = new File;
        $omekaFile->id = 5;

        // Add a new file.
        $this->mapFileTable->addNewMapFile($map, $omekaFile);

        // Check map file count.
        $this->assertEquals(count(
            $this->mapFileTable->findBySql('map_id = ?', array($map->id))
        ), 6);

    }

    /**
     * Test numberOfFilesInMap().
     *
     * @return void.
     */
    public function testNumberOfFilesInMap()
    {

        // Create map.
        $map = $this->helper->_createMap();

        // Check map file count.
        $this->assertEquals($this->mapFileTable->numberOfFilesInMap($map->id), 5);

    }

    /**
     * Test deleteFile(), with delete Omeka files set to false.
     *
     * @return void.
     */
    public function testDeleteFileKeepOmekaFile()
    {

        // Create map.
        $map = $this->helper->_createMap();

        // Get files with no parameters.
        $files = $this->mapFileTable->getFiles($map->id, null, null);
        $file = $files[0];

        // Get table classes.
        $mapTable = $this->db->getTable('NeatlineMapsMap');
        $mapFiles = $this->db->getTable('NeatlineMapsMapFile');
        $files = $this->db->getTable('File');

        // Check record counts.
        $this->assertEquals($mapTable->count(), 1);
        $this->assertEquals($mapFiles->count(), 5);
        $this->assertEquals($files->count(), 5);

        // Delete the map but don't delete the files.
        $mapFiles->deleteFile($file->id, false, false);

        // Check record counts.
        $this->assertEquals($mapTable->count(), 1);
        $this->assertEquals($mapFiles->count(), 4);
        $this->assertEquals($files->count(), 5);

    }

    /**
     * Test deleteFile(), with delete Omeka files set to true.
     *
     * @return void.
     */
    public function testDeleteFileDeleteOmekaFile()
    {

        // Because the setup code for these tests just mocks Omeka files as
        // database objects (no real files), file delete is hard to test here
        // without actually copying dummy files into the testing install of
        // Omeka, which causes a lot of other problems. In lieu of that, this
        // code just calls the deleteMap() function with file delete set to
        // true, and listens for the expected Omeka error that gets thrown
        // when Omeka can't find a physical file to delete that corresponds
        // to the database record.
        $this->setExpectedException('Omeka_Storage_Exception');

        // Create map.
        $map = $this->helper->_createMap();

        // Get files with no parameters.
        $files = $this->mapFileTable->getFiles($map->id, null, null);
        $file = $files[0];

        // Get table classes.
        $mapTable = $this->db->getTable('NeatlineMapsMap');
        $mapFiles = $this->db->getTable('NeatlineMapsMapFile');
        $files = $this->db->getTable('File');

        // Check record counts.
        $this->assertEquals($mapTable->count(), 1);
        $this->assertEquals($mapFiles->count(), 5);
        $this->assertEquals($files->count(), 5);

        // Delete the map but don't delete the files.
        $mapFiles->deleteFile($file->id, true, false);

    }

}
