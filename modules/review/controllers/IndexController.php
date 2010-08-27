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
 *
 * TODO Is it necessary to extend Controller_CRUDAction?
 */
class Review_IndexController extends Controller_CRUDAction {


    /**
     * The class of the model being administrated.
     *
     * @var Opus_Model_Abstract
     */
    protected $_modelclass = 'Opus_Document';

    /**
     * Returns a filtered representation of the document.
     *
     * @param  Opus_Document  $document The document to be filtered.
     * @return Opus_Model_Filter The filtered document.
     *
     * TODO Is this used?
     */
    private function __createFilter(Opus_Document $document, $page = null) {
        $filter = new Opus_Model_Filter();
        $filter->setModel($document);
        $blacklist = array('Collection', 'IdentifierOpus3', 'Source', 'File', 
            'ServerState', 'ServerDatePublished', 'ServerDateModified',
            'ServerDateUnlocking', 'Type');
        $filter->setBlacklist($blacklist);
        // $filter->setSortOrder($type->getAdminFormSortOrder());
        return $filter;
    }


    /**
     * Default action shows the table of unpublished documents.
     *
     * Processes clicked buttons on that page.
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('review_index_title');

        $this->view->actionUrl = $this->view->url(array(
            'module' => 'review', 'controller'=>'index', 'action'=>'index'
        ));

        $request = $this->getRequest();

        $logger = Zend_Registry::get('Zend_Log');

        $this->_processParameters();

        $sort_order = $request->getParam('sort_order');
        $this->view->sort_order = $sort_order;
        $sort_reverse = $request->getParam('sort_reverse', '0');

        $button = $request->getParam('buttonSubmit');

        if (isset($button)) {
            if (count($this->view->selected) > 0) {
                $this->_forward('clear');
            }
            else {
                $this->view->error = $this->view->translate('review_error_noselection');
            }
        }

        $button = $request->getParam('buttonUp');

        if (isset($button)) {
            $sort_reverse = '0';
        }

        $button = $request->getParam('buttonDown');

        if (isset($button)) {
            $sort_reverse = '1';
        }
        
        $button = $request->getParam('buttonSelectAll');
        if (isset($button)) {
            $this->view->selectAll = true;
        }
        else {
            $this->view->selectAll = false;
        }

        $button = $request->getParam('buttonSelectNone');
        if (isset($button)) {
            $this->view->selectNone = true;
        }
        else {
            $this->view->selectNone = false;
        }

        $this->_prepareSortOptions();

        $result = $this->_getResult($sort_order, $sort_reverse);

        $this->view->documentCount = count($result);

        $paginator = Zend_Paginator::factory($result);
        $currentPage = $this->_getParam('page');
        $currentPage = ($currentPage) ? $currentPage : 1;
        $this->view->currentPage = $currentPage;
        $paginator->setCurrentPageNumber($currentPage);
        $paginator->setItemCountPerPage(10);

        $this->view->paginator = $paginator;
    }

    /**
     * Action for showing the clear form and processing POST from it.
     */
    public function clearAction() {
        $this->view->title = $this->view->translate('review_index_title');

        $this->view->actionUrl = $this->view->url(array(
            'module' => 'review', 'controller'=>'index', 'action'=>'clear'
        ));

        $button = $this->getRequest()->getParam('buttonBack');
        if (isset($button)) {
            $this->_forward('index');
            return;
        }

        $this->_processParameters();

        $this->view->documentCount = count($this->view->selected);

        $firstName = $this->getRequest()->getParam('firstname');
        $this->view->firstName = $firstName;

        $lastName = $this->getRequest()->getParam('lastname');
        $this->view->lastName = $lastName;

        $button = $this->getRequest()->getParam('buttonClear');
        if (isset($button)) {
            if (!Zend_Validate::is($firstName, 'NotEmpty')) {
                $this->view->error = $this->view->translate('review_error_input_missing');
            }

            if (!Zend_Validate::is($lastName, 'NotEmpty')) {
                $this->view->error = $this->view->translate('review_error_input_missing');
            }

            if (empty($this->view->error)) {
                $this->_clearDocuments($this->view->selected, $lastName, $firstName);
                $this->_redirectTo('', 'index', 'index', 'review');
            }
        }
    }

    /**
     * Action for showing success message after clearing documents.
     *
     * TODO implement and use
     */
    public function successAction() {
        $this->view->title = $this->view->translate('review_index_title');
    }

    /**
     * Processes form input, especially selected documents.
     */
    protected function _processParameters() {
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
     * Returns documents from database for browsing.
     *
     * @param <type> $state
     * @param <type> $sort_order
     * @return <type>
     *
     * TODO following could be handled inside a application model
     */
    protected function _getResult($sort_order, $sort_reverse) {
        $result = null;

        $method = 'getAllDocumentsBy';

        switch ($sort_order) {
            case 'author':
                $method = $method . 'Authors';
                break;
            case 'publicationDate':
                $method = $method . 'PubDate';
                break;
            case 'docType':
                $method = $method . 'Doctype';
                break;
            case 'title':
                $method = $method . 'Titles';
                break;
            default:
                $method = 'getAllIds';
        }

        $method = $method . 'ByState';
        $result = Opus_Document::$method('unpublished', $sort_reverse);

        return $result;
    }

    /**
     * Publishes documents and adds referee.
     *
     * @param array $docIds
     * @param string $lastName
     * @param string $firstName
     *
     * FIXME add referee
     * FIXME capture success or failure for display afterwards
     */
    protected function _clearDocuments($docIds, $lastName, $firstName) {
        $logger = Zend_Registry::get('Zend_Log');

        $logger->debug('Clearing documents.');

        foreach ($docIds as $index => $docId) {
            $document = new Opus_Document( (int) $docId);

            try {
                $state = $document->getServerState();

                if ($state === 'unpublished') {
                    $logger->debug('Change state to \'published\' for document:' . $docId);
                    $document->setServerState('published');

                    $person = new Opus_Person();
                    $person->setFirstName($firstName);
                    $person->setLastName($lastName);
                    $document->addPersonReferee($person);
                    $document->store();
                }
                else {
                    // already published or deleted
                    $logger->warn('Document ' . $docId . ' already published.');
                }
            }
            catch (Exception $e) {
                $logger->err($e);
            }

        }

    }

}

