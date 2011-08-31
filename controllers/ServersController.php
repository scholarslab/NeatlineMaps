<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Servers controller.
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

class NeatlineMaps_ServersController extends Omeka_Controller_Action
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
        $servers = $this->getTable('NeatlineMapsServer')->getServers($page, $order);

        $this->view->servers = $servers;

        $this->view->current_page = $page;
        $this->view->total_results = $this->getTable('NeatlineMapsServer')->count();
        $this->view->results_per_page = get_option('per_page_admin');

    }

    /**
     * Show form to add new server.
     *
     * @return void
     */
    public function createAction()
    {

        if ($this->_request->isPost()) {

            // Get the data, instantiate validator.
            $data = $this->_request->getPost();
            $form = $this->_doServerForm();

            // Are all the fields filled out?
            if ($form->isValid($data)) {

                // Create server, process success.
                if ($this->getTable('NeatlineMapsServer')->createServer($data)) {
                    $this->flashSuccess('Server created.');
                    $this->redirect->goto('browse');
                } else {
                    $this->flashError('Error: The server was not created');
                    $this->redirect->goto('browse');
                }

            }

            else {

                $form->populate($data);
                $this->view->form = $form;

            }

        }

        else {

            $form = $this->_doServerForm();
            $this->view->form = $form;

        }

    }

    /**
     * Show form to edit existing server.
     *
     * @return void
     */
    public function editAction()
    {

        // If an edited form has been submitted
        if ($this->_request->isPost()) {

            // Get the data, instantiate validator.
            $data = $this->_request->getPost();
            $form = $this->_doServerForm('edit', $data['id']);

            // If delete was hit, do the delete.
            if (isset($data['delete_submit'])) {
                $this->_redirect('neatline-maps/servers/delete/' . $data['id']);
            }

            // Are all the fields filled out?
            if ($form->isValid($data)) {

                // If save was hit, do save.
                if (isset($data['edit_submit'])) {

                    if ($this->getTable('NeatlineMapsServer')->saveServer($data)) {

                        $this->flashSuccess('Information for server ' . $data['name'] . ' saved.');
                        $this->redirect->goto('browse');

                    } else {

                        $this->flashError('Error: Information for server ' . $data['name'] . ' not saved.');
                        $this->redirect->goto('browse');

                    }

                }

            }

            else {

                $form->populate($data);
                $id = $this->_request->id;
                $server = $this->getTable('NeatlineMapsServer')->find($id);

                $this->view->form = $form;
                $this->view->server = $server;

            }

        }

        else {

            $id = $this->_request->id;
            $server = $this->getTable('NeatlineMapsServer')->find($id);

            // Get the form.
            $form = $this->_doServerForm('edit', $id);

            // Fill it with the data.
            $form->populate(array(
                'name' => $server->name,
                'url' => $server->url,
                'username' => $server->username,
                'password' => $server->password
            ));

            $this->view->form = $form;
            $this->view->server = $server;

        }

    }

    /**
     * Confirm delete, do delete.
     *
     * @return void
     */
    public function deleteAction()
    {

        $id = $this->_request->id;
        $server = $this->getTable('NeatlineMapsServer')->find($id);
        $post = $this->_request->getPost();

        if (isset($post['deleteconfirm_submit'])) {

            if (!$server->hasChildMaps()) {

                if ($this->getTable('NeatlineMapsServer')->deleteServer($id)) {
                    $this->flashSuccess('Server ' . $server->name . ' deleted');
                    $this->redirect->goto('browse');
                } else {
                    $this->flashError('Error: Server ' . $server->name . ' was not deleted');
                    $this->redirect->goto('browse');
                }

            } else {

                $this->flashError('Error: Server ' . $server->name . ' cannot be deleted because it has maps associated with it.');
                $this->redirect->goto('browse');

            }

        }

        $this->view->name = $server->name;

    }

    /**
     * Build the form for server add/edit.
     *
     * @param $mode 'create' or 'edit.'
     * @param $server_id The id of the server for hidden input in edit case.
     *
     * @return void
     */
    protected function _doServerForm($mode = 'create', $server_id = null)
    {

        $form = new Zend_Form();

        $name = new Zend_Form_Element_Text('name');
        $name->setRequired(true)
            ->setLabel('Name:')
            ->setAttrib('size', 55);

        $url = new Zend_Form_Element_Text('url');
        $url->setRequired(true)
            ->setLabel('URL:')
            ->setAttrib('size', 55);

        $username = new Zend_Form_Element_Text('username');
        $username->setRequired(true)
            ->setLabel('Username:')
            ->setAttrib('size', 55);

        $password = new Zend_Form_Element_Password('password');
        $password->setRequired(true)
            ->setLabel('Password:')
            ->setAttrib('size', 55)
            ->setRenderPassword(true);

        $form->addElement($name);
        $form->addElement($url);
        $form->addElement($username);
        $form->addElement($password);

        if ($mode == 'create') {

            $submit = new Zend_Form_Element_Submit('create_submit');
            $submit->setLabel('Create');

            $form->addElement($submit);
            $form->setAction('create')->setMethod('post');

        }

        else if ($mode == 'edit') {

            $id = new Zend_Form_Element_Hidden('id');
            $id->setValue($server_id);

            $submit = new Zend_Form_Element_Submit('edit_submit');
            $submit->setLabel('Save');

            $delete = new Zend_Form_Element_Submit('delete_submit');
            $delete->setLabel('Delete');

            $form->addElement($id);
            $form->addElement($submit);
            $form->addElement($delete);
            $form->setAction('')->setMethod('post');

        }

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
