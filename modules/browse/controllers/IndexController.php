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

class Browse_IndexController extends Controller_Action {

    public function indexAction() {
        $this->view->title = $this->view->translate('search_index_browsing');
        $this->view->baseUrl = $this->getRequest()->getBaseUrl();
        // Generate a list of all CollectionRoles existing in the repository and pass it as an Iterator to the View
        $browsingList = new Browse_Model_BrowsingListFactory("collectionRoles");
        $browsingListProduct = $browsingList->getBrowsingList();
        #print_r($browsingListProduct);
        $this->view->browsinglist = $browsingListProduct;
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
                $browsingList = new Browse_Model_BrowsingListFactory($list, $role);
                $browsingListProduct = $browsingList->getBrowsingList();
                $this->view->browsinglist = new Opus_Search_Iterator_PersonsListIterator($browsingListProduct);
                break;
            case 'authors':
                $this->view->title = $this->view->translate('search_index_authors_browsing');
                $browsingList = new Browse_Model_BrowsingListFactory($list);
                $browsingListProduct = $browsingList->getBrowsingList();
                $this->view->browsinglist = new Opus_Search_Iterator_PersonsListIterator($browsingListProduct);
                break;
            case 'editors':
                $this->view->title = $this->view->translate('search_index_editors_browsing');
                $browsingList = new Browse_Model_BrowsingListFactory($list);
                $browsingListProduct = $browsingList->getBrowsingList();
                $this->view->browsinglist = new Opus_Search_Iterator_PersonsListIterator($browsingListProduct);
                break;
            case 'doctypes':
                $url = $this->view->url(array('module'=>'browse','controller'=>'index','action'=>'doctypeslist'),null,true);
                $this->redirectTo($url);
                break;
            case 'collection':
                $node = $this->_getParam("node");
                if (isset($node) === false)
                    $node = 0;
                $collection = $this->_getParam("collection");
                $this->view->collection = $this->_getParam("collection");
                if (isset($collection) === false)
                    $collection = 0;
                $browsingList = new Browse_Model_BrowsingListFactory($list, null, $collection, $node);
                $browsingListProduct = $browsingList->getBrowsingList();

                $this->view->title = $browsingListProduct->getDisplayName('browsing');
                $this->view->browsinglist = $browsingListProduct;
                $this->view->page = $this->_getParam("page");
                #$this->view->hitlist_paginator = Zend_Paginator::factory(Opus_Search_List_CollectionNode::getDocumentIds($collection, $node));

                $documentsIds = array();
                if ($browsingListProduct instanceof Opus_Collection) {
                    $documentsIds = $browsingListProduct->getDocumentIds();
                }

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
                                $lastname = $d->getPersonAuthor($counter)->getLastName();
                                $firstname = $d->getPersonAuthor($counter)->getFirstName();
                                $name = $d->getPersonAuthor($counter)->getName();
                                $this->view->url_author[$runningIndex][$counter] = $this->view->url(
                                                array(
                                                    'module' => 'solrsearch',
                                                    'controller' => 'index',
                                                    'action' => 'search',
                                                    'author' => '"'.$firstname.' '.$lastname.'"',
                                                    'searchtype'=>'authorsearch'
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

    public function doctypeslistAction() {
        $facetname = 'doctype';
        $query = new Opus_SolrSearch_Query(Opus_SolrSearch_Query::FACET_ONLY);
        $query->setFacetField($facetname);
        $searcher = new Opus_SolrSearch_Searcher();
        $result = $searcher->search($query);
        $facets = $result->getFacets();
        $facetitems = $facets[$facetname];
        $this->view->facetitems = $facetitems;
    }
}
?>