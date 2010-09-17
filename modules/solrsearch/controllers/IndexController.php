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
 * @category    View, SolrSearch
 * @author      Julian Heise <heise@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id:$
 */

/**
 * Controller for Solr search module
 */
class Solrsearch_IndexController extends Controller_Action {

    const SIMPLE_SEARCH = 'simple';
    const ADVANCED_SEARCH = 'advanced';
    const AUTHOR_SEARCH = 'authorsearch';
    
    private $log;
    private $query;
    private $numOfHits;
    private $searchtype;
    private $resultList;

    public function  init() {
        $this->log = Zend_Registry::get('Zend_Log');
    }

    public function indexAction() {
        $this->view->title = $this->view->translate('solrsearch_title_simple');
    }

    public function advancedAction() {
        $this->view->title = $this->view->translate('solrsearch_title_advanced');
    }

    public function nohitsAction() {
        $this->view->title = $this->view->translate('solrsearch_title_nohits');
    }

    public function resultsAction() {
        $this->view->title = $this->view->translate('solrsearch_title_results');
    }

    public function latestAction() {
        $data = null;
        if ($this->_request->isPost() === true)
            $data = $this->_request->getPost();
        else
            $data = $this->_request->getParams();
        
        $rows = $this->getRows($data, 10, 100);
        $this->query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::LATEST_DOCS);
        $this->query->setRows($rows);
        $searcher = new Opus_SolrSearch_Searcher();
        $this->resultList = $searcher->search($this->query);

        $this->setGeneralViewValues($data);
        $this->view->results = $this->resultList->getResults();
        $this->view->isSimpleList = true;
        $this->view->specialTitle = $this->view->translate('title_latest_docs_article').' '.$rows. ' '.$this->view->translate('title_latest_docs');
        $this->view->searchType = 'latest';
        $this->view->rows = $rows;

