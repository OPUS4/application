<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 12:44 PM
 * To change this template use File | Settings | File Templates.
 */

class AccountSecurity15Test extends AccessModuleSetupAndAdminTest {

    public function setUp() {
        $this->username = 'security15';
        $this->password = 'security15pwd';
        $this->acls = array(
            'module_admin' => false,
            'module_setup' => false,
            'controller_staticpage' => false,
            'controller_helppage' => false,
            'controller_language' => true
        );
        parent::setUp();
    }
}