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
        'install'
        // 'define_routes',
        // 'config_form',
        // 'config',
        // 'after_save_file',
        // 'public_theme_header'
    );

    private static $_filters = array(
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

        // add neatline/geoserver namespace
        // add 'historic map' item type

        $historicMapItemType = array(
            'name' => 'Historical Map',
            'description' => 'Historical map with accompanying WMS service.'
        );

        $historicMapItemTypeMetadata =
            array(
                array(
                    'name' => 'Service Address',
                    'description' => 'The address of the map\'s WMS server.'
                ),
                array(
                    'name' => 'Layer Name',
                    'description' => 'The WMS name of the map.'
                )
            );

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

        $router->addConfig(new Zend_Config_Ini(FEDORA_CONNECTOR_PLUGIN_DIR .
            DIRECTORY_SEPARATOR . 'routes.ini', 'routes'));

    }

    /**
     * Establish access privilges.
     *
     * @param Omeka_Acl $acl The ACL instance controlling the access list.
     *
     * @return void
     */
    public function defineAcl($acl)
    {

        // if (version_compare(OMEKA_VERSION, '2.0-dev', '<')) {
        //     $serversResource = new Omeka_Acl_Resource('ThePlugin_ActionSuite');
        // } else {
        //     $serversResource = new Zend_Acl_Resource('ThePlugin_ActionSuite');
        // }

        // $acl->add($serversResource);
        // $acl->add($datastreamsResource);

        // $acl->allow('super', 'ThePlugin_ActionSuite');
        // $acl->allow('super', 'ThePlugin_ActionSuite');

    }

    /**
     * Do config form.
     *
     * @return void
     */
    public function configForm()
    {

        include 'config_form.php';

    }

    /**
     * Save the config form.
     *
     * @return void
     */
    public function config()
    {

        // set_option('the_plugin_the_option', $_POST['the_input_name']);

    }

    /**
     * Load the geoserver raster on file save.
     *
     * @return void
     */
    public function afterSaveFile()
    {



    }

    /**
     * Include GeoServer JavaScript dependencies.
     *
     * @return void
     */
    public function publicThemeHeader()
    {



    }

    /**
     * Filter callbacks:
     */

    /**
     * Render the map.
     *
     * @return void
     */
    public function exhibitBuilderExhibitDisplayItem()
    {



    }

}
