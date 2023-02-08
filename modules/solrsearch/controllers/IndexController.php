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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Security\Realm;

/**
 * Main controller for solrsearch module.
 *
 * TODO cleanup (e.g. move functions to models, use forms, etc.)
 * TODO separate search types into classes
 * TODO searchType for Solr is different from searchType for OPUS (e.g. series => simple)
 */
class Solrsearch_IndexController extends Application_Controller_Action
{
    /**
     * Initialize controller.
     */
    public function init()
    {
        parent::init();

        $this->_helper->mainMenu('search'); // activate entry in main menu
        $this->view->robots = 'noindex, nofollow';
    }

    /**
     * Displays simple search form.
     */
    public function indexAction()
    {
        $this->view->title = 'solrsearch_title_simple';
    }

    /**
     * Shows advanced search form.
     *
     * TODO make advanced.phtml optional
     */
    public function advancedAction()
    {
        $form = new Solrsearch_Form_AdvancedSearch();
        $form->setAction($this->view->url([
            'module'     => 'solrsearch',
            'controller' => 'dispatch',
            'action'     => 'index',
        ]));
        $this->view->form  = $form;
        $this->view->title = $this->view->translate('solrsearch_title_advanced');
    }

    /**
     * TODO get rid of this action
     */
    public function invalidsearchtermAction()
    {
        $this->view->title = $this->view->translate('solrsearch_title_invalidsearchterm');
        $searchtype        = $this->getRequest()->getParam('searchtype');

        // TODO create form

        if ($searchtype === Application_Util_Searchtypes::ADVANCED_SEARCH) {
            $this->view->searchType = Application_Util_Searchtypes::ADVANCED_SEARCH;
        } else {
            $this->view->searchType = Application_Util_Searchtypes::SIMPLE_SEARCH;
        }
    }

    /**
     * legacy OPUS 4.0.x action: externalized in Solrsearch_DispatchController.indexAction
     *
     * TODO remove?
     */
    public function searchdispatchAction()
    {
        $this->_forward('index', 'dispatch');
    }

    /**
     * Redirects to the Export Module.
     *
     * @param array $params Parameters for url
     *
     * TODO remove this - go to export directly
     */
    private function redirectToExport($params)
    {
        unset($params['start']);
        if ($params['searchtype'] !== 'latest') {
            unset($params['rows']);
        } else {
            if (! array_key_exists('rows', $params)) {
                $params['rows'] = 10;
            }
        }

        if ($this->getRequest()->getParam('export') === 'rss') {
            unset($params['export']);
            unset($params['sortfield']);
            unset($params['sortorder']);
            $this->_helper->Redirector->redirectToAndExit('index', null, 'index', 'rss', $params);
            return;
        }

        $this->_helper->Redirector->redirectToAndExit('index', null, 'index', 'export', $params);
    }

    /**
     * @throws Application_SearchException
     * @throws Zend_Form_Exception
     */
    public function searchAction()
    {
        // check if searchtype = latest and params parsed incorrect
        $searchType = $this->getParam('searchtype');
        $request    = $this->getRequest();

        if (in_array($searchType, ['advanced', 'authorsearch']) && $this->getParam('Reset') !== null) {
            // redirect to new advanced search form
            // TODO find better way
            $this->_helper->Redirector->redirectTo('advanced', null, 'index', 'solrsearch');
            return;
        }

        // TODO remove this export redirect
        if (strpos($searchType, 'latest/export') !== false) {
            $paramArray           = explode('/', $searchType);
            $params               = $request->getParams();
            $params['searchtype'] = 'latest';
            $params['export']     = $paramArray[2];
            $params['stylesheet'] = $paramArray[4];
            $this->redirectToExport($params);
            return;
        }

        if ($request->getParam('export') !== null) {
            $params = $request->getParams();
            // export module ignores pagination parameters
            $this->redirectToExport($params);
            return;
        }

        // TODO does the following make sense after the above?
        // TODO move code somewhere else (encapsulate)
        $config = $this->getConfig();
        if (isset($config->export->stylesheet->search) && Realm::getInstance()->checkModule('export')) {
            $this->view->stylesheet = $config->export->stylesheet->search;
        }

        $search = new Solrsearch_Model_Search();

        $searchPlugin = $search->getSearchPlugin($searchType);
        $searchPlugin->setView($this->view);

        $query = $searchPlugin->buildQuery($request);

        // if query is null, redirect has already been set
        if ($query !== null) {
            /*
             * TODO refactor to make facets independent of each other (no openFacets with list of facets,
             *      just a list of facets that know if they are open or not)
             * TODO what are open/close facets? document!
             * TODO replace FacetMenu with FacetManager? NO - facetMenu is request dependent (FacetManager is not)
             */
            $facetMenu = new Solrsearch_Model_FacetMenu();

            $openFacets = $facetMenu->buildFacetArray($request->getParams());

            $resultList = $searchPlugin->performSearch($query, $openFacets);

            $this->view->openFacets = $openFacets;

            // TODO What happens here?
            $searchPlugin->setViewValues($request, $query, $resultList, $searchType);

            $this->view->facets = $facetMenu->getFacets($resultList, $request);

            // TODO What happens here?
            $this->setLinkRelCanonical();

            $this->view->form = $searchPlugin->createForm($request);

            $numOfHits = $resultList->getNumberOfHits();

            $this->view->resultScript = $this->_helper->resultScript();

            // TODO not sure I like having a separate nohits page (leads to redundant code)
            if ($numOfHits === 0 || $query->getStart() >= $numOfHits) {
                $this->render('nohits');
            } else {
                $this->render('results');
            }
        }
    }

    private function setLinkRelCanonical()
    {
        $query         = $this->getRequest()->getParams();
        $query['rows'] = 10;
        unset($query['sortfield']);
        unset($query['sortorder']);

        $serverUrl        = $this->view->serverUrl();
        $fullCanonicalUrl = $serverUrl . $this->view->url($query, null, true);

        $this->view->headLink(['rel' => 'canonical', 'href' => $fullCanonicalUrl]);
    }
}
