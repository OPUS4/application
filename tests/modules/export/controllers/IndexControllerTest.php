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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\FileInterface;
use Opus\Common\Model\ModelException;
use Opus\Common\Title;
use Opus\Search\Service;

/**
 * @covers Export_IndexController
 */
class Export_IndexControllerTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string */
    protected $additionalResources = 'all';

    /** @var bool */
    private $removeExportFromGuest = false;

    public function tearDown(): void
    {
        if ($this->removeExportFromGuest) {
            $this->removeAccessOnModuleExportForGuest();
        }

        parent::tearDown();
    }

    /**
     * expectedException Application_Exception
     */
    public function testUnknownAction()
    {
        $this->dispatch('/export/index/invalid');
        $this->assertResponseCode(500);
        $body = $this->getResponse()->getBody();
        $this->assertContains('Plugin invalid not found', $body);
    }

    public function testIndexActionWithoutFormat()
    {
        $this->dispatch('/export');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('export format is not specified', $response->getBody());
    }

    public function testIndexActionWithUnsupportedFormat()
    {
        $this->dispatch('/export/index/index/export/unsupporedformat');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('export format is not supported', $response->getBody());
    }

    public function testIndexActionWithoutQuery()
    {
        $this->dispatch('/export/index/index/export/xml');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('Unspecified search type', $response->getBody());
    }

    public function testIndexActionWithoutStylesheetParam()
    {
        $this->dispatch('/export/index/index/export/xml/query/foo/searchtype/latest');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<?xml version="1.0" encoding="utf-8"?>', $response->getBody());
        $this->assertContains('<export timestamp=', $response->getBody());
    }

    public function testIndexActionWithStylesheetParam()
    {
        $this->dispatch('/export/index/index/export/xml/query/foo/searchtype/latest/stylesheet/example');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<?xml version="1.0" encoding="utf-8"?>', $response->getBody());
        $this->assertContains('<export-example>', $response->getBody());
    }

    public function testIndexActionCollectionSearch()
    {
        $this->dispatch('/export/index/index/searchtype/collection/id/2/export/xml/stylesheet/example');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<?xml version="1.0" encoding="utf-8"?>', $response->getBody());
        $this->assertContains('<export-example>', $response->getBody());
        $this->assertTrue(substr_count($response->getBody(), '<doc>') === 1);
    }

    public function testIndexActionInvalidCollectionSearchMissingIdParam()
    {
        $this->dispatch('/export/index/index/searchtype/collection/export/xml/stylesheet/example');
        $this->assertResponseCode(400);
        $this->assertContains("Could not browse collection due to missing id parameter.", $this->getResponse()->getBody());
    }

    public function testIndexActionInvalidCollectionSearchUnknownId()
    {
        $this->dispatch('/export/index/index/searchtype/collection/id/-1/export/xml/stylesheet/example');
        $this->assertResponseCode(404);
        $this->assertContains("Collection with id '-1' does not exist.", $this->getResponse()->getBody());
    }

    public function testIndexActionInvalidCollectionSearchUnvisible()
    {
        $this->dispatch('/export/index/index/searchtype/collection/id/23/export/xml/stylesheet/example');
        $this->assertResponseCode(404);
        $this->assertContains("Collection with id '23' is not visible.", $this->getResponse()->getBody());
    }

    public function testIndexActionSeriesSearch()
    {
        $this->dispatch('/export/index/index/searchtype/series/id/1/export/xml/stylesheet/example');
        $this->assertResponseCode(200);
        $body = $this->getResponse()->getBody();
        $this->assertContains('<?xml version="1.0" encoding="utf-8"?>', $body);
        $this->assertContains('<export-example>', $body);
        $this->assertTrue(substr_count($body, '<doc>') === 6);
    }

    public function testIndexActionInvalidSeriesSearchMissingIdParam()
    {
        $this->dispatch('/export/index/index/searchtype/series/export/xml/stylesheet/example');
        $this->assertResponseCode(400);
        $this->assertContains("Could not browse series due to missing id parameter.", $this->getResponse()->getBody());
    }

    public function testIndexActionInvalidSeriesSearchUnknownId()
    {
        $this->dispatch('/export/index/index/searchtype/series/id/999999/export/xml/stylesheet/example');
        $this->assertResponseCode(404);
        $this->assertContains("Series with id '999999' does not exist.", $this->getResponse()->getBody());
    }

    public function testIndexActionInvalidSeriesSearchUnvisible()
    {
        $this->dispatch('/export/index/index/searchtype/series/id/3/export/xml/stylesheet/example');
        $this->assertResponseCode(404);
        $this->assertContains("Series with id '3' is not visible.", $this->getResponse()->getBody());
    }

    public function testIndexActionInvalidSeriesSearchNoDocuments()
    {
        $this->dispatch('/export/index/index/searchtype/series/id/8/export/xml/stylesheet/example');
        $this->assertResponseCode(404);
        $this->assertContains("Series with id '8' does not have any published documents.", $this->getResponse()->getBody());
    }

    /**
     * request for raw export output is denied for non-administrative people
     */
    public function testRequestToRawXmlIsDenied()
    {
        $this->removeExportFromGuest = $this->addAccessOnModuleExportForGuest();

        // enable security
        $config           = $this->getConfig();
        $config->security = self::CONFIG_VALUE_TRUE;

        $this->dispatch('/export/index/index/export/xml');

        $this->assertResponseCode(500);
        $this->assertContains('missing parameter stylesheet', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2337
     */
    public function testUnavailableSolrServerReturns503()
    {
        $this->markTestSkipped('TODO Solr configuration and disabling has changed - fix');

        $this->requireSolrConfig();

        // role guest needs privilege to access module export
        $this->removeExportFromGuest = $this->addAccessOnModuleExportForGuest();

        // manipulate solr configuration
        $config = $this->getConfig();
        $host   = $config->searchengine->index->host;
        $port   = $config->searchengine->index->port;
        $this->disableSolr();

        $config->security = self::CONFIG_VALUE_TRUE;

        $this->dispatch('/export/index/index/searchtype/all/export/xml/stylesheet/example');
        $body = $this->getResponse()->getBody();

        $this->assertNotContains("http://{$host}:{$port}/solr/corethatdoesnotexist", $body);
        $this->assertContains("exception 'Application_SearchException' with message 'search server is not responding -- try again later'", $body);
        $this->assertResponseCode(503);
    }

    /**
     * Regression test for OPUSVIER-1726
     */
    public function testSolrIndexIsNotUpToDate()
    {
        $this->markTestSkipped('TODO - getting Solr out-of-sync works differently - fix');

        $doc1 = $this->createTestDocument();
        $doc1->setServerState('published');
        $doc1->setLanguage('eng');
        $title = Title::new();
        $title->setValue('test document for OPUSVIER-1726');
        $title->setLanguage('eng');
        $doc1->setTitleMain($title);
        $doc1->store();
        $docId1 = $doc1->getId();

        // add a document to the search index that is not stored in database
        $doc2 = $this->createTestDocument();
        $doc2->setServerState('published');
        $doc2->setLanguage('eng');
        $title = Title::new();
        $title->setValue('another test document for OPUSVIER-1726');
        $title->setLanguage('eng');
        $doc2->setTitleMain($title);
        // unregister index plugin: database changes are not reflected in search index
        $doc2->unregisterPlugin('Opus_Document_Plugin_Index');
        $doc2->store();
        $docId2 = $doc2->getId();

        $indexer = Service::selectIndexingService();
        $solrXml = $indexer->toSolrDocument($doc2);

        // delete document from database
        $doc2->delete();

        // add document to search index
        // TODO fix $methodSendSolrXmlToServer = $class->getMethod('sendSolrXmlToServer');
        // $methodSendSolrXmlToServer->setAccessible(true);
        // $methodSendSolrXmlToServer->invoke($indexer, $solrXml);
        // $indexer->commit();

        $this->dispatch('/export/index/index/searchtype/simple/query/opusvier-1726/export/xml');

        // make search index up to date
        $indexer->removeDocumentFromEntryIndexById($docId2);
        $indexer->commit();

        $doc1->delete();

        $body = $this->getResponse()->getBody();

        $this->assertNotContains("No Opus_Db_Documents with id $docId2 in database.", $body);
        $this->assertContains('Language="eng" Value="test document for OPUSVIER-1726" Type="main"', $body);
        $this->assertNotContains('Language="eng" Value="another test document for OPUSVIER-1726" Type="main"', $body);
        $this->assertContains('<Opus_Document Id="' . $docId1 . '" Language="eng"', $body);
        $this->assertNotContains('<Opus_Document Id="' . $docId2 . '" Language="eng"', $body);

        $this->assertContains(' doccount="1"', $body); // only the first document can be instantiated (xml output does not contain the second document although it exists in search index)
        $this->assertContains(' queryhits="2"', $body); // both documents exist in search index, but only the first one exists in database (queryhits contains the number of search hits)
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * helper function for tests related to OPUSVIER-2488
     *
     * @param string $url
     * @param int    $numOfTestDocs
     * @param int    $rows
     * @param int    $start
     */
    private function helperForOPUSVIER2488($url, $numOfTestDocs, $rows, $start = 0)
    {
        $docs = [];
        for ($i = 0; $i < $numOfTestDocs; $i++) {
            $doc = $this->createTestDocument();
            $doc->setServerState('published');
            $doc->setLanguage('eng');
            $title = Title::new();
            $title->setValue('OPUSVIER-2488');
            $title->setLanguage('eng');
            $doc->setTitleMain($title);
            $doc->store();
            array_push($docs, $doc);
        }

        $this->dispatch($url);
        $body = $this->getResponse()->getBody();

        $docIds = [];

        foreach ($docs as $doc) {
            array_push($docIds, $doc->getId());
        }

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $this->assertContains('doccount="' . $rows . '"', $body);
        $this->assertContains('queryhits="' . $numOfTestDocs . '"', $body);
        $this->assertEquals($rows, substr_count($body, 'Language="eng" Value="OPUSVIER-2488" Type="main"'));
        $this->assertNotContains('Application_Exception', $body);

        for ($i = $start; $i < $rows; $i++) {
            $this->assertContains('<Opus_Document Id="' . $docIds[$i] . '"', $body);
        }
    }

    /**
     * begin: tests for OPUSVIER-2488
     */
    public function testPaginationIsSupportedInExportWithoutPaginationParams()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamStart0()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/0', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamStart2()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/2', 5, 3, 2);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamStart5()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/5', 5, 0);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamStartTooLarge()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/10', 5, 0);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamStartTooSmall()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/-1', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamStartInvalid()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/foo', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamRows0()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/rows/0', 5, 0);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamRows2()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/rows/2', 5, 2);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamRows5()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/rows/5', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamRowsTooLarge()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/rows/50', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamRowsTooSmall()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/rows/-1', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamRowsInvalid()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/rows/foo', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamsStart0Rows2()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/0/rows/2', 5, 2);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamsStart0Rows10()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/0/rows/10', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamsStart2Rows2()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/2/rows/2', 5, 2, 2);
    }

    public function testPaginationIsSupportedInExportWithPaginationParamsStart2Rows5()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/2/rows/5', 5, 3, 2);
    }

    public function testPaginationIsSupportedInExportWithExtremeValues1()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/0/rows/2147483647', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithExtremeValues2()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/0/rows/2147483648', 5, 5);
    }

    public function testPaginationIsSupportedInExportWithExtremeValues3()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/2147483647/rows/10', 5, 0);
    }

    public function testPaginationIsSupportedInExportWithExtremeValues4()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/2147483648/rows/10', 5, 0);
    }

    public function testPaginationIsSupportedInExportWithExtremeValues5()
    {
        $this->helperForOPUSVIER2488('/export/index/index/searchtype/simple/query/opusvier-2488/export/xml/start/2147483646/rows/1', 5, 0);
    }

    public function testPaginationStartValueLargerThanMaxRows()
    {
        $this->enableSecurity();
        $this->addAccessOnModuleExportForGuest();

        /** setting max rows to 5, so it is less than start = 10 */
        $this->adjustConfiguration(['plugins' => ['export' => ['default' => ['maxDocumentsGuest' => 5]]]]);

        $this->dispatch('/export/index/csv/searchtype/all/export/xml/start/10/rows/10');

        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        // first line is CSV header, therefore it should be 6 lines
        $this->assertEquals(6, substr_count($body, PHP_EOL));
    }

    /**
     * end: tests for OPUSVIER-2488
     */

    /**
     * Regression test for OPUSVIER-2434
     */
    public function testInvalidSearchQueryReturns500()
    {
        $this->markTestSkipped('TODO - not clear why this query should be unsupported - explain');

        $this->requireSolrConfig();

        $this->dispatch('/export/index/index/searchtype/simple/export/xml/start/0/rows/10/query/%22%5C%22%22');

        $response = $this->getResponse()->getBody();

        $this->assertContains("The given search query is not supported.", $response);
        $this->assertNotContains("exception 'Application_SearchException' with message 'search server is not responding -- try again later'", $response);

        $this->assertEquals(500, $this->getResponse()->getHttpResponseCode());
    }

    /**
     * begin: tests for OPUSVIER-2778
     */
    public function testPublistActionWithoutAnyParameter()
    {
        $this->dispatch('/export/index/publist');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('role is not specified', $response->getBody());
    }

    public function testPublistActionWithoutStylesheetValue()
    {
        $this->dispatch('/export/index/publist/stylesheet');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('role is not specified', $response->getBody());
    }

    public function testPublistActionWithoutRoleParameter()
    {
        $this->dispatch('/export/index/publist/stylesheet/default');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('role is not specified', $response->getBody());
    }

    public function testPublistActionWithoutRoleArgument()
    {
        $this->dispatch('/export/index/publist/stylesheet/default/role');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('role is not specified', $response->getBody());
    }

    public function testPublistActionWithoutNumberParameter()
    {
        $this->dispatch('/export/index/publist/stylesheet/default/role/publists');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('number is not specified', $response->getBody());
    }

    public function testPublistActionWithoutNumberArgument()
    {
        $this->dispatch('/export/index/publist/stylesheet/default/role/publists/number');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('number is not specified', $response->getBody());
    }

    /**
     * begin: tests for OPUSVIER-2779
     */
    public function testPublistActionWithNonexistentStylesheet()
    {
        $this->dispatch('/export/index/publist/stylesheet/example/role/publists/number/coll_visible');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('given stylesheet does not exist or is not readable', $response->getBody());
    }

    /**
     * begin: tests for OPUSVIER-2780
     */
    public function testPublistActionWithNonexistentRole()
    {
        $this->dispatch('/export/index/publist/stylesheet/default/role/nonexistent/number/coll_visible');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('specified role does not exist', $response->getBody());
    }

    public function testPublistActionWithInvisibleRole()
    {
        $this->dispatch('/export/index/publist/stylesheet/default/role/no-root-test/number/foo');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('specified role is invisible', $response->getBody());
    }

    public function testPublistActionWithNonexistentNumber()
    {
        $this->dispatch('/export/index/publist/stylesheet/default/role/publists/number/nonexistent');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('specified number does not exist', $response->getBody());
    }

    public function testPublistActionWithInvisibleCollection()
    {
        $this->dispatch('/export/index/publist/stylesheet/default/role/publists/number/coll_invisible');
        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('specified collection is invisible', $response->getBody());
    }

    public function testPublistActionWithVisibleCollection()
    {
        $this->dispatch('/export/index/publist/stylesheet/default/role/publists/number/coll_visible');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<h1>Sichtbare Publikationsliste</h1>', $response->getBody());
    }

    public function testPublistActionWithCollectionNumberIncludingWhiteSpace()
    {
        $this->dispatch('/export/index/publist/stylesheet/default/role/publists/number/coll%20whitespace');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<h1>Publikationsliste mit Whitespace</h1>', $response->getBody());
    }

    public function testPublistActionWithCollectionNumberIncludingSlash()
    {
        $this->dispatch('/export/index/publist/stylesheet/default/role/publists/number/coll%2Fslash');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<h1>Publikationsliste mit Slash</h1>', $response->getBody());
    }

    /**
     * begin: tests for OPUSVIER-2866
     */
    public function testPublistActionWithoutStylesheetParameterInUrl()
    {
        $this->dispatch('/export/index/publist/role/publists/number/coll_visible');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<h1>Sichtbare Publikationsliste</h1>', $response->getBody());
    }

    public function testPublistActionWithoutStylesheetParameterInUrlAndInvalidConfigParameter()
    {
        $config = $this->getConfig();
        if (isset($config->plugins->export->publist->stylesheet)) {
            $config->plugins->export->publist->stylesheet = 'invalid';
        } else {
            $this->adjustConfiguration([
                'plugins' => ['export' => ['publist' => ['stylesheet' => 'invalid']]],
            ]);
        }

        $this->dispatch('/export/index/publist/role/publists/number/coll_visible');

        $this->assertResponseCode(500);
        $response = $this->getResponse();
        $this->assertContains('given stylesheet does not exist or is not readable', $response->getBody());
    }

    public function testPublistActionWithValidStylesheetInConfig()
    {
        $config = $this->getConfig();

        if (isset($config->plugins->export->publist->stylesheet)) {
            $config->plugins->export->publist->stylesheet = 'raw';
        } else {
            $this->adjustConfiguration([
                'plugins' => ['export' => ['publist' => ['stylesheet' => 'raw']], true],
            ]);
        }

        if (isset($config->plugins->export->publist->stylesheetDirectory)) {
            $config->plugins->export->publist->stylesheetDirectory = 'stylesheets';
        } else {
            $this->adjustConfiguration([
                'plugins' => ['export' => ['publist' => ['stylesheetDirectory' => 'stylesheets']], true],
            ]);
        }

        $this->dispatch('/export/index/publist/role/publists/number/coll_visible');

        $this->assertResponseCode(200);
        $response = $this->getResponse();
        $this->assertContains('<export timestamp=', $response->getBody());
        $this->assertContains('</export>', $response->getBody());
    }

    /**
     * begin: tests for OPUSVIER-2867
     */
    public function testPublistActionGroupedByPublishedYear()
    {
        $this->dispatch('/export/index/publist/role/publists/number/coll_visible');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<h1>Sichtbare Publikationsliste</h1>', $response->getBody());
        $normalizedResponseBody = preg_replace('/\n/', "", $response->getBody());
        $this->assertRegExp(
            '/<a href="#opus-year-2010">2010<\/a>.*<a href="#opus-year-2009">2009<\/a>/',
            $normalizedResponseBody
        );
        $this->assertRegExp(
            '/<h4 id="opus-year-2010">2010<\/h4>.*<h4 id="opus-year-2009">2009<\/h4>/',
            $normalizedResponseBody
        );
    }

    public function testPublistActionUrnResolverUrlCorrect()
    {
        $this->dispatch('/export/index/publist/role/ccs/number/H.3');

        $urnResolverUrl = $this->getConfig()->urn->resolverUrl;

        $this->assertXpathContentContains('//a[starts-with(@href, "' . $urnResolverUrl . '")]', 'URN');
    }

    public function testPublistActionDoiResolverUrlCorrect()
    {
        $this->dispatch('/export/index/publist/role/ddc/number/51'); // contains test document 146

        $doiResolverUrl = $this->getConfig()->doi->resolverUrl;

        $this->assertXpathContentContains('//a[starts-with(@href, "' . $doiResolverUrl . '")]', 'DOI');
    }

    /**
     * TODO: Fix manipulation of Zend_Config:
     * 1. $oldConfig and $config are references to the same object
     * 2. Undoing changes are not necessary, as Zend_Config is initialized per test.
     * May apply to other tests as well.
     *
     * @param array $options
     */
    protected function setPublistConfig($options)
    {
    }

    public function testPublistActionGroupedByCompletedYear()
    {
        $config = $this->getConfig();
        // FIXME OPUSVIER-4130 config does not make sense - completely ignores value of setting
        if (isset($config->plugins->export->publist->groupby->completedyear)) {
            $config->plugins->export->publist->groupby->completedyear = self::CONFIG_VALUE_TRUE;
        } else {
            $this->adjustConfiguration([
                'plugins' => ['export' => ['publist' => ['groupby' => ['completedyear' => self::CONFIG_VALUE_TRUE]]]],
            ]);
        }

        $this->dispatch('/export/index/publist/role/publists/number/coll_visible');

        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<h1>Sichtbare Publikationsliste</h1>', $response->getBody());
        $normalizedResponseBody = preg_replace('/\n/', "", $response->getBody());
        $this->assertRegExp('/<a href="#opus-year-2011">2011<\/a>.*<a href="#opus-year-2009">2009<\/a>/', $normalizedResponseBody);
        $this->assertRegExp('/<h4 id="opus-year-2011">2011<\/h4>.*<h4 id="opus-year-2009">2009<\/h4>/', $normalizedResponseBody);
    }

    /**
     * OPUSVIER: 2888
     */
    public function testPublistActionAbsoluteUrls()
    {
        $this->dispatch('/export/index/publist/role/publists/number/coll_visible');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertRegexp('/<a href="http:\/\/.*\/frontdoor\/index\/index\/docId\/113">/', $response->getBody());
    }

    /**
     * OPUSVIER: 2892
     */
    public function testNoNamespaceDefinitonsInDefaultLayout()
    {
        $this->dispatch('/export/index/publist/role/publists/number/coll_visible');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertNotContains(' xmlns=', $response->getBody());
        $this->assertNotContains(' xmlns:php=', $response->getBody());
        $this->assertNotContains(' xmlns:xsi=', $response->getBody());
        $this->assertNotContains(' xmlns:xsl=', $response->getBody());
    }

    /**
     * OPUSVIER: 2889
     */
    public function testPrefixesForIdClassAndAnchorInDefaultLayout()
    {
        $this->dispatch('/export/index/publist/role/publists/number/coll_visible');
        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();

        /* id */
        $this->assertContains(' id="opus-publist"', $response->getBody());
        $this->assertContains(' id="opus-header"', $response->getBody());
        $this->assertNotContains(' id="header"', $response->getBody());
        $this->assertNotContains(' id="publist"', $response->getBody());
        $this->assertNotRegExp('/ id="[a-z]+"/', $response->getBody());

        /* class */
        $this->assertContains(' class="opus-persons"', $response->getBody());
        $this->assertContains(' class="opus-year"', $response->getBody());
        $this->assertContains(' class="opus-title"', $response->getBody());
        $this->assertContains(' class="opus-metadata"', $response->getBody());
        $this->assertContains(' class="opus-links"', $response->getBody());
        $this->assertNotContains(' class="persons"', $response->getBody());
        $this->assertNotContains(' class="year"', $response->getBody());
        $this->assertNotContains(' class="title"', $response->getBody());
        $this->assertNotContains(' class="metadata"', $response->getBody());
        $this->assertNotContains(' class="links"', $response->getBody());
        $this->assertNotRegExp('/ class="[a-z]+"/', $response->getBody());

        /* anchor */
        $this->assertContains(' href="#opus-year-2010"', $response->getBody());
        $this->assertContains(' id="opus-year-2010"', $response->getBody());
        $this->assertNotContains(' href="#L2010', $response->getBody());
        $this->assertNotContains(' id="L2010"', $response->getBody());
        $this->assertNotRegExp('/ href="#L[0-9]{4}"/', $response->getBody());
        $this->assertNotRegExp('/ id="L[0-9]{4}"/', $response->getBody());
    }

    /**
     * Regression Test for OPUSVIER-2998 and OPUSVIER-2999
     */
    public function testPublistActionDisplaysUrlencodedFiles()
    {
        $this->adjustConfiguration([
            'plugins' => [
                'export' => [
                    'publist' => [
                        'file' => [
                            'allow' => [
                                'mimetype' => ['application/xhtml+xml' => 'HTML'],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        // explicitly re-initialize mime type config to apply changes in Zend_Config
        // This is necessary due to static variable in Export_Model_PublicationList
        // which is not reset between tests.

        $config = $this->getConfig();
        $this->assertTrue(
            isset($config->plugins->export->publist->file->allow->mimetype),
            'Failed setting configuration option'
        );
        $this->assertEquals(
            ['application/xhtml+xml' => 'HTML'],
            $config->plugins->export->publist->file->allow->mimetype->toArray(),
            'Failed setting configuration option'
        );

        $doc  = Document::get(92);
        $file = $doc->getFile(1);
        $this->assertTrue($file instanceof FileInterface, 'Test setup has changed.');
        $this->assertEquals('datei mit unüblichem Namen.xhtml', $file->getPathName(), 'Test setup has changed.');

        $collection = $doc->getCollection(0);

        $this->assertEquals('coll_visible', $collection->getNumber(), 'Test setup has changed');
        $this->assertEquals(1, $collection->getVisible(), 'Test setup has changed');

        $this->dispatch('/export/index/publist/role/publists/number/coll_visible');

        $this->assertResponseCode(200, $this->getResponse()->getBody());

        $response = $this->getResponse();
        $this->assertContains(urlencode('datei mit unüblichem Namen.xhtml'), $response->getBody());
    }

    public function testXMLExportForFrontdoor()
    {
        $document = $this->createTestDocument();
        $document->setServerState('published');
        $docId = $document->store();

        $this->dispatch('/export/index/index/docId/' . $docId . '/searchtype/id/export/xml/stylesheet/example');

        $this->assertResponseCode(200, $this->getResponse()->getBody());
        $response = $this->getResponse();
        $this->assertContains('<?xml version="1.0" encoding="utf-8"?>', $response->getBody());
        $this->assertContains('<export-example>', $response->getBody());
        $this->assertContains($docId, $response->getBody());
    }

    /**
     * Without access rights, no documents can be exported.
     */
    public function testXmlExportForSearchtypeIdWithoutAccessRights()
    {
        $this->enableSecurity();
        $changedAccess = $this->removeAccessOnModuleExportForGuest();
        $this->useEnglish();

        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $docId = $doc->store();

        $this->dispatch("/export/index/index/docId/$docId/export/xml/stylesheet/example/searchtype/id");

        if ($changedAccess) {
            $this->addAccessOnModuleExportForGuest();
        }

        $this->assertXpath('//error');
        $this->assertXpathContentContains('//error', 'Unauthorized: Access to module not allowed.');
    }

    public function testXmlExportDoesNotContainUnpublishedDocument()
    {
        parent::setUpWithEnv('production');

        $this->enableSecurity();
        $this->assertSecurityConfigured();
        $changedAccess = $this->addAccessOnModuleExportForGuest();

        $doc = $this->createTestDocument();
        $doc->setServerState('unpublished');
        $docId = $doc->store();

        $this->dispatch("/export/index/index/docId/$docId/export/xml/stylesheet/example/searchtype/id");

        if ($changedAccess) {
            $this->removeAccessOnModuleExportForGuest();
        }

        $this->assertResponseCode(401);
        $this->assertContains('export of unpublished documents is not allowed', $this->getResponse()->getBody());
    }

    /**
     * Regressionstest für OPUSVIER-3391.
     * TODO refactor - bootstrapping happens before so the helpers need to be adjusted - better way?
     */
    public function testExportedFilePath()
    {
        $opusUrl = 'https://localhost/opus4';

        $view = $this->getView();
        $view->getHelper('ServerUrl')->setHost('localhost');
        $view->getHelper('ServerUrl')->setScheme('https');
        Zend_Controller_Front::getInstance()->setBaseUrl('/opus4');

        $this->dispatch('/export/index/index/docId/146/export/xml/stylesheet/example/searchtype/id');
        $this->assertXpathContentContains('//file', $opusUrl . '/files/146/test.pdf');
    }

    /**
     * Zugriff auf MARC21 Export standardmäßig nur für Administratoren freigegeben,
     * auch wenn das Zugriffsrecht auf das Module export vorhanden ist.
     *
     * @throws ModelException
     */
    public function testNonAdminAccessOnRestrictedMarc21ExportForbidden()
    {
        $exportAccessProvided = $this->addAccessOnModuleExportForGuest();
        $this->enableSecurity();

        $this->dispatch("/export/index/marc21/docId/146/searchtype/id");

        if ($exportAccessProvided) {
            $this->removeAccessOnModuleExportForGuest();
        }

        $this->assertResponseCode(401);
    }

    /**
     * Zugriff auf DataCite Export standardmäßig nur für Administratoren freigegeben,
     * auch wenn das Zugriffsrecht auf das Module export vorhanden ist.
     *
     * @throws ModelException
     */
    public function testNonAdminAccessOnRestrictedDataCiteExportForbidden()
    {
        $exportAccessProvided = $this->addAccessOnModuleExportForGuest();
        $this->enableSecurity();

        $this->dispatch('/export/index/datacite/docId/146');

        if ($exportAccessProvided) {
            $this->removeAccessOnModuleExportForGuest();
        }

        $this->assertResponseCode(401);
    }

    /**
     * Zugriff auf RIS Export ist nicht eingeschränkt, wenn Zugriffsrecht auf das
     * Module export besteht.
     *
     * @throws ModelException
     */
    public function testNonAdminAccessOnUnrestrictedExportAllowed()
    {
        $exportAccessProvided = $this->addAccessOnModuleExportForGuest();
        $this->enableSecurity();

        $this->dispatch("/export/index/ris/searchtype/id/docId/146");

        if ($exportAccessProvided) {
            $this->removeAccessOnModuleExportForGuest();
        }

        $this->assertResponseCode(200);
    }

    /**
     * Zugriff auf den Marc21-Export auch für Nicht-Administratoren,
     * wenn entsprechende Konfigurationseinstellung deaktiviert.
     */
    public function testNonAdminAccessOnUnrestrictedMarc21ExportAllowed()
    {
        $this->adjustConfiguration([
            'plugins' => ['export' => ['marc21' => ['adminOnly' => self::CONFIG_VALUE_FALSE]]],
        ]);

        $exportAccessProvided = $this->addAccessOnModuleExportForGuest();
        $this->enableSecurity();

        $this->dispatch("/export/index/marc21/docId/146/searchtype/id");

        if ($exportAccessProvided) {
            $this->removeAccessOnModuleExportForGuest();
        }

        $this->assertResponseCode(200);
    }

    /**
     * Zugriffsrecht auf das Module export gewähren.
     * Gibt true zurück, wenn der Zugriff auf das Module export hinzugefügt wurde.
     *
     * @return bool
     * @throws ModelException
     */
    private function addAccessOnModuleExportForGuest()
    {
        $this->removeExportFromGuest = true;
        return $this->addModuleAccess('export', 'guest');
    }

    /**
     * Zugriffsrecht auf das Module export entziehen.
     * Gibt true zurück, wenn der Zugriff auf das Module export entzogen wurde.
     *
     * @return bool
     */
    private function removeAccessOnModuleExportForGuest()
    {
        $this->removeExportFromGuest = false;
        return $this->removeModuleAccess('export', 'guest');
    }

    public function testPublistPubmedRendering()
    {
        $this->dispatch('/export/index/publist/role/publists/number/coll_visible');

        $this->assertResponseCode(200);

        $this->markTestIncomplete('Setup collection for publist including document with pubmed-id');
    }
}
