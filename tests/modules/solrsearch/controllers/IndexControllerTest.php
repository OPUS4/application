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
 * @package     Module_Solrsearch
 * @author      Julian Heise <heise@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Solrsearch_IndexControllerTest.
 *
 * @covers Solrsearch_IndexController
 */
class Solrsearch_IndexControllerTest extends ControllerTestCase
{

    private function doStandardControllerTest($url, $controller, $action) {
        $this->dispatch($url);

        $this->assertResponseCode(200);

        if (!is_null($controller)) {
            $this->assertController($controller);
        }
        if (!is_null($action)) {
            $this->assertAction($action);
        }

        $this->assertNotEquals('', trim($this->getResponse()->getBody()), 'HTTP Response Body is empty');
    }

    public function testIndexAction() {
        $this->doStandardControllerTest('/solrsearch', 'index', 'index');
    }


    public function testAdvancedAction() {
        $this->doStandardControllerTest('/solrsearch/index/advanced', 'index', 'advanced');
        $response = $this->getResponse();
        $this->checkForBadStringsInHtml($response->getBody());
    }

    public function testEmptySearch() {
        $this->dispatch('/solrsearch/index/search/searchtype/simple/start/0/rows/10/query/thissearchtermdoesnotexist/sortfield/score/sortorder/desc');
        $this->assertNotContains('result_box', $this->getResponse()->getBody());
        $this->assertNotContains('search_results', $this->getResponse()->getBody());
    }

