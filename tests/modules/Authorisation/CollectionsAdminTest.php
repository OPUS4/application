<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/20/14
 * Time: 11:58 AM
 * To change this template use File | Settings | File Templates.
 */

class CollectionsAdminTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('security9', 'security9pwd');
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
        $this->assertNotQuery('//a[@href="/admin/licence"]');
        $this->assertQuery('//a[@href="/admin/index/info"]');
        $this->assertNotQuery('//a[@href="/admin/documents"]');
        $this->assertNotQuery('//a[@href="/admin/index/security"]');
        $this->assertQuery('//a[@href="/admin/collectionroles"]');
        $this->assertNotQuery('//a[@href="/admin/series"]');
        $this->assertNotQuery('//a[@href="/admin/language"]');
        $this->assertNotQuery('//a[@href="/admin/dnbinstitute"]');
        $this->assertNotQuery('//a[@href="/review"]');
        $this->assertQuery('//a[@href="/admin/index/setup"]');
    }

    /**
     * Prüft, ob die List Collection Seite korrekt aufgerufen wird
     */
    public function testAccessCollectionControllerShowAction() {
        $this->useEnglish();
        $this->dispatch('/admin/collection/show/id/4');
        $this->assertQueryContentContains('//html/head/title', 'List Collection Entries');
        $this->assertQueryContentContains('//div[@class="breadcrumbsContainer"]', 'List Collection Entries');
    }

    /**
     * Prüft, ob die Assign Collection Seite gesperrt ist für security9.
     */
    public function testNoAccessCollectionControllerAssignAction() {
        $this->dispatch('/admin/collection/assign/document/92');
        $this->assertRedirectTo('/auth', 'redirect from admin/collection/assign/document/92 to auth not asserted');
    }
}
