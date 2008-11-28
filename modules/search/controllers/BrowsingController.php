<?php
/**
 * Controller for any browsing operation
 * 
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
 * @category    Browsing
 * @package     Module_Search
 * @author      Oliver Marahrens (o.marahrens@tu-harburg.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
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
		$this->view->browsinglist = new CollectionNodeListIterator($browsingListProduct);
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
    	switch ($filter)
    	{
    		case 'author':
    			$this->view->title = $this->view->translate('search_index_authorsbrowsing');
	    		$authorId = (int) $this->_getParam("author");
    			$author = Opus_Search_Adapter_PersonAdapter::getPerson($authorId);
				$hitlist = BrowsingFilter::getAuthorTitles($author);
				$this->view->author = $author->get();
				break;
    		case 'doctype':
    			$this->view->title = $this->view->translate('search_index_doctypebrowsing');
	    		$authorId = (int) $this->_getParam("doctype");
    			$author = Opus_Search_Adapter_DocumentTypeAdapter::getDocType($authorId);
				$hitlist = BrowsingFilter::getDocumentTypeTitles($author);
				$this->view->doctype = $author->get();
				break;
			default:
				$this->view->title = $this->view->translate('search_index_alltitlesbrowsing');
				// Default Filter is: show all documents from the server
				$hitlist = BrowsingFilter::getAllTitles();
    	}
		$this->view->hitlist = new HitListIterator($hitlist);
    }

	/**
	 * Build the hitlist to browse titles filtered by some criteria
	 * Filter criteria has to be passed to the action by URL-parameter filter
	 * Possible values for list are:
	 * authors			- list of authors in the OPUS database
	 * doctypes 		- list of document types in this repository
	 * institutions		- list of faculties or institutes from the repository owner
	 * schriftenreihe	- list of series stored in this repository
	 * collections		- list of collections stored in this repository
	 * ddc				- list of DDC classes
	 * ccs				- list of CCS classes
	 * jel				- list of JEL classes
	 * msc				- list of MSC classes
	 * pacs				- list of PACS classes
	 * bkl				- list of BKL classes
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
    		case 'authors':
    			$this->view->title = $this->view->translate('search_index_authorsbrowsing');
				$browsingList = new BrowsingListFactory($list);
				$browsingListProduct = $browsingList->getBrowsingList();
				$this->view->browsinglist = new PersonsListIterator($browsingListProduct);
				break;
			case 'doctypes':
				$this->view->title = $this->view->translate('search_index_doctypebrowsing');
				$browsingList = new BrowsingListFactory($list);
				$browsingListProduct = $browsingList->getBrowsingList();
				$this->view->browsinglist = new DocumentTypeListIterator($browsingListProduct);
				break;
			case 'collection':
				$this->view->title = $this->view->translate('search_index_collectionsbrowsing');
				$node = $this->_getParam("node");
				if (isset($node) === false) $node = 0;
				$collection = $this->_getParam("collection");
				if (isset($collection) === false) $collection = 0;
				$browsingList = new BrowsingListFactory($list, $collection, $node);
				$browsingListProduct = $browsingList->getBrowsingList();				
				$this->view->browsinglist = new CollectionNodeListIterator($browsingListProduct->getSubNodes());
				$this->view->documentlist = new CollectionNodeDocumentIterator($browsingListProduct);
				break;
			default:
				$this->view->title = $this->view->translate('search_index_alltitlesbrowsing');
				// Just to be there... List is not supported (Exception is thrown by BrowsingListFactory)
    	}
    }
}
?>
