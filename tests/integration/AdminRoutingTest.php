<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Interface tests.
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

class BagIt_AdminRoutingTest extends Omeka_Test_AppTestCase
{

    public function setUp()
    {

        parent::setUp();
        $this->helper = new BagIt_Test_AppTestCase;
        $this->helper->setUpPlugin();

    }

    public function testCanBrowseCollections()
    {

        $this->dispatch('bag-it/collections/browse');
        $this->assertModule('bag-it');
        $this->assertController('collections');
        $this->assertAction('browse');

    }

    public function testIndexRedirectWorks()
    {

        $this->dispatch('bag-it');
        $this->assertModule('bag-it');
        $this->assertController('collections');
        $this->assertAction('browse');

    }

    public function testCanViewImportInterface()
    {

        $this->dispatch('bag-it/collections/import');
        $this->assertModule('bag-it');
        $this->assertController('collections');
        $this->assertAction('import');

    }

    public function testImportRoute()
    {

        $this->dispatch('bag-it/import');
        $this->assertModule('bag-it');
        $this->assertController('collections');
        $this->assertAction('import');

    }

    public function testBrowseCollectionRoute()
    {

        $this->helper->_createFileCollections(1);
        $this->dispatch('bag-it/collections/1');
        $this->assertModule('bag-it');
        $this->assertController('collections');
        $this->assertAction('browsecollection');

    }

    public function testDeleteCollectionRoute()
    {

        $this->helper->_createFileCollections(1);
        $this->dispatch('bag-it/collections/1/delete');
        $this->assertModule('bag-it');
        $this->assertController('collections');
        $this->assertAction('deletecollection');

    }

    public function testAddFilesRoute() {

        $this->helper->_createFileCollections(1);
        $this->dispatch('bag-it/collections/1/add');
        $this->assertModule('bag-it');
        $this->assertController('collections');
        $this->assertAction('addfiles');

    }

}
