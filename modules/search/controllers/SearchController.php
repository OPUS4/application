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

        $searchForm = new MetadataSearch();
        $searchForm->setAction($this->view->url(array("controller"=>"search", "action"=>"metadatasearch")));
        $searchForm->setMethod('post');

        $this->view->form = $searchForm;
        $this->render('form');
    }

    /**
     * Do the search operation and set the hitlist to the view
     *
     * @return void
     */
    public function searchAction()
    {
        $this->view->title = $this->view->translate('search_searchresult');
        $page = 1;
        $resultlist = new Zend_Session_Namespace('resultlist');
        if ($this->_request->isPost() === true) {
            // post request
            $data = $this->_request->getPost();
            $form = new FulltextSearch();
            if ($form->isValid($data) === true) {
                // valid form
                $this->view->form = $form->populate($data);
                $query = new Opus_Search_Query($form->getValue('query'));
                $hitlist = $query->commit();
                $resultlist->hitlist = $hitlist;
            } else {
                // invalid form
                $this->view->form = $form->populate($data);
                return $this->render('form');
            }
        } else {
            // nonpost request
            $data = $this->_request->getParams();
            if (array_key_exists('page', $data)) {
                // paginator
                $page = $data['page'];
                $hitlist = $resultlist->hitlist;
            } else {
                return $this->_forward('fulltextsearch');
            }
        }

        $hitlistIterator = new Opus_Search_Iterator_HitListIterator($hitlist);
        $this->view->hitlist_count = $hitlist->count();
        $paginator = Zend_Paginator::factory($hitlistIterator);
        $paginator->setCurrentPageNumber($page);
        $this->view->hitlist_paginator = $paginator;
    }

    /**
     * Do the search operation and set the hitlist to the view
     *
     * @return void
     */
    public function metadatasearchAction()
    {
        $this->view->title = $this->view->translate('search_searchresult');
        $page = 1;
        $resultlist = new Zend_Session_Namespace('resultlist');
        if ($this->_request->isPost() === true) {
            // post request
            $data = $this->_request->getPost();
            $form = new MetadataSearch();
            if ($form->isValid($data) === true) {
                // valid form
                $this->view->form = $form->populate($data);
                // build the query
                $query = new Opus_Search_Query($form->getValue('query'));
                $hitlist = $query->commit();
                $resultlist->hitlist = $hitlist;
            } else {
                // invalid form
                $this->view->form = $form->populate($data);
                return $this->render('form');
            }
        } else {
            // nonpost request
            $data = $this->_request->getParams();
            if (array_key_exists('page', $data)) {
                // paginator
                $page = $data['page'];
                $hitlist = $resultlist->hitlist;
            } else {
                return $this->_forward('fulltextsearch');
            }
        }

        $hitlistIterator = new Opus_Search_Iterator_HitListIterator($hitlist);
        $this->view->hitlist_count = $hitlist->count();
        $paginator = Zend_Paginator::factory($hitlistIterator);
        $paginator->setCurrentPageNumber($page);
        $this->view->hitlist_paginator = $paginator;
    }
}
