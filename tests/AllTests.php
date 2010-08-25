<?php
/**
 * @author    "Scholars Lab"
 * @version   SVN: $Id$
 * @copyright 2010 The Board and Visitors of the University of Virginia
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0
 * @link      http://github.com/scholarslab/bagit
 */

define('NEATLINE_MAP_DIR', dirname(dirname(__FILE__)));

require_once 'NeatlineMaps_IntegrationHelper.php';

class NeatlineMap_AllTests extends PHPUnit_Framework_TestSuite
{
    public static function suite()
    {
        $suite = new NeatlineMap_AllTests('NeatlineMaps Tests');
        $testCollector = new PHPUnit_Runner_IncludePathTestCollector(
            array(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'cases')
        );

        $suite->addTestFiles($testCollector->collectTests());
        return $suite;
    }
}