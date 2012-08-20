<?php
/**
 * Test for the NeatlineMaps ACL.
 */
class AclTest extends NLMAPS_Test_AppTestCase
{
    const RESOURCE = 'NeatlineMaps_Servers';

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
     * Data for testAcl
     */
    public function acl()
    {
        return array(
            // $isAllowed, $role, $privilege
            array(false, null, 'add'),
            array(false, null, 'edit'),
            array(false, null, 'browse'),            
            array(false, null, 'delete'),
            array(false, null, 'active'),
            array(false, null, 'show'),            
            array(false, 'researcher', 'add'),
            array(false, 'researcher', 'edit'),
            array(false, 'researcher', 'browse'),            
            array(false, 'researcher', 'delete'),
            array(false, 'researcher', 'active'),
            array(false, 'researcher', 'show'),
            array(false, 'contributor', 'add'),
            array(false, 'contributor', 'edit'),
            array(false, 'contributor', 'browse'),            
            array(false, 'contributor', 'delete'),
            array(false, 'contributor', 'active'),
            array(false, 'contributor', 'show'),
            array(true, 'admin', 'add'),
            array(true, 'admin', 'edit'),
            array(true, 'admin', 'browse'),
            array(true, 'admin', 'delete'),
            array(true, 'admin', 'active'),
            array(true, 'admin', 'show'),
            array(true, 'super', 'add'),
            array(true, 'super', 'edit'),
            array(true, 'super', 'browse'),
            array(true, 'super', 'delete'),
            array(true, 'super', 'active'),
            array(true, 'super', 'show')
        );
    }

    public function assertPreConditions()
    {
        $this->assertTrue($this->acl->has(self::RESOURCE));
    }

    /**
     * @dataProvider acl
     */
    public function testAcl($isAllowed, $role, $privilege = null)
    {
        $this->assertEquals($isAllowed,
            $this->acl->isAllowed($role, self::RESOURCE, $privilege));
    }
}
