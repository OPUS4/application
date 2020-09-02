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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for the review module.
 *
 * @category    Application
 * @package     Module_Publish
 */
class Review_IndexController extends Application_Controller_Action
{

    /**
     * Restrict reviewable documents to the given status.
     *
     * @var string
     */
    private static $_reviewServerState = 'unpublished';

    /**
     * @var Publish_Model_LoggedUser
     */
    private $_loggedUser = null;

    /**
     * Setup module.  Check privileges.
     */
    public function init()
    {
        parent::init();

        // Highlight menu entries.
        if (true === Opus_Security_Realm::getInstance()->checkModule('admin')) {
            $this->getHelper('MainMenu')->setActive('admin');
        } else {
            $this->getHelper('MainMenu')->setActive('review');
        }

        $this->_loggedUser = new Publish_Model_LoggedUser();
        $this->view->title = $this->view->translate('review_index_title');
    }

    /**
     * Default action shows the table of unpublished documents.
     *
     * Processes clicked buttons on that page.
     */
    public function indexAction()
    {
        $ids = $this->_filterReviewableIds($this->_getParam('selected'));

        if ($this->_isButtonPressed('buttonSubmit', true, false)) {
            return $this->_forward('clear', null, null, ['selected' => $ids]);
        }

        if ($this->_isButtonPressed('buttonReject', true, false)) {
            return $this->_forward('reject', null, null, ['selected' => $ids]);
        }

        $sortOrder = $this->_getParam('sort_order');
        $sortReverse = $this->_getParam('sort_reverse') ? 1 : 0;
        $sortReverse = $this->_isButtonPressed('buttonUp', '0', $sortReverse);
        $sortReverse = $this->_isButtonPressed('buttonDown', '1', $sortReverse);

        $this->view->selected = $ids;
        $this->view->actionUrl = $this->view->url(['action' => 'index']);
        $this->view->sort_order = $sortOrder;
        $this->view->sort_reverse = $sortReverse;
        $this->view->selectAll = $this->_getParam('buttonSelectAll') ? 1 : 0;
        $this->view->selectNone = $this->_getParam('buttonSelectNone') ? 1 : 0;
        $this->view->sortOptions = [
            'author' => $this->view->translate('review_option_author'),
            'publicationDate' => $this->view->translate('review_option_date'),
            'docType' => $this->view->translate('review_option_doctype'),
            'title' => $this->view->translate('review_option_title'),
            'id' => $this->view->translate('review_option_docid'),
        ];

        // Get list of document identifiers
        $finder = $this->_prepareDocumentFinder();

        switch ($sortOrder) {
            case 'author':
                $finder->orderByAuthorLastname($sortReverse != 1);
                break;
            case 'publicationDate':
                $finder->orderByServerDatePublished($sortReverse != 1);
                break;
            case 'docType':
                $finder->orderByType($sortReverse != 1);
                break;
            case 'title':
                $finder->orderByTitleMain($sortReverse != 1);
                break;
            default:
                $finder->orderById($sortReverse != 1);
        }

        $this->view->breadcrumbsDisabled = true;

        $result = $finder->ids();
        if (empty($result)) {
            $this->view->message = 'review_no_docs_found';
            return $this->render('message');
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
    public function clearAction()
    {
        $ids = $this->_filterReviewableIds($this->_getParam('selected'));

        if (count($ids) < 1) {
            $this->view->message = 'review_error_noselection';
            return $this->render('message');
        }

        $this->view->selected = $ids;
        $this->view->documentCount = count($ids);
        $this->view->actionUrl = $this->view->url(['action' => 'clear']);

        $person = null;

        $config = $this->getConfig();
        $useCurrentUser = isset($config, $config->clearing->addCurrentUserAsReferee) &&
            filter_var($config->clearing->addCurrentUserAsReferee, FILTER_VALIDATE_BOOLEAN);

        if ($useCurrentUser) {
            $person = $this->_loggedUser->createPerson();

            if (is_null($person) or ! $person->isValid()) {
                $message = "Problem clearing documents.  Information for current user is incomplete or invalid.";
                $this->getLogger()->err($message);
                throw new Application_Exception($message);
            }
        }

        if ($this->_isButtonPressed('sureno', true, false)) {
            return $this->_forward('index', null, null, ['selected' => $ids]);
        }

        if ($this->_isButtonPressed('sureyes', true, false)) {
            $helper = new Review_Model_ClearDocumentsHelper();

            $userId = $this->_loggedUser->getUserId();
            if (is_null($userId) or empty($userId)) {
                $userId = 'unknown';
            }

            $helper->clear($ids, $userId, $person);

            $this->view->message = 'review_accept_success';
            return $this->render('message');
        }

        $this->view->title       = 'review_accept_title';
        $this->view->instruction = 'review_accept_instruction';
        $this->render('confirm');
    }

    /**
     * Confirm rejection of selected documents and reject.
     */
    public function rejectAction()
    {
        $ids = $this->_filterReviewableIds($this->_getParam('selected'));

        if (count($ids) < 1) {
            $this->view->message = 'review_error_noselection';
            return $this->render('message');
        }

        $this->view->selected = $ids;
        $this->view->documentCount = count($ids);
        $this->view->actionUrl = $this->view->url(['action' => 'reject']);

        if ($this->_isButtonPressed('sureno', true, false)) {
            return $this->_forward('index', null, null, ['selected' => $ids]);
        }

        if ($this->_isButtonPressed('sureyes', true, false)) {
            $helper = new Review_Model_ClearDocumentsHelper();

            $userId = $this->_loggedUser->getUserId();
            if (is_null($userId) or empty($userId)) {
                $userId = 'unknown';
            }

            $helper->reject($ids, $userId);

            $this->view->message = 'review_reject_success';
            return $this->render('message');
        }

        $this->view->title       = 'review_reject_title';
        $this->view->instruction = 'review_reject_instruction';
        $this->render('confirm');
    }

    /**
     * Prepare document finder.
     *
     * @return Opus_DocumentFinder
     */
    protected function _prepareDocumentFinder()
    {
        $finder = new Opus_DocumentFinder();
        $finder->setServerState(self::$_reviewServerState);

        $logger = $this->getLogger();
        $userId = $this->_loggedUser->getUserId();
        $onlyReviewerByUserId = false;

        // Add constraint for reviewer, if current user is *not* admin.
        if (Opus_Security_Realm::getInstance()->checkModule('admin')) {
            $message = "Review: Showing all unpublished documents to admin";
            $logger->debug($message . " (user_id: $userId)");
        } elseif (Opus_Security_Realm::getInstance()->checkModule('review')) {
            if ($onlyReviewerByUserId) {
                $message = "Review: Showing only documents belonging to reviewer";
                $finder->setEnrichmentKeyValue('reviewer.user_id', $userId);
            } else {
                $message = "Review: Showing all unpublished documents to reviewer";
            }
            $logger->debug($message . " (user_id: $userId)");
        } else {
            $message = 'Review: Access to unpublished documents denied.';
            $logger->err($message . " (user_id: $userId)");
            throw new Application_Exception($message);
        }

        return $finder;
    }

    /**
     * Filter a given document list for reviewable ids.
     *
     * @param array $ids
     * @return array
     */
    protected function _filterReviewableIds($ids)
    {
        if (isset($ids) and ! is_array($ids)) {
            $ids = [$ids];
        }

        if (! isset($ids) or ! is_array($ids) or (count($ids) < 1)) {
            return [];
        }

        $this->getLogger()->debug("ids before filtering: " . implode(", ", $ids));

        $finder = $this->_prepareDocumentFinder();
        $ids = $finder->setIdSubset($ids)->ids();

        $this->getLogger()->debug("ids after filtering: " . implode(", ", $ids));
        return $ids;
    }

    /**
     * Checks if a button has been pressed and selects value.
     * @param <type> $name
     * @param <type> $value
     * @param <type> $default
     * @return mixed
     */
    protected function _isButtonPressed($name, $value, $default = null)
    {
        $button = $this->_getParam($name);
        return (isset($button) && ! empty($button)) ? $value : $default;
    }
}
