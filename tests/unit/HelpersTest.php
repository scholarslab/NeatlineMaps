<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Unit tests for helper functions.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package omeka
 * @subpackage BagIt
 * @author Scholars' Lab
 * @author David McClure (david.mcclure@virginia.edu)
 * @copyright 2011
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 * PHP version 5
 *
 */
?>

<?php

class BagIt_HelpersTest extends Omeka_Test_AppTestCase
{

    public function setUp()
    {

        parent::setUp();
        $this->helper = new BagIt_Test_AppTestCase;
        $this->helper->setUpPlugin();
        $this->db = get_db();

    }

    public function testBagithelpersTestForFiles()
    {

        $this->assertEquals(bagithelpers_testForFiles(), false);
        $this->helper->_createItem('Testing Item');
        $this->helper->_createFileCollections(1);
        $this->helper->_createFiles();
        $this->assertEquals(get_db()->getTable('File')->count(), 13);

    }

    public function testBagithelpersGetFileKb()
    {

        $this->assertEquals(bagithelpers_getFileKb(5000), 4.88);

    }

    public function testBagithelpersColumnSorting()
    {

        $this->assertEquals(bagithelpers_doColumnSortProcessing('name', ''), 'name DESC');
        $this->assertEquals(bagithelpers_doColumnSortProcessing('name', 'd'), 'name DESC');
        $this->assertEquals(bagithelpers_doColumnSortProcessing('name', 'a'), 'name ASC');

    }

}
