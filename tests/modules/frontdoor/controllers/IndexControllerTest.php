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
 * @author      Julian Heise <heise@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Frontdoor_IndexControllerTest extends ControllerTestCase {

    /**
     * Document to count on :)
     *
     * @var Opus_Document
     */
    protected $_document = null;

    protected $_security_backup = null;


    /**
     * Provide clean documents and statistics table and remove temporary files.
     * Create document for counting.
     *
     * @return void
     */

    public function setUp() {
        parent::setUp();

        $path = Zend_Registry::get('temp_dir') . '~localstat.xml';
        @unlink($path);

        $this->_document = new Opus_Document();
        $this->_document->setType("doctoral_thesis");
        $this->_document->store();

        //setting server globals
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'bla';
        $_SERVER['REDIRECT_STATUS'] = 200;

        // enable security
        $config = Zend_Registry::get('Zend_Config');
        $this->_security_backup = $config->security;
        $config->security = '1';
    }

    protected function tearDown() {
        // restore old security config
        $config = Zend_Registry::get('Zend_Config');
        $config->security = $this->_security_backup;
        Zend_Registry::set('Zend_Config', $config);

        if ($this->_document instanceof Opus_Document) {
            $this->_document->deletePermanent();
        }
        parent::tearDown();
    }

    public function testIndexActionOnPublished() {
        $this->_document->setServerState('published')->store();
        $doc_id = $this->_document->getId();
        $this->dispatch('/frontdoor/index/index/docId/'.$doc_id);

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
        $this->assertContains('<div class="frontdoor">', $response->getBody());
    }

    public function testIndexActionOnDeleted() {
        $this->_document->setServerState('deleted')->store();
        $doc_id = $this->_document->getId();
        $this->dispatch('/frontdoor/index/index/docId/'.$doc_id);

        $this->assertResponseCode(410);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    public function testIndexActionOnUnpublished() {
        $this->_document->setServerState('unpublished')->store();
        $doc_id = $this->_document->getId();
        $this->dispatch('/frontdoor/index/index/docId/'.$doc_id);

        $this->assertResponseCode(403);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    public function testIndexActionOnTemporary() {
        $this->_document->setServerState('temporary')->store();
        $doc_id = $this->_document->getId();
        $this->dispatch('/frontdoor/index/index/docId/'.$doc_id);

        $this->assertResponseCode(403);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    public function testIndexActionOnNonExistent() {
        $doc_id = $this->_document->getId();
        $this->dispatch('/frontdoor/index/index/docId/'.$doc_id.$doc_id.'100');

        $this->assertResponseCode(404);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    /**
     * @deprecated since OPUS 3.0.2, the function under test is marked as deprecated
     * and will be removed in future releases
     */
    public function testMapopus3Action() {        
        $opus3_id = 'foobar-'.rand();
        $this->_document->addIdentifierOpus3()->setValue($opus3_id);
        $doc_id = $this->_document->store();

        $this->dispatch('/frontdoor/index/mapopus3/oldId/'.$opus3_id);

        $this->assertResponseCode(302);
        $this->assertModule('frontdoor');
        $this->assertController('index');
        $this->assertAction('mapopus3');

        $response = $this->getResponse();
        $headers = $response->getHeaders();

        $this->assertEquals('Location', $headers[0]['name']);
        $this->assertStringEndsWith('/rewrite/index/id/type/opus3-id/value/' . $opus3_id, $headers[0]['value']);

        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * test to document bug OPUSVIER-1695
     */
    public function testUncontrolledKeywordHeaderIsNotDisplayedIfUncontrolledKeywordsDoNotExist() {
	$this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertResponseCode(200);
        $this->assertModule('frontdoor');
        $this->assertController('index');
        $this->assertAction('index');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<table class="result-data frontdoordata">', $body);
        $this->assertNotContains('<td><em class="data-marker"/></td>', $body);
    }

    /*
     * Regression test for OPUSVIER-2165
     */
    public function testFrontdoorTitleRespectsDocumentLanguageDeu() {
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertNotContains('<title>OPUS 4 | COLN</title>', $this->getResponse()->getBody());
        $this->assertContains('<title>OPUS 4 | KOBV</title>', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2165
     */
    public function testFrontdoorTitleRespectsDocumentLanguageEng() {
        $d = new Opus_Document(146);
        $lang = $d->getLanguage();
        $d->setLanguage('eng');        
        $d->store();

        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertContains('<title>OPUS 4 | COLN</title>', $this->getResponse()->getBody());        
        $this->assertNotContains('<title>OPUS 4 | KOBV</title>', $this->getResponse()->getBody());

        // restore language
        $d = new Opus_Document(146);
        $d->setLanguage($lang);
        $d->store();
    }

    /**
     * Regression test for OPUSVIER-2165
     *
     * if database does not contain a title in the document's language,
     * the first title is used as page title
     * 
     */
    public function testFrontdoorTitleRespectsDocumentLanguageWithoutCorrespondingTitle() {
        $d = new Opus_Document(146);
        $lang = $d->getLanguage();
        $d->setLanguage('fra');
        $d->store();

        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertNotContains('<title>OPUS 4 | COLN</title>', $this->getResponse()->getBody());
        $this->assertContains('<title>OPUS 4 | KOBV</title>', $this->getResponse()->getBody());

        // restore language
        $d = new Opus_Document(146);
        $d->setLanguage($lang);
        $d->store();
    }

    /**
     * Regression test for OPUSVIER-2165
     *
     * if database contains more than one title in the document's language,
     * the first title is used as page title
     */
    public function testFrontdoorTitleRespectsDocumentLanguageMultipleCandidates() {
        $d = new Opus_Document(146);
        $titles = $d->getTitleMain();
        $d->addTitleMain()->setValue('VBOK')->setLanguage('deu');
        $d->store();

        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertNotContains('<title>OPUS 4 | COLN</title>', $this->getResponse()->getBody());
        $this->assertNotContains('<title>OPUS 4 | VBKO</title>', $this->getResponse()->getBody());
        $this->assertContains('<title>OPUS 4 | KOBV</title>', $this->getResponse()->getBody());

        // restore titles
        $d = new Opus_Document(146);
        $d->setTitleMain($titles);
        $d->store();
    }

    /**
     * Regression test for OPUSVIER-1924
     */
    public function testIdentifierUrlIsHandledProperlyInFrontdoorForNonProtocolURL() {
        $d = new Opus_Document('91');
        $identifiers = $d->getIdentifierUrl();
        $identifier = $identifiers[0];
        $this->assertEquals('www.myexampledomain.de/myexamplepath', $identifier->getValue());
        $this->dispatch('/frontdoor/index/index/docId/91');
        $this->assertTrue(2 == substr_count($this->getResponse()->getBody(), 'http://www.myexampledomain.de/myexamplepath'));
    }

    /**
     * Regression test for OPUSVIER-1924
     */
    public function testIdentifierUrlIsHandledProperlyInFrontdoorForProtocolURL() {
        $d = new Opus_Document('92');
        $identifiers = $d->getIdentifierUrl();
        $identifier = $identifiers[0];
        $this->assertEquals('http://www.myexampledomain.de/myexamplepath', $identifier->getValue());
        $this->dispatch('/frontdoor/index/index/docId/92');
        $this->assertTrue(2 == substr_count($this->getResponse()->getBody(), 'http://www.myexampledomain.de/myexamplepath'));
    }

    /**
     * Regression test for OPUSVIER-1647
     */
    public function testUrlEscapedFileNameDoc1() {
        $d = new Opus_Document(1);
        $filePathnames = array();
        foreach ($d->getFile() AS $file) {
            $filePathnames[] = $file->getPathName();
        }

        $filenameNormal = 'asis-hap.pdf';
        $filenameWeird = 'asis-hap_\'.pdf';
        $this->assertContains($filenameNormal, $filePathnames, "testdata changed!");
        $this->assertContains($filenameWeird, $filePathnames, "testdata changed!");

        $this->dispatch('/frontdoor/index/index/docId/1');

        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<a href="[^"]+\/1\/asis-hap.pdf"/', $responseBody);
        $this->assertRegExp('/<a href="[^"]+\/1\/asis-hap_%27.pdf"/', $responseBody);
        $this->assertNotRegExp('/<a href="[^"]+\/1\/asis-hap_\'.pdf"/', $responseBody);
    }

    /**
     * Regression test for OPUSVIER-1647
     */
    public function testUrlEscapedFileNameDoc147() {
        $d = new Opus_Document(147);
        $filePathnames = array();
        foreach ($d->getFile() AS $file) {
            $filePathnames[] = $file->getPathName();
        }

        $filenameNormal = 'special-chars-%-"-#-&.pdf';
        $filenameWeird = "'many'  -  spaces  and  quotes.pdf";
        $this->assertContains($filenameNormal, $filePathnames, "testdata changed!");
        $this->assertContains($filenameWeird, $filePathnames, "testdata changed!");

        $this->dispatch('/frontdoor/index/index/docId/147');

        $responseBody = $this->getResponse()->getBody();
        $this->assertRegExp('/<a href="[^"]+\/\d+\/special-chars-%25-%22-%23-%26.pdf">/', $responseBody);
        $this->assertRegExp('/<a href="[^"]+\/\d+\/%27many%27\+\+-\+\+spaces\+\+and\+\+quotes.pdf">/', $responseBody);
    }

    /**
     * Regression test for OPUSVIER-2129
     */
    public function testSeries146() {
        $this->dispatch('/frontdoor/index/index/docId/146');
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/1" ', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2232
     */
    public function testSeries149InVisible() {
        $d = new Opus_Document(149);
        $seriesIds     = array();
        $seriesNumbers = array();
        foreach ($d->getSeries() AS $series) {
            $seriesIds[] = $series->getModel()->getId();
            $seriesNumbers[] = $series->getNumber();
        }

        $this->assertContains(3, $seriesIds);
        $this->assertContains(4, $seriesIds);
        $this->assertContains('id-3-is-invisible', $seriesNumbers);
        $this->assertContains('id-4-is-visible', $seriesNumbers);

        $this->dispatch('/frontdoor/index/index/docId/149');
        $this->assertResponseCode(200);
        $responseBody = $this->getResponse()->getBody();

        // series 3 is NOT visible
        $this->assertNotContains('id-3-is-invisible',
                $responseBody);
        $this->assertNotRegExp('/href="\/solrsearch\/index\/search\/searchtype\/series\/id\/3"/',
                $responseBody);

        // series 4 is visible
        $this->assertContains('id-4-is-visible',
                $responseBody);
        $this->assertRegExp('/href="\/solrsearch\/index\/search\/searchtype\/series\/id\/4"/',
                $responseBody);
    }

}
