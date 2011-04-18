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

class CitationExport_IndexControllerTest extends ControllerTestCase {

    private $documentId;

    public function setUp() {
        parent::setUp();

        $document = new Opus_Document();
        $document->setServerState('published');

        $this->documentId = $document->store();
        $this->assertNotNull($this->documentId);
    }

    protected function tearDown() {
        parent::tearDown();

        $document = new Opus_Document($this->documentId);
        $document->deletePermanent();
    }

    public function testIndexActionWithMissingDocIdParam() {
        $this->dispatch('/citationExport/index/index');
        $this->assertResponseCode(400);
    }

    public function testIndexActionWithInvalidDocIdParam() {
        $this->dispatch('/citationExport/index/index/docId/invalidid');
        $this->assertResponseCode(400);
    }

    public function testIndexActionWithMissingOutputParam() {
        $this->dispatch('/citationExport/index/index/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testIndexActionWithInvalidOutputParam() {
        $this->dispatch('/citationExport/index/index/output/foo/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testIndexActionWithUnpublishedDocument() {
        $doc = new Opus_Document($this->documentId);
        $doc->setServerState('unpublished');
        $doc->store();
        $this->dispatch('/citationExport/index/index/output/foo/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testIndexActionRis() {
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('UR  - ', $response->getBody());
        $this->assertContains('/frontdoor/index/index/docId/' . $this->documentId, $response->getBody());
        $this->assertContains('/citationExport/index/download/output/ris/docId/' . $this->documentId, $response->getBody());
    }

    public function testIndexActionBibtexDoctypeArticle() {
        $this->setDocumentType('article');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@article');
    }

    public function testIndexActionBibtexDoctypeBook() {
        $this->setDocumentType('book');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@book');
    }

    public function testIndexActionBibtexDoctypeBookpart() {
        $this->setDocumentType('bookpart');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@incollection');
    }

    public function testIndexActionBibtexDoctypeConferenceobject() {
        $this->setDocumentType('conferenceobject');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@inproceedings');
    }

    public function testIndexActionBibtexDoctypeDoctoralthesis() {
        $this->setDocumentType('doctoralthesis');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@phdthesis');
    }

    public function testIndexActionBibtexDoctypeMasterthesis() {
        $this->setDocumentType('masterthesis');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@mastersthesis');
    }

    public function testIndexActionBibtexDoctypePreprint() {
        $this->setDocumentType('preprint');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@unpublished');
    }

    public function testIndexActionBibtexDoctypeReport() {
        $this->setDocumentType('report');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@techreport');
    }

    public function testIndexActionBibtexMisc() {
        $this->setDocumentType('foo');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@misc');
    }

    public function testDownloadActionWithMissingDocIdParam() {
        $this->dispatch('/citationExport/index/download');
        $this->assertResponseCode(400);
    }

    public function testDownloadActionWithInvalidDocIdParam() {
        $this->dispatch('/citationExport/index/download/docId/invalidid');
        $this->assertResponseCode(400);
    }

    public function testDownloadActionWithMissingOutputParam() {
        $this->dispatch('/citationExport/index/download/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testDownloadActionWithInvalidOutputParam() {
        $this->dispatch('/citationExport/index/download/output/foo/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testDownloadActionWithUnpublishedDocument() {
        $doc = new Opus_Document($this->documentId);
        $doc->setServerState('unpublished');
        $doc->store();
        $this->dispatch('/citationExport/index/download/output/foo/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testDownloadActionRis() {
        $this->dispatch('/citationExport/index/download/output/ris/docId/' . $this->documentId);
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('UR  - ', $response->getBody());
        $this->assertContains('/frontdoor/index/index/docId/' . $this->documentId, $response->getBody());
    }

    public function testDownloadActionBibtexDoctypeArticle() {
        $this->setDocumentType('article');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@article', false);
    }

    public function testDownloadActionBibtexDoctypeBook() {
        $this->setDocumentType('book');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@book', false);
    }

    public function testDownloadActionBibtexDoctypeBookpart() {
        $this->setDocumentType('bookpart');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@incollection', false);
    }

    public function testDownloadActionBibtexDoctypeConferenceobject() {
        $this->setDocumentType('conferenceobject');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@inproceedings', false);
    }

    public function testDownloadActionBibtexDoctypeDoctoralthesis() {
        $this->setDocumentType('doctoralthesis');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@phdthesis', false);
    }

    public function testDownloadActionBibtexDoctypeMasterthesis() {
        $this->setDocumentType('masterthesis');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@mastersthesis', false);
    }

    public function testDownloadActionBibtexDoctypePreprint() {
        $this->setDocumentType('preprint');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@unpublished', false);
    }

    public function testDownloadActionBibtexDoctypeReport() {
        $this->setDocumentType('report');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@techreport', false);
    }

    public function testDownloadActionBibtexMisc() {
        $this->setDocumentType('foo');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@misc', false);
        
    }

    private function setDocumentType($documenttype) {
        $doc = new Opus_Document($this->documentId);
        $doc->setType($documenttype);
        $doc->store();
    }

    private function checkBibtexAssertions($bibtexType, $downloadLinkExists = true) {
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains($bibtexType, $response->getBody());
        if ($downloadLinkExists) {
            $this->assertContains('/citationExport/index/download/output/bibtex/docId/' . $this->documentId, $response->getBody());
        }
    }
}
?>