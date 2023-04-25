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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\DocumentFinderInterface;
use Opus\Common\Repository;
use Opus\Common\Security\Realm;

/**
 * Main entry point for the review module.
 */
class Review_IndexController extends Application_Controller_Action
{
    /**
     * Restrict reviewable documents to the given status.
     *
     * @var string
     */
    private static $reviewServerState = 'unpublished';

    /** @var Publish_Model_LoggedUser */
    private $loggedUser;

    /**
     * Setup module.  Check privileges.
     */
    public function init()
    {
        parent::init();

        // Highlight menu entries.
        if (true === Realm::getInstance()->checkModule('admin')) {
            $this->getHelper('MainMenu')->setActive('admin');
        } else {
            $this->getHelper('MainMenu')->setActive('review');
        }

        $this->loggedUser  = new Publish_Model_LoggedUser();
        $this->view->title = $this->view->translate('review_index_title');
    }

    /**
     * Default action shows the table of unpublished documents.
     *
     * Processes clicked buttons on that page.
     */
    public function indexAction()
    {
        $ids = $this->filterReviewableIds($this->_getParam('selected'));

        if ($this->isButtonPressed('buttonSubmit', true, false)) {
            $this->_forward('clear', null, null, ['selected' => $ids]);
            return;
        }

        if ($this->isButtonPressed('buttonReject', true, false)) {
            $this->_forward('reject', null, null, ['selected' => $ids]);
            return;
        }

        $sortOrder   = $this->_getParam('sort_order');
        $sortReverse = $this->_getParam('sort_reverse') ? 1 : 0;
        $sortReverse = $this->isButtonPressed('buttonUp', '0', $sortReverse);
        $sortReverse = $this->isButtonPressed('buttonDown', '1', $sortReverse);

        $this->view->selected     = $ids;
        $this->view->actionUrl    = $this->view->url(['action' => 'index']);
        $this->view->sort_order   = $sortOrder;
        $this->view->sort_reverse = $sortReverse;
        $this->view->selectAll    = $this->_getParam('buttonSelectAll') ? 1 : 0;
        $this->view->selectNone   = $this->_getParam('buttonSelectNone') ? 1 : 0;
        $this->view->sortOptions  = [
            'author'          => $this->view->translate('review_option_author'),
            'publicationDate' => $this->view->translate('review_option_date'),
            'docType'         => $this->view->translate('review_option_doctype'),
            'title'           => $this->view->translate('review_option_title'),
            'id'              => $this->view->translate('review_option_docid'),
        ];

        // Get list of document identifiers
        $finder = $this->prepareDocumentFinder();

        switch ($sortOrder) {
            case 'author':
                $finder->setOrder($finder::ORDER_AUTHOR, $sortReverse !== 1);
                break;
            case 'publicationDate':
                $finder->setOrder($finder::ORDER_SERVER_DATE_PUBLISHED, $sortReverse !== 1);
                break;
            case 'docType':
                $finder->setOrder($finder::ORDER_DOCUMENT_TYPE, $sortReverse !== 1);
                break;
            case 'title':
                $finder->setOrder($finder::ORDER_TITLE, $sortReverse !== 1);
                break;
            default:
                $finder->setOrder($finder::ORDER_ID, $sortReverse !== 1);
        }

        $this->view->breadcrumbsDisabled = true;

        $result = $finder->getIds();

        if (empty($result)) {
            $this->view->message = 'review_no_docs_found';
            $this->render('message');
            return;
        }

        $currentPage = $this->_getParam('page', 1);
        $paginator   = Zend_Paginator::factory($result);
        $paginator->setCurrentPageNumber($currentPage);
        $paginator->setItemCountPerPage(10);

        $this->view->currentPage   = $currentPage;
        $this->view->documentCount = count($result);
        $this->view->paginator     = $paginator;
    }

