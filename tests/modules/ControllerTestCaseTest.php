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
 * @category    Application Unit Test
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class ControllerTestCaseTest extends ControllerTestCase {

    public function tearDown() {
        $this->restoreSecuritySetting();
        parent::tearDown();
    }

    /**
     * Prüft, ob der User eingeloggt wurde.
     *
     * Dient der Vorbereitung von Test "testTearDownDidLogout".
     */
    public function testLoginAdmin() {
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');
        $realm = Opus_Security_Realm::getInstance();

        $this->assertContains('administrator', $realm->getRoles(), Zend_Debug::dump($realm->getRoles(), null, false));
    }

    /**
     * Prüft, ob der User vom Test "testLoginAdmin" nicht mehr eingeloggt ist.
     *
     * Regression Test für OPUSVIER-3283
     */
    public function testTearDownDidLogout() {
        $this->enableSecurity();
        $realm = Opus_Security_Realm::getInstance();
        $this->assertNotContains('administrator', $realm->getRoles());
    }

    public function testSetHostname() {
        $view = Zend_Registry::get('Opus_View');

        $this->assertEquals('http://', $view->serverUrl());

        $this->setHostname('localhost');

        $this->assertEquals('http://localhost', $view->serverUrl());
    }

    public function testSetBaseUrlNotSet() {
        $view = Zend_Registry::get('Opus_View');

        $this->assertEquals('', $view->baseUrl());

        // base Url must be set before first baseUrl() call, won't be changed afterwards
        $this->setBaseUrl('opus4');
        $this->assertEquals('', $view->baseUrl());
    }

    public function testSetBaseUrlSet() {
        $view = Zend_Registry::get('Opus_View');

        $this->setBaseUrl('opus4');

        $this->assertEquals('opus4', $view->baseUrl());
    }

    /**
     * Test removing document using identifier.
     *
     * @expectedException Opus_Model_NotFoundException
     */
    public function testRemoveDocumentById() {
        $doc = new Opus_Document();
        $docId = $doc->store();

        $this->removeDocument($docId);

        $doc = new Opus_Document($docId);
    }

    /**
     * Test removing document using object.
     *
     * @expectedException Opus_Model_NotFoundException
     */
    public function testRemoveDocument() {
        $doc = new Opus_Document();
        $docId = $doc->store();

        $this->removeDocument($doc);

        $doc = new Opus_Document($docId);
    }

    /**
     * Test removing document that has not been stored.
     */
    public function testRemoveDocumentNotStored() {
        $doc = new Opus_Document();

        $this->removeDocument($doc);
    }
}
