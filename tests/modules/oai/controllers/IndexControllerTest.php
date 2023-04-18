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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Collection;
use Opus\Common\CollectionRole;
use Opus\Common\DnbInstitute;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Enrichment;
use Opus\Common\File;
use Opus\Common\Identifier;
use Opus\Common\Licence;
use Opus\Common\Person;
use Opus\Common\Series;
use Opus\Common\TitleAbstract;
use Opus\Common\UserRole;

/**
 * TODO split specific protocol tests into separate classes
 * TODO unit tests transformations directly without "dispatch"
 * TODO create plugins for formats/protocols/standards
 * TODO test dc:type value for different formats
 * TODO test ListSets values for document type sets
 *
 * @covers Oai_IndexController
 */
class Oai_IndexControllerTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string[] */
    protected $additionalResources = ['database', 'view', 'mainMenu'];

    /** @var bool */
    private $addOaiModuleAccess;

    /** @var int[] */
    private $docIds = []; // TODO BUG written, but never read

    /** @var string[] */
    protected $xpathNamespaces = [
        'oai'       => "http://www.openarchives.org/OAI/2.0/",
        'oai_dc'    => "http://www.openarchives.org/OAI/2.0/oai_dc/",
        'cc'        => "http://www.d-nb.de/standards/cc/",
        'dc'        => "http://purl.org/dc/elements/1.1/",
        'ddb'       => "http://www.d-nb.de/standards/ddb/",
        'pc'        => "http://www.d-nb.de/standards/pc/",
        'xMetaDiss' => "http://www.d-nb.de/standards/xmetadissplus/",
        'epicur'    => "urn:nbn:de:1111-2004033116",
        'dcterms'   => "http://purl.org/dc/terms/",
        'thesis'    => "http://www.ndltd.org/standards/metadata/etdms/1.0/",
        'eprints'   => 'http://www.openarchives.org/OAI/1.1/eprints',
        'oaiid'     => 'http://www.openarchives.org/OAI/2.0/oai-identifier',
        'marc'      => 'http://www.loc.gov/MARC21/slim',
    ];

    /**
     * Method to check response for "bad" strings.
     *
     * @param string $body
     */
    protected function checkForBadStringsInHtml($body)
    {
        $badStrings = [
            "Exception",
            "Fehler",
            "Stacktrace",
            "badVerb",
            "unauthorized",
            "internal error",
            "<error",
            "</error>",
        ];
        $this->checkForCustomBadStringsInHtml($body, $badStrings);
    }

    /**
     * Create DOMXPath object and register namespaces.
     *
     * @param string $resultString XML
     * @return DOMXPath Resulting Xpath object with registered namespaces
     */
    protected function prepareXpathFromResultString($resultString)
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($resultString);

        $xpath = new DOMXPath($domDocument);

        foreach ($this->xpathNamespaces as $prefix => $namespaceUri) {
            $xpath->registerNamespace($prefix, $namespaceUri);
        }

        return $xpath;
    }

    /**
     * Basic test for invalid verbs.
     *
     * @covers ::indexAction
     */
    public function testInvalidVerb()
    {
        $this->dispatch('/oai?verb=InvalidVerb');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->assertContains('badVerb', $response->getBody(), "Response must contain 'badVerb'");
    }

    /**
     * Basic test for requests without verb.
     *
     * @covers ::indexAction
     */
    public function testNoVerb()
    {
        $this->dispatch('/oai');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->assertContains('badVerb', $response->getBody(), "Response must contain 'badVerb'");
    }

    /**
     * Test verb=Identify.
     *
     * @covers ::indexAction
     */
    public function testIdentify()
    {
        $this->adjustConfiguration([
            'oai' => ['repository' => ['name' => 'test-repo-name']],
        ]);

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
        $values = [
            'content'          => ['url' => 'test-content-url', 'text' => 'test-content-text'],
            'metadataPolicy'   => ['url' => 'test-metadata-url', 'text' => 'test-metadata-text'],
            'dataPolicy'       => ['url' => 'test-data-url', 'text' => 'test-data-text'],
            'submissionPolicy' => ['url' => 'test-submission-url', 'text' => 'test-submission-text'],
            'comment'          => ['url' => 'test-comment-url', 'text' => 'test-comment-text'],
        ];

        $this->adjustConfiguration([
            'oai' => ['description' => ['eprints' => $values]],
        ]);

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
        $this->adjustConfiguration([
            'oai' => [
                'repository' => ['identifier' => 'test-repo-identifier'],
                'sample'     => ['identifier' => 'test-sample-identifier'],
            ],
        ]);

        $this->dispatch('/oai?verb=Identify');
        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathCount('//oai:description', 2);
        $this->assertXpathCount('//oai:description/oaiid:oai-identifier', 1);
        $this->assertXpathContentContains('//oai:description/oaiid:oai-identifier/oaiid:scheme', 'oai');
        $this->assertXpathContentContains(
            '//oai:description/oaiid:oai-identifier/oaiid:repositoryIdentifier',
            'test-repo-identifier'
        );
        $this->assertXpathContentContains('//oai:description/oaiid:oai-identifier/oaiid:delimiter', ':');
        $this->assertXpathContentContains(
            '//oai:description/oaiid:oai-identifier/oaiid:sampleIdentifier',
            'test-sample-identifier'
        );
    }

    /**
     * Test verb=ListMetadataFormats.
     *
     * @covers ::indexAction
     */
    public function testListMetadataFormats()
    {
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
    public function testListSets()
    {
        $this->dispatch('/oai?verb=ListSets');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());

        // Test "valid" set specs: Non-empty sets in test data
        $assertSets = [
            'doc-type:article',
            'doc-type:preprint',
            'bibliography:true',
            'bibliography:true',
            'ddc:62',
            'msc:65Fxx',
            'pacs:07.07.Df',
        ];
        foreach ($assertSets as $assertSet) {
            $this->assertContains(
                $assertSet,
                $response->getBody(),
                "Response must contain set '$assertSet'"
            );
            $this->assertContains(
                "<setSpec>$assertSet</setSpec>",
                $response->getBody(),
                "Response must contain set '$assertSet'"
            );
        }

        // Test "valid" set specs: Non-existent/empty sets in test data.
        $assertNoSets = ['msc:90C90'];
        foreach ($assertNoSets as $assertNoSet) {
            $this->assertNotContains(
                $assertNoSet,
                $response->getBody(),
                "Response must not contain set '$assertNoSet'"
            );
        }
    }

    /**
     * @covers ::indexAction
     */
    public function testGetRecordsFormats()
    {
        $formatTestDocuments = [
            'xMetaDissPlus' => 41,
            'XMetaDissPlus' => 41,
            'oai_dc'        => 91,
            'oai_pp'        => 91,
            'copy_xml'      => 91,
            'epicur'        => 91,
            'marc21'        => 91,
        ];

        foreach ($formatTestDocuments as $format => $docId) {
            $this->dispatch("/oai?verb=GetRecord&metadataPrefix=$format&identifier=oai::$docId");
            $this->assertResponseCode(200);

            $response   = $this->getResponse();
            $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
            $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

            $this->assertContains(
                "oai::$docId",
                $response->getBody(),
                "Response must contain 'oai::$docId'"
            );

            $xpath = $this->prepareXpathFromResultString($response->getBody());

            $result = $xpath->query('/*[name()="OAI-PMH"]');
            $this->assertEquals(
                1,
                $result->length,
                'Expecting one <OAI-PMH> element'
            );

            $result = $xpath->query('/*[name()="OAI-PMH"]/*[name()="error"]');
            $this->assertEquals(
                0,
                $result->length,
                'Expecting no <OAI-PMH>/<error> element'
            );

            $result = $xpath->query('/*[name()="OAI-PMH"]/*[name()="GetRecord"]');
            $this->assertEquals(
                1,
                $result->length,
                'Expecting one <OAI-PMH>/<GetRecord> element'
            );

            $result = $xpath->query('/*[name()="OAI-PMH"]/*[name()="GetRecord"]/*[name()="record"]');
            $this->assertEquals(
                1,
                $result->length,
                'Expecting one <OAI-PMH>/<GetRecord>/<record> element'
            );
        }
    }

    /**
     * Test verb=GetRecord, prefix=XMetaDissPlus.
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlus()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::41');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->assertContains(
            'oai::41',
            $response->getBody(),
            "Response must contain 'oai::41'"
        );

        $this->assertContains(
            'xMetaDiss',
            $response->getBody(),
            "Response must contain 'xMetaDiss'"
        );
    }

    /**
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusOnlyIfNotInEmbargo()
    {
        $today = date('Y-m-d', time());

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setEmbargoDate($today);
        $docId = $doc->store();

        $this->dispatch("/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::$docId");
        $this->assertResponseCode(200);

        $response   = $this->getResponse()->getBody();
        $badStrings = ["Exception", "Stacktrace", "badVerb"];
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
    public function testGetRecordXMetaDissPlusAlternativeSpelling()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::41');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->assertContains(
            'oai::41',
            $response->getBody(),
            "Response must contain 'oai::41'"
        );

        $this->assertContains(
            'xMetaDiss',
            $response->getBody(),
            "Response must contain 'xMetaDiss'"
        );
    }

    /**
     * Test verb=GetRecord, prefix=XMetaDissPlus.
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusContentDoc41()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::41');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        // Regression test for OPUSVIER-1866
        $assertTitles = ["Dr.", "Prof."];
        foreach ($assertTitles as $title) {
            $testString = "<pc:academicTitle>$title</pc:academicTitle>";
            $this->assertContains(
                $testString,
                $response->getBody(),
                "Response must contain '$testString'"
            );
        }
    }

    /**
     * Test verb=GetRecord, prefix=XMetaDissPlus.
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusContentDoc91()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::91');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        // Regression test for OPUSVIER-1865
        $xpath    = $this->prepareXpathFromResultString($response->getBody());
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dc:creator');
        $this->assertEquals(
            3,
            $elements->length,
            "Unexpected dc:creator count"
        );

        // Regression test for OPUSVIER-2164
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/*/pc:person');
        $this->assertEquals(
            4,
            $elements->length,
            "Unexpected pc:person count"
        );
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/*/pc:person/pc:name');
        $this->assertEquals(
            4,
            $elements->length,
            "Unexpected pc:name count"
        );
    }

    /**
     * Test verb=GetRecord, prefix=XMetaDissPlus.
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusNamespacesDoc91()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::91');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        // Regression test for OPUSVIER-2170, OPUSVIER-2175
        $badNamespaces = [
            'xmlns:dc="http://www.d-nb.de/standards/subject/"',
            'xmlns:dcterms="http://www.d-nb.de/standards/subject/"',
            'xmlns:ddb="http://www.d-nb.de/standards/subject/"',
            'xmlns:ddb1="http://www.d-nb.de/standards/ddb/"',
        ];
        foreach ($badNamespaces as $badNamespace) {
            $this->assertNotContains(
                $badNamespace,
                $response->getBody(),
                "Output contains '$badNamespace', which indicates bad namespaces."
            );
        }
    }

    /**
     * Regression test for OPUSVIER-2193
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc91Dcterms()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::91');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2193
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:medium');
        $this->assertEquals(
            2,
            $elements->length,
            "Unexpected dcterms:medium count"
        );

        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:medium[text()="application/pdf"]');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected dcterms:medium count for application/pdf"
        );

        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:medium[text()="text/plain"]');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected dcterms:medium count for text/plain"
        );
    }

    /**
     * Regression test for OPUSVIER-2068
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc91CheckThesisYearAccepted()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::91');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2068
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:dateAccepted');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected dcterms:dateAccepted count"
        );

        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:dateAccepted[text()="2010-02-26"]');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected dcterms:dateAccepted count"
        );
    }

    /**
     * Regression test for OPUSVIER-1788
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc146SubjectDDC()
    {
        $doc  = Document::get(146);
        $ddcs = [];
        foreach ($doc->getCollection() as $c) {
            if ($c->getRoleName() === 'ddc') {
                $ddcs[] = $c->getNumber();
            }
        }
        $this->assertContains('28', $ddcs, "testdata changed");
        $this->assertContains('51', $ddcs, "testdata changed");

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-1788 (show DDC 51)
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dc:subject[@xsi:type="xMetaDiss:DDC-SG" and text()="51"]');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected count for ddc:51 (should be visible)"
        );

        // Regression test for OPUSVIER-1788 (dont show DDC 28)
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dc:subject[@xsi:type="xMetaDiss:DDC-SG" and text()="28"]');
        $this->assertEquals(
            0,
            $elements->length,
            "Unexpected count for ddc:28 (should be invisible)"
        );
    }

    /**
     * Regression test for OPUSVIER-2068
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc148CheckThesisYearAccepted()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::148');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2068
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:dateAccepted');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected dcterms:dateAccepted count"
        );

        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:dateAccepted[text()="2012"]');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected dcterms:dateAccepted count"
        );
    }

    /**
     * Regression test for OPUSVIER-2448
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc1DdbIdentifier()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::1');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2448 - ddb:identifier with frontdoor url
        $elements = $xpath->query('//xMetaDiss:xMetaDiss/ddb:identifier[@ddb:type="URL"]/text()');
        $this->assertEquals(1, $elements->length, "Unexpected ddb:identifier count");

        $value = $elements->item(0)->nodeValue;
        $this->assertContains(
            "frontdoor/index/index/docId/1",
            $value,
            'expected frontdoor URL in ddb:identifier'
        );
    }

    /**
     * Regression test for OPUSVIER-2452
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc132EmptyThesisGrantor()
    {
        $doc = Document::get(132);
        $this->assertEquals(
            'doctoralthesis',
            $doc->getType(),
            'testdata changed: document type changed'
        );
        $this->assertEquals(
            'published',
            $doc->getServerState(),
            'testdata changed: document state changed'
        );
        $this->assertEquals(
            0,
            count($doc->getThesisGrantor()),
            'testdata changed: thesis grantor added to document'
        );

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::132');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2452 - no thesis:grantor element
        $elements = $xpath->query('//thesis:degree/thesis:grantor');
        $this->assertEquals(0, $elements->length, "Unexpected thesis:grantor count");
    }

    /**
     * Regression test for OPUSVIER-2523
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc132EmptyThesisPublisher()
    {
        $doc = Document::get(132);
        $this->assertEquals(
            'doctoralthesis',
            $doc->getType(),
            'testdata changed: document type changed'
        );
        $this->assertEquals(
            'published',
            $doc->getServerState(),
            'testdata changed: document state changed'
        );
        $this->assertEquals(
            0,
            count($doc->getThesisPublisher()),
            'testdata changed: thesis publisher added to document'
        );

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::132');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2523 - no ddb:contact element
        $elements = $xpath->query('//ddb:contact');
        $this->assertEquals(0, $elements->length, "Unexpected thesis:grantor count");
    }

    /**
     * Regression tests on document 93
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc93()
    {
        $doc = Document::get(93);
        $this->assertEquals(
            'doctoralthesis',
            $doc->getType(),
            'testdata changed: document type changed'
        );
        $this->assertEquals(
            'published',
            $doc->getServerState(),
            'testdata changed: document state changed'
        );
        $this->assertEquals(
            1,
            count($doc->getThesisPublisher()),
            'testdata changed: thesis publisher removed from document'
        );
        $this->assertEquals(
            "",
            $doc->getThesisPublisher(0)->getDnbContactId(),
            'testdata changed: someone added a DnbContactId to thesis publisher '
        );

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::93');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2523 - no ddb:contact element on empty contactId
        $elements = $xpath->query('//ddb:contact');
        $this->assertEquals(0, $elements->length, "Unexpected thesis:grantor count");
    }

    /**
     * Regression test for existing thesis:* and ddb:* elements
     *
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusDoc146ThesisAndDdb()
    {
        $doc = Document::get(146);
        $this->assertEquals(
            'masterthesis',
            $doc->getType(),
            'testdata changed: document type changed'
        );
        $this->assertEquals(
            'published',
            $doc->getServerState(),
            'testdata changed: document state changed'
        );
        $this->assertEquals(
            2,
            count($doc->getThesisGrantor()),
            'testdata changed: thesis grantor added to document'
        );
        $this->assertEquals(
            2,
            count($doc->getThesisPublisher()),
            'testdata changed: thesis publisher added to document'
        );

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

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
    public function testThesisLevelForXMetaDissPlus()
    {
        $thesisLevel = ['diplom' => 'Diplom', 'magister' => 'M.A.', 'examen' => 'other'];
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
     * Regression test for OPUSVIER-2535
     *
     * @covers ::indexAction
     */
    public function testGetRecordWithNonExistingDocumentId()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::12345678');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2535 (check OAI error codes)
        $elements = $xpath->query('//oai:error[@code="idDoesNotExist"]');
        $this->assertEquals(1, $elements->length, "Expecting idDoesNotExist");
    }

    /**
     * Regression test for OPUSVIER-2454
     *
     * @covers ::indexAction
     */
    public function testGetRecordWithInvalidIdentifierPrefix()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=foo::1');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2454 (check OAI error codes)
        $elements = $xpath->query('//oai:error[@code="badArgument"]');
        $this->assertEquals(1, $elements->length, "Expecting badArgument");
    }

    /**
     * Test verb=ListIdentifiers.
     *
     * @covers ::indexAction
     */
    public function testListIdentifiers()
    {
        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=oai_dc');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=ListRecords, metadataPrefix=oai_dc.
     *
     * @covers ::indexAction
     */
    public function testListRecords()
    {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&from=2006-01-01');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->assertContains(
            '<ListRecords>',
            $response->getBody(),
            "Response must contain '<ListRecords>'"
        );
        $this->assertContains(
            '<record>',
            $response->getBody(),
            "Response must contain '<record>'"
        );
    }

    /**
     * Regression Test for OPUSVIER-3142
     *
     * @covers ::indexAction
     */
    public function testListRecordsXMetaDissPlusDocumentsWithFilesOnly()
    {
        $this->adjustConfiguration([
            'oai' => [
                'max' => [
                    'listrecords'     => '100',
                    'listidentifiers' => '200',
                ],
            ],
        ]);

        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=xMetaDissPlus');

        $responseBody = $this->getResponse()->getBody();

        $this->assertNotContains(
            '<ddb:fileNumber>0</ddb:fileNumber>',
            $responseBody,
            "Response must not contain records without files"
        );
    }

    /**
     * TODO Test depends on record without URN in testdata.
     */
    public function testListRecordsXMetaDissPlusDocumentsWithoutUrn()
    {
        $this->adjustConfiguration([
            'oai' => [
                'max' => [
                    'listrecords'     => '100',
                    'listidentifiers' => '200',
                ],
            ],
        ]);

        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=xMetaDissPlus');

        $xpath = $this->prepareXpathFromResultString($this->getResponse()->getBody());

        $elements    = $xpath->query('//xMetaDiss:xMetaDiss[not(contains(., "urn:nbn"))]');
        $recordCount = $elements->length;

        $this->assertTrue($recordCount > 0);
    }

    /**
     * @covers ::indexAction
     */
    public function testListRecordsXMetaDissPlusDocumentsNotInEmbargoOnly()
    {
        $tomorrow  = date('Y-m-d', strtotime('tomorrow'));
        $yesterday = date('Y-m-d', strtotime('yesterday'));

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setEmbargoDate($tomorrow);
        $file = $this->createOpusTestFile('volltext.pdf');
        $doc->addFile($file);
        $docId = $doc->store();

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = $this->createOpusTestFile('volltext2.pdf');
        $doc->addFile($file);
        $visibleId = $doc->store();

        $this->dispatch("/oai?verb=ListRecords&metadataPrefix=xMetaDissPlus&from=$yesterday");

        $body = $this->getResponse()->getBody();

        $this->assertNotContains("oai::$docId", $body, 'Response should not contain embargoed document.');
        $this->assertContains("oai::$visibleId", $body, 'Response should contain document without embargo.');
    }

    /**
     * Regression test for OPUSVIER-3501
     *
     * @covers ::indexAction
     */
    public function testListRecordsXMetaDissPlusSetAndUntilAttributesSetCorrectly()
    {
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
     *
     * @covers ::indexAction
     */
    public function testGetDeletedDocumentReturnsStatusDeleted()
    {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::123');

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
    public function testTransferUrlIsPresent()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = File::new();
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
    public function testTransferUrlIsNotPresent()
    {
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
    public function testGetRecordEpicurUrlEncoding()
    {
        $expectedFileNames = ["'many'  -  spaces  and  quotes.pdf", 'special-chars-%-"-#-&.pdf'];

        $doc       = Document::get(147);
        $fileNames = array_map(function ($f) {
            return $f->getPathName();
        }, $doc->getFile());
        sort($fileNames);

        $this->assertEquals(2, count($fileNames), "testdata changed");
        $this->assertEquals($expectedFileNames, $fileNames, "testdata changed");

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=epicur&identifier=oai::147');
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2444 - url encoding of transfer files.
        $elements = $xpath->query('//epicur:resource/epicur:identifier[@scheme="url"]/text()');
        $this->assertEquals(3, $elements->length, "Unexpected identifier count");

        $fetchedNames = [];
        foreach ($elements as $element) {
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
    public function testDifferentFilesVisibilityOfOneDoc()
    {
        //create document with two files
        $d = $this->createTestDocument();
        $d->setServerState('published');

        $f1 = File::new();
        $f1->setPathName('foo.pdf');
        $f1->setVisibleInOai(false);
        $d->addFile($f1);

        $f2 = File::new();
        $f2->setPathName('bar.pdf');
        $f2->setVisibleInOai(false);
        $d->addFile($f2);

        $this->docIds[] = $d->store();
        $id             = $d->getId();

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
    public function testRequestForMetadataPrefixCopyxmlAndVerbGetRecordIsDenied()
    {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=copy_xml&identifier=oai::80');
        $this->assertContains(
            '<error code="cannotDisseminateFormat">The metadataPrefix \'copy_xml\' is not supported by the item or this repository.</error>',
            $this->getResponse()->getBody(),
            'Usage of metadataPrefix copy_xml and verb GetRecords is not denied'
        );
    }

    /**
     * @covers ::indexAction
     */
    public function testRequestForMetadataPrefixCopyxmlAndVerbListRecordIsDenied()
    {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=copy_xml&from=2100-01-01');
        $this->assertContains(
            '<error code="cannotDisseminateFormat">The metadataPrefix \'copy_xml\' is not supported by the item or this repository.</error>',
            $this->getResponse()->getBody(),
            'Usage of metadataPrefix copy_xml and verb ListRecords is not denied'
        );
    }

    /**
     * @covers ::indexAction
     */
    public function testRequestForMetadataPrefixCopyxmlAndVerbListIdentifiersIsDenied()
    {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=copy_xml');
        $this->assertContains(
            '<error code="cannotDisseminateFormat">The metadataPrefix \'copy_xml\' is not supported by the item or this repository.</error>',
            $this->getResponse()->getBody(),
            'Usage of metadataPrefix copy_xml and verb ListIdentifiers is not denied'
        );
    }

    public function enableSecurity()
    {
        $r = UserRole::fetchByName('guest');

        $modules                  = $r->listAccessModules();
        $this->addOaiModuleAccess = ! in_array('oai', $modules);
        if ($this->addOaiModuleAccess) {
            $r->appendAccessModule('oai');
            $r->store();
        }

        // enable security
        $this->adjustConfiguration(['security' => self::CONFIG_VALUE_TRUE]);
    }

    /**
     * Regression test for OPUSVIER-2450
     *
     * @covers ::indexAction
     */
    public function testDdbFileNumberForSingleDocumentAndSingleFile()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = File::new();
        $file->setVisibleInOai(true);
        $file->setPathName('foobar.pdf');
        $doc->addFile($file);
        $this->docIds[] = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());

        $this->assertResponseCode(200);
        $this->assertContains('<ddb:fileNumber>1</ddb:fileNumber>', $this->getResponse()->getBody());
        $this->assertContains(
            $this->getRequest()->getBaseUrl() . '/oai/container/index/docId/' . $doc->getId() . '</ddb:transfer>',
            $this->getResponse()->getBody()
        );
    }

    /**
     * Regression test for OPUSVIER-2450
     *
     * @covers ::indexAction
     */
    public function testDdbFileNumberForSingleDocumentAndMultipleFiles()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $file = File::new();
        $file->setVisibleInOai(true);
        $file->setPathName('foo.pdf');
        $doc->addFile($file);
        $file = File::new();
        $file->setVisibleInOai(true);
        $file->setPathName('bar.pdf');
        $doc->addFile($file);
        $this->docIds[] = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());

        $this->assertResponseCode(200);
        $this->assertContains('<ddb:fileNumber>2</ddb:fileNumber>', $this->getResponse()->getBody());
        $this->assertContains(
            $this->getRequest()->getBaseUrl() . '/oai/container/index/docId/' . $doc->getId() . '</ddb:transfer>',
            $this->getResponse()->getBody()
        );
    }

    /**
     * Regression test for OPUSVIER-2450
     *
     * @covers ::indexAction
     */
    public function testDdbFileNumberForMultipleDocumentsForXMetaDissPlus()
    {
        $collection = Collection::get(112);

        $doc1 = $this->createTestDocument();
        $doc1->setServerState('published');
        $file = File::new();
        $file->setVisibleInOai(true);
        $file->setPathName('foo.pdf');
        $doc1->addFile($file);
        $file = File::new();
        $file->setVisibleInOai(true);
        $file->setPathName('bar.pdf');
        $doc1->addFile($file);
        $doc1->addCollection($collection);
        $this->docIds[] = $doc1->store();

        $doc2 = $this->createTestDocument();
        $doc2->setServerState('published');
        $file = File::new();
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
    public function testTransferUrlIsIOnlyGivenForDocsWithFulltext()
    {
        $collection = Collection::get(112);

        $doc1 = $this->createTestDocument();
        $doc1->setServerState('published');
        $file = File::new();
        $file->setVisibleInOai(true);
        $file->setPathName('foo.pdf');
        $doc1->addFile($file);
        $file = File::new();
        $file->setVisibleInOai(true);
        $file->setPathName('bar.pdf');
        $doc1->addFile($file);
        $doc1->addCollection($collection);
        $this->docIds[] = $doc1->store();

        $doc2 = $this->createTestDocument();
        $doc2->setServerState('published');
        $file = File::new();
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
        $this->assertContains(
            '<ddb:transfer ddb:type="dcterms:URI">http:///oai/container/index/docId/' . $doc1->getId() . '</ddb:transfer>',
            $body
        );
        $this->assertContains(
            '<ddb:transfer ddb:type="dcterms:URI">http:///oai/container/index/docId/' . $doc2->getId() . '</ddb:transfer>',
            $body
        );
        $this->assertNotContains(
            '<ddb:transfer ddb:type="dcterms:URI">http:///oai/container/index/docId/' . $doc3->getId() . '</ddb:transfer>',
            $body
        );
    }

    /**
     *  Regression Test for OPUSVIER-3072 (was Regression test for OPUSVIER-2509)
     *
     * @covers ::indexAction
     */
    public function testForDDCSubjectTypeForXMetaDissPlus()
    {
        $collection = Collection::get(112);

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->addCollection($collection);

        // fixing test for OPUSVIER-3142
        $visibleFile = File::new();
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
    public function testForInvalidSetSpecsInListRecords()
    {
        $collectionRole = CollectionRole::fetchByOaiName('pacs');
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
    public function testForInvalidSetSpecsInListIdentifiers()
    {
        $collectionRole = CollectionRole::fetchByOaiName('pacs');
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
    public function testForInvalidSetSpecsInGetRecord79()
    {
        $collectionRole = CollectionRole::fetchByOaiName('pacs');
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
    public function testListRecordsForEmptySet()
    {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=open_access');

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $this->assertContains('<error code="noRecordsMatch">', $body);
    }

    /**
     * @covers ::indexAction
     */
    public function testListRecordsForEmptySubset()
    {
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
    public function testXMetaDissPlusUsePersonEnteredUnderGivenNameIfSurnameIsEmpty()
    {
        $document = $this->createTestDocument();
        $document->setServerState('published');

        $author = Person::new();
        $author->setLastName('Foo');
        $author->setDateOfBirth('1900-01-01');
        $author->setPlaceOfBirth('Berlin');
//      $authorId = $author->store();
        $document->addPersonAuthor($author);

        $advisor = Person::new();
        $advisor->setLastName('Bar');
        $advisor->setDateOfBirth('1900-01-01');
        $advisor->setPlaceOfBirth('Berlin');
//      $advisorId = $advisor->store();
        $document->addPersonAdvisor($advisor);

        $referee = Person::new();
        $referee->setLastName('Baz');
        $referee->setDateOfBirth('1900-01-01');
        $referee->setPlaceOfBirth('Berlin');
//      $refereeId = $referee->store();
        $document->addPersonReferee($referee);

        $editor = Person::new();
        $editor->setLastName('TestEditor');
        $document->addPersonEditor($editor);

        $this->docIds[] = $document->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $document->getId());

        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        $authorName = $xpath->query('//xMetaDiss:xMetaDiss/dc:creator/pc:person/pc:name');
        $this->assertEquals(1, $authorName->length);
        $authorFirstName = $xpath->query('//xMetaDiss:xMetaDiss/dc:creator/pc:person/pc:name/pc:foreName');
        $this->assertEquals(0, $authorFirstName->length);

        $authorLastName = $xpath->query('//xMetaDiss:xMetaDiss/dc:creator/pc:person/pc:name/pc:personEnteredUnderGivenName');
        $this->assertEquals(1, $authorLastName->length);

        $advisorName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="advisor"]/pc:person/pc:name');
        $this->assertEquals(1, $advisorName->length);
        $advisorFirstName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="advisor"]/pc:person/pc:name/pc:foreName');
        $this->assertEquals(0, $advisorFirstName->length);
        $advisorLastName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="advisor"]/pc:person/pc:name/pc:personEnteredUnderGivenName');
        $this->assertEquals(1, $advisorLastName->length);

        $refereeName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="referee"]/pc:person/pc:name');
        $this->assertEquals(1, $refereeName->length);
        $refereeFirstName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="referee"]/pc:person/pc:name/pc:foreName');
        $this->assertEquals(0, $refereeFirstName->length);
        $refereeLastName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="referee"]/pc:person/pc:name/pc:personEnteredUnderGivenName');
        $this->assertEquals(1, $refereeLastName->length);

        $editorName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="editor"]/pc:person/pc:name');
        $this->assertEquals(1, $editorName->length);
        $editorFirstName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="editor"]/pc:person/pc:name/pc:foreName');
        $this->assertEquals(0, $editorFirstName->length);
        $editorLastName = $xpath->query('//xMetaDiss:xMetaDiss/dc:contributor[@thesis:role="editor"]/pc:person/pc:name/pc:personEnteredUnderGivenName');
        $this->assertEquals(1, $editorLastName->length);
    }

    /**
     * Regression Test for OPUSVIER-3041
     * (was Regression Test for OPUSVIER-2599, but departments are revived now)
     *
     * @covers ::indexAction
     */
    public function testShowThesisGrantorDepartmentName()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');

        $this->assertResponseCode(200);
        $response = $this->getResponse();

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        $grantorInstitution = $xpath->query(
            '//xMetaDiss:xMetaDiss/thesis:degree/thesis:grantor/cc:universityOrInstitution/cc:name'
        );
