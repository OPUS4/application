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
 * @package     CitationExport
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class CitationExport_IndexControllerTest.
 *
 * @covers CitationExport_IndexController
 */
class CitationExport_IndexControllerTest extends ControllerTestCase
{

    private $documentId;

    public function setUp()
    {
        parent::setUp();

        $document = $this->createTestDocument();
        $document->setServerState('published');

        $this->documentId = $document->store();
        $this->assertNotNull($this->documentId);
    }

    /* Regression-Test OPUSVIER-2738 */
    public function testMultipleIdentifiersInRis()
    {
        $this->dispatch('/citationExport/index/index/output/ris/docId/153');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('SN  - 1-2345-678-9', $response->getBody());
        $this->assertContains('SN  - 1-5432-876-9', $response->getBody());
        $this->assertContains('SN  - 1234-5678', $response->getBody());
        $this->assertContains('SN  - 4321-8765', $response->getBody());
        $urnResolverUrl = Zend_Registry::get('Zend_Config')->urn->resolverUrl;
        $this->assertContains('UR  - ' . $urnResolverUrl . 'urn:nbn:de:foo:123-bar-456', $response->getBody());
        $this->assertContains('UR  - ' . $urnResolverUrl . 'urn:nbn:de:foo:123-bar-789', $response->getBody());
        $this->assertContains('UR  - http://www.myexampledomain.de/foo', $response->getBody());
        $this->assertContains('UR  - http://www.myexampledomain.de/bar', $response->getBody());
    }

    /* Regression-Test OPUSVIER-2328 */
    public function testTitleParentInRis()
    {
        $this->dispatch('/citationExport/index/index/output/ris/docId/146');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('T2  - Parent Title', $response->getBody());
    }

    /* Regression-Test OPUSVIER-2328 */
    public function testPersonEditorInRis()
    {
        $this->dispatch('/citationExport/index/index/output/ris/docId/146');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('A2  - Doe, Jane', $response->getBody());
    }

