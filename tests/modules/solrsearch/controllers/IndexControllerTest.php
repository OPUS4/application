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
 * @package     Module_Solrsearch
 * @author      Julian Heise <heise@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Solrsearch_IndexControllerTest extends ControllerTestCase {

    private function doStandardControllerTest($url, $controller, $action) {
        $this->dispatch($url);
        $this->assertResponseCode(200);
        if($controller != null)
            $this->assertController($controller);
        if($action != null)
            $this->assertAction($action);
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

    public function testPhraseQueriesWithWildcards() {
        $d = new Opus_Document();
        $d->setServerState('published');
        $d->setLanguage('eng');
        $d->addTitleMain()->setValue('testphrasequerieswithwildcard*s')->setLanguage('eng');
        $d->store();

        $testCnt = 1;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"testphrasequerieswith*"', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 0, "($testCnt) result is not empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"testphrasequerieswithwildcard*"', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 1, "($testCnt) result is empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"testphrasequerieswithwildcard*s"', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 1, "($testCnt) result is empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"TESTPHRASEQUERIESWITH*"', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 0, "($testCnt) result is not empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"TESTPHRASEQUERIESWITHWILDCARD*"', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 1, "($testCnt) result is empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/"TESTPHRASEQUERIESWITHWILDCARD*S"', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 1, "($testCnt) result is empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/testphrasequerieswith*', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 1, "($testCnt) result is empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/testphrasequerieswithwildcard*', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 1, "($testCnt) result is empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/testphrasequerieswithwildcard*s', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 1, "($testCnt) result is empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/TESTPHRASEQUERIESWITH*', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 1, "($testCnt) result is empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/TESTPHRASEQUERIESWITHWILDCARD*', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 1, "($testCnt) result is empty");
        $testCnt++;

        $this->getResponse()->clearBody();
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/TESTPHRASEQUERIESWITHWILDCARD*S', null, null);
        $this->assertTrue(substr_count($this->getResponse()->getBody(), 'result_box') == 1, "($testCnt) result is empty");
        $testCnt++;

        // cleanup
        $d->deletePermanent();
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
        $this->assertContains('<h3>75', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2147 (doctype browsing)
     */
    public function testPaginationBarContainsOverallNumberOfHitsInDoctypeBrowsing() {
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/*%3A*/browsing/true/doctypefq/report', null, null);
        $this->assertContains('<h3>51', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2144
     */
    public function testLastPageUrlEqualsNextPageUrlDocTypeArticle() {
        $docFinder = new Opus_DocumentFinder();
        $docFinder->setType('article')->setServerState('published');
        $this->assertEquals(20, $docFinder->count(), "Test data changed!");

        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/*%3A*/browsing/true/doctypefq/article', null, null);
        $this->assertTrue(4 == substr_count($this->getResponse()->getBody(), '/solrsearch/index/search/searchtype/simple/query/%2A%3A%2A/browsing/true/doctypefq/article/start/10/rows/10">'));
        $this->assertNotContains('solrsearch/index/search/searchtype/simple/query/%2A%3A%2A/browsing/true/doctypefq/doctoralthesis/start/19/rows/10">', $this->getResponse()->getBody());
        $this->assertContains('<h3>20', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2144
     */
    public function testLastPageUrlEqualsNextPageUrlDocTypeDoctoralThesis() {
        $this->doStandardControllerTest('/solrsearch/index/search/searchtype/simple/query/*%3A*/browsing/true/doctypefq/doctoralthesis', null, null);
        $this->assertTrue(4 == substr_count($this->getResponse()->getBody(), '/solrsearch/index/search/searchtype/simple/query/%2A%3A%2A/browsing/true/doctypefq/doctoralthesis/start/10/rows/10">'));
        $this->assertNotContains('solrsearch/index/search/searchtype/simple/query/%2A%3A%2A/browsing/true/doctypefq/doctoralthesis/start/17/rows/10">', $this->getResponse()->getBody());
        $this->assertContains('<h3>18', $this->getResponse()->getBody());
    }

    /**
     * Regression test for OPUSVIER-2134
     */
    public function testCatchAllSearch() {
        $d = new Opus_Document();
        $d->setServerState('published');
        $d->setLanguage('eng');
        $d->addTitleParent()->setValue('testcatchallsearch_title_parent')->setLanguage('eng');
        $d->addTitleAdditional()->setValue('testcatchallsearch_title_additional')->setLanguage('eng');
        $d->addTitleAdditional()->setValue('testcatchallsearch_title_sub')->setLanguage('eng');
        $d->setPublisherName('testcatchallsearch_publisher_name');
        $d->setPublisherPlace('testcatchallsearch_publisher_place');
        $d->setCreatingCorporation('testcatchallsearch_creating_corporation');
        $d->setContributingCorporation('testcatchallsearch_contributing_corporation');
        $d->store();

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
            $this->assertTrue(substr_count($this->getResponse()->getBody(), '<strong>1</strong>') == 4);
            $this->assertContains('<h3>1', $this->getResponse()->getBody());
            $this->getResponse()->clearBody();
        }

        // cleanup
        $d->deletePermanent();
    }

    public function testRssLinkIsDisplayedForSimpleSearch() {
        $this->dispatch('/solrsearch/index/search/searchtype/simple/start/0/rows/10/query/doe/sortfield/author/sortorder/asc/yearfq/2008');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/simple/query/doe/yearfq/2008" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
    }

    public function testRssLinkIsDisplayedForAdvancedSearch() {
        $this->dispatch('/solrsearch/index/search/searchtype/advanced/start/0/rows/20/sortfield/score/sortorder/desc/author/doe/authormodifier/contains_all/fulltext/test/fulltextmodifier/contains_all/subjectfq/eBook');
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
        $this->dispatch('/solrsearch/index/search/searchtype/series/id/1/start/0/rows/10/author_facetfq/John+Doe/sortfield/seriesnumber/sortorder/asc');
        $this->assertResponseCode(200);
        $this->assertContains('/rss/index/index/searchtype/series/id/1/author_facetfq/John+Doe" rel="alternate" type="application/rss+xml"', $this->getResponse()->getBody());
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

}
