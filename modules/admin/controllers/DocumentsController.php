<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @package     Module_Admin
 * @author      Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Administrative work with document metadata.
 */
class Admin_DocumentsController extends Controller_CRUDAction {

    /**
     * The class of the model being administrated.
     *
     * @var Opus_Model_Abstract
     */
    protected $_modelclass = 'Opus_Document';

    protected $sortingOptions = array('id', 'title', 'author',
        'publicationDate', 'docType');

    protected $docOptions = array(/* 'all', */ 'published', 'unpublished', 'deleted');

    /**
     * Returns a filtered representation of the document.
     *
     * @param  Opus_Document  $document The document to be filtered.
     * @return Opus_Model_Filter The filtered document.
     */
    private function __createFilter(Opus_Document $document, $page = null) {
        $filter = new Opus_Model_Filter();
        $filter->setModel($document);
        $blacklist = array('Collection', 'IdentifierOpus3', 'Source', 'File', 'ServerState', 'ServerDatePublished', 'ServerDateModified', 'ServerDateUnlocking', 'Type');
        $filter->setBlacklist($blacklist);
        // $filter->setSortOrder($type->getAdminFormSortOrder());
        return $filter;
    }

    /**
     * Display documents (all or filtered by state)
     *
     * @return void
     */
    public function indexAction() {
    	$this->view->title = $this->view->translate('admin_documents_index');

        $this->_prepareDocStateLinks();

        $url_call_id = array(
            'module' => 'admin',
            'controller' => 'documents',
            'action' => 'edit'
        );
        $this->view->url_call_id = $this->view->url($url_call_id, 'default', true);

        $this->_prepareSortingLinks();

        $data = $this->_request->getParams();
        $filter = $this->_getParam("filter");
        $this->view->filter = $filter;
        $data = $this->_request->getParams();

        $page = 1;
        if (array_key_exists('page', $data)) {
            // set page if requested
            $page = $data['page'];
        }

        // Default Ordering...
        $sort_reverse = '0';
        if (true === array_key_exists('sort_reverse', $data)) {
           $sort_reverse = $data['sort_reverse'];
        }
        $this->view->sort_reverse = $sort_reverse;
        $this->view->sortDirection = ($sort_reverse) ? 'descending' : 'ascending';

        $config = Zend_Registry::get('Zend_Config');

                $state = 'unpublished';
        if (true === array_key_exists('state', $data)) {
            $state = $data['state'];
        }
        else if (isset($config->admin->documents->defaultview)) {
            $state = $config->admin->documents->defaultview;
        }
        
        if (!empty($state) && !in_array($state, $this->docOptions)) {
            $state = 'unpublished';
        }

        $this->view->state = $state;

        $sort_order = 'id';
        if (true === array_key_exists('sort_order', $data)) {
            $sort_order = $data['sort_order'];
        }
        
        $this->view->sort_order = $sort_order;

        $result = $this->_helper->documents($sort_order, $sort_reverse, $state);

        $paginator = Zend_Paginator::factory($result);
        if (array_key_exists('hitsPerPage', $data)) {
        	if ($data['hitsPerPage'] === '0') {
        	    $hitsPerPage = '10000';
        	}
            else {
            	$hitsPerPage = $data['hitsPerPage'];
            }
            $paginator->setItemCountPerPage($hitsPerPage);
        }
        $page = 1;
        if (array_key_exists('page', $data)) {
            // paginator
            $page = $data['page'];
        }
        $paginator->setCurrentPageNumber($page);
        $this->view->paginator = $paginator;
    }

    protected function _prepareDocStateLinks() {
        $registers = array();

        foreach ($this->docOptions as $name) {
            $params = array('module' => 'admin', 'controller'=>'documents', 'action'=>'index');
            if ($name !== 'all') {
                $params['state'] = $name;
            }
            $url = $this->view->url($params, null, true);
            $registers[$name] = $url;
        }

        $this->view->registers = $registers;
    }

    protected function _prepareSortingLinks() {
        $sortingLinks = array();

        foreach ($this->sortingOptions as $name) {
            $params = array(
                'module' => 'admin',
                'controller' => 'documents',
                'action' => 'index',
                'sort_order' => $name
            );
            $sortUrl = $this->view->url($params, 'default', false);
            $sortingLinks[$name] = $sortUrl;
        }

        $this->view->sortingLinks = $sortingLinks;

        $directionLinks = array();

        $directionLinks['ascending'] = $this->view->url(array('sort_reverse' => '0'), 'default', false);
        $directionLinks['descending'] = $this->view->url(array('sort_reverse' => '1'), 'default', false);

        $this->view->directionLinks = $directionLinks;
    }

    public function showAction() {
        $model = parent::showAction();
        if (!empty($model)) {
            $this->view->docHelper = new Review_Model_DocumentAdapter($this->view, $model);
        }
    }

