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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\Model\NotFoundException;
use Symfony\Component\Console\Output\NullOutput;

/**
 * TODO create fixture helper class to create N test documents and get their IDs
 *      additionally an array with data could be provided to be used for creating
 *      the test documents
 */
class Application_Document_DuplicateFinderTest extends ControllerTestCase
{
    /** @var int */
    private $docId1;

    /** @var int */
    private $docId2;

    /** @var int */
    private $docId3;

    /** @var Application_Document_DuplicateFinder */
    private $helper;

    /** @var string[] */
    protected $additionalResources = ['database'];

    public function setUp(): void
    {
        parent::setUp();

        $this->helper = new Application_Document_DuplicateFinder();
        $this->helper->setOutput(new NullOutput());
        $this->helper->setRemoveEnabled(true);

        $this->setupTestDocuments();
    }

    public function testFindDocuments()
    {
        $helper = $this->helper;

        $doi = '10.1000/182';

        $docIds = $helper->findDocuments($doi);

        $this->assertCount(2, $docIds);
    }

    public function testFindDocumentsUnknownDoi()
    {
        $helper = $this->helper;

        $doi = '10.1000/282';

        $docIds = $helper->findDocuments($doi);

        $this->assertCount(0, $docIds);
    }

    public function testRemoveNewerDuplicateDocument()
    {
        $this->helper->removeDuplicateDocument('10.1000/182');

        Document::get($this->docId3);

        $this->expectException(NotFoundException::class);

        Document::get($this->docId2);
    }

    public function testDoNotRemoveNewerDuplicateDocumentIfDryRunEnabled()
    {
        $helper = $this->helper;

        $helper->setDryRunEnabled(true);
        $helper->removeDuplicateDocument('10.1000/182');

        Document::get($this->docId2);
        Document::get($this->docId1);
    }

    public function testRemoveOnlyUnpublishedDocuments()
    {
        $doc = Document::get($this->docId2);
        $doc->setServerState(Document::STATE_INPROGRESS);
        $doc->store();

        $this->helper->removeDuplicateDocument('10.1000/182');

        Document::get($this->docId2);
        Document::get($this->docId1);
        Document::get($this->docId3);
    }

    public function testGetNewestDocument()
    {
        $docIds = [$this->docId1, $this->docId2];

        $doc = $this->helper->getNewestDocument($docIds);

        $this->assertNotNull($doc);
        $this->assertEquals($this->docId2, $doc->getId());

        $docIds = [$this->docId2, $this->docId1];

        $doc = $this->helper->getNewestDocument($docIds);

        $this->assertNotNull($doc);
        $this->assertEquals($this->docId2, $doc->getId());
    }

    protected function setupTestDocuments()
    {
        $doc = $this->createTestDocument();
        $doi = $doc->addIdentifierDoi();
        $doi->setValue('10.1000/182');
        $this->docId1 = $doc->store();

        sleep(1);

        $doc = $this->createTestDocument();
        $doi = $doc->addIdentifierDoi();
        $doi->setValue('10.1000/182');
        $this->docId2 = $doc->store();

        $doc = $this->createTestDocument();
        $doi = $doc->addIdentifierDoi();
        $doi->setValue('10.1000/183');
        $this->docId3 = $doc->store();
    }
}
