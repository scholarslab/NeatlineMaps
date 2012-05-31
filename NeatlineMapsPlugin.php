<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Plugin runner.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */


class NeatlineMapsPlugin
{

    // Hooks.
    private static $_hooks = array(
        'install',
        'uninstall',
        'define_routes',
        'after_save_form_item',
        'after_insert_file',
        'admin_append_to_items_show_primary',
        'public_append_to_items_show',
        'before_delete_item',
        'public_theme_header',
        'admin_theme_header'
    );

    private static $_filters = array(
        'admin_navigation_main',
        'admin_items_form_tabs'
    );

    /**
     * Get database, add hooks and filters.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_db = get_db();
        $this->servicesTable = $this->_db->getTable('NeatlineMapsService');
        $this->serversTable = $this->_db->getTable('NeatlineMapsServer');
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
     * @return void.
     */
    public function install()
    {

        // Servers table.
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}neatline_maps_servers` (
                `id`              int(10) unsigned not null auto_increment,
                `name`            tinytext collate utf8_unicode_ci,
                `url`             tinytext collate utf8_unicode_ci,
                `username`        tinytext collate utf8_unicode_ci,
                `password`        tinytext collate utf8_unicode_ci,
                `namespace`       tinytext collate utf8_unicode_ci,
                `active`          tinyint(1) NOT NULL,
                 PRIMARY KEY (`id`)
               ) ENGINE=innodb DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $this->_db->query($sql);

        // Web map services table.
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->_db->prefix}neatline_maps_services` (
                `id`              int(10) unsigned not null auto_increment,
                `item_id`         int(10) unsigned unique,
                `address`         text collate utf8_unicode_ci,
                `layers`          text collate utf8_unicode_ci,
                 PRIMARY KEY (`id`)
               ) ENGINE=innodb DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

        $this->_db->query($sql);

    }

    /**
     * Uninstall.
     *
     * @return void.
     */
    public function uninstall()
    {

        // Drop the servers table.
        $sql = "DROP TABLE IF EXISTS `{$this->_db->prefix}neatline_maps_servers`";
        $this->_db->query($sql);

        // Drop the services table.
        $sql = "DROP TABLE IF EXISTS `{$this->_db->prefix}neatline_maps_services`";
        $this->_db->query($sql);

    }

    /**
     * Register routes.
     *
     * @param object $router The router.
     *
     * @return void.
     */
    public function defineRoutes($router)
    {

        // Default servers routes.
        $router->addRoute(
            'neatlineMapsServersDefault',
            new Zend_Controller_Router_Route(
                'neatline-maps/:action',
                array(
                    'module'        => 'neatline-maps',
                    'controller'    => 'servers',
                    'action'        => 'browse'
                )
            )
        );

        // Server-specific routes.
        $router->addRoute(
            'neatlineMapsServersId',
            new Zend_Controller_Router_Route(
                'neatline-maps/:action/:id',
                array(
                    'module'        => 'neatline-maps',
                    'controller'    => 'servers'
                ),
                array(
                    'id'            => '\d+'
                )
            )
        );

    }

    /**
     * Process WMS data on item add/edit.
     *
     * @param Item $record The item.
     * @param array $post The complete $_POST.
     *
     * @return void.
     */
    public function afterSaveFormItem($item, $post)
    {

        if (!isset($_FILES['file']) ||
            count($_FILES['file']['size']) == 1 &&
            empty($_FILES['file']['size'][0])) {

            // Create/update/delete WMS.
            $this->servicesTable->createOrUpdate(
                $item,
                $post['address'],
                $post['layers']
            );

        }

        $flash = new Omeka_Controller_Flash;
        $flash->setFlash(1, 'tester');

    }

    /**
     * Try to post new file to Geoserver.
     *
     * @param File $file The file.
     *
     * @return void.
     */
    public function afterInsertFile($file)
    {

        // Is the image a tiff?
        if ($file->getMimeType() == 'image/tiff') {

            // Get the active server.
            $server = $this->serversTable->getActiveServer();

            // Throw file at Geoserver.
            if ($server && _putFileToGeoserver($file, $server)) {

                // Get parent item and WMS.
                $item = $file->getItem();
                $wms = $this->servicesTable->findByItem($item);

                // If no WMS exists, create one for the file that
                // was just uploaded to Geoserver.
                if (!$wms) {
                    $this->servicesTable->createFromFileAndServer($file, $server);
                }

                // If a WMS already exists and the address is the
                // same as the address of the active server, append
                // the new layer to the active layers list.
                else if ($wms->address == $server->getWmsAddress()) {
                    $wms->layers .= ',' . nlwms_layerName($server, $file);
                    $wms->save();
                }

            }

        }

    }

    /**
     * Show WMS in admin items show.
     *
     * @return void.
     */
    public function adminAppendToItemsShowPrimary()
    {
        $item = get_current_item();
        echo nlwms_renderMap($item);
    }

    /**
     * Show WMS in public items show.
     *
     * @return void.
     */
    public function publicAppendToItemsShow()
    {
        $item = get_current_item();
        echo nlwms_renderMap($item);
    }

    /**
     * On item delete, delete associated WMS.
     *
     * @param Omeka_record $item The item.
     *
     * @return void.
     */
    public function beforeDeleteItem($item)
    {
        $wms = $this->servicesTable->findByItem($item);
        if ($wms) { $wms->delete(); }
    }

    /**
     * Include Openlayers in public views.
     *
     * @param array $request The request.
     *
     * @return void.
     */
    public function publicThemeHeader($request)
    {

        // Listen for items show.
        if ($request->getModuleName() == 'default' &&
            $request->getActionName() == 'show') {
                queue_css('openlayers/style');
                queue_js('openlayers/OpenLayers');
        }

    }

    /**
     * Include Openlayers in admin views.
     *
     * @param array $request The request.
     *
     * @return void.
     */
    public function adminThemeHeader($request)
    {

        // Listen for items show.
        if ($request->getModuleName() == 'default' &&
            $request->getActionName() == 'show') {
                queue_css('openlayers/style');
                queue_js('openlayers/OpenLayers');
        }

    }


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
     * Add tab to items add/edit.
     *
     * @param array $tabs Associative array with tab name => markup.
     *
     * @return array The tabs array with the Web Map Service tab.
     */
    public function adminItemsFormTabs($tabs)
    {

        // Set service false by default.
        $service = false;

        // Get item.
        $item = get_current_item();

        // If there is an item, try to get a service.
        if (!is_null($item->id)) {
            $service = $this->servicesTable->findByItem($item);
        }

        // Insert tab.
        $tabs['Web Map Service'] = __v()->partial(
          'items/_serviceForm.php', array(
            'service' => $service
          )
        );

        return $tabs;

    }

}
