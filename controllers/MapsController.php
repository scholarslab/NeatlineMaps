<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Maps controller.
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

class NeatlineMaps_MapsController extends Omeka_Controller_Action
{

    /**
     * Show maps.
     *
     * @return void
     */
    public function browseAction()
    {

        $sort_field = $this->_request->getParam('sort_field');
        $sort_dir = $this->_request->getParam('sort_dir');

        // Get the maps.
        $page = $this->_request->page;
        $order = _doColumnSortProcessing($sort_field, $sort_dir);
        $maps = $this->getTable('NeatlineMapsMap')->getMaps($page, $order);

        $this->view->maps = $maps;
        $this->view->filesTable = $this->getTable('NeatlineMapsMapFile');
        $this->view->current_page = $page;
        $this->view->total_results = $this->getTable('NeatlineMapsMap')->count();
        $this->view->results_per_page = get_option('per_page_admin');

    }

    /**
     * View, edit, delete map files.
     *
     * @return void
     */
    public function editmapAction()
    {

        $sort_field = $this->_request->getParam('sort_field');
        $sort_dir = $this->_request->getParam('sort_dir');
        $id = $this->_request->id;

        // Get the files.
        $page = $this->_request->page;
        $order = _doColumnSortProcessing($sort_field, $sort_dir);
        $files = $this->getTable('NeatlineMapsMapFile')->getFiles($id, $page, $order);
        $map = $this->getTable('NeatlineMapsMap')->find($id);

        $this->view->files = $files;
        $this->view->map = $map;

        $this->view->current_page = $page;
        $this->view->total_results = $this->getTable('NeatlineMapsMap')->count();
        $this->view->results_per_page = get_option('per_page_admin');

    }

    /**
     * Delete an individual file.
     *
     * @return void
     */
    public function deletefileAction()
    {

        $id = $this->_request->id;
        $file = $this->getTable('NeatlineMapsMapFile')->find($id);
        $map = $this->getTable('NeatlineMapsMap')->getMapByFile($file);
        $post = $this->_request->getPost();

        if (isset($post['deleteconfirm_submit'])) {

            $deleteFiles = false;
            $deleteLayers = false;

            // If selected, delete the files themselves.
            if (isset($post['delete_omeka_files'])) {
                $deleteFiles = true;
            }

            // If selected, delete the GeoServer layers.
            if (isset($post['delete_geoserver_files'])) {
                $deleteLayers = true;
            }

            // Delete the Map record.
            $this->getTable('NeatlineMapsMapFile')->deleteFile($id, $deleteFiles, $deleteLayers);

            $this->flashSuccess('File deleted.');
            $this->_redirect('neatline-maps/maps/' . $map->id . '/files');

        }

        $this->view->file = $file;

    }

    /**
     * Choose which item to add the map to.
     *
     * @return void
     */
    public function itemselectAction()
    {

        if ($this->getTable('NeatlineMapsServer')->count() == 0) {

            $this->flashError('You have to create a server before you can add a map.');
            $this->_forward('create', 'servers', 'neatline-maps');

        }

        $sort_field = $this->_request->getParam('sort_field');
        $sort_dir = $this->_request->getParam('sort_dir');
        $search = $this->_request->getParam('search');

        // Get the datastreams.
        $page = $this->_request->page;
        $order = _doColumnSortProcessing($sort_field, $sort_dir);
        $items = _getItems($page, $order, $search);

        $this->view->items = $items;
        $this->view->current_page = $page;
        $this->view->total_results = $this->getTable('Item')->count();
        $this->view->results_per_page = get_option('per_page_admin');
        $this->view->search = $search;

    }

    /**
     * Get map name and server.
     *
     * @return void
     */
    public function getserverAction()
    {

        $post = $this->_request->getPost();

        if (isset($post['submitted'])) {

            $item_id = $this->_request->getParam('item_id');
            $item = _getSingleItem($item_id);

            $form = $this->_doServerForm($item_id);
            $form->isValid($post);
            $form->populate($post);

            $this->view->item = $item;
            $this->view->form = $form;

        }

        else {

            $item_id = $this->_request->getParam('item_id');
            $item = _getSingleItem($item_id);

            $this->view->item = $item;
            $this->view->form = $this->_doServerForm($item_id);

        }

    }

