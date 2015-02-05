<?php
/*
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
 * @category    Application Unit Test
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
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
