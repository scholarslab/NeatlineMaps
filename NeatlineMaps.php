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
        'after_save_form_record'
    );

    private static $_filters = array(
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
        $geoserver_namespace_prefix = $_POST['neatlinemaps_geoserver_namespace_prefix'];
        $geoserver_namespace_url = $_POST['neatlinemaps_geoserver_namespace_url'];
        $geoserver_user = $_POST['neatlinemaps_geoserver_user'];
        $geoserver_password = $_POST['neatlinemaps_geoserver_password'];
        $geoserver_spatial_reference_service = $_POST['neatlinemaps_geoserver_spatial_reference_service'];
        $geoserver_tag_prefix = $_POST['neatlinemaps_geoserver_tag_prefix'];

        $startingNamespacePrefix = get_option('neatlinemaps_geoserver_namespace_prefix');

        // Set options.
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

        $clientCheckNamespace = new Zend_Http_Client($geoServerNamespaceCheck);
        $clientCheckNamespace->setAuth($geoserver_user, $geoserver_password);

        // Does the namespace already exist?
        if (strpos(
                $clientCheckNamespace->request(Zend_Http_Client::GET)->getBody(),
                'No such namespace:'
        ) !== false) {

            $namespaceJSON = '
                {
                    "namespace": {
                        "prefix": "' . $geoserver_namespace_prefix . '",
                        "uri": "' . $geoserver_namespace_url . '"
                    }
                }
            ';

            $ch = curl_init($geoServerConfigurationAddress);
            curl_setopt($ch, CURLOPT_POST, True);

            $authString = $geoserver_user . ':' . $geoserver_password;
            curl_setopt($ch, CURLOPT_USERPWD, $authString);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $namespaceJSON);

            $successCode = 201;
            $buffer = curl_exec($ch);

            // How to thread in custom error/success flashes to the front controller here?

            // $info = curl_getinfo($ch);

            // if ($info['http_code'] != $successCode) {

            //     if ($startingNamespacePrefix == null) { // This is the first save...
            //         $msgStr = 'There was an error - the namespace \'' .
            //             $geoserver_namespace_prefix . '\' was not added.';
            //         $controller->flashError($msgStr);
            //     }

            //     else if ($startingNamespacePrefix) {
            //         $msgStr = 'There was an error - the new namespace \'' .
            //             $geoserver_namespace_prefix . '\' was not added.';
            //         $controller->flashError($msgStr);
            //     }

            // } else {

            //     $msgStr = 'New namespace ' . $geoserver_namespace_prefix . ' successfully added.';
            //     $controller->flashSuccess($mstStr);

            // }

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
    public function afterSaveFormRecord($record, $post)
    {

        // Try to add the new maps to geoserver.
        if (!empty($_FILES['map'])) {

            $file = insert_files_for_item(
                $record,
                'Upload',
                'map',
                array('ignoreNoFile'=>true));

            // Does GeoServer recognize the file as a map?
            $zip = new ZipArchive();
            $zipfilename = ARCHIVE_DIR . '/' . $file->archive_filename . '.zip';
            $zip->open($zipfilename, ZIPARCHIVE::CREATE);
            $zip->addFile(ARCHIVE_DIR . '/files/' . $file->archive_filename, $file->archive_filename);
            $zip->close();

            // do the rest...

            // if ([>geoserveracceptsthefile<]) { // if geoserver accepts the file...

            //     $neatlineMap = new NeatlineMap();
            //     $neatlineMap->item_id = $record->id;
            //     $neatlineMap->file_id = $file[0]->id;
            //     $neatlineMap->save();

            // }

            // else {
            //     $file->delete();
            // }

        }

        // Do deletes.
        foreach ($post['delete_maps'] as $id) {

            $neatlineMap = $this->_db->getTable('NeatlineMap')->find($id);
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
