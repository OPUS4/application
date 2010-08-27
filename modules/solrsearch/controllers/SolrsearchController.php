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
class Solrsearch_SolrsearchController extends Zend_Controller_Action {

    /**
     * A searcher object handling Solr communication
     * @var Opus_SolrSearch_Searcher
     */
    private $searcher;
    /**
     * Zend Logger
     * @var Zend_Log
     */
    private $log;
    /**
     * Current Solr search query
     * @var Opus_SolrSearch_Query
     */
    private $query;
    /**
     * Flag for search type. True if a simple search was performed. False if an
     * advanced search was performed
     * @var <type> boolean
     */

    /**
     * the number of hits returned by the last search request
     * @var int
     */
    private $numOfHits;

    /**
     * the type of search to use; either 'simple' or 'advanced'
     * @var string
     */
    private $searchtype;

    /**
     * 
     * @var Opus_SolrSearch_ResultList
     */
    private $resultList;

    public function  init() {
        $this->log = Zend_Registry::get('Zend_Log');
    }

    /**
     * Shows the simple search page
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('solrsearch_title_simple');
    }

    /**
     * Shows the advanced search page
     */
    public function advancedAction() {
        $this->view->title = $this->view->translate('solrsearch_title_advanced');
    }

    /**
     * Shows the no hits page
     */
    public function nohitsAction() {
        $this->view->title = $this->view->translate('solrsearch_title_nohits');
    }

    public function resultsAction() {
        $this->view->title = $this->view->translate('solrsearch_title_results');
    }

    public function invalidsearchtermAction() {
        $this->view->title = $this->view->translate('solrsearch_title_invalidsearchterm');
        $params = $this->_request->isPost() ? $this->_request->getPost() : $this->_request->getParams();
        $searchtype = array_key_exists('searchtype', $params) ? $params['searchtype'] : 'simple';
        $this->view->__set('searchType', $searchtype);
    }

