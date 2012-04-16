<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Index controller integration tests.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class NLMAPS_IndexControllerTest extends NLMAPS_Test_AppTestCase
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
     * There should be a web map service tab in the item add form.
     *
     * @return void.
     */
    public function testItemAddTab()
    {

        // Hit item add.
        $this->dispatch('items/add');

        // Check for tab.
        $this->assertXpathContentContains(
            '//ul[@id="section-nav"]/li/a[@href="#web-map-service-metadata"]',
            'Web Map Service'
        );

        // Check for textareas.
        $this->assertXpath('//textarea[@id="address"][@name="address"]');
        $this->assertXpath('//textarea[@id="layers"][@name="layers"]');

    }

    /**
     * There should be a web map service tab in the item edit form.
     *
     * @return void.
     */
    public function testItemEditTab()
    {

        // Create item.
        $item = new Item();
        $item->save();

        // Hit item edit.
        $this->dispatch('items/edit/' . $item->id);

        // Check for tab.
        $this->assertXpathContentContains(
            '//ul[@id="section-nav"]/li/a[@href="#web-map-service-metadata"]',
            'Web Map Service'
        );

        // Check for textareas.
        $this->assertXpath('//textarea[@id="address"][@name="address"]');
        $this->assertXpath('//textarea[@id="layers"][@name="layers"]');

    }

    /**
     * If there is an existing service for the item, the data should be
     * populated in the textareas.
     *
     * @return void.
     */
    public function testItemEditData()
    {

        // Create item and service.
        $item = $this->__item();
        $service = $this->__service($item, 'address', 'layers');

        // Hit item edit.
        $this->dispatch('items/edit/' . $item->id);

        // Check for populated form.
        $this->assertXpathContentContains(
            '//textarea[@id="address"][@name="address"]',
            'address'
        );
        $this->assertXpath(
            '//textarea[@id="layers"][@name="layers"]',
            'layers'
        );

    }

    /**
     * When an item is added and service data is entered, the service should
     * be created.
     *
     * @return void.
     */
    public function testServiceCreationOnItemAdd()
    {

        // Capture starting count.
        $count = $this->wmsTable->count();

        // Set exhibit id.
        $this->request->setMethod('POST')
            ->setPost(array(
                'public' => 1,
                'featured' => 0,
                'Elements' => array(),
                'order' => array(),
                'address' => 'address',
                'layers' => 'layers'
            )
        );

        // Hit item edit.
        $this->dispatch('items/add');

        // +1 editions.
        $this->assertEquals($this->wmsTable->count(), $count+1);

        // Get out service and check.
        $service = $this->wmsTable->find(1);
        $this->assertEquals($service->address, 'address');
        $this->assertEquals($service->layers, 'layers');

    }

    /**
     * When an item is edited and service data is entered, the service should
     * be created.
     *
     * @return void.
     */
    public function testServiceCreationOnItemEdit()
    {

        // Create item.
        $item = $this->__item();

        // Capture starting count.
        $count = $this->wmsTable->count();

        // Set exhibit id.
        $this->request->setMethod('POST')
            ->setPost(array(
                'public' => 1,
                'featured' => 0,
                'Elements' => array(),
                'order' => array(),
                'address' => 'address',
                'layers' => 'layers'
            )
        );

        // Hit item edit.
        $this->dispatch('items/edit/' . $item->id);

        // +1 editions.
        $this->assertEquals($this->wmsTable->count(), $count+1);

        // Get out service and check.
        $service = $this->wmsTable->find(1);
        $this->assertEquals($service->address, 'address');
        $this->assertEquals($service->layers, 'layers');

    }

    /**
     * When an item is edited and service data is changed, the service should
     * be updated.
     *
     * @return void.
     */
    public function testServiceUpdateOnItemEdit()
    {

        // Create item and service.
        $item = $this->__item();
        $service = $this->__service($item, 'address1', 'layers1');

        // Capture starting count.
        $count = $this->wmsTable->count();

        // Set exhibit id.
        $this->request->setMethod('POST')
            ->setPost(array(
                'public' => 1,
                'featured' => 0,
                'Elements' => array(),
                'order' => array(),
                'address' => 'address2',
                'layers' => 'layers2'
            )
        );

        // Hit item edit.
        $this->dispatch('items/edit/' . $item->id);

        // +1 editions.
        $this->assertEquals($this->wmsTable->count(), $count);

        // Get out service and check.
        $service = $this->wmsTable->find(1);
        $this->assertEquals($service->address, 'address2');
        $this->assertEquals($service->layers, 'layers2');

    }

    /**
     * When an item is edited and service data is deleted, the service should
     * be deleted.
     *
     * @return void.
     */
    public function testServiceDeleteOnItemEdit()
    {

        // Create item and service.
        $item = $this->__item();
        $service = $this->__service($item, 'address1', 'layers1');

        // Capture starting count.
        $count = $this->wmsTable->count();

        // Set exhibit id.
        $this->request->setMethod('POST')
            ->setPost(array(
                'public' => 1,
                'featured' => 0,
                'Elements' => array(),
                'order' => array(),
                'address' => '',
                'layers' => ''
            )
        );

        // Hit item edit.
        $this->dispatch('items/edit/' . $item->id);

        // +1 editions.
        $this->assertEquals($this->wmsTable->count(), $count-1);

        // Check for no service.
        $this->assertFalse($this->wmsTable->findByItem($item));


    }

}
