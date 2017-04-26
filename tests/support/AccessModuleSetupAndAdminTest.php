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
abstract class AccessModuleSetupAndAdminTest extends ControllerTestCase {

    private $acls = array(
        'module_admin' => false,
        'module_setup' => false,
        'controller_staticpage' => false,
        'controller_helppage' => false,
        'controller_language' => false
    );

    public function setUpTests($username = null, $password = null, $acls) {
        $this->acls = $acls;
        $this->enableSecurity();
        $this->loginUser($username, $password);
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
            $this->assertRedirectTo(
                '/auth/index/rmodule/admin/rcontroller/index/raction/index',
                'assert redirect from /admin to auth failed'
            );
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
            $this->assertRedirectTo(
                '/auth/index/rmodule/admin/rcontroller/licence/raction/index',
                'assert redirect from /admin/licence to auth failed'
            );
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
            $this->assertRedirectTo(
                '/auth/index/rmodule/admin/rcontroller/documents/raction/index',
                'assert redirect from /admin/documents to auth failed'
            );
        }
    }

    /**
     * Überprüfe, dass keine Zugriff auf Module Review
     */
    public function testNoAccessReviewModule() {
        $this->dispatch('/review');
        $this->assertRedirectTo(
            '/auth/index/rmodule/review/rcontroller/index/raction/index',
            'assert redirect from /review to auth failed'
        );
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
            $this->assertRedirectTo(
                '/auth/index/rmodule/setup/rcontroller/static-page/raction/index',
                'assert redirect from /setup/static-page to auth failed'
            );
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
            $this->assertRedirectTo(
                '/auth/index/rmodule/setup/rcontroller/help-page/raction/index',
                'assert redirect from /setup/help-page to auth failed'
            );
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
            $this->assertRedirectTo(
                '/auth/index/rmodule/admin/rcontroller/index/raction/setup',
                'assert redirect from /admin to auth failed'
            );
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
