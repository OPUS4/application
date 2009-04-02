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
 * Test websearch api.
 *
 * @group WebapiSearchTest
 */
class Modules_Webapi_SearchTests extends PHPUnit_Framework_TestCase {

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
        $this->__restUrl = $config['docroot'] . '/' . $config['modul'] . '/search';
        $restClient = new Zend_Rest_Client();
        $restClient->setUri($this->__restUri);
        $this->__restClient = $restClient;
    }

    /**
     * Test for a good search.
     *
     * @return void
     */
    public function testSearchWithResults() {
        $query = array(
            'field0' => 'title',
            'boolean0' => 'and',
            'query0' => 'gegen',
            'searchtype' => 'truncated',
            'language' => '0',
        );

        $result = $this->__restClient->restGet($this->__restUrl, $query);

        $xml = new DOMDocument();
        $xml->loadXML($result->getBody());
        $search = $xml->getElementsByTagName('Search');
        $this->assertTrue($search->item(0)->hasAttribute('hits'), 'Search element should has a "hits" attribute.');
        $this->assertEquals(2, $search->item(0)->getAttribute('hits'), 'Search should return 2 values');
        $resultlist = $xml->getElementsByTagName('ResultList');
        $this->assertEquals(1, $resultlist->length, 'A search with at least one hit should contain a ResultList.');
        $results = $xml->getElementsByTagName('Result');
        $this->assertGreaterThan(0, $results->length, 'There should be at least one result.');
        $this->assertTrue($results->item(0)->hasAttribute('number'), 'A result should have a number attribute.');
        $this->assertTrue($results->item(0)->hasAttribute('xlink:href'), 'A result should have a xlink:ref attribute.');
        $this->assertTrue($results->item(0)->hasAttribute('title'), 'A result should have a title attribute.');
        $this->assertTrue($results->item(0)->hasAttribute('author'), 'A result should have an author attribute.');
        $this->assertTrue($results->item(0)->hasAttribute('abstract'), 'A result should have an abstract attribute.');
    }

    /**
     * Test for good return values if search returns no hits.
     *
     * @return void
     */
    public function testSearchWithNoHits() {
        $query = array(
            'field0' => 'title',
            'boolean0' => 'and',
            'query0' => 'gegen',
            'searchtype' => '',
            'language' => '0',
        );

        $result = $this->__restClient->restGet($this->__restUrl, $query);

        $xml = new DOMDocument();
        $xml->loadXML($result->getBody());
        $search = $xml->getElementsByTagName('Search');
        $this->assertTrue($search->item(0)->hasAttribute('hits'), 'Search element should has a "hits" attribute.');
        $this->assertEquals(0, $search->item(0)->getAttribute('hits'), 'Search should return 0 values');
    }

    /**
     * Test if a too short query throws a 400 HTPP error.
     *
     * @return void
     */
    public function testSearchingWithShortQuery() {
        $query = array(
            'field0' => 'title',
            'boolean0' => 'and',
            'query0' => 'b',
        );

        $result = $this->__restClient->restGet($this->__restUrl, $query);

        $this->assertEquals(400, $result->getStatus(), 'HTTP status should be 400 (Bad request).');

        $xml = new DOMDocument();
        $xml->loadXML($result->getBody());
        $error = $xml->getElementsByTagName('Error');
        $this->assertNotNull($error->item(0)->nodeValue, 'Error element contain no error message.');

    }
}