    public function searchdispatchAction() {
        $this->log->debug("Received new search request. Redirecting to search action");

        $redirector = $this->configureRedirector();
        $requestData = null;
        $url = '';

        if ($this->_request->isPost() === true)
            $requestData = $this->_request->getPost();
        else
            $requestData = $this->_request->getParams();

        $searchtype = $requestData['searchtype'];
        if($searchtype === 'simple') {
            $url = $this->createSimpleSearchUrl($requestData);
            if(!$this->isSimpleSearchRequestValid($requestData)) {
                $url = $this->view->url(array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'invalidsearchterm','searchtype'=>'simple'), null, true);
            }
        } else if($searchtype === 'advanced' || $searchtype === 'authorsearch') {
            $url = $this->createAdvancedSearchUrl($requestData);
            if(!$this->isAdvancedSearchRequestValid($requestData)) {
                $url = $this->view->url(array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'invalidsearchterm','searchtype'=>$searchtype), null, true);
            }
        }

        $this->log->debug("URL is: " . $url);
        $redirector->gotoUrl($url);
    }

    private function configureRedirector() {
        $redirector = $this->_helper->getHelper('Redirector');
        $redirector->setPrependBase(false);
        $redirector->setGotoUrl('');
        $redirector->setExit(false);
        return $redirector;
    }

    private function isSimpleSearchRequestValid($data) {
        if(!array_key_exists('query', $data) || $data['query'] === '')
            return false;
        return true;
    }

    private function isAdvancedSearchRequestValid($data) {
        $author = array_key_exists('author', $data) ? $data['author'] : '';
        $abstract = array_key_exists('abstract', $data) ? $data['abstract'] : '';
        $fulltext = array_key_exists('fulltext', $data) ? $data['fulltext'] : '';
        $title = array_key_exists('title', $data) ? $data['title'] : '';
        $year = array_key_exists('year', $data) ? $data['year'] : '';
        $evaluator = array_key_exists('evaluator', $data) ? $data['evaluator'] : '';

        if($author === '' && $abstract === '' && $fulltext === '' && $title === '' && $year === '' && $evaluator === '')
            return false;

        return true;
    }

    /**
     * Creates an URL for the simple search
     * @param array $data
     * @return string
     */
    private function createSimpleSearchUrl($data) {
        $simpleUrl = $this->view->url(array(
                'module'=>'solrsearch',
                'controller'=>'solrsearch',
                'action'=>'search',
                'searchtype'=> array_key_exists('searchtype', $data) ? $data['searchtype'] : 'simple',
                'start'=> array_key_exists('start', $data) ? $data['start'] : '0',
                'rows'=> array_key_exists('rows', $data) ? $data['rows'] : '10',
                'query'=> array_key_exists('query', $data) ? $data['query'] : '*:*',
                'sortfield'=> array_key_exists('sortfield', $data) ? $data['sortfield'] : 'score',
                'sortorder'=> array_key_exists('sortorder', $data) ? $data['sortorder'] : 'desc')
            , null, true);
        return $simpleUrl;
    }

    private function createAdvancedSearchUrl($data) {
        $urlArray = array(
                'module'=>'solrsearch',
                'controller'=>'solrsearch',
                'action'=>'search',
                'searchtype'=> array_key_exists('searchtype', $data) ? $data['searchtype'] : 'simple',
                'start'=> array_key_exists('start', $data) ? $data['start'] : '0',
                'rows'=> array_key_exists('rows', $data) ? $data['rows'] : '10',
                'sortfield'=> array_key_exists('sortfield', $data) ? $data['sortfield'] : 'score',
                'sortorder'=> array_key_exists('sortorder', $data) ? $data['sortorder'] : 'desc',
            );

        if(array_key_exists('author', $data) && $data['author'] != '')
            $urlArray['author'] = $data['author'];

        if(array_key_exists('title', $data) && $data['title'] != '')
            $urlArray['title'] = $data['title'];

        if(array_key_exists('abstract', $data) && $data['abstract'] != '')
            $urlArray['abstract'] = $data['abstract'];

        if(array_key_exists('fulltext', $data) && $data['fulltext'] != '')
            $urlArray['fulltext'] = $data['fulltext'];

        if(array_key_exists('year', $data) && $data['year'] != '')
            $urlArray['year'] = $data['year'];

        if(array_key_exists('authormodifier', $data) && $data['authormodifier'] != '')
            $urlArray['authormodifier'] = $data['authormodifier'];

        if(array_key_exists('titlemodifier', $data) && $data['titlemodifier'] != '')
            $urlArray['titlemodifier'] = $data['titlemodifier'];

        if(array_key_exists('abstractmodifier', $data) && $data['abstractmodifier'] != '')
            $urlArray['abstractmodifier'] = $data['abstractmodifier'];

        if(array_key_exists('yearmodifier', $data) && $data['yearmodifier'] != '')
            $urlArray['yearmodifier'] = $data['yearmodifier'];

        if(array_key_exists('evaluatormodifier', $data) && $data['evaluatormodifier'] != '')
            $urlArray['evaluatormodifier'] = $data['evaluatormodifier'];

        if(array_key_exists('evaluator', $data) && $data['evaluator'] != '')
            $urlArray['evaluator'] = $data['evaluator'];

        if(array_key_exists('fulltextmodifier', $data) && $data['fulltextmodifier'] != '')
            $urlArray['fulltextmodifier'] = $data['fulltextmodifier'];

        $this->log->debug("author form param val: " .$data['author']);

        $advancedUrl = $this->view->url($urlArray, null, true);
        return $advancedUrl;
    }

    public function searchAction() {
        $this->query = $this->buildQuery($this->_request);
        $this->performSearch();
        $this->setViewValues();
        $this->createFacetsForView();

        if(0 === $this->numOfHits || $this->query->getStart() >= $this->numOfHits) {
            $this->render('nohits');
            return;
        }
        $this->render('results');            
    }

    private function performSearch() {
        $this->log->debug("performing search");
        $this->searcher = new Opus_SolrSearch_Searcher();
        $this->resultList = $this->searcher->search($this->query);
        $this->numOfHits = $this->resultList->getNumberOfHits();
        $this->log->debug("resultlist: " . $this->resultList);
    }

    private function setViewValues() {
        $this->view->__set("results", $this->resultList->getResults());
        $this->view->__set("searchType", $this->searchtype);
        $this->view->__set("numOfHits", $this->numOfHits);
        $this->view->__set("queryTime", $this->resultList->getQueryTime());
        $this->view->__set("start", $this->query->getStart());
        $this->view->__set("numOfPages", (int) ($this->numOfHits / $this->query->getRows()) + 1);
        $this->view->__set("rows", $this->query->getRows());
        $this->view->__set("q", $this->query->getCatchAll());
        $this->view->__set("authorSearch", array("module"=>"solrsearch","controller"=>"solrsearch","action"=>"search","searchtype"=>"advanced"));

        if($this->searchtype === 'simple') {
            $this->view->__set("nextPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'query'=>$this->query->getQ(),'start'=>(int)($this->query->getStart()) + (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->__set("prevPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'query'=>$this->query->getQ(),'start'=>(int)($this->query->getStart()) - (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->__set("lastPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'query'=>$this->query->getQ(),'start'=>(int)($this->numOfHits / $this->query->getRows()) * $this->query->getRows(),'rows'=>$this->query->getRows()));
            $this->view->__set("firstPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'query'=>$this->query->getQ(),'start'=>'0','rows'=>$this->query->getRows()));
        } else if($this->searchtype === 'advanced' || $this->searchtype === 'authorsearch') {
            $this->view->__set("nextPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'start'=>(int)($this->query->getStart()) + (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->__set("prevPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'start'=>(int)($this->query->getStart()) - (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->__set("lastPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'start'=>(int)($this->numOfHits / $this->query->getRows()) * $this->query->getRows(),'rows'=>$this->query->getRows()));
            $this->view->__set("firstPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'start'=>'0','rows'=>$this->query->getRows()));
            $this->view->__set("authorQuery", $this->query->getField('author'));
            $this->view->__set("titleQuery", $this->query->getField('title'));
            $this->view->__set("abstractQuery", $this->query->getField('abstract'));
            $this->view->__set("fulltextQuery", $this->query->getField('fulltext'));
            $this->view->__set("yearQuery", $this->query->getfield('year'));
            $this->view->__set("authorQueryModifier", $this->query->getModifier('author'));
            $this->view->__set("titleQueryModifier", $this->query->getModifier('title'));
            $this->view->__set("abstractQueryModifier", $this->query->getModifier('abstract'));
            $this->view->__set("yearQueryModifier", $this->query->getModifier('year'));
            $this->view->__set("evaluatorQuery", $this->query->getField('referee'));
            $this->view->__set("evaluatorQueryModifier", $this->query->getModifier('referee'));
        }
    }

    private function createFacetsForView() {
        $facets = $this->resultList->getFacets();
        $facetArray = array();
        foreach($facets as $key=>$facet) {
            $this->log->debug("found ".$key." facet in search results");
            $facetArray[$key] = $facet;
        }
        $this->view->__set("facets", $facetArray);
    }

    /**
     * Builds an Opus_SolrSearch_Query using form values.
     * @return Opus_SolrSearch_Query
     * @throws Application_Exception if any of the parameters couldn't be read
     */
    private function buildQuery($request) {
        $data = null;

        if ($request->isPost() === true) {
            $this->log->debug("Request is post. Extracting data.");
            $data = $request->getPost();
        } else {
            $this->log->debug("Request is non post. Trying to extract data. Request should be post normally.");
            $data = $request->getParams();
        }

        if (is_null($data)) 
            throw new Application_Exception("Unable to read request data. Search cannot be performed.");

        if (!array_key_exists('searchtype', $data)) 
            throw new Application_Exception("Unable to create query for unspecified searchtype");
        
        $data = $this->validateParameterValues($data);

        $this->searchtype = $data['searchtype'];
        if ($this->searchtype === 'simple') 
            return $this->createSimpleSearchQuery($data);
        if ($this->searchtype === 'advanced' || $this->searchtype === 'authorsearch')
            return $this->createAdvancedSearchQuery($data);

        throw new Application_Exception("Unable to create query for searchtype " . $this->searchtype);
    }

    private function validateParameterValues($data) {
        if(isset($data['rows']) && (int)$data['rows'] > 100) {
            $this->log->warn("Values greater than 100 are currently not allowed for the rows paramter.");
            $data['rows'] = '100';
        }
        if(isset($data['rows']) && (int)$data['rows'] <= 0) {
            $this->log->warn("row parameter is smaller than 1: adjust it to 1 ");
            $data['rows'] = '1';
        }
        if (isset($data['start']) && (int)$data['start'] < 0) {
            $this->log->warn("a negative start parameter is ignored");
            $data['start'] = '0';
        }
        if($data['searchtype'] === 'advanced' || $data['searchtype'] === 'authorsearch') {
            if (isset($data['author'])) {
                $data['author'] = str_replace(array(',', ';'), '', $data['author']);
            }
        }
        return $data;
    }

    private function createSimpleSearchQuery($data) {

        // TODO validate request parameters
        $this->log->debug("Constructing query for simple search.");

        $start = array_key_exists('start', $data) ? $data['start'] : '0';
        $rows = array_key_exists('rows', $data) ? $data['rows'] : '10';
        $catchAll = array_key_exists('query', $data) ? $data['query'] : '*:*';
        $sortfield = array_key_exists('sortfield', $data) ? $data['sortfield'] : 'score';
        $sortorder = array_key_exists('sortorder', $data) ? $data['sortorder'] : 'desc';

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setStart($start);
        $query->setCatchAll($catchAll);
        $query->setRows($rows);
        $query->setSortField($sortfield);
        $query->setSortOrder($sortorder);

        $this->addFiltersToQuery($data, $query);
        $this->log->debug("Query $query complete");
        return $query;
    }

    private function addFiltersToQuery($data, $query) {
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
            if(array_key_exists($facetKey, $data)) {
                $this->log->debug("request has facet key: ".$facetKey." value is: ".$data[$facetKey]." corresponding facet is: ".$facet);
                $query->addFilterQuery($facet.":".$data[$facetKey]);
            }
        }
    }

    private function createAdvancedSearchQuery($data) {
        $this->log->debug("constructing query for advanced search");

        $start = array_key_exists('start', $data) ? $data['start'] : '0';
        $rows = array_key_exists('rows', $data) ? $data['rows'] : '10';
        $sortfield = array_key_exists('sortfield', $data) ? $data['sortfield'] : 'score';
        $sortorder = array_key_exists('sortorder', $data) ? $data['sortorder'] : 'desc';
        $author = array_key_exists('author', $data) ? $data['author'] : '';
        $abstract = array_key_exists('abstract', $data) ? $data['abstract'] : '';
        $fulltext = array_key_exists('fulltext', $data) ? $data['fulltext'] : '';
        $title = array_key_exists('title', $data) ? $data['title'] : '';
        $year = array_key_exists('year', $data) ? $data['year'] : '';
        $authormodifier = array_key_exists('authormodifier', $data) ? $data['authormodifier'] : Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL;
        $abstractmodifier = array_key_exists('abstractmodifier', $data) ? $data['abstractmodifier'] : Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL;
        $titlemodifier = array_key_exists('titlemodifier', $data) ? $data['titlemodifier'] : Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL;
        $yearmodifier = array_key_exists('yearmodifier', $data) ? $data['yearmodifier'] : Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL;
        $evaluator = array_key_exists('evaluator', $data) ? $data['evaluator'] : '';
        $evaluatorModifier = array_key_exists('evaluatormodifier', $data) ? $data['evaluatormodifier'] : Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL;
        $fulltextmodifier = array_key_exists('fulltextmodifier', $data) ? $data['fulltextmodifier'] : Opus_SolrSearch_Query::SEARCH_MODIFIER_CONTAINS_ALL;

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::ADVANCED);
        $query->setStart($start);
        $query->setRows($rows);
        $query->setSortField($sortfield);
        $query->setSortOrder($sortorder);
        if($author != '') $query->setField('author', $author, $authormodifier);
        if($abstract != '') $query->setField('abstract', $abstract, $abstractmodifier);
        if($fulltext != '') $query->setField('fulltext', $fulltext, $fulltextmodifier);
        if($title != '') $query->setField('title', $title, $titlemodifier);
        if($year != '') $query->setField('year', $year, $yearmodifier);
        if($evaluator != '') $query->setField('referee', $evaluator, $evaluatorModifier);

        $this->addFiltersToQuery($data, $query);
        $this->log->debug("Query $query complete");
        return $query;
    }
}
?>
