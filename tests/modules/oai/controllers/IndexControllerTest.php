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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Oai_IndexControllerTest extends ControllerTestCase {

    private $_security;
    private $_addOaiModuleAccess;

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
        $xpath->registerNamespace('oai_dc', "http://www.openarchives.org/OAI/2.0/oai_dc/");
        $xpath->registerNamespace('dc', "http://purl.org/dc/elements/1.1/");
        $xpath->registerNamespace('pc', "http://www.d-nb.de/standards/pc/");
        $xpath->registerNamespace('xMetaDiss', "http://www.d-nb.de/standards/xmetadissplus/");
        $xpath->registerNamespace('dcterms', "http://purl.org/dc/terms/");
        return $xpath;
    }

    /**
     * Basic test for invalid verbs.
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
     */
    public function testIdentify() {
        $this->dispatch('/oai?verb=Identify');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=ListMetadataFormats.
     */
    public function testListMetadataFormats() {
        $this->dispatch('/oai?verb=ListMetadataFormats');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=ListSets.
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

    public function testGetRecordsFormats() {
        $formatTestDocuments = array(
            'xMetaDiss' => 80,
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
     * Test verb=GetRecord, prefix=xMetaDiss.
     */
    public function testGetRecordxMetaDiss() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDiss&identifier=oai::80');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());

        $this->assertContains('oai::80', $response->getBody(),
                "Response must contain 'oai::80'");
    }

    /**
     * Test verb=GetRecord, prefix=oai_dc.
     */
    public function testGetRecordOaiDc() {
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=oai_dc&identifier=oai::35');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=GetRecord, prefix=XMetaDissPlus.
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
     * Test verb=GetRecord, prefix=xMetaDissPlus.
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
        $this->assertEquals(3, $elements->length,
                "Unexpected dcterms:medium count");

        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:medium[text()="application/pdf"]');
        $this->assertEquals(1, $elements->length,
                "Unexpected dcterms:medium count for application/pdf");

        $elements = $xpath->query('//xMetaDiss:xMetaDiss/dcterms:medium[text()="text/plain"]');
        $this->assertEquals(2, $elements->length,
                "Unexpected dcterms:medium count for text/plain");
    }

    /**
     * Regression test for OPUSVIER-2068
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
     */
    public function testGetRecordXMetaDissPlusDoc146SubjectDDCSG() {
        $doc = new Opus_Document(146);
        $ddcs = array();
        foreach ($doc->getCollection() AS $c) {
            if ($c->getRoleName() == 'ddc') {
                $ddcs[] = $c->getNumber();
            }
        }
        $this->assertContains(28, $ddcs, "testdata changed");
        $this->assertContains(51, $ddcs, "testdata changed");

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
     * Regression test for OPUSVIER-2379
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
     * Regression test for OPUSVIER-2380 and OPUSVIER-2378
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
     * Test verb=ListIdentifiers.
     */
    public function testListIdentifiers() {
        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=oai_dc');
        $this->assertResponseCode(200);

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * Test verb=ListRecords, metadataPrefix=oai_dc.
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
     * Test that proves the bugfix for OPUSVIER-1710 is working as intended.
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

    public function testTransferUrlIsPresent() {
        $doc = new Opus_Document();
        $doc->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('foobar.pdf');
        $doc->addFile($file);
        $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());
        $this->assertResponseCode(200);
        $this->assertContains('<ddb:transfer', $this->getResponse()->getBody());
        $this->assertContains($this->getRequest()->getBaseUrl() . '/oai/container/index/docId/' . $doc->getId() . '</ddb:transfer>', $this->getResponse()->getBody());

        $doc->deletePermanent();
    }

    public function testTransferUrlIsNotPresent() {
        $doc = new Opus_Document();
        $doc->setServerState("published");
        $doc->store();
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());
        $this->assertResponseCode(200);
        $this->assertNotContains('<ddb:transfer ddb:type="dcterms:URI">', $this->getResponse()->getBody());
        $doc->deletePermanent();
    }

    /**
     * Test if the flag "VisibileInOai" affects all files of a document
     */
    public function testDifferentFilesVisibilityOfOneDoc() {

        //create document with two files
        $d = new Opus_Document();
        $d->setServerState('published');

        $f1 = new Opus_File();
        $f1->setPathName('foo.pdf');
        $f1->setVisibleInOai(false);
        $d->addFile($f1);

        $f2 = new Opus_File();
        $f2->setPathName('bar.pdf');
        $f2->setVisibleInOai(false);
        $d->addFile($f2);

        $d->store();
        $id = $d->getId();

        //oai query of that document
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=copy_xml&identifier=oai::' . $id);
        $response = $this->getResponse()->getBody();
        $this->assertContains('<Opus_Document xmlns="" Id="' . $id . '"', $response);
        $this->assertNotContains('<File', $response);
    }

    /**
     * request for metadataPrefix=copy_xml is denied for non-administrative people
     */
    public function testRequestForMetadataPrefixCopyxmlAndVerbGetRecordIsDenied() {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=copy_xml&identifier=oai::80');
        $this->assertContains('<error code="cannotDisseminateFormat">The metadata format &amp;quot;copy_xml&amp;quot; given by metadataPrefix is not supported by the item or this repository.</error>',
                $this->getResponse()->getBody(), 'do not prevent usage of metadataPrefix copy_xml and verb GetRecords');
        $this->resetSecurity();
    }

    public function testRequestForMetadataPrefixCopyxmlAndVerbListRecordIsDenied() {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=copy_xml&from=2100-01-01');
        $this->assertContains('<error code="cannotDisseminateFormat">The metadata format &amp;quot;copy_xml&amp;quot; given by metadataPrefix is not supported by the item or this repository.</error>',
                $this->getResponse()->getBody(), 'do not prevent usage of metadataPrefix copy_xml and verb ListRecords');
        $this->resetSecurity();
    }

    public function testRequestForMetadataPrefixCopyxmlAndVerbListIdentifiersIsDenied() {
        $this->enableSecurity();
        $this->dispatch('/oai?verb=ListIdentifiers&metadataPrefix=copy_xml');
        $this->assertContains('<error code="cannotDisseminateFormat">The metadata format &amp;quot;copy_xml&amp;quot; given by metadataPrefix is not supported by the item or this repository.</error>',
                $this->getResponse()->getBody(), 'do not prevent usage of metadataPrefix copy_xml and verb ListIdentifiers');
        $this->resetSecurity();
    }

    private function enableSecurity() {
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
     */
    public function testDdbFileNumberForSingleDocumentAndSingleFile() {
        $doc = new Opus_Document();
        $doc->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('foobar.pdf');
        $doc->addFile($file);
        $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());
        $this->assertResponseCode(200);
        $this->assertContains('<ddb:fileNumber>1</ddb:fileNumber>', $this->getResponse()->getBody());
        $this->assertContains($this->getRequest()->getBaseUrl() . '/oai/container/index/docId/' . $doc->getId() . '</ddb:transfer>', $this->getResponse()->getBody());

        $doc->deletePermanent();
    }

    /**
     * Regression test for OPUSVIER-2450
     */
    public function testDdbFileNumberForSingleDocumentAndMultipleFiles() {
        $doc = new Opus_Document();
        $doc->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('foo.pdf');
        $doc->addFile($file);
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('bar.pdf');
        $doc->addFile($file);
        $doc->store();

        $this->dispatch('/oai?verb=GetRecord&metadataPrefix=xMetaDissPlus&identifier=oai::' . $doc->getId());
        $this->assertResponseCode(200);
        $this->assertContains('<ddb:fileNumber>2</ddb:fileNumber>', $this->getResponse()->getBody());
        $this->assertContains($this->getRequest()->getBaseUrl() . '/oai/container/index/docId/' . $doc->getId() . '</ddb:transfer>', $this->getResponse()->getBody());

        $doc->deletePermanent();
    }

    /**
     * Regression test for OPUSVIER-2450
     */
    public function testDdbFileNumberForMultipleDocumentsForXMetaDissPlus() {
        $collection = new Opus_Collection(112);

        $doc1 = new Opus_Document();
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
        $doc1->store();

        $doc2 = new Opus_Document();
        $doc2->setServerState('published');
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('baz.pdf');
        $doc2->addFile($file);
        $doc2->addCollection($collection);
        $doc2->store();

        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=xMetaDissPlus&set=ddc:000');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<ddb:fileNumber>2</ddb:fileNumber>', $body);
        $this->assertContains('<ddb:fileNumber>1</ddb:fileNumber>', $body);
        $this->assertNotContains('<ddb:fileNumber>3</ddb:fileNumber>', $body);

        $doc1->deletePermanent();
        $doc2->deletePermanent();
    }

    /**
     * Regression test for OPUSVIER-2450
     */
    public function testDdbFileNumberForMultipleDocumentsForXMetaDiss() {
        $collection = new Opus_Collection(112);

        $doc1 = new Opus_Document();
        $doc1->setServerState('published');
        $doc1->setType('habilitation'); // xMetaDiss liefert nur Doktorarbeiten und Habilitationen aus
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('foo.pdf');
        $doc1->addFile($file);
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('bar.pdf');
        $doc1->addFile($file);
        $doc1->addCollection($collection);
        $doc1->store();

        $doc2 = new Opus_Document();
        $doc2->setServerState('published');
        $doc2->setType('doctoralthesis'); // xMetaDiss liefert nur Doktorarbeiten und Habilitationen aus
        $file = new Opus_File();
        $file->setVisibleInOai(true);
        $file->setPathName('baz.pdf');
        $doc2->addFile($file);
        $doc2->addCollection($collection);
        $doc2->store();

        $this->dispatch('/oai?verb=ListRecords&metadataPrefix=xMetaDiss&set=ddc:000');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<ddb:fileNumber>2</ddb:fileNumber>', $body);
        $this->assertContains('<ddb:fileNumber>1</ddb:fileNumber>', $body);
        $this->assertNotContains('<ddb:fileNumber>3</ddb:fileNumber>', $body);

        $doc1->deletePermanent();
        $doc2->deletePermanent();
    }


}
