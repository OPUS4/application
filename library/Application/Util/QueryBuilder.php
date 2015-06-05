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
 * @package     Util
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Application_Util_QueryBuilder {

    private $_logger;
    private $_filterFields;
    private $_searchFields;
    private $_export = false;

    /**
     *
     * @param boolean $export
     */
    public function __construct($logger, $export = false) {
        $this->_logger = $logger;

        $this->_filterFields = array();
        $config = Zend_Registry::get("Zend_Config");
        if (!isset($config->searchengine->solr->facets)) {
            $this->_logger->debug("key searchengine.solr.facets is not present in config. skipping filter queries");
        }
        $filters = $config->searchengine->solr->facets;
        $this->_logger->debug("searchengine.solr.facets is set to $filters");

        foreach (explode(',', $filters) as $filterfield) {
            if ($filterfield == 'year_inverted') {
                $filterfield = 'year';
            }
            array_push($this->_filterFields, trim($filterfield));
        }

        $this->_searchFields = array('author', 'title', 'persons', 'referee', 'abstract', 'fulltext', 'year');
        $this->_export = $export;
    }

    /**
     *
     * @param $request
     * @return array
     */
    public function createQueryBuilderInputFromRequest($request) {
        if (is_null($request->getParams())) {
            throw new Application_Util_QueryBuilderException('Unable to read request data. Search cannot be performed.');
        }

        if (is_null($request->getParam('searchtype'))) {
            throw new Application_Util_QueryBuilderException('Unspecified search type: unable to create query.');
        }

        if (!Application_Util_Searchtypes::isSupported($request->getParam('searchtype'))) {
            throw new Application_Util_QueryBuilderException(
                'Unsupported search type ' . $request->getParam('searchtype') . ' : unable to create query.'
            );
        }

        $this->validateParamsType($request);

	    if ( $request->getParam( 'sortfield' ) ) {
		    $sorting = array( $request->getParam( 'sortfield' ), 'asc' );
	    } else {
		    $sorting = Opus_Search_Query::getDefaultSorting();
	    }

        $input = array(
            'searchtype' => $request->getParam('searchtype'),
            'start' => $request->getParam('start', Opus_Search_Query::getDefaultStart()),
            'rows' => $request->getParam('rows', Opus_Search_Query::getDefaultRows()),
            'sortField' => $sorting[0],
            'sortOrder' => $request->getParam('sortorder', $sorting[1]),
            'docId' => $request->getParam('docId'),
            'query' => $request->getParam('query', '*:*')
        );

        if ($this->_export) {
            $maxRows = Opus_SolrSearch_Query::MAX_ROWS;
            // pagination within export was introduced in OPUS 4.2.2
            $startParam = $request->getParam('start', 0);
            $rowsParam = $request->getParam('rows', $maxRows);
            $start = intval($startParam);
            $rows = intval($rowsParam);
            $input['start'] = $start > 0 ? $start : 0;
            $input['rows'] = $rows > 0 || ($rows == 0 && $rowsParam == '0') ? $rows : $maxRows;
            if ($input['start'] > $maxRows) {
                $input['start'] = $maxRows;
            }
            if ($input['rows'] + $input['start'] > $maxRows) {
                $input['rows'] = $maxRows - $start;
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


        if ($request->getParam('searchtype') === Application_Util_Searchtypes::COLLECTION_SEARCH
                || $request->getParam('searchtype') === Application_Util_Searchtypes::SERIES_SEARCH) {
            $searchParams = new Application_Util_BrowsingParams($request, $this->_logger);
            switch ($request->getParam('searchtype')) {
                case Application_Util_Searchtypes::COLLECTION_SEARCH:
                    $input['collectionId'] = $searchParams->getCollectionId();
                    break;
                case Application_Util_Searchtypes::SERIES_SEARCH:
                    $input['seriesId'] = $searchParams->getSeriesId();
                    break;
            }
        }

        return $input;
    }

    /**
     * Checks if all given parameters are of type string. Otherwise, throws Application_Util_QueryBuilderException.
     *
     * @throws Application_Util_QueryBuilderException
     */
    private function validateParamsType($request) {
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
                throw new Application_Util_QueryBuilderException('Parameter ' . $paramName . ' is not of type string');
            }
        }
    }

    /**
     *
     * @param array $input
     * @return Opus_SolrSearch_Query
     */
    public function createSearchQuery($input) {
        if ($input['searchtype'] === Application_Util_Searchtypes::SIMPLE_SEARCH) {
            return $this->createSimpleSearchQuery($input);
        }
        if ($input['searchtype'] === Application_Util_Searchtypes::ADVANCED_SEARCH
                || $input['searchtype'] === Application_Util_Searchtypes::AUTHOR_SEARCH) {
            return $this->createAdvancedSearchQuery($input);
        }
        if ($input['searchtype'] === Application_Util_Searchtypes::LATEST_SEARCH) {
            return $this->createLatestSearchQuery($input);
        }
        if ($input['searchtype'] === Application_Util_Searchtypes::COLLECTION_SEARCH) {
            return $this->createCollectionSearchQuery($input);
        }
        if ($input['searchtype'] === Application_Util_Searchtypes::SERIES_SEARCH) {
            return $this->createSeriesSearchQuery($input);
        }
        if ($input['searchtype'] === Application_Util_Searchtypes::ALL_SEARCH) {
            return $this->createAllSearchQuery($input);
        }
        if ($input['searchtype'] === Application_Util_Searchtypes::ID_SEARCH) {
            return $this->createIdSearchQuery($input);
        }
    }

    private function createIdSearchQuery($input) {
        $this->_logger->debug("Constructing query for id search.");

        if (is_null($input['docId'])) {
            throw new Application_Exception("No id provided.", 404);
        }

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::DOC_ID);
        $query->setField('id', $input['docId']);

        if ($this->_export) {
            $query->setReturnIdsOnly(true);
        }

        $this->_logger->debug("Query $query complete");
        return $query;
    }

    private function createSimpleSearchQuery($input) {
        $this->_logger->debug("Constructing query for simple search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setStart($input['start']);
        $query->setRows($input['rows']);
        $query->setSortField($input['sortField']);
        $query->setSortOrder($input['sortOrder']);

        $query->setCatchAll($input['query']);
        $this->addFiltersToQuery($query, $input);

        if ($this->_export) {
            $query->setReturnIdsOnly(true);
        }

        $this->_logger->debug("Query $query complete");
        return $query;
    }

    private function createAdvancedSearchQuery($input) {
        $this->_logger->debug("Constructing query for advanced search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::ADVANCED);
        $query->setStart($input['start']);
        $query->setRows($input['rows']);
        $query->setSortField($input['sortField']);
        $query->setSortOrder($input['sortOrder']);

        foreach (array('author', 'title', 'persons', 'referee', 'abstract', 'fulltext', 'year') as $fieldname) {
            if (!empty($input[$fieldname])) {
                $query->setField($fieldname, $input[$fieldname], $input[$fieldname . 'modifier']);
            }
        }

        $this->addFiltersToQuery($query, $input);

        //im Falle einer Autorensuche werden Kommas und Semikolons aus dem Suchstring entfernt
        if (!is_null($query->getField('author'))) {
            $author = $query->getField('author');
            $authormodifier = $query->getModifier('author');
            $query->setField('author', str_replace(array(',', ';'), '', $author), $authormodifier);
        }

        if ($this->_export) {
            $query->setReturnIdsOnly(true);
        }

        $this->_logger->debug("Query $query complete");
        return $query;
    }

    private function createLatestSearchQuery($input) {
        $this->_logger->debug("Constructing query for latest search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::LATEST_DOCS);
        $query->setRows($input['rows']);
        $query->setStart($input['start']);

        if ($this->_export) {
            $query->setReturnIdsOnly(true);
        }

        $this->_logger->debug("Query $query complete");
        return $query;
    }

    private function createCollectionSearchQuery($input) {
        $this->_logger->debug("Constructing query for collection search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setStart($input['start']);
        $query->setRows($input['rows']);
        $query->setSortField($input['sortField']);
        $query->setSortOrder($input['sortOrder']);

        $query->setCatchAll('*:*');
        $query->addFilterQuery('collection_ids', $input['collectionId']);
        $this->addFiltersToQuery($query, $input);

        if ($this->_export) {
            $query->setReturnIdsOnly(true);
        }

        $this->_logger->debug("Query $query complete");
        return $query;
    }

    private function createSeriesSearchQuery($input) {
        $this->_logger->debug("Constructing query for series search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setStart($input['start']);
        $query->setRows($input['rows']);
        if ($input['sortField'] === 'seriesnumber'
                || $input['sortField'] === Opus_Search_Query::getDefaultSortingField()) {
            $query->setSortField('doc_sort_order_for_seriesid_' . $input['seriesId']);
        }
        else {
            $query->setSortField($input['sortField']);
        }
        $query->setSortOrder($input['sortOrder']);

        $query->setCatchAll('*:*');
        $query->addFilterQuery('series_ids', $input['seriesId']);
        $this->addFiltersToQuery($query, $input);

        if ($this->_export) {
            $query->setReturnIdsOnly(true);
        }

        $this->_logger->debug("Query $query complete");
        return $query;
    }

    private function createAllSearchQuery($input) {
        $this->_logger->debug("Constructing query for all search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::ALL_DOCS);
        $query->setStart($input['start']);
        $query->setRows($input['rows']);
        $query->setSortField($input['sortField']);
        $query->setSortOrder($input['sortOrder']);

        $this->addFiltersToQuery($query, $input);

        if ($this->_export) {
            $query->setReturnIdsOnly(true);
        }

        $this->_logger->debug("Query $query complete");
        return $query;
    }

    private function addFiltersToQuery($query, $input) {
        foreach ($this->_filterFields as $filterField) {
            $facetKey = $filterField . 'fq';
            $facetValue = $input[$facetKey];
            if ($facetValue !== '') {
                $this->_logger->debug(
                    "request has facet key: $facetKey - value is: $facetValue - corresponding facet is: $filterField"
                );
                $query->addFilterQuery($filterField, $facetValue);
            }
        }
    }
}