    public function testLatestAction() {
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/latest', 'index', 'search');
        $this->checkForBadStringsInHtml($this->getResponse()->getBody());
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 10);
    }

    public function testLatestActionWith20Hits() {
        $this->doStandardControllerTest('/solrsearch/index/search/rows/20/searchtype/latest', 'index', 'search');
        $this->checkForBadStringsInHtml($this->getResponse()->getBody());
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 20);
    }

    public function testLatestActionWithNegativeNumberOfHits() {
        $this->doStandardControllerTest('/solrsearch/index/search/rows/-1/searchtype/latest', 'index', 'search');
        $this->checkForBadStringsInHtml($this->getResponse()->getBody());
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 10);
    }

    public function testLatestActionWithTooLargeNumberOfHits() {
        $this->doStandardControllerTest('/solrsearch/index/search/rows/1000/searchtype/latest', 'index', 'search');
        // we need to mask 'fehler' in metadata (otherwise checkForBadStringsInHtml will assume an error has occurred)
        $this->checkForBadStringsInHtml(str_replace('IMU–Sensorfehler', 'IMU–Sensorxxxxxx', $this->getResponse()->getBody()));
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 100);
    }

    public function testSearchdispatchAction() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'searchtype' => 'simple',
                    'query'=>'*:*'
                ));
        $this->dispatch('/solrsearch/index/searchdispatch');
        $this->assertRedirect();
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'searchtype' => 'advanced',
                    'author'=>'a*'
                ));
        $this->dispatch('/solrsearch/index/searchdispatch');
        $this->assertRedirect();
    }

    public function testSimpleSearchAction() {
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/*:*', null, null);
        $this->assertContains('results_title', strtolower($this->getResponse()->getBody()));
    }

    public function testAdvancedSearchAction() {
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/advanced/author/doe', null, null);
        $this->assertContains('results_title', strtolower($this->getResponse()->getBody()));
    }

    public function testWildcardAsteriskUppercaseQuerySearch() {
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/test+Docum*', null, null);
        $numberOfHitsUpper = substr_count($this->getResponse()->getBody(), 'result_box');

        $this->getResponse()->clearBody();

        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/test+docum*', null, null);
        $numberOfHitsLower = substr_count($this->getResponse()->getBody(), 'result_box');

        $this->assertTrue($numberOfHitsLower > 0);
        $this->assertEquals($numberOfHitsLower, $numberOfHitsUpper);
    }

    public function testWildcardQuestionMarkUppercaseQuerySearch() {
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/test+Do%3Fum%3Fnt', null, null);
        $numberOfHitsUpper = substr_count($this->getResponse()->getBody(), 'result_box');

        $this->getResponse()->clearBody();

        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/test+do%3Fum%3Fnt', null, null);
        $numberOfHitsLower = substr_count($this->getResponse()->getBody(), 'result_box');

        $this->assertTrue($numberOfHitsLower > 0);
        $this->assertEquals($numberOfHitsLower, $numberOfHitsUpper);
    }

    private function createPublishedTestDoc() {
        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->setLanguage('eng');
        $document->addTitleMain()->setValue('testphrasequerieswithwildcard*s')->setLanguage('eng');
        $document->store();
    }

    public function testPhraseQueriesWithWildcards1() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"testphrasequerieswith*"', null, null);
        $this->assertEquals(0, substr_count($this->getResponse()->getBody(), 'result_box'), "result is not empty");
    }

    public function testPhraseQueriesWithWildcards2() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"testphrasequerieswithwildcard*"', null, null);
        $this->assertEquals(1, substr_count($this->getResponse()->getBody(), 'result_box'), "result is empty");
    }

    public function testPhraseQueriesWithWildcards3() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"testphrasequerieswithwildcard*s"', null, null);
        $this->assertEquals(1, substr_count($this->getResponse()->getBody(), 'result_box'), "result is empty");
    }

    public function testPhraseQueriesWithWildcards4() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"TESTPHRASEQUERIESWITH*"', null, null);
        $this->assertEquals(0, substr_count($this->getResponse()->getBody(), 'result_box'), "result is not empty");
    }

    public function testPhraseQueriesWithWildcards5() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"TESTPHRASEQUERIESWITHWILDCARD*"', null, null);
        $this->assertEquals(1, substr_count($this->getResponse()->getBody(), 'result_box'), "result is empty");
    }

    public function testPhraseQueriesWithWildcards6() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"TESTPHRASEQUERIESWITHWILDCARD*S"', null, null);
        $this->assertEquals(1, substr_count($this->getResponse()->getBody(), 'result_box'), "result is empty");
    }

    public function testPhraseQueriesWithWildcards7() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/testphrasequerieswith*', null, null);
        $this->assertEquals(1, substr_count($this->getResponse()->getBody(), 'result_box'), "result is empty");
    }

    public function testPhraseQueriesWithWildcards8() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/testphrasequerieswithwildcard*', null, null);
        $this->assertEquals(1, substr_count($this->getResponse()->getBody(), 'result_box'), "result is empty");
    }

    public function testPhraseQueriesWithWildcards9() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/testphrasequerieswithwildcard*s', null, null);
        $this->assertEquals(1, substr_count($this->getResponse()->getBody(), 'result_box'), "result is empty");
    }

    public function testPhraseQueriesWithWildcards10() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/TESTPHRASEQUERIESWITH*', null, null);
        $this->assertEquals(1, substr_count($this->getResponse()->getBody(), 'result_box'), "result is empty");
    }

    public function testPhraseQueriesWithWildcards11() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/TESTPHRASEQUERIESWITHWILDCARD*', null, null);
        $this->assertEquals(1, substr_count($this->getResponse()->getBody(), 'result_box'), "result is empty");
    }

    public function testPhraseQueriesWithWildcards12() {
        $this->createPublishedTestDoc();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/TESTPHRASEQUERIESWITHWILDCARD*S', null, null);
        $this->assertEquals(1, substr_count($this->getResponse()->getBody(), 'result_box'), "result is empty");
    }

    public function testInvalidsearchtermAction() {
        $searchtypeParams = array ('', 'searchtype/simple', 'searchtype/advanced', 'searchtype/foo');
        foreach ($searchtypeParams as $searchtypeParam) {
            $this->dispatch('/solrsearch/index/invalidsearchterm/' . $searchtypeParam);
            $this->assertResponseCode(200);
            $responseBody = $this->getResponse()->getBody();
            $this->assertContains('<div class="invalidsearchterm">', $responseBody);
        }
    }

    public function testEmptySimpleQuery() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'searchtype' => 'simple',
                    'query' => ''
                ));
        $this->dispatch('/solrsearch/index/searchdispatch');
        $this->assertRedirect();
        //$this->assertRedirectTo('/solrsearch/index/invalidsearchterm');
    }

    public function testEmptyAdvancedQuery() {
        $this->request
                ->setMethod('POST')
                ->setPost(array(
                    'searchtype' => 'advanced'
                ));
        $this->dispatch('/solrsearch/index/searchdispatch');
        $this->assertRedirect();
        //$this->assertRedirectTo('/solrsearch/index/invalidsearchterm');
    }

    /**
     * Regression test for OPUSVIER-2147 (collection browsing)
     */
    public function testPaginationBarContainsOverallNumberOfHitsInCollectionBrowsing() {
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/collection/id/74', null, null);
        $this->assertEquals(75, $this->getNumOfHits());
    }

    /**
     * Regression test for OPUSVIER-2147 (doctype browsing)
     */
    public function testPaginationBarContainsOverallNumberOfHitsInDoctypeBrowsing() {
        $this->doStandardControllerTest(
            '/solrsearch/index/search/searchtype/simple/query/*%3A*/browsing/true/doctypefq/report', null, null
        );
        $this->assertEquals(52, $this->getNumOfHits());
    }

    /**
     * Regression test for OPUSVIER-2144
     *
     * IMPORTANT: Unit Test funktioniert nicht mehr, wenn die Zahl der Dokumente 20 übersteigt.
     */
    public function testLastPageUrlEqualsNextPageUrlDocTypeArticle() {
        $docFinder = new Opus_DocumentFinder();
        $docFinder->setType('article')->setServerState('published');

        // check if test requirements are met
        $docCount = $docFinder->count();

        $this->assertGreaterThan(10, $docCount, "Test requires at least 11 documents.");

        $startLast = floor(($docCount - 1) / 10) * 10; // 10 results per page, multiple of 10
        $start = $startLast - 10;

        $this->doStandardControllerTest(
            "/solrsearch/index/search/searchtype/simple/query/*%3A*/browsing/true/doctypefq/article/start/$start",
            null, null
        );

        $link = '/solrsearch/index/search/searchtype/simple/query/%2A%3A%2A/browsing/true/doctypefq/article';

        $body = $this->getResponse()->getBody();

        // check four next/last page links are all the same
        $this->assertTrue(4 == substr_count($body, "$link/start/$startLast/rows/10\""));
        $this->assertNotContains("$link/start/19/rows/10\">", $body);
        $this->assertEquals($docCount, $this->getNumOfHits());
    }

    /**
     * Regression test for OPUSVIER-2144
     */
    public function testLastPageUrlEqualsNextPageUrlDocTypeDoctoralThesis() {
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/*%3A*/browsing/true/doctypefq/doctoralthesis', null, null);
        $this->assertTrue(4 == substr_count($this->getResponse()->getBody(), '/solrsearch/index/search/searchtype/simple/query/%2A%3A%2A/browsing/true/doctypefq/doctoralthesis/start/10/rows/10"'));
        $this->assertNotContains('solrsearch/index/search/searchtype/simple/query/%2A%3A%2A/browsing/true/doctypefq/doctoralthesis/start/17/rows/10"', $this->getResponse()->getBody());
        $this->assertEquals(18, $this->getNumOfHits());
    }

    /**
     * Regression test for OPUSVIER-2134
     */
    public function testCatchAllSearch() {
        $document = $this->createTestDocument();
        $document->setServerState('published');
        $document->setLanguage('eng');
        $document->addTitleParent()->setValue('testcatchallsearch_title_parent')->setLanguage('eng');
        $document->addTitleAdditional()->setValue('testcatchallsearch_title_additional')->setLanguage('eng');
        $document->addTitleAdditional()->setValue('testcatchallsearch_title_sub')->setLanguage('eng');
        $document->setPublisherName('testcatchallsearch_publisher_name');
        $document->setPublisherPlace('testcatchallsearch_publisher_place');
        $document->setCreatingCorporation('testcatchallsearch_creating_corporation');
        $document->setContributingCorporation('testcatchallsearch_contributing_corporation');
        $document->store();

        $queries = array(
            'testcatchallsearch_title_parent',
            'testcatchallsearch_title_additional',
            'testcatchallsearch_title_sub',
            'testcatchallsearch_publisher_name',
            'testcatchallsearch_publisher_place',
            'testcatchallsearch_creating_corporation',
            'testcatchallsearch_contributing_corporation'
        );

        // check that each catch all search for given query terms returns one hit
        foreach ($queries as $query) {
            $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/start/0/rows/10/query/' . $query, null, null);
            $hits = $this->getNumOfHits();
            $this->assertTrue(substr_count($this->getResponse()->getBody(), '<strong>1</strong>') == 4, $query . " (hits = '$hits')");
            $this->assertEquals(1, $hits);
            $this->getResponse()->clearBody();
        }
    }

    public function testRssLinkIsDisplayedForSimpleSearch() {
        $this->dispatch('/solrsearch/index/search/searchtype/simple/start/0/rows/10/query/doe/sortfield/author/sortorder/asc/yearfq/2008');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/simple/query/doe/yearfq/2008" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }

    public function testRssLinkIsDisplayedForAdvancedSearch() {
        $this->dispatch('/solrsearch/index/search/searchtype/advanced/start/0/rows/20/sortfield/score/sortorder/desc'
            . '/author/doe/authormodifier/contains_all/fulltext/test/fulltextmodifier/contains_all/subjectfq/eBook');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/advanced/author/doe/authormodifier/contains_all/fulltext/test/fulltextmodifier/contains_all/subjectfq/eBook" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }

    public function testRssLinkIsDisplayedForAllSearch() {
        $this->dispatch('/solrsearch/index/search/searchtype/all/start/0/rows/10/sortfield/author/sortorder/asc/author_facetfq/Arndt+Klocke');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/all/author_facetfq/Arndt+Klocke" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }

    public function testRssLinkIsDisplayedForLatestSearch() {
        $this->dispatch('/solrsearch/index/search?rows=20&searchtype=latest');
        $this->assertResponseCode(200);
        $this->assertContains('/rss" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }

    public function testRssLinkIsDisplayedForLatestSearchAlternative() {
        $this->dispatch('/solrsearch/index/search/rows/20/searchtype/latest');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/latest" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }

    public function testRssLinkIsDisplayedForBrowseDocumenttypes() {
        $this->dispatch('/solrsearch/index/search/searchtype/simple/query/*%3A*/browsing/true/doctypefq/workingpaper/start/0/rows/10/author_facetfq/Siang+Fung+Ang');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/simple/query/%2A%3A%2A/doctypefq/workingpaper/author_facetfq/Siang+Fung+Ang" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }

    public function testRssLinkIsDisplayedForBrowseSeries() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/1/start/0/rows/10/languagefq/eng/sortfield/seriesnumber/sortorder/asc');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/series/id/1/languagefq/eng" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }

    public function testRssLinkIsDisplayedForBrowseCollection() {
        $this->dispatch('/solrsearch/index/search/searchtype/collection/id/63/start/0/rows/10/languagefq/deu');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/collection/id/63/languagefq/deu" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }

    public function testRssLinkIsDisplayedForAuthorSearch() {
        $this->dispatch('/solrsearch/index/search/searchtype/authorsearch/author/"John+Doe"/start/0/rows/10/yearfq/2008/sortfield/year/sortorder/desc');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/authorsearch/author/%22John+Doe%22/yearfq/2008" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }

    public function testRssLinkIsDisplayedForEmptySearch() {
        $this->dispatch('/solrsearch/index/search/searchtype/simple/start/0/rows/10/query/thissearchtermdoesnotexist/sortfield/score/sortorder/desc');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/simple/query/thissearchtermdoesnotexist" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }


    /**
     * series search related test cases
     *
     */

    public function testSeriesSearchWithInvalidId() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/12345');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/solrsearch/browse');
    }

    public function testSeriesSearchWithoutId() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/solrsearch/browse');
    }

    public function testSeriesSearchWithInvisibleId() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/3');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/solrsearch/browse');
    }

    public function testSeriesSearchWithEmptyDocumentsId() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/8');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/solrsearch/browse');
    }

    public function testSeriesSearch() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/1');
        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $docIds = array(146, 93, 92, 94, 91);
        foreach ($docIds as $docId) {
            $this->assertContains('/frontdoor/index/index/searchtype/series/id/1/docId/' . $docId, $body);
        }

        $seriesNumbers = array('5/5', '4/5', '3/5', '2/5', '1/5');
        foreach ($seriesNumbers as $seriesNumber) {
            $this->assertContains('<div class="results_seriesnumber">' . $seriesNumber . '</div>', $body);
        }

        $this->assertContains('/series_logos/1/300_150.png', $body);
        $this->assertContains('Dies ist die Schriftenreihe <b>MySeries</b>', $body);
    }

    public function testSeriesSearchPaginationAndSortingLinks() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/5');
        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $this->assertContains('/series_logos/5/400_100.png', $body);
        $this->assertContains('Lorem ipsum dolor sit amet,', $body);

        // pagination links
        $this->assertTrue(substr_count($body, '/solrsearch/index/search/searchtype/series/id/5/start/10/rows/10"') == 4);

        // sorting links
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/5/start/0/rows/10/sortfield/seriesnumber/sortorder/asc', $body);
        $this->assertNotContains('/solrsearch/index/search/searchtype/series/id/5/start/0/rows/10/sortfield/seriesnumber/sortorder/desc', $body);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/5/start/0/rows/10/sortfield/year/sortorder/asc', $body);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/5/start/0/rows/10/sortfield/year/sortorder/desc', $body);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/5/start/0/rows/10/sortfield/title/sortorder/asc', $body);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/5/start/0/rows/10/sortfield/title/sortorder/desc', $body);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/5/start/0/rows/10/sortfield/author/sortorder/asc', $body);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/5/start/0/rows/10/sortfield/author/sortorder/desc', $body);
    }

    public function testSeriesSearchPaginationWorks() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/5/start/10/rows/10');
        $this->assertResponseCode(200);

        $body = $this->getResponse()->getBody();

        $this->assertXpathCount('//a[contains(@href, "/docId/3") and contains(@href, "/frontdoor/index/index")]', 1);
        $this->assertXpathCount('//a[contains(@href, "/docId/2") and contains(@href, "/frontdoor/index/index")]', 1);
        $this->assertXpathCount('//a[contains(@href, "/docId/1") and contains(@href, "/frontdoor/index/index")]', 1);

        $this->assertContains('<div class="results_seriesnumber">C</div>', $body);
        $this->assertContains('<div class="results_seriesnumber">B</div>', $body);
        $this->assertContains('<div class="results_seriesnumber">A</div>', $body);
        $this->assertContains('/series_logos/5/400_100.png', $body);
        $this->assertContains('Lorem ipsum dolor sit amet,', $body);

        // pagination links
        $count = substr_count($body, '/solrsearch/index/search/searchtype/series/id/5/start/0/rows/10"');
        $this->assertTrue($count == 4);
    }

    public function testSeriesSearchRespectsDefaultDocSortOrder() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/1');
        $this->assertResponseCode(200);

        $responseBody = $this->getResponse()->getBody();

        $this->assertContains('/solrsearch/index/search/searchtype/series/id/1/start/0/rows/10/sortfield/seriesnumber/sortorder/asc" ', $responseBody);
        $this->assertNotContains('/solrsearch/index/search/searchtype/series/id/1/start/0/rows/10/sortfield/seriesnumber/sortorder/desc" ', $responseBody);

        $responseBody = $this->getResponse()->getBody();
        $seriesIds = array(146, 93, 92, 94, 91);
        foreach ($seriesIds as $seriesId) {
            preg_match("/\/frontdoor\/index\/index.*\/docId\/$seriesId/", $responseBody, $matches, PREG_OFFSET_CAPTURE);
            $this->assertNotEmpty($matches, "Document $seriesId not found!");
            $responseBody = substr($responseBody, $matches[0][1]);
        }
    }

    public function testSeriesActionRespectsAscendingDocSortOrder() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/1/start/0/rows/10/sortfield/seriesnumber/sortorder/asc');
        $this->assertResponseCode(200);

        $responseBody = $this->getResponse()->getBody();

        $this->assertContains('/solrsearch/index/search/searchtype/series/id/1/start/0/rows/10/sortfield/seriesnumber/sortorder/desc" ', $responseBody);
        $this->assertNotContains('/solrsearch/index/search/searchtype/series/id/1/start/0/rows/10/sortfield/seriesnumber/sortorder/asc" ', $responseBody);

        $responseBody = $this->getResponse()->getBody();
        $seriesIds = array_reverse(array(146, 93, 92, 94, 91));
        foreach ($seriesIds as $seriesId) {
            preg_match("/\/frontdoor\/index\/index.*\/docId\/$seriesId/", $responseBody, $matches, PREG_OFFSET_CAPTURE);
            $this->assertNotEmpty($matches, "Document $seriesId not found!");
            $responseBody = substr($responseBody, $matches[0][1]);
        }
    }

    public function testSeriesActionRespectsDescendingDocSortOrder() {
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/1/start/0/rows/10/sortfield/seriesnumber/sortorder/desc');
        $this->assertResponseCode(200);

        $responseBody = $this->getResponse()->getBody();

        $this->assertContains('/solrsearch/index/search/searchtype/series/id/1/start/0/rows/10/sortfield/seriesnumber/sortorder/asc" ', $responseBody);
        $this->assertNotContains('/solrsearch/index/search/searchtype/series/id/1/start/0/rows/10/sortfield/seriesnumber/sortorder/desc" ', $responseBody);

        $seriesIds = array(146, 93, 92, 94, 91);
        foreach ($seriesIds as $seriesId) {
            preg_match("/\/frontdoor\/index\/index.*\/docId\/$seriesId/", $responseBody, $matches, PREG_OFFSET_CAPTURE);
            $this->assertNotEmpty($matches, "Document $seriesId not found!");
            $responseBody = substr($responseBody, $matches[0][1]);
        }
    }

    /**
     * Regression test for OPUSVIER-2434
     */
    public function testInvalidSearchQueryReturns500() {
        $this->markTestSkipped('TODO - query seems to be processed without exception - check');

        $this->requireSolrConfig();

        $this->dispatch('/solrsearch/index/search/searchtype/simple/start/0/rows/10/query/"\""');

        $body = $this->getResponse()->getBody();
        $this->assertNotContains('Application_Exception: error_search_unavailable', $body);
        $this->assertContains('Application_SearchException: error_search_invalidquery', $body);
        $this->assertEquals(500, $this->getResponse()->getHttpResponseCode());
    }

    public function testUnavailableSolrServerReturns503() {
        $this->requireSolrConfig();

        // manipulate solr configuration
        $config = Zend_Registry::get('Zend_Config');

        $host = $config->searchengine->solr->default->service->default->endpoint->primary->host;
        $port = $config->searchengine->solr->default->service->default->endpoint->primary->port;
        $oldValue = $config->searchengine->solr->default->service->default->endpoint->primary->path;
        $config->searchengine->solr->default->service->default->endpoint->primary->path = '/solr/corethatdoesnotexist';
        Zend_Registry::set('Zend_Config', $config);

        $this->dispatch('/solrsearch/browse/doctypes');

        $body = $this->getResponse()->getBody();
        $this->assertNotContains("http://${host}:${port}/solr/corethatdoesnotexist", $body);
        $this->assertContains('The search service is currently not available.', $body);
        $this->assertResponseCode(503);

        // restore configuration
        $config = Zend_Registry::get('Zend_Config');
        $config->searchengine->solr->default->service->default->endpoint->primary->path = $oldValue;
        Zend_Registry::set('Zend_Config', $config);
    }

    /**
     * test for OPUSVIER-2475
     */
    public function testCatchAllSearchConsidersIdentifiers() {
        $this->requireSolrConfig();

        // create a test doc with all available identifier types
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setLanguage('eng');
        $title = new Opus_Title();
        $title->setValue('test document for OPUSVIER-2475');
        $title->setLanguage('eng');
        $doc->setTitleMain($title);

        $id = new Opus_Identifier();
        $field = $id->getField('Type');
        $identifierTypes = array_keys($field->getDefault());

        foreach ($identifierTypes as $identifierType) {
            $doc->addIdentifier()
                ->setType($identifierType)
                ->setValue($identifierType . '-opusvier-2475');
        }
        $doc->store();

        // search for document based on identifiers
        foreach ($identifierTypes as $identifierType) {
            $searchString = $identifierType . '-opusvier-2475';
            $this->dispatch('/solrsearch/index/search/searchtype/simple/query/' . $searchString);

            $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
            $this->assertContains('test document for OPUSVIER-2475', $this->getResponse()->getBody());

            $this->getResponse()->clearAllHeaders();
            $this->getResponse()->clearBody();
        }
    }

    /**
     * test for OPUSVIER-2484 and regression test for OPUSVIER-2539
     */
    public function testCatchAllSearchConsidersAllPersons() {
        $this->requireSolrConfig();

        // create a test doc with all available person types
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setLanguage('eng');
        $title = new Opus_Title();
        $title->setValue('test document for OPUSVIER-2484');
        $title->setLanguage('eng');
        $doc->setTitleMain($title);

        $person = new Opus_Person();
        $person->setLastName('personauthor-opusvier-2484');
        $doc->addPersonAuthor($person);

        $person = new Opus_Person();
        $person->setLastName('personadvisor-opusvier-2484');
        $doc->addPersonAdvisor($person);

        $person = new Opus_Person();
        $person->setLastName('personcontributor-opusvier-2484');
        $doc->addPersonContributor($person);

        $person = new Opus_Person();
        $person->setLastName('personeditor-opusvier-2484');
        $doc->addPersonEditor($person);

        $person = new Opus_Person();
        $person->setLastName('personreferee-opusvier-2484');
        $doc->addPersonReferee($person);

        $person = new Opus_Person();
        $person->setLastName('personother-opusvier-2484');
        $doc->addPersonOther($person);

        $person = new Opus_Person();
        $person->setLastName('persontranslator-opusvier-2484');
        $doc->addPersonTranslator($person);

        // nach Submitter kann nicht gesucht werden
        $person = new Opus_Person();
        $person->setLastName('personsubmitter-opusvier-2484');
        $doc->addPersonSubmitter($person);

        $doc->store();

        // search for document based on persons
        $persons = array(
            'personauthor-opusvier-2484',
            'personadvisor-opusvier-2484',
            'personcontributor-opusvier-2484',
            'personeditor-opusvier-2484',
            'personreferee-opusvier-2484',
            'personother-opusvier-2484',
            'persontranslator-opusvier-2484',
        );
        foreach ($persons as $person) {
            $this->dispatch('/solrsearch/index/search/searchtype/simple/query/' . $person);

            $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
            $this->assertContains('test document for OPUSVIER-2484', $this->getResponse()->getBody());
            $this->assertContains($person, $this->getResponse()->getBody());

            $this->getResponse()->clearAllHeaders();
            $this->getResponse()->clearBody();
        }

        $this->dispatch('/solrsearch/index/search/searchtype/simple/query/personsubmitter-opusvier-2484');

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        // search should not return the test document
        $this->assertNotContains('test document for OPUSVIER-2484', $this->getResponse()->getBody());
    }

    public function testFacetLimitWithDefaultSetting() {
        $config = Zend_Registry::get('Zend_Config');

        $numOfSubjects = 20;
        $doc = $this->addSampleDocWithMultipleSubjects($numOfSubjects);

        $this->dispatch('/solrsearch/index/search/searchtype/simple/query/facetlimittestwithsubjects-opusvier2610');

        for ($index = 0; $index < $config->searchengine->solr->globalfacetlimit; $index++) {
            $path = '/solrsearch/index/search/searchtype/simple/query/facetlimittestwithsubjects-opusvier2610/start/0/rows/10/subjectfq/subject';
            if ($index < 10) {
                $path .= '0';
            }
            $this->assertContains($path . $index, $this->getResponse()->getBody());
        }
        for ($index = $config->searchengine->solr->globalfacetlimit; $index < $numOfSubjects; $index++) {
            $path = '/solrsearch/index/search/searchtype/simple/query/facetlimittestwithsubjects-opusvier2610/start/0/rows/10/subjectfq/subject';
            if ($index < 10) {
                $path .= '0';
            }
            $this->assertNotContains($path . $index, $this->getResponse()->getBody());
        }
    }

    public function testFacetLimitWithGlobalSetting() {
        // manipulate application configuration
        $config = Zend_Registry::get('Zend_Config');
        $limit = null;
        if (isset($config->searchengine->solr->globalfacetlimit)) {
            $limit = $config->searchengine->solr->globalfacetlimit;
        }
        $config->searchengine->solr->globalfacetlimit = 5;
        Zend_Registry::set('Zend_Config', $config);

        $numOfSubjects = 10;
        $doc = $this->addSampleDocWithMultipleSubjects($numOfSubjects);

        $this->dispatch('/solrsearch/index/search/searchtype/simple/query/facetlimittestwithsubjects-opusvier2610');

        // undo configuration manipulation
        $config = Zend_Registry::get('Zend_Config');
        $config->searchengine->solr->globalfacetlimit = $limit;
        Zend_Registry::set('Zend_Config', $config);

        for ($index = 0; $index < 5; $index++) {
            $this->assertContains('/solrsearch/index/search/searchtype/simple/query/facetlimittestwithsubjects-opusvier2610/start/0/rows/10/subjectfq/subject0' . $index, $this->getResponse()->getBody());
        }
        for ($index = 5; $index < $numOfSubjects; $index++) {
            $this->assertNotContains('/solrsearch/index/search/searchtype/simple/query/facetlimittestwithsubjects-opusvier2610/start/0/rows/10/subjectfq/subject0' . $index, $this->getResponse()->getBody());
        }
    }

    public function testFacetLimitWithLocalSettingForSubjectFacet() {
        // manipulate application configuration
        $config = Zend_Registry::get('Zend_Config');
        $limit = null;
        $oldConfig = null;
        if (isset($config->searchengine->solr->facetlimit->subject)) {
            $limit = $config->searchengine->solr->facetlimit->subject;
        }
        else {
            $config = new Zend_Config(array(
                'searchengine' => array(
                    'solr' => array(
                        'facetlimit' => array(
                            'subject' => 5)))), true);
            $oldConfig = Zend_Registry::get('Zend_Config');
            // Include the above made configuration changes in the application configuration.
            $config->merge(Zend_Registry::get('Zend_Config'));
        }
        Zend_Registry::set('Zend_Config', $config);

        $numOfSubjects = 10;
        $doc = $this->addSampleDocWithMultipleSubjects($numOfSubjects);

        $this->dispatch('/solrsearch/index/search/searchtype/simple/query/facetlimittestwithsubjects-opusvier2610');

        // undo configuration manipulation
        $config = Zend_Registry::get('Zend_Config');
        if (!is_null($oldConfig)) {
            $config = $oldConfig;
        }
        else {
            $config->searchengine->solr->facetlimit->subject = $limit;
        }
        Zend_Registry::set('Zend_Config', $config);

        for ($index = 0; $index < 5; $index++) {
            $this->assertContains('/solrsearch/index/search/searchtype/simple/query/facetlimittestwithsubjects-opusvier2610/start/0/rows/10/subjectfq/subject0' . $index, $this->getResponse()->getBody());
        }
        for ($index = 5; $index < $numOfSubjects; $index++) {
            $this->assertNotContains('/solrsearch/index/search/searchtype/simple/query/facetlimittestwithsubjects-opusvier2610/start/0/rows/10/subjectfq/subject0' . $index, $this->getResponse()->getBody());
        }
    }

    public function testFacetExtenderLinkIncludesTarget() {
        $this->dispatch('solrsearch/index/search/searchtype/all');
        // ends-with function would be more accurate, but currently not supported
        $this->assertXPath('//div[@id="author_facet_facet"]//a[contains(@href, "#author_facet_facet")]');
        $this->assertXPath('//div[@id="year_facet"]//a[contains(@href, "#year_facet")]');
        $this->assertXPath('//div[@id="doctype_facet"]//a[contains(@href, "#doctype_facet")]');
        $this->assertXPath('//div[@id="subject_facet"]//a[contains(@href, "#subject_facet")]');
        $this->assertXPath('//div[@id="institute_facet"]//a[contains(@href, "#institute_facet")]');
    }

    private function addSampleDocWithMultipleSubjects($numOfSubjects = 0) {
        $doc = $this->createTestDocument();
        $doc->setServerState('published');
        $doc->setLanguage('eng');
        $title = new Opus_Title();
        $title->setValue('facetlimittestwithsubjects-opusvier2610');
        $title->setLanguage('eng');
        $doc->addTitleMain($title);

        for ($index = 0; $index < $numOfSubjects; $index++) {
            $subject = new Opus_Subject();
            if ($index < 10) {
                $subject->setValue('subject' . '0' . $index);
            }
            else {
                $subject->setValue('subject' . $index);
            }
            $subject->setType('uncontrolled');
            $subject->setLanguage('eng');
            $doc->addSubject($subject);
        }

        $doc->store();
        return $doc;
    }

    public function testFacetSortLexicographicallyForInstituteFacet() {
        // manipulate application configuration
        $oldConfig = Zend_Registry::get('Zend_Config');

        $config = Zend_Registry::get('Zend_Config');
        if (isset($config->searchengine->solr->sortcrit->institute)) {
            $config->searchengine->solr->sortcrit->institute = 'lexi';
        }
        else {
            $config = new Zend_Config(array(
                'searchengine' => array(
                    'solr' => array(
                        'sortcrit' => array(
                            'institute' => 'lexi')))), true);
            $oldConfig = Zend_Registry::get('Zend_Config');
            // Include the above made configuration changes in the application configuration.
            $config->merge(Zend_Registry::get('Zend_Config'));
        }
        Zend_Registry::set('Zend_Config', $config);

        $this->dispatch('/solrsearch/index/search/searchtype/all');

        // undo configuration manipulation
        Zend_Registry::set('Zend_Config', $oldConfig);

        $searchStrings = array(
            'Abwasserwirtschaft und Gewässerschutz B-2',
            'Bauwesen',
            'Bibliothek',
            'Biomechanik M-3',
            'Bioprozess- und Biosystemtechnik V-1',
            'Elektrotechnik und Informationstechnik',
            'Entwerfen von Schiffen und Schiffssicherheit M-6',
            'Fluiddynamik und Schiffstheorie M-8',
            'Geotechnik und Baubetrieb B-5',
            'Hochfrequenztechnik E-3');

        $this->assertPositions($this->getResponse()->getBody(), $searchStrings, 'id="institute_facet"');

        $this->dispatch('/solrsearch/index/search/searchtype/all');

        $searchStrings = array(
            'Technische Universität Hamburg-Harburg',
            'Entwerfen von Schiffen und Schiffssicherheit M-6',
            'Keramische Hochleistungswerkstoffe M-9',
            'Bibliothek',
            'Elektrotechnik und Informationstechnik',
            'Maschinenbau',
            'Abwasserwirtschaft und Gewässerschutz B-2',
            'Bauwesen',
            'Biomechanik M-3',
            'Verfahrenstechnik');
        $this->assertPositions($this->getResponse()->getBody(), $searchStrings, 'id="institute_facet"');
    }

    public function testFacetSortForYearInverted() {
        // manipulate application configuration
        $oldConfig = Zend_Registry::get('Zend_Config');

        $config = Zend_Registry::get('Zend_Config');
        if (isset($config->searchengine->solr->sortcrit->year_inverted)) {
            $config->searchengine->solr->sortcrit->year_inverted = 'lexi';
        }
        else {
            $config = new Zend_Config(array(
                'searchengine' => array(
                    'solr' => array(
                        'sortcrit' => array(
                            'year_inverted' => 'lexi')))), true);
            // Include the above made configuration changes in the application configuration.
            $config->merge(Zend_Registry::get('Zend_Config'));
        }

        if (isset($config->searchengine->solr->facets)) {
            $config->searchengine->solr->facets = 'year_inverted,doctype,author_facet,language,has_fulltext,belongs_to_bibliography,subject,institute';
        }
        else {
            $config = new Zend_Config(array(
                'searchengine' => array(
                    'solr' => array(
                        'facets' => 'year_inverted,doctype,author_facet,language,has_fulltext,belongs_to_bibliography,subject,institute'))), true);
            // Include the above made configuration changes in the application configuration.
            $config->merge(Zend_Registry::get('Zend_Config'));
        }
        Zend_Registry::set('Zend_Config', $config);

        $this->dispatch('/solrsearch/index/search/searchtype/all');

        // undo configuration manipulation
        Zend_Registry::set('Zend_Config', $oldConfig);

        $searchStrings = array(
            '2013',
            '2012',
            '2011',
            '2010',
            '2009',
            '2008',
            '2007',
            '2005',
            '2004',
            '2003');
        $this->assertPositions($this->getResponse()->getBody(), $searchStrings, 'id="year_facet"');

        $this->resetResponse();
        $this->dispatch('/solrsearch/index/search/searchtype/all');
        $searchStrings = array(
            '2011',
            '2009',
            '2010',
            '1978',
            '2008',
            '2012',
            '1979',
            '1962',
            '1963',
            '1975');

        // Wenn es hier bei den Tests Probleme gibt AssumptionChecker für die Diagnose verwenden (in tests/support).
        $this->assertPositions($this->getResponse()->getBody(), $searchStrings, 'id="year_facet"');
    }

    private function assertPositions($response, $searchStrings, $startString) {
        $startPos = strpos($response, $startString);
        $this->assertFalse($startPos === false);
        $lastPos = $startPos;
        $loopComplete = true;
        for ($i = 0; $i < 10; $i++) {
            $lastPos = strpos($response, '>' . $searchStrings[$i] . '</a>', $lastPos);
            $this->assertFalse($lastPos === false, "'" . $searchStrings[$i] . '\' not found in year facet list (iteration ' . $i . ')');
            if ($lastPos === false) {
                break;
                $loopComplete = false;
            }
        }
        $this->assertTrue($loopComplete);
    }

    private function getNumOfHits() {
        $document = new DOMDocument();
        $document->loadHTML($this->getResponse()->getBody());
        $element = $document->getElementById('search-result-numofhits');
        $this->assertNotNull($element, '#search-result-numofhits does not exist in response body');
        $this->assertNotNull($element->firstChild, 'first child does not exist in response body');
        return $element->firstChild->textContent;
    }

    /**
     * Regression Test for OPUSVIER-3131
     */
    public function testInvalidSearchRequestPageTitle() {
        $this->dispatch('/solrsearch/index/invalidsearchterm/searchtype/simple');
        $this->assertNotContains('solrsearch_title_invalidsearchterm', $this->getResponse()->getBody());
    }

    /**
     * Asserts, that in browsing the documents are sorted by server_date_published.
     * Opusvier-1989.
     */
    public function testSortOrderOfDocumentsInBrowsing() {
        $olderDoc = $this->createTestDocument();
        $olderDoc->setServerState('published');
        $date = new Opus_Date();
        $date->setNow();
        $date->setDay($date->getDay() - 1);
        $olderDoc->setServerDatePublished($date);
        $olderDoc->setType('article');
        $olderDocId = $olderDoc->store();

        $newerDoc = $this->createTestDocument();
        $newerDoc->setServerState('published');
        $newerDoc->setType('article');
        $newerDocId = $newerDoc->store();

        $this->dispatch('/solrsearch/index/search/searchtype/simple/query/*%3A*/browsing/true/doctypefq/article');

        $responseBody = $this->getResponse()->getBody();

        preg_match("$/frontdoor/index/index.*/docId/$olderDocId$", $responseBody, $matches, PREG_OFFSET_CAPTURE);
        $this->assertNotEmpty($matches, "Document $olderDocId not found!");
        $olderDocPosition = $matches[0][1];

        preg_match("$/frontdoor/index/index.*/docId/$newerDocId$", $responseBody, $matches, PREG_OFFSET_CAPTURE);
        $this->assertNotEmpty($matches, "Document $newerDocId not found!");
        $newerDocPosition = $matches[0][1];

        $this->assertTrue($newerDocPosition < $olderDocPosition);
    }

    /**
     * Tests, that the sortfields in browsing are still working.
     * see Opusvier-3334.
     */
    public function testSortOrderOfDocumentsInBrowsingWithSortfield() {
        $olderDoc = $this->createTestDocument();
        $olderDoc->setServerState('published');
        $olderDoc->setLanguage('eng');
        $date = new Opus_Date();
        $date->setNow();
        $date->setDay($date->getDay() - 1);
        $olderDoc->setServerDatePublished($date);
        $olderDoc->setType('article');

        $title = new Opus_Title();
        $title->setValue('zzzOlderDoc'); // 'zzz' to show the document at the first page
        $title->setLanguage('eng');
        $olderDoc->addTitleMain($title);
        $olderDocId = $olderDoc->store();

        $newerDoc = $this->createTestDocument();
        $newerDoc->setServerState('published');
        $newerDoc->setLanguage('eng');
        $newerDoc->setType('article');
        $title = new Opus_Title();
        $title->setValue('zzzNewerDoc');
        $title->setLanguage('eng');
        $newerDoc->addTitleMain($title);
        $newerDocId = $newerDoc->store();

        $this->dispatch('/solrsearch/index/search/searchtype/simple/query/*%3A*/browsing/true/doctypefq/article/sortfield/title/sortorder/desc');

        $responseBody = $this->getResponse()->getBody();

        preg_match("$/frontdoor/index/index.*/docId/$olderDocId$", $responseBody, $matches, PREG_OFFSET_CAPTURE);
        $this->assertNotEmpty($matches, "Document $olderDocId not found!");
        $olderDocPosition = $matches[0][1];

        preg_match("$/frontdoor/index/index.*/docId/$newerDocId$", $responseBody, $matches, PREG_OFFSET_CAPTURE);
        $this->assertNotEmpty($matches, "Document $newerDocId not found!");
        $newerDocPosition = $matches[0][1];

        $this->assertTrue($newerDocPosition > $olderDocPosition, "Documents are not sorted by sortfield (title).");
    }

    /**
     * Authorfacette aufgeklappt -> '- less' soll angezeigt werden.
     * Test für OPUSVIER-1713.
     */
    public function testAuthorFacetOpen() {
        $this->useEnglish();
        $this->dispatch('/solrsearch/index/search/searchtype/all/start/0/rows/10/facetNumber_author_facet/all');
        $this->assertXpathCount('//a[contains(@href, "author_facetfq")]', 104); // stimmt für Testdaten TODO über SQL
        $this->assertQueryContentContains('//a', 'Stecher, Wilfried');
        $this->assertQueryContentContains('//a', 'Walruss, Wally');
        $this->assertQueryContentContains('//a', 'Scheinpflug, M.');
        $this->assertQueryContentContains("//div[@id='author_facet_facet']/div/a", ' - less');
    }

    /**
     * Alle Facetten zugeklappt -> '+ more' Link soll für die Authorenliste angezeigt werden. Für BelongsToBibliography
     * und hasFulltext nicht.
     * Test für OPUSVIER-1713.
     */
    public function testAuthorFacetClosed() {
        $this->useEnglish();
        $this->dispatch('/solrsearch/index/search/searchtype/all/start/0/rows/10');
        $this->assertQueryContentContains('//a', 'Doe, John');
        $this->assertQueryContentContains('//a', 'Schneider, Gerold A.');
        $this->assertNotQueryContentContains('//a', 'Stecher, Wilfried');
        $this->assertNotQueryContentContains('//a', 'Walruss, Wally');
        $this->assertNotQueryContentContains('//a', 'Scheinpflug, M.');
        $this->assertQueryContentContains("//div[@id='author_facet_facet']/div/a", ' + more');
        $this->assertNotQueryContentContains("//div[@id='has_fulltext_facet']//a", ' + more');
        $this->assertNotQueryContentContains("//div[@id='belongs_to_bibliography_facet']//a", ' + more');
        $this->assertNotQueryContentContains("//div[@id='language_facet']//a", ' + more');
    }

    /**
     * Wenn in der config.ini weniger oder mehr Parameter als üblich (oder als in searchengine->solr->globalFacetLimit)
     * angegeben sind, muss der FacetExtender trotzdem noch angezeigt werden.
     */
    public function testFacetExtenderWithVariousConfigFacetLimits() {
        $this->useEnglish();
        $config = Zend_Registry::get('Zend_Config');
        $config->merge(new Zend_Config(array('searchengine' =>
            array('solr' =>
                array('facetlimit' =>
                    array('author_facet' => 3,
                          'year'         => 15))))));

        $this->dispatch('/solrsearch/index/search/searchtype/all/');
        $this->assertQueryContentContains("//div[@id='author_facet_facet']/div/a", ' + more');
        $this->assertQueryContentContains("//div[@id='year_facet']/div/a", ' + more');
    }

    /**
     * Redirect from search result with searchtype=latest should work with mixture of parameters.
     * Parameter 'searchtype' should not be interpreted as 'latest/export/xml/stylesheet/example'.
     * Regressiontest für OPUSVIER-2742.
     */
    public function testRedirectToExportFromSearchtypeLatestWithParameterTypeMixture() {
        $this->dispatch('/solrsearch/index/search?rows=10&searchtype=latest/export/xml/stylesheet/example');
        $this->assertRedirectTo('/export/index/index/rows/10/searchtype/latest/export/xml/stylesheet/example');
    }

    /**
     * Redirect from search result with searchtype=latest should work for get-Parameters.
     * Regressiontest für OPUSVIER-2742.
     */
    public function testRedirectToExportFromSearchtypeLatestWithGetParameters() {
        $this->dispatch('/solrsearch/index/search?rows=10&searchtype=latest&export=xml&stylesheet=example');
        $this->assertRedirectTo('/export/index/index/rows/10/searchtype/latest/export/xml/stylesheet/example');
    }

    /**
     * Redirect from search result with searchtype=latest should work for parameters before get-statement
     * Regressiontest für OPUSVIER-2742.
     */
    public function testRedirectToExportFromSearchtypeLatestWithParametersBeforeGet() {
        $this->dispatch('/solrsearch/index/search/export/xml/stylesheet/example?rows=10&searchtype=latest');
        $this->assertRedirectTo('/export/index/index/export/xml/stylesheet/example/rows/10/searchtype/latest');
    }

    /**
     * Important: parameter 'rows' should not be deleted (OPUSVIER-2742).
     */
    public function testRedirectToExportWithRowsParameter() {
        $this->dispatch('/solrsearch/index/search/searchtype/latest/start/0/rows/15/export/xml/stylesheet/example');
        $this->assertRedirectTo('/export/index/index/searchtype/latest/rows/15/export/xml/stylesheet/example');
    }

    /**
     * Important: parameter 'rows' should be appended (OPUSVIER-2742).
     */
    public function testRedirectToExportWithoutRowsParameter() {
        $this->dispatch('/solrsearch/index/search/searchtype/latest/export/xml/stylesheet/example');
        $this->assertRedirectTo('/export/index/index/searchtype/latest/export/xml/stylesheet/example/rows/10');
    }

    /**
     * In refined facet, the facet value extender should not exist.
     * OPUSVIER-3351.
     */
    public function testHideFacetExtenderInRefinedFacets() {
        $this->dispatch('/solrsearch/index/search/searchtype/all/rows/10/start/0/institutefq/Technische+Universität+Hamburg-Harburg');
        $this->assertNotXpath('//div[@id="institute_facet"]/div[@class="facetValueExtender"]');
    }

    /**
     * XML export link should not be present for regular users.
     *
     * TODO not really the original idea - problem is that config changes are not effective after bootstrapping
     */
    public function testXmlExportButtonNotPresent() {
        $this->enableSecurity();
        $this->dispatch('/solrsearch/index/search/searchtype/all');
        $this->assertNotQuery('//a[@href="/export/index/index/searchtype/all/export/xml/stylesheet/example"]');
    }

    /**
     * The export functionality should be available for admins.
     */
    public function testXmlExportButtonPresentForAdmin() {
        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');

        $this->dispatch('/solrsearch/index/search/searchtype/all');
        $this->assertQuery('//a[@href="/export/index/index/searchtype/all/export/xml/stylesheet/example"]');
    }

    /**
     * The export functionality should be available for admins also in latest search.
     *
     * TODO fix test
     */
    public function testXmlExportButtonPresentForAdminInLatestSearch() {
        $this->markTestSkipped('TODO - config change does not work after bootstrapping in this case');

        $this->enableSecurity();
        $this->loginUser('admin', 'adminadmin');

        Zend_Registry::get('Zend_Config')->merge(new Zend_Config(array(
            'export' => array('stylesheet' => array('search' => 'example')),
            'searchengine' => array('solr' => array('numberOfDefaultSearchResults' => 10))
        )));

        $this->dispatch('/solrsearch/index/search/searchtype/latest');
        $this->assertQuery('//a[@href="/solrsearch/index/search/searchtype/latest/rows/10/export/xml/stylesheet/example"]');
    }

    /**
     * The export functionality should not be present for guests.
     */
    public function testXmlExportButtonNotPresentForGuest() {
        $this->enableSecurity();
        $config = Zend_Registry::get('Zend_Config');
        $config->merge(new Zend_Config(array('export' => array('stylesheet' => array('search' => 'example')))));
        $this->dispatch('/solrsearch/index/search/searchtype/all');
        $this->assertFalse(Opus_Security_Realm::getInstance()->checkModule('export'));
        $this->assertNotQuery('//a[@href="/solrsearch/index/search/searchtype/all/export/xml/stylesheet/example"]');
    }

    public function testDisableEmptyCollectionTrue() {
        Zend_Registry::get('Zend_Config')->merge(
            new Zend_Config(array('browsing' => array('disableEmptyCollections' => 1)))
        );

        $this->dispatch('/solrsearch/index/search/searchtype/collection/id/2');

        $this->assertNotQuery('//a[@href="/solrsearch/index/search/searchtype/collection/id/6"]');
        $this->assertQueryContentContains('//a[@href="/rss/index/index/searchtype/collection/id/6"]/..',
            '3 Sozialwissenschaften');
    }

    public function testDisableEmptyCollectionsFalse() {
        Zend_Registry::get('Zend_Config')->merge(
            new Zend_Config(array('browsing' => array('disableEmptyCollections' => 0)))
        );

        $this->dispatch('/solrsearch/index/search/searchtype/collection/id/2');

        $this->assertQuery('//a[@href="/solrsearch/index/search/searchtype/collection/id/6"]');
        $this->assertQueryContentContains('//a[@href="/solrsearch/index/search/searchtype/collection/id/6"]',
            'Sozialwissenschaften');
        $this->assertQueryContentContains('//a[@href="/solrsearch/index/search/searchtype/collection/id/6"]/..',
            '(0)');
    }

}
