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
     * Show servers.
     *
     * @return void
     */
    public function browseAction()
    {

        $sort_field = $this->_request->getParam('sort_field');
        $sort_dir = $this->_request->getParam('sort_dir');

        // Get the servers.
        $page = $this->_request->page;
        $order = _doColumnSortProcessing($sort_field, $sort_dir);
        $maps = $this->getTable('NeatlineMapsMap')->getMaps($page, $order);

        $this->view->maps = $maps;

        $this->view->current_page = $page;
        $this->view->total_results = $this->getTable('NeatlineMapsMap')->count();
        $this->view->results_per_page = get_option('per_page_admin');

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

        $item_id = $this->_request->getParam('item_id');
        $item = _getSingleItem($item_id);

        $post = $this->_request->getPost();
        $form = $this->_doServerForm($item_id);

        if ($form->isValid($post)) {

            // Show namespace form.
            $this->view->item = $item;
            $this->view->form = $this->_doNamespaceForm($item_id, $post['server']);

        }

        else {

            $this->_forward('getserver', 'maps', 'neatline-maps');

        }

    }

    /**
     * Create the map.
     *
     * @return void
     */
    public function addmapAction()
    {

        $item_id = $this->_request->getParam('item_id');
        $item = _getSingleItem($item_id);

        $post = $this->_request->getPost();
        $namespaceForm = $this->_doNamespaceForm($item_id);

        // Is a namespace selected (must select an existing one or enter a name for a new one).
        if ($post['existing_namespace'] == '-' && $post['new_namespace'] == '') {
            // Bounce back to get namespace.
        }

        // Were files selected for upload?
        if (!isset($_FILES['map'])) {
            // Bounce back to get files.
        }

        // If files and namespace, do add.









    }

    /**
     * Show and process form to add new namespace.
     *
     * @return void
     */
    // public function createAction()
    // {

    //     if ($this->_request->isPost()) {

    //         // Get the data, instantiate validator.
    //         $data = $this->_request->getPost();
    //         $form = $this->_doNamespaceForm();

    //         // Are all the fields filled out?
    //         if ($form->isValid($data)) {

    //             $namespaceStatus = $this->getTable('NeatlineMapsNamespace')->createNamespace($data);

    //             // Create server, process success.
    //             if ($namespaceStatus == 'added') {

    //                 $this->flashSuccess('Namespace created on GeoServer and registered in Neatline.');
    //                 $this->redirect->goto('browse');

    //             } else if ($namespaceStatus == 'registered') {

    //                 $this->flashSuccess('Namespace registered in Neatline');
    //                 $this->redirect->goto('browse');

    //             } else if ($namespaceStatus == 'updated') {



    //             }

    //         }

    //         else {

    //             $form->populate($data);
    //             $this->view->form = $form;

    //         }

    //     }

    //     else {

    //         if (count($this->getTable('NeatlineMapsServer')->getServers()) > 0) {
    //             $form = $this->_doNamespaceForm();
    //             $this->view->form = $form;
    //         }

    //         else {
    //             $this->flashError('Before you can create a namespace, you have to add a server.');
    //             $this->_redirect('/neatline-maps/servers/create');
    //         }

    //     }

    // }

    /**
     * Show form to edit existing server.
     *
     * @return void
     */
    // public function editAction()
    // {

    //     // If an edited form has been submitted
    //     if ($this->_request->isPost()) {

    //         // Get the data, instantiate validator.
    //         $data = $this->_request->getPost();
    //         $form = $this->_doServerForm('edit', $data['id']);

    //         // If delete was hit, do the delete.
    //         if (isset($data['delete_submit'])) {
    //             $this->_redirect('neatline-maps/servers/delete/' . $data['id']);
    //         }

    //         // Are all the fields filled out?
    //         if ($form->isValid($data)) {

    //             // If save was hit, do save.
    //             if (isset($data['edit_submit'])) {

    //                 if ($this->getTable('NeatlineMapsServer')->saveServer($data)) {

    //                     $this->flashSuccess('Information for server ' . $data['name'] . ' saved');
    //                     $this->redirect->goto('browse');

    //                 } else {

    //                     $this->flashError('Error: Information for server ' . $data['name'] . ' not saved');
    //                     $this->redirect->goto('browse');

    //                 }

    //             }

    //         }

    //         else {

    //             $form->populate($data);
    //             $id = $this->_request->id;
    //             $server = $this->getTable('NeatlineMapsServer')->find($id);

    //             $this->view->form = $form;
    //             $this->view->server = $server;

    //         }

    //     }

    //     else {

    //         $id = $this->_request->id;
    //         $server = $this->getTable('NeatlineMapsServer')->find($id);

    //         // Get the form.
    //         $form = $this->_doServerForm('edit', $id);

    //         // Fill it with the data.
    //         $form->populate(array(
    //             'name' => $server->name,
    //             'url' => $server->url,
    //             'username' => $server->username,
    //             'password' => $server->password
    //         ));

    //         $this->view->form = $form;
    //         $this->view->server = $server;

    //     }

    // }

    /**
     * Process edit form - delete or save.
     *
     * @return void
     */
    // public function updateAction()
    // {

    //     // // Get the data, instantiate validator.
    //     // $data = $this->_request->getPost();
    //     // $form = $this->_doServerForm();

    //     // // If delete was hit, do the delete.
    //     // if (isset($data['delete_submit'])) {
    //     //     $this->_redirect('neatline-maps/servers/delete/' . $data['id']);
    //     //     exit();
    //     // }

    //     // // Are all the fields filled out?
    //     // if ($form->isValid($data)) {

    //     //     // If save was hit, do save.
    //     //     if (isset($data['edit_submit'])) {

    //     //         if ($this->getTable('NeatlineMapsServer')->saveServer($data)) {

    //     //             $this->flashSuccess('Information for server ' . $data['name'] . ' saved');
    //     //             $this->redirect->goto('browse');

    //     //         } else {

    //     //             $this->flashError('Error: Information for server ' . $data['name'] . ' not saved');
    //     //             $this->redirect->goto('browse');

    //     //         }

    //     //     }

    //     // }

    //     // else {

    //     //     $this->flashError('The server must have a name, URL, username, and password.');
    //     //     $this->_redirect('neatline-maps/servers/edit/' . $data['id']);

    //     // }

    // }

    /**
     * Confirm delete, do delete.
     *
     * @return void
     */
    // public function deleteAction()
    // {

    //     $id = $this->_request->id;
    //     $server = $this->getTable('NeatlineMapsServer')->find($id);
    //     $post = $this->_request->getPost();

    //     if (isset($post['deleteconfirm_submit'])) {

    //         if ($this->getTable('NeatlineMapsServer')->deleteServer($id)) {
    //             $this->flashSuccess('Server ' . $server->name . ' deleted');
    //             $this->redirect->goto('browse');
    //         } else {
    //             $this->flashError('Error: Server ' . $server->name . ' was not deleted');
    //             $this->redirect->goto('browse');
    //         }

    //     }

    //     $this->view->name = $server->name;

    // }

    /**
     * Build the form for server add/edit.
     *
     * @param $mode 'create' or 'edit.'
     * @param $server_id The id of the server for hidden input in edit case.
     *
     * @return void
     */
    protected function _doServerForm($item_id)
    {

        $form = new Zend_Form();
        $form->setAction('getnamespace')->getMethod('post');

        $name = new Zend_Form_Element_Text('name');
        $name->setRequired(true)
            ->setLabel('Name:')
            ->setAttrib('size', 55);

        $server = new Zend_Form_Element_Select('server');
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
    protected function _doNamespaceForm($item_id, $server_id)
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

        $submit = new Zend_Form_Element_Submit('create_map');
        $submit->setLabel('Create');

        $item = new Zend_Form_Element_Hidden('item_id');
        $item->setValue($item_id);

        $form->addElement($namespace);
        $form->addElement($newNamespace);
        $form->addElement($submit);
        $form->addElement($item);

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
