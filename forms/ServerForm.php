<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Add server form.
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
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      Bethany Nowviskie <bethany@virginia.edu>
 * @author      Adam Soroka <ajs6f@virginia.edu>
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
                            Zend_Validate_NotEmpty::IS_EMPTY => 'Enter a url.'
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

        // Submit.
        $this->addElement('submit', 'submit', array(
            'label' => 'Create'
        ));

        // Group the fields.
        $this->addDisplayGroup(array('name', 'url'), 'server_information');

        // Group the submit button sparately.
        $this->addDisplayGroup(array('submit'), 'submit_button');

    }

}
