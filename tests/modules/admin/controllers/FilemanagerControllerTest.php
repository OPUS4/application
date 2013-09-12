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
 * @category    Application Unit Tests
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_FilemanagerControllerTest extends ControllerTestCase {

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
        $this->verifyBreadcrumbDefined();


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
        $this->dispatch('/admin/filemanager/upload/id/91');
        $this->assertResponseCode(200);
        $this->assertModule('admin');
        $this->assertController('filemanager');
        $this->assertAction('upload');

        $this->validateXHTML();
        $this->verifyBreadcrumbDefined();
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

}