    /**
     * Edits a model instance
     *
     * @return void
     */
    public function editAction() {
        // get parameters
        $id = $this->getRequest()->getParam('id');
        if (empty($id) or !is_numeric($id)) {
            $this->_helper->redirector('index');
        }

        $this->view->title = $this->view->translate('admin_documents_edit', $id);

        $form_builder = new Form_Builder();
        $document = new $this->_modelclass($id);

        $documentInSession = new Zend_Session_Namespace('document');
        $documentInSession->document = $document;

        $this->view->showFilemanager = $document->hasField('File');
        $documentWithFilter = $this->__createFilter($document);

        $modelForm = $form_builder->build($documentWithFilter);

        $action_url = $this->view->url(array("action" => "create"));
        $modelForm->setAction($action_url);
        $this->view->form = $modelForm;
        $this->view->docId = $id;
        $assignedCollections = array();
        foreach ($document->getCollection() as $assignedCollection) {
            $assignedCollections[] = array('collectionName' => $assignedCollection->getDisplayName(), 'collectionId' => $assignedCollection->getId(), 'roleName' => $assignedCollection->getRole()->getName(), 'roleId' => $assignedCollection->getRole()->getId());
        }
        $this->view->assignedCollections = $assignedCollections;
        $this->view->docHelper = new Review_Model_DocumentAdapter($this->view, $document);

    }

    /**
     * Deletes a document (sets state to deleted)
     *
     * @return void
     */
    public function deleteAction() {
        if ($this->_request->isPost() !== true && is_null($this->getRequest()->getParam('docId'))) {
            $this->_redirectTo('index');
        }

        $id = $this->getRequest()->getParam('docId');
        if ($id === null) {
            $id = $this->getRequest()->getPost('id');
        }
        $sureyes = $this->getRequest()->getPost('sureyes');
        $sureno = $this->getRequest()->getPost('sureno');
        if (isset($sureyes) === true or isset($sureno) === true) {
            // Safety question answered, deleting
            if (isset($sureyes) === true) {
                $model = new $this->_modelclass($id);
                $model->delete();
                $this->_redirectTo('index', 'Model successfully deleted.');
            }
            else {
                $this->_redirectTo('index');
            }
        }
        else {
            // show safety question
            $this->view->title = $this->view->translate('admin_doc_delete');
            $this->view->text = $this->view->translate('admin_doc_delete_sure', $id);
            $yesnoForm = $this->_getConfirmationForm($id, 'delete');
            $this->view->form = $yesnoForm;
        }
    }

    /**
     * Deletes a document permanently (removes it from database and disk)
     *
     * @return void
     */
    public function permanentdeleteAction() {
        if ($this->_request->isPost() === true || $this->getRequest()->getParam('docId') !== null) {
            $id = null;
            $id = $this->getRequest()->getParam('docId');
            if ($id === null) {
                $id = $this->getRequest()->getPost('id');
            }
            $sureyes = $this->getRequest()->getPost('sureyes');
            $sureno = $this->getRequest()->getPost('sureno');
            if (isset($sureyes) === true or isset($sureno) === true) {
            	// Safety question answered, deleting
            	if (isset($sureyes) === true) {
                    $model = new $this->_modelclass($id);
                    try {
                    	$model->deletePermanent();
                    }
                    catch (Exception $e) {
                    	$this->_redirectTo('index', $e->getMessage());
                    }
                    $this->_redirectTo('index', 'Model successfully deleted.');
            	}
            	else {
                    $this->_redirectTo('index');
            	}
            }
            else {
                // show safety question
                $this->view->title = $this->view->translate('admin_doc_delete_permanent');
                $this->view->text = $this->view->translate('admin_doc_delete_permanent_sure', $id);
                $yesnoForm = $this->_getConfirmationForm($id, 'permanentdelete');
                $this->view->form = $yesnoForm;
            }
        } else {
            $this->_redirectTo('index');
        }
    }

    protected function _getConfirmationForm($id, $action) {
        $yesnoForm = new Admin_Form_YesNoForm();
        $idElement = new Zend_Form_Element_Hidden('id');
        $idElement->setValue($id);
        $yesnoForm->addElement($idElement);
        $yesnoForm->setAction($this->view->url(array("controller"=>"documents", "action"=>$action)));
        $yesnoForm->setMethod('post');
        return $yesnoForm;
    }

