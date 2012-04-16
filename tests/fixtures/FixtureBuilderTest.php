<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4; */

/**
 * Special 'test' suite that hits each of the routes in the fixtures controller
 * and saves off the baked markup. Ensures that the front end test suite is always
 * working on real-application HTML.
 *
 * @package     omeka
 * @subpackage  neatline
 * @author      Scholars' Lab <>
 * @author      David McClure <david.mcclure@virginia.edu>
 * @copyright   2012 The Board and Visitors of the University of Virginia
 * @license     http://www.apache.org/licenses/LICENSE-2.0.html Apache 2 License
 */

class NLMAPS_FixtureBuilderTest extends NLMAPS_Test_AppTestCase
{

    protected $_isAdminTest = false;
    private static $path_to_fixtures = '../spec/javascripts/fixtures/';

    /**
     * Install the plugin.
     *
     * @return void.
     */
    public function setUp()
    {
        $this->setUpPlugin();
    }

    /**
     * .
     *
     * @return void.
     */
    public function testStub()
    {

        // $fixture = fopen(self::$path_to_fixtures . 'the-file.html', 'w');

        // $this->dispatch('the/url');
        // $response = $this->getResponse()->getBody('default');

        // fwrite($fixture, $response);
        // fclose($fixture);

    }

}
