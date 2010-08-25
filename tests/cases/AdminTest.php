<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AdminTest
 *
 * @author wsg4w
 */
class NeatlineMaps_AdminTest extends Omeka_Test_AppTestCase
{
    protected $_isAdminTest = true;

    public function setUp()
    {
        parent::setUp();
        $this->helper = new NeatlineMaps_IntegrationHelper;
        $this->helper->setUpPlugin();
    }

    public function testIsInstalled()
    {
        $this->assertEquals(1,1);
    }

    

}
?>
