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
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Main controller for solrsearch module.
 *
 * TODO cleanup (e.g. move functions to models, use forms, etc.)
 */
class Solrsearch_IndexController extends Application_Controller_Action {

    /**
     * Search query.
     * @var
     */
    private $_query;

    /**
     * Total number of search results.
     * @var
     */
    private $_numOfHits;

    /**
     * Type of search.
     *
     * @var
     */
    private $_searchtype;

    /**
     * Search results.
     * @var
     */
    private $_resultList;

    /**
     * Model for handling facets.
     *
     * @var Solrsearch_Model_FacetMenu
     */
    private $_facetMenu;

    /**
     * Initialize controller.
     */
    public function  init() {
        parent::init();

        $this->_facetMenu = new Solrsearch_Model_FacetMenu();

        $this->_helper->mainMenu('search'); // activate entry in main menu
    }

    /**
     * Displays simple search form.
     */
    public function indexAction() {
        $this->view->title = 'solrsearch_title_simple';
    }

    /**
     * Shows advanced search form.
     *
     * TODO make advanced.phtml optional
     */
    public function advancedAction() {
        $form = new Solrsearch_Form_AdvancedSearch();
        $form->setAction($this->view->url(array(
                    'module' => 'solrsearch', 'controller' => 'dispatch', 'action' => 'index'
        )));
        $this->view->form = $form;
        $this->view->title = $this->view->translate('solrsearch_title_advanced');
    }

    public function invalidsearchtermAction() {
        $this->view->title = $this->view->translate('solrsearch_title_invalidsearchterm');
        $searchtype = $this->getRequest()->getParam('searchtype');

        // TODO create form


        if ($searchtype === Application_Util_Searchtypes::ADVANCED_SEARCH) {
            $this->view->searchType = Application_Util_Searchtypes::ADVANCED_SEARCH;
        }
        else {
            $this->view->searchType = Application_Util_Searchtypes::SIMPLE_SEARCH;
        }
    }

    /**
     * legacy OPUS 4.0.x action: externalized in Solrsearch_DispatchController.indexAction
     */
    public function searchdispatchAction() {
        $this->_forward('index', 'dispatch');
    }

    /**
     * Redirects to the Export Module.
     * @param $params Parameters for url
     */
    private function redirectToExport($params) {
        unset($params['start']);
        if ($params['searchtype'] != 'latest') {
            unset($params['rows']);
        }
        else {
            if (!array_key_exists('rows', $params)) {
                $params['rows'] = 10;
            }
        }

        if ($this->getRequest()->getParam('export') === 'rss') {
            unset($params['export']);
            unset($params['sortfield']);
            unset($params['sortorder']);
            return $this->_redirectToAndExit('index', null, 'index', 'rss', $params);
        }

        return $this->_redirectToAndExit('index', null, 'index', 'export', $params);
    }

    public function searchAction() {
        // TODO OPUSVIER-3324 Mischform in der url entfernen
        // check if searchtype = latest and params parsed incorrect
        $searchType = $this->getParam('searchtype');
        $request = $this->getRequest();

        if (in_array($searchType, array('advanced', 'authorsearch')) && !is_null($this->getParam('Reset'))) {
            $this->_redirectTo('advanced', null, 'index', 'solrsearch');
            return;
        }

        if (strpos($searchType, 'latest/export') !== false) {
            $paramArray = explode('/', $searchType);
            $params = $request->getParams();
            $params['searchtype'] = 'latest';
            $params['export'] = $paramArray[2];
            $params['stylesheet'] = $paramArray[4];
            $this->redirectToExport($params);
            return;
        }

        if (!is_null($request->getParam('export'))) {
            $params = $request->getParams();
            // export module ignores pagination parameters
            $this->redirectToExport($params);
            return;
        }

        // TODO does the following make sense after the above?
        $config = $this->getConfig();
        if (isset($config->export->stylesheet->search) && Opus_Security_Realm::getInstance()->checkModule('export')) {
            $this->view->stylesheet = $config->export->stylesheet->search;
        }

        $query = $this->buildQuery();
        // if query is null, redirect has already been set
        if (!is_null($query)) {
            $this->_query = $query;
            $this->performSearch();
            $this->setViewValues();
            $this->_facetMenu->prepareViewFacets($this->_resultList, $this->getRequest());
            $this->view->facets = $this->_facetMenu->getFacets();
            $this->view->selectedFacets = $this->_facetMenu->getSelectedFacets();
            $this->view->facetNumberContainer = $this->_facetMenu->getFacetNumberContainer();
            $this->view->showFacetExtender = $this->_facetMenu->getShowFacetExtender();

            $this->setLinkRelCanonical();

            switch ($searchType) {
                case 'advanced':
                case 'authorsearch':
                    $form = new Solrsearch_Form_AdvancedSearch($searchType);
                    $form->populate($this->getAllParams());
                    $form->setAction($this->view->url(array(
                                'module' => 'solrsearch', 'controller' => 'dispatch', 'action' => 'index'
                                    ), null, true));
                    $this->view->form = $form;
                    break;
                case 'latest':
                    $form = new Solrsearch_Form_Options();
                    $form->setMethod(Zend_FORM::METHOD_GET);
                    $form->setAction($this->view->url(array(
                                'module' => 'solrsearch', 'controller' => 'index', 'action' => 'search'
                                    ), null, true));
                    $form->populate($this->getAllParams());
                    $this->view->form = $form;
                    break;
                default:
                    break;
            }

            if ($this->_numOfHits === 0 || $this->_query->getStart() >= $this->_numOfHits) {
                $this->render('nohits');
            }
            else {
                $this->render('results');
            }
        }
    }

