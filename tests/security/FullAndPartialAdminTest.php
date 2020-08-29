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
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class FullAndPartialAdminTest extends ControllerTestCase
{

    protected $configModifiable = true;

    protected $additionalResources = 'all';

    public function setUp()
    {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('security6', 'security6pwd');
    }

    public function tearDown()
    {
        $this->logoutUser();
        $this->restoreSecuritySetting();
        parent::tearDown();
    }

    /**
     * Prüft, ob nur die erlaubten Einträge im Admin Menu angezeigt werden.
     */
    public function testAdminMenuFiltering()
    {
        $this->useEnglish();
        $this->dispatch('/admin');
        $this->assertQuery('//a[@href="/admin/licence"]');
        $this->assertQuery('//a[@href="/admin/documents"]');
        $this->assertQuery('//a[@href="/admin/index/security"]');
        $this->assertQuery('//a[@href="/admin/collectionroles"]');
        $this->assertQuery('//a[@href="/admin/series"]');
        $this->assertQuery('//a[@href="/admin/language"]');
        $this->assertQuery('//a[@href="/admin/dnbinstitute"]');
        $this->assertQuery('//a[@href="/admin/index/info"]');
        $this->assertQuery('//a[@href="/admin/index/setup"]');
        $this->assertNotQuery('//a[@href="/review"]');
    }

    /**
     * Prüft, ob auf die Seite zur Verwaltung von Lizenzen zugegriffen werden kann.
     */
    public function testAccessCollectionRolesController()
    {
        $this->useEnglish();
        $this->dispatch('/admin/collectionroles');
        $this->assertQueryContentContains('//html/head/title', 'Manage Collections', 'manage collections not asserted');
    }

    /**
     * Prüft, das auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testAccessAccountController()
    {
        $this->useEnglish();
        $this->dispatch('/admin/account');
        $this->assertQueryContentContains('//html/head/title', 'Accounts', 'admin/accounts not asserted');
    }

    /**
     * Voller Zugriff auf Admin Modul schließt nicht Zugriff auf Review Modul mit ein.
     */
    public function testNoAccessReviewModule()
    {
        $this->dispatch('/review');
        $this->assertRedirectTo(
            '/auth/index/rmodule/review/rcontroller/index/raction/index',
            'redirect from review to auth not asserted'
        );
    }

    /**
     * Prüft, ob fuer Nutzer mit vollem Zugriff auf Admin Modul der Edit Link in der Frontdoor angezeigt wird.
     */
    public function testEditLinkInFrontdoorPresent()
    {
        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertQuery('//div[@id="actionboxContainer"]', 'actionboxContainer not asserted');
    }
}
