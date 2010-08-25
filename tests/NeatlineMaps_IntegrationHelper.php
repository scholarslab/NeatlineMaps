<?php
/**
 * @author    "Scholars Lab"
 * @version   SVN: $Id$
 * @copyright 2010 The Board and Visitors of the University of Virginia
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @link      http://github.com/scholarslab/bagit
 */

class NeatlineMaps_IntegrationHelper {
    const PLUGIN_NAME = 'NeatlineMaps';

    public function setUpPlugin()
    {
        $pluginHelper = new Omeka_Test_Helper_Plugin;
        $this->_addPluginHooksAndFilters($pluginHelper->pluginBroker,
                self::PLUGIN_NAME);
    }

    public function _addPluginHooksAndFilters($pluginBroker, $pluginName)
    {
         // Set the current plugin so the add_plugin_hook function works
        $pluginBroker->setCurrentPluginDirName($pluginName);

        add_plugin_hook('install', 'neatlinemaps_install');
        add_plugin_hook('uninstall', 'neatlinemaps_uninstall');
        add_plugin_hook('define_routes', 'neatlinemaps_routes');
        add_plugin_hook('after_upload_file', 'load_geoserver_raster');
        add_plugin_hook('public_append_to_items_show', 'neatlinemaps_widget');
        add_plugin_hook('public_theme_header', 'neatlinemaps_header');

        // add plugin filters
        add_filter("show_item_in_page","neatlinemaps_show_item_in_page");
        add_filter(array('Form','Item','Item Type Metadata','Background'),
            "neatlinemaps_background_widget");

    }

    public function createNewItem($isPublic = true, $title = 'Test Map',
            $titleIsHtml = false)
    {
        // grab a map file from maps
        $filename = '';

        $item = insert_item(
                array ('public' => $isPublic
            ),
                array (
                'Dublin Core' => array (
                    'Title' => array (
                        array('text' => $title, 'html' => $titleIsHtml)
                    )
                )
            ),
                array (
                'item_type_name' => 'Historical Map'
            ),
                array('file_transfer_type' => 'Filesystem', 'files' => $filename)
        );
        
        return $item;

    }
}
?>
