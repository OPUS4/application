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
 * Tests for resource person.
 *
 * @group WebapiPersonTest
 */
class Modules_Webapi_PersonTests extends PHPUnit_Framework_TestCase {

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
        $this->__restUrl = $config['docroot'] . '/' . $config['modul'] . '/person';
        $restClient = new Zend_Rest_Client();
        $restClient->setUri($this->__restUri);
        $this->__restClient = $restClient;
    }

    /**
     * Test general structure of returned person data.
     *
     * @return void
     */
    public function testGetPersonData() {
        $restData = $this->__restClient->restGet($this->__restUrl . '/1');
        // check for http status
        $this->assertEquals(200, $restData->getStatus(), 'HTTP status should be 200 (OK).');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getBody());
        // loading of xml works
        $this->assertNotNull($xml, 'DOMDocument should not be null.');
        $data = $xml->getElementsByTagName('Opus_Person');
        // count of Opus_Documents should be one
        $this->assertEquals(1, $data->length, 'DOMDocument should only contain one Opus_Person.');
        $this->assertNotNull($data->item(0));
    }

    /**
     * Test that a invalid id causes a 404 error and a error message
     *
     * @return void
     */
    public function testGetPersonDataWithInvalidId() {
        $restData = $this->__restClient->restGet($this->__restUrl . '/aaa');
        $this->assertEquals(404, $restData->getStatus(), 'HTTP status should be 404 (File not found).');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getBody());
        $error = $xml->getElementsByTagName('Error');
        $this->assertNotNull($error->item(0)->nodeValue, 'Error element should not be empty.');
    }

    /**
     * Test for adding a new person.
     *
     * @return void
     */
    public function testPutPersonData() {
        $putData = '<?xml version="1.0" encoding="utf-8"?>
                    <Opus xmlns:xlink="http://www.w3.org/1999/xlink"><Opus_Person DateOfBirth="2009-01-01" PlaceOfBirth="Dresden" FirstName="Test" LastName="Tester"/></Opus>';

        $restData = $this->__restClient->restPut($this->__restUrl, $putData);

        $this->assertEquals(200, $restData->getStatus(), 'HTTP status should be 200 (OK).');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getRawBody());
        $personTag = $xml->getElementsByTagName('Opus_Person_Id');
        $this->assertEquals(1, $personTag->length);
        $personId = $personTag->item(0)->nodeValue;
        $this->assertNotNull($personId, 'It should be a person id transmitted.');

    }

    /**
     * Test for adding a new person with invalid data.
     *
     * @return void
     */
    public function testPutInvalidPersonData() {

        $putData = '<?xml version="1.0" encoding="utf-8"?>
                    <Opus xmlns:xlink="http://www.w3.org/1999/xlink"><OpusPerson 1stName="Test" 8stName="Tester"/></Opus>';

        $restData = $this->__restClient->restPut($this->__restUrl, $putData);

        $this->assertEquals(402, $restData->getStatus(), 'HTTP status should be 404.');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getRawBody());
        $error = $xml->getElementsByTagName('Error');
        $this->assertNotNull($error->item(0)->nodeValue, 'Error element should not be empty.');
    }

    /**
     * Test for deleting a person.
     *
     * @return void
     */
    public function testDeletePerson() {
        $this->markTestSkipped('Skipped because of hard coding person id.');
        $restData = $this->__restClient->restDelete($this->__restUrl . '/37');
        $this->assertEquals(200, $restData->getStatus(), 'Expected a 200 HTTP response on successful deleting a document.');
    }

    /**
     * Test for deleting a person with an invalid numeric id.
     *
     * @return void
     */
    public function testDeletePersonWithInvalidId() {
        $restData = $this->__restClient->restDelete($this->__restUrl . '/0');
        $this->assertEquals(404, $restData->getStatus(), 'Expected a 400 HTTP response.');
    }

    /**
     * Test for deleting a person with an non-numeric id.
     *
     * @return void
     */
    public function testDeletePersonWithInvalidNonNumericId() {
        $restData = $this->__restClient->restDelete($this->__restUrl . '/add');
        $this->assertEquals(404, $restData->getStatus(), 'Expected a 404 HTTP response.');
    }

    /**
     * Test for updating existing person data.
     *
     * @return void
     */
    public function testPostPersonData() {
        $putData = '<?xml version="1.0" encoding="utf-8"?>
                    <Opus xmlns:xlink="http://www.w3.org/1999/xlink"><Opus_Person PlaceOfBirth="Dresden" FirstName="Test" LastName="Tester"/></Opus>';

        $postData = '<?xml version="1.0" encoding="utf-8"?>
                    <Opus xmlns:xlink="http://www.w3.org/1999/xlink"><Opus_Person PlaceOfBirth="Berlin" FirstName="Test" LastName="Tester"/></Opus>';

        $restData = $this->__restClient->restPut($this->__restUrl, $putData);
        $xml = new DOMDocument();
        $xml->loadXML($restData->getRawBody());
        $personTag = $xml->getElementsByTagName('Opus_Person_Id');
        $this->assertEquals(1, $personTag->length);
        $personId = $personTag->item(0)->nodeValue;

        $restData2 = $this->__restClient->restPost($this->__restUrl . '/' . $personId, $postData);
        $this->assertEquals(200, $restData->getStatus(), 'HTTP status should be 200 (OK).');
        $xml2 = new DOMDocument();
        $xml->loadXML($restData2->getRawBody());
        $personTag = $xml->getElementsByTagName('Opus_Person_Info');
        $this->assertEquals(1, $personTag->length, 'There should be an Opus_Person_Info tag.');
        $updateMessage = $personTag->item(0)->nodeValue;
        $this->assertNotNull($updateMessage, 'There should be an update message');
    }

    /**
     * Test for updating person data with invalid id.
     *
     * @return void
     */
    public function testPostPersonWithInvalidId() {
        $restData = $this->__restClient->restPost($this->__restUrl . '/0', '<emptyString />');
        $this->assertEquals(402, $restData->getStatus(), 'Expected a 402 HTTP response.');
    }
}
