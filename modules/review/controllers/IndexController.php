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
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
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
     * Restrict reviewable documents to the given status.
     *
     * @var string
     */
    public static $reviewServerState = 'unpublished';

    /**
     * Setup module.  Check privileges.
     */
    public function init() {
        parent::init();

        $this->requirePrivilege('clearance');

        if (true === Opus_Security_Realm::getInstance()->check('administrate')) {
            $this->getHelper('MainMenu')->setActive('admin');
        }
        else {
            $this->getHelper('MainMenu')->setActive('review');
        }

        $this->view->title = $this->view->translate('review_index_title');
    }

    /**
     * Default action shows the table of unpublished documents.
     *
     * Processes clicked buttons on that page.
     */
    public function indexAction() {
        $ids = $this->_filterReviewableIds( $this->_getParam('selected') );

        if ($this->_isButtonPressed('buttonSubmit', true, false)) {
            $this->_forward('clear', null, null, array('selected' => $ids));
        }

        if ($this->_isButtonPressed('buttonReject', true, false)) {
            $this->_forward('reject', null, null, array('selected' => $ids));
        }

        $sort_order = $this->_getParam('sort_order');
        $sort_reverse = $this->_getParam('sort_reverse') ? 1 : 0;
        $sort_reverse = $this->_isButtonPressed('buttonUp', '0', $sort_reverse);
        $sort_reverse = $this->_isButtonPressed('buttonDown', '1', $sort_reverse);

        $this->view->selected = $ids;
        $this->view->actionUrl = $this->view->url(array('action'=>'index'));
        $this->view->sort_order = $sort_order;
        $this->view->sort_reverse = $sort_reverse;
        $this->view->selectAll = $this->_getParam('buttonSelectAll')  ? 1 : 0;
        $this->view->selectNone = $this->_getParam('buttonSelectNone')  ? 1 : 0;
        $this->view->sortOptions = array(
            'author' => $this->view->translate('review_option_author'),
            'publicationDate' => $this->view->translate('review_option_date'),
            'docType' => $this->view->translate('review_option_doctype'),
            'title' => $this->view->translate('review_option_title'),
            'id' => $this->view->translate('review_option_docid'),
        );

        // Get list of document identifiers
        $finder = $this->_prepareDocumentFinder();

        switch ($sort_order) {
            case 'author':
                $finder->orderByAuthorLastname($sort_reverse != 1);
                break;
            case 'publicationDate':
                $finder->orderByServerDatePublished($sort_reverse != 1);
                break;
            case 'docType':
                $finder->orderByType($sort_reverse != 1);
                break;
            case 'title':
                $finder->orderByTitleMain($sort_reverse != 1);
                break;
            default:
                $finder->orderById($sort_reverse != 1);
        }

        $result = $finder->ids();
        if (empty($result)) {
            return $this->render('nodocs');
        }

        $currentPage = $this->_getParam('page', 1);
        $paginator = Zend_Paginator::factory($result);
        $paginator->setCurrentPageNumber($currentPage);
        $paginator->setItemCountPerPage(10);

        $this->view->currentPage = $currentPage;
        $this->view->documentCount = count($result);
        $this->view->paginator = $paginator;
    }

    /**
     * Action for showing the clear form and processing POST from it.
     */
    public function clearAction() {
        $ids = $this->_filterReviewableIds( $this->_getParam('selected') );

        if (count($ids) < 1) {
            return $this->render('nodocs');
        }

        $this->view->selected = $ids;
        $this->view->documentCount = count($ids);
        $this->view->actionUrl = $this->view->url(array('action'=>'clear'));

        $useCurrentUser = false;
        $person = null;

        $config = Zend_Registry::get('Zend_Config');
        if (isset($config, $config->clearing->addCurrentUserAsReferee)) {
            $useCurrentUser = $config->clearing->addCurrentUserAsReferee;
        }

        if ($useCurrentUser) {
            $loggedUserModel = new Publish_Model_LoggedUser();
            $person = $loggedUserModel->createPerson();

            if (is_null($person) or !$person->isValid()) {
                $message = "Problem clearing documents.  Information for current user is incomplete or invalid.";
                $this->_logger->err($message);
                throw new Application_Exception($message);
            }
        }

        if ($this->_isButtonPressed('sureno', true, false)) {
            $this->_forward('index', null, null, array('selected' => $ids));
        }

        if ($this->_isButtonPressed('sureyes', true, false)) {
            $helper = new Review_Model_ClearDocumentsHelper();
            $helper->clear($ids, $person);
            $this->_redirectTo('index');
        }

        $this->view->text = $this->view->translate('review_accept_sure');
    }

    /**
     * Confirm rejection of selected documents and reject.
     */
    public function rejectAction() {
        $ids = $this->_filterReviewableIds( $this->_getParam('selected') );

        if (count($ids) < 1) {
            return $this->render('nodocs');
        }

        $this->view->selected = $ids;
        $this->view->documentCount = count($ids);
        $this->view->actionUrl = $this->view->url(array('action' => 'reject'));

        if ($this->_isButtonPressed('sureno', true, false)) {
            $this->_forward('index', null, null, array('selected' => $ids));
        }

        if ($this->_isButtonPressed('sureyes', true, false)) {
            $helper = new Review_Model_ClearDocumentsHelper();
            $helper->reject($ids);
            $this->_redirectTo('index');
        }

        $this->view->text = $this->view->translate('review_reject_sure');
    }

    /**
     * Prepare document finder.
     *
     * @return Opus_DocumentFinder
     */
    protected function _prepareDocumentFinder() {
        $finder = new Opus_DocumentFinder();
        $finder->setServerState(self::$reviewServerState);

        // Add constraint for reviewer, if current user is *not* admin.
        if (false === Opus_Security_Realm::getInstance()->check('administrate')) {
            $loggedUser = new Publish_Model_LoggedUser();
            $userId = $loggedUser->getUserId();
            $finder->setEnrichmentKeyValue('reviewer.user_id', $userId);
        }

        return $finder;
    }

    /**
     * Filter a given document list for reviewable ids.
     *
     * @param array $ids
     * @return array
     */
    protected function _filterReviewableIds( $ids ) {
        if (!isset($ids) || !is_array($ids) || count($ids) < 1) {
            return array();
        }

        $this->_logger->debug("ids before filtering: " . implode(", ", $ids));

        $finder = $this->_prepareDocumentFinder();
        $ids = $finder->setIdSubset($ids)->ids();

        $this->_logger->debug("ids after filtering: " . implode(", ", $ids));
        return $ids;
    }

    /**
     * Checks if a button has been pressed and selects value.
     * @param <type> $name
     * @param <type> $value
     * @param <type> $default
     * @return mixed
     */
    protected function _isButtonPressed($name, $value, $default = null) {
        $button = $this->_getParam($name);
        return isset($button) ? $value : $default;
    }

}

