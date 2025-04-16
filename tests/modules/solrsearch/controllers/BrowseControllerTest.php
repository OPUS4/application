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

use Opus\Common\Model\ModelException;
use Opus\Common\Series;

/**
 * @covers Solrsearch_BrowseController
 */
class Solrsearch_BrowseControllerTest extends ControllerTestCase
{
    /** @var string */
    protected $additionalResources = 'all';

    public function setUp(): void
    {
        parent::setUp();
        $this->requireSolrConfig();
    }

    public function testIndexAction()
    {
        $this->dispatch('/solrsearch/browse');
        $this->assertResponseCode(200);

        $this->assertXpath('//a[contains(@href, "solrsearch/index/search/searchtype/latest")]');
        $this->assertXpath('//a[contains(@href, "solrsearch/browse/doctypes")]');
        $this->assertXpath('//a[contains(@href, "solrsearch/browse/years")]');
    }

    public function testShowDoctypesDisabled()
    {
        $this->adjustConfiguration([
            'browsing' => ['showDocumentTypes' => 0],
        ]);

        $this->dispatch('/solrsearch/browse');
        $this->assertResponseCode(200);

        $this->assertXpath('//a[contains(@href, "solrsearch/index/search/searchtype/latest")]');
        $this->assertNotXpath('//a[contains(@href, "solrsearch/browse/doctypes")]');
        $this->assertXpath('//a[contains(@href, "solrsearch/browse/years")]');
    }

    public function testDoctypesActionEnabled()
    {
        $this->dispatch('/solrsearch/browse/doctypes');
        $this->assertResponseCode(200);

        $this->assertXpath('//div[@id = "content" and contains(@class, "solrsearch_browse_doctypes")]');
    }

    public function testDoctypesActionDisabled()
    {
        $this->adjustConfiguration([
            'browsing' => ['showDocumentTypes' => 0],
        ]);

        $this->dispatch('/solrsearch/browse/doctypes');
        $this->assertRedirect('/solrsearch/browse/index');
    }

