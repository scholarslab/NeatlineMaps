<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Server form.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class ServerForm extends Omeka_Form
{

    /**
     * Construct the exhibit add/edit form.
     *
     * @return void.
     */
    public function init()
    {

        parent::init();

        $this->setMethod('post');
        $this->setAttrib('id', 'server-form');
        $this->addElementPrefixPath('Neatline', dirname(__FILE__));

        // Name.
        $this->addElement('text', 'name', array(
            'label'         => 'Name',
            'description'   => 'An internal (non-public) identifier for the server.',
            'size'          => 40,
            'required'      => true,
            'validators'    => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' =>
                    array(
                        'messages' => array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Enter a name.'
                        )
                    )
                )
            )
        ));

        // URL.
        $this->addElement('text', 'url', array(
            'label'         => 'URL',
            'description'   => 'The location of the server.',
            'size'          => 40,
            'required'      => true,
            'validators'    => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' =>
                    array(
                        'messages' => array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Enter a URL.'
                        )
                    )
                ),
                array('validator' => 'IsUrl', 'breakChainOnFailure' => true, 'options' =>
                    array(
                        'messages' => array(
                            Neatline_Validate_IsUrl::INVALID_URL => 'Enter a valid URL.'
                        )
                    )
                )
            )
        ));

        // Namespace.
        $this->addElement('text', 'workspace', array(
            'label'         => 'Workspace',
            'description'   => 'Enter the Geoserver workspace.',
            'size'          => 40,
            'required'      => true,
            'validators'    => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' =>
                    array(
                        'messages' => array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Enter a workspace.'
                        )
                    )
                )
            )
        ));

        // Username.
        $this->addElement('text', 'username', array(
            'label'         => 'Username',
            'description'   => 'Enter the Geoserver username.',
            'size'          => 40,
            'required'      => true,
            'validators'    => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' =>
                    array(
                        'messages' => array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Enter a username.'
                        )
                    )
                )
            )
        ));

        // Password.
        $this->addElement('password', 'password', array(
            'label'         => 'Password',
            'description'   => 'Enter the Geoserver password.',
            'size'          => 40,
            'required'      => true,
            'renderPassword'=> true,
            'validators'    => array(
                array('validator' => 'NotEmpty', 'breakChainOnFailure' => true, 'options' =>
                    array(
                        'messages' => array(
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Enter a password.'
                        )
                    )
                )
            )
        ));

        // Active.
        $this->addElement('checkbox', 'active', array(
            'label'         => 'Active',
            'description'   => 'Should this server be used to handle new GeoTiff uploads?',
            'checked'       => true
        ));

        // Submit.
        $this->addElement('submit', 'submit', array(
            'label' => 'Save'
        ));

        // Group the data fields.
        $this->addDisplayGroup(array(
            'name', 'url', 'workspace', 'username', 'password', 'active'
        ), 'server_information');

        // Group the submit button sparately.
        $this->addDisplayGroup(array('submit'), 'submit_button');

    }

}
