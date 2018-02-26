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
 * @package     Oai
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Oai_ContainerControllerTest.
 *
 * @covers Oai_ContainerController
 */
class Oai_ContainerControllerTest extends ControllerTestCase {

    public function testRequestWithoutDocId() {
        $this->dispatch('/oai/container/index');
        $this->assertResponseCode(500);
        $this->assertContains('missing parameter docId',
                $this->getResponse()->getBody());
    }

    public function testRequestInvalidDocId() {
        $this->dispatch('/oai/container/index/docId/foobar');
        $this->assertResponseCode(500);
        $this->assertContains('invalid value for parameter docId',
                $this->getResponse()->getBody());
    }

    public function testRequestUnknownDocId() {
        $this->dispatch('/oai/container/index/docId/123456789');
        $this->assertResponseCode(500);
        $this->assertContains('requested docId does not exist',
                $this->getResponse()->getBody());
    }

    public function testRequestUnpublishedDoc() {
        $r = Opus_UserRole::fetchByName('guest');

        $modules = $r->listAccessModules();
        $addOaiModuleAccess = !in_array('oai', $modules);
        if ($addOaiModuleAccess) {
            $r->appendAccessModule('oai');
            $r->store();
        }

        // enable security
        $config = Zend_Registry::get('Zend_Config');
        $security = $config->security;
        $config->security = '1';
        Zend_Registry::set('Zend_Config', $config);

        $doc = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $doc->store();
        $this->dispatch('/oai/container/index/docId/' . $doc->getId());

        if ($addOaiModuleAccess) {
            $r->removeAccessModule('oai');
            $r->store();
        }

        // restore security settings
        $config->security = $security;
        Zend_Registry::set('Zend_Config', $config);
        
        $this->assertResponseCode(500);
        $this->assertContains('access to requested document is forbidden', $this->getResponse()->getBody());        
    }

    public function testRequestPublishedDocWithoutAssociatedFiles() {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->store();
        $this->dispatch('/oai/container/index/docId/' . $doc->getId());
        
        $this->assertResponseCode(500);
        $this->assertContains('requested document does not have any associated readable files', $this->getResponse()->getBody());        
    }

    public function testRequestPublishedDocWithInaccessibleFile() {
        // create test file test.pdf in file system
        $config = Zend_Registry::get('Zend_Config');
        $path = $config->workspacePath . DIRECTORY_SEPARATOR . uniqid();
        mkdir($path, 0777, true);
        $filepath = $path . DIRECTORY_SEPARATOR . 'test.pdf';
        touch($filepath);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');        

        $file = new Opus_File();
        $file->setVisibleInOai(false);
        $file->setPathName('test.pdf');
        $file->setTempFile($filepath);
        $doc->addFile($file);
        $doc->store();

        $this->dispatch('/oai/container/index/docId/' . $doc->getId());

        // cleanup
        $file->delete();
        Opus_Util_File::deleteDirectory($path);
        
        $this->assertResponseCode(500);
        $this->assertContains(
            'access denied on all files that are associated to the requested document', $this->getResponse()->getBody()
        );
    }

    public function testRequestPublishedDocWithAccessibleFile() {
        $this->markTestIncomplete(
            'build breaks when running this test on ci system ' .
            '-- it seems that phpunit does not allow to test for file downloads'
        );

        // create test file test.pdf in file system
        $config = Zend_Registry::get('Zend_Config');
        $path = $config->workspacePath . DIRECTORY_SEPARATOR . uniqid();
        mkdir($path, 0777, true);
        $filepath = $path . DIRECTORY_SEPARATOR . 'test.pdf';
        touch($filepath);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');

        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('test.pdf');
        $file->setTempFile($filepath);
        $doc->addFile($file);
        $doc->store();

        $this->dispatch('/oai/container/index/docId/' . $doc->getId());        

        // cleanup
        $file->delete();
        Opus_Util_File::deleteDirectory($path);
        
        $this->assertResponseCode(200);
    }
}
