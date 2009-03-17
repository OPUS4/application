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
        $this->assertNotNull($restData);
        // check for http status
        $this->assertEquals(200, $restData->getStatus());
        $this->assertNotNull($restData->getBody());
    }

    /**
     * Test if an get request of a special document resource returns document data.
     *
     * @return void
     */
    public function testGetSpecificDocument() {
        $restData = $this->__restClient->restGet($this->__restUrl . '/37');
        // check for http status
        $this->assertEquals(200, $restData->getStatus());
        $xml = new DOMDocument();
        $xml->loadXML($restData->getBody());
        // loading of xml works
        $this->assertNotNull($xml);
        $data = $xml->getElementsByTagName('Opus_Document');
        // count of Opus_Documents should be one
        $this->assertEquals(1, $data->length);
        $this->assertNotNull($data->item(0));
        // look if document has a type
        $this->assertEquals('monograph', $data->item(0)->getAttribute('Type'));
    }

}