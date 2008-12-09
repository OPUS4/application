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
 * Controller for search operations
 * 
 */
class Search_SearchController extends Zend_Controller_Action
{

    /**
	 * Define Search form
	 *
	 * @return Zend_Form
	 *
	 */
    private function getSearchForm()
    {
        $form = new Zend_Form();
		$form->setAction($this->view->url(array("controller"=>"search", "action"=>"search")))
     		->setMethod('post');

		// Create and configure username element:
		$query = $form->createElement('text', 'query');
		$query->addValidator('alnum')
        		->addValidator('regex', false, array('/^[a-z]+/'))
         		->addValidator('stringLength', false, array(1, 100))
         		->setRequired(true);

		// Add elements to form:
		$form->addElement($query)
	     // use addElement() as a factory to create 'Login' button:
    		 ->addElement('submit', 'search', array('label' => $this->view->translate('search_searchaction')));
    	return $form;
    }

    /**
	 * Show Search form
	 *
	 * @return void
	 *
	 */
    public function indexAction()
    {
    	$this->view->title = $this->view->translate('search_modulename');
        /* get search form from Zend_Form-Object directly
    	// Add form from Object
    	//$this->view->form = $searchForm;
    	// Add form by method call
    	$this->view->form = $this->getSearchForm();
    	*/
    }

     /**
	 * Show Search form
	 *
	 * @return void
	 *
	 */
    public function fulltextsearchAction()
    {
    	$this->view->title = $this->view->translate('search_index_fulltextsearch');
        /* get search form from Zend_Form-Object directly
         * does not work properly (problems with strings that should be translated)
        $searchForm = new FulltextSearch();
        $searchForm->setAction($this->view->url(array("controller"=>"search", "action"=>"search")));
        $searchForm->setMethod('post');
        /*
        // form posted
        /*
        if ($this->_request->isPost() === true) {
	        $data = $this->_request->getPost();
	        if ($uploadForm->isValid($data) === true) {
	            if ($uploadForm->file->receive() === true) {
	                $this->view->message = 'File transfer successfull!';
	                // TODO store / move data to correct place
	            } else {
                    $this->view->message = 'Error file transfer!';
	            }
	        } else {
	            $this->view->message = 'not a valid form!';
	            $uploadForm->populate($data);
	            $this->view->form = $uploadForm;
	        }
	    } else {
            $this->view->form = $uploadForm;
	    }*/
    	// Add form from Object
    	//$this->view->form = $searchForm;
    	// Add form by method call
    	$this->view->form = $this->getSearchForm();
    }

    /**
	 * Do the search operation and set the hitlist to the view
	 *
	 * @return void
	 *
	 */
    public function searchAction()
    {
    	try 
    	{
    		$data = $this->_request->getPost();
    		$query = new Opus_Search_Query($data["query"]);
    		$hitlist = $query->commit();
    	}
    	catch (Zend_Exception $ze)
    	{
    		echo "No index found! Using testdata!";
    		$hitlist = BrowsingFilter::getAllDummyTitles();
    	}
    	$this->view->title = $this->view->translate('search_searchresult');
    	$this->view->hitlist = new HitListIterator($hitlist);
    }
}
?>
