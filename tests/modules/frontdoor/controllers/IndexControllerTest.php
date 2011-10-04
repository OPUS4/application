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
 * @author      Julian Heise <heise@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Frontdoor_IndexControllerTest extends ControllerTestCase {

    /**
     * Document to count on :)
     *
     * @var Opus_Document
     */
    protected $_document = null;

    protected $_security_backup = null;


    /**
     * Provide clean documents and statistics table and remove temporary files.
     * Create document for counting.
     *
     * @return void
     */

    public function setUp() {
        parent::setUp();

        $path = Zend_Registry::get('temp_dir') . '~localstat.xml';
        @unlink($path);

        $this->_document = new Opus_Document();
        $this->_document->setType("doctoral_thesis");
        $this->_document->store();

        //setting server globals
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        $_SERVER['HTTP_USER_AGENT'] = 'bla';
        $_SERVER['REDIRECT_STATUS'] = 200;

        // enable security
        $config = Zend_Registry::get('Zend_Config');
        $this->_security_backup = $config->security;
        $config->security = '1';
    }

    protected function tearDown() {
        // restore old security config
        $config = Zend_Registry::get('Zend_Config');
        $config->security = $this->_security_backup;
        Zend_Registry::set('Zend_Config', $config);

        if ($this->_document instanceof Opus_Document) {
            $this->_document->deletePermanent();
        }
        parent::tearDown();
    }

    public function testIndexActionOnPublished() {
        $this->_document->setServerState('published')->store();
        $doc_id = $this->_document->getId();
        $this->dispatch('/frontdoor/index/index/docId/'.$doc_id);

        $this->assertResponseCode(200);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
        $this->assertContains('<div class="frontdoor">', $response->getBody());
    }

    public function testIndexActionOnDeleted() {
        $this->_document->setServerState('deleted')->store();
        $doc_id = $this->_document->getId();
        $this->dispatch('/frontdoor/index/index/docId/'.$doc_id);

        $this->assertResponseCode(410);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    public function testIndexActionOnUnpublished() {
        $this->_document->setServerState('unpublished')->store();
        $doc_id = $this->_document->getId();
        $this->dispatch('/frontdoor/index/index/docId/'.$doc_id);

        $this->assertResponseCode(403);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    public function testIndexActionOnTemporary() {
        $this->_document->setServerState('temporary')->store();
        $doc_id = $this->_document->getId();
        $this->dispatch('/frontdoor/index/index/docId/'.$doc_id);

        $this->assertResponseCode(403);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    public function testIndexActionOnNonExistent() {
        $doc_id = $this->_document->getId();
        $this->dispatch('/frontdoor/index/index/docId/'.$doc_id.$doc_id.'100');

        $this->assertResponseCode(404);
        $this->assertController('index');
        $this->assertAction('index');

        $response = $this->getResponse();
        $this->assertContains('<div class="frontdoor-error">', $response->getBody());
    }

    /**
     * @deprecated since OPUS 3.0.2, the function under test is marked as deprecated
     * and will be removed in future releases
     */
    public function testMapopus3Action() {        
        $opus3_id = 'foobar-'.rand();
        $this->_document->addIdentifierOpus3()->setValue($opus3_id);
        $doc_id = $this->_document->store();

        $this->dispatch('/frontdoor/index/mapopus3/oldId/'.$opus3_id);

        $this->assertResponseCode(302);
        $this->assertModule('frontdoor');
        $this->assertController('index');
        $this->assertAction('mapopus3');

        $response = $this->getResponse();
        $headers = $response->getHeaders();

        $this->assertEquals('Location', $headers[0]['name']);
        $this->assertStringEndsWith('/rewrite/index/id/type/opus3-id/value/' . $opus3_id, $headers[0]['value']);

        $this->checkForBadStringsInHtml($response->getBody());
    }

    /**
     * test to document bug OPUSVIER-1695
     */
    public function testUncontrolledKeywordHeaderIsNotDisplayedIfUncontrolledKeywordsDoNotExist() {
	$this->dispatch('/frontdoor/index/index/docId/103');
        $this->assertResponseCode(200);
        $this->assertModule('frontdoor');
        $this->assertController('index');
        $this->assertAction('index');
        $body = $this->getResponse()->getBody();
        $this->assertContains('<table class="result-data frontdoordata">', $body);
        $this->assertNotContains('<td></td>', $body);
    }
}
?>
