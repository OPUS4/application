<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 12:19 PM
 * To change this template use File | Settings | File Templates.
 */

class AccountSecurity12Test extends AccessModuleSetupAndAdminTest {

    public function setUp() {
        $this->username = 'security12';
        $this->password = 'security12pwd';
        $this->acls = array(
            'module_admin' => false,
            'module_setup' => true,
            'controller_staticpage' => false,
            'controller_helppage' => false,
            'controller_language' => false
        );
        parent::setUp();
    }
}

