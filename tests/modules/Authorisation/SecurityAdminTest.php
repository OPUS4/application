<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 10:02 AM
 * To change this template use File | Settings | File Templates.
 */

class SecurityAdminTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('security10', 'security10pwd');
    }

    public function tearDown() {
        $this->logoutUser();
        $this->restoreSecuritySetting();
        parent::tearDown();
    }


    /**
     * Prüft, ob nur die erlaubten Einträge im Admin Menu angezeigt werden.
     */
    public function testAdminMenuFiltering() {
        $this->dispatch('/admin');
        $this->assertQuery('//a[@href="/admin/index/security"]');
        $this->assertNotQuery('//a[@href="/admin/collectionroles"]');
        $this->assertNotQuery('//a[@href="/admin/documents"]');
        $this->assertNotQuery('//a[@href="/admin/licence"]');
        $this->assertNotQuery('//a[@href="/admin/series"]');
        $this->assertNotQuery('//a[@href="/admin/language"]');
        $this->assertNotQuery('//a[@href="/admin/dnbinstitute"]');
        $this->assertQuery('//a[@href="/admin/index/info"]'); // Untermenü für Informationen
        $this->assertNotQuery('//a[@href="/review"]');
        $this->assertQuery('//a[@href="/admin/index/setup"]');
    }

    public function testAccessAccountController() {
        $this->useEnglish();
        $this->dispatch('/admin/account');
        $this->assertQueryContentContains('//html/head/title', 'Accounts');
        $this->assertQueryContentContains('//html/body', 'Add Account');
    }

    public function testAccessIprangeController() {
        $this->useEnglish();
        $this->dispatch('/admin/iprange');
        $this->assertQueryContentContains('//html/head/title', 'Manage IP Ranges');
        $this->assertQueryContentContains('//html/body', 'Add IP range');
    }

    public function testAccessAccessController() {
        $this->useEnglish();
        $this->dispatch('/admin/access/listmodule/roleid/2');
        $this->assertQueryContentContains('//html/head/title', 'Edit user roles - access control');
    }

}