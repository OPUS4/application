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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Collection;
use Opus\CollectionRole;
use Opus\DnbInstitute;
use Opus\Document;
use Opus\Enrichment;
use Opus\File;
use Opus\Identifier;
use Opus\Licence;
use Opus\Person;
use Opus\Series;
use Opus\TitleAbstract;
use Opus\UserRole;

/**
 * TODO unit tests transformations directly without "dispatch"
 * TODO create plugins for formats/protocols/standards
 * TODO test dc:type value for different formats
 * TODO test ListSets values for document type sets
 *
 * @covers Oai_IndexController
 */
class Oai_Format_DcTest extends ControllerTestCase
{

    protected $configModifiable = true;

    protected $additionalResources = ['database', 'view', 'mainMenu'];

    private $_security;
    private $_addOaiModuleAccess;
    private $docIds = [];

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
        'oaiid' => 'http://www.openarchives.org/OAI/2.0/oai-identifier',
        'marc' => 'http://www.loc.gov/MARC21/slim'
    ];

    /**
     * Method to check response for "bad" strings.
     */
    protected function checkForBadStringsInHtml($body)
    {
        $badStrings = [
            "Exception", "Fehler", "Stacktrace", "badVerb", "unauthorized", "internal error", "<error", "</error>"
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
     * Test verb=GetRecord, prefix=oai_dc.
     *
     * @covers ::indexAction
     */
    public function testGetRecordOaiDc()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::35');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Regression test for OPUSVIER-2379
     *
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc91DocType()
    {
        $doc = Document::get(91);
        $this->assertEquals("report", $doc->getType(), "testdata changed");

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::91');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2379 (show doc-type:report)
        $elements = $xpath->query('//oai_dc:dc/dc:type[text()="doc-type:report"]');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected count for doc-type:report"
        );
    }

    /**
     * Regression tests on document 146
     *
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc146()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::146');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2393 (show dc:contributor)
        $elements = $xpath->query('//oai_dc:dc/dc:contributor/text()');
        $this->assertGreaterThanOrEqual(2, $elements->length, 'dc:contributor count changed');
        $this->assertEquals('Doe, Jane (PhD)', $elements->item(0)->nodeValue, 'dc:contributor field changed');
        $this->assertEquals('Baz University', $elements->item(1)->nodeValue, 'dc:contributor field changed');

        // Regression test for OPUSVIER-2393 (show dc:identifier)
        $urnResolverUrl = $this->getConfig()->urn->resolverUrl;
        $elements = $xpath->query('//oai_dc:dc/dc:identifier[text()="' . $urnResolverUrl . 'urn:nbn:op:123"]');
        $this->assertEquals(1, $elements->length, 'dc:identifier URN count changed');

        $elements = $xpath->query('//oai_dc:dc/dc:identifier[text()="123"]');
        $this->assertGreaterThanOrEqual(1, $elements->length, 'dc:identifier URN count changed');
    }

    /**
     * Regression tests on document 91
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc91()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::91');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2393 (show dc:identifier)
        $elements = $xpath->query('//oai_dc:dc/dc:identifier/text()');

        $foundIds = [];
        foreach ($elements as $element) {
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
    public function testGetRecordOaiDcDoc10SubjectDdcAndDate()
    {
        $doc = Document::get(10);
        $ddcs = [];
        foreach ($doc->getCollection() as $c) {
            if ($c->getRoleName() == 'ddc') {
                $ddcs[] = $c->getNumber();
            }
        }
        $this->assertContains("004", $ddcs, "testdata changed");

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::10');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2380 (show <dc:subject>ddc:)
        $elements = $xpath->query('//oai_dc:dc/dc:subject[text()="ddc:004"]');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected count for ddc:004"
        );

        // Regression test for OPUSVIER-2378 (show <dc:date>)
        $elements = $xpath->query('//oai_dc:dc/dc:date');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected count for dc:date"
        );

        // Regression test for OPUSVIER-2378 (show <dc:date>2003)
        $elements = $xpath->query('//oai_dc:dc/dc:date[text()="2003"]');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected count for dc:date"
        );
    }

    /**
     * Regression test for OPUSVIER-2378
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc114DcDate()
    {
        $doc = Document::get(114);
        $completedDate = $doc->getCompletedDate();
        $this->assertEquals("2011-04-19", "$completedDate", "testdata changed");

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::114');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2378 (show <dc:date>)
        $elements = $xpath->query('//oai_dc:dc/dc:date');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected count for dc:date"
        );

        // Regression test for OPUSVIER-2378 (show <dc:date>2011-04-19)
        $elements = $xpath->query('//oai_dc:dc/dc:date[text()="2011-04-19"]');
        $this->assertEquals(
            1,
            $elements->length,
            "Unexpected count for dc:date"
        );
    }

    /**
     * Regression test for OPUSVIER-2454
     * @covers ::indexAction
     */
    public function testGetRecordOaiDcDoc1ByIdentifierPrefixOai()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::1');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
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
    public function testGetRecordOaiDcDoc1ByIdentifierPrefixUrn()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=urn:nbn:de:gbv:830-opus-225');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $badStrings = ["Exception", "Error", "Stacktrace", "badVerb"];
        $this->checkForCustomBadStringsInHtml($response->getBody(), $badStrings);

        $xpath = $this->prepareXpathFromResultString($response->getBody());

        // Regression test for OPUSVIER-2454 (check returned dc:identifier)
        $elements = $xpath->query('//oai_dc:dc/dc:identifier[text()="urn:nbn:de:gbv:830-opus-225"]');
        $this->assertEquals(1, $elements->length, "Expected URN not found");
    }

    public function testGetRecordOaiDcContainsDoi()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::146');

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpathContentContains('//oai_dc:dc/dc:identifier', '123');
    }
}
