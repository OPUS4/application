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

class CollectionsAdminTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string[] */
    protected $additionalResources = ['database', 'view', 'mainMenu', 'navigation', 'translation'];

    public function setUp(): void
    {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('security9', 'security9pwd');
    }

    public function tearDown(): void
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
        $this->dispatch('/admin');
        $this->assertNotQuery('//a[@href="/admin/licence"]');
        $this->assertQuery('//a[@href="/admin/index/info"]');
        $this->assertNotQuery('//a[@href="/admin/documents"]');
        $this->assertNotQuery('//a[@href="/admin/index/security"]');
        $this->assertQuery('//a[@href="/admin/collectionroles"]');
        $this->assertNotQuery('//a[@href="/admin/series"]');
        $this->assertNotQuery('//a[@href="/admin/language"]');
        $this->assertNotQuery('//a[@href="/admin/dnbinstitute"]');
        $this->assertNotQuery('//a[@href="/review"]');
        $this->assertNotQuery('//a[@href="/admin/index/setup"]');
    }

    /**
     * Prüft, ob die List Collection Seite korrekt aufgerufen wird
     */
    public function testAccessCollectionControllerShowAction()
    {
        $this->useEnglish();
        $this->dispatch('/admin/collection/show/id/4');
        $this->assertQueryContentContains('//html/head/title', 'List Collection Entries');
        $this->assertQueryContentContains('//div[@class="breadcrumbsContainer"]', 'List Collection Entries');
    }

    /**
     * Prüft, ob die Assign Collection Seite gesperrt ist für security9.
     */
    public function testNoAccessCollectionControllerAssignAction()
    {
        $this->dispatch('/admin/collection/assign/document/92');
        $this->assertRedirectTo(
            '/auth/index/rmodule/admin/rcontroller/collection/raction/assign/document/92',
            'redirect from admin/collection/assign/document/92 to auth not asserted'
        );
    }
}