    private function setLinkRelCanonical() {
        $query = $this->getRequest()->getParams();
        $query['rows'] = 10;
        unset($query['sortfield']);
        unset($query['sortorder']);

        $serverUrl = $this->view->serverUrl();
        $fullCanonicalUrl = $serverUrl . $this->view->url($query, null, true);

        $this->view->headLink(array('rel' => 'canonical', 'href' => $fullCanonicalUrl));
    }

    /**
     * @throws Application_Exception
     * @throws Application_SearchException
     *
     * TODO this should happen in model class so it can be tested directly
     */
    private function performSearch() {
        $this->getLogger()->debug('performing search');
        try {
            $searcher = new Opus_SolrSearch_Searcher();
            $openFacets = $this->_facetMenu->buildFacetArray( $this->getRequest()->getParams() );
            $searcher->setFacetArray($openFacets);
            $this->_resultList = $searcher->search($this->_query);
            $this->view->openFacets = $openFacets;
        }
        catch (Opus_SolrSearch_Exception $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e);
            throw new Application_SearchException($e);
        }
        $this->_numOfHits = $this->_resultList->getNumberOfHits();
    }

    private function setViewValues() {
        $this->setGeneralViewValues();

        if ($this->_numOfHits > 0) {
            $nrOfRows = (int)$this->_query->getRows();
            $start = $this->_query->getStart();
            $query = null;
            if ($this->_searchtype === Application_Util_Searchtypes::SIMPLE_SEARCH
                    || $this->_searchtype === Application_Util_Searchtypes::ALL_SEARCH) {
                $query = $this->_query->getCatchAll();
            }
            $this->setUpPagination($nrOfRows, $start, $query);
        }

        switch ($this->_searchtype) {
            case Application_Util_Searchtypes::SIMPLE_SEARCH:
            case Application_Util_Searchtypes::ALL_SEARCH:
                $queryString = $this->_query->getCatchAll();
                if (trim($queryString) !== '*:*') {
                    $this->view->q = $queryString;
                }
                else {
                    $this->view->q = '';
                }
                $this->setFilterQueryBaseURL();
                $browsing = $this->getRequest()->getParam('browsing', 'false');
                if ($browsing === 'true') {
                    $this->view->specialTitle = $this->view->translate($this->getRequest()->getParam('doctypefq', ''));
                    $this->view->doctype = $this->getRequest()->getParam('doctypefq', null);
                }
                break;
            case Application_Util_Searchtypes::ADVANCED_SEARCH:
            case Application_Util_Searchtypes::AUTHOR_SEARCH:
            case Application_Util_Searchtypes::COLLECTION_SEARCH:
            case Application_Util_Searchtypes::SERIES_SEARCH:
                $this->setFilterQueryBaseURL();
                break;
            case Application_Util_Searchtypes::LATEST_SEARCH:
                $this->view->isSimpleList = true;
                $this->view->specialTitle = $this->view->translate('title_latest_docs_article') . ' '
                    . $this->_query->getRows(). ' '.$this->view->translate('title_latest_docs');
                break;
            default:
                break;
        }
    }

    /**
     * Sets up pagination for search results.
     * @param $rows Number of results per page
     * @param $startIndex Starting number for first result on current page
     * @param $query Current query
     */
    private function setUpPagination($rows, $startIndex, $query) {
        $pagination = new Solrsearch_Model_PaginationUtil(
                $rows, $this->_numOfHits, $startIndex, $query, $this->_searchtype
        );
        $this->view->nextPage = self::createSearchUrlArray($pagination->getNextPageUrlArray());
        $this->view->prevPage = self::createSearchUrlArray($pagination->getPreviousPageUrlArray());
        $this->view->lastPage = self::createSearchUrlArray($pagination->getLastPageUrlArray());
        $this->view->firstPage = self::createSearchUrlArray($pagination->getFirstPageUrlArray());
    }

    private function setGeneralViewValues() {
        $this->view->results = $this->_resultList->getResults();
        $this->view->searchType = $this->_searchtype;
        $this->view->numOfHits = $this->_numOfHits;
        $this->view->queryTime = $this->_resultList->getQueryTime();
        $this->view->start = $this->_query->getStart();
        $nrOfRows = $this->_query->getRows();
        if ($nrOfRows != 0) {
            $this->view->numOfPages = (int) ($this->_numOfHits / $nrOfRows) + 1;
        }
        $this->view->rows = $this->_query->getRows();
        $this->view->authorSearch = self::createSearchUrlArray(array('searchtype' => Application_Util_Searchtypes::AUTHOR_SEARCH));
        $this->view->isSimpleList = false;
        $this->view->browsing = (boolean) $this->getRequest()->getParam('browsing', false);
        if ($this->_searchtype == Application_Util_Searchtypes::SERIES_SEARCH) {
            $this->view->sortfield = $this->getRequest()->getParam('sortfield', 'seriesnumber');
        }
        else {
            $this->view->sortfield = $this->getRequest()->getParam('sortfield', 'score');
        }
        $this->view->sortorder = $this->getRequest()->getParam('sortorder', 'desc');
        $this->setRssUrl();
    }

    private function setRssUrl() {
        $this->view->rssUrl = self::createSearchUrlArray(array(), true);
    }

    /**
     * Builds query for Solr search.
     * @return Opus_SolrSearch_Query|void
     * @throws Application_Exception
     */
    private function buildQuery() {
        $request = $this->getRequest();

        $this->_searchtype = $request->getParam('searchtype');

        if ($this->_searchtype === Application_Util_Searchtypes::COLLECTION_SEARCH) {
            $this->prepareChildren();
        }
        else if ($this->_searchtype === Application_Util_Searchtypes::SERIES_SEARCH) {
            if (!$this->prepareSeries()) {
                return null;
            }
        }

        try {
            return Application_Search_Navigation::getQueryUrl($request, $this->getLogger());
        }
        catch (Application_Util_BrowsingParamsException $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $this->_redirectToAndExit('index', '', 'browse', null, array(), true);
            return null;
        }
        catch (Application_Util_QueryBuilderException $e) {
            $this->getLogger()->err(__METHOD__ . ' : ' . $e->getMessage());
            $this->_redirectToAndExit('index');
            return null;
        }
    }

    private function prepareSeries() {
        $series = null;
        try {
            $series = new Solrsearch_Model_Series($this->getRequest()->getParam('id'));
        }
        catch (Solrsearch_Model_Exception $e) {
            $this->getLogger()->debug($e->getMessage());
            $this->_redirectToAndExit('index', '', 'browse', null, array(), true);
            return false;
        }

        $this->view->title = $series->getTitle();
        $this->view->seriesId = $series->getId();
        $this->view->infobox = $series->getInfobox();
        $this->view->logoFilename = $series->getLogoFilename();

        return true;
    }

    private function prepareChildren() {
        $collectionList = null;
        try {
            $collectionList = new Solrsearch_Model_CollectionList($this->getRequest()->getParam('id'));
        }
        catch (Solrsearch_Model_Exception $e) {
            $this->getLogger()->debug($e->getMessage());
            return $this->_redirectToAndExit('index', '', 'browse', null, array(), true);
        }

        $this->view->collectionId = $collectionList->getCollectionId();
        $this->view->collectionRole = $collectionList->getCollectionRole();
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
                $this->getLogger()->debug(
                        "The requested theme '" . $collectionList->getTheme()
                        . "' does not exist - use default theme instead."
                );
            }
        }
    }

    /**
     * Creates an URL to execute a search. The URL will be mapped to:
     * module=solrsearch, controller=index, action=search
     */
    public static function createSearchUrlArray($params = array(), $rss = false) {
        $url = array(
            'module' => $rss ? 'rss' : 'solrsearch',
            'controller' => 'index',
            'action' => $rss ? 'index' : 'search');
        foreach ($params as $key => $value) {
            $url[$key] = $value;
        }
        if ($rss) {
            // some ignores some search related parameters
            $url['rows'] = null;
            $url['start'] = null;
            $url['sortfield'] = null;
            $url['sortorder'] = null;
            $url['browsing'] = null;
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
}
