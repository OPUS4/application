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
class Search_BrowsingController extends Zend_Controller_Action
{
	/**
	 * Just to be there. No actions taken.
	 *
	 * @return void
	 *
	 */
    public function indexAction()
    {
		$this->view->title = $this->view->translate('search_index_browsing');
		// Generate a list of all CollectionRoles existing in the repository and pass it as an Iterator to the View
		$browsingList = new BrowsingListFactory("collectionRoles");
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
    public function browsetitlesAction()
    {
    	$filter = $this->_getParam("filter");
    	$this->view->filter = $filter;
    	$page = 1;
    	$data = $this->_request->getParams();
        if (array_key_exists('page', $data)) {
            // set page if requested
            $page = $data['page'];
        }
    	$this->view->title = $this->view->translate('search_index_alltitles_browsing');
	    // Default Filter is: show all documents from the server
        $result = Opus_Document::getAllDocumentTitlesByState('published');

        // Sort the result if necessary
        // docList contains a list of IDs of the documents, that should be returned after sorting
        $docList = array();
        if (true === array_key_exists('sort_order', $data)) {
      	    switch ($data['sort_order']) {
       		    case 'title':
                    asort($result);
                    foreach ($result as $id => $doc) {
    	                $docList[] = $id;
                    }
                    break;
      		    default:
                    foreach ($result as $id => $doc) {
    	                $docList[] = $id;
                    }
    			    sort($docList);
    	    }
        }
        else {
    	    foreach ($result as $id => $doc) {
    	        $docList[] = $id;
            }
    	    sort($docList);
        }
        $paginator = Zend_Paginator::factory($docList);
        #$hitlistIterator = new Opus_Search_Iterator_HitListIterator($hitlist);
        #$this->view->hitlist_count = $hitlist->count();
        $paginator->setCurrentPageNumber($page);
        $this->view->hitlist_paginator = $paginator;
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
    public function browselistAction()
    {
    	$list = $this->_getParam("list");
    	$this->view->list = $list;
       	switch ($list)
    	{
            case 'persons':
                $role = $this->_getParam("role");
                $this->view->role = $role;
                $translatestring = 'search_index_' . $role . '_browsing';
                $this->view->title = $this->view->translate($translatestring);
                $browsingList = new BrowsingListFactory($list, $role);
                $browsingListProduct = $browsingList->getBrowsingList();
                $this->view->browsinglist = new Opus_Search_Iterator_PersonsListIterator($browsingListProduct);
                break;
    	    case 'authors':
    			$this->view->title = $this->view->translate('search_index_authors_browsing');
				$browsingList = new BrowsingListFactory($list);
				$browsingListProduct = $browsingList->getBrowsingList();
				$this->view->browsinglist = new Opus_Search_Iterator_PersonsListIterator($browsingListProduct);
				break;
            case 'editors':
                $this->view->title = $this->view->translate('search_index_editors_browsing');
                $browsingList = new BrowsingListFactory($list);
                $browsingListProduct = $browsingList->getBrowsingList();
                $this->view->browsinglist = new Opus_Search_Iterator_PersonsListIterator($browsingListProduct);
                break;
			case 'doctypes':
				$this->view->title = $this->view->translate('search_index_doctype_browsing');
				$browsingList = new BrowsingListFactory($list);
				$browsingListProduct = $browsingList->getBrowsingList();
				$this->view->browsinglist = $browsingListProduct;
				break;
			case 'collection':
				$node = $this->_getParam("node");
				if (isset($node) === false) $node = 0;
				$collection = $this->_getParam("collection");
				$this->view->collection = $this->_getParam("collection");
				if (isset($collection) === false) $collection = 0;
				$browsingList = new BrowsingListFactory($list, null, $collection, $node);
				$browsingListProduct = $browsingList->getBrowsingList();

				$this->view->title = $this->view->translate('search_index_custom_browsing_' . $browsingListProduct->getName());
				$this->view->browsinglist = $browsingListProduct;
				$this->view->page = $this->_getParam("page");
				#$this->view->hitlist_paginator = Zend_Paginator::factory(Opus_Search_List_CollectionNode::getDocumentIds($collection, $node));
				break;
			default:
				$this->view->title = $this->view->translate('search_index_alltitles_browsing');
				// Just to be there... List is not supported (Exception is thrown by BrowsingListFactory)
    	}
    }
}