    public function testSeriesAction()
    {
        $this->dispatch('/solrsearch/browse/series');
        $responseBody = $this->getResponse()->getBody();
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/1', $responseBody);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/4', $responseBody);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/2', $responseBody);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/5', $responseBody);
        $this->assertContains('/solrsearch/index/search/searchtype/series/id/6', $responseBody);
        $this->assertNotContains('/solrsearch/index/search/searchtype/series/id/3', $responseBody);
        $this->assertNotContains('/solrsearch/index/search/searchtype/series/id/7', $responseBody);
        $this->assertNotContains('/solrsearch/index/search/searchtype/series/id/8', $responseBody);
        $this->assertResponseCode(200);
    }

    public function testSeriesActionWithUnvisibleSeries()
    {
        $visibilities = $this->setAllSeriesToUnvisible();

        $this->dispatch('/solrsearch/browse/series');
        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/solrsearch/browse');

        $this->restoreSeriesVisibility($visibilities);
    }

    public function testSeriesActionWithOneVisibleSeriesWithoutAnyPublishedDocument()
    {
        $visibilities = $this->setAllSeriesToUnvisible();

        $d = $this->createTestDocument();
        $d->setServerState('unpublished');
        $d->store();

        $s = Series::get(7);
        $s->setVisible('1');
        $s->store();

        $d->addSeries($s)->setNumber('testSeriesAction-7');
        $d->store();

        $this->dispatch('/solrsearch/browse/series');

        $this->restoreSeriesVisibility($visibilities);

        $this->assertRedirect();
        $this->assertResponseLocationHeader($this->getResponse(), '/solrsearch/browse');
    }

    public function testSeriesActionWithOneVisibleSeriesWithOnePublishedDocument()
    {
        $visibilities = $this->setAllSeriesToUnvisible();

        $d = $this->createTestDocument();
        $d->setServerState('published');
        $d->store();

        $s = Series::get(7);
        $s->setVisible('1');
        $s->store();

        $d->addSeries($s)->setNumber('testSeriesAction-7');
        $d->store();

        $this->dispatch('/solrsearch/browse/series');

        $this->restoreSeriesVisibility($visibilities);

        $this->assertContains('/solrsearch/index/search/searchtype/series/id/7', $this->getResponse()->getBody());
        foreach (Series::getAll() as $series) {
            if ($series->getId() !== 7) {
                $this->assertNotContains(
                    '/solrsearch/index/search/searchtype/series/id/' . $series->getId(),
                    $this->getResponse()->getBody()
                );
            }
        }
        $this->assertResponseCode(200);
    }

    /**
     * @return array
     * @throws ModelException
     */
    private function setAllSeriesToUnvisible()
    {
        $visibilities = [];
        foreach (Series::getAll() as $seriesItem) {
            $visibilities[$seriesItem->getId()] = $seriesItem->getVisible();
            $seriesItem->setVisible(0);
            $seriesItem->store();
        }
        return $visibilities;
    }

    /**
     * @param array $visibilities
     * @throws ModelException
     */
    private function restoreSeriesVisibility($visibilities)
    {
        foreach (Series::getAll() as $seriesItem) {
            $seriesItem->setVisible($visibilities[$seriesItem->getId()]);
            $seriesItem->store();
        }
    }

    public function testSeriesActionRespectsSeriesSortOrder()
    {
        $this->dispatch('/solrsearch/browse/series');
        $this->assertResponseCode(200);
        $responseBody = $this->getResponse()->getBody();
        $seriesIds    = ['1', '4', '2', '5', '6'];
        foreach ($seriesIds as $seriesId) {
            $pos = strpos($responseBody, '/solrsearch/index/search/searchtype/series/id/' . $seriesId);
            $this->assertTrue($pos !== false);
            $responseBody = substr($responseBody, $pos);
        }
    }

    public function testSeriesActionRespectsSeriesSortOrderAfterManipulation()
    {
        $sortOrders = $this->getSortOrders();

        // reverse ordering of series
        foreach (Series::getAll() as $seriesItem) {
            $seriesItem->setSortOrder(10 - intval($sortOrders[$seriesItem->getId()]));
            $seriesItem->store();
        }

        $this->dispatch('/solrsearch/browse/series');
        $this->assertResponseCode(200);
        $responseBody = $this->getResponse()->getBody();
        $seriesIds    = ['6', '5', '2', '4', '1'];
        foreach ($seriesIds as $seriesId) {
            $pos = strpos($responseBody, '/solrsearch/index/search/searchtype/series/id/' . $seriesId);
            $this->assertTrue($pos !== false);
            $responseBody = substr($responseBody, $pos);
        }

        $this->setSortOrders($sortOrders);
    }

    public function testSeriesActionRespectsSeriesSortOrderIfItCoincidesBetweenTwoSeries()
    {
        $sortOrders = $this->getSortOrders();

        $s = Series::get(2);
        $s->setSortOrder(6);
        $s->store();

        $s = Series::get(6);
        $s->setSortOrder(0);
        $s->store();

        $this->dispatch('/solrsearch/browse/series');
        $this->assertResponseCode(200);
        $responseBody = $this->getResponse()->getBody();
        $seriesIds    = ['1', '6', '4', '2', '5'];
        foreach ($seriesIds as $seriesId) {
            $pos = strpos($responseBody, '/solrsearch/index/search/searchtype/series/id/' . $seriesId);
            $this->assertTrue($pos !== false);
            $responseBody = substr($responseBody, $pos);
        }

        $this->setSortOrders($sortOrders);
    }

    /**
     * @return array
     */
    private function getSortOrders()
    {
        $sortOrders = [];
        foreach (Series::getAll() as $seriesItem) {
            $sortOrders[$seriesItem->getId()] = $seriesItem->getSortOrder();
        }
        return $sortOrders;
    }

    /**
     * @param array $sortOrders
     * @throws ModelException
     */
    private function setSortOrders($sortOrders)
    {
        foreach (Series::getAll() as $seriesItem) {
            $seriesItem->setSortOrder($sortOrders[$seriesItem->getId()]);
            $seriesItem->store();
        }
    }

    public function testIndexActionDoesNotDisplaySeriesBrowsingLinkIfNothingToShow()
    {
        $visibilities = $this->setAllSeriesToUnvisible();
        $this->dispatch('/solrsearch/browse');
        $this->assertResponseCode(200);
        $this->assertNotContains('/solrsearch/browse/series">', $this->getResponse()->getBody());
        $this->restoreSeriesVisibility($visibilities);
    }

    /**
     * Regression test for OPUSVIER-2337
     */
    public function testUnavailableServiceReturnsHttpCode503()
    {
        $this->markTestSkipped('How to disable Solr?');

        $this->requireSolrConfig();

        $this->disableSolr();

        $this->dispatch('/solrsearch/browse/doctypes');

        $body = $this->getResponse()->getBody();
        // $this->assertNotContains("http://${host}:${port}/solr/corethatdoesnotexist", $body);
        $this->assertContains("exception 'Application_SearchException' with message 'error_search_unavailable'", $body);
        $this->assertResponseCode(503);
    }

    public function testYearsActionEnabled()
    {
        $this->dispatch('/solrsearch/browse/years');
        $this->assertResponseCode(200);

        $this->assertXpath('//div[@id = "content" and contains(@class, "solrsearch_browse_years")]');
    }

    public function testYearsActionDisabled()
    {
        $this->adjustConfiguration([
            'browsing' => ['showYears' => 0],
        ]);

        $this->dispatch('/solrsearch/browse/years');
        $this->assertRedirect('/solrsearch/browse/index');
    }

    public function testBrowsingByYearWithInvertedYearFacetConfigured()
    {
        $this->markTestIncomplete();
    }

    public function testShowYearsDisabled()
    {
        $this->adjustConfiguration([
            'browsing' => ['showYears' => 0],
        ]);

        $this->dispatch('/solrsearch/browse');
        $this->assertResponseCode(200);

        $this->assertXpath('//a[contains(@href, "solrsearch/index/search/searchtype/latest")]');
        $this->assertXpath('//a[contains(@href, "solrsearch/browse/doctypes")]');
        $this->assertNotXpath('//a[contains(@href, "solrsearch/browse/years")]');
    }

    public function testShowLatestDocumentsDisabled()
    {
        $this->adjustConfiguration([
            'browsing' => ['showLatestDocuments' => 0],
        ]);

        $this->dispatch('/solrsearch/browse');
        $this->assertResponseCode(200);

        $this->assertNotXpath('//a[contains(@href, "solrsearch/index/search/searchtype/latest")]');
        $this->assertXpath('//a[contains(@href, "solrsearch/browse/doctypes")]');
        $this->assertXpath('//a[contains(@href, "solrsearch/browse/years")]');
    }

    public function testMissingDoctypeFacetDisablesDoctypeBrowsing()
    {
        $this->adjustConfiguration([
            'searchengine' => ['solr' => ['facets' => 'author_facet,year,language,has_fulltext']],
        ]);

        $facetManager = new Application_Search_FacetManager();

        $this->dispatch('/solrsearch/browse');
        $this->assertResponseCode(200);

        $this->assertNotXpath('//a[contains(@href, "solrsearch/browse/doctypes")]');
        $this->assertXpath('//a[contains(@href, "solrsearch/browse/years")]');
    }

    public function testMissingYearFacetDisablesYearBrowsing()
    {
        $this->adjustConfiguration([
            'searchengine' => ['solr' => ['facets' => 'author_facet,doctype,language,has_fulltext']],
        ]);

        $facetManager = new Application_Search_FacetManager();

        $this->dispatch('/solrsearch/browse');
        $this->assertResponseCode(200);

        $this->assertXpath('//a[contains(@href, "solrsearch/browse/doctypes")]');
        $this->assertNotXpath('//a[contains(@href, "solrsearch/browse/years")]');
    }
}
