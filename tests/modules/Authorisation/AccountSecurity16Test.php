<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 12:44 PM
 * To change this template use File | Settings | File Templates.
 */

class AccountSecurity16Test extends AccessModuleSetupAndAdminTest {

    public function setUp() {
        $this->username = 'security16';
        $this->password = 'security16pwd';
        $this->acls = array(
            'module_admin' => true,
            'module_setup' => false,
            'controller_staticpage' => false,
            'controller_helppage' => true,
            'controller_language' => false
        );
        parent::setUp();
    }
}