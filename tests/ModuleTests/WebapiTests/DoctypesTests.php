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
 * @package    Tests
 * @author     Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @copyright  Copyright (c) 2009, OPUS 4 development team
 * @license    http://www.gnu.org/licenses/gpl.html General Public License
 * @version    $Id$
 */

/**
 * Tests for webapi module doctypes.
 *
 * @category   Application
 * @package    Tests
 *
 * @group    WebapiDoctypesTest
 */
class ModuleTests_WebapiTests_DoctypesTests extends PHPUnit_Framework_TestCase {

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
        $config = Zend_Registry::get('Zend_Config');
        $config = $config->webapi->toArray();
        $this->__restUri = $config['protocol'] . '://' . $config['host'];
        $this->__restUrl = $config['docroot'] . '/' . $config['modul'] . '/doctype';
        $restClient = new Zend_Rest_Client();
        $restClient->setUri($this->__restUri);
        $this->__restClient = $restClient;
    }

    /**
     * Test if a request without a doctyoe returns a list of available types.
     *
     * @return void
     */
    public function testListingOfDocumentTypes() {
        $result = $this->__restClient->restGet($this->__restUrl);

        $xml = new DOMDocument();
        $xml->loadXML($result->getBody());
        $typesList = $xml->getElementsByTagName('TypesList');
        $this->assertEquals(1, $typesList->length, 'Type list should only once in result.');
        $this->assertTrue($typesList->item(0)->hasChildNodes(), 'Type list should not be empty.');
        $typexml = $xml->getElementsByTagName('Type');
        $this->assertGreaterThanOrEqual(1, $typexml->length, 'A type list should contain at least one available type.');
        $this->assertTrue($typexml->item(0)->hasAttribute('xlink:href'), 'A type should have "xlink:href".');
        $this->assertNotNull($typexml->item(0)->nodeValue, 'There should be a name for a type.');
    }

    /**
     * Test if requesting a invalid type returns a error.
     *
     * @return void
     */
    public function testRequestingInvalidType() {
        $result = $this->__restClient->restGet($this->__restUrl . '/IShouldNotExistsOrChuckNorrisWasThere');

        $xml = new DOMDocument();
        $xml->loadXML($result->getBody());
        $error = $xml->getElementsByTagName('Error');
        $this->assertEquals('Requested type is not available!', $error->item(0)->getAttribute('message'), 'Wrong error message returned.');
    }

    /**
     * Test for a good structure of a document type.
     *
     * @return void
     */
    public function testRequestWithValidType() {
        $result = $this->__restClient->restGet($this->__restUrl . '/doctoral_thesis');

        $xml = new DOMDocument();
        $xml->loadXML($result->getBody());
        $document = $xml->getElementsByTagName('Document');
        $this->assertEquals(1, $document->length, 'Result should only contain one document.');
        $this->assertTrue($document->item(0)->hasAttribute('Type'), 'A document type should have a type.');
        $this->assertTrue($document->item(0)->hasChildNodes(), 'A document should not be empty.');
    }
}
