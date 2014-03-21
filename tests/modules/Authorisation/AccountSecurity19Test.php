<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 10:40 AM
 * To change this template use File | Settings | File Templates.
 */



class AccountSecurity19Test extends AccessModuleAdminOneResourceOnlyTest {

    public function setUp() {
        $this->username = 'security19';
        $this->password = 'security19pwd';
        $this->acls = array(
            'module_admin' => true,
            'indexmaintenance' => true,
            'job' => false
        );
        parent::setUp();
    }
}

