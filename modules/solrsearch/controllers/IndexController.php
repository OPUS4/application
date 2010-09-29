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
 * @category    Module_Solrsearch
 * @author      Julian Heise <heise@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Solrsearch_IndexController extends Controller_Action {

    const SIMPLE_SEARCH = 'simple';
    const ADVANCED_SEARCH = 'advanced';
    const AUTHOR_SEARCH = 'authorsearch';
    const COLLECTION_SEARCH = 'collection';
    const LATEST_SEARCH = 'latest';
    
    private $log;
    private $query;
    private $numOfHits;
    private $searchtype;
    private $resultList;

    public function  init() {
        parent::init();
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

    public function invalidsearchtermAction() {
        $this->view->title = $this->view->translate('solrsearch_title_invalidsearchterm');
        $searchtype = $this->getRequest()->getParam('searchtype');
        if ($searchtype === self::ADVANCED_SEARCH) {
            $this->view->searchType = self::ADVANCED_SEARCH;
        }
        else {
            $this->view->searchType = self::SIMPLE_SEARCH;
        }
    }

    public function searchdispatchAction() {
        $this->log->debug('Received new search request. Redirecting to search action.');
        $params = array();
        $action = 'search';

        $searchtype = $this->getRequest()->getParam('searchtype', 'invalid searchtype');
        if($searchtype === self::SIMPLE_SEARCH) {
            if(!$this->isSimpleSearchRequestValid()) {
                $action = 'invalidsearchterm';
                $params = array('searchtype' => self::SIMPLE_SEARCH);
            }
            else {
                $params= $this->createSimpleSearchUrlParams();
            }
        } 
        else if($searchtype === self::ADVANCED_SEARCH || $searchtype === self::AUTHOR_SEARCH) {
            if(!$this->isAdvancedSearchRequestValid()) {
                $action = 'invalidsearchterm';
                $params = array('searchtype' =>  $searchtype);
            }
            else {
                $params = $this->createAdvancedSearchUrlParams();
            }
        }

        $this->_redirectTo($action, '', null, null, $params);
    }

    private function isSimpleSearchRequestValid() {
        $query = $this->getRequest()->getParam('query');
        return !is_null($query);
    }

    private function isAdvancedSearchRequestValid() {
        foreach (array('author', 'title', 'referee', 'abstract', 'fulltext',  'year') as $fieldname) {
            $fieldvalue = $this->getRequest()->getParam($fieldname);
            if (!is_null($fieldvalue)) {
                return true;
            }
        }
        return false;
    }

    private function createSimpleSearchUrlParams() {
        $params = array(
                'searchtype'=> $this->getRequest()->getParam('searchtype', Solrsearch_IndexController::SIMPLE_SEARCH),
                'start'=> $this->getRequest()->getParam('start','0'),
                'rows'=> $this->getRequest()->getParam('rows','10'),
                'query'=> $this->getRequest()->getParam('query','*:*'),
                'sortfield'=> $this->getRequest()->getParam('sortfield', 'score'),
                'sortorder'=> $this->getRequest()->getParam('sortorder','desc')
            );
        return $params;
    }

    private function createAdvancedSearchUrlParams() {
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

        return $params;
    }

    public function searchAction() {
        $this->query = $this->buildQuery();
        $this->performSearch();
        $this->setViewValues();
        $this->setViewFacets();

        if($this->numOfHits === 0 || $this->query->getStart() >= $this->numOfHits) {
            $this->render('nohits');
        }
        else {
            $this->render('results');
        }
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

        if($this->searchtype === self::SIMPLE_SEARCH || $this->searchtype === self::COLLECTION_SEARCH) {
            $this->view->q = $this->query->getCatchAll();
            $this->view->nextPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>(int)($this->query->getStart()) + (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->prevPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>(int)($this->query->getStart()) - (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->lastPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>(int)($this->numOfHits / $this->query->getRows()) * $this->query->getRows(),'rows'=>$this->query->getRows()));
            $this->view->firstPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>'0','rows'=>$this->query->getRows()));
            $browsing = $this->getRequest()->getParam('browsing', 'false');
            $this->log->debug("Browsing: $browsing");
            if($browsing === 'true') {
                $this->view->specialTitle = $this->getRequest()->getParam('doctypefq', '');
            }
            return;
        }
        if($this->searchtype === self::ADVANCED_SEARCH || $this->searchtype === self::AUTHOR_SEARCH) {
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
            return;
        }
        if ($this->searchtype === self::LATEST_SEARCH) {            
            $this->view->isSimpleList = true;
            $this->view->specialTitle = $this->view->translate('title_latest_docs_article').' '.$this->query->getRows(). ' '.$this->view->translate('title_latest_docs');
            return;
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
        $this->view->browsing = (boolean) $this->getRequest()->getParam('browsing', false);
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
        if (is_null($this->getRequest()->getParams())) {
            $this->log->info('Unable to read request data. Search cannot be performed.');
            $this->_redirectToAndExit ('index');
        }

        if (is_null($this->getRequest()->getParam('searchtype'))) {
            $this->log->info('Unable to create query for unspecified searchtype');
            $this->_redirectToAndExit ('index');
        }

        $query = null;
        $this->searchtype = $this->getRequest()->getParam('searchtype');
        if ($this->searchtype === self::SIMPLE_SEARCH) {
            $query = $this->createSimpleSearchQuery();
        }
        else if ($this->searchtype === self::ADVANCED_SEARCH || $this->searchtype === self::AUTHOR_SEARCH) {
            $query = $this->createAdvancedSearchQuery();
        }
        else if ($this->searchtype === self::LATEST_SEARCH) {
            $query = $this->createLatestSearchQuery();
        }
        else if ($this->searchtype === self::COLLECTION_SEARCH) {
            $query = $this->createCollectionSearchQuery();
        }
        else {
            $this->log->info('Unable to create query for searchtype ' . $this->searchtype);
            $this->_redirectToAndExit ('index');
        }
        
        $this->validateQuery($query);
        return $query;
    }

    private function validateQuery($query) {
        // TODO check if the two subsequent rows checks are obsolete
        if($query->getRows() > 100) {
            $this->log->warn("Values greater than 100 are currently not allowed for the rows paramter.");
            $query->setRows('100');
        }
        if($query->getRows() < 1) {
            $this->log->warn("row parameter is smaller than 1: adjusting to 1.");
            $query->setRows('1');
        }
        if ($query->getStart() < 0) {
            $this->log->warn("A negative start parameter is ignored.");
            $query->setStart('0');
        }
        if($this->searchtype === self::ADVANCED_SEARCH || $this->searchtype === self::AUTHOR_SEARCH) {
            //im Falle einer Autorensuche werden Kommas und Semikolons aus dem Suchstring entfernt
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
        $query->setStart($this->getRequest()->getParam('start', Opus_SolrSearch_Query::DEFAULT_START));
        $query->setCatchAll($this->getRequest()->getParam('query', '*:*'));
        $query->setRows($this->getRequest()->getParam('rows', Opus_SolrSearch_Query::DEFAULT_ROWS));
        $query->setSortField($this->getRequest()->getParam('sortfield', Opus_SolrSearch_Query::DEFAULT_SORTFIELD));
        $query->setSortOrder($this->getRequest()->getParam('sortorder', Opus_SolrSearch_Query::DEFAULT_SORTORDER));

        $this->addFiltersToQuery($query);
        $this->log->debug("Query $query complete");
        return $query;
    }

    private function createLatestSearchQuery() {
        $this->log->debug("Constructing query for latest search.");
        
        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::LATEST_DOCS);
        $query->setRows($this->getRows($this->getRequest()->getParam('rows', Opus_SolrSearch_Query::DEFAULT_ROWS), 10, 100));
        $this->log->debug("Query $query complete");
        return $query;
    }

    private function prepareChildren() {
        $collectionList = null;
        try {
            $collectionList = new SolrSearch_Model_CollectionList($this->getRequest()->getParam('id'));
        }
        catch (SolrSearch_Model_Exception $e) {
            $this->log->debug($e->getMessage());
            $this->_redirectToAndExit('index', '', 'browse', null, array(), true);
        }        
        
        $this->view->children = $collectionList->getChildren();
        $this->view->parents = $collectionList->getParents();
        $this->view->collectionRoleTitle = $this->view->translate($collectionList->getCollectionRoleTitle());

        if ($collectionList->isRootCollection()) {
            $this->view->title = $this->view->translate($collectionList->getTitle());
        }
        else {
            $this->view->title = $collectionList->getTitle();
        }
        
        // Get the theme assigned to this collection iff usertheme is
        // set in the request.  To enable the collection theme, add
        // /usetheme/1/ to the URL.
        $usetheme = $this->getRequest()->getParam("usetheme");
        if (!is_null($usetheme) && 1 === (int) $usetheme) {
            $this->_helper->layout->setLayoutPath(APPLICATION_PATH . '/public/layouts/' . $collectionList->getTheme());
        }
        return $collectionList->getCollectionId();
    }

    private function createCollectionSearchQuery() {
        $this->log->debug("Constructing query for collection search.");

        $collectionId = $this->prepareChildren();

        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::SIMPLE);
        $query->setStart($this->getRequest()->getParam('start', Opus_SolrSearch_Query::DEFAULT_START));
        $query->setCatchAll('*:*');
        $query->setRows($this->getRequest()->getParam('rows', Opus_SolrSearch_Query::DEFAULT_ROWS));
        $query->setSortField($this->getRequest()->getParam('sortfield', Opus_SolrSearch_Query::DEFAULT_SORTFIELD));
        $query->setSortOrder($this->getRequest()->getParam('sortorder', Opus_SolrSearch_Query::DEFAULT_SORTORDER));

        $query->addFilterQuery('collection_ids:' . $collectionId);
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

    private function createAdvancedSearchQuery() {
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
