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
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO einiges durch Selenium abgedeckt; Unit Tests vielleicht möglich
 */

/**
 * @covers Admin_FilemanagerController
 */
class Admin_FilemanagerControllerTest extends ControllerTestCase {

    private $documentId;

    public function tearDown() {
        $this->removeDocument($this->documentId);
        parent::tearDown();
    }

    /**
     * Basic unit test checks that error controller is not called.
     */
    public function testIndexAction() {
        $this->dispatch('/admin/filemanager/index/id/91');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('index');

        $this->validateXHTML();

        // check breadcrumbs
        $this->verifyBreadcrumbDefined();
        $this->assertQueryContentContains('//div.breadcrumbsContainer//a[@href="/admin/document/index/id/91"]',
            'This is a pdf test document');

        // TODO DocInfo
        /*
        $this->assertQuery('div#docinfo', 'This is a pdf test document');
        $this->assertQuery('div#docinfo', '91');
        $this->assertQuery('div#docinfo', 'Doe, John');
        */

        // TODO Formular

    }

    public function testIndexActionBadId() {
        $this->dispatch('/admin/filemanager/index/id/bla');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testIndexActionUnknownId() {
        $this->dispatch('/admin/filemanager/index/id/1000');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testIndexActionNoId() {
        $this->dispatch('/admin/filemanager/index');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testUploadAction() {
        $this->useGerman();

        $this->dispatch('/admin/filemanager/upload/id/91');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('upload');

        $this->validateXHTML();

        // check breadcrumbs
        $this->verifyBreadcrumbDefined();
        $this->assertQueryContentContains('//div.breadcrumbsContainer//a[@href="/admin/document/index/id/91"]',
            'This is a pdf test document');
        $this->assertQueryContentContains('//div.breadcrumbsContainer//a[@href="/admin/filemanager/index/id/91"]',
            'Dateien');
    }

    public function testUploadActionBadId() {
        $this->dispatch('/admin/filemanager/upload/id/bla');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testUploadActionUnknownId() {
        $this->dispatch('/admin/filemanager/upload/id/1000');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testUploadActionNoId() {
        $this->dispatch('/admin/filemanager/upload');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testMd5HashValuesPresent() {
        $hash = '1ba50dc8abc619cea3ba39f77c75c0fe';
        $this->dispatch('/admin/filemanager/index/id/91');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('index');
        $this->assertQueryContentContains('//div.Hash0-data', $hash);
        $this->assertNotQuery('//div.Hash0-data//div.hashsoll/span.hash-label');
        $this->assertNotQuery('//div.hashist');
    }

    public function testSha512HashValuesPresent() {
        $hash = '24bb2209810bacb3f9c05e08a08aec9ead4ac606fdc7c9d6c5fadffcf66f1e56396fdf46424cf52ef916f9e51f8178fb618c787f952d35aaf6d9079bbc9a50ad';
        $this->dispatch('/admin/filemanager/index/id/91');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('index');
        $this->assertQueryContentContains('//div.Hash1-data', $hash);
        $this->assertNotQuery('//div.Hash1-data//div.hashsoll/span.hash-label');
        $this->assertNotQuery('//div.hashist');
    }

    /**
     * Wenn beim Hash Fehler auftreten, werden Soll und Ist mit Labeln angezeigt.
     */
    public function testHashErrorShown() {
        $this->dispatch('/admin/filemanager/index/id/121');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('index');

        $this->assertQuery('//div.Hash0-data//div.hashsoll/span.hash-label');
        $this->assertQuery('//div.Hash0-data//div.hashist/span.hash-label');
        $this->assertQuery('//div.Hash1-data//div.hashsoll/span.hash-label');
        $this->assertQuery('//div.Hash1-data//div.hashist/span.hash-label');
    }

    public function testShowMissingFileError() {
        $this->useEnglish();

        $this->dispatch('/admin/filemanager/index/id/122'); // Datei 123 fuer Dokument 122 fehlt in Testdaten
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('index');

        $this->assertQueryContentContains('//div#FileManager-Files-File0-FileLink-element//li',
            'File does not exist!');
    }

    public function testDontShowMissingFileError() {
        $this->dispatch('/admin/filemanager/index/id/121');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('index');

        $this->assertNotQuery('//div#FileManager-Files-File0-FileLink-element//ul[@class="errors"]');
    }

    public function testDeleteActionBadDocIdErrorMessage() {
        $this->dispatch('/admin/filemanager/delete/id/9999/fileId/125');

        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testDeleteActionSyntaxInvalidDocIdErrorMessage() {
        $this->dispatch('/admin/filemanager/delete/id/foo/fileId/125');

        $this->verifyFlashMessage('admin_document_error_novalidid');
    }

    public function testDeleteActionBadFileIdErrorMessage() {
        $this->dispatch('/admin/filemanager/delete/id/124/fileId/400');

        $this->verifyFlashMessage('admin_filemanager_error_novalidid');
    }

    public function testDeleteActionSyntaxInvalidFileIdErrorMessage() {
        $this->dispatch('/admin/filemanager/delete/id/124/fileId/foo');

        $this->verifyFlashMessage('admin_filemanager_error_novalidid');
    }

    public function testDeleteActionFileDoesNotBelongToDocErrorMessage() {
        $this->dispatch('/admin/filemanager/delete/id/146/fileId/125');

        $this->verifyFlashMessage('admin_filemanager_error_filenotlinkedtodoc');
    }

    public function testDeleteAction() {
        $this->dispatch('/admin/filemanager/delete/id/91/fileId/116');

        $this->validateXHTML();
        $this->verifyBreadcrumbDefined();
        $this->assertQueryContentContains('//div.breadcrumbsContainer//a[@href="/admin/document/index/id/91"]',
            'This is a pdf test document');
    }

    public function testResetFormAction() {
        $this->dispatch('/admin/filemanager/index/id/91/continue/1');

        $this->assertNotQuery('//form[@action="/admin/filemanager/index/id/91/continue/1"]');
        $this->assertQuery('//form[@action="/admin/filemanager/index/id/91"]');
    }

    public function testRemoveGuestAccess() {
        $document = $this->createTestDocument();
        $file = $document->addFile();
        $file->setPathName('testdatei.txt');
        $this->documentId = $document->store();

        $document = new Opus_Document($this->documentId);

        $fileId = $document->getFile(0)->getId();

        $roleGuest = Opus_UserRole::fetchByName('guest');
        $files = $roleGuest->listAccessFiles();
        $this->assertContains($fileId, $files);

        $this->getRequest()->setMethod('POST')->setPost(array(
            'FileManager' => array(
                'Files' => array(
                    'File0' => array(
                        'Id' => $fileId,
                        'FileLink' => $fileId,
                        'Language' => 'deu',
                        'Comment' => 'Testkommentar',
                        'Roles' => array('administrator'),
                        'SortOrder' => '0'
                    )
                ),
                'Save' => 'Speichern'
            )
        ));

        $this->dispatch('/admin/filemanager/index/id/' . $this->documentId);
        $this->assertResponseCode(302);
        $this->assertRedirectTo('/admin/document/index/id/' . $this->documentId);

        $roleGuest = Opus_UserRole::fetchByName('guest');
        $files = $roleGuest->listAccessFiles();
        $this->assertNotContains($fileId, $files);
    }

    public function testBadDocIdNotDisplayedOnPage() {
        $this->dispatch('/admin/filemanager/delete/id/dummyDocId/fileId/125');
        $this->assertRedirectTo('/admin/documents');
        $this->verifyNotFlashMessageContains('dummyDocId');
    }

    public function testBadFileIdNotDisplayedOnPage() {
        $this->dispatch('/admin/filemanager/delete/id/124/fileId/dummyFileId');
        $this->assertRedirectTo('/admin/filemanager/index/id/124');
        $this->verifyNotFlashMessageContains('dummyFieldId');
    }

    /**
     * Prüft ob das Upload-Datum der Datei bei der Erstellung gesetzt wird.
     */
    public function testFileUploadDate() {
        $this->useGerman();
        $file = $this->createTestFile('foo.pdf');
        $file->setVisibleInOai(false);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addFile($file);

        $docId = $doc->store();

        $dateNow = new Opus_Date();
        $dateNow->setNow();

        $this->dispatch('admin/filemanager/index/id/' . $docId);
        $this->assertQueryContentContains('//label', 'Datum des Hochladens');
        $this->assertQueryContentContains('//div', $dateNow->getDay() . '.'. $dateNow->getMonth() . '.' . $dateNow->getYear());
    }

    /**
     * Nach einer Änderung in der Datei soll das ursprüngliche Upload-Datum gesetzt bleiben.
     */
    public function testFileUploadDateAfterModification() {
        $this->useGerman();
        $doc = new Opus_Document(305);

        foreach ($doc->getFile() as $file) {
            $file->setComment(rand());
        }
        $docId = $doc->store();
        $this->dispatch('admin/filemanager/index/id/' . $docId);
        $this->assertQueryContentContains('//div', '10.12.2013');
    }

    /**
     * Asserts that document files are displayed up in the correct order, if the sort order field is set.
     */
    public function testFileSortOrder() {
        $this->dispatch('/admin/filemanager/index/id/155');
        $body = $this->_response->getBody();
        $positionFile1 = strpos($body, 'oai_invisible.txt');
        $positionFile2 = strpos($body, 'test.txt');
        $positionFile3 = strpos($body, 'test.pdf');
        $positionFile4 = strpos($body, 'frontdoor_invisible.txt');
        $this->assertTrue($positionFile1 < $positionFile2);
        $this->assertTrue($positionFile2 < $positionFile3);
        $this->assertTrue($positionFile3 < $positionFile4);
    }

    /**
     * Asserts that document files are displayed up in the correct order, if the sort order field is NOT set.
     */
    public function testDocumentFilesWithoutSortOrder() {
        $this->dispatch('/admin/filemanager/index/id/92');
        $body = $this->_response->getBody();
        $positionFile1 = strpos($body, 'test.xhtml');
        $positionFile2 = strpos($body, 'datei mit unüblichem Namen.xhtml');
        $this->assertTrue($positionFile1 < $positionFile2);
    }
}