    /**
     * Action for showing the clear form and processing POST from it.
     */
    public function clearAction()
    {
        $ids = $this->filterReviewableIds($this->_getParam('selected'));

        if (count($ids) < 1) {
            $this->view->message = 'review_error_noselection';
            $this->render('message');
            return;
        }

        $this->view->selected      = $ids;
        $this->view->documentCount = count($ids);
        $this->view->actionUrl     = $this->view->url(['action' => 'clear']);

        $person = null;

        $config         = $this->getConfig();
        $useCurrentUser = isset($config, $config->clearing->addCurrentUserAsReferee) &&
            filter_var($config->clearing->addCurrentUserAsReferee, FILTER_VALIDATE_BOOLEAN);

        if ($useCurrentUser) {
            $person = $this->loggedUser->createPerson();

            if ($person === null || ! $person->isValid()) {
                $message = "Problem clearing documents.  Information for current user is incomplete or invalid.";
                $this->getLogger()->err($message);
                throw new Application_Exception($message);
            }
        }

        if ($this->isButtonPressed('sureno', true, false)) {
            $this->_forward('index', null, null, ['selected' => $ids]);
            return;
        }

        if ($this->isButtonPressed('sureyes', true, false)) {
            $helper = new Review_Model_ClearDocumentsHelper();

            $userId = $this->loggedUser->getUserId();
            if ($userId === null || empty($userId)) {
                $userId = 'unknown';
            }

            $helper->clear($ids, $userId, $person);

            $this->view->message = 'review_accept_success';
            $this->render('message');
            return;
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
        $ids = $this->filterReviewableIds($this->_getParam('selected'));

        if (count($ids) < 1) {
            $this->view->message = 'review_error_noselection';
            $this->render('message');
            return;
        }

        $this->view->selected      = $ids;
        $this->view->documentCount = count($ids);
        $this->view->actionUrl     = $this->view->url(['action' => 'reject']);

        if ($this->isButtonPressed('sureno', true, false)) {
            $this->_forward('index', null, null, ['selected' => $ids]);
            return;
        }

        if ($this->isButtonPressed('sureyes', true, false)) {
            $helper = new Review_Model_ClearDocumentsHelper();

            $userId = $this->loggedUser->getUserId();
            if ($userId === null || empty($userId)) {
                $userId = 'unknown';
            }

            $helper->reject($ids, $userId);

            $this->view->message = 'review_reject_success';
            $this->render('message');
            return;
        }

        $this->view->title       = 'review_reject_title';
        $this->view->instruction = 'review_reject_instruction';
        $this->render('confirm');
    }

    /**
     * Prepare document finder.
     *
     * @return DocumentFinderInterface
     */
    protected function prepareDocumentFinder()
    {
        $finder = Repository::getInstance()->getDocumentFinder();
        $finder->setServerState(self::$reviewServerState);

        $logger               = $this->getLogger();
        $userId               = $this->loggedUser->getUserId();
        $onlyReviewerByUserId = false;

        // Add constraint for reviewer, if current user is *not* admin.
        if (Realm::getInstance()->checkModule('admin')) {
            $message = "Review: Showing all unpublished documents to admin";
            $logger->debug($message . " (user_id: $userId)");
        } elseif (Realm::getInstance()->checkModule('review')) {
            if ($onlyReviewerByUserId) {
                $message = "Review: Showing only documents belonging to reviewer";
                $finder->setEnrichmentValue('reviewer.user_id', $userId);
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
    protected function filterReviewableIds($ids)
    {
        if (isset($ids) && ! is_array($ids)) {
            $ids = [$ids];
        }

        if (! isset($ids) || ! is_array($ids) || (count($ids) < 1)) {
            return [];
        }

        $this->getLogger()->debug("ids before filtering: " . implode(", ", $ids));

        $finder   = $this->prepareDocumentFinder();
        $foundIds = $finder->setDocumentIds($ids)->getIds();

        $this->getLogger()->debug("ids after filtering: " . implode(", ", $foundIds));

        return $foundIds;
    }

    /**
     * Checks if a button has been pressed and selects value.
     *
     * @param string      $name
     * @param string      $value
     * @param string|null $default
     * @return string
     */
    protected function isButtonPressed($name, $value, $default = null)
    {
        $button = $this->_getParam($name);
        return isset($button) && ! empty($button) ? $value : $default;
    }
}
