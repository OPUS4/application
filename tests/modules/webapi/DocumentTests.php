<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @category   Application
 * @package    Tests_Module_Webapi
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

require_once 'PHPUnit/Framework.php';

require_once 'Zend/Config/Ini.php';
require_once 'Zend/Rest/Client.php';

/**
 * Tests for document webapi.
 *
 * @group WebapiDocumentTest
 */
class Modules_Webapi_DocumentTests extends PHPUnit_Framework_TestCase {

    /**
     * Holds uri location for tests. Should be configurable.
     *
     * @var string
     */
    private $__restUri = '';

    /**
     * Holds url information like doc_root and module name.
     *
     * @var string
     */
    private $__restUrl = '';

    /**
     * Holds rest client.
     *
     * @var Zend_Rest_Client
     */
    private $__restClient = null;

    /**
     * Do some initial stuff.
     *
     * @return void
     */
    protected function setUp() {
        $configfile = realpath(dirname(dirname(__FILE)) . '/config.ini');
        $config = new Zend_Config_Ini($configfile, 'webapi');
        $config = $config->toArray();
        $this->__restUri = $config['protocol'] . '://' . $config['host'];
        $this->__restUrl = $config['docroot'] . '/' . $config['modul'] . '/document';
        $restClient = new Zend_Rest_Client();
        $restClient->setUri($this->__restUri);
        $this->__restClient = $restClient;
    }

    /**
     * Test if an get request on a document resource returns a list of all documents.
     *
     * @return void
     */
    public function testGetDocumentList() {
        $restData = $this->__restClient->restGet($this->__restUrl);
        $this->assertNotNull($restData, 'REST get return noting.');
        // check for http status
        $this->assertEquals(200, $restData->getStatus(), 'HTTP status should be 200 (OK).');
        $this->assertNotNull($restData->getBody(), 'HTTP body contain no value.');
    }

