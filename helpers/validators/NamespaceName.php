<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Custom validator class for namespace titles. Ensures that duplicate
 * namespaces don't get added.
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

class NeatlineMaps_NamespaceName extends Zend_Validate_Abstract
{

    protected $_messageTemplates = array(
        self::MSG_DUPLICATE => "A namespace called %value% already exists on the selected server."
    );

    /**
     * Make sure the name is unique on the selected server.
     *
     * @param string $value The name posted in the form.
     * @param array $context The rest of the form fields.
     *
     * @return boolean True if the name is unique.
     */
    public function isValid($value, $context = null)
    {

        $this->_setValue($value);
        $db = get_db();
        $namespaceTable = $db->getTable('NeatlineMapsNamespace');

        $existingNamespace = $namespaceTable->fetchObject(

            $namespaceTable->getSelect()->where('server_id = ' . $context['server'] .
                'AND name = ' . $context['name'])

        );

        $match = false;

        foreach ($existingNamespace as $namespace) {

            if (strcmp($namespace->name, $value)) {
                $match = true;
            }

        }

        return $match;

    }

}
