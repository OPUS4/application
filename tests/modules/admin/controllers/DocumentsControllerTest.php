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
 * @category    Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Basic unit test for the documents controller in the admin module.
 *
 * @covers Admin_DocumentsController
 */
class Admin_DocumentsControllerTest extends ControllerTestCase {

    /**
     * Test index action.
     */
    public function testIndexAction() {
        $this->dispatch('/admin/documents');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('index');
    }

    /**
     * Regression test for OPUSVIER-2540
     */
    public function testCollectionRoleNameGetsTranslatedForDDC() {
        $this->dispatch('/admin/documents/index/collectionid/2');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $body = $this->getResponse()->getBody();
        $this->assertNotContains('ddc', $body);
        $this->assertTrue(strstr($body, '<b>Dewey Decimal Classification</b>') || strstr($body, '<b>DDC-Klassifikation</b>'));
    }

    /**
     * Regression test for OPUSVIER-2540
     */
    public function testCollectionRoleNameGetsTranslatedForUserCollection() {
        $cr = new Opus_CollectionRole();
        $cr->setName('foo');
        $cr->setOaiName('foo');
        $cr->store();

        $this->dispatch('/admin/documents/index/collectionid/' . $cr->getId());
        $cr->delete();

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $this->assertNotContains('<b>foo</b>', $this->getResponse()->getBody());
    }

    public function testShowAllDocsForDDCCollection() {
        $role = new Opus_CollectionRole(2);
        $displayBrowsing = $role->getDisplayBrowsing();
        $role->setDisplayBrowsing('Name');
        $role->store();

        $this->dispatch('/admin/documents/index/collectionid/74');

        // undo changes
        $role->setDisplayBrowsing($displayBrowsing);
        $role->store();

        $this->assertContains('<b>62 Ingenieurwissenschaften</b>', $this->getResponse()->getBody());
        $this->assertNotContains('<b>Ingenieurwissenschaften</b>', $this->getResponse()->getBody());
    }

    public function testShowAllDocsForBklCollection() {
        $role = new Opus_CollectionRole(7);
        $displayBrowsing = $role->getDisplayBrowsing();
        $role->setDisplayBrowsing('Name');
        $role->store();
        
        $this->dispatch('/admin/documents/index/collectionid/15028');

        // undo changes
        $role->setDisplayBrowsing($displayBrowsing);
        $role->store();

        $this->assertContains('<b>52.00 Maschinenbau, Energietechnik, Fertigungstechnik: Allgemeines</b>', $this->getResponse()->getBody());
        $this->assertNotContains('<b>Maschinenbau, Energietechnik, Fertigungstechnik: Allgemeines</b>', $this->getResponse()->getBody());
    }
    
    public function testShowHitsPerPageLinks() {
        $this->dispatch('/admin/documents');
        $this->assertQueryCount('div.itemCountLinks//li', 4);
        $this->assertQueryCount('div.itemCountLinks//a', 3); // einer ist aktiv und kein Link
    }
    
    public function testShowHitsPerPageOptionAsLink() {
        $this->dispatch('/admin/documents/index/hitsperpage/10');
        
        $this->assertQueryContentContains("div.itemCountLinks//a", '50');
        $this->assertQueryContentContains('div.itemCountLinks//a', '100');
    }
    
    public function testShowSelectedHitsPerPageOptionNotAsLink() {
        $this->dispatch('/admin/documents/index/hitsperpage/10');
        
        $this->assertQueryCount("a[@href='" . $this->getRequest()->getBaseUrl() . "/admin/documents/index/hitsperpage/10']", 0);
    }
    
    public function testSelectHitsPerPage() {
        $this->dispatch('/admin/documents/index/state/unpublished/hitsperpage/8');
        $this->assertQueryCount('span.title', 8);
    }
    
    public function testShowAllHits() {
        $docFinder = new Opus_DocumentFinder();
        $docFinder->setServerState('unpublished');
        
        $unpublishedDocs = $docFinder->count();
        
        $this->dispatch('/admin/documents/index/state/unpublished/hitsperpage/all');
        $this->assertQueryCount('span.title', $unpublishedDocs);
    }
    
    public function testHitsPerPageBadParameter() {
        $docFinder = new Opus_DocumentFinder();

        $this->dispatch('/admin/documents/index/state/unpublished/hitsperpage/dummy');
        $this->assertQueryCount('span.title', 10); // default
    }
    
    public function testConfigureDefaultHitsPerPage() {
        $config = Zend_Registry::get('Zend_Config');
        $config->admin->documents->maxDocsDefault = 7;
        
        $this->dispatch('/admin/documents');
        $this->assertQueryCount('span.title', 7);
    }
    
    public function testConfigureHitsPerPageOptions() {
        $config = Zend_Registry::get('Zend_Config');
        $config->admin->documents->maxDocsOptions = "20,60,all";
        
        $this->dispatch('/admin/documents');
        $this->assertQueryContentContains("div.itemCountLinks//a", '20');
        $this->assertQueryContentContains('div.itemCountLinks//a', '60');
        $this->assertQueryCount("a[@href='" . $this->getRequest()->getBaseUrl() . "/admin/documents/index/hitsperpage/all']", 1);
    }
    
    public function testShowEditLink() {
        $this->dispatch('/admin/documents');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('documents');
        $this->assertAction('index');
        
        $this->assertQueryCount("td.edit/a", 10);
        $this->assertXpathCount('//a[contains(@href, "/admin/document/edit/id/")]', 10);
    }

    /**
     * Regression test for OPUSVIER-2401.
     */
    public function testLinkForStateAuditedPresent() {
        $this->dispatch('/admin/documents');
        $this->assertQuery("//a[@href='/admin/documents/index/state/published']");
        $this->assertQuery("//a[@href='/admin/documents/index/state/audited']");
    }

    public function testShowAuthorFilter()
    {
        $person = new Opus_Person();
        $person->setLastName('Test');
        $person->setFirstName('Justa');
        $person->setIdentifierOrcid('0000-0000-0000-0001');
        $person->setIdentifierGnd('123456789');
        $person->setIdentifierMisc('ID1234');
        $person->store();

        $this->dispatch(
            '/admin/documents/index/state/all/role/author/last_name/Test/first_name/Justa' .
            '/identifier_orcid/0000-0000-0000-0001/identifier_gnd/123456789/identifier_misc/ID1234'
        );

        $this->assertQueryContentContains('li.identifier_orcid', '0000-0000-0000-0001');
        $this->assertQueryContentContains('li.identifier_gnd', '123456789');
        $this->assertQueryContentContains('li.identifier_misc', 'ID1234');

    }

}

