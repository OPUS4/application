<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 12:43 PM
 * To change this template use File | Settings | File Templates.
 */

class AccountSecurity14Test extends AccessModuleSetupAndAdminTest {

    public function setUp() {
        $this->username = 'security14';
        $this->password = 'security14pwd';
        $this->acls = array(
            'module_admin' => false,
            'module_setup' => false,
            'controller_staticpage' => true,
            'controller_helppage' => false,
            'controller_language' => false
        );
        parent::setUp();
    }
}