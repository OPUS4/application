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

    /**
     * Returns a filtered representation of the document.
     *
     * @param  Opus_Document  $document The document to be filtered.
     * @return Opus_Model_Filter The filtered document.
     */
    private function __createFilter(Opus_Document $document, $page = null) {
        $filter = new Opus_Model_Filter();
        $filter->setModel($document);
        $type = new Opus_Document_Type($document->getType());
        $pages = $type->getPages();
        $alwayshidden = array('Collection', 'IdentifierOpus3', 'Source', 'File', 'ServerState', 'ServerDatePublished', 'ServerDateModified', 'ServerDateUnlocking');
        $blacklist = array_merge($alwayshidden, $type->getAdminFormBlackList());
        if (false === is_null($page) and true === array_key_exists($page, $pages)) {
            $filter->setWhitelist(array_diff($pages[$page]['fields'], $blacklist));
        } else {
            $filter->setBlacklist($blacklist);
        }
        $filter->setSortOrder($type->getAdminFormSortOrder());
        return $filter;
    }

    /**
     * Display documents (all or filtered by state)
     *
     * @return void
     */
    public function indexAction() {
        $this->view->registers = array(
            array(
                $this->view->url(array('module' => 'admin', 'controller'=>'documents', 'action'=>'index'), null, true), 'docs_all'
            ),
            array(
                $this->view->url(array('module' => 'admin', 'controller'=>'documents', 'action'=>'index', 'state' => 'published'), null, true), 'docs_published'
            ),
            array(
                $this->view->url(array('module' => 'admin', 'controller'=>'documents', 'action'=>'index', 'state' => 'unpublished'), null, true), 'docs_unpublished'
            ),
            array(
                $this->view->url(array('module' => 'admin', 'controller'=>'documents', 'action'=>'index', 'state' => 'deleted'), null, true), 'docs_deleted'
            )
            );
        $url_call_id = array(
            'module' => 'admin',
            'controller' => 'documents',
            'action' => 'edit'
        );
        $this->view->url_call_id = $this->view->url($url_call_id, 'default', true);
        $url_sort_by_id = array(
            'module' => 'admin',
            'controller' => 'documents',
            'action' => 'index',
            'sort_order' => 'id'
        );
        $url_sort_by_title = array(
            'module' => 'admin',
            'controller' => 'documents',
            'action' => 'index',
            'sort_order' => 'title'
        );
        $url_sort_by_author = array(
            'module' => 'admin',
            'controller' => 'documents',
            'action' => 'index',
            'sort_order' => 'author'
        );
        $url_sort_by_date = array(
            'module' => 'admin',
            'controller' => 'documents',
            'action' => 'index',
            'sort_order' => 'publicationDate'
        );
        $url_sort_by_doctype = array(
            'module' => 'admin',
            'controller' => 'documents',
            'action' => 'index',
            'sort_order' => 'docType'
        );
        $url_sort_asc = array(
            'sort_reverse' => '0'
        );
        $url_sort_desc = array(
            'sort_reverse' => '1'
        );
        $this->view->url_sort_by_id = $this->view->url($url_sort_by_id, 'default', false);
        $this->view->url_sort_by_title = $this->view->url($url_sort_by_title, 'default', false);
        $this->view->url_sort_by_author = $this->view->url($url_sort_by_author, 'default', false);
        $this->view->url_sort_by_date = $this->view->url($url_sort_by_date, 'default', false);
        $this->view->url_sort_by_doctype = $this->view->url($url_sort_by_doctype, 'default', false);
        $this->view->url_sort_asc = $this->view->url($url_sort_asc, 'default', false);
        $this->view->url_sort_desc = $this->view->url($url_sort_desc, 'default', false);

        $data = $this->_request->getParams();
        $filter = $this->_getParam("filter");
        $this->view->filter = $filter;
        $data = $this->_request->getParams();

        $page = 1;
        if (array_key_exists('page', $data)) {
            // set page if requested
            $page = $data['page'];
        }
        $this->view->title = $this->view->translate('search_index_alltitles_browsing');

        // Default Ordering...
        if (true === array_key_exists('sort_reverse', $data)) {
           $sort_reverse = $data['sort_reverse'];
        }
        else {
           $sort_reverse = '0';
        }
        $this->view->sort_reverse = $sort_reverse;

        if (true === array_key_exists('state', $data)) {
        	$this->view->state = $data['state'];
        }
        // following could be handled inside a application model
        if (true === array_key_exists('sort_order', $data)) {
        	$this->view->sort_order = $data['sort_order'];
        	switch ($data['sort_order']) {
        		case 'author':
        	    	if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentsByAuthorsByState($data['state'], $sort_reverse);
                    } else {
                        $result = Opus_Document::getAllDocumentsByAuthors($sort_reverse);
                    }
        		    break;
        		case 'publicationDate':
        	    	if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentsByPubDateByState($data['state'], $sort_reverse);
                    } else {
                        $result = Opus_Document::getAllDocumentsByPubDate($sort_reverse);
                    }
        		    break;
         		case 'docType':
        	    	if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentsByDoctypeByState($data['state'], $sort_reverse);
                    } else {
                        $result = Opus_Document::getAllDocumentsByDoctype($sort_reverse);
                    }
        		    break;
        		case 'title':
        		    if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentsByTitlesByState($data['state'], $sort_reverse);
                    } else {
                        $result = Opus_Document::getAllDocumentsByTitles($sort_reverse);
                    }
                    break;
        		default:
                	if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllIdsByState($data['state'], $sort_reverse);
                    } else {
                        $result = Opus_Document::getAllIds($sort_reverse);
                    }
        	}
        }
        else {
        	if (true === array_key_exists('state', $data)) {
                $result = Opus_Document::getAllIdsByState($data['state'], $sort_reverse);
            } else {
                $result = Opus_Document::getAllIds($sort_reverse);
            }
        }

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
        $this->view->title = array();
        $this->view->author = array();
        $this->view->url_frontdoor = array();
        $this->view->url_permanentdelete = array();
        $this->view->url_delete = array();
        $this->view->url_edit = array();
        $this->view->url_author = array();
        foreach ($paginator as $id) {
            $url_frontdoor = array(
                'module' => 'frontdoor',
                'controller' => 'index',
                'action' => 'index',
                'docId' => $id
            );
            $url_edit = array(
                'module' => 'admin',
                'controller' => 'documents',
                'action' => 'edit',
                'id' => $id
            );
            $url_delete = array (
                'module' => 'admin',
                'controller' => 'documents',
                'action' => 'delete',
            );
            $url_permadelete = array (
                'module' => 'admin',
                'controller' => 'documents',
                'action' => 'permanentdelete',
            );
            $this->view->url_edit[$runningIndex] = $this->view->url($url_edit, 'default', true);
            $this->view->url_delete[$runningIndex] = $this->view->url($url_delete, 'default', true);
            $this->view->url_permanentdelete[$runningIndex] = $this->view->url($url_permadelete, 'default', true);
            $this->view->url_frontdoor[$runningIndex] = $this->view->url($url_frontdoor, 'default', true);

            $d = new Opus_Document( (int) $id);
            $this->view->docId[$runningIndex] = $id;
            try {
                $this->view->docState = $d->getServerState();
            }
            catch (Exception $e) {
                $this->view->docState = 'undefined';
            }

            $c = count($d->getPersonAuthor());
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
                $this->view->title[$runningIndex] = $d->getTitleMain(0)->getValue();
            }
            catch (Exception $e) {
            	$this->view->title[$runningIndex] = $this->view->translate('document_no_title') . $id;
            }
            $runningIndex++;
        }
    }

    /**
     * Edits a model instance
     *
     * @return void
     */
    public function editAction() {
        $id = $this->getRequest()->getParam('id');
        $this->view->title = $this->view->translate('admin_documents_edit');
        $form_builder = new Form_Builder();
        $document = new $this->_modelclass($id);
        $documentInSession = new Zend_Session_Namespace('document');
        $documentInSession->document = $document;
        if ($document->getServerState() === 'unpublished') {
            $this->view->actions = 'publish';
        }
        else if ($document->getServerState() === 'published') {
            $this->view->actions = 'unpublish';
        }
        if ($document->getServerState() === 'deleted') {
            $this->view->actions = 'undelete';
        }
        $type = new Opus_Document_Type($document->getType());
        $documentWithFilter = new Opus_Model_Filter;
        $documentWithFilter->setModel($document)
            ->setBlacklist(array_merge(array('Collection', 'IdentifierOpus3', 'Source', 'File', 'ServerState', 'ServerDatePublished', 'ServerDateModified', 'ServerDateUnlocking', 'Type', 'Workflow'), $type->getAdminFormBlackList()))
            ->setSortOrder($type->getAdminFormSortOrder());
        $modelForm = $form_builder->build($documentWithFilter);
        $action_url = $this->view->url(array("action" => "create"));
        $modelForm->setAction($action_url);
        $this->view->form = $modelForm;
        $this->view->docId = $id;
        $assignedCollections = array();
        foreach ($document->getCollection() as $assignedCollection) {
            $assignedCollections[] = array('collectionName' => $assignedCollection->getDisplayName(), 'collectionId' => $assignedCollection->getId(), 'roleName' => $assignedCollection->getRoleName(), 'roleId' => $assignedCollection->getRoleId());
        }
        $this->view->assignedCollections = $assignedCollections;
    }

    /**
     * Deletes a document (sets state to deleted)
     *
     * @return void
     */
    public function deleteAction() {
        if ($this->_request->isPost() === true || $this->getRequest()->getParam('docId') !== null) {
        	$id = null;
        	$id = $this->getRequest()->getParam('docId');
            if ($id === null) $id = $this->getRequest()->getPost('id');
            $sureyes = $this->getRequest()->getPost('sureyes');
            $sureno = $this->getRequest()->getPost('sureno');
            if (isset($sureyes) === true or isset($sureno) === true) {
            	// Safety question answered, deleting
            	if (isset($sureyes) === true) {
                    $model = new $this->_modelclass($id);
                    $model->delete();
                    $this->_redirectTo('Model successfully deleted.', 'index');
            	}
            	else {
            		$this->_redirectTo('', 'index');
            	}
            }
            else {
                // show safety question
                $this->view->title = $this->view->translate('admin_doc_delete');
                $this->view->text = $this->view->translate('admin_doc_delete_sure');
                $yesnoForm = new YesNoForm();
                $idElement = new Zend_Form_Element_Hidden('id');
                $idElement->setValue($id);
                $yesnoForm->addElement($idElement);
                $yesnoForm->setAction($this->view->url(array("controller"=>"documents", "action"=>"delete")));
                $yesnoForm->setMethod('post');
                $this->view->form = $yesnoForm;
            }
        } else {
            $this->_redirectTo('', 'index');
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
            if ($id === null) $id = $this->getRequest()->getPost('id');
            $sureyes = $this->getRequest()->getPost('sureyes');
            $sureno = $this->getRequest()->getPost('sureno');
            if (isset($sureyes) === true or isset($sureno) === true) {
            	// Safety question answered, deleting
            	if (isset($sureyes) === true) {
                    $model = new $this->_modelclass($id);
                    $model->deletePermanent();
                    $this->_redirectTo('Model successfully deleted.', 'index');
            	}
            	else {
            		$this->_redirectTo('', 'index');
            	}
            }
            else {
                // show safety question
                $this->view->title = $this->view->translate('admin_doc_delete_permanent');
                $this->view->text = $this->view->translate('admin_doc_delete_permanent_sure');
                $yesnoForm = new YesNoForm();
                $idElement = new Zend_Form_Element_Hidden('id');
                $idElement->setValue($id);
                $yesnoForm->addElement($idElement);
                $yesnoForm->setAction($this->view->url(array("controller"=>"documents", "action"=>"permanentdelete")));
                $yesnoForm->setMethod('post');
                $this->view->form = $yesnoForm;
            }
        } else {
            $this->_redirectTo('', 'index');
        }
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
            } else {
                try{
                    if ($form->isValid($data) === true) {
                        // store document
                        $document->store();

                		$config = Zend_Registry::get('Zend_Config');

		                $searchEngine = $config->searchengine->engine;
		                if (empty($searchEngine) === true) {
			                $searchEngine = 'Lucene';
		                }
                        // Reindex
                        $engineclass = 'Opus_Search_Index_'.$searchEngine.'_Indexer';
                        $indexer = new $engineclass();
                        $indexer->removeDocumentFromEntryIndex($document);
                        $indexer->addDocumentToEntryIndex($document);

                        // The first 3 params are module, controller and action.
                        // Additional parameters are passed through.
                        $params = $this->getRequest()->getUserParams();
                        $module = array_shift($params);
                        $controller = array_shift($params);
                        $action = array_shift($params);
                        $this->_redirectTo('', 'edit', $controller, $module, $params);
                    } else {
                        $this->view->form = $form;
                    }
                }
                catch (Exception $e) {
                	echo $e->getMessage();
                }
            }
        } else {
            $this->_redirectTo('', 'edit');
        }
    }

    /**
     * Publishes a document
     *
     * @return void
     */
    public function publishAction() {
        $id = $this->getRequest()->getParam('docId');
        $doc = new Opus_Document($id);
        if (false === is_null($doc->getField('ServerDateUnlocking')) and $doc->getServerDateUnlocking() > date('Y-m-d')) {
        	$this->_redirectTo('publish_unlocking_date_not_reached', 'index');
        }
        $doc->setServerState('published');
//        $doc->setServerDatePublished(date('Y-m-d'));
        $doc->setServerDatePublished(date('c'));
        $doc->store();

   		$config = Zend_Registry::get('Zend_Config');

        $searchEngine = $config->searchengine->engine;
        if (empty($searchEngine) === true) {
            $searchEngine = 'Lucene';
	    }
        // Add to index
        $engineclass = 'Opus_Search_Index_'.$searchEngine.'_Indexer';
        $indexer = new $engineclass();
        $indexer->addDocumentToEntryIndex($doc);

        $this->_redirectTo('document_published', 'index');
    }

    /**
     * Unpublishes a document
     *
     * @return void
     */
    public function unpublishAction() {
        $id = $this->getRequest()->getParam('docId');
        $doc = new Opus_Document($id);
        $doc->setServerState('unpublished');
        $doc->store();

   		$config = Zend_Registry::get('Zend_Config');

        $searchEngine = $config->searchengine->engine;
        if (empty($searchEngine) === true) {
            $searchEngine = 'Lucene';
	    }
        // Add to index
        $engineclass = 'Opus_Search_Index_'.$searchEngine.'_Indexer';
        $indexer = new $engineclass();
        $indexer->removeDocumentFromEntryIndex($doc);

        $this->_redirectTo('', 'index');
    }

    /**
     * Removes a document from a collection.
     *
     * @return void
     */
    public function unlinkcollectionAction() {
        if (true === $this->_request->isPost()) {
            $document_id = $this->getRequest()->getParam('id');
            $role_id = $this->getRequest()->getParam('role');
            $collection_id = $this->getRequest()->getParam('collection');
            $collection = new Opus_Collection($collection_id, $role_id);
            $collection->deleteEntry(new Opus_Document($document_id));
            $params = $this->getRequest()->getUserParams();
            $module = array_shift($params);
            $controller = array_shift($params);
            $action = array_shift($params);
            $this->_redirectTo('', 'edit', $controller, $module, $params);
        } else {
            $this->_redirectTo('', 'index');
        }
    }
}
