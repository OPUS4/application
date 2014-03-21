<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 9:39 AM
 * To change this template use File | Settings | File Templates.
 */

/**
 *
 */
class ReviewerAndPartialAdminTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('security5', 'security5pwd');
    }

    public function tearDown() {
        $this->logoutUser();
        $this->restoreSecuritySetting();
        parent::tearDown();
    }

    /**
     * Prüft, ob 'Review' Eintrag im Hauptmenu existiert.
     */
    public function testMainMenu() {
        $this->useEnglish();
        $this->dispatch('/home');
        $this->assertQueryContentContains("//div[@id='header']", 'Administration');
        $this->assertNotQueryContentContains("//div[@id='header']", 'Review');
    }

    /**
     * Prüft, daß Review Link in Admin Menu erscheint.
     */
    public function testAdminMenuFiltering() {
        $this->dispatch('/admin');
        $this->assertQuery('//a[@href="/review"]');
        $this->assertQuery('//a[@href="/admin/licence"]');
        $this->assertQuery('//a[@href="/admin/index/info"]');
    }

    /**
     * Prüft, ob auf die Startseite des Review Modules zugegriffen werden kann.
     */
    public function testAccessReviewModule() {
        $this->useEnglish();
        $this->dispatch('/review');
        $this->assertQueryContentContains('//html/head/title', 'Review Documents');
    }

    /**
     * Prüft, ob auf den LicenceController zugegriffen werden kann.
     */
    public function testAccessLicenceController() {
        $this->useEnglish();
        $this->dispatch('/admin/licence');
        $this->assertQueryContentContains('//html/head/title', 'Admin Licences');
    }

    /**
     * Prüft, ob auf den OaiLinkController zugriffen werden kann.
     */
    public function testAccessOaiLinkController() {
        $this->useEnglish();
        $this->dispatch('/admin/oailink');
        $this->assertQueryContentContains('//html/head/title', 'OAI Links');
    }

    /**
     * Prüft, das nicht auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testNoAccessDocumentsController() {
        $this->dispatch('/admin/documents');
        $this->assertRedirectTo('/auth', 'redirect from /admin/documents to /auth not asserted');
    }

}