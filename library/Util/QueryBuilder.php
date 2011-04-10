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

class Util_QueryBuilder {
    
    private $log;
    private $filterFields;
    private $searchFields;

    public function __construct() {
        $this->log = Zend_Registry::get('Zend_Log');

        $this->filterFields = array();
        $config = Zend_Registry::get("Zend_Config");
        if (!isset($config->searchengine->solr->facets)){
            $this->log->debug("key searchengine.solr.facets is not present in config. skipping filter queries");
        }
        $filters = $config->searchengine->solr->facets;
        $this->log->debug("searchengine.solr.facets is set to $filters");

        foreach (explode(',', $filters) as $filterfield) {
            array_push($this->filterFields, trim($filterfield));
        }

        $this->searchFields = array('author', 'title', 'referee', 'abstract', 'fulltext', 'year');
    }

    public function createQueryBuilderInputFromRequest($request) {
        if (is_null($request->getParams())) {
            throw new Util_QueryBuilderException('Unable to read request data. Search cannot be performed.');
        }

        if (is_null($request->getParam('searchtype'))) {
            throw new Util_QueryBuilderException('Unspecified search type: unable to create query.');
        }

        if (!Util_Searchtypes::isSupported($request->getParam('searchtype'))) {
            throw new Util_QueryBuilderException('Unsupported search type ' . $request->getParam('searchtype') . ' : unable to create query.');
        }

        $this->validateParamsType($request);

        $input = array(
            'searchtype' => $request->getParam('searchtype'),
            'start' => $request->getParam('start', Opus_SolrSearch_Query::DEFAULT_START),
            'rows' => $request->getParam('rows', Opus_SolrSearch_Query::DEFAULT_ROWS),
            'sortField' => $request->getParam('sortfield', Opus_SolrSearch_Query::DEFAULT_SORTFIELD),
            'sortOrder' => $request->getParam('sortorder', Opus_SolrSearch_Query::DEFAULT_SORTORDER),
            'query' => $request->getParam('query', '*:*')
        );

        foreach ($this->searchFields as $searchField) {
            $input[$searchField] = $request->getParam($searchField, '');
            $input[$searchField . 'modifier'] = $request->getParam($searchField . 'modifier', Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL);
        }

        foreach ($this->filterFields as $filterField) {
            $param = $filterField . 'fq';
            $input[$param] = $request->getParam($param, '');                        
        }
       
        return $input;
    }

    /**
     * Checks if all given parameters are of type string. Otherwise, throws Util_QueryBuilderException.
     *
     * @throws Util_QueryBuilderException
     */
    private function validateParamsType($request) {
        $paramNames = array(
            'searchtype',
            'start',
            'rows',
            'sortField',
            'sortOrder',
            'query',
        );
        foreach ($this->searchFields as $searchField) {
            array_push($paramNames, $searchField, $searchField . 'modifier');
        }
        foreach ($this->filterFields as $filterField) {
            array_push($paramNames, $filterField . 'fq');
        }

        foreach ($paramNames as $paramName) {
            $paramValue = $request->getParam($paramName, null);
            if (!is_null($paramValue) && !is_string($paramValue)) {
                throw new Util_QueryBuilderException('Parameter ' . $paramName . ' is not of type string');
            }
        }
    }

    public function createSearchQuery($input) {
        if ($input['searchtype'] === Util_Searchtypes::SIMPLE_SEARCH) {
            return $this->createSimpleSearchQuery($input);
        }
        if ($input['searchtype'] === Util_Searchtypes::ADVANCED_SEARCH || $input['searchtype'] === Util_Searchtypes::AUTHOR_SEARCH) {
            return $this->createAdvancedSearchQuery($input);
        }
        if ($input['searchtype'] === Util_Searchtypes::LATEST_SEARCH) {
            return $this->createLatestSearchQuery($input);
        }
        if ($input['searchtype'] === Util_Searchtypes::COLLECTION_SEARCH) {
            return $this->createCollectionSearchQuery($input);
        }
        if ($input['searchtype'] === Util_Searchtypes::ALL_SEARCH) {
            return $this->createAllSearchQuery($input);
        }
    }

    private function createSimpleSearchQuery($input) {
        $this->log->debug("Constructing query for simple search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setStart($input['start']);        
        $query->setRows($input['rows']);
        $query->setSortField($input['sortField']);
        $query->setSortOrder($input['sortOrder']);

        $query->setCatchAll($input['query']);
        $this->addFiltersToQuery($query, $input);
        
        $this->log->debug("Query $query complete");
        return $query;
    }

    private function createAdvancedSearchQuery($input) {
        $this->log->debug("Constructing query for advanced search.");
        
        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::ADVANCED);
        $query->setStart($input['start']);
        $query->setRows($input['rows']);
        $query->setSortField($input['sortField']);
        $query->setSortOrder($input['sortOrder']);

        foreach (array('author', 'title', 'referee', 'abstract', 'fulltext', 'year') as $fieldname) {
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

        $this->log->debug("Query $query complete");
        return $query;
    }

    private function createLatestSearchQuery($input) {
        $this->log->debug("Constructing query for latest search.");
        
        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::LATEST_DOCS);
        $query->setRows($input['rows']);

        $this->log->debug("Query $query complete");
        return $query;
    }

    private function createCollectionSearchQuery($input) {
        $this->log->debug("Constructing query for collection search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setStart($input['start']);        
        $query->setRows($input['rows']);
        $query->setSortField($input['sortField']);
        $query->setSortOrder($input['sortOrder']);

        $query->setCatchAll('*:*');
        $query->addFilterQuery('collection_ids', $input['collectionId']);
        $this->addFiltersToQuery($query, $input);

        $this->log->debug("Query $query complete");
        return $query;
    }

    private function createAllSearchQuery($input) {
        $this->log->debug("Constructing query for all search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::ALL_DOCS);
        $query->setStart($input['start']);
        $query->setRows($input['rows']);
        $query->setSortField($input['sortField']);
        $query->setSortOrder($input['sortOrder']);
        
        $this->addFiltersToQuery($query, $input);

        $this->log->debug("Query $query complete");
        return $query;
    }

    private function addFiltersToQuery($query, $input) {
        foreach($this->filterFields as $filterField) {
            $facetKey = $filterField . 'fq';
            $facetValue = $input[$facetKey];
            if ($facetValue !== '') {
                $this->log->debug("request has facet key: $facetKey - value is: $facetValue - corresponding facet is: $filterField");
                $query->addFilterQuery($filterField, $facetValue);
            }
        }
    }
}
?>