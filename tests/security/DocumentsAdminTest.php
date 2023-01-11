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

class DocumentsAdminTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string */
    protected $additionalResources = 'all';

    public function setUp(): void
    {
        parent::setUp();
        $this->enableSecurity();
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
        $this->useEnglish();
        $this->loginUser("security8", "security8pwd");
        $this->dispatch('/admin');
        $this->assertNotQuery('//a[@href="/admin/licence"]');
        $this->assertQuery('//a[@href="/admin/documents"]');
        $this->assertQuery('//a[@href="/admin/index/info"]');
        $this->assertNotQuery('//a[@href="/admin/index/security"]');
        $this->assertNotQuery('//a[@href="/admin/collectionroles"]');
        $this->assertNotQuery('//a[@href="/admin/series"]');
        $this->assertNotQuery('//a[@href="/admin/language"]');
        $this->assertNotQuery('//a[@href="/admin/dnbinstitute"]');
        $this->assertNotQuery('//a[@href="/admin/index/setup"]');
        $this->assertNotQuery('//a[@href="/review"]');
    }

    /**
     * Prüft, ob auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testAccessDocumentsController()
    {
        $this->useEnglish();
        $this->loginUser("security8", "security8pwd");
        $this->dispatch('/admin/documents');
        $this->assertQueryContentContains('//html/head/title', 'Administration of Documents');
    }

    /**
     * Prüft, das auf die Seite Übersichtsseite eines Dokumentes zugegriffen werden kann.
     */
    public function testAccessDocumentController()
    {
        $this->useEnglish();
        $this->loginUser("security8", "security8pwd");
        $this->dispatch('/admin/document/index/id/92');
        $this->assertQueryContentContains('//html/head/title', 'Document');
        $this->assertQueryContentContains('//html/body', 'This is a xhtml test document');
    }

    /**
     * Prüft, ob dem Nutzer die Actionbox für Administratoren angezeigt wird.
     */
    public function testAdminLinksInFrontdoorPresent()
    {
        $this->useEnglish();
        $this->loginUser("security8", "security8pwd");
        $this->dispatch('/frontdoor/index/index/docId/92');

        $baseUri = $this->baseUrl;

        $this->assertQuery('//div[@id="actionboxContainer"]');
        $this->assertQuery("//a[@href=\"$baseUri/admin/document/edit/id/92\"]");
        $this->assertQueryContentContains("//a[@href=\"$baseUri/admin/document/edit/id/92\"]", 'Edit');
        $this->assertQuery("//a[@href=\"$baseUri/admin/filemanager/index/id/92\"]");
        $this->assertQueryContentContains("//a[@href=\"$baseUri/admin/filemanager/index/id/92\"]", 'Files');
        $this->assertQuery("//a[@href=\"$baseUri/admin/document/index/id/92\"]");
        $this->assertQueryContentContains("//a[@href=\"$baseUri/admin/document/index/id/92\"]", 'Administration');

        $this->assertQuery("//a[@href=\"$baseUri/admin/workflow/changestate/docId/92/targetState/restricted\"]");
        $this->assertQueryContentContains(
            "//a[@href=\"$baseUri/admin/workflow/changestate/docId/92/targetState/restricted\"]",
            'Restrict document'
        );
    }

    /**
     * Prueft, ob auf den FilemanagerController zugegriffen werden kann.
     */
    public function testAccessFilemanagerController()
    {
        $this->useEnglish();
        $this->loginUser("security8", "security8pwd");
        $this->dispatch('/admin/filemanager/index/id/92');
        $this->assertQueryContentContains('//html/head/title', 'Files');
        $this->assertQueryContentContains('//html/body', 'test.xhtml');
    }

    public function testAccessCollectionControllerAssignAction()
    {
        $this->useEnglish();
        $this->loginUser("security8", "security8pwd");
        $this->dispatch('/admin/collection/assign/document/92');
        $this->assertQueryContentContains('//html/head/title', 'Assign Collection');
        $this->assertQueryContentContains('//div[@class="breadcrumbsContainer"]', 'Assign Collection');
    }

    public function testNoAccessCollectionControllerShowAction()
    {
        $this->loginUser("security8", "security8pwd");
        $this->dispatch('/admin/collection/show/id/4');
        $this->assertRedirectTo(
            '/auth/index/rmodule/admin/rcontroller/collection/raction/show/id/4',
            'redirection from /admin/collection/show/id/4 to /auth not asserted'
        );
    }

    public function testStateChangeLinks()
    {
        $this->useEnglish();
        $this->loginUser('security8', 'security8pwd');
        $this->dispatch('/admin/document/index/id/96');
        $this->assertQuery("//*[@id='State-Link-published']", 'Publish document');
        $this->assertQuery("//*[@id='State-Link-deleted']", 'Delete document');
        $this->assertNotQuery("//*[@id='State-link-audited']", 'Change state: Audited');
        $this->assertNotQuery("//*[@id='State-link-inprogress']", 'Change state: In Progress');
        $this->assertNotQuery("//*[@id='State-Link-restricted']", 'Restrict document');
    }

    /**
     * Basically, just checks access to WorkflowController.
     */
    public function testDeleteDocument()
    {
        $this->useEnglish();
        $this->loginUser('security8', 'security8pwd');
        $this->dispatch('/admin/workflow/changestate/docId/300/targetState/deleted');
        $this->assertQueryContentContains('//html/head/title', 'Delete document');
        $this->assertQueryContentContains('//html/body', 'Are you sure you want to delete document 300?');
    }

    /**
     * Basically, just checks access to WorkflowController.
     */
    public function testPublishDocument()
    {
        $this->useEnglish();
        $this->loginUser('security8', 'security8pwd');
        $this->dispatch('/admin/workflow/changestate/docId/300/targetState/published');
        $this->assertQueryContentContains('//html/head/title', 'Publish document');
        $this->assertQueryContentContains('//html/body', 'Are you sure you want to publish document 300?');
    }

    public function testAccessFilebrowserController()
    {
        $this->useEnglish();
        $this->loginUser("security8", "security8pwd");
        $this->dispatch('/admin/filebrowser/index/id/92');
        $this->assertQueryContentContains('//html/head/title', 'Filebrowser');
        $this->assertQueryContentContains('//html/body', 'Add files to document with id');
    }

    public function testAccessFrontdoorForUnpublishedDocumentRegression2815()
    {
        $this->useEnglish();
        $this->loginUser('security8', 'security8pwd');
        $this->dispatch('/frontdoor/index/index/docId/101');
        $this->assertNotQueryContentContains('//div', 'Error displaying the document');
        $this->assertNotQueryContentContains('//div', 'Document with ID 101 has not been published yet!');
    }

    public function testAccessToMetadatenOverview()
    {
        $this->loginUser('security8', 'security8pwd');
        $this->dispatch('/admin/document/index/id/146');
        $this->assertNotQuery('//div[@class="messages"]/div[@class="failure"]');
        $this->assertQuery('//h2/span[@class="docid"]');
        $this->assertQueryContentContains('//h2/span[@class="docid"]', '146');
    }

    public function testAccessToMetadatenFormular()
    {
        $this->loginUser('security8', 'security8pwd');
        $this->dispatch('/admin/document/edit/id/146');
        $this->assertNotQuery('//div[@class="messages"]/div[@class="failure"]');
        $this->assertQuery('//h2/span[@class="docid"]');
        $this->assertQueryContentContains('//h2/span[@class="docid"]', '146');
        $this->assertQuery('//input[id="Document-ActionBox-Save"]');
        $this->assertQuery('//select[id="Document-General-Language"]');
    }

    public function testNoAccessToMetadatenOverview()
    {
        $this->loginUser('security9', 'security9pwd');
        $this->dispatch('/admin/document/index/id/146');
        $this->assertRedirectTo('/auth/index/rmodule/admin/rcontroller/document/raction/index/id/146');
    }

    public function testNoAccessToMetadatenFormular()
    {
        $this->loginUser('security9', 'security9pwd');
        $this->dispatch('/admin/document/edit/id/146');
        $this->assertRedirectTo('/auth/index/rmodule/admin/rcontroller/document/raction/edit/id/146');
    }
}
