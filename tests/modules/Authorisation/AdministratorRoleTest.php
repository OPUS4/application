<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/20/14
 * Time: 10:31 AM
 * To change this template use File | Settings | File Templates.
 */

class AdministratorRoleTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');
    }

    public function tearDown() {
        $this->logoutUser();
        $this->restoreSecuritySetting();
        parent::tearDown();
    }

    /**
     * Prüft, ob ein Nutzer mit der Role administrator Superrechte hat
     */
    public function testAdministratorRoleHasSuperPrivileges() {
        $this->dispatch('/admin');
        $this->assertQuery('//a[@href="/admin/licence"]');
        $this->assertQuery('//a[@href="/admin/documents"]');
        $this->assertQuery('//a[@href="/admin/index/security"]');
        $this->assertQuery('//a[@href="/admin/collectionroles"]');
        $this->assertQuery('//a[@href="/admin/series"]');
        $this->assertQuery('//a[@href="/admin/language"]');
        $this->assertQuery('//a[@href="/admin/dnbinstitute"]');
        $this->assertQuery('//a[@href="/admin/index/info"]');
        $this->assertQuery('//a[@href="/review"]');
        $this->assertQuery('//a[@href="/admin/index/setup"]');
    }

    /**
     * Prüft, ob auf die Seite zur Verwaltung von Lizenzen zugegriffen werden kann.
     */
    public function testAccessLicenceController() {
        $this->useEnglish();
        $this->dispatch('/admin/licence');
        $this->assertQueryContentContains('//html/head/title', 'Admin Licences', 'admin/licence not asserted');
    }

    /**
     * Prüft, das auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testAccessDocumentsController() {
        $this->useEnglish();
        $this->dispatch('/admin/documents');
        $this->assertQueryContentContains('//html/head/title', 'Administration of Documents', 'admin/documents not asserted');
    }

    /**
     * Voller Zugriff auf Review Modul
     */
    public function testAccessReviewModule() {
        $this->useEnglish();
        $this->dispatch('/review');
        $this->assertQueryContentContains('//html/head/title', 'Review Documents', 'review not asserted');
    }

    /**
     * Voller Zugriff auf Setup Modul
     */
    public function testAccessSetupModuleTranslations() {
        $this->useEnglish();
        $this->dispatch('/setup/language');
        $this->assertQueryContentContains('//html/head/title', 'Translations', 'setup/translations not asserted');
    }

    /**
     * Voller Zugriff auf Setup Modul
     */
    public function testAccessSetupModuleStaticPage() {
        $this->useEnglish();
        $this->dispatch('/setup/static-page');
        $this->assertQueryContentContains('//html/head/title', 'Static Pages', 'setup/static-page not asserted');
    }

    /**
     * Voller Zugriff auf Setup Modul
     * Schreibrechte werden vor dem Seitenaufruf weggenommen und anschließen wieder hinzugefügt
     * um Browservergleichbarkeit zu erreichen
     */
    public function testAccessSetupModuleHelpPage() {
        $filePath = APPLICATION_PATH . '/modules/home/language_custom';
        $fileRights = fileperms($filePath);
        chmod($filePath, 0400);
        $this->dispatch('/setup/help-page');
        chmod($filePath, $fileRights);
        $this->assertRedirectTo('/setup/help-page/error', 'setup/help-page not asserted');
    }

    /**
     * Prüft, ob fuer Nutzer mit vollem Zugriff auf Admin Modul der Edit Link in der Frontdoor angezeigt wird.
     */
    public function testActionBoxInFrontdoorPresent() {
        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertQuery('//div[@id="actionboxContainer"]', 'frontdoor not asserted');
    }

}
