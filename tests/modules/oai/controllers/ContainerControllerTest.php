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
        $doc = new Opus_Document();
        $doc->setServerState('unpublished');
        $doc->store();
        $this->dispatch('/oai/container/index/docId/' . $doc->getId());
        $this->assertResponseCode(500);
        $this->assertContains('access to requested document is forbidden',
                $this->getResponse()->getBody());
        $doc->deletePermanent();
    }

    public function testRequestPublishedDocWithoutAssociatedFiles() {
        $doc = new Opus_Document();
        $doc->setServerState('published');
        $doc->store();
        $this->dispatch('/oai/container/index/docId/' . $doc->getId());
        $this->assertResponseCode(500);
        $this->assertContains('requested document does not have any associated files',
                $this->getResponse()->getBody());
        $doc->deletePermanent();
    }

    public function testRequestPublishedDocWithInaccessibleFile() {
        $doc = new Opus_Document();
        $doc->setServerState('published');        

        $file = new Opus_File();
        $file->setVisibleInOai(false);
        $file->setPathName('foo');
        $doc->addFile($file);
        $doc->store();

        $this->dispatch('/oai/container/index/docId/' . $doc->getId());
        $this->assertResponseCode(500);
        $this->assertContains('access denied on all files that are associated to the requested document',
                $this->getResponse()->getBody());

        // cleanup
        $file->delete();
        $doc->deletePermanent();
    }

    public function testRequestPublishedDocWithAccessibleFile() {
        // create test file test.pdf in file system
        $config = Zend_Registry::get('Zend_Config');
        $path = $config->workspacePath . DIRECTORY_SEPARATOR . uniqid();
        mkdir($path, 0777, true);
        $filepath = $path . DIRECTORY_SEPARATOR . 'test.pdf';
        touch($filepath);

        $doc = new Opus_Document();
        $doc->setServerState('published');

        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('test.pdf');
        $file->setTempFile($filepath);
        $doc->addFile($file);
        $doc->store();

        $this->dispatch('/oai/container/index/docId/' . $doc->getId());
        $this->assertResponseCode(200);

        // cleanup
        $file->delete();
        $doc->deletePermanent();
        Opus_Util_File::deleteDirectory($path);
    }
}