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
        $this->assertEquals('report', $data->item(0)->getAttribute('Type'), 'Type of this Opus_Document should be monograph.');
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
        $this->assertEquals(200, $restData->getStatus(), 'Expected a 200 HTTP response on deleting a document.');
    }

    /**
     * Test deleting of a document with an invalid id.
     *
     * @return void
     */
    public function testDeleteDocumentWithInvalidNumericId() {
        $restData = $this->__restClient->restDelete($this->__restUrl . '/1');
        $this->assertEquals(400, $restData->getStatus(), 'Expected a 400 HTTP response.');
    }

    /**
     * Test deleting of a document with an non numeric id.
     *
     * @return void
     */
    public function testDeleteDocumentWithInvalidNonNumericId() {
        $restData = $this->__restClient->restDelete($this->__restUrl);
        $this->assertEquals(404, $restData->getStatus(), 'Expected a 404 HTTP response.');
    }

}
