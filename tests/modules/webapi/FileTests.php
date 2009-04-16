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

class Modules_Webapi_FileTests extends PHPUnit_Framework_TestCase {

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
        $this->__restUrl = $config['docroot'] . '/' . $config['modul'] . '/file';
        $restClient = new Zend_Rest_Client();
        $restClient->setUri($this->__restUri);
        $this->__restClient = $restClient;
    }

    /**
     * Test that a invalid id causes a 404 error and a error message
     *
     * @return void
     */
    public function testGetFileWithInvalidId() {
        $restData = $this->__restClient->restGet($this->__restUrl . '/aaa');
        $this->assertEquals(404, $restData->getStatus(), 'HTTP status should be 404 (File not found).');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getBody());
        $error = $xml->getElementsByTagName('Error');
        $this->assertTrue($error->item(0)->hasAttribute('message'), 'Error element should has a message attribute.');
    }

    /**
     * Test if try to get a file listing rise an error
     *
     * @return void
     */
    public function testGetFileListing() {
        $restData = $this->__restClient->restGet($this->__restUrl . '/');
        $this->assertEquals(404, $restData->getStatus(), 'HTTP status should be 404 (File not found).');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getBody());
        $error = $xml->getElementsByTagName('Error');
        $this->assertTrue($error->item(0)->hasAttribute('message'), 'Error element should has a message attribute.');
    }

    /**
     * Test return structure from a valid file resource.
     *
     * @return void
     */
    public function testGetFileData() {
        $restData = $this->__restClient->restGet($this->__restUrl . '/2');
        $this->assertEquals(200, $restData->getStatus(), 'HTTP status should be 200 (OK).');
        $xml = new DOMDocument();
        $xml->loadXML($restData->getBody());
        $file = $xml->getElementsByTagName('Opus_File');

        $this->assertEquals(1, $file->length, 'There should be one Opus_File element.');
        $this->assertTrue($file->item(0)->hasAttribute('PathName'), 'Opus_File should has a PathName attribute.');

        $deliver = $file->item(0)->getElementsByTagName('Deliver');
        $this->assertGreaterThanOrEqual(1, $deliver->length, 'Opus_File should have at least a Deliver child element');
    }
}