        $this->render('results');
    }

    public function invalidsearchtermAction() {
        $this->view->title = $this->view->translate('solrsearch_title_invalidsearchterm');
        $params = $this->_request->isPost() ? $this->_request->getPost() : $this->_request->getParams();
        $searchtype = array_key_exists('searchtype', $params) ? $params['searchtype'] : Solrsearch_IndexController::SIMPLE_SEARCH;
        $this->view->__set('searchType', $searchtype);
    }

    public function searchdispatchAction() {
        $this->log->debug('Received new search request. Redirecting to search action.');
        $url = '';

        $searchtype = $this->getRequest()->getParam('searchtype', 'invalid searchtype');
        if($searchtype === self::SIMPLE_SEARCH) {
            if(!$this->isSimpleSearchRequestValid()) {
                $url = $this->view->url(array(
                    'module' => 'solrsearch',
                    'controller' => 'index',
                    'action' => 'invalidsearchterm',
                    'searchtype' => self::SIMPLE_SEARCH), null, true);
            }
            else {
                $url = $this->createSimpleSearchUrl();
            }
        } 
        else if($searchtype === self::ADVANCED_SEARCH || $searchtype === self::AUTHOR_SEARCH) {
            if(!$this->isAdvancedSearchRequestValid()) {
                $url = $this->view->url(array(
                    'module' => 'solrsearch',
                    'controller' => 'index',
                    'action' => 'invalidsearchterm',
                    'searchtype' => $searchtype), null, true);
            }
            else {
                $url = $this->createAdvancedSearchUrl();
            }
        }

        $this->log->debug("URL is: " . $url);
        $this->redirectTo($url);     
    }

    private function isSimpleSearchRequestValid() {
        $query = $this->getRequest()->getParam('query', '');
        if ($query === '') {
            return false;
        }
        return true;
    }

    private function isAdvancedSearchRequestValid() {
        foreach (array('author', 'title', 'referee', 'abstract', 'fulltext',  'year') as $fieldname) {
            $fieldvalue = $this->getRequest()->getParam($fieldname, '');
            if ($fieldvalue !== '') {
                return true;
            }
        }
        return false;
    }

    private function createSimpleSearchUrl() {
        $params = array(
                'searchtype'=> $this->getRequest()->getParam('searchtype', Solrsearch_IndexController::SIMPLE_SEARCH),
                'start'=> $this->getRequest()->getParam('start','0'),
                'rows'=> $this->getRequest()->getParam('rows','10'),
                'query'=> $this->getRequest()->getParam('query','*:*'),
                'sortfield'=> $this->getRequest()->getParam('sortfield', 'score'),
                'sortorder'=> $this->getRequest()->getParam('sortorder','desc')
            );
        return $this->view->url(self::createSearchUrlArray($params), null, true);
    }

    private function createAdvancedSearchUrl() {
        $params = array (
            'searchtype'=> $this->getRequest()->getParam('searchtype',Solrsearch_IndexController::ADVANCED_SEARCH),
            'start'=> $this->getRequest()->getParam('start', '0'),
            'rows'=> $this->getRequest()->getParam('rows','10'),
            'sortfield'=> $this->getRequest()->getParam('sortfield','score'),
            'sortorder'=> $this->getRequest()->getParam('sortorder','desc')
        );

        foreach (array('author', 'title', 'abstract', 'fulltext', 'year', 'referee') as $fieldname) {
            $fieldvalue = $this->getRequest()->getParam($fieldname, '');
            if($fieldvalue !== '') {
                $params[$fieldname] = $fieldvalue;
                $params[$fieldname . 'modifier'] = $this->getRequest()->getParam($fieldname . 'modifier',Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL);
            }
        }

        return $this->view->url(self::createSearchUrlArray($params), null, true);
    }

    public function searchAction() {
        $this->query = $this->buildQuery();
        $this->performSearch();
        $this->setViewValues();
        $this->setViewFacets();

        if($this->numOfHits === 0 || $this->query->getStart() >= $this->numOfHits) {
            $this->render('nohits');
            return;
        }
        $this->render('results');            
    }

    private function performSearch() {
        $this->log->debug('performing search');
        $searcher = new Opus_SolrSearch_Searcher();
        $this->resultList = $searcher->search($this->query);
        $this->numOfHits = $this->resultList->getNumberOfHits();
        $this->log->debug("resultlist: $this->resultList");
    }

    private function setViewValues() {
        
        $this->setGeneralViewValues();

        if($this->searchtype === Solrsearch_IndexController::SIMPLE_SEARCH) {
            $this->view->q = $this->query->getCatchAll();            
            $this->view->nextPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>(int)($this->query->getStart()) + (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->prevPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>(int)($this->query->getStart()) - (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->lastPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>(int)($this->numOfHits / $this->query->getRows()) * $this->query->getRows(),'rows'=>$this->query->getRows()));
            $this->view->firstPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>'0','rows'=>$this->query->getRows()));
            return;
        }
        if($this->searchtype === Solrsearch_IndexController::ADVANCED_SEARCH || $this->searchtype === Solrsearch_IndexController::AUTHOR_SEARCH) {
            $this->view->nextPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'start'=>(int)($this->query->getStart()) + (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->prevPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'start'=>(int)($this->query->getStart()) - (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->lastPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'start'=>(int)($this->numOfHits / $this->query->getRows()) * $this->query->getRows(),'rows'=>$this->query->getRows()));
            $this->view->firstPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'start'=>'0','rows'=>$this->query->getRows()));
            $this->view->authorQuery = $this->query->getField('author');
            $this->view->titleQuery = $this->query->getField('title');
            $this->view->abstractQuery = $this->query->getField('abstract');
            $this->view->fulltextQuery = $this->query->getField('fulltext');
            $this->view->yearQuery = $this->query->getfield('year');
            $this->view->authorQueryModifier = $this->query->getModifier('author');
            $this->view->titleQueryModifier = $this->query->getModifier('title');
            $this->view->abstractQueryModifier = $this->query->getModifier('abstract');
            $this->view->yearQueryModifier = $this->query->getModifier('year');
            $this->view->refereeQuery = $this->query->getField('referee');
            $this->view->refereeQueryModifier = $this->query->getModifier('referee');
        }
    }

    private function setGeneralViewValues() {
        $this->view->results = $this->resultList->getResults();
        $this->view->searchType = $this->searchtype;
        $this->view->numOfHits = $this->numOfHits;
        $this->view->queryTime = $this->resultList->getQueryTime();
        $this->view->start = $this->query->getStart();
        $this->view->numOfPages = (int) ($this->numOfHits / $this->query->getRows()) + 1;
        $this->view->rows = $this->query->getRows();
        $this->view->authorSearch = self::createSearchUrlArray(array('searchtype' => self::AUTHOR_SEARCH));
        $this->view->isSimpleList = false;
        $this->view->browsing = (boolean)$this->getRequest()->getParam('browsing',false);
        $specialTitle = $this->getRequest()->getParam('specialtitle','');
        if($specialTitle !== '')
            $this->view->specialTitle = $specialTitle;
        $this->view->sortfield = $this->getRequest()->getParam('sortfield', 'score');
        $this->view->sortorder = $this->getRequest()->getParam('sortorder', 'desc');
    }

    private function setViewFacets() {
        $facets = $this->resultList->getFacets();
        $facetArray = array();
        $selectedFacets = array();

        foreach($facets as $key=>$facet) {
            $this->log->debug("found $key facet in search results");

            $facetValue = $this->getRequest()->getParam($key.'fq','');
            if($facetValue !== '') {
                $selectedFacets[$key] = $facetValue;
            }

            if(count($facets[$key]) > 1 || $facetValue !== '') {
                $facetArray[$key] = $facet;
            }
        }
        
        $this->view->__set('facets', $facetArray);
        $this->view->__set('selectedFacets', $selectedFacets);
    }

    private function buildQuery() {

        if (is_null($this->getRequest()->getParams()))
            throw new Application_Exception("Unable to read request data. Search cannot be performed.");

        if ($this->getRequest()->getParam('searchtype', '') === '')
            throw new Application_Exception("Unable to create query for unspecified searchtype");

        $query = null;
        $this->searchtype = $this->getRequest()->getParam('searchtype','');
        if ($this->searchtype === Solrsearch_IndexController::SIMPLE_SEARCH)
            $query = $this->createSimpleSearchQuery();
        else if ($this->searchtype === Solrsearch_IndexController::ADVANCED_SEARCH || $this->searchtype === Solrsearch_IndexController::AUTHOR_SEARCH)
            $query = $this->createAdvancedSearchQuery();
        else
            throw new Application_Exception("Unable to create query for searchtype " . $this->searchtype);

        $this->validateQuery($query);
        return $query;
    }

    private function validateQuery($query) {
        if($query->getRows() > 100) {
            $this->log->warn("Values greater than 100 are currently not allowed for the rows paramter.");
            $query->setRows('100');
        }
        if($query->getRows() < 1) {
            $this->log->warn("row parameter is smaller than 1: adjusting to 1 ");
            $query->setRows('1');
        }
        if ($query->getStart() < 0) {
            $this->log->warn("a negative start parameter is ignored");
            $query->setStart('0');
        }
        $searchType = $query->getSearchType();
        if($searchType === Solrsearch_IndexController::ADVANCED_SEARCH || $searchType === Solrsearch_IndexController::AUTHOR_SEARCH) {
            if (!is_null($query->getField('author'))) {
                $author = $query->getField('author');
                $authormodifier = $query->getModifier('author');
                $query->setField('author', str_replace(array(',', ';'), '', $author), $authormodifier);
            }
        }
    }

    private function createSimpleSearchQuery() {
        $this->log->debug("Constructing query for simple search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setStart($this->getRequest()->getParam('start',Opus_SolrSearch_Query::DEFAULT_START));
        $query->setCatchAll($this->getRequest()->getParam('query', '*:*'));
        $query->setRows($this->getRequest()->getParam('rows', Opus_SolrSearch_Query::DEFAULT_ROWS));
        $query->setSortField($this->getRequest()->getParam('sortfield', Opus_SolrSearch_Query::DEFAULT_SORTFIELD));
        $query->setSortOrder($this->getRequest()->getParam('sortorder', Opus_SolrSearch_Query::DEFAULT_SORTORDER));

        $this->addFiltersToQuery($query);
        $this->log->debug("Query $query complete");
        return $query;
    }

    private function addFiltersToQuery($query) {
        $config = Zend_Registry::get("Zend_Config");

        if(!isset($config->searchengine->solr->facets)){
            $this->log->debug("key searchengine.solr.facets is not present in config. skipping filter queries");
            return;
        }

        $facets = $config->searchengine->solr->facets;
        $this->log->debug("searchengine.solr.facets is set to " . $facets);
        $facetsArray = explode(",", $facets);

        foreach($facetsArray as $facet) {
            $facet = trim($facet);
            $facetKey = $facet."fq";
            $facetValue = $this->getRequest()->getParam($facetKey, '');
            if($facetValue !== '') {
                $this->log->debug("request has facet key: ".$facetKey." value is: ".$facetValue." corresponding facet is: ".$facet);
                $query->addFilterQuery($facet.":".$facetValue);
            }
        }
    }

    private function createAdvancedSearchQuery($data) {
        $this->log->debug("Constructing query for advanced search.");

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::ADVANCED);
        $query->setStart($this->getRequest()->getParam('start', Opus_SolrSearch_Query::DEFAULT_START));
        $query->setRows($this->getRequest()->getParam('rows', Opus_SolrSearch_Query::DEFAULT_ROWS));
        $query->setSortField($this->getRequest()->getParam('sortfield', Opus_SolrSearch_Query::DEFAULT_SORTFIELD));
        $query->setSortOrder($this->getRequest()->getParam('sortorder', Opus_SolrSearch_Query::DEFAULT_SORTORDER));

        foreach (array('author', 'title', 'referee', 'abstract', 'fulltext', 'year' ) as $fieldname) {
            $fieldvalue = $this->getRequest()->getParam($fieldname, '');
            if (!empty($fieldvalue)) {
                $fieldmodifier = $this->getRequest()->getParam($fieldname . 'modifier', Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL);
                $query->setField($fieldname, $fieldvalue, $fieldmodifier);
            }
        }

        $this->addFiltersToQuery($query);
        $this->log->debug("Query $query complete");
        return $query;
    }

    /**
     * Creates an URL to execute a search. The URL will be mapped to:
     * module=solrsearch, controller=index, action=search
     */
    public static function createSearchUrlArray($params = array()) {
        $url = array(
            'module' => 'solrsearch',
            'controller' => 'index',
            'action' => 'search');
        foreach($params as $key=>$value) {
            $url[$key]=$value;
        }
        return $url;
    }

    /**
     * Returns the actual rows parameter value if it is between $lowerBoundInclusive
     * and $upperBoundInclusive. Otherwise, in case the actual value is smaller (greater)
     * than $lowerBoundInclusive ($upperBoundInclusive) it is adjusted to
     * $lowerBoundInclusive ($upperBoundInclusive).
     *
     * @param array $data An array that contains the request parameters.
     * @param int $lowerBoundInclusive The lower bound.
     * @param int $upperBoundInclusive The upper bound.
     * @return int Returns the actual rows parameter value or an adjusted value if
     * it is not in the interval [$lowerBoundInclusive, $upperBoundInclusive].
     */
    private function getRows($data, $lowerBoundInclusive, $upperBoundInclusive) {
        $rows = (int) $this->getRequest()->getParam('rows', $lowerBoundInclusive);
        if($rows < $lowerBoundInclusive) {
            return $lowerBoundInclusive;
        }
        if($rows > $upperBoundInclusive) {
            return $upperBoundInclusive;
        }
        return $rows;
    }
}
?>
