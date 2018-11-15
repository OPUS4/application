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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO split specific protocol tests into separate classes
 * TODO unit tests transformations directly without "dispatch"
 * TODO create plugins for formats/protocols/standards
 *
 * @covers Oai_IndexController
 */
class Oai_IndexControllerTest extends ControllerTestCase {

    private $_security;
    private $_addOaiModuleAccess;
    private $docIds = array();

    private $xpathNamespaces = [
        'oai' => "http://www.openarchives.org/OAI/2.0/",
        'oai_dc' => "http://www.openarchives.org/OAI/2.0/oai_dc/",
        'cc' => "http://www.d-nb.de/standards/cc/",
        'dc' => "http://purl.org/dc/elements/1.1/",
        'ddb' => "http://www.d-nb.de/standards/ddb/",
        'pc' => "http://www.d-nb.de/standards/pc/",
        'xMetaDiss' => "http://www.d-nb.de/standards/xmetadissplus/",
        'epicur' => "urn:nbn:de:1111-2004033116",
        'dcterms' => "http://purl.org/dc/terms/",
        'thesis' => "http://www.ndltd.org/standards/metadata/etdms/1.0/",
        'eprints' => 'http://www.openarchives.org/OAI/1.1/eprints',
        'oaiid' => 'http://www.openarchives.org/OAI/2.0/oai-identifier'
        ];


    /**
     * Method to check response for "bad" strings.
     */
    protected function checkForBadStringsInHtml($body) {
        $badStrings = array("Exception", "Fehler", "Stacktrace", "badVerb",
            "unauthorized", "internal error", "<error", "</error>");
        $this->checkForCustomBadStringsInHtml($body, $badStrings);
    }

    /**
     * Create DOMXPath object and register namespaces.
     *
     * @param string $resultString XML
     * @return DOMXPath Resulting Xpath object with registered namespaces
     */
    protected function prepareXpathFromResultString($resultString) {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($resultString);

        $xpath = new DOMXPath($domDocument);

        foreach ($this->xpathNamespaces as $prefix => $namespaceUri)
        {
            $xpath->registerNamespace($prefix, $namespaceUri);
        }

        return $xpath;
    }

    /**
     * Basic test for invalid verbs.
     *
     * @covers ::indexAction
     */
    public function testInvalidVerb() {
        $this->dispatch('/oai?verb=InvalidVerb');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->assertContains('badVerb', $response->getBody(),
                "Response must contain 'badVerb'");
    }

    /**
     * Basic test for requests without verb.
     *
     * @covers ::indexAction
     */
    public function testNoVerb() {
        $this->dispatch('/oai');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->assertContains('badVerb', $response->getBody(),
                "Response must contain 'badVerb'");
    }

    /**
     * Test verb=Identify.
     *
     * @covers ::indexAction
     */
    public function testIdentify() {
        Zend_Registry::get('Zend_Config')->merge(new Zend_Config(array(
            'oai' => array('repository' => array('name' => 'test-repo-name'))
        )));

        $this->dispatch('/oai?verb=Identify');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpath('//oai:Identify');
        $this->assertXpathContentContains('//oai:Identify/oai:repositoryName', 'test-repo-name');
        $this->assertXpathContentContains('//oai:Identify/oai:baseURL', 'http:///oai');
        $this->assertXpathContentContains('//oai:Identify/oai:protocolVersion', '2.0');
        $this->assertXpathContentContains('//oai:Identify/oai:adminEmail', 'opus4ci@example.org');
        $this->assertXpathContentContains('//oai:Identify/oai:earliestDatestamp', '2002-04-29');
        $this->assertXpathContentContains('//oai:Identify/oai:deletedRecord', 'persistent');
        $this->assertXpathContentContains('//oai:Identify/oai:granularity', 'YYYY-MM-DD');
    }

    public function testIdentifyDescriptionEprintsBasic()
    {
        $this->dispatch('/oai?verb=Identify');
        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathCount('//oai:description', 2);
        $this->assertXpathCount('//oai:description/eprints:eprints', 1);
        $this->assertNotXpath('//eprints:content/eprints:URL');
        $this->assertXpathContentContains(
            '//oai:description/eprints:eprints/eprints:content/eprints:text',
            'OPUS 4 repository containing document metadata and fulltexts.'
        );

        $this->assertXpath('//eprints:metadataPolicy');
        $this->assertNotXpath('//eprints:metadataPolicy/eprints:URL');
        $this->assertNotXpath('//eprints:metadataPolicy/eprints:text');

        $this->assertXpath('//eprints:dataPolicy');
        $this->assertNotXpath('//eprints:dataPolicy/eprints:URL');
        $this->assertNotXpath('//eprints:dataPolicy/eprints:text');

        $this->assertNotXpath('//eprints:submissionPolicy');
        $this->assertNotXpath('//eprints:comment');
    }

    public function testIdentifyDescriptionEprintsConfigured()
    {
        $values = array(
            'content' => array('url' => 'test-content-url', 'text' => 'test-content-text'),
            'metadataPolicy' => array('url' => 'test-metadata-url', 'text' => 'test-metadata-text'),
            'dataPolicy' => array('url' => 'test-data-url', 'text' => 'test-data-text'),
            'submissionPolicy' => array('url' => 'test-submission-url', 'text' => 'test-submission-text'),
            'comment' => array('url' => 'test-comment-url', 'text' => 'test-comment-text')
        );

        Zend_Registry::get('Zend_Config')->merge(new Zend_Config(array(
            'oai' => array('description' => array('eprints' => $values))
        )));

        $this->dispatch('/oai?verb=Identify');
        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathCount('//oai:description', 2);
        $this->assertXpathCount('//oai:description/eprints:eprints', 1);

        foreach ($values as $element => $content) {
            $this->assertXpathContentContains(
                "//oai:description/eprints:eprints/eprints:$element/eprints:URL",
                $content['url']
            );
            $this->assertXpathContentContains(
                "//oai:description/eprints:eprints/eprints:$element/eprints:text",
                $content['text']
            );
        }
    }

    public function testIdentifyDescriptionOaiIdentifier()
    {
        Zend_Registry::get('Zend_Config')->merge(new Zend_Config(array(
            'oai' => array('repository' => array('identifier' => 'test-repo-identifier'),
                'sample' => array('identifier' => 'test-sample-identifier'))
        )));

        $this->dispatch('/oai?verb=Identify');
        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathCount('//oai:description', 2);
        $this->assertXpathCount('//oai:description/oaiid:oai-identifier', 1);
        $this->assertXpathContentContains('//oai:description/oaiid:oai-identifier/oaiid:scheme', 'oai');
        $this->assertXpathContentContains(
            '//oai:description/oaiid:oai-identifier/oaiid:repositoryIdentifier', 'test-repo-identifier'
        );
        $this->assertXpathContentContains('//oai:description/oaiid:oai-identifier/oaiid:delimiter', ':');
        $this->assertXpathContentContains(
            '//oai:description/oaiid:oai-identifier/oaiid:sampleIdentifier', 'test-sample-identifier'
        );
    }

    /**
     * Test verb=ListMetadataFormats.
     *
     * @covers ::indexAction
     */
    public function testListMetadataFormats() {
        $this->dispatch('/oai?verb=ListMetadataFormats');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=ListSets.
     *
     * @covers ::indexAction
     */
    public function testListSets() {
        $this->dispatch('/oai?verb=ListSets');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());

        // Test "valid" set specs: Non-empty sets in test data
        $assertSets = array('doc-type:article', 'doc-type:preprint',
            'bibliography:true', 'bibliography:true',
            'ddc:62', 'msc:65Fxx', 'pacs:07.07.Df');
        foreach ($assertSets AS $assertSet) {
            $this->assertContains($assertSet, $response->getBody(),
                    "Response must contain set '$assertSet'");
            $this->assertContains("<setSpec>$assertSet</setSpec>", $response->getBody(),
                    "Response must contain set '$assertSet'");
        }

        // Test "valid" set specs: Non-existent/empty sets in test data.
        $assertNoSets = array('msc:90C90');
        foreach ($assertNoSets AS $assertNoSet) {
            $this->assertNotContains($assertNoSet, $response->getBody(),
                    "Response must not contain set '$assertNoSet'");
        }
    }

