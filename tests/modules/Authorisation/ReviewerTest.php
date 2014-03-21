<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 9:55 AM
 * To change this template use File | Settings | File Templates.
 */

/**
 *
 */
class ReviewerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('security3', 'security3pwd');
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
        $this->assertQueryContentContains("//div[@id='header']", 'Review');
    }

    /**
     * Prüft, daß nicht auf das Admin Menu zugegriffen werden kann.
     */
    public function testNoAccessAdminMenu() {
        $this->dispatch('/admin');
        $this->assertRedirectTo('/auth', 'redirect to /auth from /admin not asserted');
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
     * Prüft, das nicht auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testNoAccessDocumentsController() {
        $this->dispatch('/admin/documents');
        $this->assertRedirectTo('/auth', 'redirect to /auth from /admin/documents not asserted');
    }

}