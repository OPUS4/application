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

class ReviewerAndPartialAdminTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string[] */
    protected $additionalResources = ['database', 'translation', 'view', 'mainMenu', 'navigation'];

    public function setUp(): void
    {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('security5', 'security5pwd');
    }

    public function tearDown(): void
    {
        $this->logoutUser();
        $this->restoreSecuritySetting();
        parent::tearDown();
    }

    /**
     * Prüft, ob 'Review' Eintrag im Hauptmenu existiert.
     */
    public function testMainMenu()
    {
        $this->useEnglish();
        $this->dispatch('/home');
        $this->assertQueryContentContains("//div[@id='header']", 'Administration');
        $this->assertNotQueryContentContains("//div[@id='header']", 'Review');
    }

    /**
     * Prüft, daß Review Link in Admin Menu erscheint.
     */
    public function testAdminMenuFiltering()
    {
        $this->dispatch('/admin');
        $this->assertQuery('//a[@href="/review"]');
        $this->assertQuery('//a[@href="/admin/licence"]');
        $this->assertQuery('//a[@href="/admin/index/info"]');
    }

    /**
     * Prüft, ob auf die Startseite des Review Modules zugegriffen werden kann.
     */
    public function testAccessReviewModule()
    {
        $this->useEnglish();
        $this->dispatch('/review');
        $this->assertQueryContentContains('//html/head/title', 'Review Documents');
    }

    /**
     * Prüft, ob auf den LicenceController zugegriffen werden kann.
     */
    public function testAccessLicenceController()
    {
        $this->useEnglish();
        $this->dispatch('/admin/licence');
        $this->assertQueryContentContains('//html/head/title', 'Admin Licences');
    }

    /**
     * Prüft, ob auf den OaiLinkController zugriffen werden kann.
     */
    public function testAccessOaiLinkController()
    {
        $this->useEnglish();
        $this->dispatch('/admin/oailink');
        $this->assertQueryContentContains('//html/head/title', 'OAI Links');
    }

    /**
     * Prüft, das nicht auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testNoAccessDocumentsController()
    {
        $this->dispatch('/admin/documents');
        $this->assertRedirectTo(
            '/auth/index/rmodule/admin/rcontroller/documents/raction/index',
            'redirect from /admin/documents to /auth not asserted'
        );
    }
}
