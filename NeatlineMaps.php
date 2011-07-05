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
 * @subpackage  theplugin
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
        'config',
        'congig_form'
        // Other hooks.
    );

    private static $_filters = array(
        'admin_navigation_main'
        // Other filters.
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
     * Install.
     *
     * @return void
     */
    public function install()
    {

        $db = get_db();

        // $db->query("
        //     CREATE TABLE IF NOT EXISTS `$db->ThePlugintable` (
        //         `id` int(10) unsigned NOT NULL auto_increment,
        //         `int_field` int(10) unsigned,
        //         `text_field` tinytext collate utf8_unicode_ci,
        //         PRIMARY KEY  (`id`)
        //     ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
        //     ");

        // set_option('a_plugin_option', 'the_plugin_option');

    }

    /**
     * Uninstall.
     *
     * @return void
     */
    public function uninstall()
    {

        $db = get_db();

        // $db->query("DROP TABLE IF EXISTS `$db->ThePluginTable`");
        // $db->query("DROP TABLE IF EXISTS `$db->ThePluginTable`");

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
     * Add link to main admin menu bar.
     *
     * @param array $tabs This is an array of label => URI pairs.
     *
     * @return array The tabs array passed in with the new link.
     */
    public function adminNavigationMain($tabs)
    {

        // if (has_permission('ThePlugin_ActionSuite', 'index')) {
        //     $tabs['The Plugin'] = uri('the-plugin/defaultaction');
        // }

        return $tabs;

    }

}
