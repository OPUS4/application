<?php

/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * TODO statt der Redirect Prüfung (für Access) eine Funktion definieren (assertNoAccess)
 */
abstract class AccessModuleAdminOneResourceOnlyTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string[] */
    protected $additionalResources = ['database', 'view', 'mainMenu', 'navigation', 'translation'];

    /** @var array */
    private $acls = [
        'module_admin'     => false,
        'indexmaintenance' => false,
        'job'              => false,
    ];

    /**
     * @param string     $username
     * @param string     $password
     * @param null|array $acls
     */
    public function setUpTests($username, $password, $acls = null)
    {
        $this->enableSecurity();
        $this->acls = $acls;
        $this->loginUser($username, $password);
    }

    public function tearDown(): void
    {
        $this->logoutUser();
        $this->restoreSecuritySetting();
        parent::tearDown();
    }

    /**
     * @param string $xpath
     * @param bool   $present
     */
    private function assertElement($xpath, $present = true)
    {
        if ($present) {
            $this->assertQuery($xpath);
        } else {
            $this->assertNotQuery($xpath);
        }
    }

    /**
     * Überprüft, ob nur die erlaubten Einträge im Admin Menu angezeigt werden.
     */
    public function testAdminMenuFiltering()
    {
        $this->dispatch('/admin');
        $this->assertElement('//a[@href="/admin/licence"]', false);
        $this->assertElement('//a[@href="/admin/documents"]', false);
        $this->assertElement('//a[@href="/admin/index/security"]', false);
        $this->assertElement('//a[@href="/admin/collectionroles"]', false);
        $this->assertElement('//a[@href="/admin/series"]', false);
        $this->assertElement('//a[@href="/admin/language"]', false);
        $this->assertElement('//a[@href="/admin/dnbinstitute"]', false);
        $this->assertElement('//a[@href="/admin/index/setup"]', $this->acls['module_setup']);
        $this->assertElement('//a[@href="/review"]', false);
        $this->assertElement('//a[@href="/admin/index/info"]', $this->acls['indexmaintenance'] || $this->acls['job']);
    }

    /**
     * Überprüft, ob auf die Seite zur Verwaltung von Lizenzen zugegriffen werden kann.
     */
    public function testAccessLicenceController()
    {
        $this->dispatch('/admin/licence');
        $this->assertRedirectTo(
            '/auth/index/rmodule/admin/rcontroller/licence/raction/index',
            'assert redirect from /admin/licence to auth failed'
        );
    }

    /**
     * Überprüft, ob auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testAccessDocumentsController()
    {
        $this->dispatch('/admin/documents');
        $this->assertRedirectTo(
            '/auth/index/rmodule/admin/rcontroller/documents/raction/index',
            'assert redirect from /admin/documents to auth failed'
        );
    }

    /**
     * Überprüfe, dass keine Zugriff auf Module Review
     */
    public function testNoAccessReviewModule()
    {
        $this->dispatch('/review');
        $this->assertRedirectTo(
            '/auth/index/rmodule/review/rcontroller/index/raction/index',
            'assert redirect from /review to auth failed'
        );
    }

    /**
     * Überprüft Zugriff auf die Einträge in der Rubrik "Setup" im Admin Untermenü
     */
    public function testAccessSetupMenu()
    {
        $this->dispatch('/admin/setup');
        $this->assertElement('//a[@href="/admin/enrichmentkey"]', false);
        $this->assertElement('//a[@href="/setup/helppage"]', false);
        $this->assertElement('//a[@href="/setup/translation"]', false);
        $this->assertElement('//a[@href="/setup/language"]', false);
    }

    /**
     * Prüft, ob fuer Nutzer mit vollem Zugriff auf Admin Modul der Edit Link in der Frontdoor angezeigt wird.
     */
    public function testActionBoxInFrontdoorPresent()
    {
        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertElement('//div[@id="actionboxContainer"]', false);
    }

    public function testSubMenuInfo()
    {
        $this->dispatch('/admin/index/info/menu');
        $this->assertElement('//a[@href="/admin/index/info"]', false);
        $this->assertElement('//a[@href="/admin/oailink"]', true);
        $this->assertElement('//a[@href="/admin/statistic"]', false);
        $this->assertElement('//a[@href="/admin/indexmaintenance"]', $this->acls['indexmaintenance']);
        $this->assertElement('//a[@href="/admin/job"]', $this->acls['job']);
    }

    public function testIndexmaintenance()
    {
        $this->useEnglish();
        $this->dispatch('/admin/indexmaintenance');
        if ($this->acls['indexmaintenance']) {
            $this->assertQueryContentContains('//h1', 'Solr Index Maintenance');
            $this->assertQueryContentContains('//div', 'This feature is currently disabled.');
        } else {
            $this->assertRedirectTo(
                '/auth/index/rmodule/admin/rcontroller/indexmaintenance/raction/index',
                'assert redirect from  to auth failed'
            );
        }
    }

    public function testJob()
    {
        $this->useEnglish();
        $this->dispatch('/admin/job');
        if ($this->acls['job']) {
            $this->assertQueryContentContains('//legend', 'Job Processing');
            $this->assertQueryContentContains('//div', 'Asynchronous Job Processing is disabled');
        } else {
            $this->assertRedirectTo(
                '/auth/index/rmodule/admin/rcontroller/job/raction/index',
                'assert redirect from /admin/job to auth failed'
            );
        }
    }
}
