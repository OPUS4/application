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
 * @package     Oai_Format
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

    public function testXmlXsiSchemaDeclarationPresentForDcMetadata()
    {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::146');

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpath('//oai_dc:dc');

        $xml = $this->getResponse()->getBody();

        if (preg_match('#<oai_dc:dc.*>#', $xml, $matches)) {
            $startTag = $matches[0];
            $this->assertContains('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', $startTag);
        } else {
            $this->fail('element \'oai_dc:dc\' not found');
        }
    }

    public function testProblemAssertXPathWithMetadataNamespaceAttributes()
    {
        $this->markTestSkipped('Test for documenting OAI namespace testing problem.');

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::146');

        $this->registerXpathNamespaces($this->xpathNamespaces);

        $this->assertXpath('//oai_dc:dc');
        $this->assertXpath('//oai:request[@metadataPrefix]');
        $this->assertXpath('//oai:request[@identifier = "oai::146"]');
        $this->assertXpath('//oai_dc:dc');
        $this->assertXpath('//oai_dc:dc[@xsi:schemaLocation]');

        // TODO cannot assert presence of attributes with namespaces that are only declared in metadata content root
        // $this->assertXpath('//oai_dc:dc[@xmlns:dc]');
        // $this->assertXpath('//oai_dc:dc[@xmlns:dc = "http://www.w3.org/2001/XMLSchema-instance"]');

        // Trying alternative way

        $xml = $this->getResponse()->getBody();

        $xpath = $this->prepareXpathFromResultString($xml);
        $nodes = $xpath->query('//oai_dc:dc');

        // TODO there should be multiple attributes
        $this->assertEquals(1, $nodes->length);

        $element = $nodes->item(0);

        $this->assertEquals(1, $element->attributes->length);

        $attr = $element->attributes->item(0);

        // TODO this is the only namespace used for the metadata, that is declared in the root of the document
        $this->assertEquals('xsi:schemaLocation', $attr->nodeName);

        // TODO apparently the attributes with the "unknown" namespaces in the metadata section get dropped when
        //      parsing the document
    }
}
