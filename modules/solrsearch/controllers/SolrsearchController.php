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
 * @category    View
 * @author      Julian Heise <heise@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
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
     * the type of search to use; must be either 'simple' or 'advanced'
     * @var string
     */
    private $searchtype;

    /**
     *
     * @var Opis_SolrSearch_ResultList
     */
    private $resultList;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        parent::__construct($request, $response, $invokeArgs);
        $this->log = Zend_Registry::getInstance()->get("Zend_Log");
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

    public function searchdispatchAction() {
        $this->log->debug("Received new search request. Redirecting to search action");

        $redirector = $this->_helper->getHelper('Redirector');
        $redirector->setPrependBase(false);
        $redirector->setGotoUrl('');
        $redirector->setExit(false);
        $requestData = null;
        $url = '';

        if ($this->_request->isPost() === true)
            $requestData = $this->_request->getPost();
        else
            $requestData = $this->_request->getParams();

        if($requestData['searchtype'] === 'simple') {
            $url = $this->view->url(array(
                'module'=>'solrsearch',
                'controller'=>'solrsearch',
                'action'=>'search',
                'searchtype'=>  array_key_exists('searchtype', $requestData) ? $requestData['searchtype'] : 'simple',
                'start'=> array_key_exists('start', $requestData) ? $requestData['start'] : '0',
                'rows'=> array_key_exists('rows', $requestData) ? $requestData['rows'] : '10',
                'query'=> array_key_exists('query', $requestData) ? $requestData['query'] : '*:*',
                'sortfield'=> array_key_exists('sortfield', $requestData) ? $requestData['sortfield'] : 'score',
                'sortorder'=> array_key_exists('sortorder', $requestData) ? $requestData['sortorder'] : 'desc')
            , null, true);
        }
        $this->log->debug("URL is: " . $url);
        $redirector->gotoUrl($url);
    }

    public function searchAction() {
        $this->query = $this->buildQuery($this->_request);
        $this->performSearch();

        if (0 === $this->numOfHits)
            $this->render('nohits');
        else
            $this->render('results');
    }

    /**
     *
     */
    public function authorSearchAction() {
        // TODO implement
        $this->render('nohits');
    }

    /**
     * Performs the SolrSearch using the $query instance variable. Has side-effect:
     * some view parameters are set in order to display results
     */
    private function performSearch() {
        $this->log->debug("performing search");

        $this->searcher = new Opus_SolrSearch_Searcher();
        $this->resultList = $this->searcher->search($this->query);
        $this->numOfHits = $this->resultList->getNumberOfHits();
        $this->log->debug("resultlist: " . $this->resultList);

        $this->view->__set("results", $this->resultList->getResults());
        $this->view->__set("searchType", $this->searchtype);
        $this->view->__set("numOfHits", $this->numOfHits);
        $this->view->__set("queryTime", $this->resultList->getQueryTime());
        $this->view->__set("start", $this->query->getStart());
        $this->view->__set("numOfPages", (int) ($this->numOfHits / $this->query->getRows()) + 1);
        $this->view->__set("rows", $this->query->getRows());
        $this->view->__set("q", $this->query->getQ());

        // TODO für das erzeugen der arrays eine weiche einbauen um zwischen simple und advanced zu unterscheiden
        $this->view->__set("nextPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'query'=>$this->query->getQ(),'start'=>(int)($this->query->getStart()) + (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
        $this->view->__set("prevPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'query'=>$this->query->getQ(),'start'=>(int)($this->query->getStart()) - (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
        $this->view->__set("lastPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'query'=>$this->query->getQ(),'start'=>(int)($this->numOfHits / $this->query->getRows()) * $this->query->getRows(),'rows'=>$this->query->getRows()));
        $this->view->__set("firstPage", array('module'=>'solrsearch','controller'=>'solrsearch','action'=>'search','searchtype'=>$this->searchtype,'query'=>$this->query->getQ(),'start'=>'0','rows'=>$this->query->getRows()));

        $facets = $this->resultList->getFacets();
        if (array_key_exists('year', $facets)) {
            $this->view->__set("yearFacet", $facets['year']);
        }

        $this->log->debug("search complete");
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
        }
        else {
            $this->log->debug("Request is non post. Trying to extract data. Request should be post normally.");
            $data = $request->getParams();
        }

        if (is_null($data)) {
            throw new Application_Exception("Unable to read request data. Search cannot be performed.");
        }

        if (!array_key_exists('searchtype', $data)) {
            throw new Application_Exception("Unable to create query for unspecified searchtype");
        }
        $this->searchtype = $data['searchtype'];

        if ($this->searchtype === 'simple') {
            return $this->createSimpleSearchQuery($data);
        }
        if ($this->searchtype === 'advanced') {
            return $this->createAdvancedSearchQuery($data);
        }

        throw new Application_Exception("Unable to create query for searchtype " . $this->searchtype);
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

        if(isset($data['year'])) {
            $this->log->debug("year filter query is set to: ".$data['year']);
            $query->addFilterQuery("year:".$data['year']);
        }

        $this->log->debug("Query $query complete");

        return $query;
    }

    private function createAdvancedSearchQuery($data) {
        // TODO implement
        return null;
    }

}
?>
