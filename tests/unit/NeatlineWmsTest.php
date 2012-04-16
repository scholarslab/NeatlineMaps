<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Edition row tests.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class NLMAPS_NeatlineWmsTest extends NLMAPS_Test_AppTestCase
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
     * Test get and set on columns.
     *
     * @return void.
     */
    public function testAttributeAccess()
    {

        // Create a record.
        $wms = new NeatlineWms();

        // Set.
        $wms->item_id = 1;
        $wms->address = 'http://test.edu:8080/geoserver/ws/wms';
        $wms->layers = 'ws:test1,ws:test2';
        $wms->save();

        // Re-get the edition object.
        $wms = $this->wmsTable->find($wms->id);

        // Get.
        $this->assertEquals($wms->item_id, 1);
        $this->assertEquals($wms->address, 'http://test.edu:8080/geoserver/ws/wms');
        $this->assertEquals($wms->layers, 'ws:test1,ws:test2');

    }

    /**
     * Test foreign key assignment when item is passed on instantiation.
     *
     * @return void.
     */
    public function testConstructKeyAssignments()
    {

        // Create item.
        $item = $this->__item();

        // Create a record.
        $wms = new NeatlineWms($item);

        // Check.
        $this->assertEquals($wms->item_id, $item->id);

    }

    /**
     * getItem() should return the parent item.
     *
     * @return void.
     */
    public function testGetItem()
    {

        // Create item.
        $item = $this->__item();

        // Create a record.
        $wms = new NeatlineWms($item);

        // Check ids.
        $this->assertEquals($wms->getItem()->id, $item->id);

    }

}
