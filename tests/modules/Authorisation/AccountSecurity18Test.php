<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 2:30 PM
 * To change this template use File | Settings | File Templates.
 */

class AccountSecurity18Test extends AccessModuleSetupAndAdminTest {

    public function setUp() {
        $this->username = 'security18';
        $this->password = 'security18pwd';
        $this->acls = array(
            'module_admin' => true,
            'module_setup' => false,
            'controller_staticpage' => false,
            'controller_helppage' => false,
            'controller_language' => true
        );
        parent::setUp();
    }
}