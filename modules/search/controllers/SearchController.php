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
     * Show menu for search actions
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->title = $this->view->translate('search_modulename');
    }

    /**
     * Show fulltext search form
     *
     * @return void
     */
    public function fulltextsearchformAction()
    {
        $this->view->title = $this->view->translate('search_index_fulltextsearch');

        $searchForm = new FulltextSearch();
        $searchForm->setAction($this->view->url(array("controller"=>"search", "action"=>"search")));
        $searchForm->setMethod('post');
        $searchForm->setAttrib('accept-charset', 'utf-8');

        $this->view->form = $searchForm;
        $this->render('form');
    }

    /**
     * Show metadata search form
     *
     * @return void
     */
    public function metadatasearchformAction()
    {
        $this->view->title = $this->view->translate('search_index_metadatasearch');

        $this->view->form = $this->buildMetadataForm();
        $this->render('form');
    }

    /**
     * Do the search operation and set the hitlist to the view
     *
     * @return void
     */
    public function searchAction()
    {
   		$this->view->languageSelectorDisabled = true;
        $failure = false;
   		$config = Zend_Registry::get('Zend_Config');

		$searchEngine = $config->searchengine->engine;
		if (empty($searchEngine) === true) {
			$searchEngine = 'Lucene';
		}

        $this->view->title = $this->view->translate('search_searchresult');
        $page = 1;
        $resultlist = new Zend_Session_Namespace('resultlist');
        if ($this->_request->isPost() === true) {
            // post request
            $data = $this->_request->getPost();
            $form = new FulltextSearch();
        $form->setAction($this->view->url(array("controller"=>"search", "action"=>"search")));
        $form->setMethod('post');
        $form->setAttrib('accept-charset', 'utf-8');
            if ($form->isValid($data) === true) {
                // valid form
                $this->view->form = $form->populate($data);
                try {
                    $queryObject = new Opus_Search_Query($form->getValue('query'), 'ignore', $searchEngine);
                    $hitlist = $queryObject->commit();
                    $resultlist->query = $queryObject->parsedQuery;
                    $resultlist->hitlist = $hitlist;
                    $resultlist->postedData = $data;
                }
                catch (Exception $e) {
                	$failure = $e->getMessage();
                }
            } else {
                // invalid form
                $this->view->form = $form->populate($data);
                return $this->render('form');
            }
        } else {
            // nonpost request
            $form = new FulltextSearch();
        $form->setAction($this->view->url(array("controller"=>"search", "action"=>"search")));
        $form->setMethod('post');
        $form->setAttrib('accept-charset', 'utf-8');
            $data = $this->_request->getParams();
            if (isset($resultlist->postedData) === true) {
            	$this->view->form = $form->populate($resultlist->postedData);
            	$data['hitsPerPage'] = $resultlist->postedData['hitsPerPage'];
            	$data['sort'] = $resultlist->postedData['sort'];
            }
            if (array_key_exists('page', $data)) {
                // paginator
                $page = $data['page'];
                $hitlist = $resultlist->hitlist;
            } else {
                return $this->_forward('fulltextsearch');
            }
        }
        if ($failure === false) {
		    if (array_key_exists('sort', $data)) {
			    $hitlist->sort($data['sort']);
		    }
            $hitlistIterator = new Opus_Search_Iterator_HitListIterator($hitlist);
            $this->view->hitlist_count = count($hitlist);
            if (is_null($hitlist) === false) {
                $paginator = Zend_Paginator::factory($hitlistIterator);
                if (array_key_exists('hitsPerPage', $data)) {
        	        if ($data['hitsPerPage'] === '0') {
        	            $hitsPerPage = '10000';
        	        }
                    else {
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
                $this->view->relevance = array();
                $this->view->author = array();
                $this->view->url_frontdoor = array();
                $this->view->url_author = array();
                $d = null;
                foreach ($paginator as $hit) {
                	$h = $hit->getSearchHit();
                	$doc = $h->getDocument();
                	$id = $doc['id'];
                    $url_frontdoor = array(
                        'module' => 'frontdoor',
                        'controller' => 'index',
                        'action' => 'index',
                        'docId' => $id
                    );
                    $this->view->url_frontdoor[$runningIndex] = $this->view->url($url_frontdoor, 'default', true);

                    try {
                        $d = new Opus_Document( (int) $id);
                        $this->view->docId[$runningIndex] = $id;
                        $this->view->docState = $d->getServerState();
                        $c = count($d->getPersonAuthor());
                        $this->view->doctitle[$runningIndex] = $d->getTitleMain(0)->getValue();
                    }
                    catch (Exception $e) {
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
                                'module'        => 'search',
                                'controller'    => 'search',
                                'action'        => 'metadatasearch',
                                'author'        => $name
                            ),
                            null,
                            true
                        );
                        $this->view->author[$runningIndex][$counter] = $name;
                    }
                    try {
                    	if (array_key_exists('noform', $data) === false) {
                    		$this->view->relevance[$runningIndex] = $hit->getRelevance();
                    		$this->view->abstractValue[$runningIndex] = Opus_Search_Adapter_Lucene_SearchHitAdapter::highlight($resultlist->query, $d->getTitleAbstract(0)->getValue());
                    	}
                    	else {
                    		if ($d !== null) {
                    		    $this->view->abstractValue[$runningIndex] = $d->getTitleAbstract(0)->getValue();
                    		}
                    	}
                    }
                    catch (Exception $e) {
                    	// dont do anything, the worst is that there is no abstract to display
                    }
                    $runningIndex++;
                }
            }
            $this->render('search');


/*		    if (array_key_exists('sort', $data)) {
			    $hitlist->sort($data['sort']);
		    }
            $hitlistIterator = new Opus_Search_Iterator_HitListIterator($hitlist);
            $this->view->hitlist_count = $hitlist->count();
            $paginator = Zend_Paginator::factory($hitlistIterator);
            if (array_key_exists('hitsPerPage', $data)) {
        	    if ($data['hitsPerPage'] === '0') {
        	        $hitsPerPage = '10000';
        	    }
                else {
            	    $hitsPerPage = $data['hitsPerPage'];
                }
                $paginator->setItemCountPerPage($hitsPerPage);
            }
            $paginator->setCurrentPageNumber($page);
            $this->view->hitlist_paginator = $paginator;
            */
        }
        else {
        	$this->view->failure = $failure;
            $this->render('search');
        }

    }

    /**
     * Build metadata search form
     */
    public function buildMetadataForm() {
    	$searchfields = new Zend_Session_Namespace('fields');
    	if (isset($searchfields->fields) === false) {
    		$searchfields->fields = 1;
    	}
        $form = new Zend_Form();
        // decorate form
        #$form->clearDecorators();
        $decorators = array(
            array('ViewHelper'),
            array('Errors'),
            array('Label', array(
                'requiredSuffix' => ' *',
                'class' => 'leftalign'
            )),
            array('HtmlTag', array('tag' => 'p')),
        );
        $fieldDecorators = array(
            array('ViewHelper'),
            array('Errors'),
            array('Label', array(
                'requiredSuffix' => ' *',
                'class' => 'leftalign'
            )),
            array('HtmlTag', array(
                'tag' => 'div',
                'class' => 'fieldsearch'
            )),
        );
        $searchtermDecorators = array(
            array('ViewHelper'),
            array('Errors'),
            array('Label', array(
                'requiredSuffix' => ' *',
                'class' => 'leftalign'
            )),
            array('HtmlTag', array(
                'tag' => 'div',
                'class' => 'queryterm'
            )),
        );

        // Create and configure query field elements:
        $truncation = new Zend_Form_Element_Select('searchtype');
        $truncation->addMultiOptions(array('exact' => 'exact_search', 'truncated' => 'truncated_search'));

        $hitsPerPage = new Zend_Form_Element_Select('hitsPerPage');
        $hitsPerPage->addMultiOptions(array('0' => 'all_hits', '10' => 10, '20' => 20, '25' => 25, '50' => 50));
        $hitsPerPage->setValue('10');
        $hitsPerPage->setLabel('search_hitsPerPage');
        $hitsPerPage->setDecorators($decorators);

        $sort = new Zend_Form_Element_Select('sort');
        $sort->addMultiOptions(array('relevance' => 'search_sort_relevance', 'yat' => 'search_sort_yearandtitle', 'year' => 'search_sort_year', 'title' => 'search_sort_title', 'author' => 'search_sort_author', 'relevance_asc' => 'search_sort_relevance_asc', 'yat_desc' => 'search_sort_yearandtitle_desc', 'year_desc' => 'search_sort_year_desc', 'title_desc' => 'search_sort_title_desc', 'author_desc' => 'search_sort_author_desc'));
        $sort->setLabel('search_sort');
        $sort->setDecorators($decorators);

        $languageList = new Zend_Form_Element_Select('language');
        $langs = Zend_Registry::get('Available_Languages');
        $languageList->setLabel('Language')
            ->setMultiOptions(array('0' => 'all_hits'));
        $languageList->addMultiOptions($langs);
        $languageList->setDecorators($decorators);

        $doctypeList = new Zend_Form_Element_Select('doctype');
        $doctypes = BrowsingList::getDocumentTypeList();
        $doctypeList->setLabel('searchfield_doctype')
            ->setMultiOptions(array('0' => 'all_hits'));
        $doctypeList->addMultiOptions($doctypes);
        $doctypeList->setDecorators($decorators);

        $workflowSelector = new Zend_Form_Element_Select('workflow');
        $workflowSelector->addMultiOptions(array('0' => 'all_hits', 'bibliography' => 'search_bibliographic_only', 'repository' => 'search_repository_only'));
        $workflowSelector->setLabel('search_workflow');
        $workflowSelector->setDecorators($decorators);

        $query = array();
        $field = array();
        $boolean = array();
        for ($n = 0; $n < $searchfields->fields; $n++)
        {
            $field[$n] = new Zend_Form_Element_Select('field[' . $n . ']');
            $field[$n]->addMultiOptions($this->retrieveSearchFields());
            $field[$n]->setDecorators($fieldDecorators);

            $query[$n] = new Zend_Form_Element_Text('query[' . $n . ']');
            $query[$n]->addValidator('stringLength', false, array(3, 100));
            $query[$n]->setDecorators($searchtermDecorators);

            if ($n < ($searchfields->fields-1))
            {
                $boolean[$n] = new Zend_Form_Element_Select('boolean[' . $n . ']');
                $boolean[$n]->addMultiOptions(array('and' => 'boolean_and', 'or' => 'boolean_or', 'not' => 'boolean_not'));
            }
        }
        $addElement = new Zend_Form_Element_Button('add');
        $addElement->setLabel('add_searchfield');
        $addElement->setAttrib('name', 'Action');
        $addElement->setAttrib('onClick', 'javascript:location.href=\'' . $this->view->url(array('module' => 'search', 'controller' => 'search', 'action' => 'metadatasearch', 'add' => 'true'), null, true) . '\'');

        $removeElement = new Zend_Form_Element_Button('remove');
        $removeElement->setLabel('remove_searchfield');
        $removeElement->setAttrib('name', 'Action');
        $removeElement->setAttrib('onClick', 'javascript:location.href=\'' . $this->view->url(array('module' => 'search', 'controller' => 'search', 'action' => 'metadatasearch', 'remove' => 'true'), null, true) . '\'');

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('search_searchaction');

        // Add elements to form:
        $form->addElements(array($truncation, $hitsPerPage, $sort, $languageList, $doctypeList, $workflowSelector));
        for ($n = 0; $n < $searchfields->fields; $n++)
        {
            $form->addElements(array($field[$n], $query[$n]));
            if ($n < ($searchfields->fields-1))
            {
                $form->addElement($boolean[$n]);
            }
        }
        $form->addElement($addElement);
        if ($searchfields->fields > 1) {
        	$form->addElement($removeElement);
        }
        $form->addElement($submit);
        $form->setAction($this->view->url(array('module' => 'search', 'controller' => 'search', 'action' => 'metadatasearch'), null, true));
        $form->setMethod('post');
        return $form;
    }

    /**
     * Do the search operation and set the hitlist to the view
     *
     * @return void
     */
    public function metadatasearchAction()
    {
        $this->view->languageSelectorDisabled = true;
   		$config = Zend_Registry::get('Zend_Config');

		$searchEngine = $config->searchengine->engine;
		if (empty($searchEngine) === true) {
			$searchEngine = 'Lucene';
		}

        $this->view->title = $this->view->translate('search_searchresult');
        $page = 1;
        $resultlist = new Zend_Session_Namespace('resultlist');
        $searchfields = new Zend_Session_Namespace('fields');
        $failure = false;
        $data = $this->_request->getPost();
        $requestData = $this->_request->getParams();
        if (array_key_exists('add', $requestData) === true) {
            $searchfields->fields++;
            $form = $this->buildMetadataForm();
            $this->view->form = $form->populate($data);
            $this->render('form');
            return;
        }
        if (array_key_exists('remove', $requestData) === true) {
            $searchfields->fields--;
            $form = $this->buildMetadataForm();
            $this->view->form = $form->populate($data);
            $this->render('form');
            return;
        }
        if ($this->_request->isPost() === true) {
            // post request
            $data = $this->_request->getPost();
            $form = $this->buildMetadataForm();
            if ($form->isValid($data) === true) {
                // valid form
                $this->view->form = $form->populate($data);
                // build the query
                #print_r($data);
                $query = '';

                for ($n = 0; $n < $searchfields->fields; $n++)
                {
                	if ($n > 0 && false === empty($data['query' . $n])) {
                		$query .= ' ' . $data['boolean' . ($n-1)] . ' ';
                	}
                	if (false === empty($data['query' . $n])) {
                		$query .= $data['field' . $n] . ':';
                	    $query .= $data['query' . $n];
                	}
                	if ($data['searchtype'] === 'truncated')
                	{
                	    $query .= '*';
                	}
                }
                if ($data['language'] !== '0')
                {
                	if ($query !== '') {
                		$query .= ' AND ';
                	}
                	$query .= 'language:' . $data['language'];
                }
                if ($data['doctype'] !== '0')
                {
                	if ($query !== '') {
                		$query .= ' AND ';
                	}
                	$query .= 'doctype:' . $data['doctype'];
                }
                if ($data['workflow'] !== '0')
                {
                	if ($query !== '') {
                		$query .= ' AND ';
                	}
                	$query .= 'workflow:' . $data['workflow'];
                }
                try {
                    #echo "Complete query: " . $query;
                    $queryObject = new Opus_Search_Query($query, 'ignore', $searchEngine);
                    $hitlist = $queryObject->commit();
                    $resultlist->hitlist = $hitlist;
                    $resultlist->postedData = $data;
                }
                catch (Exception $e) {
                	$failure = $e->getMessage();
                }
            } else {
                // invalid form
                #print_r($data);
                $this->view->form = $form->populate($data);
                return $this->render('form');
            }
        } else {
            // nonpost request
            $hitlist = null;
            $form = $this->buildMetadataForm();
            $data = $this->_request->getParams();
            if (array_key_exists('noform', $data) === true) {
              	$this->view->noform = true;
            }
            if (isset($resultlist->postedData) === true) {
                if (array_key_exists('noform', $data) === false) {
                	$this->view->form = $form->populate($resultlist->postedData);
                }
                $data['hitsPerPage'] = $resultlist->postedData['hitsPerPage'];
                $data['sort'] = $resultlist->postedData['sort'];
            }
            if (array_key_exists('page', $data)) {
                // paginator
                $page = $data['page'];
                $hitlist = $resultlist->hitlist;
            }
            // do a new query by URL-parameter
            $query = '';
            foreach ($data as $searchField => $searchValue) {
            	// allow doctype to be searched additionally
                if (array_key_exists($searchField, $this->retrieveSearchFields()) || $searchField === 'doctype')
                {
                	$query .= $searchField . ':' . $searchValue . ' ';
                }
            }
            if ($query !== '') {
                try {
                    #echo "Complete query: " . $query;
                    $queryObject = new Opus_Search_Query($query, 'ignore', $searchEngine);
                    $hitlist = $queryObject->commit();
                    $resultlist->hitlist = $hitlist;
                }
                catch (Exception $e) {
                	$failure = $e->getMessage();
                }
            }
        }
        if ($failure === false) {
		    if (array_key_exists('sort', $data)) {
			    $hitlist->sort($data['sort']);
		    }
            $hitlistIterator = new Opus_Search_Iterator_HitListIterator($hitlist);
            $this->view->hitlist_count = count($hitlist);
            if (is_null($hitlist) === false) {
                $paginator = Zend_Paginator::factory($hitlistIterator);
                if (array_key_exists('hitsPerPage', $data)) {
        	        if ($data['hitsPerPage'] === '0') {
        	            $hitsPerPage = '10000';
        	        }
                    else {
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
                $this->view->relevance = array();
                $this->view->author = array();
                $this->view->url_frontdoor = array();
                $this->view->url_author = array();
                $d = null;
                foreach ($paginator as $hit) {
                	$h = $hit->getSearchHit();
                	$doc = $h->getDocument();
                	$id = $doc['id'];
                    $url_frontdoor = array(
                        'module' => 'frontdoor',
                        'controller' => 'index',
                        'action' => 'index',
                        'docId' => $id
                    );
                    $this->view->url_frontdoor[$runningIndex] = $this->view->url($url_frontdoor, 'default', true);

                    try {
                        $d = new Opus_Document( (int) $id);
                        $this->view->docId[$runningIndex] = $id;
                        $this->view->docState = $d->getServerState();
                        $c = count($d->getPersonAuthor());
                        $this->view->doctitle[$runningIndex] = $d->getTitleMain(0)->getValue();
                    }
                    catch (Exception $e) {
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
                                'module'        => 'search',
                                'controller'    => 'search',
                                'action'        => 'metadatasearch',
                                'author'        => $name
                            ),
                            null,
                            true
                        );
                        $this->view->author[$runningIndex][$counter] = $name;
                    }
                    try {
                    	if (array_key_exists('noform', $data) === false) {
                    		$this->view->relevance[$runningIndex] = $hit->getRelevance();
                    		$this->view->abstractValue[$runningIndex] = Opus_Search_Adapter_Lucene_SearchHitAdapter::highlight($queryObject->parsedQuery, $d->getTitleAbstract(0)->getValue());
                    	}
                    	else {
                    		if ($d !== null) {
                    		    $this->view->abstractValue[$runningIndex] = $d->getTitleAbstract(0)->getValue();
                    		}
                    	}
                    }
                    catch (Exception $e) {
                    	// dont do anything, the worst is that there is no abstract to display
                    }
                    $runningIndex++;
                }
            /*
            $paginator = Zend_Paginator::factory($hitlist);
            $paginator->setCurrentPageNumber($page);
            if (array_key_exists('hitsPerPage', $data)) {
        	    if ($data['hitsPerPage'] === '0') {
        	        $hitsPerPage = '10000';
        	    }
                else {
                	$hitsPerPage = $data['hitsPerPage'];
                }
                $paginator->setItemCountPerPage($hitsPerPage);
            }
            $this->view->hitlist_paginator = $paginator;
            */
            }
            $this->render('search');
        }
        else {
        	$this->view->failure = $failure;
            $this->render('search');
        }
    }

    /**
     * Retrieve a list of possible search fields from outside
     * 
     * @return Array array with fieldnames as keys and labels as values
     */
    public static function retrieveSearchFields()
    {
    	// aus Opus3:
    	// Titel, Person, Freitext, Schlagwort, Körperschaft, Fakultät, Institut, Abstract
    	// Dokumentart, Quelle, Jahr, verf. Klassifikationen
    	// Opus4: Personen differenzieren, Quelle raus (?)
    	// doctype should not be in array, because its a filter option and should not be selectable as search field
    	$fields = array(
            'title' => 'searchfield_title',
            'author' => 'searchfield_author',
            'persons' => 'searchfield_persons',
            'fulltext' => 'searchfield_fulltext',
            'abstract' => 'searchfield_abstract',
            'subject' => 'searchfield_subject',
            'year' => 'searchfield_year',
            'institute' => 'searchfield_institute',
            'urn' => 'searchfield_urn',
            'isbn' => 'searchfield_isbn',
            'series' => 'searchfield_series',
            'collection' => 'searchfield_coll',
            'opac-id' => 'searchfield_opac'
            );
    	return $fields;
    }
}
