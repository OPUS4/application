<?php
/**
 * Created by IntelliJ IDEA.
 * User: michael
 * Date: 3/21/14
 * Time: 10:13 AM
 * To change this template use File | Settings | File Templates.
 */

abstract class AccessModuleAdminOneResourceOnlyTest extends ControllerTestCase {

    protected $username;
    protected $password;

    protected $acls = array(
        'module_admin' => false,
        'indexmaintenance' => false,
        'job' => false
    );

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
     */
    public function testAdminMenuFiltering() {
        $this->dispatch('/admin');
        $this->assertElement('//a[@href="/admin/licence"]', false);
        $this->assertElement('//a[@href="/admin/documents"]', false);
        $this->assertElement('//a[@href="/admin/index/security"]', false);
        $this->assertElement('//a[@href="/admin/collectionroles"]', false);
        $this->assertElement('//a[@href="/admin/series"]', false);
        $this->assertElement('//a[@href="/admin/language"]', false);
        $this->assertElement('//a[@href="/admin/dnbinstitute"]', false);
        $this->assertElement('//a[@href="/admin/index/setup"]', true);
        $this->assertElement('//a[@href="/review"]', false);
        $this->assertElement('//a[@href="/admin/index/info"]', $this->acls['indexmaintenance'] || $this->acls['job']);
    }

    /**
     * Überprüft, ob auf die Seite zur Verwaltung von Lizenzen zugegriffen werden kann.
     */
    public function testAccessLicenceController() {
        $this->dispatch('/admin/licence');
        $this->assertRedirectTo('/auth', 'assert redirect from /admin/licence to auth failed');
    }

    /**
     * Überprüft, ob auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testAccessDocumentsController() {
        $this->dispatch('/admin/documents');
        $this->assertRedirectTo('/auth', 'assert redirect from /admin/documents to auth failed');
    }

    /**
     * Überprüfe, dass keine Zugriff auf Module Review
     */
    public function testNoAccessReviewModule() {
        $this->dispatch('/review');
        $this->assertRedirectTo('/auth', 'assert redirect from /review to auth failed');
    }

    /**
     * Überprüft Zugriff auf die Einträge in der Rubrik "Setup" im Admin Untermenü
     */
    public function testAccessSetupMenu() {
        $this->dispatch('/admin/setup');
        $this->assertElement('//a[@href="/admin/enrichmentkey"]', false);
        $this->assertElement('//a[@href="/setup/help-page"]', false);
        $this->assertElement('//a[@href="/setup/static-page"]', false);
        $this->assertElement('//a[@href="/setup/language"]', false);
    }

    /**
     * Prüft, ob fuer Nutzer mit vollem Zugriff auf Admin Modul der Edit Link in der Frontdoor angezeigt wird.
     */
    public function testActionBoxInFrontdoorPresent() {
        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertElement('//div[@id="actionboxContainer"]', false);
    }

    public function testSubMenuInfo() {
        $this->dispatch('/admin/index/info/menu');
        $this->assertElement('//a[@href="/admin/index/info"]', false);
        $this->assertElement('//a[@href="/admin/oailink"]', true);
        $this->assertElement('//a[@href="/admin/statistic"]', false);
        $this->assertElement('//a[@href="/admin/indexmaintenance"]', $this->acls['indexmaintenance']);
        $this->assertElement('//a[@href="/admin/job"]', $this->acls['job']);
    }

    public function testIndexmaintenance() {
        $this->useEnglish();
        $this->dispatch('/admin/indexmaintenance');
        if ($this->acls['indexmaintenance']) {
            $this->assertQueryContentContains('//h1', 'Solr Index Maintenance');
            $this->assertQueryContentContains('//div', 'This feature is currently disabled.');
        }
        else {
            $this->assertRedirectTo('/auth', 'assert redirect from  to auth failed');
        }
    }

    public function testJob() {
        $this->useEnglish();
        $this->dispatch('/admin/job');
        if ($this->acls['job']) {
            $this->assertQueryContentContains('//legend', 'Job Processing');
            $this->assertQueryContentContains('//div', 'Asynchronous Job Processing is disabled');
        }
        else {
            $this->assertRedirectTo('/auth', 'assert redirect from /admin/job to auth failed');
        }
    }

}
