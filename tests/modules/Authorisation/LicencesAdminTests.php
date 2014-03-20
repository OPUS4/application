<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/20/14
 * Time: 2:36 PM
 * To change this template use File | Settings | File Templates.
 */

class LicencesAdminTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('security2', 'security2pwd');
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
        $this->assertQuery('//a[@href="/admin/licence"]');
        $this->assertQuery('//a[@href="/admin/index/info"]');
        $this->assertNotQuery('//a[@href="/admin/documents"]');
        $this->assertNotQuery('//a[@href="/admin/index/security"]');
        $this->assertNotQuery('//a[@href="/admin/collectionroles"]');
        $this->assertNotQuery('//a[@href="/admin/series"]');
        $this->assertNotQuery('//a[@href="/admin/language"]');
        $this->assertNotQuery('//a[@href="/admin/dnbinstitute"]');
        $this->assertQuery('//a[@href="/admin/index/setup"]');
        $this->assertNotQuery('//a[@href="/review"]');
    }

    /**
     * Prüft, ob auf die Seite zur Verwaltung von Lizenzen zugegriffen werden kann.
     */
    public function testAccessLicenceController() {
        $this->useEnglish();
        $this->dispatch('/admin/licence');
        $this->assertQueryContentContains('//html/head/title', 'Admin Licences');
    }

    /**
     * Prüft, das nicht auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testNoAccessDocumentsController() {
        $this->dispatch('/admin/documents');
        $this->assertRedirectTo('/auth');
    }

    /**
     * Prüft, ob fuer Nutzer mit vollem Zugriff auf Admin Modul der Edit Link in der Frontdoor angezeigt wird.
     */
    public function testEditLinkInFrontdoorNotPresent() {
        $this->useEnglish();
        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertNotQueryContentContains('//html/body', 'Edit this document');
    }

    public function testNoAccessFilebrowserController() {
        $this->dispatch('/admin/filebrowser/index/docId/92');
        $this->assertRedirectTo('/auth');
    }

    public function testNoAccessWorkflowController() {
        $this->dispatch('/admin/workflow/changestate/docId/300/targetState/deleted');
        $this->assertRedirectTo('/auth');
    }

    public function testNoAccessAccessController() {
        $this->dispatch('/admin/access/listmodule/roleid/2');
        $this->assertRedirectTo('/auth');
    }

}
