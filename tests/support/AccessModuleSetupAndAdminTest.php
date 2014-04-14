<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 11:47 AM
 * To change this template use File | Settings | File Templates.
 */

abstract class AccessModuleSetupAndAdminTest extends ControllerTestCase {

    protected $acls = array(
        'module_admin' => false,
        'module_setup' => false,
        'controller_staticpage' => false,
        'controller_helppage' => false,
        'controller_language' => false
    );

    protected $username;
    protected $password;

    public function setUp() {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser($this->username, $this->password);
    }

    public function tearDown() {
        $this->logoutUser();
        $this->restoreSecuritySetting();
        parent::tearDown();
    }

    private function assertElement($xpath, $present = true) {
        if ($present) {
            $this->assertQuery($xpath);
        }
        else {
            $this->assertNotQuery($xpath);
        }
    }

    /**
     * Überprüft, ob nur die erlaubten Einträge im Admin Menu angezeigt werden.
     * Grund fuer if-Abfrage: die Seite kann nicht aufgerufen werden und es wird als content eine leere Variable
     * zurueckgegeben
     */
    public function testAdminMenuFiltering() {
        $this->dispatch('/admin');
        if ($this->acls['module_admin']) {
            $this->assertElement('//a[@href="/admin/licence"]', $this->acls['module_admin']);
            $this->assertElement('//a[@href="/admin/documents"]', $this->acls['module_admin']);
            $this->assertElement('//a[@href="/admin/index/security"]', $this->acls['module_admin']);
            $this->assertElement('//a[@href="/admin/collectionroles"]', $this->acls['module_admin']);
            $this->assertElement('//a[@href="/admin/series"]', $this->acls['module_admin']);
            $this->assertElement('//a[@href="/admin/language"]', $this->acls['module_admin']);
            $this->assertElement('//a[@href="/admin/dnbinstitute"]', $this->acls['module_admin']);
            $this->assertElement('//a[@href="/admin/index/info"]', $this->acls['module_admin']);
            $this->assertElement('//a[@href="/admin/index/setup"]', $this->acls['module_admin']);
            $this->assertElement('//a[@href="/review"]', false);
        }
        else {
            $this->assertRedirectTo('/auth', 'assert redirect from /admin to auth failed');
        }
    }


    /**
     * Überprüft, ob auf die Seite zur Verwaltung von Lizenzen zugegriffen werden kann.
     */
    public function testAccessLicenceController() {
        $this->useEnglish();
        $this->dispatch('/admin/licence');
        if ($this->acls['module_admin']) {
            $this->assertQueryContentContains('//html/head/title', 'Admin Licences');
        }
        else {
            $this->assertRedirectTo('/auth', 'assert redirect from /admin/licence to auth failed');
        }
    }

    /**
     * Überprüft, ob auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testAccessDocumentsController() {
        $this->useEnglish();
        $this->dispatch('/admin/documents');
        if ($this->acls['module_admin']) {
            $this->assertQueryContentContains('//html/head/title', 'Administration of Documents');
        }
        else {
            $this->assertRedirectTo('/auth', 'assert redirect from /admin/documents to auth failed');
        }
    }

    /**
     * Überprüfe, dass keine Zugriff auf Module Review
     */
    public function testNoAccessReviewModule() {
        $this->dispatch('/review');
        $this->assertRedirectTo('/auth', 'assert redirect from /review to auth failed');
    }

    /**
     * Überprüft Zugriff auf Language Controller im Setup Modul
     */
    /* TODO OPUSVIER-3268 - Menü Eintrag wurde versteckt
    public function testAccessSetupModuleTranslations() {
        $this->useEnglish();
        $this->dispatch('/setup/language');
        if ($this->acls['module_setup'] || $this->acls['controller_language']) {
            $this->assertQueryContentContains('//html/head//title', 'Translations');
        }
        else {
            $this->assertRedirectTo('/auth', 'assert redirect from /admin/language to auth failed');
        }
    }
    */

    /**
     * Überprüft Zugriff auf StaticPage Controller im Setup Modul
     */
    public function testAccessSetupModuleStaticPage() {
        $this->useEnglish();
        $this->dispatch('/setup/static-page');
        if ($this->acls['module_setup'] || $this->acls['controller_staticpage']) {
            $this->assertQueryContentContains('//html/head//title', 'Static Pages');
        }
        else {
            $this->assertRedirectTo('/auth', 'assert redirect from /setup/static-page to auth failed');
        }
    }

    /**
     * Überprüft Zugriff auf HelpPage Controller im Setup Modul
     */
    public function testAccessSetupModuleHelpPage() {
        $filePath = APPLICATION_PATH . '/modules/home/language_custom';
        $fileRights = fileperms($filePath);
        chmod($filePath, 0400);
        $this->dispatch('/setup/help-page');
        chmod($filePath, $fileRights);
        if ($this->acls['module_setup'] || $this->acls['controller_helppage']) {
            $this->assertRedirectTo('/setup/help-page/error', 'setup/help-page not asserted');
        }
        else {
            $this->assertRedirectTo('/auth', 'assert redirect from /setup/help-page to auth failed');
        }
    }

    /**
     * Überprüft Zugriff auf die Einträge in der Rubrik "Setup" im Admin Untermenü
     */
    public function testAccessSetupMenu() {
        $this->dispatch('/admin/index/setup');
        if ($this->acls['module_admin']) {
            $this->assertElement('//a[@href="/admin/enrichmentkey"]', $this->acls['module_admin']);
            $this->assertElement('//a[@href="/setup/help-page"]', $this->acls['module_admin'] && ($this->acls['module_setup'] || $this->acls['controller_helppage']));
            $this->assertElement('//a[@href="/setup/static-page"]', $this->acls['module_admin'] && ($this->acls['module_setup'] || $this->acls['controller_staticpage']));
            /* TODO OPUSVIER-3268 - Menu Eintrag wurde versteckt.
             $this->assertElement('//a[@href="/setup/language"]', $this->acls['module_admin'] && ($this->acls['module_setup'] || $this->acls['controller_language']));
            */
        }
        else {
            $this->assertRedirectTo('/auth', 'assert redirect from /admin to auth failed');
        }
    }


    /**
     * Prüft, ob fuer Nutzer mit vollem Zugriff auf Admin Modul der Edit Link in der Frontdoor angezeigt wird.
     */
    public function testActionBoxInFrontdoorPresent() {
        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertElement('//div[@id="actionboxContainer"]', $this->acls['module_admin']);
    }

}
