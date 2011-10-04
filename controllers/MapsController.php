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
        $map = $this->getTable('NeatlineMapsMap')->find($id);

        // If the server is offline, bounce out.
        if (!$map->getServer()->isOnline()) {
            $this->flashError('The server for this map is currently offline.');
            $this->_forward('browse', 'maps', 'neatline-maps');
        }

        // Get the files.
        $page = $this->_request->page;
        $order = _doColumnSortProcessing($sort_field, $sort_dir);
        $files = $this->getTable('NeatlineMapsMapFile')->getFiles($id, $page, $order);

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

        $serverTable = $this->getTable('NeatlineMapsServer');

        // Make sure that there is at least one viable server before allowing map create flow to start.
        if ($serverTable->count() == 0 || !$serverTable->isAvailableServer()) {
            $this->flashError('There has to be at least one active server registered before you can add a map.');
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
    public function getworkspaceAction()
    {

        $post = $this->_request->getPost();
        $server = $this->getTable('NeatlineMapsServer')->find($post['server_id']);
        $item_id = $this->_request->getParam('item_id');
        $form = $this->_doServerForm($item_id);

        if (!isset($post['existing_namespace'])) {

            if ($form->isValid($post)) {

                // Show namespace form.
                $this->view->item_id = $item_id;
                $this->view->server_id = $post['server_id'];
                $this->view->map_name = $post['map_name'];
                $this->view->workspaces = $server->getWorkspaceNames();

            }

            else {

                $this->_forward('getserver', 'maps', 'neatline-maps');

            }

        }

        else {

            // Show namespace form.
            $this->view->item_id = $item_id;
            $this->view->server_id = $post['server_id'];
            $this->view->map_name = $post['map_name'];
            $this->view->workspaces = $server->getWorkspaceNames();

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
        $item = $map->getItem();

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

        $this->_redirect('neatline-maps/maps/' . $map->id . '/files');

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

        // If files and namespace, do add.
        $files = insert_files_for_item(
            $item,
            'Upload',
            $map,
            array('ignoreNoFile' => true));

        // If new namespace is specified, add namespace.
        if ($post['new_workspace'] != '') {

            // Create the new workspace.
            _createGeoServerWorkspace(
                $server->url,
                $post['new_workspace'],
                $server->username,
                $server->password,
                $post['new_url']
            );

            $workspace = $post['new_workspace'];

        } else {

            $workspace = $post['existing_workspace'];

        }

        // Create the new map object.
        $map = $this->getTable('NeatlineMapsMap')->addNewMap($item, $server, $post['map_name'], $workspace);

        // Throw each of the files at GeoServer and see if it accepts them.
        $successCount = 0;
        foreach ($files as $file) {

            if (_putFileToGeoServer($file, $server, $workspace)) { // if GeoServer accepts the file...
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
        $form->setAction('getworkspace')->getMethod('post');

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

        $submit = new Zend_Form_Element_Submit('select_workspace');
        $submit->setLabel('Continue');
        $submit->removeDecorator('DtDdWrapper');

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

}


/*
 * Local variables:
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * End:
 */

?>