    /**
     * Get namespace and files.
     *
     * @return void
     */
    public function getnamespaceAction()
    {

        $post = $this->_request->getPost();
        $server = $this->getTable('NeatlineMapsServer')->find($post['server_id']);
        $form = $this->_doServerForm($post['item_id']);

        if (!isset($post['existing_namespace'])) {

            if ($form->isValid($post)) {

                // Show namespace form.
                $this->view->item_id = $post['item_id'];
                $this->view->server_id = $post['server_id'];
                $this->view->map_name = $post['map_name'];
                $this->view->namespaces = $server->getNamespaceNames();

            }

            else {

                $this->_forward('getserver', 'maps', 'neatline-maps');

            }

        }

        else {

            // Show namespace form.
            $this->view->item_id = $post['item_id'];
            $this->view->server_id = $post['server_id'];
            $this->view->map_name = $post['map_name'];
            $this->view->namespaces = $server->getNamespaceNames();

        }

    }

    /**
     * Get namespace and files.
     *
     * @return void
     */
    public function addfilesAction()
    {

        $id = $this->_request->id;
        $map = $this->getTable('NeatlineMapsMap')->find($id);


        // Show files form.
        $this->view->map = $map;

    }

    /**
     * Add new files to an already existing map.
     *
     * @return void
     */
    public function uploadfilesAction()
    {

        $id = $this->_request->id;
        $map = $this->getTable('NeatlineMapsMap')->find($id);
        $server = $map->getServer();

        // Were files selected for upload?
        if ($_FILES['map'][0]['size'] > 0) {
            $this->flashError('Select files.');
            $this->_redirect('neatline-maps/maps/' . $map->id . '/addfiles');
        }

        // If files and namespace, do add.
        $files = insert_files_for_item(
            $item,
            'Upload',
            'map',
            array('ignoreNoFile'=>true));

        // Create the new map object.
        $map = $this->getTable('NeatlineMapsMap')->addNewMap($item, $server, $post['map_name'], $namespace);

        // Throw each of the files at GeoServer and see if it accepts them.
        $successCount = 0;
        foreach ($files as $file) {

            if (_putFileToGeoServer($file, $server, $map->namespace)) { // if GeoServer accepts the file...
                $this->getTable('NeatlineMapsMapFile')->addNewMapFile($map, $file);
                $successCount++;
            }

            else {
                $file->delete();
            }

        }

        // If none of the files were successfully posted to GeoServer, delete the empty map record.
        if ($successCount == 0) {

            $this->flashError('There was an error; the maps were not added.');

        } else {

            $this->flashSuccess('Map created and files added to GeoServer.');

        }

        $this->_redirect('neatline-maps/maps/' . $map->id);

    }

    /**
     * Create the map.
     *
     * @return void
     */
    public function addmapAction()
    {

        $post = $this->_request->getPost();
        $item = $this->getTable('Item')->find($post['item_id']);
        $server = $this->getTable('NeatlineMapsServer')->find($post['server_id']);


        // Is a namespace selected (must select an existing one or enter a name for a new one).
        if ($post['existing_namespace'] == '-' && $post['new_namespace'] == null) {
            $this->flashError('Select an existing namespace or enter a new one.');
            $this->_forward('getnamespace', 'maps', 'neatline-maps');
        }

        // Were files selected for upload?
        if ($_FILES['map'][0]['size'] > 0) {
            $this->flashError('Select files.');
            $this->_forward('getnamespace', 'maps', 'neatline-maps');
        }

        // If files and namespace, do add.
        $files = insert_files_for_item(
            $item,
            'Upload',
            'map',
            array('ignoreNoFile'=>true));

        // If new namespace is specified, add namespace.
        if ($post['new_namespace'] != '') {

            // Create the new namespace.
            _createGeoServerNamespace(
                $server->url,
                $post['new_namespace'],
                $server->username,
                $server->password,
                $post['new_url']
            );

            $namespace = $post['new_namespace'];

        } else {

            $namespace = $post['existing_namespace'];

        }

        // Create the new map object.
        $map = $this->getTable('NeatlineMapsMap')->addNewMap($item, $server, $post['map_name'], $namespace);

        // Throw each of the files at GeoServer and see if it accepts them.
        $successCount = 0;
        foreach ($files as $file) {

            if (_putFileToGeoServer($file, $server, $namespace)) { // if GeoServer accepts the file...
                $this->getTable('NeatlineMapsMapFile')->addNewMapFile($map, $file);
                $successCount++;
            }

            else {
                $file->delete();
            }

        }

        // If none of the files were successfully posted to GeoServer, delete the empty map record.
        if ($successCount == 0) {

            $map->delete();
            $this->flashError('There was an error; the maps were not added.');

        } else {

            $this->flashSuccess('Map created and files added to GeoServer.');

        }

        $this->redirect->goto('browse');

    }

