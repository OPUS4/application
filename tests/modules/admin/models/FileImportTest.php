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
class Admin_Model_FileImportTest extends ControllerTestCase {           #

    private $model;
    
    private $documentId;
    
    private $importFolder;
    
    public function setUp() {
        parent::setUp();
        
        $this->model = new Admin_Model_FileImport();
        
        $this->importFolder = APPLICATION_PATH . '/tests/workspace/incoming';
        $this->clearImportFolder();
    }
    
    public function tearDown() {
        if (isset($documentId)) {
            try {
                $document = new Opus_Document($this->documentId);
                $document->deletePermanent();
            }
            catch (Opus_Model_NotFoundException $omnfe) {
            }
        }
        $this->clearImportFolder();
        parent::tearDown();
    }
    
    public function testConstruct() {
        $this->assertEquals(APPLICATION_PATH . '/workspace/incoming', $this->model->getImportFolder());
    }

    public function testSetGetImportFolder() {
        $this->model->setImportFolder('../workspace/incoming');
        $this->assertEquals('../workspace/incoming', $this->model->getImportFolder());
    }
    
    public function testAddFilesToDocument() {
        $document = new Opus_Document();
        
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
    
    public function clearImportFolder() {
        foreach (new DirectoryIterator($this->importFolder) as $fileInfo) {
            if (!$fileInfo->isDot() && $fileInfo->isFile()) {
                unlink($fileInfo->getPathname());
            }
        }
    }

}
