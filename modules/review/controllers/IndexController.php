<?php
/*
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
 * @package     Application - Module Review
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for the review module.
 *
 * @category    Application
 * @package     Module_Publish
 */
class Review_IndexController extends Controller_Action {

    /**
     * Setup title.
     */
    public function init() {
        parent::init();
        $this->view->title = $this->view->translate('review_index_title');
    }

    /**
     * Default action shows the table of unpublished documents.
     *
     * Processes clicked buttons on that page.
     */
    public function indexAction() {
        $this->_processSelection();

        if ($this->_isButtonPressed('buttonSubmit', true, false)) {
            if (count($this->view->selected) > 0) {
                $this->_forward('clear');
            }
            else {
                $this->view->error = $this->view->translate('review_error_noselection');
            }
        }

        $this->view->actionUrl = $this->view->url(array('action'=>'index'));

        $request = $this->getRequest();

        $sort_order = $request->getParam('sort_order');
        $this->view->sort_order = $sort_order;

        $sort_reverse = $request->getParam('sort_reverse', '0');
        $sort_reverse = $this->_isButtonPressed('buttonUp', '0');
        $sort_reverse = $this->_isButtonPressed('buttonDown', '1');

        $this->view->selectAll = $this->_isButtonPressed('buttonSelectAll', true, false);
        $this->view->selectNone = $this->_isButtonPressed('buttonSelectNone', true, false);

        $this->_prepareSortOptions();

        // Get list of document identifiers
        $result = $this->_helper->documents($sort_order, $sort_reverse, 'unpublished');

        // TODO remove or disable if log level is not DEBUG
        foreach ($result as $testid) {
            $this->_logger->debug('Document '. $testid);
        }

        if (empty($result)) {
            $this->_helper->viewRenderer('nodocs');
        }
        else {
            $this->view->documentCount = count($result);

            $currentPage = $this->_getParam('page', 1);
            $this->view->currentPage = $currentPage;

            $paginator = Zend_Paginator::factory($result);
            $paginator->setCurrentPageNumber($currentPage);
            $paginator->setItemCountPerPage(10);
            $this->view->paginator = $paginator;
        }
    }



    /**
     * Action for showing the clear form and processing POST from it.
     */
    public function clearAction() {
        // redirect get requests to module entry page
        if (!$this->getRequest()->isPost()) {
            $this->_redirectTo('index');
        }

        // if back button was pressed return to document selection
        if ($this->_isButtonPressed('buttonBack', true, false)) {
            $this->_forward('index');
            return;
        }

        $this->view->actionUrl = $this->view->url(array('action'=>'clear'));

        $this->_processSelection();

        $this->view->documentCount = count($this->view->selected);

        $firstName = $this->getRequest()->getParam('firstname');
        $this->view->firstName = $firstName;

        $lastName = $this->getRequest()->getParam('lastname');
        $this->view->lastName = $lastName;

        if ($this->_isButtonPressed('buttonClear', true, false)) {
            if (!Zend_Validate::is($firstName, 'NotEmpty')) {
                $this->view->error = $this->view->translate('review_error_input_missing');
            }

            if (!Zend_Validate::is($lastName, 'NotEmpty')) {
                $this->view->error = $this->view->translate('review_error_input_missing');
            }

            if (empty($this->view->error)) {
                $helper = new Review_Model_ClearDocumentsHelper();
                $helper->clear($this->view->selected, $lastName, $firstName);
                $this->_redirectTo('index');
            }
        }
    }

    /**
     * Processes form input, especially selected documents.
     */
    protected function _processSelection() {
        $selected = $this->getRequest()->getParam('selected');

        if (!isset($selected) || !is_array($selected)) {
            $selected = array();
        }

        $this->view->selected = $selected;
    }

    /**
     * Prepares array of sorting options for the form.
     */
    protected function _prepareSortOptions() {
        $sortOptions = array();
        $sortOptions['id'] = $this->view->translate('review_option_docid');
        $sortOptions['title'] = $this->view->translate('review_option_title');
        $sortOptions['author'] = $this->view->translate('review_option_author');
        $sortOptions['publicationDate'] = $this->view->translate('review_option_date');
        $sortOptions['docType'] = $this->view->translate('review_option_doctype');
        $this->view->sortOptions = $sortOptions;
    }

    /**
     * Checks if a button has been pressed and selects value.
     * @param <type> $name
     * @param <type> $value
     * @param <type> $default
     * @return mixed
     */
    protected function _isButtonPressed($name, $value, $default = null) {
        $button = $this->getRequest()->getParam($name);

        if (isset($button)) {
            return $value;
        }
        else {
            return $default;
        }
    }

}

