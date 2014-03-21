<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 11:41 AM
 * To change this template use File | Settings | File Templates.
 */

class AccountSecurity20Test extends AccessModuleAdminOneResourceOnlyTest {

    public function setUp() {
        $this->username = 'security20';
        $this->password = 'security20pwd';
        $this->acls = array(
            'module_admin' => true,
            'indexmaintenance' => false,
            'job' => true
        );
        parent::setUp();
    }
}
