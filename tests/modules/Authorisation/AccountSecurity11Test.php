<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 12:01 PM
 * To change this template use File | Settings | File Templates.
 */

class AccountSecurity11Test extends AccessModuleSetupAndAdminTest {

    public function setUp() {
        $this->username = 'security11';
        $this->password = 'security11pwd';
        $this->acls = array(
            'module_admin' => true,
            'module_setup' => true,
            'controller_staticpage' => false,
            'controller_helppage' => false,
            'controller_language' => false
        );
        parent::setUp();
    }
}
