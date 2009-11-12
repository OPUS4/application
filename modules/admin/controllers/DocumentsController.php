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
            ),
            array(
                $this->view->url(array('module' => 'admin'), null, true), 'admin_index_title'
            )
            );
        $data = $this->_request->getParams();
        // following could be handled inside a application model
        if (true === array_key_exists('sort_order', $data)) {
        	switch ($data['sort_order']) {
        		case 'author':
        	    	if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentAuthorsByState($data['state']);
                    } else {
                        $result = Opus_Document::getAllDocumentAuthors();
                    }
        		    break;
         		case 'docType':
        	    	if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentsByDoctypeByState($data['state']);
                    } else {
                        $result = Opus_Document::getAllDocumentsByDoctype();
                    }
        		    break;
        		default:
        		    if (true === array_key_exists('state', $data)) {
                        $result = Opus_Document::getAllDocumentTitlesByState($data['state']);
                    } else {
                        $result = Opus_Document::getAllDocumentTitles();
                    }
        	}
        }
        else {
        	if (true === array_key_exists('state', $data)) {
                $result = Opus_Document::getAllDocumentTitlesByState($data['state']);
            } else {
                $result = Opus_Document::getAllDocumentTitles();
            }
        }

        // Sort the result if necessary
        // docList contains a list of IDs of the documents, that should be returned after sorting
        $docList = array();
        if (true === array_key_exists('sort_order', $data)) {
        	switch ($data['sort_order']) {
        		case 'title':
                    asort($result);
                    foreach ($result as $id => $doc) {
        	            $docList[] = $id;
                    }
                    break;
        		case 'docType':
        		    $tmpdocList = array();
                    foreach ($result as $id => $doc) {
                        $docType = $this->view->translate($doc);
                        $tmpdocList[$id] = $docType;
                    }
                    asort($tmpdocList);
                    foreach ($tmpdocList as $id => $doc) {
        	            $docList[] = $id;
                    }
                    break;
        		case 'author':
        		    // Do nothing, the list has been sorted already!
                    foreach ($result as $id => $doc) {
        	            $docList[] = $id;
                    }        		    
        		    break;
        		/*    $tmpdocList = array();
                    foreach ($result as $id => $doc) {
       	                $d = new Opus_Document($id);
                        try {
       	                    $aut = $d->getPersonAuthor();
       	                    if (is_array($aut) === true && isset($aut[0]) === true) {
       	                	    $a = $aut[0];
       		                    $name = '';
       		                    $lastName = '';
       		                    $name = $a->getName();
       		                    $lastName = $a->getLastName();
       		                    if (false === empty($name)) {
       		                        $author = $a->getName();
       		                    }
       		                    else if (false === empty($lastName)) {
           			                $author = $a->getLastName();
           		                }
       	    	                else {
       		    	                $author = " ";
       		                    }
       	                    }
       	                    else {
           	                	$author = ' ';
           	                }
       	                }
       	                catch (Exception $e) {
       	                	$author = ' ';
       	                }
                        $tmpdocList[$id] = $author;
                    }
                    asort($tmpdocList);
                    foreach ($tmpdocList as $id => $doc) {
        	            $docList[] = $id;
                    }
                    break;*/
        		default:
                    foreach ($result as $id => $doc) {
        	            $docList[] = $id;
                    }
        			sort($docList);
        	}
        }
        else {
        	foreach ($result as $id => $doc) {
        	    $docList[] = $id;
            }
        	sort($docList);
        }

        $paginator = Zend_Paginator::factory($docList);
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
        $this->view->documentList = $paginator;
        #$this->view->documentList = $docList;
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
            ->setBlacklist(array_merge(array('IdentifierOpus3', 'Source', 'File', 'ServerState', 'ServerDatePublished', 'ServerDateModified', 'ServerDateUnlocking'), $type->getAdminFormBlackList()))
            ->setSortOrder($type->getAdminFormSortOrder());
        $modelForm = $form_builder->build($documentWithFilter);
        $action_url = $this->view->url(array("action" => "create"));
        $modelForm->setAction($action_url);
        $this->view->form = $modelForm;
        $this->view->docId = $id;
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
            if (array_key_exists('submit', $data) === false) {
                $form = $form_builder->buildFromPost($data);
                $action_url = $this->view->url(array("action" => "create"));
                $form->setAction($action_url);
                $this->view->form = $form;
            } else {
                $form = $form_builder->buildFromPost($data);
                if ($form->isValid($data) === true) {
                    // retrieve values from form and save them into model
                    $model = $form_builder->getModelFromForm($form);
                    $form_builder->setFromPost($model, $form->getValues());
                    $model->store();
            
                    // Reindex
                    $doc = new Opus_Document($this->getRequest()->getParam('id'));
                    $indexer = new Opus_Search_Index_Indexer();
                    $indexer->removeDocumentFromEntryIndex($doc);
                    $indexer->addDocumentToEntryIndex($doc);
                    
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
        $doc->setServerState('published');
        $doc->store();

        // Add to index
        $indexer = new Opus_Search_Index_Indexer();
        $indexer->addDocumentToEntryIndex($doc);

        $this->_redirectTo('', 'index');
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

        // Add to index
        $indexer = new Opus_Search_Index_Indexer();
        $indexer->removeDocumentFromEntryIndex($doc);

        $this->_redirectTo('', 'index');
    }
}
