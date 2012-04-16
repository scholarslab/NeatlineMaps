<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Test Runner.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

require_once 'NeatlineMaps_Test_AppTestCase.php';

class NeatlineMaps_AllTests extends PHPUnit_Framework_TestSuite
{

    /**
     * Aggregate the tests.
     *
     * @return NeatlineMaps_AllTests $suite The test suite.
     */
    public static function suite()
    {

        $suite = new NeatlineMaps_AllTests('Neatline Maps Tests');

        $collector = new PHPUnit_Runner_IncludePathTestCollector(
            array(
                dirname(__FILE__) . '/integration',
                dirname(__FILE__) . '/unit',
                dirname(__FILE__) . '/fixtures'
            )
        );

        $suite->addTestFiles($collector->collectTests());

        return $suite;

    }

}
