<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Installer and hook/filter dispatcher class.
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

class NeatlineMaps
{

    private static $_hooks = array(
        'install',
        'uninstall',
        'define_routes',
        'config_form',
        'config',
        'after_save_file',
        'public_theme_header',
        'admin_theme_header',
        'after_save_form_item',
        'public_append_to_items_show'
    );

    private static $_filters = array(
        'admin_navigation_main',
        'admin_items_form_tabs',
        'exhibit_builder_exhibit_display_item'
    );

    /**
     * Invoke addHooksAndFilters().
     *
     * @return void
     */
    public function __construct()
    {

        $this->_db = get_db();
        self::addHooksAndFilters();

    }

    /**
     * Iterate over hooks and filters, define callbacks.
     *
     * @return void
     */
    public function addHooksAndFilters()
    {

        foreach (self::$_hooks as $hookName) {
            $functionName = Inflector::variablize($hookName);
            add_plugin_hook($hookName, array($this, $functionName));
        }

        foreach (self::$_filters as $filterName) {
            $functionName = Inflector::variablize($filterName);
            add_filter($filterName, array($this, $functionName));
        }

    }

    /**
     * Hook callbacks:
     */

    /**
     * Install. Create _neatline_maps table, add new item type.
     *
     * @return void.
     */
    public function install()
    {

        $db = $this->_db;

        $db->query("
            CREATE TABLE IF NOT EXISTS `$db->NeatlineMap` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `file_id` int(10) unsigned,
                `item_id` int(10) unsigned,
                PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ");

        $historicMapItemType = array(
            'name' => NEATLINE_MAPS_MAP_ITEM_TYPE_NAME,
            'description' => 'Historical map with WMS service.'
        );

        $historicMapItemTypeMetadata =
            array(
                array(
                    'name' => 'Namespace',
                    'description' => 'The namespace that contains the map\'s layers.',
                    'data_type' => 'Tiny Text'
                ),
                array(
                    'name' => 'Namespace URL',
                    'description' => 'The URL associated with the map\'s namespace.',
                    'data_type' => 'Tiny Text'
                )
            );

        insert_item_type($historicMapItemType, $historicMapItemTypeMetadata);

    }

    /**
     * Uninstall. Drop the _neatline_maps table.
     *
     * @return void.
     */
    public function uninstall()
    {

        $db = $this->_db;
        $db->query("DROP TABLE IF EXISTS `$db->NeatlineMap`");

    }

    /**
     * Wire up the routes in routes.ini.
     *
     * @param object $router Router passed in by the front controller.
     *
     * @return void
     */
    public function defineRoutes($router)
    {

        $router->addConfig(new Zend_Config_Ini(NEATLINE_MAPS_PLUGIN_DIR .
            DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));

    }

    /**
     * Do config form.
     *
     * @return void
     */
    public function configForm()
    {

        include 'forms/config-form.php';

    }

    /**
     * Save the config form, add the new namespace to GeoServer if necessary.
     *
     * @return void
     */
    public function config()
    {

        // Get the form.
        $geoserver_url = $_POST['neatlinemaps_geoserver_url'];
        $geoserver_user = $_POST['neatlinemaps_geoserver_user'];
        $geoserver_password = $_POST['neatlinemaps_geoserver_password'];
        $geoserver_spatial_reference_service = $_POST['neatlinemaps_geoserver_spatial_reference_service'];

        $startingNamespacePrefix = get_option('neatlinemaps_geoserver_namespace_prefix');

        // Set options.
        set_option('neatlinemaps_geoserver_url',
            $geoserver_url);

        set_option('neatlinemaps_geoserver_user',
            $geoserver_user);

        set_option('neatlinemaps_geoserver_password',
            $geoserver_password);

        set_option('neatlinemaps_geoserver_spatial_reference_service',
            $geoserver_spatial_reference_service);

    }

    /**
     * Load the geoserver raster on file save.
     *
     * @return void
     */
    public function afterSaveFile($file)
    {

        if (!$this->_db->getTable('NeatlineMap')->fileHasNeatlineMap($file)) {

            if (_putFileToGeoServer($file, $file->getItem())) { // if GeoServer accepts the file...
                $item = $file->getItem();
                $this->_db->getTable('NeatlineMap')->addNewMap($item, $file);
            }

        }

    }

    /**
     * Include GeoServer JavaScript dependencies.
     *
     * @return void
     */
    public function publicThemeHeader()
    {

        $request = Zend_Controller_Front::getInstance()->getRequest();

        // Is there a way to hone in on the items module, more specifically
        // than using 'default' here?
        if ($request->getModuleName() == 'default' && $request->getActionName() == 'show') {
            _doHeaderJsAndCss();
        }

    }

    /**
     * Include the javascript for the add-files functionality in the maps item tab.
     *
     * @return void
     */
    public function adminThemeHeader()
    {

        $request = Zend_Controller_Front::getInstance()->getRequest();

        if ($request->getModuleName() == 'default' &&
            ($request->getActionName() == 'edit') ||
            ($request->getActionName() == 'add')) {

            _doAdminHeaderJsAndCss();

        }

    }

    /**
     * Save the new map files.
     *
     * @return void
     */
    public function afterSaveFormItem($record, $post)
    {

        $itemType = $this->_db->getTable('ItemType')->find($record->item_type_id);

        // If the saved record is a Historical Map...
        if ($itemType->name == NEATLINE_MAPS_MAP_ITEM_TYPE_NAME) {

            $namespace = $record->getElementTextsByElementNameAndSetName('Namespace', 'Item Type Metadata');
            $namespace = $namespace[0]->text;

            $namespaceURL = $record->getElementTextsByElementNameAndSetName('Namespace URL', 'Item Type Metadata');
            $namespaceURL = $namespaceURL[0]->text;

            // ...then dial out to see whether the defined namespace needs to be added.
            _createGeoServerNamespace(
                get_option('neatlinemaps_geoserver_url'),
                $namespace,
                get_option('neatlinemaps_geoserver_user'),
                get_option('neatlinemaps_geoserver_password'),
                $namespaceURL
            );

        }

        // Were map files posted from the form?
        if (isset($_FILES['map'])) {

            $files = insert_files_for_item(
                $record,
                'Upload',
                'map',
                array('ignoreNoFile'=>true));

            // Throw each of the files at GeoServer and see if it accepts them.
            foreach ($files as $file) {

                if (!$this->_db->getTable('NeatlineMap')->fileHasNeatlineMap($file)) {

                    if (_putFileToGeoServer($file, $record)) { // if GeoServer accepts the file...
                        $this->_db->getTable('NeatlineMap')->addNewMap($item, $file);
                    }

                    else {
                        $file->delete();
                    }

                }

            }

        }

        // Do deletes.
        foreach ($post['delete_maps'] as $key => $id) {

            $neatlineMap = $this->_db->getTable('NeatlineMap')->find($id);
            $file = $neatlineMap->getFile();

            $neatlineMap->delete();
            $file->delete();

        }

        // Check to see if any of the marked-for-deletion files in the normal Files tab
        // is also a map, and if so, delete the record in _neatline_maps.
        foreach ($post['delete_files'] as $key => $id) {

            $neatlineMap = $this->_db->getTable('NeatlineMap')->findBySql('file_id = ?', array($id));

            if ($neatlineMap[0]) {
                $neatlineMap[0]->delete();
            }

        }

    }

    /**
     * Show the map layers on the item page.
     *
     * @return void
     */
    public function publicAppendToItemsShow()
    {

        $item = get_current_item();

        // Does the item have at least one map file attached to it?
        if ($this->_db->getTable('NeatlineMap')->itemHasNeatlineMap($item)) {

            // If so, construct the map class, which takes care of the preparation and rendering.
            $geoserverMap = new GeoserverMap_Item($item);

        }

    }

    /**
     * Filter callbacks:
     *

    /**
     * Add link to main admin menu bar.
     *
     * @param array $tabs This is an array of label => URI pairs.
     *
     * @return array The tabs array with the Neatline Maps tab.
     */
    public function adminNavigationMain($tabs)
    {

        $tabs['Neatline Maps'] = uri('neatline-maps');
        return $tabs;

    }

    /**
     * Add Neatline Maps tab to the Items interface.
     *
     * @param array $tabs An array mapping tab name to HTML for that tab.
     *
     * @return array The $tabs array updated with the Neatline Maps tab.
     */
    public function adminItemsFormTabs($tabs)
    {

        $item = get_current_item();
        $tabs['Neatline Maps'] = _doItemForm($item);

        return $tabs;

    }

    /**
     * Render the map on Exhibit pages.
     *
     * @return void
     */
    public function exhibitBuilderExhibitDisplayItem()
    {



    }

}
