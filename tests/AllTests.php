<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Ignition for BagIt test suite.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at http://www.apache.org/licenses/LICENSE-2.0 Unless required by
 * applicable law or agreed to in writing, software distributed under the
 * License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific
 * language governing permissions and limitations under the License.
 *
 * @package omeka
 * @subpackage BagIt
 * @author Scholars' Lab
 * @author David McClure (david.mcclure@virginia.edu)
 * @copyright 2011
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 *
 * PHP version 5
 *
 */
?>

<?php

require_once 'NeatlineMaps_Test_AppTestCase.php';

class NeatlineMaps_AllTests extends PHPUnit_Framework_TestSuite
{

    public static function suite()
    {

        $suite = new NeatlineMaps_AllTests('NeatlineMaps Tests');

        $collector = new PHPUnit_Runner_IncludePathTestCollector(
            array(
                dirname(__FILE__) . '/integration',
                dirname(__FILE__) . '/unit'
            )
        );

        $suite->addTestFiles($collector->collectTests());

        return $suite;

    }

}
