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

    public static $reviewServerState = 'unpublished';

    /**
     * Setup title.
     */
    public function init() {
        parent::init();
        $this->view->title = $this->view->translate('review_index_title');

        // Get list of selected documents...
        $selected = $this->getRequest()->getParam('selected');
        if (!isset($selected) || !is_array($selected)) {
            $selected = array();
        }

        // Add constraint for current user
        if (count($selected) > 0) {
            $this->_logger->debug("ids before: " . implode(", ", $selected));
            $finder = $this->_prepareSearcher()->setIdSubset($selected);
            $selected = $finder->ids();
            $this->_logger->debug("ids after: " . implode(", ", $selected));
        }

        $this->view->selected = $selected;
    }

    /**
     * Default action shows the table of unpublished documents.
     *
     * Processes clicked buttons on that page.
     */
    public function indexAction() {
        if ($this->_isButtonPressed('buttonSubmit', true, false)) {
            if (count($this->view->selected) > 0) {
                $this->_forward('clear');
            }
            else {
                $this->view->error = $this->view->translate(
                        'review_error_noselection');
            }
        }

        if ($this->_isButtonPressed('buttonReject', true, false)) {
            if (count($this->view->selected) > 0) {
                $this->_forward('reject');
            }
            else {
                $this->view->error = $this->view->translate('review_error_noselection');
            }
        }

        $this->view->actionUrl = $this->view->url(array('action'=>'index'));

        $request = $this->getRequest();

        $sort_order = $request->getParam('sort_order');
        $this->view->sort_order = $sort_order;

        $sort_reverse = $request->getParam('sort_reverse') ? 1 : 0;
        $sort_reverse = $this->_isButtonPressed('buttonUp', '0', $sort_reverse);
        $sort_reverse = $this->_isButtonPressed('buttonDown', '1', $sort_reverse);
        $this->view->sort_reverse = $sort_reverse;

        $this->view->selectAll = $this->_isButtonPressed('buttonSelectAll',
                true, false);
        $this->view->selectNone = $this->_isButtonPressed('buttonSelectNone',
                true, false);

        $this->_prepareSortOptions();

        // Get list of document identifiers
        $finder = $this->_prepareSearcher();

        switch ($sort_order) {
            case 'author':
                $finder->orderByAuthorLastname($sort_reverse != 1);
            case 'publicationDate':
                $finder->orderByServerDatePublished($sort_reverse != 1);
            case 'docType':
                $finder->orderByType($sort_reverse != 1);
            case 'title':
                $finder->orderByTitleMain($sort_reverse != 1);
            default:
                $finder->orderById($sort_reverse != 1);
        }

        $result = $finder->ids();

        // TODO remove or disable if log level is not DEBUG
        foreach ($result as $testid) {
            $this->_logger->debug('Document '. $testid);
        }

        if (empty($result)) {
            return $this->_helper->viewRenderer('nodocs');
        }

        $this->view->documentCount = count($result);

        $currentPage = $this->_getParam('page', 1);
        $this->view->currentPage = $currentPage;

        $paginator = Zend_Paginator::factory($result);
        $paginator->setCurrentPageNumber($currentPage);
        $paginator->setItemCountPerPage(10);
        $this->view->paginator = $paginator;
    }



    /**
     * Action for showing the clear form and processing POST from it.
     */
    public function clearAction() {
        // redirect get requests to module entry page
        if (!$this->getRequest()->isPost()) {
            $this->_redirectTo('index');
            return;
        }

        $config = Zend_Registry::get('Zend_Config');

        // if back button was pressed return to document selection
        if ($this->_isButtonPressed('buttonBack', true, false)) {
            $this->_forward('index');
            return;
        }

        $this->view->actionUrl = $this->view->url(array('action'=>'clear'));

        $useCurrentUser = false;
        if (isset($config->clearing->addCurrentUserAsReferee)) {
            $useCurrentUser = $config->clearing->addCurrentUserAsReferee;
        }

        if ($useCurrentUser) {
            $this->_logger->debug("useCurrentUser...");

            $loggedUserModel = new Publish_Model_LoggedUser();
            $person = $loggedUserModel->createPerson();

            if (is_null($person) or !$person->isValid()) {
                $message = "Problem clearing documents.  Person object for logged user is null or not valid.";
                $this->_logger->err($message);
                throw new Application_Exception($message);
            }

            $helper = new Review_Model_ClearDocumentsHelper();
            $helper->clear($this->view->selected, $person);
            $this->_redirectTo('index');

            return;
        }

        $this->_logger->debug("not-useCurrentUser...");

        $this->view->documentCount = count($this->view->selected);
        $this->view->firstName = $this->getRequest()->getParam('firstname');
        $this->view->lastName = $this->getRequest()->getParam('lastname');

        if ($this->_isButtonPressed('buttonAccept', true, false)) {
            if (!Zend_Validate::is($this->view->firstName, 'NotEmpty')) {
                $this->view->error = $this->view->translate(
                                'review_error_input_missing');
            }

            if (!Zend_Validate::is($this->view->lastName, 'NotEmpty')) {
                $this->view->error = $this->view->translate(
                                'review_error_input_missing');
            }

            if (empty($this->view->error)) {
                $person = new Opus_Person();
                $person->setFirstName(trim($this->view->firstName))
                        ->setLastName(trim($this->view->lastName));

                $helper = new Review_Model_ClearDocumentsHelper();
                $helper->clear($this->view->selected, $person);
                $this->_redirectTo('index');
            }
        }

    }

    /**
     * Confirm rejection of selected documents and reject.
     */
    public function rejectAction() {
        // redirect get requests to module entry page
        if (!$this->getRequest()->isPost()) {
            $this->_redirectTo('index');
            return;
        }

        $this->view->documentCount = count($this->view->selected);

        if ($this->_isButtonPressed('sureno', true, false)) {
            $this->_forward('index');
            return;
        }
 
        if ($this->_isButtonPressed('sureyes', true, false)) {
            $helper = new Review_Model_ClearDocumentsHelper();
            $helper->reject($this->view->selected);
            $this->_redirectTo('index');
        }

//        $this->view->title = $this->view->translate('admin_doc_delete');
        $this->view->text = $this->view->translate('review_reject_sure');
        $this->view->actionUrl = $this->view->url(array('action' => 'reject'));
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
     * Prepare document finder.
     *
     * @return Opus_DocumentFinder
     */
    protected function _prepareSearcher() {
        $loggedUser = new Publish_Model_LoggedUser();

        $finder = new Opus_DocumentFinder();
        $finder->setServerState(self::$reviewServerState);
        $finder->setEnrichmentKeyValue('reviewer.user_id', $loggedUser->getUserId());

        return $finder;
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

