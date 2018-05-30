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
 * @package     Rss
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Rss_IndexControllerTest
 *
 * TODO fix Solr configuration
 *
 * @covers Rss_IndexController
 */
class Rss_IndexControllerTest extends ControllerTestCase {

    public function testIndexAction() {
        $this->dispatch('/rss/index/index');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<?xml version="1.0" encoding="utf-8"?>', $response->getBody());
        $this->assertContains('<rss version="2.0">', $response->getBody());
    }

    /**
     * Regression test for OPUSVIER-2337
     */
    public function testUnavailableSolrServerReturns503() {
        $this->markTestSkipped('configuration of Solr has changed - fix');

        $this->requireSolrConfig();

        // manipulate solr configuration
        $config = Zend_Registry::get('Zend_Config');
        $host = $config->searchengine->index->host;
        $port = $config->searchengine->index->port;
        $oldValue = $config->searchengine->index->app;
        $config->searchengine->index->app = 'solr/corethatdoesnotexist';
        Zend_Registry::set('Zend_Config', $config);

        $this->dispatch('/rss/index/index/searchtype/all');
        $body = $this->getResponse()->getBody();

        // restore configuration
        $config = Zend_Registry::get('Zend_Config');
        $config->searchengine->index->app = $oldValue;
        Zend_Registry::set('Zend_Config', $config);

        $this->assertNotContains("http://${host}:${port}/solr/corethatdoesnotexist", $body);
        $this->assertContains("The search service is currently not available.", $body);

        $this->assertResponseCode(503);

        // Prüfen, ob Layout aktiviert ist
        $this->assertQuery("//div#container", "Container DIV missing. Layout disabled?");
    }

    /**
     * Regression test for OPUSVIER-1726
     */
    public function testSolrIndexIsNotUpToDate() {
        $this->markTestSkipped('disabling indexing does not work - fix');

        // add a document to the search index that is not stored in database
        $doc1 = $this->createTestDocument();
        $doc1->setServerState('published');
        $doc1->setLanguage('eng');
        $title = new Opus_Title();
        $title->setValue('test document for OPUSVIER-1726');
        $title->setLanguage('eng');
        $doc1->setTitleMain($title);
        // unregister index plugin: database changes are not reflected in search index
        $doc1->unregisterPlugin('Opus_Document_Plugin_Index');
        $doc1->store();

        $docId1 = $doc1->getId();
        $date = new Zend_Date($doc1->getServerDatePublished());
        $dateValue1 = $date->get(Zend_Date::RFC_2822);

        $indexer = Opus_Search_Service::selectIndexingService( null, 'solr' );

        $indexer->addDocumentsToIndex($doc1);

        // delete document from database
        $doc1->deletePermanent();

        sleep(2); // make sure $doc2 do not get the same value for server_date_published

        $doc2 = $this->createTestDocument();
        $doc2->setServerState('published');
        $doc2->setLanguage('eng');
        $title = new Opus_Title();
        $title->setValue('another test document for OPUSVIER-1726');
        $title->setLanguage('eng');
        $doc2->setTitleMain($title);
        $doc2->store();

        $docId2 = $doc2->getId();
        $date = new Zend_Date($doc2->getServerDatePublished());
        $dateValue2 = $date->get(Zend_Date::RFC_2822);

        $this->dispatch('/rss/index/index/searchtype/all');

        // make search index up to date
        $indexer->removeDocumentsFromIndexById($docId1);

        $doc2->deletePermanent();

        $body = $this->getResponse()->getBody();
        $this->assertNotContains("No Opus_Db_Documents with id $docId1 in database.", $body);
        $this->assertNotContains('<title>test document for OPUSVIER-1726</title>', $body);
        $this->assertContains('<title>another test document for OPUSVIER-1726</title>', $body);
        $this->assertNotContains("frontdoor/index/index/docId/$docId1</link>", $body);
        $this->assertContains("frontdoor/index/index/docId/$docId2</link>", $body);
        $this->assertNotContains("<pubDate>$dateValue1</pubDate>", $body);
        $this->assertNotContains("<lastBuildDate>$dateValue1</lastBuildDate>", $body);
        $this->assertContains("<pubDate>$dateValue2</pubDate>", $body);
        $this->assertContains("<lastBuildDate>$dateValue2</lastBuildDate>", $body);
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * Regression test for OPUSVIER-2434
     */
    public function testInvalidSearchQueryReturn500() {
        $this->markTestSkipped('TODO - not clear how the request should be handled - why is it invalid?');

        $this->requireSolrConfig();

        $this->dispatch('/rss/index/index/searchtype/simple/start/0/rows/10/query/%22%5C%22%22');

        $this->assertContains("The given search query is not supported", $this->getResponse()->getBody());
        $this->assertNotContains("exception 'Application_SearchException' with message 'search server is not responding -- try again later'", $this->getResponse()->getBody());

        $this->assertEquals(500, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * Regression test for OPUSVIER-2534
     */
    public function testOutputWithEmptySearchResult() {
        $this->requireSolrConfig();

        $this->dispatch('/rss/index/index/searchtype/simple/start/0/rows/10/query/asearchquerywithoutanyhits');

        $this->assertNotContains("Warning: XSLTProcessor::transformToXml(): runtime error", $this->getResponse()->getBody());

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());

    }

    /**
     * Testet, ob Links im Rss richtig aufgebaut werden.
     * Im PhpUnit-Test ist der Host leer, deswegen wird er hier im Test nicht mit berücksichtigt.
     * TODO: insert host in test-url
     */
    public function testRssLink() {
        Zend_Controller_Front::getInstance()->setBaseUrl('opus4dev');
        $this->dispatch('/rss/index/index');
        $this->assertXpathContentContains('//link', 'http://opus4dev/frontdoor/index/index/docId/147');
        $this->assertXpathContentContains('//link', 'http://opus4dev/frontdoor/index/index/docId/150');
    }

}
