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

    private $_export = false;

    private $_view;

    private $_filterFields;

    private $_searchFields;

    private $_searchType;

    /**
     * Maximum number of rows for search.
     * @var int
     */
    private $_maxRows = Opus_SolrSearch_Query::MAX_ROWS;

    /**
     *
     * TODO move out of constructor?
     */
    public function __construct() {
        $logger = $this->getLogger();

        $this->_filterFields = array();

        $filters = Opus_Search_Config::getFacetFields();
        if ( !count( $filters ) ) {
            $logger->debug( 'key searchengine.solr.facets is not present in config. skipping filter queries' );
        } else {
            $logger->debug( 'searchengine.solr.facets is set to ' . implode( ',', $filters ) );
        }

        foreach ($filters as $filterfield) {
            if ($filterfield == 'year_inverted') {
                $filterfield = 'year';
            }
            array_push($this->_filterFields, trim($filterfield));
        }

        $this->_searchFields = array('author', 'title', 'persons', 'referee', 'abstract', 'fulltext', 'year');
    }

    /**
     *
     * @param $request
     * @return array
     */
    public function createQueryBuilderInputFromRequest($request) {
        if (is_null($request->getParams())) {
            throw new Application_Search_QueryBuilderException('Unable to read request data. Search cannot be performed.');
        }

        $this->validateParamsType($request);

        if ( $request->getParam( 'sortfield' ) ) {
            $sorting = array( $request->getParam( 'sortfield' ), 'asc' );
        } else {
            $sorting = Opus_Search_Query::getDefaultSorting();
        }

        $searchType = $this->getSearchType();

        $input = array(
            'searchtype' => $searchType,
            'start' => $request->getParam('start', Opus_Search_Query::getDefaultStart()),
            'rows' => $request->getParam('rows', Opus_Search_Query::getDefaultRows()),
            'sortField' => $sorting[0],
            'sortOrder' => $request->getParam('sortorder', $sorting[1]),
            'docId' => $request->getParam('docId'),
            'query' => $request->getParam('query', '*:*')
        );

        if ($this->getExport()) {
            $maxNumber = $this->getMaxRows();

            // pagination within export was introduced in OPUS 4.2.2
            $startParam = $request->getParam('start', 0);
            $rowsParam = $request->getParam('rows', $maxNumber);
            $start = intval($startParam);
            $rows = intval($rowsParam);

            $input['start'] = $start > 0 ? $start : 0;

            $input['rows'] = $rows > 0 || ($rows == 0 && $rowsParam == '0') ? $rows : $maxNumber;

            if ($input['start'] > $maxNumber)
            {
                $input['start'] = $maxNumber;
            }
            if ($input['rows'] + $input['start'] > $maxNumber)
            {
                $input['rows'] = $maxNumber - $input['start'];
            }
        }

        foreach ($this->_searchFields as $searchField) {
            $input[$searchField] = $request->getParam($searchField, '');
            $input[$searchField . 'modifier'] = $request->getParam(
                $searchField . 'modifier', Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL
            );
        }

        foreach ($this->_filterFields as $filterField) {
            $param = $filterField . 'fq';
            $input[$param] = $request->getParam($param, '');
        }

        return $input;
    }

    /**
     * Checks if all given parameters are of type string. Otherwise, throws Application_Search_QueryBuilderException.
     *
     * @throws Application_Search_QueryBuilderException
     */
    public function validateParamsType($request) {
        $paramNames = array(
            'searchtype',
            'start',
            'rows',
            'sortField',
            'sortOrder',
            'query',
            'collectionId',
            'seriesId'
        );
        foreach ($this->_searchFields as $searchField) {
            array_push($paramNames, $searchField, $searchField . 'modifier');
        }
        foreach ($this->_filterFields as $filterField) {
            array_push($paramNames, $filterField . 'fq');
        }

        foreach ($paramNames as $paramName) {
            $paramValue = $request->getParam($paramName, null);
            if (!is_null($paramValue) && !is_string($paramValue)) {
                throw new Application_Search_QueryBuilderException('Parameter ' . $paramName . ' is not of type string');
            }
        }
    }

    public function setView($view)
    {
        $this->_view = $view;
    }

    public function getView()
    {
        return $this->_view;
    }

    public function setExport($exportEnabled)
    {
        $this->_export = $exportEnabled;
    }

    public function getExport()
    {
        return $this->_export;
    }

    public function setSearchType($searchType)
    {
        $this->_searchType = $searchType;
    }

    public function getSearchType()
    {
        return $this->_searchType;
    }

    public function buildQuery($request)
    {
        try
        {
            return $this->getQueryUrl($request);
        }
        catch (Application_Util_BrowsingParamsException $e)
        {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $this->_helper->Redirector->redirectToAndExit('index', '', 'browse', null, array(), true);
            return null;
        }
        catch (Application_Search_QueryBuilderException $e)
        {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $this->_helper->Redirector->redirectToAndExit('index');
            return null;
        }
    }

    public function getQueryUrl($request) {

        $queryBuilderInput = $this->createQueryBuilderInputFromRequest($request);

        $searchType = $request->getParam('searchtype');

        if (is_null($request->getParam('sortfield')) &&
            ($request->getParam('browsing') === 'true' || $searchType === 'collection')) {
            $queryBuilderInput['sortField'] = 'server_date_published';
        }

        if ($searchType === Application_Util_Searchtypes::LATEST_SEARCH) {
            return $this->createSearchQuery($this->_validateInput($queryBuilderInput, 10, 100));
        }

        return $this->createSearchQuery($this->_validateInput($queryBuilderInput));
    }

    /**
     * Adjust the actual rows parameter value if it is not between $min
     * and $max (inclusive). In case the actual value is smaller (greater)
     * than $min ($max) it is adjusted to $min ($max).
     *
     * Sets the actual start parameter value to 0 if it is negative.
     *
     * @param array $data An array that contains the request parameters.
     * @param int $lowerBoundInclusive The lower bound.
     * @param int $upperBoundInclusive The upper bound.
     * @return int Returns the actual rows parameter value or an adjusted value if
     * it is not in the interval [$lowerBoundInclusive, $upperBoundInclusive].
     *
     */
    protected function _validateInput($input, $min = 1, $max = 100)
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
     */
    public function buildExportQuery($request) {
        $queryBuilderInput = array();
        try {
            $queryBuilderInput = $this->createQueryBuilderInputFromRequest($request);
        }
        catch (Application_Search_QueryBuilderException $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $applicationException = new Application_Exception($e->getMessage());
            $code = $e->getCode();
            if ($code != 0) {
                $applicationException->setHttpResponseCode($code);
            }
            throw $applicationException;
        }

        return $this->createSearchQuery($queryBuilderInput);
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

    abstract public function createSearchQuery($input);

    /**
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
            $searcher = new Opus_SolrSearch_Searcher();

            if (!is_null($openFacets))
            {
                $searcher->setFacetArray($openFacets);
            }

            $resultList = $searcher->search($query);
        }
        catch (Opus_SolrSearch_Exception $e)
        {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e);
            throw new Application_SearchException($e);
        }

        return $resultList;
    }

    public function addFiltersToQuery($query, $input) {
        foreach ($this->_filterFields as $filterField) {
            $facetKey = $filterField . 'fq';
            $facetValue = $input[$facetKey];
            if ($facetValue !== '') {
                $this->getLogger()->debug(
                    "request has facet key: $facetKey - value is: $facetValue - corresponding facet is: $filterField"
                );
                $query->addFilterQuery($filterField, $facetValue);
            }
        }
    }

    /**
     * Returns maximum number of rows for search.
     * @return int
     */
    public function getMaxRows()
    {
        return $this->_maxRows;
    }

    /**
     * Sets maximum number of rows for search.
     * @param $maxRows integer
     */
    public function setMaxRows($maxRows)
    {
        $this->_maxRows = $maxRows;
    }

}