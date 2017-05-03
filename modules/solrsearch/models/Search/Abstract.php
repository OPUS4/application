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
 * @package     Solrsearch_Model_Search
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Abstract base class for search type implementations.
 *
 * TODO eliminate switch/if/else constructs for different search types
 */
abstract class Solrsearch_Model_Search_Abstract extends Application_Model_Abstract
{

    private $_view;

    public function setView($view)
    {
        $this->_view = $view;
    }

    public function getView()
    {
        return $this->_view;
    }

    public function buildQuery($request)
    {
        try
        {
            return Application_Search_Navigation::getQueryUrl($request, $this->getLogger());
        }
        catch (Application_Util_BrowsingParamsException $e)
        {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $this->_helper->Redirector->redirectToAndExit('index', '', 'browse', null, array(), true);
            return null;
        }
        catch (Application_Util_QueryBuilderException $e)
        {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $this->_helper->Redirector->redirectToAndExit('index');
            return null;
        }
    }

    /**
     * Sets up the xml query.
     *
     * TODO CRITICAL merge with regular buildQuery
     */
    public function buildExportQuery($request) {
        $queryBuilder = new Application_Util_QueryBuilder($this->getLogger(), true);
        $queryBuilderInput = array();
        try {
            $queryBuilderInput = $queryBuilder->createQueryBuilderInputFromRequest($request);
        }
        catch (Application_Util_QueryBuilderException $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            throw new Application_Exception($e->getMessage());
        }

        return $queryBuilder->createSearchQuery($queryBuilderInput);
    }

    public function setViewValues($request, $query, $resultList, $searchType) {
        $this->setGeneralViewValues($request, $query, $resultList, $searchType);

        if ($resultList->getNumberOfHits() > 0) {
            $nrOfRows = (int)$query->getRows();
            $start = $query->getStart();
            $queryString = null;
            if ($searchType === Application_Util_Searchtypes::SIMPLE_SEARCH
                || $searchType === Application_Util_Searchtypes::ALL_SEARCH) {
                $queryString = $query->getCatchAll();
            }
            $this->setUpPagination($nrOfRows, $start, $queryString, $searchType, $resultList);
        }

        $view = $this->getView();

        switch ($searchType) {
            case Application_Util_Searchtypes::SIMPLE_SEARCH:
            case Application_Util_Searchtypes::ALL_SEARCH:
                $queryString = $query->getCatchAll();
                if (trim($queryString) !== '*:*') {
                    $view->q = $queryString;
                }
                else {
                    $view->q = '';
                }
                $this->setFilterQueryBaseURL($request);
                $browsing = $request->getParam('browsing', 'false');
                if ($browsing === 'true') {
                    $view->specialTitle = $view->translate($request->getParam('doctypefq', ''));
                    $view->doctype = $request->getParam('doctypefq', null);
                }
                break;
            case Application_Util_Searchtypes::ADVANCED_SEARCH:
            case Application_Util_Searchtypes::AUTHOR_SEARCH:
            case Application_Util_Searchtypes::COLLECTION_SEARCH:
            case Application_Util_Searchtypes::SERIES_SEARCH:
                $this->setFilterQueryBaseURL($request);
                break;
            case Application_Util_Searchtypes::LATEST_SEARCH:
                $view->isSimpleList = true;
                $view->specialTitle = $view->translate('title_latest_docs_article') . ' '
                    . $query->getRows() . ' ' . $view->translate('title_latest_docs');
                break;
            default:
                break;
        }
    }

    public function setGeneralViewValues($request, $query, $resultList, $searchType)
    {
        $numOfHits = $resultList->getNumberOfHits();

        $view = $this->getView();

        $view->results = $resultList->getResults();
        $view->searchType = $searchType;
        $view->numOfHits = $numOfHits;
        $view->queryTime = $resultList->getQueryTime();
        $view->start = $query->getStart();

        $nrOfRows = $query->getRows();
        if ($nrOfRows != 0) {
            $view->numOfPages = (int) ($numOfHits / $nrOfRows) + 1;
        }

        $view->rows = $query->getRows();
        $view->authorSearch = Solrsearch_Model_Search::createSearchUrlArray(array(
            'searchtype' => Application_Util_Searchtypes::AUTHOR_SEARCH
        ));
        $view->isSimpleList = false;
        $view->browsing = (boolean) $request->getParam('browsing', false);

        if ($searchType == Application_Util_Searchtypes::SERIES_SEARCH)
        {
            $view->sortfield = $request->getParam('sortfield', 'seriesnumber');
        }
        else
        {
            $view->sortfield = $request->getParam('sortfield', 'score');
        }

        $view->sortorder = $request->getParam('sortorder', 'desc');

        $this->setRssUrl();
    }

    public function setRssUrl() {
        $view = $this->getView();

        $view->rssUrl = Solrsearch_Model_Search::createSearchUrlArray(array(), true);
    }

    /**
     * Sets the base URL that is used to build all remove filter query URLs.
     */
    public function setFilterQueryBaseURL($request) {
        $view = $this->getView();

        $view->removeFilterQueryBase = $request->getParams();
        unset($view->removeFilterQueryBase['start']);
    }

    /**
     * Sets up pagination for search results.
     * @param $rows Number of results per page
     * @param $startIndex Starting number for first result on current page
     * @param $query Current query
     */
    public function setUpPagination($rows, $startIndex, $queryString, $searchType, $resultList)
    {
        $view = $this->getView();

        $numOfHits = $resultList->getNumberOfHits();

        $pagination = new Solrsearch_Model_PaginationUtil($rows, $numOfHits, $startIndex, $queryString, $searchType);

        $view->nextPage = Solrsearch_Model_Search::createSearchUrlArray($pagination->getNextPageUrlArray());
        $view->prevPage = Solrsearch_Model_Search::createSearchUrlArray($pagination->getPreviousPageUrlArray());
        $view->lastPage = Solrsearch_Model_Search::createSearchUrlArray($pagination->getLastPageUrlArray());
        $view->firstPage = Solrsearch_Model_Search::createSearchUrlArray($pagination->getFirstPageUrlArray());
    }

    public function createForm($request)
    {
        return null;
    }

    /**
     * @throws Application_Exception
     * @throws Application_SearchException
     *
     * TODO facets optional (export search)
     */
    public function performSearch($query, $openFacets = null) {
        $this->getLogger()->debug('performing search');

        $resultList = null;

        try {
            $searcher = new Opus_SolrSearch_Searcher();

            if (!is_null($openFacets))
            {
                $searcher->setFacetArray($openFacets);
            }

            $resultList = $searcher->search($query);
        }
        catch (Opus_SolrSearch_Exception $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e);
            throw new Application_SearchException($e);
        }

        return $resultList;
    }


}