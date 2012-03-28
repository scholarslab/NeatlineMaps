<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/** {{{docblock
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
 *}}}
 */

class NeatlineMapsPlugin
{

    private static $_hooks = array(
        'install',
        'define_routes',
        'public_theme_header',
        'admin_theme_header',
        'public_append_to_items_show',
        'admin_append_to_items_show_primary'
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

        add_mime_display_type(
            array('image/tiff', 'image/tif'),
            array($this, 'filterNeatlineMapsFiles')
        );

    }

    // {{{ hookcallbacks
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
            CREATE TABLE IF NOT EXISTS `$db->NeatlineMapsMap` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `item_id` int(10) unsigned,
                `server_id` int(10) unsigned,
                `name` tinytext collate utf8_unicode_ci,
                `namespace` tinytext collate utf8_unicode_ci,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `$db->NeatlineMapsMapFile` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `file_id` int(10) unsigned,
                `map_id` int(10) unsigned,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ");

        $db->query("
            CREATE TABLE IF NOT EXISTS `$db->NeatlineMapsServer` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `name` tinytext collate utf8_unicode_ci,
                `url` tinytext collate utf8_unicode_ci,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ");

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
            _queueOpenLayers();
        }

    }

    /**
     * Include extra .js and .css in admin header.
     *
     * @return void
     */
    public function adminThemeHeader()
    {

        $request = Zend_Controller_Front::getInstance()->getRequest();

        // Queue the neatline admin stylesheet.
        if (in_array($request->getModuleName(), array('default', 'neatline-maps'))) {
            _queueAdminCss();
        }

        // Queue OpenLayers.js.
        if ($request->getModuleName() == 'neatline-maps' || ($request->getControllerName() == 'items' && $request->getActionName() == 'show')) {
            _queueOpenLayers();
        }

    }

    /**
     * Show the map layers on the public items/show view.
     *
     * @return void.
     */
    public function publicAppendToItemsShow()
    {
        echo neatline_maps_display_maps_for_item();
    }
    // }}}

    /**
     * Show the map layers on the admin items/show view.
     *
     * @return void.
     */
    public function adminAppendToItemsShowPrimary()
    {
        echo neatline_maps_display_maps_for_item();
    }

    // {{{ filtercallbacks

    /**
     * Filter callbacks:
     */

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

        if (isset($item->id)) {
            $tabs['Neatline Maps'] = _doItemForm($item);
        }

        return $tabs;

    }

    /**
     * Render the map on Exhibit pages.
     *
     * @return void
     */
    public function exhibitBuilderExhibitDisplayItem($html, $displayFilesOptions, $linkOptions, $item)
    {

        $mapsHtml = neatline_maps_display_maps_for_item($item);

        // neatline_maps_display_maps_for_item() returns empty string if there are no maps for the item.
        if ($mapsHtml != '') {
            _queueOpenLayers();
            $html = $mapsHtml;
        }

        return $html;

    }

  //}}}

    /**
     * Removes display of NeatlineMaps geotiffs from Omeka_View_Helper_Media,
     * for display elsewhere.
     */
    public function filterNeatlineMapsFiles($file)
    {

        // Get out the Neatline Map record.
        $mapFile = get_db()->getTable('NeatlineMapsMapFile')
            ->findBy(array('file_id' => $file->id));

        // If a map is present, scrub out the display markup.
        if ($mapFile) { $html = ''; }

        return $html;

    }
}

/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */
