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
        'define_routes',
        'config_form',
        'config',
        'after_save_file',
        'public_theme_header',
        'admin_theme_header',
        'after_save_form_record'
    );

    private static $_filters = array(
        'admin_items_form_tabs',
        'exhibit_builder_exhibit_display_item'
    );

    private $_db;

    /**
     * Invoke addHooksAndFilters().
     *
     * @return void
     */
    public function __construct()
    {

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
     * Install.
     *
     * @return void
     */
    public function install()
    {

        $db = get_db();
        $db->query("
            CREATE TABLE IF NOT EXISTS `$db->NeatlineMap` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `item_id` int(10) unsigned,
                `file_id` int(10) unsigned,
                PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ");

        $historicMapItemType = array(
            'name' => NEATLINE_MAPS_MAP_ITEM_TYPE_NAME,
            'description' => 'Historical map with accompanying WMS service.'
        );

        $historicMapItemTypeMetadata =
            array(
                array(
                    'name' => 'Service Address',
                    'description' => 'The address of the map\'s WMS server.',
                    'data_type' => 'Tiny Text'
                ),
                array(
                    'name' => 'Layer Name',
                    'description' => 'The WMS name of the map.',
                    'data_type' => 'Tiny Text'
                )
            );

        insert_item_type($historicMapItemType, $historicMapItemTypeMetadata);

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

        $geoserver_url = $_POST['neatlinemaps_geoserver_url'];
        $geoserver_namespace_prefix = $_POST['neatlinemaps_geoserver_namespace_prefix'];
        $geoserver_namespace_url = $_POST['neatlinemaps_geoserver_namespace_url'];
        $geoserver_user = $_POST['neatlinemaps_geoserver_user'];
        $geoserver_password = $_POST['neatlinemaps_geoserver_password'];
        $geoserver_spatial_reference_service = $_POST['neatlinemaps_geoserver_spatial_reference_service'];
        $geoserver_tag_prefix = $_POST['neatlinemaps_geoserver_tag_prefix'];

        set_option('neatlinemaps_geoserver_url',
            $geoserver_url);

        set_option('neatlinemaps_geoserver_namespace_prefix',
            $geoserver_namespace_prefix);

        set_option('neatlinemaps_geoserver_namespace_url',
            $geoserver_namespace_url);

        set_option('neatlinemaps_geoserver_user',
            $geoserver_user);

        set_option('neatlinemaps_geoserver_password',
            $geoserver_password);

        set_option('neatlinemaps_geoserver_spatial_reference_service',
            $geoserver_spatial_reference_service);

        set_option('neatlinemaps_geoserver_tag_prefix',
            $geoserver_tag_prefix);

        // Set up curl to dial out to GeoServer.
        $geoServerConfigurationAddress = $geoserver_url . '/rest/namespaces';
        $geoServerNamespaceCheck = $geoServerConfigurationAddress . '/' . $geoserver_namespace_prefix;

        $client = new Zend_Http_Client($geoServerConfigurationAddress);
        $clientCheckNamespace = new Zend_Http_Client($geoServerNamespaceCheck);

        $client->setAuth($geoserver_user, $geoserver_password);
        $clientCheckNamespace->setAuth($geoserver_user, $geoserver_password);

        // Does the namespace already exist?
        if (strpos(
                $clientCheckNamespace->request(Zend_Http_Client::GET)->getBody(),
                'No such namespace:'
        ) !== false) {

            // If not, create it.
            // $namespaceJSON = '
            //     {
            //         "namespaces": {
            //             "namespace": [{
            //                 "prefix": "' . $geoserver_namespace_prefix . '",
            //                 "uri": "' . $geoserver_namespace_url . '"
            //             }]
            //         }
            //     }
            // ';

            // $namespaceJSON = '
            //     {
            //         "namespace": {
            //             "prefix": "' . $geoserver_namespace_prefix . '",
            //             "uri": "' . $geoserver_namespace_url . '"
            //         }
            //     }
            // ';

            // $namespaceXML = '
            //     <namespace><prefix>' . $geoserver_namespace_prefix . '</prefix></namespace>
            // ';

            // Namespace add is NOT working. Always get HTTP500 'Internal Server Error.' What gives?

            $response = $client->setRawData($namespaceXML, 'application/xml')->request(Zend_Http_Client::POST);

        }

    }

    /**
     * Load the geoserver raster on file save.
     *
     * @return void
     */
    public function afterSaveFile($file)
    {



    }

    /**
     * Include GeoServer JavaScript dependencies.
     *
     * @return void
     */
    public function publicThemeHeader()
    {

        $request = Zend_Controller_Front::getInstance()->getRequest();

        if ($request->getModuleName() == 'neatline-maps' && $request->getActionName() == 'show') {
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

        if ($request->getModuleName() == 'default' && $request->getActionName() == 'edit') {
            _doAdminHeaderJsAndCss();
        }

    }

    /**
     * Save the new map files.
     *
     * @return void
     */
    public function afterSaveFormRecord($record, $post)
    {

        // Try to add the new maps to geoserver.
        // Delete maps marked for deletion.

        $db = get_db();

        // Do deletes.
        foreach ($post['delete_maps'] as $id) {

            $neatlineMap = $db->getTable('NeatlineMap')->find($id);
            $file = $neatlineMap->getFile();
            $neatlineMap->delete();
            $file->delete();

        }

    }

    /**
     * Filter callbacks:
     */

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