    /**
     * Regression-Test OPUSVIER-2716
     *
     * TODO use to be 'title   = {Dokumenttitel mit Sonderzeichen \%-\&quot;-\#-\&amp;, vgl. OPUSVIER-2716.},'
     * There used to be a '\' before '&quot;'. That was removed when fixing OPUSVIER-3950. However it is not clear what
     * is correct and why.
     */
    public function testSpecialCharactersInTtitle()
    {
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/152');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains(
             'title   = {Dokumenttitel mit Sonderzeichen \%-&quot;-\#-\&amp;, vgl. OPUSVIER-2716.},',
            $response->getBody()
        );
    }

    public function testIndexActionWithMissingDocIdParam()
    {
        $this->dispatch('/citationExport/index/index');
        $this->assertResponseCode(400);
    }

    public function testIndexActionWithInvalidDocIdParam()
    {
        $this->dispatch('/citationExport/index/index/docId/invalidid');
        $this->assertResponseCode(400);
    }

    public function testIndexActionWithMissingOutputParam()
    {
        $this->dispatch('/citationExport/index/index/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testIndexActionWithInvalidOutputParam()
    {
        $this->dispatch('/citationExport/index/index/output/foo/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testIndexActionWithUnpublishedDocument()
    {
        $doc = new Opus_Document($this->documentId);
        $doc->setServerState('unpublished');
        $doc->store();
        $this->dispatch('/citationExport/index/index/output/foo/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testIndexActionRis()
    {
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('UR  - ', $response->getBody());
        $this->assertContains('/frontdoor/index/index/docId/' . $this->documentId, $response->getBody());
        $this->assertContains(
            '/citationExport/index/download/output/ris/docId/' . $this->documentId,
            $response->getBody()
        );
    }


    /* RIS - TESTS  for Document-Types */

    public function testIndexActionRisDoctypeArticle()
    {
        $this->setDocumentType('article');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('JOUR');
    }

    public function testIndexActionRisDoctypeBook()
    {
        $this->setDocumentType('book');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('BOOK');
    }

    public function testIndexActionRisDoctypeBookpart()
    {
        $this->setDocumentType('bookpart');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('CHAP');
    }

    public function testIndexActionRisDoctypeConferenceobject()
    {
        $this->setDocumentType('conferenceobject');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('CONF');
    }

    public function testIndexActionRisDoctypeDoctoralthesis()
    {
        $this->setDocumentType('doctoralthesis');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('THES');;
    }

    public function testIndexActionRisDoctypeMasterthesis()
    {
        $this->setDocumentType('masterthesis');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('THES');
    }

    public function testIndexActionRisDoctypeBachelorthesis()
    {
        $this->setDocumentType('bachelorthesis');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('THES');
    }

    public function testIndexActionRisDoctypeHabilitation()
    {
        $this->setDocumentType('habilitation');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('THES');
    }

    public function testIndexActionRisDoctypeReport()
    {
        $this->setDocumentType('report');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('RPRT');
    }

    public function testIndexActionRisPreprint()
    {
        $this->setDocumentType('preprint');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('INPR');
    }

    public function testIndexActionRisPeriodical()
    {
        $this->setDocumentType('periodical');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('JFULL');
    }

    public function testIndexActionRisContributiontoperiodical()
    {
        $this->setDocumentType('contributiontoperiodical');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('NEWS');
    }

    public function testIndexActionRisReview()
    {
        $this->setDocumentType('review');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('JOUR');
    }

    public function testIndexActionRisWorkingpaper()
    {
        $this->setDocumentType('workingpaper');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('UNPD');
    }

    public function testIndexActionRisMovingimage()
    {
        $this->setDocumentType('movingimage');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('VIDEO');
    }

    public function testIndexActionRisCoursematerial()
    {
        $this->setDocumentType('coursematerial');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('GEN');
    }

    public function testIndexActionRisImage()
    {
        $this->setDocumentType('image');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('GEN');
    }

    public function testIndexActionRisLecture()
    {
        $this->setDocumentType('lecture');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('GEN');
    }

    public function testIndexActionRisSound()
    {
        $this->setDocumentType('sound');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('GEN');
    }

    public function testIndexActionRisStudythesis()
    {
        $this->setDocumentType('studythesis');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('GEN');
    }

    public function testIndexActionRisOther()
    {
        $this->setDocumentType('other');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('GEN');
    }

    public function testIndexActionRisMisc()
    {
        $this->setDocumentType('foo');
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->checkRisAssertions('GEN');
    }

    /* RIS - TESTS  for Content */

    public function testIndexActionRisSubjectUncontrolled()
    {
        $doc = new Opus_Document($this->documentId);
        $doc->addSubject()->setType('uncontrolled')->setValue('Freies Schlagwort');
        $doc->store();
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('KW  - Freies Schlagwort', $response->getBody());
    }

    public function testIndexActionRisSubjectSwd()
    {
        $doc = new Opus_Document($this->documentId);
        $doc->addSubject()->setType('swd')->setValue('SWD-Schlagwort');
        $doc->store();
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('KW  - SWD-Schlagwort', $response->getBody());
    }

    public function testIndexActionRisSeriesVisible()
    {
        $s = new Opus_Series(4);
        $doc = new Opus_Document($this->documentId);
        $doc->addSeries($s)->setNumber('SeriesNumber');
        $doc->store();
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('T3  - ' . $s->getTitle() . ' - SeriesNumber', $response->getBody());

    }

    public function testIndexActionRisSeriesInvisible()
    {
        $s = new Opus_Series(3);
        $doc = new Opus_Document($this->documentId);
        $doc->addSeries($s)->setNumber('SeriesNumber');
        $doc->store();
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $this->documentId);
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertNotContains('T3  - ' . $s->getTitle() . ' - SeriesNumber', $response->getBody());
    }

    public function testIndexActionRisPublicNote()
    {
        $doc = new Opus_Document(146);
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $doc->getId());
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('N1  - Für die Öffentlichkeit', $response->getBody());
    }

    public function testIndexActionRisPrivateNote()
    {
        $doc = new Opus_Document(146);
        $this->dispatch('/citationExport/index/index/output/ris/docId/' . $doc->getId());
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertNotContains('N1  - Für den Admin.', $response->getBody());
    }


    /* BIBTEX - TESTS for Documenttypes */

    public function testIndexActionBibtexDoctypeArticle()
    {
        $this->setDocumentType('article');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@article');
    }

    public function testIndexActionBibtexDoctypeBook()
    {
        $this->setDocumentType('book');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@book');
    }

    public function testIndexActionBibtexDoctypeBookpart()
    {
        $this->setDocumentType('bookpart');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@incollection');
    }

    public function testIndexActionBibtexDoctypeConferenceobject() {
        $this->setDocumentType('conferenceobject');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@inproceedings');
    }

    public function testIndexActionBibtexDoctypeDoctoralthesis()
    {
        $this->setDocumentType('doctoralthesis');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@phdthesis');
    }

    public function testIndexActionBibtexDoctypeMasterthesis()
    {
        $this->setDocumentType('masterthesis');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@mastersthesis');
    }

    public function testIndexActionBibtexDoctypePreprint()
    {
        $this->setDocumentType('preprint');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@unpublished');
    }

    public function testIndexActionBibtexDoctypeReport()
    {
        $this->setDocumentType('report');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@techreport');
    }

    public function testIndexActionBibtexMisc() {
        $this->setDocumentType('foo');
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@misc');
    }

    /* BIBTEX - TESTS  for Content */

    public function testIndexActionBibtexSeriesVisible()
    {
        $this->setDocumentType('preprint');
        $s = new Opus_Series(4);
        $doc = new Opus_Document($this->documentId);
        $doc->addSeries($s)->setNumber('SeriesNumber');
        $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('series    = {' . $s->getTitle() . '},', $response->getBody());
        $this->assertContains('number    = {SeriesNumber},', $response->getBody());
    }

    public function testIndexActionBibtexSeriesInvisible()
    {
        $this->setDocumentType('preprint');
        $s = new Opus_Series(3);
        $doc = new Opus_Document($this->documentId);
        $doc->addSeries($s)->setNumber('SeriesNumber');
        $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $this->documentId);
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertNotContains('series    = {' . $s->getTitle() . '},', $response->getBody());
        $this->assertNotContains('number    = {SeriesNumber},', $response->getBody());
    }

    /** Regression Test for OPUSVIER-3251 */
    public function testIndexActionBibtexEnrichmentVisibleAsNote()
    {
        $bibtexConfArray = array(
            'citationExport' => array('bibtex' => array('enrichment' => 'SourceTitle'))
        );
        $bibtexConf = new Zend_Config($bibtexConfArray);
        Zend_Registry::getInstance()->get('Zend_Config')->merge($bibtexConf);
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/146');
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains(
            'note        = {Dieses Dokument ist auch erschienen als ...}',
            $response->getBody()
        );
    }

    /* DOWNLOAD - TESTS */

    public function testDownloadActionWithMissingDocIdParam()
    {
        $this->dispatch('/citationExport/index/download');
        $this->assertResponseCode(400);
    }

    public function testDownloadActionWithInvalidDocIdParam()
    {
        $this->dispatch('/citationExport/index/download/docId/invalidid');
        $this->assertResponseCode(400);
    }

    public function testDownloadActionWithMissingOutputParam()
    {
        $this->dispatch('/citationExport/index/download/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testDownloadActionWithInvalidOutputParam()
    {
        $this->dispatch('/citationExport/index/download/output/foo/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testDownloadActionWithUnpublishedDocument()
    {
        $doc = new Opus_Document($this->documentId);
        $doc->setServerState('unpublished');
        $doc->store();
        $this->dispatch('/citationExport/index/download/output/foo/docId/' . $this->documentId);
        $this->assertResponseCode(400);
    }

    public function testDownloadActionRis()
    {
        $this->dispatch('/citationExport/index/download/output/ris/docId/' . $this->documentId);
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('UR  - ', $response->getBody());
        $this->assertContains('/frontdoor/index/index/docId/' . $this->documentId, $response->getBody());
    }

    public function testDownloadActionBibtexDoctypeArticle()
    {
        $this->setDocumentType('article');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@article', false);
    }

    public function testDownloadActionBibtexDoctypeBook()
    {
        $this->setDocumentType('book');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@book', false);
    }

    public function testDownloadActionBibtexDoctypeBookpart()
    {
        $this->setDocumentType('bookpart');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@incollection', false);
    }

    public function testDownloadActionBibtexDoctypeConferenceobject()
    {
        $this->setDocumentType('conferenceobject');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@inproceedings', false);
    }

    public function testDownloadActionBibtexDoctypeDoctoralthesis()
    {
        $this->setDocumentType('doctoralthesis');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@phdthesis', false);
    }

    public function testDownloadActionBibtexDoctypeMasterthesis()
    {
        $this->setDocumentType('masterthesis');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@mastersthesis', false);
    }

    public function testDownloadActionBibtexDoctypePreprint()
    {
        $this->setDocumentType('preprint');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@unpublished', false);
    }

    public function testDownloadActionBibtexDoctypeReport()
    {
        $this->setDocumentType('report');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@techreport', false);
    }

    public function testDownloadActionBibtexMisc()
    {
        $this->setDocumentType('foo');
        $this->dispatch('/citationExport/index/download/output/bibtex/docId/' . $this->documentId);
        $this->checkBibtexAssertions('@misc', false);
    }

    /**
     * Regression-Tests für OPUSVIER-3289.
     * Der Test prüft, ob das Jahr mit ausgegeben wird, wenn NUR das Feld 'publishedYear' gesetzt ist.
     */
    public function testYearIsNotExportedWhenOnlyPublishedYearIsSet()
    {
        $doc = $this->createTestDocument();
        $doc->setPublishedYear(2013);
        $docId = $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex_conferenceobject/docId/' . $docId);
        $this->assertQueryContentContains('//pre', "@inproceedings{OPUS4-$docId,");
        $this->assertQueryContentContains('//pre', "{2013}");
    }

    /**
     * Regression-Tests für OPUSVIER-3289.
     * Der Test prüft, ob das Jahr mit ausgegeben wird, wenn NUR das Feld 'publishedDate' gesetzt ist.
     */
    public function testBibtexYearExportWithOnlyPublishedDateSet()
    {
        $doc = $this->createTestDocument();
        $doc->setPublishedDate('2012-02-01');
        $docId = $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex_conferenceobject/docId/' . $docId);
        $this->assertQueryContentContains('//pre', "@inproceedings{OPUS4-$docId,");
        $this->assertQueryContentContains('//pre', "{2012}");
    }

    /**
     * Regression-Tests für OPUSVIER-3289.
     * Der Test prüft, ob das Jahr mit ausgegeben wird, wenn NUR das Feld 'completedDate' gesetzt ist.
     */
    public function testBibtexYearExportWithOnlyCompletedDateSet()
    {
        $doc = $this->createTestDocument();
        $doc->setCompletedDate('2012-02-01');
        $docId = $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex_conferenceobject/docId/' . $docId);
        $this->assertQueryContentContains('//pre', "@inproceedings{OPUS4-$docId,");
        $this->assertQueryContentContains('//pre', "{2012}");
    }

    /**
     * Regression-Tests für OPUSVIER-3289.
     * Der Test prüft, ob das Jahr mit ausgegeben wird, wenn NUR das Feld 'completedYear' gesetzt ist.
     */
    public function testBibtexYearExportWithOnlyCompletedYearSet()
    {
        $doc = $this->createTestDocument();
        $doc->setCompletedYear(2012);
        $docId = $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex_conferenceobject/docId/' . $docId);
        $this->assertQueryContentContains('//pre', "@inproceedings{OPUS4-$docId,");
        $this->assertQueryContentContains('//pre', "{2012}");
    }

    /**
     * Der Test prüft, ob das richtige Jahr ausgegeben wird, wenn alle Felder gesetzt sind.
     */
    public function testBibtexYearExportWithEveryDate()
    {
        $doc = $this->createTestDocument();
        $doc->setCompletedDate('2015-01-01');
        $doc->setPublishedYear(2012);
        $doc->setPublishedDate('2013-01-01');
        $doc->setCompletedYear(2014);
        $docId = $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex_conferenceobject/docId/' . $docId);
        $this->assertQueryContentContains('//pre', "@inproceedings{OPUS4-$docId,");
        $this->assertQueryContentContains('//pre', "{2015}");
    }

    /**
     * Der Test prüft, ob das richtige Jahr ausgegeben wird, wenn alle Felder auf leere Strings gesetzt sind.
     */
    public function testBibtexYearExportWithEmptyStrings()
    {
        $doc = $this->createTestDocument();
        $doc->setCompletedDate('');
        $doc->setCompletedYear(null);
        $doc->setPublishedDate('');
        $doc->setPublishedYear('2015');
        $docId = $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex_conferenceobject/docId/' . $docId);
        $this->assertQueryContentContains('//pre', "@inproceedings{OPUS4-$docId,");
        $this->assertQueryContentContains('//pre', "{2015}");
    }

    public function testBibtexTypeMasterthesis()
    {
        $doc = $this->createTestDocument();
        $doc->setType('masterthesis');
        $docId = $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $docId);
        $this->assertQueryContentContains('//pre', "{masterthesis}");
    }

    public function testBibtexTypeDoctoralthesis()
    {
        $doc = $this->createTestDocument();
        $doc->setType('doctoralthesis');
        $docId = $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $docId);
        $this->assertQueryContentContains('//pre', "{doctoralthesis}");
    }

    public function testBibtexNoType()
    {
        $doc = $this->createTestDocument();
        $docId = $doc->store();
        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $docId);
        $this->assertNotQueryContentContains('//pre', "type        =");
    }

    private function setDocumentType($documenttype)
    {
        $doc = new Opus_Document($this->documentId);
        $doc->setType($documenttype);
        $doc->store();
    }

    private function checkBibtexAssertions($bibtexType, $downloadLinkExists = true)
    {
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains($bibtexType, $response->getBody());
        if ($downloadLinkExists) {
            $this->assertContains(
                '/citationExport/index/download/output/bibtex/docId/' . $this->documentId,
                $response->getBody()
            );
        }
    }

    private function checkRisAssertions($risType, $downloadLinkExists = true)
    {
        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('TY  - ' . $risType, $response->getBody());
        if ($downloadLinkExists) {
            $this->assertContains(
                '/citationExport/index/download/output/ris/docId/' . $this->documentId,
                $response->getBody()
            );
        }
    }

    public function testDoiRendering()
    {
        $doc = $this->createTestDocument();
        $doc->setType('article');

        $doi = new Opus_Identifier();
        $doi->setValue('123_345_678');
        $doc->addIdentifierDoi($doi);

        $docId = $doc->store();

        $this->dispatch('/citationExport/index/index/output/bibtex/docId/' . $docId);

        $this->assertQueryContentContains('//pre', "@article{");
        $this->assertQueryContentContains('//pre', '123_345_678');
        $this->assertNotQueryContentContains('//pre', '123\_345\_678');
    }
}
