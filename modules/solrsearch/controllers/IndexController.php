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
 * @package     Module_Solrsearch
 * @author      Julian Heise <heise@zib.de>
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Solrsearch_IndexController extends Controller_Action {
    
    private $query;
    private $numOfHits;
    private $searchtype;
    private $resultList;

    public function  init() {
        parent::init();
        $this->_helper->mainMenu('search');
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
        if ($searchtype === Util_Searchtypes::ADVANCED_SEARCH) {
            $this->view->searchType = Util_Searchtypes::ADVANCED_SEARCH;
        }
        else {
            $this->view->searchType = Util_Searchtypes::SIMPLE_SEARCH;
        }
    }

    /**
     * legacy OPUS 4.0.x action: externalized in Solrsearch_DispatchController.indexAction
     */
    public function searchdispatchAction() {
        $this->_forward('index', 'dispatch');
    }

    public function searchAction() {
        if (!is_null($this->getRequest()->getParam('export'))) {
            $params = $this->getRequest()->getParams();
            // export module ignores pagination parameters
            unset($params['rows']);
            unset($params['start']);
            return $this->_redirectToAndExit('index', null, 'index', 'export', $params);
        }

        $this->query = $this->buildQuery();
        $this->performSearch();
        $this->setViewValues();
        $this->setViewFacets();

        $this->setLinkRelCanonical();

        if($this->numOfHits === 0 || $this->query->getStart() >= $this->numOfHits) {
            $this->render('nohits');
        }
        else {
            $this->render('results');
        }
    }
    
    private function setLinkRelCanonical() {
        $query = $this->getRequest()->getParams();
        $query['rows'] = 10;
        unset($query['sortfield']);
        unset($query['sortorder']);

        $serverUrl = $this->view->serverUrl();
        $fullCanonicalUrl = $serverUrl . $this->view->url( $query, null, true );

        $this->view->headLink(array('rel' => 'canonical', 'href' => $fullCanonicalUrl));
    }

    private function performSearch() {
        $this->_logger->debug('performing search');
        try {
            $searcher = new Opus_SolrSearch_Searcher();
            $this->resultList = $searcher->search($this->query);
        }
        catch (Opus_SolrSearch_Exception $e) {
            $this->_logger->err("Sorry, an internal server error occurred: " . $e);
            throw new Application_Exception('Sorry, an internal server error occurred.');
        }
        $this->numOfHits = $this->resultList->getNumberOfHits();
    }

    private function setViewValues() {
        $this->setGeneralViewValues();        

        if ($this->searchtype === Util_Searchtypes::SIMPLE_SEARCH || $this->searchtype === Util_Searchtypes::ALL_SEARCH) {
            $queryString = $this->query->getCatchAll();
            if (trim($queryString) !== '*:*') {
                $this->view->q = $queryString;
            }
            else {
                $this->view->q = '';
            }
            $this->view->nextPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>(int)($this->query->getStart()) + (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->prevPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>(int)($this->query->getStart()) - (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->lastPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>(int)(($this->numOfHits - 1) / $this->query->getRows()) * $this->query->getRows(),'rows'=>$this->query->getRows()));
            $this->view->firstPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'query'=>$this->query->getCatchAll(),'start'=>'0','rows'=>$this->query->getRows()));
            $this->setFilterQueryBaseURL();
            $browsing = $this->getRequest()->getParam('browsing', 'false');
            if ($browsing === 'true') {
                $this->view->specialTitle = $this->getRequest()->getParam('doctypefq', '');
            }
            return;
        }
        if ($this->searchtype === Util_Searchtypes::ADVANCED_SEARCH || $this->searchtype === Util_Searchtypes::AUTHOR_SEARCH) {
            $this->view->nextPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'start'=>(int)($this->query->getStart()) + (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->prevPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'start'=>(int)($this->query->getStart()) - (int)($this->query->getRows()),'rows'=>$this->query->getRows()));
            $this->view->lastPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'start'=>(int)(($this->numOfHits - 1) / $this->query->getRows()) * $this->query->getRows(),'rows'=>$this->query->getRows()));
            $this->view->firstPage = self::createSearchUrlArray(array('searchtype'=>$this->searchtype,'start'=>'0','rows'=>$this->query->getRows()));
            $this->setFilterQueryBaseURL();
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
        if ($this->searchtype === Util_Searchtypes::COLLECTION_SEARCH) {
            $this->view->nextPage = self::createSearchUrlArray(array('searchtype' => Util_Searchtypes::COLLECTION_SEARCH, 'start'=>(int)($this->query->getStart()) + (int)($this->query->getRows()), 'rows'=>$this->query->getRows()));
            $this->view->prevPage = self::createSearchUrlArray(array('searchtype' => Util_Searchtypes::COLLECTION_SEARCH, 'start'=>(int)($this->query->getStart()) - (int)($this->query->getRows()), 'rows'=>$this->query->getRows()));
            $this->view->lastPage = self::createSearchUrlArray(array('searchtype' => Util_Searchtypes::COLLECTION_SEARCH, 'start'=>(int)(($this->numOfHits - 1) / $this->query->getRows()) * $this->query->getRows(), 'rows'=>$this->query->getRows()));
            $this->view->firstPage = self::createSearchUrlArray(array('searchtype' => Util_Searchtypes::COLLECTION_SEARCH, 'start'=>'0', 'rows'=>$this->query->getRows()));
            $this->setFilterQueryBaseURL();
            return;
        }
        if ($this->searchtype === Util_Searchtypes::LATEST_SEARCH) {
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
        $this->view->authorSearch = self::createSearchUrlArray(array('searchtype' => Util_Searchtypes::AUTHOR_SEARCH));
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
            $this->_logger->debug("found $key facet in search results");

            $facetValue = $this->getRequest()->getParam($key . 'fq','');
            if($facetValue !== '') {
                $selectedFacets[$key] = $facetValue;
            }

            if(count($facets[$key]) > 1 || $facetValue !== '') {
                $facetArray[$key] = $facet;
            }
        }
        
        $this->view->facets = $facetArray;
        $this->view->selectedFacets = $selectedFacets;
    }

    private function buildQuery() {
        $queryBuilder = new Util_QueryBuilder($this->_logger);
        $queryBuilderInput = array();
        try {
            $queryBuilderInput = $queryBuilder->createQueryBuilderInputFromRequest($this->getRequest());
        }
        catch (Util_QueryBuilderException $e) {
            $this->_logger->err(__METHOD__ . ' : ' . $e->getMessage());
            return $this->_redirectToAndExit('index');
        }

        $this->searchtype = $this->getRequest()->getParam('searchtype');
        if ($this->searchtype === Util_Searchtypes::LATEST_SEARCH) {
            return $queryBuilder->createSearchQuery($this->validateInput($queryBuilderInput, 10, 100));
        }
        if ($this->searchtype === Util_Searchtypes::COLLECTION_SEARCH) {
            $queryBuilderInput['collectionId'] = $this->prepareChildren();
        }
        return $queryBuilder->createSearchQuery($this->validateInput($queryBuilderInput));
    }

    private function prepareChildren() {
        $collectionList = null;
        try {
            $collectionList = new Solrsearch_Model_CollectionList($this->getRequest()->getParam('id'));
        }
        catch (Solrsearch_Model_Exception $e) {
            $this->_logger->debug($e->getMessage());
            return $this->_redirectToAndExit('index', '', 'browse', null, array(), true);
        }

        $this->view->children = $collectionList->getChildren();
        $this->view->parents = $collectionList->getParents();
        $translation = $this->view->translate($collectionList->getCollectionRoleTitle());
        if ($translation === $collectionList->getCollectionRoleTitle()) {
            $translation = $collectionList->getCollectionRoleTitlePlain();
        }
        $this->view->collectionRoleTitle = $translation;

        if ($collectionList->isRootCollection()) {
            $this->view->title = $translation;
        }
        else {
            $this->view->title = $collectionList->getTitle();
        }

        // Get the theme assigned to this collection iff usertheme is
        // set in the request.  To enable the collection theme, add
        // /usetheme/1/ to the URL.
        $usetheme = $this->getRequest()->getParam("usetheme");
        if (!is_null($usetheme) && 1 === (int) $usetheme) {
            $layoutPath = APPLICATION_PATH . '/public/layouts/' . $collectionList->getTheme();
            if (is_readable($layoutPath . '/common.phtml')) {
                $this->_helper->layout->setLayoutPath($layoutPath);
            }
            else {
                $this->_logger->debug("The requested theme '" . $collectionList->getTheme() . "' does not exist - use default theme instead.");
            }
        }
        return $collectionList->getCollectionId();
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
     * Sets the base URL that is used to build all remove filter query URLs.
     */
    private function setFilterQueryBaseURL() {
        $this->view->removeFilterQueryBase = $this->getRequest()->getParams();
        unset($this->view->removeFilterQueryBase['start']);
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
     */
    private function validateInput($input, $min = 1, $max = 100) {
        if ($input['rows'] > $max) {
            $this->_logger->warn("Values greater than 100 are currently not allowed for the rows paramter.");
            $input['rows'] = $max;
        }
        if ($input['rows'] < $min) {
            $this->_logger->warn("rows parameter is smaller than 1: adjusting to 1.");
            $input['rows'] = $min;
        }
        if ($input['start'] < 0) {
            $this->_logger->warn("A negative start parameter is ignored.");
            $input['start'] = 0;
        }
        return $input;
    }
}
?>