    /**
     * Test if an get request of a special document resource returns document data.
     *
     * @return void
     */
    public function testGetSpecificDocument() {
        $restData = $this->__restClient->restGet($this->__restUrl . '/37');
        // check for http status
        $this->assertEquals(200, $restData->getStatus(), 'HTTP status should be 200 (OK).');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getBody());
        // loading of xml works
        $this->assertNotNull($xml, 'DOMDocument should not be null.');
        $data = $xml->getElementsByTagName('Opus_Document');
        // count of Opus_Documents should be one
        $this->assertEquals(1, $data->length, 'DOMDocument should only contain one Opus_Document.');
        $this->assertNotNull($data->item(0));
        // look if document has a type
        // skip following assert because it depends too much on test data
        //$this->assertEquals('report', $data->item(0)->getAttribute('Type'), 'Type of this Opus_Document should be report.');
    }

    /**
     * Test if an invalid numeric id causes a 404 error.
     *
     * @return void
     */
    public function testGetDocumentWithInvalidId() {
        $restData = $this->__restClient->restGet($this->__restUrl . '/100000');
        $this->assertEquals(404, $restData->getStatus(), 'HTTP status should be 404 (File not found).');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getBody());
        $error = $xml->getElementsByTagName('Error');
        $this->assertTrue($error->item(0)->hasAttribute('message'), 'Error element contain no error message.');
    }

    /**
     * Test to delete a valid document.
     *
     * @return void
     */
    public function testDeleteDocument() {
        $this->markTestSkipped('Skipped because of hard coding document id.');
        $restData = $this->__restClient->restDelete($this->__restUrl . '/37');
        $this->assertEquals(204, $restData->getStatus(), 'Expected a 204 HTTP response on deleting a document.');
    }

    /**
     * Test deleting of a document with an invalid id.
     *
     * @return void
     */
    public function testDeleteDocumentWithInvalidNumericId() {
        $restData = $this->__restClient->restDelete($this->__restUrl . '/0');
        $this->assertEquals(404, $restData->getStatus(), 'Expected a 400 HTTP response.');
    }

    /**
     * Test deleting of a document with an non numeric id.
     *
     * @return void
     */
    public function testDeleteDocumentWithInvalidNonNumericId() {
        $restData = $this->__restClient->restDelete($this->__restUrl . '/add');
        $this->assertEquals(404, $restData->getStatus(), 'Expected a 404 HTTP response.');
    }

    /**
     * Test if adding a document works (correct reponse code and document id).
     *
     * @return void
     */
    public function testPutDocumentData() {
        $putData = '<?xml version="1.0" encoding="utf-8"?>
                    <Opus xmlns:xlink="http://www.w3.org/1999/xlink">
                    <Opus_Document Language="de" Source="Der ultimative Test." CompletedYear="2009" DateAccepted="2009-05-06" Type="doctoral_thesis">
                        <PersonAuthor FirstName="Test" LastName="Test0r" Role="author"/>
                        <SubjectSwd Language="ger" Type="swd" Value="Test, testen, getestet"/>
                        <SubjectUncontrolled Language="eng" Type="uncontrolled" Value="test, test, test"/>
                        <SubjectUncontrolled Language="ger" Type="uncontrolled" Value="test, testen, getestet"/>
                        <TitleAbstract Language="ger" Value="Ein erster Test fuer das Hinzufuegen von Dokumentdaten ueber die Webapi."/>
                        <TitleAbstract Language="eng" Value="A first test for adding document data over webapi."/>
                        <TitleMain Language="ger" Value="Webapi Test - Hinzufügen eines Dokuments"/>
                        <TitleMain Language="eng" Value="Webapi Test - put document data"/>
                    </Opus_Document>
                    </Opus>';

        $restData = $this->__restClient->restPut($this->__restUrl, $putData);

        $this->assertEquals(200, $restData->getStatus(), 'HTTP status should be 200 (OK).');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getRawBody());
        $documentTag = $xml->getElementsByTagName('Opus_Document_Id');
        $this->assertEquals(1, $documentTag->length);
        $documentId = $documentTag->item(0)->nodeValue;
        $this->assertNotNull($documentId, 'It should be a document id transmitted.');
    }

    /**
     * Test if invalid data causes a 402 HTTP response code and a not empty error message.
     *
     * @return void
     */
    public function testPutDocumentWithInvalidData() {
        $putData = '<?xml version="1.0" encoding="utf-8"?>
                    <Opus xmlns:xlink="http://www.w3.org/1999/xlink">
                    <Opus_Document Language="de" Source="Der ultimative Test." CompletedYear="2009" DateAccepted="2009-05-06" Type="doctoral_thesis">
                        <PersonAuthor FirstName="Test" LastName="Test0r" Role="author"/>
                        <SubjectSwd Language="ger" Type="swd" Value="Test, testen, getestet"/>
                        <SubjectUncontrolled Language="eng" Type="uncontrolled" Value="test, test, test"/>
                        <SubjectUncontrolled Language="ger" Type="uncontrolled" Value="test, testen, getestet"/>
                        <TitleAbstract Language="ger" Value="Ein erster Test fuer das Hinzufuegen von Dokumentdaten ueber die Webapi."/>
                        <TitleAbstract Language="eng" Value="A first test for adding document data over webapi."/>
                        <TitleMain Language="ger" Value="Webapi Test - Hinzufügen eines Dokuments"/>
                        <TitleMain Language="eng" Value="Webapi Test - put document data">
                    </Opus_Document>
                    </Opus>';

        $restData = $this->__restClient->restPut($this->__restUrl, $putData);

        $this->assertEquals(402, $restData->getStatus(), 'HTTP status should be 200 (OK).');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getRawBody());
        $error = $xml->getElementsByTagName('Error');
        $this->assertNotNull($error->item(0)->nodeValue, 'Error element should not be empty.');
    }

    /**
     * Test if updating of a document works.
     *
     * @return void
     */
    public function testPostDocument() {
        $putData = '<?xml version="1.0" encoding="utf-8"?>
                    <Opus xmlns:xlink="http://www.w3.org/1999/xlink">
                    <Opus_Document Language="de" Source="Der ultimative Test." CompletedYear="2009" DateAccepted="2009-05-06" Type="doctoral_thesis">
                        <PersonAuthor FirstName="Test" LastName="Test0r" Role="author"/>
                        <SubjectSwd Language="ger" Type="swd" Value="Test, testen, getestet"/>
                        <SubjectUncontrolled Language="eng" Type="uncontrolled" Value="test, test, test"/>
                        <SubjectUncontrolled Language="ger" Type="uncontrolled" Value="test, testen, getestet"/>
                        <TitleAbstract Language="ger" Value="Ein erster Test fuer das Hinzufuegen von Dokumentdaten ueber die Webapi."/>
                        <TitleAbstract Language="eng" Value="A first test for adding document data over webapi."/>
                        <TitleMain Language="ger" Value="Webapi Test - Hinzufügen eines Dokuments"/>
                        <TitleMain Language="eng" Value="Webapi Test - put document data"/>
                    </Opus_Document>
                    </Opus>';

        $postData = '<?xml version="1.0" encoding="utf-8"?>
                    <Opus xmlns:xlink="http://www.w3.org/1999/xlink">
                    <Opus_Document Language="de" Source="Der ultimative Aktualisierungsest." CompletedYear="2008" DateAccepted="2009-05-06" Type="doctoral_thesis">
                        <PersonAuthor FirstName="Test" LastName="Test0r" Role="author"/>
                        <SubjectSwd Language="ger" Type="swd" Value="Test, testen, getestet"/>
                        <SubjectUncontrolled Language="eng" Type="uncontrolled" Value="test, test, test, update"/>
                        <SubjectUncontrolled Language="ger" Type="uncontrolled" Value="test, testen, getestet, aktualisieren"/>
                        <TitleAbstract Language="ger" Value="Ein erster AktualisierungsTest fuer das Hinzufuegen von Dokumentdaten ueber die Webapi."/>
                        <TitleAbstract Language="eng" Value="A first test for updating document data over webapi."/>
                        <TitleMain Language="ger" Value="Webapi Test - Hinzufügen eines Dokuments"/>
                        <TitleMain Language="eng" Value="Webapi Test - put document data"/>
                    </Opus_Document>
                    </Opus>';

        $restData = $this->__restClient->restPut($this->__restUrl, $putData);
        $xml = new DOMDocument();
        $xml->loadXML($restData->getRawBody());
        $documentTag = $xml->getElementsByTagName('Opus_Document_Id');
        $this->assertEquals(1, $documentTag->length, 'There should be only one document returned.');
        $documentId = $documentTag->item(0)->nodeValue;

        $restData2 = $this->__restClient->restPost($this->__restUrl . '/' . $documentId, $postData);
        $this->assertEquals(200, $restData->getStatus(), 'HTTP status should be 200 (OK).');
        $xml2 = new DOMDocument();

        $xml->loadXML($restData2->getRawBody());
        $documentTag = $xml->getElementsByTagName('Opus_Document_Info');
        $this->assertEquals(1, $documentTag->length, 'There should be an Opus_Document_Info tag.');
        $updateMessage = $documentTag->item(0)->nodeValue;
        $this->assertNotNull($updateMessage, 'There should be an update message');

    }
}
