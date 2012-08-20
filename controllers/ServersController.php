<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Servers controller.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class NeatlineMaps_ServersController extends Omeka_Controller_Action
{

    /**
     * Initialize.
     *
     * @return void
     */
    public function init()
    {

        $modelName = 'NeatlineMapsServer';
        if (version_compare(OMEKA_VERSION, '2.0-dev', '>=')) {
            $this->_helper->db->setDefaultModelName($modelName);
        } else {
            $this->_modelClass = $modelName;
        }

        try {
            $this->_table = $this->getTable($modelName);
            $this->aclResource = $this->findById();
        } catch (Omeka_Controller_Exception_404 $e) {}

    }

    /**
     * Add server.
     *
     * @return void
     */
    public function addAction()
    {

        // Create server and form.
        $server = new NeatlineMapsServer;
        $form = new ServerForm;

        // If a form as been posted.
        if ($this->_request->isPost()) {

            // Get post.
            $post = $this->_request->getPost();

            // If form is valid.
            if ($form->isValid($post)) {

                // Create server.
                $this->_table->updateServer($server, $post);

                // Redirect to browse.
                $this->redirect->goto('browse');

            }

            // If form is invalid.
            else {
                $form->populate($post);
            }

        }

        // Push form to view.
        $this->view->form = $form;

    }

    /**
     * Edit server.
     *
     * @return void
     */
    public function editAction()
    {

        // Get server.
        $server = $this->findById();

        // Get form.
        $form = new ServerForm;

        // Populate the form.
        $form->populate(array(
            'name' => $server->name,
            'url' => $server->url,
            'workspace' => $server->namespace,
            'username' => $server->username,
            'password' => $server->password,
            'active' => $server->active
        ));

        // If a form as been posted.
        if ($this->_request->isPost()) {

            // Get post.
            $post = $this->_request->getPost();

            // If form is valid.
            if ($form->isValid($post)) {

                // Create server.
                $this->_table->updateServer($server, $post);

                // Redirect to browse.
                $this->redirect->goto('browse');

            }

            // If form is invalid.
            else {
                $form->populate($post);
            }

        }

        // Push form to view.
        $this->view->form = $form;

    }

    /**
     * Set server active.
     *
     * @return void
     */
    public function activeAction()
    {

        // Get server.
        $server = $this->findById();

        // Set active and save.
        $server->active = 1;
        $server->save();

        // Redirect to browse.
        $this->redirect->goto('browse');

    }

    /**
     * Sets the delete confirm message
     */
    protected function _getDeleteConfirmMessage($server)
    {
        return __('This will permanently delete the %s server.', $server->name);
    }
}
