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

class Review_Model_ClearDocumentsHelperTest extends ControllerTestCase {

    private $documentId = null;
    private $person = null;

    public function setUp() {
        parent::setUp();

        $document = new Opus_Document();
        $document->setServerState('unpublished');
        $document->setPersonReferee(array());
        $document->setEnrichment(array());
        $this->documentId = $document->store();

        $document = new Opus_Document($this->documentId);
        $this->assertEquals(0, count($document->getPersonReferee()));
        $this->assertEquals(0, count($document->getEnrichment()));

        $person = new Opus_Person();
        $person->setFirstName('John');
        $person->setLastName('Doe');
        $this->person = $person;
    }

    protected function tearDown() {
        parent::tearDown();

        $document = new Opus_Document($this->documentId);
        $document->deletePermanent();
    }

    public function testClearDocument() {
        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->clear(array($this->documentId), 23, $this->person);

        $document = new Opus_Document($this->documentId);
        $this->assertEquals('published', $document->getServerState());
        $this->assertEquals(1, count($document->getPersonReferee()));

        $enrichments = $document->getEnrichment();
        $this->assertEquals(1, count($enrichments));
        $this->assertEquals(23, $enrichments[0]->getValue());
    }

    public function testRejectDocument() {
        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->reject(array($this->documentId), 23, $this->person);

        $document = new Opus_Document($this->documentId);
        $this->assertNotEquals('published', $document->getServerState());
        $this->assertEquals(1, count($document->getPersonReferee()));

        $enrichments = $document->getEnrichment();
        $this->assertEquals(1, count($enrichments));
        $this->assertEquals(23, $enrichments[0]->getValue());
    }

    public function testClearInvalidDocument() {
        $helper = new Review_Model_ClearDocumentsHelper();

        $this->setExpectedException('Opus_Model_NotFoundException');
        $helper->clear(array($this->documentId + 100000), 23);
    }

    public function testRejectInvalidDocument() {
        $helper = new Review_Model_ClearDocumentsHelper();

        $this->setExpectedException('Opus_Model_NotFoundException');
        $helper->reject(array($this->documentId + 100000), 23);
    }

    public function testClearDocumentWoPerson() {
        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->clear(array($this->documentId), 23);

        $document = new Opus_Document($this->documentId);
        $this->assertEquals('published', $document->getServerState());
        $this->assertEquals(0, count($document->getPersonReferee()));

        $enrichments = $document->getEnrichment();
        $this->assertEquals(1, count($enrichments));
        $this->assertEquals(23, $enrichments[0]->getValue());
    }

    public function testRejectDocumentWoPerson() {
        $helper = new Review_Model_ClearDocumentsHelper();
        $helper->reject(array($this->documentId), 23);

        $document = new Opus_Document($this->documentId);
        $this->assertNotEquals('published', $document->getServerState());
        $this->assertEquals(0, count($document->getPersonReferee()));

        $enrichments = $document->getEnrichment();
        $this->assertEquals(1, count($enrichments));
        $this->assertEquals(23, $enrichments[0]->getValue());
    }
}
