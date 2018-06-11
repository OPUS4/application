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
 * @category    Application Unit Test
 * @package     Admin_Model
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_Model_FileImportTest extends ControllerTestCase {

    private $model;
    
    private $documentId;
    
    private $importFolder;
    
    public function setUp() {
        parent::setUp();
        
        $this->model = new Admin_Model_FileImport();
        
        $this->importFolder = APPLICATION_PATH . '/tests/workspace/incoming';
        $this->_clearImportFolder();
    }
    
    public function tearDown() {
        $this->_clearImportFolder();
        parent::tearDown();
    }

    private function _clearImportFolder() {
        foreach (new DirectoryIterator($this->importFolder) as $fileInfo) {
            if (!$fileInfo->isDot() && $fileInfo->isFile()) {
                unlink($fileInfo->getPathname());
            }
        }
    }

    public function testConstruct() {
        $this->assertEquals(APPLICATION_PATH . '/workspace/incoming', $this->model->getImportFolder());
    }

    public function testSetGetImportFolder() {
        $this->model->setImportFolder('../workspace/incoming');
        $this->assertEquals('../workspace/incoming', $this->model->getImportFolder());
    }
    
    public function testAddFilesToDocument() {
        $document = $this->createTestDocument();
        
        $this->documentId = $document->store();

        $this->model->setImportFolder($this->importFolder);
        $filePath = $this->importFolder . '/test.txt';
        
        file_put_contents($filePath, 'testfile');
        
        $this->model->addFilesToDocument($this->documentId, array('test.txt'));
        
        $document = new Opus_Document($this->documentId);
        
        $files = $document->getFile();
        
        $this->assertNotNull($files);
        $this->assertEquals(1, count($files));
        $this->assertEquals('test.txt', $files[0]->getPathName());
        $this->assertEquals('test.txt', $files[0]->getLabel());
        
        $this->assertFalse(file_exists($filePath)); // deleted after import
    }
    
    public function testGetNamesOfIncomingFiles() {
        $this->model->setImportFolder($this->importFolder);
        
        file_put_contents($this->importFolder . '/testfile', 'testfile');
        
        $files = $this->model->getNamesOfIncomingFiles();
        
        $this->assertInternalType('array', $files);
        $this->assertEquals(1, count($files));
        $this->assertEquals('testfile', $files[0]);
        
        unlink($this->importFolder . '/testfile');
    }
    
    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage no files for import
     */
    public function testAddFilesToDocumentNoFiles() {
        $this->model->addFilesToDocument(200, null);
    }
    
    /**
     * @expectedException Application_Exception
     * @expectedExceptionMessage no document found for id 500
     */
    public function testAddFilesToDocumentUnknownDocument() {
        $this->model->addFilesToDocument(500, array('testfile'));
    }

    public function testIsValidFileId() {
        $this->assertTrue($this->model->isValidFileId(116), 'Datei ID = 116 (Dokument 91) fehlt.');

        $this->assertFalse($this->model->isValidFileId(5555), 'Datei ID = 5555 sollte nicht gültig sein.');
        $this->assertFalse($this->model->isValidFileId('bla'), 'Datei ID = bla sollte nicht gültig sein.');
        $this->assertFalse($this->model->isValidFileId(null), 'Datei ID = null sollte nicht gültig sein.');
        $this->assertFalse($this->model->isValidFileId(' '), 'Datei ID = \' \' sollte nicht gültig sein.');
    }

    public function testFileBelongsToDocument() {
        $this->assertTrue($this->model->isFileBelongsToDocument(91, 116),
            'Datei ID = 116 sollte zu Dokument ID = 91 gehören.');
        $this->assertTrue($this->model->isFileBelongsToDocument(91, "116"),
            'Datei ID = "116" sollte zu Dokument ID = 91 gehören.');
        $this->assertFalse($this->model->isFileBelongsToDocument(91, 123),
            'Datei ID = 123 sollte nicht Dokument ID = 91 gehören.');
        $this->assertFalse($this->model->isFileBelongsToDocument(91, 5555),
            'Datei ID = 5555 sollte nicht Dokument ID = 91 gehören.');
        $this->assertFalse($this->model->isFileBelongsToDocument(91, null),
            'Datei ID = null sollte nicht Dokument ID = 91 gehören.');
        $this->assertFalse($this->model->isFileBelongsToDocument(91, ' '),
            'Datei ID = \' \' sollte nicht Dokument ID = 91 gehören.');
        $this->assertFalse($this->model->isFileBelongsToDocument(91, 'bla'),
            'Datei ID = \'bla\' sollte nicht Dokument ID = 91 gehören.');
    }

    public function testDeleteFile() {
        $this->model->setImportFolder($this->importFolder);

        $document = $this->createTestDocument();
        $this->documentId = $document->store();

        $filePath1 = $this->importFolder . '/test1.txt';
        file_put_contents($filePath1, 'testfile1');

        $filePath2 = $this->importFolder . '/test2.txt';
        file_put_contents($filePath2, 'testfile2');

        $this->model->addFilesToDocument($this->documentId, array('test1.txt', 'test2.txt'));

        $document = new Opus_Document($this->documentId);

        $files = $document->getFile();

        $this->assertNotNull($files);
        $this->assertEquals(2, count($files));
        $this->assertEquals('test1.txt', $files[0]->getPathName());
        $this->assertEquals('test2.txt', $files[1]->getPathName());

        $this->assertFalse(file_exists($filePath1)); // deleted after import
        $this->assertFalse(file_exists($filePath2)); // deleted after import

        // eigentlicher Test
        $this->model->deleteFile($this->documentId, $files[0]->getId());

        $document = new Opus_Document($this->documentId);

        $files = $document->getFile();

        $this->assertNotNull($files);
        $this->assertEquals(1, count($files));
        $this->assertEquals('test2.txt', $files[0]->getPathName());
    }

}