//        $this->assertEquals(2, $grantorInstitution->length, "Expected one grantor institution");
        $this->assertEquals('Foobar Universität', $grantorInstitution->item(0)->nodeValue);

        $grantorDepartment = $xpath->query(
            '//xMetaDiss:xMetaDiss/thesis:degree/thesis:grantor/cc:universityOrInstitution/cc:department/cc:name'
        );
        $this->assertEquals('Testwissenschaftliche Fakultät', $grantorDepartment->item(0)->nodeValue);
    }

    /**
     * Regression Test for OPUSVIER-3162
     *
     * @covers ::indexAction
     */
    public function testXMetaDissPlusOutputLanguageCode()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::302');
        $xpath    = $this->prepareXpathFromResultString($this->getResponse()->getBody());
        $language = $xpath->query('//xMetaDiss:xMetaDiss/dc:language')->item(0);
        $this->assertEquals('fre', $language->nodeValue);
    }

    /**
     * XMetaDissPlus Schema validation (see OPUSVIER-3165)
     *
     * @covers ::indexAction
     */
    public function testXMetaDissPlusIsSchemaValid()
    {
        $xmlCatalog = getenv('XML_CATALOG_FILES');
        if (! strpos($xmlCatalog, 'opus4-catalog.xml')) {
            $this->markTestSkipped(
                'Environment Variable XML_CATALOG_FILES not set for resources/opus4-catalog.xml.'
            );
        }

        libxml_clear_errors();
        $useInternalErrors = libxml_use_internal_errors(true);

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');
        $xpath            = $this->prepareXpathFromResultString($this->getResponse()->getBody());
        $xMetaDissNode    = $xpath->query('//xMetaDiss:xMetaDiss')->item(0);
        $metadataDocument = new DOMDocument();
        $importedNode     = $metadataDocument->importNode($xMetaDissNode, true);
        $metadataDocument->appendChild($importedNode);

        $valid = $metadataDocument->schemaValidate(APPLICATION_PATH
            . '/tests/resources/xmetadissplus/xmetadissplus.xsd');

        $this->assertTrue($valid, 'XML Schema validation failed for XMetaDissPlus');
        libxml_use_internal_errors($useInternalErrors);
        libxml_clear_errors();
    }

    /**
     * @covers ::indexAction
     */
    public function testListRecordsWithResumptionToken()
    {
        $maxRecords = '2';

        $this->adjustConfiguration(['oai' => ['max' => ['listrecords' => $maxRecords]]]);

        // first request: fetch documents list and expect resumption code
        $this->dispatch("/oai?verb=ListRecords&metadataPrefix=oai_dc");
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath          = $this->prepareXpathFromResultString($response->getBody());
        $recordElements = $xpath->query('//oai:ListRecords/oai:record');
        $this->assertEquals($maxRecords, $recordElements->length);

        $rsTokenElement = $xpath->query('//oai:ListRecords/oai:resumptionToken[@cursor="0"]');
        $this->assertEquals(1, $rsTokenElement->length, 'foobar');
        $rsToken = $rsTokenElement->item(0)->textContent;
        $this->assertNotEmpty($rsToken);

        // next request: continue document list with resumption token
        $this->resetRequest();
        $this->dispatch("/oai?verb=ListRecords&resumptionToken=$rsToken");
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Stacktrace", "badVerb", "badArgument"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath          = $this->prepareXpathFromResultString($response->getBody());
        $recordElements = $xpath->query('//oai:ListRecords/oai:record');
        $this->assertEquals($maxRecords, $recordElements->length);

        $rsTokenElement = $xpath->query('//oai:ListRecords/oai:resumptionToken[@cursor="' . $maxRecords . '"]');
        $this->assertEquals(1, $rsTokenElement->length, 'foobar');
    }

    /**
     * TODO test requires less than 200 documents in response
     * TODO create test documents on the fly
     */
    public function testListRecordsWithEmptyResumptionTokenForLastBlock()
    {
        $maxRecords = '100';

        $this->adjustConfiguration(['oai' => ['max' => ['listrecords' => $maxRecords]]]);

        // first request: fetch documents list and expect resumption code
        $this->dispatch("/oai?verb=ListRecords&metadataPrefix=oai_dc");
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "badArgument", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath          = $this->prepareXpathFromResultString($response->getBody());
        $recordElements = $xpath->query('//oai:ListRecords/oai:record');
        $this->assertEquals($maxRecords, $recordElements->length);

        $rsTokenElement = $xpath->query('//oai:ListRecords/oai:resumptionToken[@cursor="0"]');
        $this->assertEquals(1, $rsTokenElement->length, 'foobar');
        $rsToken = $rsTokenElement->item(0)->textContent;
        $this->assertNotEmpty($rsToken);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathCount('//oai:ListRecords/oai:resumptionToken', 1);
        $this->assertXpathCount('//oai:ListRecords/oai:resumptionToken[node()]', 1);

        // next request: continue document list with resumption token
        $this->resetRequest();
        $this->dispatch("/oai?verb=ListRecords&resumptionToken=$rsToken");
        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Stacktrace", "badVerb", "badArgument"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath          = $this->prepareXpathFromResultString($response->getBody());
        $recordElements = $xpath->query('//oai:ListRecords/oai:record');
        $this->assertLessThan($maxRecords, $recordElements->length);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathCount('//oai:ListRecords/oai:resumptionToken', 1);
        $this->assertXpathCount('//oai:ListRecords/oai:resumptionToken[not(node())]', 1); // no token
        $this->assertXpathCount('//oai:ListRecords/oai:resumptionToken[not(@*)]', 1); // no attributes
    }

    /**
     * Regression Test for OPUSVIER-2762
     *
     * @covers ::indexAction
     */
    public function testDcCreatorIsAuthorIfExists()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::302');
        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        $dcCreator = $xpath->query('//oai_dc:dc/dc:creator');
        $this->assertEquals(1, $dcCreator->length);
        $this->assertEquals('Author', $dcCreator->item(0)->nodeValue);
    }

    /**
     * Regression Test for OPUSVIER-2762
     *
     * @covers ::indexAction
     */
    public function testDcCreatorIsEditorIfAuthorNotExists()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::303');
        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        $dcCreator = $xpath->query('//oai_dc:dc/dc:creator');
        $this->assertEquals(1, $dcCreator->length);
        $this->assertEquals('Editor', $dcCreator->item(0)->nodeValue);
    }

    /**
     * Regression Test for OPUSVIER-2762
     *
     * @covers ::indexAction
     */
    public function testDcCreatorIsCreatingCorporationIfAuthorAndEditorNotExist()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::304');

        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        $dcCreator = $xpath->query('//oai_dc:dc/dc:creator');
        $this->assertEquals(1, $dcCreator->length);
        $this->assertEquals('CreatingCorporation', $dcCreator->item(0)->nodeValue);
    }

    /**
     * Regression Test for OPUSVIER-2762
     *
     * @covers ::indexAction
     */
    public function testDcCreatorIsOmittedIfNoValidEntrySupplied()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::305');

        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        $dcCreator = $xpath->query('//oai_dc:dc/dc:creator');
        $this->assertEquals(0, $dcCreator->length);
    }

    /**
     * @covers ::indexAction
     */
    public function testDcLangUsesShortest639Code()
    {
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
    public function testHabilitationIsDcTypeDoctoralthesis()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::80');

        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());

        $docType = $xpath->query('//oai_dc:dc/dc:type');
        $values  = $this->nodeListToArray($docType);

        $this->assertContains('doctoralthesis', $values);
        $this->assertContains('doc-type:doctoralThesis', $values);
    }

    /**
     * @covers ::indexAction
     */
    public function testXMetaDissPlusDcsourceContainsTitleParent()
    {
        $doc         = Document::get(146);
        $parentTitle = $doc->getTitleParent();
        $this->assertFalse(empty($parentTitle), 'Test Data modified: Expected TitleParent');

        $parentTitleValue = $parentTitle[0]->getValue();
        $this->assertFalse(
            empty($parentTitleValue),
            'Test Data modified: Expected non-empty value for TitleParent'
        );

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');
        $response = $this->getResponse();
        $xpath    = $this->prepareXpathFromResultString($response->getBody());
        $dcSource = $xpath->query('//xMetaDiss:xMetaDiss/dc:source');

        $this->assertEquals(1, $dcSource->length);
        $this->assertEquals(
            $parentTitleValue . ', '
            . $doc->getVolume() . ', '
            . $doc->getIssue() . ', '
            . 'S. ' . $doc->getPageFirst() . '-' . $doc->getPageLast(),
            $dcSource->item(0)->nodeValue
        );
    }

    /**
     * @covers ::indexAction
     */
    public function testXMetaDissPlusDcsourceContainsTitleParentPageNumber()
    {
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

        $this->assertEquals('TitleParent, '
            . $doc->getVolume() . ', '
            . $doc->getIssue() . ', '
            . $doc->getPageNumber() . ' S.', $dcSource->item(0)->nodeValue);
    }

    /**
     * @covers ::indexAction
     */
    public function testXMetaDissPlusDctermsispartofContainsSeriesTitleAndNumber()
    {
        $doc    = Document::get(146);
        $series = $doc->getSeries();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');
        $response        = $this->getResponse();
        $xpath           = $this->prepareXpathFromResultString($response->getBody());
        $dctermsIspartof = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:isPartOf');

        $this->assertEquals(1, $dctermsIspartof->length);

        $this->assertEquals(
            $series[0]->getTitle() . ' ; ' . $series[0]->getNumber(),
            $dctermsIspartof->item(0)->nodeValue
        );
    }

    /**
     * Mindestanforderungstest für OpenAire 3.0.
     * Document 145 und 146
     * Test verb=ListRecords, metadataPrefix=oai_dc, set=openaire.
     *
     * @covers ::indexAction
     */
    public function testListRecordsForOpenAireCompliance()
    {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=openaire');
        $this->assertResponseCode(200);

        $responseBody = $this->getResponse()->getBody();
        $badStrings   = ["Exception", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($responseBody, $badStrings);

        $this->assertContains(
            '<setSpec>openaire</setSpec>',
            $responseBody,
            'OpenAire requires set-name to be "openaire"'
        );
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
        $this->assertEquals(
            'info:eu-repo/date/embargoEnd/2050-01-01',
            $queryResponse->item(1)->nodeValue,
            "If document is embargoed, <dc:date> should contain embargo date"
        );
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
     * @covers ::indexAction
     */
    public function testListRecordsForOpenAireRelation()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');

        $relation = Enrichment::new();
        $relation->setKeyName('Relation');
        $relation->setValue('test-1234');
        $doc->addEnrichment($relation);

        $relation = Enrichment::new();
        $relation->setKeyName('Relation');
        $relation->setValue('info:eu-repo/grantAgreement/EC/FP7/1234withPrefix');
        $doc->addEnrichment($relation);

        $role     = CollectionRole::fetchByName('openaire');
        $openaire = $role->getCollectionByOaiSubset('openaire');
        $doc->addCollection($openaire);

        $docId = $doc->store();

        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=openaire');
        $this->assertResponseCode(200);

        $responseBody = $this->getResponse()->getBody();
        $badStrings   = ["Exception", "Stacktrace", "badVerb"];
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
     *
     * @covers ::indexAction
     */
    public function testListRecordsForOpenAireComplianceForRecommendedFields()
    {
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=oai_dc&set=openaire');
        $this->assertResponseCode(200);

        $responseBody = $this->getResponse()->getBody();
        $badStrings   = ["Exception", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($responseBody, $badStrings);

        $xpath = $this->prepareXpathFromResultString($responseBody);

        // Language
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:language");
        $values        = $this->nodeListToArray($queryResponse);
        $this->assertContains('deu', $values);

        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:language");
        $values        = $this->nodeListToArray($queryResponse);
        $this->assertContains('deu', $values);

        // Publication Version
        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/146']/dc:type");
        $values        = $this->nodeListToArray($queryResponse);
        $this->assertContains('info:eu-repo/semantics/publishedVersion', $values);

        $queryResponse = $xpath->query("//oai_dc:dc[dc:identifier='http:///frontdoor/index/index/docId/145']/dc:type");
        $values        = $this->nodeListToArray($queryResponse);
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
    public function testXMetaDissPlusForPeriodicalParts()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setType('periodicalpart');
        $series = Series::get(7);
        $doc->addSeries($series)->setNumber('1337');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai:opus4.demo:' . $docId);

        $xpath    = $this->prepareXpathFromResultString($this->getResponse()->getBody());
        $elements = $xpath->query('//dcterms:isPartOf[@xsi:type="ddb:ZSTitelID"]');
        $this->assertEquals($elements->item(0)->nodeValue, 7, 'data contains wrong series id. expected id: 7');
        $elements = $xpath->query('//dcterms:isPartOf[@xsi:type="ddb:ZS-Ausgabe"]');
        $this->assertEquals($elements->item(0)->nodeValue, '1337', 'data contains wrong series number; expected number: 1337');
    }

    /**
     * @covers ::indexAction
     */
    public function testGetRecordXMetaDissPlusLanguageCodes()
    {
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

    /**
     * @param array $nodeList
     * @return array
     */
    protected function nodeListToArray($nodeList)
    {
        $values = [];

        foreach ($nodeList as $node) {
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

        $errorCodes = [
            $elements->item(0)->getAttribute('code'),
            $elements->item(1)->getAttribute('code'),
        ];

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

        libxml_clear_errors();
        $useInternalErrors = libxml_use_internal_errors(true);

        $xpath            = $this->prepareXpathFromResultString($this->getResponse()->getBody());
        $xMetaDissNode    = $xpath->query('//xMetaDiss:xMetaDiss')->item(0);
        $metadataDocument = new DOMDocument();
        $importedNode     = $metadataDocument->importNode($xMetaDissNode, true);
        $metadataDocument->appendChild($importedNode);

        // TODO libxml_use_internal_errors(true);
        $valid = $metadataDocument->schemaValidate(
            APPLICATION_PATH . '/tests/resources/xmetadissplus/xmetadissplus.xsd'
        );

        /* TODO provide functionality for all tests
        $errors = libxml_get_errors();
        foreach($errors as $error) {
            var_dump($error);
        } */

        $this->assertTrue($valid, 'XML Schema validation failed for XMetaDissPlus');

        // Schema validation does not detect problem
        $this->assertNotContains(
            '>"',
            $this->getResponse()->getBody(),
            'XML contains \'"\' after an element.'
        );
        libxml_use_internal_errors($useInternalErrors);
        libxml_clear_errors();
    }

    public function testGetRecordXMetaDissPlusContainsDoi()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('//xMetaDiss:xMetaDiss/dc:identifier', '123');
        $this->assertXpathContentContains('//xMetaDiss:xMetaDiss/ddb:identifier', '10.1007/978-3-540-76406-9');
    }

    public function testGetRecordXMetaDissPlusDcmiType()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=XMetaDissPlus&identifier=oai::146');

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('//xMetaDiss:xMetaDiss/dc:type[@xsi:type = "dcterms:DCMIType"]', 'Text');
    }

    public function testGetRecordMarc21OfDocId91()
    {
        $this->adjustConfiguration([
            'marc21' => [
                'isil'          => 'DE-9999',
                'publisherName' => 'publisherNameFromConfig',
                'publisherCity' => 'publisherCityFromConfig',
            ],
        ]);

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::91');

        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('//marc:leader', '00000nam a22000005  4500');
        $this->assertXpathContentContains('//marc:controlfield[@tag="001"]', 'docId-91');
        $this->assertXpathContentContains('//marc:controlfield[@tag="003"]', 'DE-9999');
        $this->assertXpathContentContains('//marc:datafield[@tag="041"]/marc:subfield[@code="a"]', 'eng');
        $this->assertXpathContentContains('//marc:datafield[@tag="100"]/marc:subfield[@code="a"]', 'Doe, John');
        $this->assertXpathContentContains('//marc:datafield[@tag="245"]/marc:subfield[@code="a"]', 'This is a pdf test document');
        $this->assertXpathContentContains('//marc:datafield[@tag="264"]/marc:subfield[@code="a"]', 'publisherCityFromConfig');
        $this->assertXpathContentContains('//marc:datafield[@tag="264"]/marc:subfield[@code="b"]', 'publisherNameFromConfig');
        $this->assertXpathContentContains('//marc:datafield[@tag="264"]/marc:subfield[@code="c"]', '2010');
        $this->assertXpathContentContains('//marc:datafield[@tag="490"]/marc:subfield[@code="a"]', 'MySeries');
        $this->assertXpathContentContains('//marc:datafield[@tag="490"]/marc:subfield[@code="v"]', '1/5');
        $this->assertXpathContentContains('//marc:datafield[@tag="520"]/marc:subfield[@code="a"]', 'This is a pdf test document');
        $this->assertXpathContentContains('//marc:datafield[@tag="653"]/marc:subfield[@code="a"]', 'Informationssystem');
        $this->assertXpathContentContains('//marc:datafield[@tag="653"]/marc:subfield[@code="a"]', 'eBook');
        $this->assertXpathContentContains('//marc:datafield[@tag="655"]/marc:subfield[@code="a"]', 'report');
        $this->assertXpathContentContains('//marc:datafield[@tag="700"]/marc:subfield[@code="a"]', 'Zufall, Rainer');
        $this->assertXpathContentContains('//marc:datafield[@tag="700"]/marc:subfield[@code="a"]', 'Fall, Klara');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'This is a parent title');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///frontdoor/index/index/docId/91');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///oai/container/index/docId/91');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///files/91/test.pdf');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///files/91/test.txt');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///files/91/frontdoor_invisible.txt');
        $this->assertNotXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///files/91/oai_invisible.txt');
        $this->assertNotXpath('//marc:datafield[@tag="856"]/marc:subfield[@code="z"]');
    }

    public function testGetRecordMarc21OfTestDocOfUnknownType()
    {
        $doc = $this->createTestDocument();
        $doc->setType('unknown');
        $doc->setServerState('published'); // nur freigeschaltete Dokumente können per OAI-PMH abgerufen werden
        $doc->setPublishedYear(2048);
        $doc->setLanguage('deu');
        $doc->setIssue('issue');
        $doc->setVolume('volume');
        $doc->setPageFirst('10');
        $doc->setPageLast('19');
        $doc->setPageNumber('10');
        $doc->setCreatingCorporation('Foo Creating Corp.');

        $identifierUrn = Identifier::new();
        $identifierUrn->setType('urn');
        $identifierUrn->setValue('urn:nbn:de:foo:opus-4711');
        $identifierIssn = Identifier::new();
        $identifierIssn->setType('issn');
        $identifierIssn->setValue('0953-4563');
        $doc->setIdentifier([$identifierUrn, $identifierIssn]);

        $ddc33x = Collection::get(45); // sichtbar
        $ddc334 = Collection::get(402); // unsichtbar
        $ddc34x = Collection::get(46); // sichtbar
        $doc->setCollection([$ddc33x, $ddc334, $ddc34x]);

        $titleMainDeu = TitleAbstract::new();
        $titleMainDeu->setLanguage('deu');
        $titleMainDeu->setType('main');
        $titleMainDeu->setValue('TitleMainInDocumentLanguage');
        $titleMainEng = TitleAbstract::new();
        $titleMainEng->setLanguage('eng');
        $titleMainEng->setType('main');
        $titleMainEng->setValue('TitleMainInOtherLanguage');
        $doc->setTitleMain([$titleMainDeu, $titleMainEng]);

        $titleSubDeu = TitleAbstract::new();
        $titleSubDeu->setLanguage('deu');
        $titleSubDeu->setType('sub');
        $titleSubDeu->setValue('TitleSubInDocumentLanguage');
        $titleSubEng = TitleAbstract::new();
        $titleSubEng->setLanguage('eng');
        $titleSubEng->setType('sub');
        $titleSubEng->setValue('TitleSubInOtherLanguage');
        $doc->setTitleSub([$titleSubDeu, $titleSubEng]);

        $titleParent = TitleAbstract::new();
        $titleParent->setLanguage('deu');
        $titleParent->setType('parent');
        $titleParent->setValue('TitleParentInDocumentLanguage');
        $doc->setTitleParent([$titleParent]);

        $abstractDeu = TitleAbstract::new();
        $abstractDeu->setLanguage('deu');
        $abstractDeu->setType('abstract');
        $abstractDeu->setValue('TitleAbstractInDocumentLanguage');
        $abstractEng = TitleAbstract::new();
        $abstractEng->setLanguage('eng');
        $abstractEng->setType('abstract');
        $abstractEng->setValue('TitleAbstractInOtherLanguage');
        $doc->setTitleAbstract([$abstractEng, $abstractDeu]);

        $doc->setThesisPublisher([DnbInstitute::get(2), DnbInstitute::get(4)]);

        $editor = Person::new();
        $editor->setFirstName('John');
        $editor->setLastName('Doe');
        $doc->addPersonEditor($editor);

        $doc->addSeries(Series::get(1))->setNumber(1);
        $doc->addSeries(Series::get(2))->setNumber(2);
        $doc->addSeries(Series::get(3))->setNumber(3);

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $response   = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('//marc:leader', '00000nam a22000005  4500');
        $this->assertXpathContentContains('//marc:controlfield[@tag="001"]', 'docId-' . $docId);
        $this->assertNotXpath('//marc:controlfield[@tag="003"]');
        $this->assertXpathContentContains('//marc:datafield[@tag="024"]/marc:subfield[@code="a"]', 'urn:nbn:de:foo:opus-4711');
        $this->assertXpathContentContains('//marc:datafield[@tag="041"]/marc:subfield[@code="a"]', 'ger');
        $this->assertXpathContentContains('//marc:datafield[@tag="082"]/marc:subfield[@code="a"]', '33');
        $this->assertXpathContentContains('//marc:datafield[@tag="082"]/marc:subfield[@code="a"]', '34');
        $this->assertNotXpathContentContains('//marc:datafield[@tag="082"]/marc:subfield[@code="a"]', '334');
        $this->assertXpathContentContains('//marc:datafield[@tag="110"]/marc:subfield[@code="a"]', 'Foo Creating Corp.');
        $this->assertXpathContentContains('//marc:datafield[@tag="245"]/marc:subfield[@code="a"]', 'TitleMainInDocumentLanguage');
        $this->assertXpathContentContains('//marc:datafield[@tag="245"]/marc:subfield[@code="b"]', 'TitleSubInDocumentLanguage');
        $this->assertXpathContentContains('//marc:datafield[@tag="246"]/marc:subfield[@code="a"]', 'TitleMainInOtherLanguage');
        $this->assertXpathContentContains('//marc:datafield[@tag="246"]/marc:subfield[@code="b"]', 'TitleSubInOtherLanguage');
        $this->assertXpathContentContains('(//marc:datafield[@tag="264"])[1]/marc:subfield[@code="a"]', 'Musterstadt');
        $this->assertXpathContentContains('(//marc:datafield[@tag="264"])[1]/marc:subfield[@code="b"]', 'Foobar Universitätsbibliothek');
        $this->assertXpathContentContains('(//marc:datafield[@tag="264"])[1]/marc:subfield[@code="c"]', '2048');
        $this->assertXpathContentContains('(//marc:datafield[@tag="264"])[2]/marc:subfield[@code="a"]', 'Universe');
        $this->assertXpathContentContains('(//marc:datafield[@tag="264"])[2]/marc:subfield[@code="b"]', 'School of Life');
        $this->assertNotXpath('(//marc:datafield[@tag="264"])[2]/marc:subfield[@code="c"]'); // Jahresangabe nur beim ersten ThesisPublisher
        $this->assertXpathContentContains('//marc:datafield[@tag="300"]/marc:subfield[@code="a"]', '10');
        $this->assertXpathContentContains('//marc:datafield[@tag="490"]/marc:subfield[@code="a"]', 'MySeries');
        $this->assertXpathContentContains('//marc:datafield[@tag="490"]/marc:subfield[@code="v"]', '1');
        $this->assertXpathContentContains('//marc:datafield[@tag="490"]/marc:subfield[@code="a"]', 'Foobar Series');
        $this->assertXpathContentContains('//marc:datafield[@tag="490"]/marc:subfield[@code="v"]', '2');
        $this->assertNotXpathContentContains('//marc:datafield[@tag="490"]/marc:subfield[@code="a"]', 'Invisible Series');
        $this->assertNotXpathContentContains('//marc:datafield[@tag="490"]/marc:subfield[@code="v"]', '3');
        $this->assertXpathContentContains('(//marc:datafield[@tag="520"])[1]/marc:subfield[@code="a"]', 'TitleAbstractInDocumentLanguage');
        $this->assertXpathContentContains('(//marc:datafield[@tag="520"])[2]/marc:subfield[@code="a"]', 'TitleAbstractInOtherLanguage');
        $this->assertXpathContentContains('//marc:datafield[@tag="655"]/marc:subfield[@code="a"]', 'Other');
        $this->assertXpathContentContains('//marc:datafield[@tag="700"]/marc:subfield[@code="a"]', 'Doe, John');
        $this->assertXpathContentContains('//marc:datafield[@tag="700"]/marc:subfield[@code="4"]', 'edt');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParentInDocumentLanguage');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]', '0953-4563');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Jahrgang volume, Heft issue, Seiten 10-19');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'https://nbn-resolving.org/urn:nbn:de:foo:opus-4711');
        $this->assertXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'http:///frontdoor/index/index/docId/' . $docId);
    }

    public function testGenerationOfField265YearOnly()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="264"]/marc:subfield[@code="a"]');
        $this->assertNotXpath('//marc:datafield[@tag="264"]/marc:subfield[@code="b"]');
        $this->assertXpath('//marc:datafield[@tag="264"]/marc:subfield[@code="c"]');
    }

    public function testGenerationOfField264PublisherNameAndYearOnly()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setPublisherName('publisherName');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="264"]/marc:subfield[@code="a"]');
        $this->assertXpathContentContains('//marc:datafield[@tag="264"]/marc:subfield[@code="b"]', 'publisherName');
        $this->assertXpath('//marc:datafield[@tag="264"]/marc:subfield[@code="c"]');
    }

    public function testGenerationOfField264PublisherPlaceAndYearOnly()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setPublisherPlace('publisherPlace');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('//marc:datafield[@tag="264"]/marc:subfield[@code="a"]', 'publisherPlace');
        $this->assertNotXpath('//marc:datafield[@tag="264"]/marc:subfield[@code="b"]');
        $this->assertXpath('//marc:datafield[@tag="264"]/marc:subfield[@code="c"]');
    }

    public function testGenerationOfField856WithInvisibleInOaiFile()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setPublisherPlace('publisherPlace');

        $f1 = File::new();
        $f1->setPathName('invisible-in-oai.pdf');
        $f1->setVisibleInOai(false);
        $doc->addFile($f1);

        $licencePresent = Licence::get(1);
        $doc->addLicence($licencePresent);

        $licenceMissing = Licence::get(2);
        $doc->addLicence($licenceMissing);

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="x"]', 'Transfer-URL');
        $this->assertNotXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="u"]', 'invisible-in-oai.pdf');
        $this->assertNotXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="z"]', $licenceMissing->getNameLong());
        $this->assertNotXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="z"]', $licencePresent->getNameLong());
    }

    /**
     * TODO test depends on urn.autoCreate being enabled
     */
    public function testGenerationOfField856With2VisibleInOaiFiles()
    {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setPublisherPlace('publisherPlace');

        $f1 = File::new();
        $f1->setPathName('visible-in-oai.pdf');
        $f1->setVisibleInOai(true);
        $doc->addFile($f1);

        $f2 = File::new();
        $f2->setPathName('visible-in-oai.txt');
        $f2->setVisibleInOai(true);
        $doc->addFile($f2);

        $licencePresent = Licence::get(1);
        $doc->addLicence($licencePresent);

        $licenceMissing = Licence::get(2);
        $doc->addLicence($licenceMissing);

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('(//marc:datafield[@tag="856"])[3]/marc:subfield[@code="x"]', 'Transfer-URL');
        $this->assertXpathContentContains('(//marc:datafield[@tag="856"])[3]/marc:subfield[@code="z"]', $licencePresent->getNameLong());
        $this->assertXpathContentContains('(//marc:datafield[@tag="856"])[4]/marc:subfield[@code="u"]', 'visible-in-oai.pdf');
        $this->assertXpathContentContains('(//marc:datafield[@tag="856"])[4]/marc:subfield[@code="z"]', $licencePresent->getNameLong());
        $this->assertXpathContentContains('(//marc:datafield[@tag="856"])[5]/marc:subfield[@code="u"]', 'visible-in-oai.txt');
        $this->assertXpathContentContains('(//marc:datafield[@tag="856"])[5]/marc:subfield[@code="z"]', $licencePresent->getNameLong());

        $this->assertNotXpathContentContains('//marc:datafield[@tag="856"]/marc:subfield[@code="z"]', $licenceMissing->getNameLong());
    }

    public function testGenerationOfField773WithSingleTitleParent()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');

        $this->addTitleParent($doc, 'deu', 'TitleParent');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParent');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField773WithSingleTitleParentAndVolume()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->setVolume('volume1');

        $this->addTitleParent($doc, 'deu', 'TitleParent');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParent');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Jahrgang volume');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField773WithSingleTitleParentAndVolumeAndIssue()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->setVolume('volume');
        $doc->setIssue('issue');

        $this->addTitleParent($doc, 'deu', 'TitleParent');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParent');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Jahrgang volume, Heft issue');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField773WithSingleTitleParentAndVolumeAndPages()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->setVolume('volume');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParent');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParent');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Jahrgang volume, Seiten 1-2');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField773WithSingleTitleParentAndVolumeAndIssueAndPages()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->setVolume('volume');
        $doc->setIssue('issue');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParent');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParent');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Jahrgang volume, Heft issue, Seiten 1-2');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField773WithSingleTitleParentAndIssue()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->setIssue('issue');

        $this->addTitleParent($doc, 'deu', 'TitleParent');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParent');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Heft issue');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField773WithSingleTitleParentAndIssueAndPages()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->setIssue('issue');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParent');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParent');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Heft issue, Seiten 1-2');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField773WithSingleTitleParentAndPages()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParent');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParent');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Seiten 1-2');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField773WithSingleTitleParentAndPageFirst()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->setPageFirst('1');

        $this->addTitleParent($doc, 'deu', 'TitleParent');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParent');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField773WithSingleTitleParentAndPageLast()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParent');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParent');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField022WithSingleIssn()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');

        $this->addIdentifier($doc, '1234-5678', 'issn');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('//marc:datafield[@tag="022"]/marc:subfield[@code="a"]', '1234-5678');
        $this->assertXpathCount('//marc:datafield[@tag="022"]', 1);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]');
    }

    public function testGenerationOfField022WithMultipleIssns()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');

        $this->addIdentifier($doc, '1234-5678', 'issn');
        $this->addIdentifier($doc, '1234-6789', 'issn');
        $this->addIdentifier($doc, '1234-7890', 'issn');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('(//marc:datafield[@tag="022"])[1]/marc:subfield[@code="a"]', '1234-5678');
        $this->assertXpathContentContains('(//marc:datafield[@tag="022"])[2]/marc:subfield[@code="a"]', '1234-6789');
        $this->assertXpathContentContains('(//marc:datafield[@tag="022"])[3]/marc:subfield[@code="a"]', '1234-7890');
        $this->assertXpathCount('//marc:datafield[@tag="022"]', 3);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]');
    }

    public function testGenerationOfField020WithSingleIsbn()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');

        $this->addIdentifier($doc, '978-3-012345678', 'isbn');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('//marc:datafield[@tag="020"]/marc:subfield[@code="a"]', '978-3-012345678');
        $this->assertXpathCount('//marc:datafield[@tag="020"]', 1);

        $this->assertNotXpath('//marc:datafield[@tag="022"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]');
    }

    public function testGenerationOfField020WithMultipleIsbns()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');

        $this->addIdentifier($doc, '978-3-012345678', 'isbn');
        $this->addIdentifier($doc, '978-3-123456789', 'isbn');
        $this->addIdentifier($doc, '978-3-234567890', 'isbn');
        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('(//marc:datafield[@tag="020"])[1]/marc:subfield[@code="a"]', '978-3-012345678');
        $this->assertXpathContentContains('(//marc:datafield[@tag="020"])[2]/marc:subfield[@code="a"]', '978-3-123456789');
        $this->assertXpathContentContains('(//marc:datafield[@tag="020"])[3]/marc:subfield[@code="a"]', '978-3-234567890');
        $this->assertXpathCount('//marc:datafield[@tag="020"]', 3);

        $this->assertNotXpath('//marc:datafield[@tag="022"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]');
    }

    public function testGenerationOfField773WithVolumeIssuePages()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->setVolume('volume');
        $doc->setIssue('issue');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Jahrgang volume, Heft issue, Seiten 1-2');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
    }

    public function testGenerationOfField773WithMultipleTitleParentAndVolumeIssuePagesAndIssnAndIsbn()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->addVolume('volume');
        $doc->addIssue('issue');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParentDeu');
        $this->addTitleParent($doc, 'eng', 'TitleParentEng');

        $this->addIdentifier($doc, '1234-5678', 'issn');
        $this->addIdentifier($doc, '1234-6789', 'issn');
        $this->addIdentifier($doc, '978-3-012345678', 'isbn');
        $this->addIdentifier($doc, '978-3-123456789', 'isbn');

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathCount('//marc:datafield[@tag="773"]', 7);
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="g"]', 'Jahrgang volume, Heft issue, Seiten 1-2');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[2]/marc:subfield[@code="t"]', 'TitleParentDeu');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[3]/marc:subfield[@code="t"]', 'TitleParentEng');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[4]/marc:subfield[@code="x"]', '1234-5678');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[5]/marc:subfield[@code="x"]', '1234-6789');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[6]/marc:subfield[@code="z"]', '978-3-012345678');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[7]/marc:subfield[@code="z"]', '978-3-123456789');
    }

    /**
     * In diesem Fall wird genau ein 773-Feld erzeugt.
     */
    public function testGenerationOfField773WithSingleTitleParentAndVolumeIssuePagesAndIssn()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->addVolume('volume');
        $doc->addIssue('issue');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParentDeu');

        $this->addIdentifier($doc, '1234-5678', 'issn');

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Jahrgang volume, Heft issue, Seiten 1-2');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParentDeu');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]', '1234-5678');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]');
    }

    /**
     * In diesem Fall wird jede ISSN in ein eigenes 773-Feld geschrieben.
     */
    public function testGenerationOfField773WithSingleTitleParentAndVolumeIssuePagesAndMultipleIssns()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->addVolume('volume');
        $doc->addIssue('issue');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParentDeu');

        $this->addIdentifier($doc, '1234-5678', 'issn');
        $this->addIdentifier($doc, '1234-6789', 'issn');

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathCount('//marc:datafield[@tag="773"]', 3);

        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="g"]', 'Jahrgang volume, Heft issue, Seiten 1-2');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="t"]', 'TitleParentDeu');
        $this->assertNotXpath('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="x"]');
        $this->assertNotXpath('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="z"]');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[2]/marc:subfield[@code="x"]', '1234-5678');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[3]/marc:subfield[@code="x"]', '1234-6789');
    }

    /**
     * In diesem Fall wird genau ein 773-Feld erzeugt.
     */
    public function testGenerationOfField773WithSingleTitleParentAndVolumeIssuePagesAndIsbn()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->addVolume('volume');
        $doc->addIssue('issue');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParentDeu');

        $this->addIdentifier($doc, '978-3-012345678', 'isbn');

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);

        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Jahrgang volume, Heft issue, Seiten 1-2');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="t"]', 'TitleParentDeu');
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="z"]', '978-3-012345678');
        $this->assertNotXpath('//marc:datafield[@tag="773"]/marc:subfield[@code="x"]');
    }

    /**
     * In diesem Fall wird jede ISBN in ein eigenes 773-Feld geschrieben.
     */
    public function testGenerationOfField773WithSingleTitleParentAndVolumeIssuePagesAndMultipleIsbns()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->addVolume('volume');
        $doc->addIssue('issue');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParentDeu');

        $this->addIdentifier($doc, '978-3-012345678', 'isbn');
        $this->addIdentifier($doc, '978-3-123456789', 'isbn');

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathCount('//marc:datafield[@tag="773"]', 3);

        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="g"]', 'Jahrgang volume, Heft issue, Seiten 1-2');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="t"]', 'TitleParentDeu');
        $this->assertNotXpath('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="x"]');
        $this->assertNotXpath('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="z"]');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[2]/marc:subfield[@code="z"]', '978-3-012345678');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[3]/marc:subfield[@code="z"]', '978-3-123456789');
    }

    /**
     * In diesem Fall sollen ISSN und ISBN jeweils in ein eigenes 773-Feld. TitleParent und Volume, Issue, Pages
     * sollen dagegen zusammen in ein 773-Feld.
     */
    public function testGenerationOfField773WithSingleTitleParentAndVolumeIssuePagesAndIssnAndIsbn()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->addVolume('volume');
        $doc->addIssue('issue');
        $doc->setPageFirst('1');
        $doc->setPageLast('2');

        $this->addTitleParent($doc, 'deu', 'TitleParentDeu');

        $this->addIdentifier($doc, '1234-5678', 'issn');
        $this->addIdentifier($doc, '978-3-012345678', 'isbn');

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');

        $this->assertXpathCount('//marc:datafield[@tag="773"]', 3);

        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="g"]', 'Jahrgang volume, Heft issue, Seiten 1-2');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="t"]', 'TitleParentDeu');
        $this->assertNotXpath('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="x"]');
        $this->assertNotXpath('(//marc:datafield[@tag="773"])[1]/marc:subfield[@code="z"]');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[2]/marc:subfield[@code="x"]', '1234-5678');
        $this->assertXpathContentContains('(//marc:datafield[@tag="773"])[3]/marc:subfield[@code="z"]', '978-3-012345678');
    }

    /**
     * kein TitleParent: ISSN soll in Feld 022; Volume in Feld 773
     */
    public function testGenerationOfField773WithVolumeAndIssn()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->addVolume('volume');

        $this->addIdentifier($doc, '1234-5678', 'issn');

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('//marc:datafield[@tag="022"]//marc:subfield[@code="a"]', '1234-5678');
        $this->assertNotXpath('//marc:datafield[@tag="020"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Jahrgang volume');
    }

    /**
     * kein TitleParent: ISBN soll in Feld 020; Volume in Feld 773
     */
    public function testGenerationOfField773WithVolumeAndIsbn()
    {
        $doc = $this->createTestDocument();
        $doc->setLanguage('deu');
        $doc->setServerState('published');
        $doc->addVolume('volume');

        $this->addIdentifier($doc, '978-3-012345678', 'isbn');

        $docId = $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=marc21&identifier=oai::' . $docId);

        $this->assertResponseCode(200);

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('//marc:datafield[@tag="020"]//marc:subfield[@code="a"]', '978-3-012345678');
        $this->assertNotXpath('//marc:datafield[@tag="022"]');
        $this->assertXpathCount('//marc:datafield[@tag="773"]', 1);
        $this->assertXpathContentContains('//marc:datafield[@tag="773"]/marc:subfield[@code="g"]', 'Jahrgang volume');
    }

    /**
     * Helper function for adding title parent to given document.
     *
     * @param DocumentInterface $doc
     * @param string            $language
     * @param string            $value
     */
    private function addTitleParent($doc, $language, $value)
    {
        $titleParent = TitleAbstract::new();
        $titleParent->setType('parent');
        $titleParent->setLanguage($language);
        $titleParent->setValue($value);

        $doc->addTitleParent($titleParent);
    }

    /**
     * Helper function for adding identifier of given type to given document.
     *
     * @param DocumentInterface $doc
     * @param string            $value
     * @param string            $type
     */
    private function addIdentifier($doc, $value, $type)
    {
        $identifier = Identifier::new();
        $identifier->setType($type);
        $identifier->setValue($value);

        $doc->addIdentifier($identifier);
    }

    /**
     * @return array[]
     */
    public function metadataPrefixProvider()
    {
        return [
            ['MARC21'],
            ['marc21'],
            ['mArC21'],
        ];
    }

    /**
     * @param string $metadataPrefix
     * @dataProvider metadataPrefixProvider
     */
    public function testMetadataPrefixCaseInsensitive($metadataPrefix)
    {
        $this->dispatch("/oai?verb=ListRecords&metadataPrefix=$metadataPrefix");
        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $this->checkForCustomBadStringsInHtml($body, ["Exception", "Stacktrace", "badVerb"]);

        $this->assertContains(
            '<ListRecords>',
            $body,
            "Response must contain '<ListRecords>'"
        );
        $this->assertContains(
            '<record>',
            $body,
            "Response must contain '<record>'"
        );

        // TODO check that metadata is generated
        $this->assertNotContains(
            '<metadata/>',
            $body,
            'Response must not contains empty \'<metadata/>\' elements.'
        );
    }
}
