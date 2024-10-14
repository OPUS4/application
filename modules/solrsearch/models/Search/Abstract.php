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
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Search\Config;
use Opus\Search\Result\Base;
use Opus\Search\SearchException;
use Opus\Search\Util\Query;

/**
 * Abstract base class for search type implementations.
 *
 * TODO eliminate switch/if/else constructs for different search types
 */
abstract class Solrsearch_Model_Search_Abstract extends Application_Model_Abstract
{
    /** @var bool */
    private $export = false;

    /** @var Zend_View */
    private $view;

    /** @var array TODO this is used for facets (not index fields) - rename! */
    private $filterFields;

    /** @var string[] */
    private $searchFields;

    /** @var string */
    private $searchType;

    /** @var int Maximum number of rows for search. */
    private $maxRows = Query::MAX_ROWS;

    /**
     * TODO move out of constructor?
     */
    public function __construct()
    {
        $logger = $this->getLogger();

        $this->filterFields = [];

        $filters = Config::getFacetNames();
        if (! count($filters)) {
            $logger->debug('key searchengine.solr.facets is not present in config. skipping filter queries');
        } else {
            $logger->debug('searchengine.solr.facets is set to ' . implode(',', $filters));
        }

        foreach ($filters as $filterfield) {
            array_push($this->filterFields, trim($filterfield));
        }

        $this->searchFields = ['author', 'title', 'persons', 'referee', 'abstract', 'fulltext', 'year'];
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return array
     */
    public function createQueryBuilderInputFromRequest($request)
    {
        if ($request->getParams() === null) {
            throw new Application_Search_QueryBuilderException('Unable to read request data. Search cannot be performed.');
        }

        $this->validateParamsType($request);

        if ($request->getParam('sortfield')) {
            $sorting = [$request->getParam('sortfield'), 'asc'];
        } else {
            $sorting = Opus\Search\Query::getDefaultSorting();
        }

        $searchType = $this->getSearchType();

        $input = [
            'searchtype' => $searchType,
            'start'      => $request->getParam('start', Opus\Search\Query::getDefaultStart()),
            'rows'       => $request->getParam('rows', Opus\Search\Query::getDefaultRows()),
            'sortField'  => $sorting[0],
            'sortOrder'  => $request->getParam('sortorder', $sorting[1]),
            'docId'      => $request->getParam('docId'),
            'query'      => $request->getParam('query', '*:*'),
        ];

        if ($this->getExport()) {
            $maxNumber = $this->getMaxRows();

            // pagination within export was introduced in OPUS 4.2.2
            $startParam = $request->getParam('start', 0);
            $rowsParam  = $request->getParam('rows', $maxNumber);
            $start      = intval($startParam);

            if (is_string($rowsParam)) {
                // for invalid values $maxNumber should be used
                if (ctype_digit($rowsParam)) {
                    $rows = intval($rowsParam);
                } else {
                    $rows = $maxNumber;
                }
            } else {
                $rows = $rowsParam;
            }

            // rows = 0 should be support to allow just getting the number of possible results (TODO Is it used?)
            $rows = $rows > $maxNumber || $rows < 0 ? $maxNumber : $rows;

            // IMPORTANT: 'start' + 'row' must not exceed 2147483647 (java,lang.Integer.MAX_VALUE)
            if ($start > Query::MAX_ROWS) {
                $start = Query::MAX_ROWS;
                $rows  = 0;
                // TODO throwing exception would be better because this query does not make sense
                //      need to change tests for changed behaviour
            }
            $start = $start > 0 ? $start : 0;

            if ($start + $rows > Query::MAX_ROWS) {
                $rows = Query::MAX_ROWS - $start;
            }

            $input['rows']  = $rows;
            $input['start'] = $start;
        }

        foreach ($this->searchFields as $searchField) {
            $input[$searchField]              = $request->getParam($searchField, '');
            $input[$searchField . 'modifier'] = $request->getParam(
                $searchField . 'modifier',
                Query::SEARCH_MODIFIER_CONTAINS_ALL
            );
        }

        foreach ($this->filterFields as $filterField) {
            $param         = $filterField . 'fq';
            $input[$param] = $request->getParam($param, '');
        }

        return $input;
    }

    /**
     * Checks if all given parameters are of type string. Otherwise, throws Application_Search_QueryBuilderException.
     *
     * @param Zend_Controller_Request_Http $request
     * @throws Application_Search_QueryBuilderException
     */
    public function validateParamsType($request)
    {
        $paramNames = [
            'searchtype',
            'start',
            'rows',
            'sortField',
            'sortOrder',
            'query',
            'collectionId',
            'seriesId',
        ];
        foreach ($this->searchFields as $searchField) {
            array_push($paramNames, $searchField, $searchField . 'modifier');
        }
        foreach ($this->filterFields as $filterField) {
            array_push($paramNames, $filterField . 'fq');
        }

        foreach ($paramNames as $paramName) {
            $paramValue = $request->getParam($paramName, null);
            if ($paramValue !== null && ! is_string($paramValue)) {
                throw new Application_Search_QueryBuilderException('Parameter ' . $paramName . ' is not of type string');
            }
        }
    }

    /**
     * @param Zend_View_Interface $view
     */
    public function setView($view)
    {
        $this->view = $view;
    }

    /**
     * @return Zend_View_Interface
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param bool $exportEnabled
     */
    public function setExport($exportEnabled)
    {
        $this->export = $exportEnabled;
    }

    /**
     * @return bool
     */
    public function getExport()
    {
        return $this->export;
    }

    /**
     * @param string $searchType
     */
    public function setSearchType($searchType)
    {
        $this->searchType = $searchType;
    }

    /**
     * @return string
     */
    public function getSearchType()
    {
        return $this->searchType;
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return string|null
     * @throws Zend_Exception
     */
    public function buildQuery($request)
    {
        try {
            return $this->getQueryUrl($request);
        } catch (Application_Util_BrowsingParamsException $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $this->_helper->Redirector->redirectToAndExit('index', '', 'browse', null, [], true);
        } catch (Application_Search_QueryBuilderException $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $this->_helper->Redirector->redirectToAndExit('index');
        }
        return null;
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return Query
     * @throws Application_Search_QueryBuilderException
     */
    public function getQueryUrl($request)
    {
        $queryBuilderInput = $this->createQueryBuilderInputFromRequest($request);

        $searchType = $request->getParam('searchtype');

        if (
            $request->getParam('sortfield') === null &&
            ($request->getParam('browsing') === 'true' || $searchType === 'collection')
        ) {
            $queryBuilderInput['sortField'] = 'server_date_published';
        }

        if ($searchType === Application_Util_Searchtypes::LATEST_SEARCH) {
            return $this->createSearchQuery($this->validateInput($queryBuilderInput, 10, 100));
        }

        return $this->createSearchQuery($this->validateInput($queryBuilderInput));
    }

    /**
     * Adjust the actual rows parameter value if it is not between $min
     * and $max (inclusive). In case the actual value is smaller (greater)
     * than $min ($max) it is adjusted to $min ($max).
     *
     * Sets the actual start parameter value to 0 if it is negative.
     *
     * @param array $input An array that contains the request parameters.
     * @param int   $min The lower bound.
     * @param int   $max The upper bound.
     * @return array Returns the actual rows parameter value or an adjusted value if
     * it is not in the interval [$lowerBoundInclusive, $upperBoundInclusive].
     */
    protected function validateInput($input, $min = 1, $max = 100)
    {
        $logger = $this->getLogger();

        if ($input['rows'] > $max) {
            $logger->warn("Values greater than $max are currently not allowed for the rows paramter.");
            $input['rows'] = $max;
        }
        if ($input['rows'] < $min) {
            $logger->warn("rows parameter is smaller than $min: adjusting to $min.");
            $input['rows'] = $min;
        }
        if ($input['start'] < 0) {
            $logger->warn("A negative start parameter is ignored.");
            $input['start'] = 0;
        }
        return $input;
    }

    /**
     * Sets up the xml query.
     *
     * TODO CRITICAL merge with regular buildQuery
     *
     * @param Zend_Controller_Request_Http $request
     * @return Query
     */
    public function buildExportQuery($request)
    {
        $queryBuilderInput = [];
        try {
            $queryBuilderInput = $this->createQueryBuilderInputFromRequest($request);
        } catch (Application_Search_QueryBuilderException $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $applicationException = new Application_Exception($e->getMessage());
            $code                 = $e->getCode();
            if ($code !== 0) {
                $applicationException->setHttpResponseCode($code);
            }
            throw $applicationException;
        }

        return $this->createSearchQuery($queryBuilderInput);
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @param Query                        $query
     * @param Base                         $resultList
     * @param string                       $searchType
     */
    public function setViewValues($request, $query, $resultList, $searchType)
    {
        $this->setGeneralViewValues($request, $query, $resultList, $searchType);

        if ($resultList->getNumberOfHits() > 0) {
            $nrOfRows    = (int) $query->getRows();
            $start       = $query->getStart();
            $queryString = null;
            if (
                $searchType === Application_Util_Searchtypes::SIMPLE_SEARCH
                || $searchType === Application_Util_Searchtypes::ALL_SEARCH
            ) {
                $queryString = $query->getCatchAll();
            }
            $this->setUpPagination($nrOfRows, $start, $queryString, $searchType, $resultList);
        }

        $view = $this->getView();

        switch ($searchType) {
            case Application_Util_Searchtypes::SIMPLE_SEARCH:
            case Application_Util_Searchtypes::ALL_SEARCH:
                $queryString = $query->getCatchAll();
                if ($queryString !== null && trim($queryString) !== '*:*') {
                    $view->q = $queryString;
                } else {
                    $view->q = '';
                }
                $this->setFilterQueryBaseURL($request);
                $browsing = $request->getParam('browsing', 'false');
                if ($browsing === 'true') {
                    $view->specialTitle = $view->translate($request->getParam('doctypefq', ''));
                    $view->doctype      = $request->getParam('doctypefq', null);
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

    /**
     * @param Zend_Controller_Request_Http $request
     * @param Query                        $query
     * @param Base                         $resultList
     * @param string                       $searchType
     */
    public function setGeneralViewValues($request, $query, $resultList, $searchType)
    {
        $numOfHits = $resultList->getNumberOfHits();

        $view = $this->getView();

        $view->results    = $resultList->getResults();
        $view->searchType = $searchType;
        $view->numOfHits  = $numOfHits;
        $view->queryTime  = $resultList->getQueryTime();
        $view->start      = $query->getStart();

        $nrOfRows = $query->getRows();
        if ($nrOfRows !== 0) {
            $view->numOfPages = (int) ($numOfHits / $nrOfRows) + 1;
        }

        $view->rows         = $query->getRows();
        $view->authorSearch = Solrsearch_Model_Search::createSearchUrlArray([
            'searchtype' => Application_Util_Searchtypes::AUTHOR_SEARCH,
        ]);
        $view->isSimpleList = false;
        $view->browsing     = (bool) $request->getParam('browsing', false);

        if ($searchType === Application_Util_Searchtypes::SERIES_SEARCH) {
            $view->sortfield = $request->getParam('sortfield', 'seriesnumber');
        } else {
            $view->sortfield = $request->getParam('sortfield', 'score');
        }

        $view->sortorder = $request->getParam('sortorder', 'desc');

        $this->setRssUrl();
    }

    public function setRssUrl()
    {
        $view = $this->getView();

        $view->rssUrl = Solrsearch_Model_Search::createSearchUrlArray([], true);
    }

    /**
     * Sets the base URL that is used to build all remove filter query URLs.
     *
     * @param Zend_Controller_Request_Http $request
     */
    public function setFilterQueryBaseURL($request)
    {
        $view = $this->getView();

        $view->removeFilterQueryBase = $request->getParams();
        unset($view->removeFilterQueryBase['start']);
    }

    /**
     * Sets up pagination for search results.
     *
     * @param int    $rows Number of results per page
     * @param int    $startIndex Starting number for first result on current page
     * @param string $queryString Current query
     * @param string $searchType
     * @param Base   $resultList
     */
    public function setUpPagination($rows, $startIndex, $queryString, $searchType, $resultList)
    {
        $view = $this->getView();

        $numOfHits = $resultList->getNumberOfHits();

        $pagination = new Solrsearch_Model_PaginationUtil($rows, $numOfHits, $startIndex, $queryString, $searchType);

        $view->nextPage  = Solrsearch_Model_Search::createSearchUrlArray($pagination->getNextPageUrlArray());
        $view->prevPage  = Solrsearch_Model_Search::createSearchUrlArray($pagination->getPreviousPageUrlArray());
        $view->lastPage  = Solrsearch_Model_Search::createSearchUrlArray($pagination->getLastPageUrlArray());
        $view->firstPage = Solrsearch_Model_Search::createSearchUrlArray($pagination->getFirstPageUrlArray());
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @return null|Zend_Form
     */
    public function createForm($request)
    {
        return null;
    }

    /**
     * @param array $input
     * @return Query
     */
    abstract public function createSearchQuery($input);

    /**
     * @param Query      $query
     * @param null|array $openFacets
     * @return Base
     * @throws Application_Exception
     * @throws Application_SearchException
     *
     * TODO facets optional (export search)
     */
    public function performSearch($query, $openFacets = null)
    {
        $this->getLogger()->debug('performing search');

        $resultList = null;

        try {
            $searcher = Application_Search_SearcherFactory::getSearcher();

            if ($openFacets !== null) {
                $searcher->setFacetArray($openFacets);
            }

            $resultList = $searcher->search($query);
        } catch (SearchException $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e);
            throw new Application_SearchException($e);
        }

        return $resultList;
    }

    /**
     * @param Query $query
     * @param array $input
     * @throws Zend_Exception
     */
    public function addFiltersToQuery($query, $input)
    {
        $facetManager = $this->getFacetManager();

        foreach ($this->filterFields as $filterField) {
            $facetName  = $filterField;
            $facetKey   = $facetName . 'fq';
            $facetValue = $input[$facetKey];
            if ($facetValue !== '') {
                $facet = $facetManager->getFacet($facetName);
                $this->getLogger()->debug(
                    "request has facet key: $facetKey - value is: $facetValue - corresponding facet is: $filterField"
                );
                $indexField = $facet->getIndexField();
                $indexField = preg_replace('/_inverted/', '', $indexField);

                $query->addFilterQuery($indexField, $facetValue);
            }
        }
    }

    /**
     * Returns maximum number of rows for search.
     *
     * @return int
     */
    public function getMaxRows()
    {
        return $this->maxRows;
    }

    /**
     * Sets maximum number of rows for search.
     *
     * @param int $maxRows
     */
    public function setMaxRows($maxRows)
    {
        $this->maxRows = $maxRows;
    }

    /**
     * @return Application_Search_FacetManager
     */
    public function getFacetManager()
    {
        return new Application_Search_FacetManager(); // TODO should be singleton
    }
}
