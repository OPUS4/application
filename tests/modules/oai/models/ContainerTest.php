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
 * @category    Application
 * @package     Tests
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Oai_Model_ContainerTest extends ControllerTestCase {
    
    public function testConstructorWithNullArgument() {
        $model = null;
        try {
            $model = new Oai_Model_Container(null);
        }
        catch (Oai_Model_Exception $e) {
            $this->assertEquals('missing parameter docId', $e->getMessage());
        }
        $this->assertTrue(is_null($model));
    }

    public function testConstructorWithInvalidArgument() {
        $model = null;
        try {
            $model = new Oai_Model_Container('foo');
        }
        catch (Oai_Model_Exception $e) {
            $this->assertEquals('invalid value for parameter docId', $e->getMessage());
        }
        $this->assertTrue(is_null($model));
    }

    public function testConstructorWithUnknownDocId() {
        $model = null;
        try {
            $model = new Oai_Model_Container('123456789');
        }
        catch (Oai_Model_Exception $e) {
            $this->assertEquals('requested docId does not exist', $e->getMessage());
        }
        $this->assertTrue(is_null($model));
    }

    public function testConstructorWithUnublishedDocument() {
        $this->markTestSkipped('needs to be tested in non-admin mode');

        $doc = new Opus_Document();
        $doc->setServerState('unpublished');
        $doc->store();

        $model = new Oai_Model_Container($doc->getId());
        $tarball = null;
        try {
            $tarball = $model->getTar();
        }
        catch (Oai_Model_Exception $e) {
            $this->assertEquals('access to requested document is forbidden', $e->getMessage());
        }
        $this->assertTrue(is_null($tarball));

        // cleanup
        $doc->deletePermanent();
    }

    public function testConstructorWithPublishedDocumentWithoutAnyFiles() {
        $doc = new Opus_Document();
        $doc->setServerState('published');
        $doc->store();

        $model = new Oai_Model_Container($doc->getId());
        $tarball = null;
        try {
            $tarball = $model->getTar();
        }
        catch (Oai_Model_Exception $e) {
            $this->assertEquals('requested document does not have any associated files', $e->getMessage());
        }
        $this->assertTrue(is_null($tarball));

        // cleanup
        $doc->deletePermanent();
    }

    public function testFunctionGetName() {
        $doc = new Opus_Document();
        $doc->setServerState('published');
        $file = new Opus_File();
        $file->setPathName('foo.pdf');
        $file->setVisibleInOai(false);
        $doc->addFile($file);
        $doc->store();

        $container = new Oai_Model_Container($doc->getId());
        $this->assertEquals($doc->getId(), $container->getName());
        $doc->deletePermanent();
    }

    public function testDocumentWithRestrictedFile() {
        $doc = new Opus_Document();
        $doc->setServerState('published');
        $file = new Opus_File();
        $file->setPathName('foo.pdf');
        $file->setVisibleInOai(false);
        $doc->addFile($file);
        $doc->store();

        $model = new Oai_Model_Container($doc->getId());
        $tarball = null;
        try {
            $tarball = $model->getTar();
        }
        catch (Oai_Model_Exception $e) {
            $this->assertEquals('access denied on all files that are associated to the requested document', $e->getMessage());
        }
        $this->assertTrue(is_null($tarball));

        // cleanup
        $doc->deletePermanent();        
    }

    public function testDocumentWithUnrestrictedFile() {
        // create test file test.pdf in file system
        $config = Zend_Registry::get('Zend_Config');
        $path = $config->workspacePath . DIRECTORY_SEPARATOR . uniqid();
        mkdir($path, 0777, true);
        $filepath = $path . DIRECTORY_SEPARATOR . 'test.pdf';
        touch($filepath);

        $doc = new Opus_Document();
        $doc->setServerState('published');
        $file = new Opus_File();
        $file->setPathName('test.pdf');
        $file->setTempFile($filepath);
        $file->setVisibleInOai(true);
        $doc->addFile($file);
        $doc->store();

        $model = new Oai_Model_Container($doc->getId());
        $tarball = $model->getTar();
        $this->assertTrue(is_readable($tarball));

        $doc->deletePermanent();
        Opus_Util_File::deleteDirectory($path);
        unlink($tarball);
    }

    public function testDeleteContainer() {
        // create test file test.pdf in file system
        $config = Zend_Registry::get('Zend_Config');
        $path = $config->workspacePath . DIRECTORY_SEPARATOR . uniqid();
        mkdir($path, 0777, true);
        $filepath = $path . DIRECTORY_SEPARATOR . 'test.pdf';
        touch($filepath);

        $doc = new Opus_Document();
        $doc->setServerState('published');
        $file = new Opus_File();
        $file->setPathName('test.pdf');
        $file->setTempFile($filepath);
        $file->setVisibleInOai(true);
        $doc->addFile($file);
        $doc->store();

        $model = new Oai_Model_Container($doc->getId());
        $tarball = $model->getTar();
        $this->assertTrue(is_readable($tarball));
        
        $model->deleteContainer($tarball);
        $this->assertFalse(file_exists($tarball));

        $doc->deletePermanent();
        Opus_Util_File::deleteDirectory($path);
    }

}
