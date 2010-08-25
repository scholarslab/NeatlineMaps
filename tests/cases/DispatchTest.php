<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of TestController
 *
 * @author wsg4w
 */
class NeatlineMaps_DispatchTest extends Omeka_Test_AppTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->helper = new NeatlineMaps_IntegrationHelper();
        $this->helper->setUpPlugin();
        $this->helper->createNewItem();
    }

    public function testDispatchItem()
    {
//        $this->dispatch('maps/show');
//        $this->assertResponseCode(200, 'Should get a 200 response code');
//        $this->assertController('Maps', 'Should get the maps controller');
//        $this->asserAction('index', 'Should show the index action');
    }
}
?>