    /**
     * Save model instance
     *
     * @return void
     */
    public function createAction() {
        if ($this->_request->isPost() === true) {
            $data = $this->_request->getPost();
            $form_builder = new Form_Builder();
            $id = $this->getRequest()->getParam('id');
            $documentInSession = new Zend_Session_Namespace('document');
            $document = $documentInSession->document;
            $form_builder->buildModelFromPostData($document, $data['Opus_Model_Filter']);
            $form = $form_builder->build($this->__createFilter($document));
            if (array_key_exists('submit', $data) === false) {
                $action_url = $this->view->url(array("action" => "create"));
                $form->setAction($action_url);
                $this->view->form = $form;
            }
            else {
                try {
                    if ($form->isValid($data) === true) {
                        // store document
                        $document->store();

                        // reindex
                        $indexer = new Opus_Search_Index_Solr_Indexer();
                        $indexer->removeDocumentFromEntryIndex($document);
                        $indexer->addDocumentToEntryIndex($document);

                        // The first 3 params are module, controller and action.
                        // Additional parameters are passed through.
                        $params = $this->getRequest()->getUserParams();
                        $module = array_shift($params);
                        $controller = array_shift($params);
                        $action = array_shift($params);
                        $this->_redirectTo('edit', '', $controller, $module, $params);
                    }
                    else {
                        $this->view->form = $form;
                    }
                }
                catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
            $this->view->title = $this->view->translate('admin_documents_edit', $id);
            $this->view->docHelper = new Review_Model_DocumentAdapter($this->view, $document);
        }
        else {
            $this->_redirectTo('edit');
        }
    }

    /**
     * Publishes a document
     *
     * @return void
     */
    public function publishAction() {
        if (($this->_request->isPost() === false) && ($this->getRequest()->getParam('docId') === null)) {
            $this->_redirect('index');
        }

        $id = null;
        $id = $this->getRequest()->getParam('docId');
        if ($id === null) {
            $id = $this->getRequest()->getPost('id');
        }
        $sureyes = $this->getRequest()->getPost('sureyes');
        $sureno = $this->getRequest()->getPost('sureno');

        if (isset($sureyes) === true) {
            // publish document
            $doc = new Opus_Document($id);
            if (false === is_null($doc->getField('ServerDateUnlocking')) and $doc->getServerDateUnlocking() > date('Y-m-d')) {
                $this->_redirectTo('index', 'publish_unlocking_date_not_reached');
            }
            $doc->setServerState('published');
            //        $doc->setServerDatePublished(date('Y-m-d'));
            //        $doc->setServerDatePublished(date('c'));
            $date = new Zend_Date();
            $doc->setServerDatePublished($date->get('yyyy-MM-ddThh:mm:ss') . 'Z');
            $doc->store();

            $message = $this->view->translate('document_published', $id);
            $this->_redirectTo('show', $message, 'documents', 'admin',
                    array('id' => $id));
        }
        else if (isset($sureno) === true) {
            $message = null;
            $this->_redirectTo('show', $message, 'documents', 'admin',
                    array('id' => $id));
        }
        else {
            // show safety question
            $this->view->title = $this->view->translate('admin_doc_publish');
            $this->view->text = $this->view->translate('admin_doc_publish_sure', $id);
            $yesnoForm = $this->_getConfirmationForm($id, 'publish');
            $this->view->form = $yesnoForm;
        }
    }

    /**
     * Unpublishes a document
     *
     * @return void
     */
    public function unpublishAction() {
        if (($this->_request->isPost() === false) && ($this->getRequest()->getParam('docId') === null)) {
            $this->_redirect('index');
        }

        $id = null;
        $id = $this->getRequest()->getParam('docId');
        if ($id === null) {
            $id = $this->getRequest()->getPost('id');
        }
        $sureyes = $this->getRequest()->getPost('sureyes');
        $sureno = $this->getRequest()->getPost('sureno');

        if (isset($sureyes) === true) {
            $doc = new Opus_Document($id);
            $doc->setServerState('unpublished');
            $doc->store();

            $message = $this->view->translate('document_unpublished', $id);
            $this->_redirectTo('show', $message, 'documents', 'admin',
                    array('id' => $id));
        }
        else if (isset($sureno) === true) {
            $message = null;
            $this->_redirectTo('show', $message, 'documents', 'admin',
                    array('id' => $id));
        }
        else {
            // show safety question
            $this->view->title = $this->view->translate('admin_doc_unpublish');
            $this->view->text = $this->view->translate('admin_doc_unpublish_sure', $id);
            $yesnoForm = $this->_getConfirmationForm($id, 'unpublish');
            $this->view->form = $yesnoForm;
        }

    }

    /**
     * Removes a document from a collection.
     *
     * @return void
     */
    public function unlinkcollectionAction() {
        if (true === $this->_request->isPost()) {
            $document_id = $this->getRequest()->getParam('id');            
            $collection_id = $this->getRequest()->getParam('collection');            
            $document = new Opus_Document($document_id);
            $collections = array();
            $deletedCollectionName = null;
            foreach ($document->getCollection() as $collection) {
                if ($collection->getId() !== $collection_id) {
                    array_push($collections, $collection);
                }
                else {
                    $deletedCollectionName = $collection->getDisplayName();
                }
            }
            $document->setCollection($collections);
            $document->store();
            $params = $this->getRequest()->getUserParams();
            $module = array_shift($params);
            $controller = array_shift($params);
            $action = array_shift($params);
            $this->_redirectTo('edit', 'collection \'' . $deletedCollectionName . '\' was removed successfully', $controller, $module, $params);
        }
        else {
            $this->_redirectTo('index');
        }
    }
    
}