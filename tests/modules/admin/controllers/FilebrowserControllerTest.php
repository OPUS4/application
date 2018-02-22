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
 * @category    Tests
 * @package     Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @covers Admin_FilebrowserController
 */
class Admin_FilebrowserControllerTest extends ControllerTestCase {
    
    private $documentId;

    public function setUp() {
        parent::setUp();

        $document = $this->createTestDocument();
        $document->setServerState('published');

        $this->documentId = $document->store();
        $this->assertNotNull($this->documentId);
    }

    public function testIndexActionWithMissingParam() {
        $this->dispatch('/admin/filebrowser/index');
        $this->assertResponseCode(500);
        $this->assertContains('missing parameter docId', $this->getResponse()->getBody());
    }

    public function testIndexActionWithInvalidDocId() {
        $this->dispatch('/admin/filebrowser/index/id/invaliddocid');
        $this->assertResponseCode(500);
        $this->assertContains('no document found for id invaliddocid', $this->getResponse()->getBody());
    }

    public function testIndexAction() {
        $this->useGerman();

        $this->dispatch('/admin/filebrowser/index/id/' . $this->documentId);
        $this->assertResponseCode(200);
        $this->assertContains('<div id="filebrowser">', $this->getResponse()->getBody());

        // check breadcrumbs
        $this->verifyBreadcrumbDefined();
        $this->assertQueryContentContains('//div.breadcrumbsContainer//a[@href="/admin/document/index/id/'
            . $this->documentId . '"]',
            "unbenanntes Dokument (id = '{$this->documentId}')");
        $this->assertQueryContentContains('//div.breadcrumbsContainer//a[@href="/admin/filemanager/index/id/'
            . $this->documentId . '/continue/1"]',
            'Dateien');
    }
    
    public function testShowDocInfoOnIndexPage() {
        $this->dispatch('/admin/filebrowser/index/id/146');
        $this->assertResponseCode(200);

        // check for docinfo header
        $this->assertQuery('div#docinfo', 'KOBV');
        $this->assertQuery('div#docinfo', '146');
        $this->assertQuery('div#docinfo', 'Doe, John');
    }

    public function testImportActionWithInvalidMethod() {
        $this->dispatch('/admin/filebrowser/import');
        $this->assertResponseCode(500);
        $this->assertContains('unsupported HTTP method', $this->getResponse()->getBody());
    }

    public function testImportActionWithMissingParam() {
        $this->request
                ->setMethod('POST')
                ->setPost(array());
        $this->dispatch('/admin/filebrowser/import');
        $this->assertResponseCode(500);
        $this->assertContains('missing parameter docId', $this->getResponse()->getBody());
    }

    public function testImportActionWithInvalidDocId() {
        $this->request
                ->setMethod('POST')
                ->setPost(array('id' => 'invaliddocid'));
        $this->dispatch('/admin/filebrowser/import');
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/filebrowser/index/id/invaliddocid');
    }

    public function testImportActionWithEmptySelection() {
        $this->request
                ->setMethod('POST')
                ->setPost(array('id' => $this->documentId));
        $this->dispatch('/admin/filebrowser/import');
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/filebrowser/index/id/' . $this->documentId);
    }

    public function testImportActionWithInvalidParamType() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                        'id' => $this->documentId,
                        'file' => 'invalid'));
        $this->dispatch('/admin/filebrowser/import');
        $this->assertResponseCode(500);
        $this->assertContains('invalid POST parameter', $this->getResponse()->getBody());
    }

    public function testImportAction() {
        $this->markTestIncomplete('TODO');
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                        'docId' => $this->documentId,
                        'file' => 'test.txt'));
        $this->dispatch('/admin/filebrowser/import');
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/filemanager/index/docId/' . $this->documentId);
    }
}

