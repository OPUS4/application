<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/19/14
 * Time: 5:20 PM
 * To change this template use File | Settings | File Templates.
 */

class FullAdminTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('security1', 'security1pwd');
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
        $this->assertQuery('//a[@href="/admin/index/setup"]');
        $this->assertQuery('//a[@href="/admin/documents"]');
        $this->assertQuery('//a[@href="/admin/index/security"]');
        $this->assertQuery('//a[@href="/admin/collectionroles"]');
        $this->assertQuery('//a[@href="/admin/series"]');
        $this->assertQuery('//a[@href="/admin/language"]');
        $this->assertQuery('//a[@href="/admin/dnbinstitute"]');
        $this->assertQuery('//a[@href="/admin/index/info"]');
        $this->assertNotQuery('//a[@href="/review"]');
    }

    /**
     * Prüft, ob auf die Seite zur Verwaltung von Lizenzen zugegriffen werden kann.
     */
    public function testAccessLicenceController() {
        $this->useEnglish();
        $this->dispatch('/admin/licence');
        $this->assertQueryContentContains('//html/head/title', 'Admin Licences', 'admin licences not asserted');
    }

    /**
     * Prüft, das auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testAccessDocumentsController() {
        $this->useEnglish();
        $this->dispatch('/admin/documents');
        $this->assertQueryContentContains('//html/head/title', 'Administration of Documents',
            'administration of documents not asserted');
    }


    /**
     * Voller Zugriff auf Admin Modul schließt nicht Zugriff auf Review Modul mit ein.
     */
    public function testNoAccessReviewModule() {
        $this->dispatch('/review');
        $this->assertRedirectTo('/auth', 'redirect from review to auth not asserted');
    }

    /**
     * Voller Zugriff auf Admin Modul schließt nicht Zugriff auf Setup Modul mit ein.
     */
    public function testNoAccessSetupModuleTranslations() {
        $this->dispatch('/setup/language');
        $this->assertRedirectTo('/auth', 'redirect from setup/language to auth not asserted');
    }

    /**
     * Voller Zugriff auf Admin Modul schließt nicht Zugriff auf Setup Modul mit ein.
     */
    public function testNoAccessSetupModuleStaticPage() {
        $this->dispatch('/setup/static-page');
        $this->assertRedirectTo('/auth', 'redirect from setup/static-page to auth not asserted ');
    }

    /**
     * Voller Zugriff auf Admin Modul schließt nicht Zugriff auf Setup Modul mit ein.
     */
    public function testNoAccessSetupModuleHelpPage() {
        $this->dispatch('/setup/help-page');
        $this->assertRedirectTo('/auth', 'redirect from setup/help-page to auth not asserted');
    }

    /**
     * Voller Zugriff auf Admin Modul schließt nicht Zugriff auf Setup Modul mit ein.
     */
    public function testAccessSetupMenu() {
        $this->dispatch('/admin/index/setup');
        $this->assertQuery('//a[@href="/admin/enrichmentkey"]', 'enrichmentkeys not asserted');
        $this->assertNotQuery('//a[@href="/setup/help-page"]', 'help-page available, but should not');
        $this->assertNotQuery('//a[@href="/setup/static-page"]', 'static-page available, but should not');
        $this->assertNotQuery('//a[@href="/setup/language"]', 'language available, but should not');
    }

    /**
     * Prüft, ob fuer Nutzer mit vollem Zugriff auf Admin Modul der Edit Link in der Frontdoor angezeigt wird.
     */
    public function testActionBoxInFrontdoorPresent() {
        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertQuery('//div[@id="actionboxContainer"]', 'actionboxContainer not asserted');
    }
}