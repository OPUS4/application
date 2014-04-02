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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
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
        $this->assertNotContains("http://${host}:${port}/solr/corethatdoesnotexist", $body);        
        $this->assertContains("exception 'Application_SearchException' with message 'search server is not responding -- try again later'", $body);

        $this->assertResponseCode(503);

        // restore configuration
        $config = Zend_Registry::get('Zend_Config');
        $config->searchengine->index->app = $oldValue;
        Zend_Registry::set('Zend_Config', $config);        
    }

    /**
     * Regression test for OPUSVIER-1726
     */
    public function testSolrIndexIsNotUpToDate() {
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
        
        $indexer = new Opus_SolrSearch_Index_Indexer();

        $class = new ReflectionClass('Opus_SolrSearch_Index_Indexer');
        $methodGetSolrXmlDocument = $class->getMethod('getSolrXmlDocument');
        $methodGetSolrXmlDocument->setAccessible(true);
        $solrXml = $methodGetSolrXmlDocument->invoke($indexer, $doc1);

        // delete document from database
        $doc1->deletePermanent();

        // add document to search index
        $methodSendSolrXmlToServer = $class->getMethod('sendSolrXmlToServer');
        $methodSendSolrXmlToServer->setAccessible(true);
        $methodSendSolrXmlToServer->invoke($indexer, $solrXml);
        $indexer->commit();

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
        $indexer->removeDocumentFromEntryIndexById($docId1);
        $indexer->commit();

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
        $this->requireSolrConfig();

        $this->dispatch('/rss/index/index/searchtype/simple/start/0/rows/10/query/%22%5C%22%22');

        $this->assertContains("exception 'Application_SearchException' with message 'search query is invalid -- check syntax'", $this->getResponse()->getBody());
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

}
