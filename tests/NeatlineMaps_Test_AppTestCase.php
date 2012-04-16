<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Testing helper class.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2011 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

require_once '../NeatlineMapsPlugin.php';
require_once 'mocks/FileMock.php';

class NLMAPS_Test_AppTestCase extends Omeka_Test_AppTestCase
{

    private $_dbHelper;

    /**
     * Spin up the plugins and prepare the database.
     *
     * @return void.
     */
    public function setUpPlugin()
    {

        parent::setUp();

        $this->user = $this->db->getTable('User')->find(1);
        $this->_authenticateUser($this->user);
        $this->wmsTable = $this->db->getTable('NeatlineWms');
        $this->serversTable = $this->db->getTable('NeatlineMapsServer');

        // Set up Neatline WMS.
        $plugin_broker = get_plugin_broker();
        $this->_addHooksAndFilters($plugin_broker, 'NeatlineMaps');
        $plugin_helper = new Omeka_Test_Helper_Plugin;
        $plugin_helper->setUp('NeatlineMaps');

        $this->_dbHelper = Omeka_Test_Helper_Db::factory($this->core);

    }

    /**
     * Install Neatline WMS.
     *
     * @return void.
     */
    public function _addHooksAndFilters($plugin_broker, $plugin_name)
    {
        $plugin_broker->setCurrentPluginDirName($plugin_name);
        new NeatlineMapsPlugin;
    }


    /**
     * Test helpers.
     */


    /**
     * Create an item.
     *
     * @return Omeka_record $item The item.
     */
    public function __item()
    {
        $item = new Item;
        $item->save();
        return $item;
    }

    /**
     * Create a service.
     *
     * @return Omeka_record $service The service.
     */
    public function __service(
        $item=null,
        $address='http://test/wms',
        $layers='test:layer1')
    {

        // If no item, create one.
        if (is_null($item)) {
            $item = $this->__item();
        }

        $service = new NeatlineWms($item);
        $service->address = $address;
        $service->layers = $layers;
        $service->save();

        return $service;

    }

    /**
     * Create an element text for an item.
     *
     * @param Omeka_record $item The item.
     * @param string $elementSet The element set.
     * @param string $elementName The element name.
     * @param string $value The value for the text.
     *
     * @return Omeka_record $text The new text.
     */
    public function __text(
        $item,
        $elementSet,
        $elementName,
        $value)
    {

        // Get tables.
        $_db = get_db();
        $elementTable = $_db->getTable('Element');
        $elementTextTable = $_db->getTable('ElementText');
        $recordTypeTable = $_db->getTable('RecordType');

        // Fetch element record and the item type id.
        $element = $elementTable->findByElementSetNameAndElementName($elementSet, $elementName);
        $itemTypeId = $recordTypeTable->findIdFromName('Item');

        $text = new ElementText;
        $text->record_id = $item->id;
        $text->record_type_id = $itemTypeId;
        $text->element_id = $element->id;
        $text->text = $value;
        $text->save();

        return $text;

    }

    /**
     * Create a server.
     *
     * @return Omeka_record $server The server.
     */
    public function __server(
        $name='Test Server',
        $url='http://test/wms',
        $namespace='workspace',
        $username='admin',
        $password='geoserver',
        $active=1)
    {

        $server = new NeatlineMapsServer;
        $server->name = $name;
        $server->url = $url;
        $server->namespace = $namespace;
        $server->username = $username;
        $server->password = $password;
        $server->active = $active;
        $server->save();

        return $server;

    }

    /**
     * Mock a file.
     *
     * @return StdClass $file The mock.
     */
    public function __file(
        $item=null,
        $originalFilename='test.tif')
    {

        // If no item, create one.
        if (is_null($item)) {
            $item = $this->__item();
        }

        $file = new FileMock;
        $file->item_id = $item->id;
        $file->original_filename = $originalFilename;

        return $file;

    }

}