    /**
     * Confirm map delete, show options, do delete.
     *
     * @return void
     */
    public function deletemapAction()
    {

        $id = $this->_request->id;
        $map = $this->getTable('NeatlineMapsMap')->find($id);
        $post = $this->_request->getPost();

        if (isset($post['deleteconfirm_submit'])) {

            $deleteFiles = false;
            $deleteLayers = false;

            // If selected, delete the files themselves.
            if (isset($post['delete_omeka_files'])) {
                $deleteFiles = true;
            }

            // If selected, delete the GeoServer layers.
            if (isset($post['delete_geoserver_files'])) {
                $deleteLayers = true;
            }

            // Delete the Map record.
            $this->getTable('NeatlineMapsMap')->deleteMap($id, $deleteFiles, $deleteLayers);

            $this->flashSuccess('Map deleted.');
            $this->redirect->goto('browse');

        }

        $this->view->map = $map;

    }

    /**
     * Build the form for server add/edit.
     *
     * @param $item_id The id of the item that the server is being attached to.
     *
     * @return void
     */
    protected function _doServerForm($item_id)
    {

        $form = new Zend_Form();
        $form->setAction('getnamespace')->getMethod('post');

        $name = new Zend_Form_Element_Text('map_name');
        $name->setRequired(true)
            ->setLabel('Name:')
            ->setAttrib('size', 55);

        $server = new Zend_Form_Element_Select('server_id');
        $server->setLabel('Server:');
        $servers = $this->getTable('NeatlineMapsServer')->getServers();

        // Add each of the servers as an option.
        foreach ($servers as $server_object) {

            if ($server_object->isOnline()) {
                $server->addMultiOption($server_object->id, $server_object->name);
            }

        }

        $submit = new Zend_Form_Element_Submit('select_namespace');
        $submit->setLabel('Continue');

        $item = new Zend_Form_Element_Hidden('item_id');
        $item->setValue($item_id);

        $submitted = new Zend_Form_Element_Hidden('submitted');
        $submitted->setValue('true');

        $form->addElement($name);
        $form->addElement($server);
        $form->addElement($submit);
        $form->addElement($item);
        $form->addElement($submitted);

        return $form;

    }

    /**
     * Build the form for server add/edit.
     *
     * @param $mode 'create' or 'edit.'
     * @param $server_id The id of the server for hidden input in edit case.
     *
     * @return void
     */
    protected function _doNamespaceForm($item_id, $server_id, $map_name)
    {

        $server = $this->getTable('NeatlineMapsServer')->find($server_id);

        $form = new Zend_Form();
        $form->setAction('addmap')->getMethod('post');

        $namespace = new Zend_Form_Element_Select('existing_namespace');
        $namespace->setLabel('Use existing namespace:');
        $namespaces = $server->getNamespaceNames();

        $namespace->addMultiOption('-', '(use new namespace below)');

        // Add each of the servers as an option.
        foreach ($namespaces as $namespace_node) {
            $namespace->addMultiOption($namespace_node, $namespace_node);
        }

        $newNamespace = new Zend_Form_Element_Text('new_namespace');
        $newNamespace->setLabel('Create a new namespace:')
            ->setAttrib('size', 55);

        $newNamespaceUrl = new Zend_Form_Element_Text('new_url');
        $newNamespaceUrl->setLabel('Url for new namespace:')
            ->setAttrib('size', 55);

        $submit = new Zend_Form_Element_Submit('create_map');
        $submit->setLabel('Create');

        $item_id_input = new Zend_Form_Element_Hidden('item_id');
        $item_id_input->setValue($item_id);

        $server_id_input = new Zend_Form_Element_Hidden('server_id');
        $server_id_input->setValue($server_id);

        $map_name_input = new Zend_Form_Element_Hidden('map_name');
        $map_name_input->setValue($map_name);

        $form->addElement($namespace);
        $form->addElement($newNamespace);
        $form->addElement($newNamespaceUrl);
        $form->addElement($submit);
        $form->addElement($item_id_input);
        $form->addElement($server_id_input);
        $form->addElement($map_name_input);

        return $form;

    }

}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
