<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 12:45 PM
 * To change this template use File | Settings | File Templates.
 */

class AccountSecurity17Test extends AccessModuleSetupAndAdminTest {

    public function setUp() {
        $this->username = 'security17';
        $this->password = 'security17pwd';
        $this->acls = array(
            'module_admin' => true,
            'module_setup' => false,
            'controller_staticpage' => true,
            'controller_helppage' => false,
            'controller_language' => false
        );
        parent::setUp();
    }
}