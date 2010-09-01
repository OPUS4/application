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
 * @package     Module_Search
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Controller for any browsing operation
 *
 */
class Search_BrowsingController extends Controller_Action {

    /**
     * Just to be there. No actions taken.
     *
     * @return void
     *
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('search_index_browsing');
        $this->view->baseUrl = $this->getRequest()->getBaseUrl();
        // Generate a list of all CollectionRoles existing in the repository and pass it as an Iterator to the View
        $browsingList = new Search_Model_BrowsingListFactory("collectionRoles");
        $browsingListProduct = $browsingList->getBrowsingList();
        #print_r($browsingListProduct);
        $this->view->browsinglist = $browsingListProduct;
    }

    /**
     * Build the hitlist to browse titles filtered by some criteria
     * Filter criteria has to be passed to the action by URL-parameter filter
     * Possible values for filter are:
     * author 	- the ID of a person in the OPUS database
     * doctype 	- the ID of a doctype in OPUS
     * ... to be continued
     *
     * If no (or an invalid) filter criteria is given, a complete list of all documents is passed to the view
     *
     * @return void
     *
     */
    public function browsetitlesAction() {
        $this->view->title = $this->view->translate('search_index_alltitles_browsing');
        $url_sort_by_id = array(
            'module' => 'search',
            'controller' => 'browsing',
            'action' => 'browseTitles',
            'sort_order' => 'id'
        );
        $url_sort_by_title = array(
            'module' => 'search',
            'controller' => 'browsing',
            'action' => 'browseTitles',
            'sort_order' => 'title'
        );
        $url_sort_by_author = array(
            'module' => 'search',
            'controller' => 'browsing',
            'action' => 'browseTitles',
            'sort_order' => 'author'
        );
        $url_sort_by_date = array(
            'module' => 'search',
            'controller' => 'browsing',
            'action' => 'browseTitles',
            'sort_order' => 'publicationDate'
        );
        $url_sort_by_doctype = array(
            'module' => 'search',
            'controller' => 'browsing',
            'action' => 'browseTitles',
            'sort_order' => 'docType'
        );
        $url_sort_asc = array(
            'sort_reverse' => '0'
        );
        $url_sort_desc = array(
            'sort_reverse' => '1'
        );
        $this->view->url_sort_by_id = $this->view->url($url_sort_by_id, 'default', false);
        $this->view->url_sort_by_title = $this->view->url($url_sort_by_title, 'default', false);
        $this->view->url_sort_by_author = $this->view->url($url_sort_by_author, 'default', false);
        $this->view->url_sort_by_date = $this->view->url($url_sort_by_date, 'default', false);
        $this->view->url_sort_by_doctype = $this->view->url($url_sort_by_doctype, 'default', false);
        $this->view->url_sort_asc = $this->view->url($url_sort_asc, 'default', false);
        $this->view->url_sort_desc = $this->view->url($url_sort_desc, 'default', false);

        $data = $this->_request->getParams();
        $filter = $this->_getParam("filter");
        $this->view->filter = $filter;
        $data = $this->_request->getParams();

        $page = 1;
        if (array_key_exists('page', $data)) {
            // set page if requested
            $page = $data['page'];
        }
        $this->view->title = $this->view->translate('search_index_alltitles_browsing');

        // Default Ordering...
        if (true === array_key_exists('sort_reverse', $data)) {
            $sort_reverse = $data['sort_reverse'];
        } else {
            $sort_reverse = '0';
        }
        $this->view->sort_reverse = $sort_reverse;

        if (true === array_key_exists('state', $data)) {
            $this->view->state = $data['state'];
        }
        // following could be handled inside a application model
        if (true === array_key_exists('sort_order', $data)) {
            $this->view->sort_order = $data['sort_order'];
            switch ($data['sort_order']) {
                case 'author':
                    if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentsByAuthorsByState($data['state'], $sort_reverse);
                    } else {
                        $result = Opus_Document::getAllDocumentsByAuthors($sort_reverse);
                    }
                    break;
                case 'publicationDate':
                    if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentsByPubDateByState($data['state'], $sort_reverse);
                    } else {
                        $result = Opus_Document::getAllDocumentsByPubDate($sort_reverse);
                    }
                    break;
                case 'docType':
                    if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentsByDoctypeByState($data['state'], $sort_reverse);
                    } else {
                        $result = Opus_Document::getAllDocumentsByDoctype($sort_reverse);
                    }
                    break;
                case 'title':
                    if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentsByTitlesByState($data['state'], $sort_reverse);
                    } else {
                        $result = Opus_Document::getAllDocumentsByTitles($sort_reverse);
                    }
                    break;
                default:
                    if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllIdsByState($data['state'], $sort_reverse);
                    } else {
                        $result = Opus_Document::getAllIds($sort_reverse);
                    }
            }
        } else {
            if (true === array_key_exists('state', $data)) {
                $result = Opus_Document::getAllIdsByState($data['state'], $sort_reverse);
            } else {
                $result = Opus_Document::getAllIds($sort_reverse);
            }
        }

        $paginator = Zend_Paginator::factory($result);
        if (array_key_exists('hitsPerPage', $data)) {
            if ($data['hitsPerPage'] === '0') {
                $hitsPerPage = '10000';
            } else {
                $hitsPerPage = $data['hitsPerPage'];
            }
            $paginator->setItemCountPerPage($hitsPerPage);
        }
        if (array_key_exists('page', $data)) {
            // paginator
            $page = $data['page'];
        } else {
            $page = 1;
        }
        $paginator->setCurrentPageNumber($page);
        $this->view->paginator = $paginator;

        // iterate the paginator and get the attributes we want to show in the view
        $runningIndex = 0;
        $this->view->docId = array();
        $this->view->doctitle = array();
        $this->view->author = array();
        $this->view->url_frontdoor = array();
        $this->view->url_author = array();
        foreach ($paginator as $id) {
            $url_frontdoor = array(
                'module' => 'frontdoor',
                'controller' => 'index',
                'action' => 'index',
                'docId' => $id
            );
            $this->view->url_frontdoor[$runningIndex] = $this->view->url($url_frontdoor, 'default', true);

            try {
                $d = new Opus_Document((int) $id);
                $this->view->docId[$runningIndex] = $id;
                $this->view->docState = $d->getServerState();
                $c = count($d->getPersonAuthor());
                $this->view->doctitle[$runningIndex] = $d->getTitleMain(0)->getValue();
            } catch (Exception $e) {
                $this->view->docState = 'undefined';
                $c = 0;
                $this->view->doctitle[$runningIndex] = $this->view->translate('document_no_title') . $id;
            }
            $this->view->author[$runningIndex] = array();
            $this->view->url_author[$runningIndex] = array();
            for ($counter = 0; $counter < $c; $counter++) {
                $name = $d->getPersonAuthor($counter)->getName();
                $this->view->url_author[$runningIndex][$counter] = $this->view->url(
                                array(
                                    'module' => 'search',
                                    'controller' => 'search',
                                    'action' => 'metadatasearch',
                                    'author' => $name
                                ),
                                null,
                                true
                );
                $this->view->author[$runningIndex][$counter] = $name;
            }
            $runningIndex++;
        }
    }

    /**
     * Build the hitlist to browse titles filtered by some criteria
     * Filter criteria has to be passed to the action by URL-parameter filter
     * Possible values for list are:
     * authors			- list of authors in the OPUS database
     * editors          - list of editors in the OPUS database
     * doctypes 		- list of document types in this repository
     * collection		- list of collections stored in this repository
     *
     * If no (or an invalid) list is given, there will be an Exception which tells that this list is not supported
     *
     * @return void
     *
     */
    public function browselistAction() {
        $list = $this->_getParam("list");
        $this->view->list = $list;
        switch ($list) {
            case 'persons':
                $role = $this->_getParam("role");
                $this->view->role = $role;
                $translatestring = 'search_index_' . $role . '_browsing';
                $this->view->title = $this->view->translate($translatestring);
                $browsingList = new Search_Model_BrowsingListFactory($list, $role);
                $browsingListProduct = $browsingList->getBrowsingList();
                $this->view->browsinglist = new Opus_Search_Iterator_PersonsListIterator($browsingListProduct);
                break;
            case 'authors':
                $this->view->title = $this->view->translate('search_index_authors_browsing');
                $browsingList = new Search_Model_BrowsingListFactory($list);
                $browsingListProduct = $browsingList->getBrowsingList();
                $this->view->browsinglist = new Opus_Search_Iterator_PersonsListIterator($browsingListProduct);
                break;
            case 'editors':
                $this->view->title = $this->view->translate('search_index_editors_browsing');
                $browsingList = new Search_Model_BrowsingListFactory($list);
                $browsingListProduct = $browsingList->getBrowsingList();
                $this->view->browsinglist = new Opus_Search_Iterator_PersonsListIterator($browsingListProduct);
                break;
            case 'doctypes':
                $this->view->title = $this->view->translate('search_index_doctype_browsing');
                $browsingList = new Search_Model_BrowsingListFactory($list);
                $browsingListProduct = $browsingList->getBrowsingList();
                $this->view->browsinglist = $browsingListProduct;
                break;
            case 'collection':
                $node = $this->_getParam("node");
                if (isset($node) === false)
                    $node = 0;
                $collection = $this->_getParam("collection");
                $this->view->collection = $this->_getParam("collection");
                if (isset($collection) === false)
                    $collection = 0;
                $browsingList = new Search_Model_BrowsingListFactory($list, null, $collection, $node);
                $browsingListProduct = $browsingList->getBrowsingList();

                $this->view->title = $browsingListProduct->getDisplayName('browsing');
                $this->view->browsinglist = $browsingListProduct;
                $this->view->page = $this->_getParam("page");
                #$this->view->hitlist_paginator = Zend_Paginator::factory(Opus_Search_List_CollectionNode::getDocumentIds($collection, $node));

                $documentsIds = $browsingListProduct->getDocumentIds();
                $documents_paginator = Zend_Paginator::factory($documentsIds);
                if ($this->view->page > 0) {
                    $documents_paginator->setCurrentPageNumber($this->view->page);
                }
                $this->view->documents_paginator = $documents_paginator;

                $numberOfDocuments = count($documentsIds);
                $this->view->numberOfDocuments = $numberOfDocuments;

                if ($numberOfDocuments > 0) {
                    $searchListItemCount = 0;
                    // iterate the paginator and get the attributes we want to show in the view
                    $runningIndex = 0;
                    $this->view->docId = array();
                    $this->view->doctitle = array();
                    $this->view->author = array();
                    $this->view->url_frontdoor = array();
                    $this->view->url_author = array();
                    foreach ($documents_paginator as $id) {
                        $d = new Opus_Document($id);
                        $url_frontdoor = array(
                            'module' => 'frontdoor',
                            'controller' => 'index',
                            'action' => 'index',
                            'docId' => $id
                        );
                        $this->view->url_frontdoor[$runningIndex] = $this->view->url($url_frontdoor, 'default', true);

                        $this->view->docId[$runningIndex] = $id;
                        try {
                            $this->view->docState = $d->getServerState();
                        } catch (Exception $e) {
                            $this->view->docState = 'undefined';
                        }

                        try {
                            $c = count($d->getPersonAuthor());
                            $this->view->author[$runningIndex] = array();
                            $this->view->url_author[$runningIndex] = array();
                            for ($counter = 0; $counter < $c; $counter++) {
                                $name = $d->getPersonAuthor($counter)->getName();
                                $this->view->url_author[$runningIndex][$counter] = $this->view->url(
                                                array(
                                                    'module' => 'search',
                                                    'controller' => 'search',
                                                    'action' => 'metadatasearch',
                                                    'author' => $name
                                                ),
                                                null,
                                                true
                                );
                                $this->view->author[$runningIndex][$counter] = $name;
                            }
                        } catch (Exception $e) {
                            //no author
                            $this->view->author[$runningIndex] = null;
                        }
                        try {
                            $this->view->doctitle[$runningIndex] = $d->getTitleMain(0)->getValue();
                        } catch (Exception $e) {
                            $this->view->doctitle[$runningIndex] = $this->view->translate('document_no_title') . $id;
                        }
                        $runningIndex++;
                    }
                }

                // Get the theme assigned to this collection iff usertheme is
                // set in the request.  To enable the collection theme, add
                // /usetheme/1/ to the browsing URL.
                $usetheme = $this->_getParam("usetheme");
                if (isset($usetheme) === true && 1 === (int) $usetheme) {
                    // $this->_helper->layout->setLayout('../' . $browsingListProduct->getTheme() . '/common');
                    $this->_helper->layout->setLayoutPath(APPLICATION_PATH . '/public/layouts/' . $browsingListProduct->getTheme());
                }

                // If node === 0, then this collection actually is a CollectionRole
                // and we want to translate the title (if specified).
                $translatelabel = 'search_index_custom_browsing_' . $this->view->title;
                if ($node === 0 && !($translatelabel === $this->view->translate($translatelabel))) {
                    $this->view->title = $this->view->translate($translatelabel);
                }

                break;
            default:
                $this->view->title = $this->view->translate('search_index_alltitles_browsing');
            // Just to be there... List is not supported (Exception is thrown by BrowsingListFactory)
        }
    }

    /**
     * get the latest publications
     */
    public function latestAction() {
        $hitlist = Search_Model_BrowsingList::getLatestDocuments();
        $this->view->title = $this->view->translate('latest_documents_title');
        $data = $this->_request->getParams();
        if (array_key_exists('output', $data) === true && $data['output'] === "rss") {
            $template = new Search_Model_RSSOutput();
            // We need an OPUS-compliant result list to return
            $hitlistList = new Opus_Search_List_HitList();
            foreach ($hitlist as $queryHit) {
                $opusHit = new Opus_Search_SearchHit();
                $array = array('id' => $queryHit);
                $opusdoc = new Opus_Search_Adapter_DocumentAdapter($array);
                $opusHit->setDocument($opusdoc);
                $hitlistList->add($opusHit);
            }
            $hitlistIterator = new Opus_Search_Iterator_HitListIterator($hitlistList);
            // Put the hitlist into a Pagionator
            $hitlist_paginator = Zend_Paginator::factory($hitlistIterator);
            if (array_key_exists('hitsPerPage', $data)) {
                if ($data['hitsPerPage'] === '0') {
                    $hitsPerPage = '10000';
                } else {
                    $hitsPerPage = $data['hitsPerPage'];
                }
                $hitlist_paginator->setItemCountPerPage($hitsPerPage);
            }
            if (array_key_exists('page', $data)) {
                // paginator
                $page = $data['page'];
            } else {
                $page = 1;
            }
            $hitlist_paginator->setCurrentPageNumber($page);

            $result = $template->getTemplate($hitlist_paginator, 'RSS Feed Latest Documents');
            $xml = $result['xmlobject'];
            $this->_helper->viewRenderer->setNoRender(true);
            $this->_helper->layout()->disableLayout();
            $this->getResponse()->setHeader('Content-Type', 'text/xml; charset=UTF-8', true);
            $this->getResponse()->setBody($xml->saveXml());
        }
        if (count($hitlist) > 0) {
            $paginator = Zend_Paginator::factory($hitlist);
            if (array_key_exists('hitsPerPage', $data)) {
                if ($data['hitsPerPage'] === '0') {
                    $hitsPerPage = '10000';
                } else {
                    $hitsPerPage = $data['hitsPerPage'];
                }
                $paginator->setItemCountPerPage($hitsPerPage);
            }
            if (array_key_exists('page', $data)) {
                // paginator
                $page = $data['page'];
            } else {
                $page = 1;
            }
            $paginator->setCurrentPageNumber($page);
            $this->view->paginator = $paginator;

            // iterate the paginator and get the attributes we want to show in the view
            $runningIndex = 0;
            $this->view->docId = array();
            $this->view->doctitle = array();
            $this->view->abstractValue = array();
            $this->view->author = array();
            $this->view->url_frontdoor = array();
            $this->view->url_author = array();
            foreach ($paginator as $id) {
                $url_frontdoor = array(
                    'module' => 'frontdoor',
                    'controller' => 'index',
                    'action' => 'index',
                    'docId' => $id
                );
                $this->view->url_frontdoor[$runningIndex] = $this->view->url($url_frontdoor, 'default', true);
                try {
                    $d = new Opus_Document((int) $id);
                    $this->view->docId[$runningIndex] = $id;
                    $this->view->docState = $d->getServerState();
                    $this->view->doctitle[$runningIndex] = $d->getTitleMain(0)->getValue();
                    $this->view->abstractValue[$runningIndex] = $d->getTitleAbstract(0)->getValue();
                    $c = count($d->getPersonAuthor());
                } catch (Exception $e) {
                    $this->view->docState = 'undefined';
                    $this->view->author[$runningIndex] = null;
                    $c = 0;
                    $this->view->doctitle[$runningIndex] = $this->view->translate('document_no_title') . $id;
                }
                $this->view->author[$runningIndex] = array();
                $this->view->url_author[$runningIndex] = array();
                for ($counter = 0; $counter < $c; $counter++) {
                    $name = $d->getPersonAuthor($counter)->getName();
                    $this->view->url_author[$runningIndex][$counter] = $this->view->url(
                                    array(
                                        'module' => 'search',
                                        'controller' => 'search',
                                        'action' => 'metadatasearch',
                                        'author' => $name
                                    ),
                                    null,
                                    true
                    );
                    $this->view->author[$runningIndex][$counter] = $name;
                }
                $runningIndex++;
            }
        }
    }

}