    /**
     * @covers ::indexAction
     */
    public function testGetRecordsFormats() {
        $formatTestDocuments = array(
            'xMetaDissPlus' => 41,
            'XMetaDissPlus' => 41,
            'oai_dc' => 91,
            'oai_pp' => 91,
            'copy_xml' => 91,
            'epicur' => 91);

        foreach ($formatTestDocuments AS $format => $docId) {
            $this->dispatch("/oai?verb=GetRecord&metadataPrefix=$format&identifier=oai::$docId");
            $this->assertResponseCode(200);

            $response = $this->getResponse();
            $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
            $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

            $this->assertContains("oai::$docId", $response->getBody(),
                    "Response must contain 'oai::$docId'");

            $xpath = $this->prepareXpathFromResultString($response->getBody());

            $result = $xpath->query('/*[name()="OAI-PMH"]');
            $this->assertEquals(1, $result->length,
                    'Expecting one <OAI-PMH> element');

            $result = $xpath->query('/*[name()="OAI-PMH"]/*[name()="error"]');
            $this->assertEquals(0, $result->length,
                    'Expecting no <OAI-PMH>/<error> element');

            $result = $xpath->query('/*[name()="OAI-PMH"]/*[name()="GetRecord"]');
            $this->assertEquals(1, $result->length,
                    'Expecting one <OAI-PMH>/<GetRecord> element');

            $result = $xpath->query('/*[name()="OAI-PMH"]/*[name()="GetRecord"]/*[name()="record"]');
            $this->assertEquals(1, $result->length,
                    'Expecting one <OAI-PMH>/<GetRecord>/<record> element');
        }
    }

