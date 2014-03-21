<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 12:33 PM
 * To change this template use File | Settings | File Templates.
 */

class AccountSecurity13Test extends AccessModuleSetupAndAdminTest {

    public function setUp() {
        $this->username = 'security13';
        $this->password = 'security13pwd';
        $this->acls = array(
            'module_admin' => false,
            'module_setup' => false,
            'controller_staticpage' => false,
            'controller_helppage' => true,
            'controller_language' => false
        );
        parent::setUp();
    }
}