    /**
     * Test verb=GetRecord, prefix=oai_dc.
     *
     * @covers ::indexAction
     */
    public function testGetRecordOaiDc() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::35');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=GetRecord, prefix=XMetaDissPlus.
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlus() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::41');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->assertContains('oai::41', $response->getBody(),
                "Response must contain 'oai::41'");

        $this->assertContains('xMetaDiss', $response->getBody(),
                "Response must contain 'xMetaDiss'");
    }

    /**
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusOnlyIfNotInEmbargo() {
        $today = date('Y-m-d', time());

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setEmbargoDate($today);
        $docId = $doc->store();

        $this->dispatch("/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::$docId");
        $this->assertResponseCode(200);

        $response = $this->getResponse()->getBody();
        $badStrings = array("Exception", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response, $badStrings);

        $this->assertContains("oai::$docId", $response, "Response must contain 'oai::$docId'");

        $this->assertContains('noRecordsMatch', $response);
        $this->assertContains('Document is not available for OAI export!', $response);
    }

    /**
     * Test verb=GetRecord, prefix=xMetaDissPlus.
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusAlternativeSpelling() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::41');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->assertContains('oai::41', $response->getBody(),
                "Response must contain 'oai::41'");

        $this->assertContains('xMetaDiss', $response->getBody(),
                "Response must contain 'xMetaDiss'");
    }

    /**
     * Test verb=GetRecord, prefix=XMetaDissPlus.
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusContentDoc41() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::41');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        // Regression test for OPUSVIER-1866
        $assertTitles = array("Dr.", "Prof.");
        foreach ($assertTitles AS $title) {
            $testString = "<pc:academicTitle>$title</pc:academicTitle>";
            $this->assertContains($testString, $response->getBody(),
                    "Response must contain '$testString'");
        }
    }

    /**
     * Test verb=GetRecord, prefix=XMetaDissPlus.
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusContentDoc91() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::91');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        // Regression test for OPUSVIER-1865
        $xpath = $this->prepareXpathFromResultString($response->getBody());
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dc:creator');
        $this->assertEquals(3, $elements->length,
                "Unexpected dc:creator count");

        // Regression test for OPUSVIER-2164
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/*/pc:person');
        $this->assertEquals(4, $elements->length,
                "Unexpected pc:person count");
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/*/pc:person/pc:name');
        $this->assertEquals(4, $elements->length,
                "Unexpected pc:name count");
    }

    /**
     * Test verb=GetRecord, prefix=XMetaDissPlus.
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusNamespacesDoc91() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::91');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        // Regression test for OPUSVIER-2170, OPUSVIER-2175
        $badNSes = array(
            'xmlns:dc="http://www.d-nb.de/standards/subject/"',
            'xmlns:dcterms="http://www.d-nb.de/standards/subject/"',
            'xmlns:ddb="http://www.d-nb.de/standards/subject/"',
            'xmlns:ddb1="http://www.d-nb.de/standards/ddb/"',
        );
        foreach ($badNSes AS $badNS) {
            $this->assertNotContains($badNS, $response->getBody(),
                    "Output contains '$badNS', which indicates bad namespaces.");
        }
    }

    /**
     * Regression test for OPUSVIER-2193
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc91Dcterms() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::91');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2193
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:medium');
        $this->assertEquals(2, $elements->length,
                "Unexpected dcterms:medium count");

        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:medium[text()="application/pdf"]');
        $this->assertEquals(1, $elements->length,
                "Unexpected dcterms:medium count for application/pdf");

        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:medium[text()="text/plain"]');
        $this->assertEquals(1, $elements->length,
                "Unexpected dcterms:medium count for text/plain");
    }

    /**
     * Regression test for OPUSVIER-2068
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc91CheckThesisYearAccepted() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::91');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2068
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:dateAccepted');
        $this->assertEquals(1, $elements->length,
                "Unexpected dcterms:dateAccepted count");

        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:dateAccepted[text()="2010-02-26"]');
        $this->assertEquals(1, $elements->length,
                "Unexpected dcterms:dateAccepted count");
    }

    /**
     * Regression test for OPUSVIER-1788
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc146SubjectDDC() {
        $doc = new Opus_Document(146);
        $ddcs = array();
        foreach ($doc->getCollection() AS $c) {
            if ($c->getRoleName() == 'ddc') {
                $ddcs[] = $c->getNumber();
            }
        }
        $this->assertContains('28', $ddcs, "testdata changed");
        $this->assertContains('51', $ddcs, "testdata changed");

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-1788 (show DDC 51)
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dc:subject[@xsi:type="xMetaDiss:DDC-SG" and text()="51"]');
        $this->assertEquals(1, $elements->length,
                "Unexpected count for ddc:51 (should be visible)");

        // Regression test for OPUSVIER-1788 (dont show DDC 28)
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dc:subject[@xsi:type="xMetaDiss:DDC-SG" and text()="28"]');
        $this->assertEquals(0, $elements->length,
                "Unexpected count for ddc:28 (should be invisible)");
    }

    /**
     * Regression test for OPUSVIER-2068
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc148CheckThesisYearAccepted() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::148');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2068
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:dateAccepted');
        $this->assertEquals(1, $elements->length,
                "Unexpected dcterms:dateAccepted count");

        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:dateAccepted[text()="2012"]');
        $this->assertEquals(1, $elements->length,
                "Unexpected dcterms:dateAccepted count");
    }

    /**
     * Regression test for OPUSVIER-2448
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc1DdbIdentifier() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::1');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2448 - ddb:identifier with frontdoor url
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/ddb:identifier[@ddb:type="URL"]/text()');
        $this->assertEquals(1, $elements->length, "Unexpected ddb:identifier count");

        $value = $elements->item(0)->nodeValue;
        $this->assertContains("frontdoor/index/index/docId/1", $value,
                'expected frontdoor URL in ddb:identifier');
    }

    /**
     * Regression test for OPUSVIER-2452
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc132EmptyThesisGrantor() {
        $doc = new Opus_Document(132);
        $this->assertEquals('doctoralthesis', $doc->getType(),
                'testdata changed: document type changed');
        $this->assertEquals('published',      $doc->getServerState(),
                'testdata changed: document state changed');
        $this->assertEquals(0,                count($doc->getThesisGrantor()),
                'testdata changed: thesis grantor added to document');

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::132');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2452 - no thesis:grantor element
        $elements = $xpath->query('//thesis:degree/thesis:grantor');
        $this->assertEquals(0, $elements->length, "Unexpected thesis:grantor count");
    }

    /**
     * Regression test for OPUSVIER-2523
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc132EmptyThesisPublisher() {
        $doc = new Opus_Document(132);
        $this->assertEquals('doctoralthesis', $doc->getType(),
                'testdata changed: document type changed');
        $this->assertEquals('published',      $doc->getServerState(),
                'testdata changed: document state changed');
        $this->assertEquals(0,                count($doc->getThesisPublisher()),
                'testdata changed: thesis publisher added to document');

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::132');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2523 - no ddb:contact element
        $elements = $xpath->query('//ddb:contact');
        $this->assertEquals(0, $elements->length, "Unexpected thesis:grantor count");
    }

    /**
     * Regression tests on document 93
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc93() {
        $doc = new Opus_Document(93);
        $this->assertEquals('doctoralthesis', $doc->getType(),
                'testdata changed: document type changed');
        $this->assertEquals('published',      $doc->getServerState(),
                'testdata changed: document state changed');
        $this->assertEquals(1,                count($doc->getThesisPublisher()),
                'testdata changed: thesis publisher removed from document');
        $this->assertEquals("",               $doc->getThesisPublisher(0)->getDnbContactId(),
                'testdata changed: someone added a DnbContactId to thesis publisher ');

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::93');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2523 - no ddb:contact element on empty contactId
        $elements = $xpath->query('//ddb:contact');
        $this->assertEquals(0, $elements->length, "Unexpected thesis:grantor count");
    }


    /**
     * Regression test for existing thesis:* and ddb:* elements
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc146ThesisAndDdb() {
        $doc = new Opus_Document(146);
        $this->assertEquals('masterthesis',   $doc->getType(),
                'testdata changed: document type changed');
        $this->assertEquals('published',      $doc->getServerState(),
                'testdata changed: document state changed');
        $this->assertEquals(2,                count($doc->getThesisGrantor()),
                'testdata changed: thesis grantor added to document');
        $this->assertEquals(2,                count($doc->getThesisPublisher()),
                'testdata changed: thesis publisher added to document');

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2452 - existing thesis:grantor element
        $elements = $xpath->query('//thesis:degree/thesis:grantor');
        $this->assertEquals(2, $elements->length, "Unexpected thesis:grantor count");

        // Regression test for OPUSVIER-2523 - existing ddb:contact element
        $elements = $xpath->query('//ddb:contact/@ddb:contactID');
        $this->assertEquals(1, $elements->length, "Unexpected ddb:contact count");
        $this->assertEquals('Lxxxx-xxxx', $elements->item(0)->nodeValue, "Wrong ddb:contact");

        // Testing for other existing elements
        $elements = $xpath->query('//thesis:degree/thesis:level[text()="master"]');
        $this->assertEquals(1, $elements->length, "Unexpected thesis:level=='master' count");

        $elements = $xpath->query('//thesis:degree/thesis:grantor/cc:universityOrInstitution/cc:name');
        $this->assertEquals(2, $elements->length, "Unexpected thesis:level=='master' count");
    }

    /**
     * Testet, ob die neuangelegten Dokumenttypen als thesislevel ausgegeben werden.
     * Opusvier-3341
     *
     * @covers ::indexAction
     */
    public function testThesisLevelForXMetaDissPlus() {
        $thesisLevel = array('diplom' => 'Diplom', 'magister' => 'M.A.', 'examen' => 'other');
        foreach ($thesisLevel as $level => $label) {
            $doc = $this->createTestDocument();
            $doc->setType($level);
            $doc->setServerState('published');
            $docId = $doc->store();

            $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai:opus4.demo:' . $docId);

            $xpath = $this->prepareXpathFromResultString($this->getResponse()->getBody());

            $elements = $xpath->query('//thesis:degree/thesis:level');
            $this->assertEquals($label, $elements->item(0)->nodeValue);

            $elements = $xpath->query('//dc:type[@xsi:type="dini:PublType"]');
            $this->assertEquals('masterThesis', $elements->item(0)->nodeValue);
        }
    }

    /**
     * Regression test for OPUSVIER-2379
     *
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc91DocType() {
        $doc = new Opus_Document(91);
        $this->assertEquals("report", $doc->getType(), "testdata changed");

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::91');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2379 (show doc-type:report)
        $elements = $xpath->query('//oai_dc:dc/dc:type[text()="doc-type:report"]');
        $this->assertEquals(1, $elements->length,
                "Unexpected count for doc-type:report");
    }

    /**
     * Regression tests on document 146
     *
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc146() {

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::146');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2393 (show dc:contributor)
        $elements = $xpath->query('//oai_dc:dc/dc:contributor/text()');
        $this->assertGreaterThanOrEqual(2, $elements->length, 'dc:contributor count changed');
        $this->assertEquals('Doe, Jane (PhD)', $elements->item(0)->nodeValue, 'dc:contributor field changed');
        $this->assertEquals('Baz University',  $elements->item(1)->nodeValue, 'dc:contributor field changed');

        // Regression test for OPUSVIER-2393 (show dc:identifier)
        $urnResolverUrl = Zend_Registry::get('Zend_Config')->urn->resolverUrl;
        $elements = $xpath->query('//oai_dc:dc/dc:identifier[text()="' . $urnResolverUrl . 'urn:nbn:op:123"]');
        $this->assertEquals(1, $elements->length, 'dc:identifier URN count changed');

        $elements = $xpath->query('//oai_dc:dc/dc:identifier[text()="123"]');
        $this->assertGreaterThanOrEqual(1, $elements->length, 'dc:identifier URN count changed');
    }

    /**
     * Regression tests on document 91
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc91() {

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::91');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2393 (show dc:identifier)
        $elements = $xpath->query('//oai_dc:dc/dc:identifier/text()');

        $foundIds = array();
        foreach ($elements AS $element) {
            $nodeValue = $element->nodeValue;
            if (strstr($nodeValue, '/files/')) {
                $foundIds[] = preg_replace("/^.*(\/files\/\d+\/.*)$/", "$1", $element->nodeValue);
            }
        }

        $this->assertContains("/files/91/test.pdf", $foundIds);
        $this->assertContains("/files/91/test.txt", $foundIds);
        $this->assertContains("/files/91/frontdoor_invisible.txt", $foundIds);

        // Regression test for OPUSVIER-2393 (show dc:creator)
        $elements = $xpath->query('//oai_dc:dc/dc:creator/text()');
        $this->assertEquals(3, $elements->length, 'dc:creator count changed');
        $this->assertEquals('Doe, John', $elements->item(0)->nodeValue, 'dc:creator field changed');
        $this->assertEquals('Zufall, Rainer', $elements->item(1)->nodeValue, 'dc:creator field changed');
        $this->assertEquals('Fall, Klara', $elements->item(2)->nodeValue, 'dc:creator field changed');
    }

    /**
     * Regression test for OPUSVIER-2380 and OPUSVIER-2378
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc10SubjectDdcAndDate() {
        $doc = new Opus_Document(10);
        $ddcs = array();
        foreach ($doc->getCollection() AS $c) {
            if ($c->getRoleName() == 'ddc') {
                $ddcs[] = $c->getNumber();
            }
        }
        $this->assertContains("004", $ddcs, "testdata changed");

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::10');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2380 (show <dc:subject>ddc:)
        $elements = $xpath->query('//oai_dc:dc/dc:subject[text()="ddc:004"]');
        $this->assertEquals(1, $elements->length,
                "Unexpected count for ddc:004");

        // Regression test for OPUSVIER-2378 (show <dc:date>)
        $elements = $xpath->query('//oai_dc:dc/dc:date');
        $this->assertEquals(1, $elements->length,
                "Unexpected count for dc:date");

        // Regression test for OPUSVIER-2378 (show <dc:date>2003)
        $elements = $xpath->query('//oai_dc:dc/dc:date[text()="2003"]');
        $this->assertEquals(1, $elements->length,
                "Unexpected count for dc:date");
    }

    /**
     * Regression test for OPUSVIER-2378
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc114DcDate() {
        $doc = new Opus_Document(114);
        $completedDate = $doc->getCompletedDate();
        $this->assertEquals("2011-04-19", "$completedDate", "testdata changed");

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::114');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2378 (show <dc:date>)
        $elements = $xpath->query('//oai_dc:dc/dc:date');
        $this->assertEquals(1, $elements->length,
                "Unexpected count for dc:date");

        // Regression test for OPUSVIER-2378 (show <dc:date>2011-04-19)
        $elements = $xpath->query('//oai_dc:dc/dc:date[text()="2011-04-19"]');
        $this->assertEquals(1, $elements->length,
                "Unexpected count for dc:date");
    }

    /**
     * Regression test for OPUSVIER-2454
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc1ByIdentifierPrefixOai() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::1');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2454 (check returned dc:identifier)
        $elements = $xpath->query('//oai_dc:dc/dc:identifier[text()="urn:nbn:de:gbv:830-opus-225"]');
        $this->assertEquals(1, $elements->length, "Expected URN not found");
    }

    /**
     * Regression test for OPUSVIER-2454
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc1ByIdentifierPrefixUrn() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=urn:nbn:de:gbv:830-opus-225');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2454 (check returned dc:identifier)
        $elements = $xpath->query('//oai_dc:dc/dc:identifier[text()="urn:nbn:de:gbv:830-opus-225"]');
        $this->assertEquals(1, $elements->length, "Expected URN not found");
    }

    /**
     * Regression test for OPUSVIER-2535
     * @covers ::indexAction
     */
    public function testGetRecordWithNonExistingDocumentId() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::12345678');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2535 (check OAI error codes)
        $elements = $xpath->query('//oai:error[@code="idDoesNotExist"]');
        $this->assertEquals(1, $elements->length, "Expecting idDoesNotExist");
    }

    /**
     * Regression test for OPUSVIER-2454
     * @covers ::indexAction
     */
    public function testGetRecordWithInvalidIdentifierPrefix() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=foo::1');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2454 (check OAI error codes)
        $elements = $xpath->query('//oai:error[@code="badArgument"]');
        $this->assertEquals(1, $elements->length, "Expecting badArgument");
    }

    /**
     * Test verb=ListIdentifiers.
     * @covers ::indexAction
     */
    public function testListIdentifiers() {
        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=oai_dc');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=ListRecords, metadataPrefix=oai_dc.
     * @covers ::indexAction
     */
    public function testListRecords() {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&from=2006-01-01');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->assertContains('<ListRecords>', $response->getBody(),
                "Response must contain '<ListRecords>'");
        $this->assertContains('<record>', $response->getBody(),
                "Response must contain '<record>'");
    }

    /**
     * Regression Test for OPUSVIER-3142
     * @covers ::indexAction
     */
    public function testListRecordsXMetaDissPlusDocumentsWithFilesOnly()
    {
        Zend_Registry::get('Zend_Config')->merge(
            new Zend_Config(array(
                'oai' => array(
                    'max' => array(
                        'listrecords' => 100,
                        'listidentifiers' => 200,
                    )
                )
            ))
        );
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=xMetaDissPlus');

        $responseBody = $this->getResponse()->getBody();

        $this->assertNotContains('<ddb:fileNumber>0</ddb:fileNumber>', $responseBody,
        "Response must not contain records without files");
    }

    /**
     * @covers ::indexAction
     */
    public function testListRecordsXMetaDissPlusDocumentsNotInEmbargoOnly()
    {
        $tomorrow = date('Y-m-d', strtotime('tomorrow'));
        $yesterday = date('Y-m-d', strtotime('yesterday'));

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setEmbargoDate($tomorrow);
        $file = $this->createTestFile('volltext.pdf');
        $doc->addFile($file);
        $docId = $doc->store();

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = $this->createTestFile('volltext2.pdf');
        $doc->addFile($file);
        $visibleId = $doc->store();

        $this->dispatch("/oai?verb=ListRecords&metadataPrefix=xMetaDissPlus&from=$yesterday");

        $body = $this->getResponse()->getBody();

        $this->assertNotContains("oai::$docId", $body, 'Response should not contain embargoed document.');
        $this->assertContains("oai::$visibleId", $body, 'Response should contain document without embargo.');
    }

    /**
     * Regression test for OPUSVIER-3501
     * @covers ::indexAction
     */
    public function testListRecordsXMetaDissPlusSetAndUntilAttributesSetCorrectly() {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=xMetaDissPlus&from=2010-01-01&until=2011-01-01'
            . '&set=bibliography:false');

        $xpath = $this->prepareXpathFromResultString($this->getResponse()->getBody());

        $elements = $xpath->query('//oai:request');
        $this->assertEquals(1, $elements->length);

        $elements = $xpath->query('//oai:request[@from="2010-01-01"]');
        $this->assertEquals(1, $elements->length);

        $elements = $xpath->query('//oai:request[@until="2011-01-01"]');
        $this->assertEquals(1, $elements->length);

        $elements = $xpath->query('//oai:request[@set="bibliography:false"]');
        $this->assertEquals(1, $elements->length);
    }

    /**
     * Test that proves the bugfix for OPUSVIER-1710 is working as intended.
     * @covers ::indexAction
     */
    public function testGetDeletedDocumentReturnsStatusDeleted() {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::123');
        $this->resetSecurity();

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $this->assertContains('<GetRecord>', $this->getResponse()->getBody());
        $this->assertContains('<header status="deleted">', $this->getResponse()->getBody());

        $this->assertNotContains('<error>', $this->getResponse()->getBody());
        $this->assertNotContains('<error>Unauthorized: Access to module not allowed.</error>', $this->getResponse()->getBody());
        $this->assertNotContains('<error code="unknown">An internal error occured.</error>', $this->getResponse()->getBody());
    }

    /**
     * @covers ::indexAction
     */
    public function testTransferUrlIsPresent() {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('foobar.pdf');
        $doc->addFile($file);
        $this->docIds[] = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());

        $this->assertResponseCode(200);
        $this->assertContains('<ddb:transfer', $this->getResponse()->getBody());
        $this->assertContains($this->getRequest()->getBaseUrl() . '/oai/container/index/docId/' . $doc->getId() . '</ddb:transfer>', $this->getResponse()->getBody());
    }

    /**
     * @covers ::indexAction
     */
    public function testTransferUrlIsNotPresent() {
        $doc = $this->createTestDocument();
        $doc->setServerState("published");
        $this->docIds[] = $doc->store();
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());

        $this->assertResponseCode(200);
        $this->assertNotContains('<ddb:transfer ddb:type="dcterms:URI">', $this->getResponse()->getBody());
    }

    /**
     * Test verb=GetRecord, prefix=epicur.
     *
     * @covers ::indexAction
     */
    public function testGetRecordEpicurUrlEncoding() {
        $expectedFileNames = array("'many'  -  spaces  and  quotes.pdf", 'special-chars-%-"-#-&.pdf');

        $doc = new Opus_Document(147);
        $fileNames = array_map(function ($f) { return $f->getPathName(); }, $doc->getFile());
        sort($fileNames);

        $this->assertEquals(2, count($fileNames), "testdata changed");
        $this->assertEquals($expectedFileNames, $fileNames, "testdata changed");

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=epicur&identifier=oai::147');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2444 - url encoding of transfer files.
        $elements = $xpath->query('//epicur:resource/epicur:identifier[@target="transfer"]/text()');
        $this->assertEquals(2, $elements->length, "Unexpected identifier count");

        $fetchedNames = array();
        foreach ($elements AS $element) {
            $fetchedNames[] = preg_replace("/^.*\/147\//", "", $element->nodeValue);
        }

        $this->assertContains("special-chars-%25-%22-%23-%26.pdf", $fetchedNames);
        $this->assertContains("%27many%27%20%20-%20%20spaces%20%20and%20%20quotes.pdf", $fetchedNames);
    }

    /**
     * Test if the flag "VisibileInOai" affects all files of a document
     *
     * @covers ::indexAction
     */
    public function testDifferentFilesVisibilityOfOneDoc() {

        //create document with two files
        $d = $this->createTestDocument();
        $d->setServerState('published');

        $f1 = new Opus_File();
        $f1->setPathName('foo.pdf');
        $f1->setVisibleInOai(false);
        $d->addFile($f1);

        $f2 = new Opus_File();
        $f2->setPathName('bar.pdf');
        $f2->setVisibleInOai(false);
        $d->addFile($f2);

        $this->docIds[] = $d->store();
        $id = $d->getId();

        //oai query of that document
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=copy_xml&identifier=oai::' . $id);

        $response = $this->getResponse()->getBody();
        $this->assertContains('<Opus_Document xmlns="" Id="' . $id . '"', $response);
        $this->assertNotContains('<File', $response);
    }

    /**
     * request for metadataPrefix=copy_xml is denied for non-administrative people
     *
     * @covers ::indexAction
     */
    public function testRequestForMetadataPrefixCopyxmlAndVerbGetRecordIsDenied() {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=copy_xml&identifier=oai::80');
        $this->assertContains('<error code="cannotDisseminateFormat">The metadata format &amp;quot;copy_xml&amp;quot; given by metadataPrefix is not supported by the item or this repository.</error>',
                $this->getResponse()->getBody(), 'do not prevent usage of metadataPrefix copy_xml and verb GetRecords');
        $this->resetSecurity();
    }

    /**
     * @covers ::indexAction
     */
    public function testRequestForMetadataPrefixCopyxmlAndVerbListRecordIsDenied() {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=copy_xml&from=2100-01-01');
        $this->assertContains('<error code="cannotDisseminateFormat">The metadata format &amp;quot;copy_xml&amp;quot; given by metadataPrefix is not supported by the item or this repository.</error>',
                $this->getResponse()->getBody(), 'do not prevent usage of metadataPrefix copy_xml and verb ListRecords');
        $this->resetSecurity();
    }

    /**
     * @covers ::indexAction
     */
    public function testRequestForMetadataPrefixCopyxmlAndVerbListIdentifiersIsDenied() {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=copy_xml');
        $this->assertContains('<error code="cannotDisseminateFormat">The metadata format &amp;quot;copy_xml&amp;quot; given by metadataPrefix is not supported by the item or this repository.</error>',
                $this->getResponse()->getBody(), 'do not prevent usage of metadataPrefix copy_xml and verb ListIdentifiers');
        $this->resetSecurity();
    }

    public function enableSecurity() {
        $r = Opus_UserRole::fetchByName('guest');

        $modules = $r->listAccessModules();
        $this->_addOaiModuleAccess = !in_array('oai', $modules);
        if ($this->_addOaiModuleAccess) {
            $r->appendAccessModule('oai');
            $r->store();
        }

        // enable security
        $config = Zend_Registry::get('Zend_Config');
        $this->_security = $config->security;
        $config->security = '1';
        Zend_Registry::set('Zend_Config', $config);
    }

    private function resetSecurity() {
        $r = Opus_UserRole::fetchByName('guest');

        if ($this->_addOaiModuleAccess) {
            $r->removeAccessModule('oai');
            $r->store();
        }

        // restore security settings
        $config = Zend_Registry::get('Zend_Config');
        $config->security = $this->_security;
        Zend_Registry::set('Zend_Config', $config);
    }

    /**
     * Regression test for OPUSVIER-2450
     *
     * @covers ::indexAction
     */
    public function testDdbFileNumberForSingleDocumentAndSingleFile() {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('foobar.pdf');
        $doc->addFile($file);
        $this->docIds[] = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());

        $this->assertResponseCode(200);
        $this->assertContains('<ddb:fileNumber>1</ddb:fileNumber>', $this->getResponse()->getBody());
        $this->assertContains($this->getRequest()->getBaseUrl() . '/oai/container/index/docId/' . $doc->getId() . '</ddb:transfer>', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2450
     *
     * @covers ::indexAction
     */
    public function testDdbFileNumberForSingleDocumentAndMultipleFiles() {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('foo.pdf');
        $doc->addFile($file);
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('bar.pdf');
        $doc->addFile($file);
        $this->docIds[] = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());

        $this->assertResponseCode(200);
        $this->assertContains('<ddb:fileNumber>2</ddb:fileNumber>', $this->getResponse()->getBody());
        $this->assertContains($this->getRequest()->getBaseUrl() . '/oai/container/index/docId/' . $doc->getId() . '</ddb:transfer>', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2450
     *
     * @covers ::indexAction
     */
    public function testDdbFileNumberForMultipleDocumentsForXMetaDissPlus() {
        $collection = new Opus_Collection(112);

        $doc1 = $this->createTestDocument();
        $doc1->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('foo.pdf');
        $doc1->addFile($file);
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('bar.pdf');
        $doc1->addFile($file);
        $doc1->addCollection($collection);
        $this->docIds[] = $doc1->store();

        $doc2 = $this->createTestDocument();
        $doc2->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('baz.pdf');
        $doc2->addFile($file);
        $doc2->addCollection($collection);
        $this->docIds[] = $doc2->store();

        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=xMetaDissPlus&set=ddc:000');

        $body = $this->getResponse()->getBody();
        $this->assertContains('<ddb:fileNumber>2</ddb:fileNumber>', $body);
        $this->assertContains('<ddb:fileNumber>1</ddb:fileNumber>', $body);
        $this->assertNotContains('<ddb:fileNumber>3</ddb:fileNumber>', $body);
    }

    /**
     * Regression test for OPUSVIER-2508
     *
     * @covers ::indexAction
     */
    public function testTransferUrlIsIOnlyGivenForDocsWithFulltext() {
        $collection = new Opus_Collection(112);

        $doc1 = $this->createTestDocument();
        $doc1->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('foo.pdf');
        $doc1->addFile($file);
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('bar.pdf');
        $doc1->addFile($file);
        $doc1->addCollection($collection);
        $this->docIds[] = $doc1->store();

        $doc2 = $this->createTestDocument();
        $doc2->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('baz.pdf');
        $doc2->addFile($file);
        $doc2->addCollection($collection);
        $this->docIds[] = $doc2->store();

        $doc3 = $this->createTestDocument();
        $doc3->setServerState('published');
        $doc3->addCollection($collection);
        $this->docIds[] = $doc3->store();

        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=xMetaDissPlus&set=ddc:000');

        $body = $this->getResponse()->getBody();
        $this->assertContains('<ddb:fileNumber>2</ddb:fileNumber>', $body);
        $this->assertContains('<ddb:fileNumber>1</ddb:fileNumber>', $body);
        // docs without files are not longer output; see OPUSVIER-3142
//        $this->assertContains('<ddb:fileNumber>0</ddb:fileNumber>', $body);
        $this->assertNotContains('<ddb:fileNumber>3</ddb:fileNumber>', $body);

        // TODO host name and instance name are empty in test environment (OPUSVIER-2511)
        $this->assertContains('<ddb:transfer ddb:type="dcterms:URI">http:///oai/container/index/docId/' . $doc1->getId() . '</ddb:transfer>', $body);
        $this->assertContains('<ddb:transfer ddb:type="dcterms:URI">http:///oai/container/index/docId/' . $doc2->getId() . '</ddb:transfer>', $body);
        $this->assertNotContains('<ddb:transfer ddb:type="dcterms:URI">http:///oai/container/index/docId/' . $doc3->getId() . '</ddb:transfer>', $body);
    }

    /**
     *  Regression Test for OPUSVIER-3072 (was Regression test for OPUSVIER-2509)
     *
     * @covers ::indexAction
     */
    public function testForDDCSubjectTypeForXMetaDissPlus() {
        $collection = new Opus_Collection(112);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addCollection($collection);

        // fixing test for OPUSVIER-3142
        $visibleFile = new Opus_File();
        $visibleFile->setPathName('visible_file.txt');
        $visibleFile->setVisibleInOai(true);
        $doc->addFile($visibleFile);

        $this->docIds[] = $doc->store();

        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=xMetaDissPlus&set=ddc:000');

        $body = $this->getResponse()->getBody();
        $this->assertNotContains('<dc:subject xsi:type="dcterms:DDC">000</dc:subject>', $body);
        $this->assertContains('<dc:subject xsi:type="xMetaDiss:DDC-SG">000</dc:subject>', $body);
    }

    /**
     * Regression test for OPUSVIER-2564
     *
     * @covers ::indexAction
     */
    public function testForInvalidSetSpecsInListRecords() {
        $collectionRole = Opus_CollectionRole::fetchByOaiName('pacs');
        $this->assertNotNull($collectionRole);

        $this->assertContains(79, $collectionRole->getDocumentIdsInSet('pacs:07.75.+h'));
        $this->assertContains(79, $collectionRole->getDocumentIdsInSet('pacs:85.85.+j'));

        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=pacs');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<setSpec>pacs:07.07.Df</setSpec>', $body);
        $this->assertContains(':79</identifier>', $body);

        // Regression test for OPUSVIER-2564: invalid SetSpec characters
        $this->assertNotContains('<setSpec>pacs:07.75.', $body);
        $this->assertNotContains('<setSpec>pacs:85.85.', $body);
    }

    /**
     * Regression test for OPUSVIER-2564
     *
     * @covers ::indexAction
     */
    public function testForInvalidSetSpecsInListIdentifiers() {
        $collectionRole = Opus_CollectionRole::fetchByOaiName('pacs');
        $this->assertNotNull($collectionRole);

        $this->assertContains(79, $collectionRole->getDocumentIdsInSet('pacs:07.75.+h'));
        $this->assertContains(79, $collectionRole->getDocumentIdsInSet('pacs:85.85.+j'));

        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=oai_dc&set=pacs');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<setSpec>pacs:07.07.Df</setSpec>', $body);
        $this->assertContains(':79</identifier>', $body);

        // Regression test for OPUSVIER-2564: invalid SetSpec characters
        $this->assertNotContains('<setSpec>pacs:07.75.', $body);
        $this->assertNotContains('<setSpec>pacs:85.85.', $body);
    }

    /**
     * Regression test for OPUSVIER-2564
     *
     * @covers ::indexAction
     */
    public function testForInvalidSetSpecsInGetRecord79() {
        $collectionRole = Opus_CollectionRole::fetchByOaiName('pacs');
        $this->assertNotNull($collectionRole);

        $this->assertContains(79, $collectionRole->getDocumentIdsInSet('pacs:07.75.+h'));
        $this->assertContains(79, $collectionRole->getDocumentIdsInSet('pacs:85.85.+j'));

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai:opus4.demo:79');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<setSpec>pacs:07.07.Df</setSpec>', $body);
        $this->assertContains(':79</identifier>', $body);

        // Regression test for OPUSVIER-2564: invalid SetSpec characters
        $this->assertNotContains('<setSpec>pacs:07.75.', $body);
        $this->assertNotContains('<setSpec>pacs:85.85.', $body);
    }

    /**
     * @covers ::indexAction
     */
    public function testListRecordsForEmptySet() {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=open_access');

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $this->assertContains('<error code="noRecordsMatch">', $body);
    }

    /**
     * @covers ::indexAction
     */
    public function testListRecordsForEmptySubset() {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=open_access:open_access');

        $this->assertResponseCode(200);
        $body = $this->getResponse()->getBody();
        $this->assertContains('<error code="noRecordsMatch">', $body);
    }

    /**
     * Regression test for OPUSVIER-2607
     *
     * @covers ::indexAction
     */
    public function testXMetaDissPlusOmitPersonSurnameIfEmpty() {

      $document = $this->createTestDocument();
      $document->setServerState('published');

      $author = new Opus_Person();
      $author->setLastName('Foo');
      $author->setDateOfBirth('1900-01-01');
      $author->setPlaceOfBirth('Berlin');
//      $authorId = $author->store();
      $document->addPersonAuthor($author);

      $advisor = new Opus_Person();
      $advisor->setLastName('Bar');
      $advisor->setDateOfBirth('1900-01-01');
      $advisor->setPlaceOfBirth('Berlin');
//      $advisorId = $advisor->store();
      $document->addPersonAdvisor($advisor);

      $referee = new Opus_Person();
      $referee->setLastName('Baz');
      $referee->setDateOfBirth('1900-01-01');
      $referee->setPlaceOfBirth('Berlin');
//      $refereeId = $referee->store();
      $document->addPersonReferee($referee);

      $this->docIds[] = $document->store();

      $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $document->getId());

      $this->assertResponseCode(200);
      $response = $this->getResponse();
      $xpath = $this->prepareXpathFromResultString($response->getBody());

      $authorName = $xpath->query('//xMetaDiss:xMetaDiss/dc:creator/pc:person/pc:name');
      $this->assertEquals(1, $authorName->length);
      $authorFirstName = $xpath->query('//xMetaDiss:xMetaDiss/dc:creator/pc:person/pc:name/pc:foreName');
      $this->assertEquals(0, $authorFirstName->length);
      $authorLastName = $xpath->query('//xMetaDiss:xMetaDiss/dc:creator/pc:person/pc:name/pc:surName');
      $this->assertEquals(1, $authorLastName->length);

      $advisorName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="advisor"]/pc:person/pc:name');
      $this->assertEquals(1, $advisorName->length);
      $advisorFirstName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="advisor"]/pc:person/pc:name/pc:foreName');
      $this->assertEquals(0, $advisorFirstName->length);
      $advisorLastName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="advisor"]/pc:person/pc:name/pc:surName');
      $this->assertEquals(1, $advisorLastName->length);

      $refereeName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="referee"]/pc:person/pc:name');
      $this->assertEquals(1, $refereeName->length);
      $refereeFirstName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="referee"]/pc:person/pc:name/pc:foreName');
      $this->assertEquals(0, $refereeFirstName->length);
      $refereeLastName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="referee"]/pc:person/pc:name/pc:surName');
      $this->assertEquals(1, $refereeLastName->length);
   }

    /**
     * Regression Test for OPUSVIER-3041
     * (was Regression Test for OPUSVIER-2599, but departments are revived now)
     *
     * @covers ::indexAction
     */
    public function testShowThesisGrantorDepartmentName() {

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');

        $this->assertResponseCode(200);
        $response = $this->getResponse();

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        $grantorInstitution = $xpath->query('//xMetaDiss:xMetaDiss/thesis:degree/thesis:grantor/cc:universityOrInstitution/cc:name');
//        $this->assertEquals(2, $grantorInstitution->length, "Expected one grantor institution");
        $this->assertEquals('Foobar Universität', $grantorInstitution->item(0)->nodeValue);

        $grantorDepartment = $xpath->query('//xMetaDiss:xMetaDiss/thesis:degree/thesis:grantor/cc:universityOrInstitution/cc:department/cc:name');
        $this->assertEquals('Testwissenschaftliche Fakultät', $grantorDepartment->item(0)->nodeValue);

    }

    /**
     * Regression Test for OPUSVIER-3162
     *
     * @covers ::indexAction
     */
    public function testXMetaDissPlusOutputLanguageCode() {

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::302');
        $xpath = $this->prepareXpathFromResultString($this->getResponse()->getBody());
        $language = $xpath->query('//xMetaDiss:xMetaDiss/dc:language')->item(0);
        $this->assertEquals('fre', $language->nodeValue);
    }

    /**
     * XMetaDissPlus Schema validation (see OPUSVIER-3165)
     *
     * @covers ::indexAction
     */
    public function testXMetaDissPlusIsSchemaValid() {
        $xmlCatalog = getenv('XML_CATALOG_FILES');
        if(!strpos($xmlCatalog, 'opus4-catalog.xml')) {
            $this->markTestSkipped(
                'Environment Variable XML_CATALOG_FILES not set for resources/opus4-catalog.xml.');
        }
        libxml_use_internal_errors(true);

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');
        $xpath = $this->prepareXpathFromResultString($this->getResponse()->getBody());
        $xMetaDissNode = $xpath->query('//xMetaDiss:xMetaDiss')->item(0);
        $metadataDocument = new DOMDocument();
        $importedNode = $metadataDocument->importNode($xMetaDissNode, true);
        $metadataDocument->appendChild($importedNode);

        $valid = $metadataDocument->schemaValidate(APPLICATION_PATH
                . '/tests/resources/xmetadissplus/xmetadissplus.xsd');

        $this->assertTrue($valid, 'XML Schema validation failed for XMetaDissPlus');
    }

    /**
     * @covers ::indexAction
     */
    public function testListRecordsWithResumptionToken() {
        $max_records = 2;

        $config = Zend_Registry::get('Zend_Config');
        $config->oai->max->listrecords = $max_records;

        // first request: fetch documents list and expect resumption code
        $this->dispatch("/oai?verb=ListRecords&metadataPrefix=oai_dc");
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Error", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());
        $recordElements = $xpath->query('//oai:ListRecords/oai:record');
        $this->assertEquals($max_records, $recordElements->length);

        $rsTokenElement = $xpath->query('//oai:ListRecords/oai:resumptionToken[@cursor="0"]');
        $this->assertEquals(1, $rsTokenElement->length, 'foobar');
        $rsToken = $rsTokenElement->item(0)->textContent;
        $this->assertNotEmpty($rsToken);

        // next request: continue document list with resumption token
        $this->resetRequest();
        $this->dispatch("/oai?verb=ListRecords&resumptionToken=$rsToken");
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = array("Exception", "Stacktrace", "badVerb", "badArgument");
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());
        $recordElements = $xpath->query('//oai:ListRecords/oai:record');
        $this->assertEquals($max_records, $recordElements->length);

        $rsTokenElement = $xpath->query('//oai:ListRecords/oai:resumptionToken[@cursor="'.$max_records.'"]');
        $this->assertEquals(1, $rsTokenElement->length, 'foobar');
    }

    /**
     * Regression Test for OPUSVIER-2762
     *
     * @covers ::indexAction
     */
    public function testDcCreatorIsAuthorIfExists() {

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::302');
        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        $dcCreator = $xpath->query('//oai_dc:dc/dc:creator');
        $this->assertEquals(1, $dcCreator->length);
        $this->assertEquals('Author', $dcCreator->item(0)->nodeValue);

    }

    /**
     * Regression Test for OPUSVIER-2762
     *
     * @covers ::indexAction
     */
    public function testDcCreatorIsEditorIfAuthorNotExists() {

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::303');
        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        $dcCreator = $xpath->query('//oai_dc:dc/dc:creator');
        $this->assertEquals(1, $dcCreator->length);
        $this->assertEquals('Editor', $dcCreator->item(0)->nodeValue);

    }

    /**
     * Regression Test for OPUSVIER-2762
     *
     * @covers ::indexAction
     */
    public function testDcCreatorIsCreatingCorporationIfAuthorAndEditorNotExist() {

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::304');

        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        $dcCreator = $xpath->query('//oai_dc:dc/dc:creator');
        $this->assertEquals(1, $dcCreator->length);
        $this->assertEquals('CreatingCorporation', $dcCreator->item(0)->nodeValue);
    }

    /**
     * Regression Test for OPUSVIER-2762
     *
     * @covers ::indexAction
     */
    public function testDcCreatorIsOmittedIfNoValidEntrySupplied() {

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::305');

        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        $dcCreator = $xpath->query('//oai_dc:dc/dc:creator');
        $this->assertEquals(0, $dcCreator->length);
    }

    /**
     * @covers ::indexAction
     */
    public function testDcLangUsesShortest639Code() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::305');

        $body = $this->getResponse()->getBody();

        $this->assertNotContains('xml:lang="deu"', $body);
        $this->assertNotContains('xml:lang="ger"', $body);
        $this->assertContains('xml:lang="de"', $body);
    }

    /**
     * Regression Test for OPUSVIER-2762
     *
     * @covers ::indexAction
     */
    public function testHabilitationIsDcTypeDoctoralthesis() {

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::80');

        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());

        $docType = $xpath->query('//oai_dc:dc/dc:type');
        $values = $this->nodeListToArray($docType);

        $this->assertContains('doctoralthesis', $values);
        $this->assertContains('doc-type:doctoralThesis', $values);
    }

    /**
     * @covers ::indexAction
     */
    public function testXMetaDissPlusDcsourceContainsTitleParent() {
        $doc = new Opus_Document(146);
        $parentTitle = $doc->getTitleParent();
        $this->assertFalse(empty($parentTitle), 'Test Data modified: Expected TitleParent');

        $parentTitleValue = $parentTitle[0]->getValue();
        $this->assertFalse(empty($parentTitleValue), 'Test Data modified: Expected non-empty value for TitleParent');

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');
        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());
        $dcSource = $xpath->query('//xMetaDiss:xMetaDiss/dc:source');

        $this->assertEquals(1, $dcSource->length);
        $this->assertEquals($parentTitleValue . ', ' .
                            $doc->getVolume() . ', ' .
                            $doc->getIssue() . ', ' .
                            'S. ' . $doc->getPageFirst() . '-' . $doc->getPageLast(), $dcSource->item(0)->nodeValue);
    }

    /**
     * @covers ::indexAction
     */
    public function testXMetaDissPlusDcsourceContainsTitleParentPageNumber() {
        $doc = $this->createTestDocument();

        $doc->setServerState('published');

        $title = $doc->addTitleMain();
        $title->setValue('TitleMain');
        $title->setLanguage('deu');

        $title = $doc->addTitleParent();
        $title->setValue('TitleParent');
        $title->setLanguage('deu');

        $doc->setVolume('5');
        $doc->setIssue('12');
        $doc->setPageNumber('34');

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::' . $docId);

        $xpath = $this->prepareXpathFromResultString($this->getResponse()->getBody());

        $dcSource = $xpath->query('//xMetaDiss:xMetaDiss/dc:source');

        $this->assertEquals(1, $dcSource->length);

        $this->assertEquals('TitleParent, ' .
            $doc->getVolume() . ', ' .
            $doc->getIssue() . ', ' .
            $doc->getPageNumber() . ' S.', $dcSource->item(0)->nodeValue);
    }

    /**
     * @covers ::indexAction
     */
    public function testXMetaDissPlusDctermsispartofContainsSeriesTitleAndNumber() {
        $doc = new Opus_Document(146);
        $series = $doc->getSeries();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');
        $response = $this->getResponse();
        $xpath = $this->prepareXpathFromResultString($response->getBody());
        $dctermsIspartof = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:isPartOf');

        $this->assertEquals(1, $dctermsIspartof->length);

        $this->assertEquals($series[0]->getTitle().' ; '.$series[0]->getNumber(), $dctermsIspartof->item(0)->nodeValue);
    }

    /**
     * Mindestanforderungstest für OpenAire 3.0.
     * Document 145 und 146
     * Test verb=ListRecords, metadataPrefix=oai_dc, set=openaire.
     *
     * @covers ::indexAction
     */
    public function testListRecordsForOpenAireCompliance() {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=openaire');
        $this->assertResponseCode(200);

        $responseBody = $this->getResponse()->getBody();
        $badStrings = array("Exception", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($responseBody, $badStrings);

        $this->assertContains('<setSpec>openaire</setSpec>', $responseBody, 'OpenAire requires set-name to be "openaire"');
        $this->assertNotContains('<setSpec>doc-type:doctoralthesis</setSpec>', $responseBody);

        $xpath = $this->prepareXpathFromResultString($responseBody);

        // Access Level
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:rights");
        $this->assertEquals('info:eu-repo/semantics/openAccess', $queryResponse->item(1)->nodeValue);
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:rights");
        $this->assertEquals('info:eu-repo/semantics/embargoedAccess', $queryResponse->item(0)->nodeValue);

        // Publication Date, Embargo Date
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:date");
        $this->assertEquals('2011', $queryResponse->item(0)->nodeValue);
        $this->assertEquals('info:eu-repo/date/embargoEnd/2050-01-01', $queryResponse->item(1)->nodeValue,
            "If document is embargoed, <dc:date> should contain embargo date");
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:date");
        $this->assertEquals(1, $queryResponse->length, '146 should not contain embargodate (it has passed)');
        $this->assertEquals('2007-04-30', $queryResponse->item(0)->nodeValue);

        // Author
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:creator");
        $this->assertEquals('Doe, John', $queryResponse->item(0)->nodeValue);
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:creator");
        $this->assertEquals('Done, John', $queryResponse->item(0)->nodeValue);

        // Description
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:description");
        $this->assertEquals('Die KOBV-Zentrale in Berlin-Dahlem.', $queryResponse->item(0)->nodeValue);

        // Project-Identifier (Reference)
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:relation");
        $this->assertEquals('info:eu-repo/grantAgreement/EC/FP7/12345', $queryResponse->item(0)->nodeValue);
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:relation");
        $this->assertEquals('info:eu-repo/grantAgreement/EC/FP7/12345', $queryResponse->item(0)->nodeValue);

        // Document Type
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:type");
        $this->assertEquals('info:eu-repo/semantics/workingPaper', $queryResponse->item(0)->nodeValue);
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:type");
        $this->assertEquals('info:eu-repo/semantics/masterThesis', $queryResponse->item(0)->nodeValue);

        // Identifier
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:identifier");
        // assertContains, weil der Identifier die url ist und diese sich mit dem Host ändert
        $this->assertContains('frontdoor/index/index/docId/145', $queryResponse->item(0)->nodeValue);
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:identifier");
        $this->assertEquals('urn:nbn:op:123', $queryResponse->item(1)->nodeValue);

        // Document Title
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:title");
        $this->assertEquals('KOBV:Service-Zentrale', $queryResponse->item(0)->nodeValue);
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:title");
        $this->assertEquals('OpenAire Test Document', $queryResponse->item(0)->nodeValue);

        // Subject
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:subject");
        $this->assertEquals('Berlin', $queryResponse->item(0)->nodeValue);
    }

    /**
     *
     * @covers ::indexAction
     */
    public function testListRecordsForOpenAireRelation()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');

        $relation = new Opus_Enrichment();
        $relation->setKeyName('Relation');
        $relation->setValue('test-1234');
        $doc->addEnrichment($relation);

        $relation = new Opus_Enrichment();
        $relation->setKeyName('Relation');
        $relation->setValue('info:eu-repo/grantAgreement/EC/FP7/1234withPrefix');
        $doc->addEnrichment($relation);

        $role = Opus_CollectionRole::fetchByName('openaire');
        $openaire = $role->getCollectionByOaiSubset('openaire');
        $doc->addCollection($openaire);

        $docId = $doc->store();

        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=openaire');
        $this->assertResponseCode(200);

        $responseBody = $this->getResponse()->getBody();
        $badStrings = array("Exception", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($responseBody, $badStrings);

        $this->assertContains('<setSpec>openaire</setSpec>', $responseBody, 'OpenAire requires set-name to be "openaire"');
        $this->assertNotContains('<setSpec>doc-type:doctoralthesis</setSpec>', $responseBody);

        $xpath = $this->prepareXpathFromResultString($responseBody);

        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/{$docId}']/dc:relation");

        $values = $this->nodeListToArray($queryResponse);

        $this->assertCount(2, $values);
        $this->assertContains('test-1234', $values);
        $this->assertContains('info:eu-repo/grantAgreement/EC/FP7/1234withPrefix', $values);
   }

    /**
     * Testet die empfohlenen Felder für die OpenAireCompliance.
     * @covers ::indexAction
     */
    public function testListRecordsForOpenAireComplianceForRecommendedFields() {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=openaire');
        $this->assertResponseCode(200);

        $responseBody = $this->getResponse()->getBody();
        $badStrings = array("Exception", "Stacktrace", "badVerb");
        $this->checkForCustomBadStringsInHtml($responseBody, $badStrings);

        $xpath = $this->prepareXpathFromResultString($responseBody);

        // Language
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:language");
        $values = $this->nodeListToArray($queryResponse);
        $this->assertContains('deu', $values);

        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:language");
        $values = $this->nodeListToArray($queryResponse);
        $this->assertContains('deu', $values);

        // Publication Version
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:type");
        $values = $this->nodeListToArray($queryResponse);
        $this->assertContains('info:eu-repo/semantics/publishedVersion', $values);

        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:type");
        $values = $this->nodeListToArray($queryResponse);
        $this->assertContains('info:eu-repo/semantics/publishedVersion', $values);
        $this->assertContains('info:eu-repo/semantics/workingPaper', $values);

        // Source (TitleParent ist nur bei 146 gesetzt
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:source");
        $this->assertEquals('Parent Title', $queryResponse->item(0)->nodeValue);
    }

    /**
     * Testet die korrekte Anzeige eines Dokuments vom Typ Periodical Parts.
     *
     * @covers ::indexAction
     */
    public function testXMetaDissPlusForPeriodicalParts() {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setType('periodicalpart');
        $series = new Opus_Series(7);
        $doc->addSeries($series)->setNumber('1337');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai:opus4.demo:' . $docId);

        $xpath = $this->prepareXpathFromResultString($this->getResponse()->getBody());
        $elements = $xpath->query('//dcterms:isPartOf[@xsi:type="ddb:ZSTitelID"]');
        $this->assertEquals($elements->item(0)->nodeValue, 7, 'data contains wrong series id. expected id: 7');
        $elements = $xpath->query('//dcterms:isPartOf[@xsi:type="ddb:ZS-Ausgabe"]');
        $this->assertEquals($elements->item(0)->nodeValue, '1337', 'data contains wrong series number; expected number: 1337');
    }

    /**
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusLanguageCodes() {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $title = $doc->addTitleMain();
        $title->setValue('French title');
        $title->setLanguage('fra');
        $title = $doc->addTitleMain();
        $title->setValue('German title');
        $title->setLanguage('deu');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai:opus4.demo:' . $docId);

        $xpath = $this->prepareXpathFromResultString($this->getResponse()->getBody());

        $elements = $xpath->query('//dc:title[@lang = "fra"]');
        $this->assertEquals(0, $elements->length);

        $elements = $xpath->query('//dc:title[@lang = "fre"]');
        $this->assertEquals(1, $elements->length);

        $elements = $xpath->query('//dc:title[@lang = "deu"]');
        $this->assertEquals(0, $elements->length);

        $elements = $xpath->query('//dc:title[@lang = "ger"]');
        $this->assertEquals(1, $elements->length);
    }

    /**
     * @covers ::indexAction
     */
    public function testExampleLinkListIdentifiers()
    {
        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=oai_dc');

        $body = $this->getResponse()->getBody();

        $domDocument = new DOMDocument();
        $domDocument->loadXML($body);

        $elements = $domDocument->getElementsByTagName('header');

        $this->assertEquals(10, $elements->length);
    }

    protected function nodeListToArray($nodeList)
    {
        $values = array();

        foreach ($nodeList as $node)
        {
            $values[] = $node->nodeValue;
        }

        return $values;
    }

    /**
     * @covers ::indexAction
     */
    public function testErrorForRepeatedParameters()
    {
        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=oai_dc&metadataPrefix=oai_dc');

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $xpath = $this->prepareXpathFromResultString($body);

        $elements = $xpath->query('//oai:error');
        $this->assertEquals(1, $elements->length);

        $error = $elements->item(0);
        $this->assertEquals('badArgument', $error->getAttribute('code'));
    }

    /**
     * @covers ::indexAction
     */
    public function testErrorForUnknownId()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai:opus4.demo:9999');

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $xpath = $this->prepareXpathFromResultString($body);

        $elements = $xpath->query('//oai:error');
        $this->assertEquals(1, $elements->length);

        $error = $elements->item(0);
        $this->assertEquals('idDoesNotExist', $error->getAttribute('code'));
    }

    /**
     * @covers ::indexAction
     */
    public function testErrorForListMetadataFormatsWithBadIdentifierParameter()
    {
        $this->dispatch('/oai?verb=ListMetadataFormats&identifier=really_wrong_id');

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $xpath = $this->prepareXpathFromResultString($body);

        $elements = $xpath->query('//oai:error');
        $this->assertEquals(2, $elements->length);

        $errorCodes = array(
            $elements->item(0)->getAttribute('code'),
            $elements->item(1)->getAttribute('code')
        );

        $this->assertContains('badArgument', $errorCodes);
        $this->assertContains('idDoesNotExist', $errorCodes);
    }

    /**
     * @covers ::indexAction
     */
    public function testGetUsingXpathInTests()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai:opus4.demo:146');

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $xpath = $this->prepareXpathFromResultString($body);

        $elements = $xpath->query('//dc:title');
        $this->assertEquals(2, $elements->length);

        $elements = $xpath->query('//oai:setSpec');
        $this->assertEquals(12, $elements->length);

        $elements = $xpath->query('//oai:request');
        $this->assertEquals(1, $elements->length);
    }

    /**
     * @covers ::indexAction
     */
    public function testXmlValidOpusvier3846()
    {
        $this->dispatch('/oai?verb=GetRecord&identifier=oai:opus4.demo:146&metadataPrefix=xMetaDissPlus');

        libxml_use_internal_errors(true);

        $xpath = $this->prepareXpathFromResultString($this->getResponse()->getBody());
        $xMetaDissNode = $xpath->query('//xMetaDiss:xMetaDiss')->item(0);
        $metadataDocument = new DOMDocument();
        $importedNode = $metadataDocument->importNode($xMetaDissNode, true);
        $metadataDocument->appendChild($importedNode);

        $valid = $metadataDocument->schemaValidate(
            APPLICATION_PATH . '/tests/resources/xmetadissplus/xmetadissplus.xsd'
        );

        $this->assertTrue($valid, 'XML Schema validation failed for XMetaDissPlus');

        // Schema validation does not detect problem
        $this->assertNotContains('>"', $this->getResponse()->getBody(), 'XML contains \'"\' after an element.');
    }

}
