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
 * @package     Module_Publish
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @author      Pascal-Nicolas Becker <becker@zib.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Publish
 */
class Publish_IndexController extends Zend_Controller_Action {

    /**
     * Redirector - defined for code completion
     *
     * @var Zend_Controller_Action_Helper_Redirector
     */
    protected $_redirector = null;

    /**
     * Do some initialization on startup of every action
     *
     * @return void
     */
    public function init()
    {
        $this->_redirector = $this->_helper->getHelper('Redirector');
    }

    /**
     * Just to be there. No actions taken.
     *
     * @return void
     *
     */
    public function indexAction() {
        $this->view->title = $this->view->translate('publish_controller_index');
        $form = new Overview();
        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
        $form->setAction($action_url);
        $this->view->form = $form;
    }

    /**
     * Show form for key upload and do the key upload
     *
     * @return void
     *
     */
    public function keyuploadAction() {
        $this->view->title = $this->view->translate('publish_controller_keyupload');
        $form = new KeyUpload();
        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
        $form->setAction($action_url);
        $this->view->form = $form;
    }

    /**
     * Create, recreate and validate a document form. If it is valid store it.
     *
     * @return void
     */
    public function createAction() {
        $this->view->title = $this->view->translate('publish_controller_create');

        if ($this->_request->isPost() === true) {
            $data = $this->_request->getPost();
            $form_builder = new Form_Builder();
            $documentInSession = new Zend_Session_Namespace('document');
            if (array_key_exists('selecttype', $data) === true || array_key_exists('gpg_key_upload', $data) === true) {
                // validate document type
                $form = new Overview();
                $alternateForm = new KeyUpload();
                if ($form->isValid($data) === true || $alternateForm->isValid($data) === true) {
                    $possibleDoctypes = Opus_Document_Type::getAvailableTypeNames();
                    $selectedDoctype = $form->getValue('selecttype');
                    if ($selectedDoctype !== $documentInSession->doctype && isset($selectedDoctype) === true) {
                        $documentInSession->doctype = $selectedDoctype;
                    }
                    else {
                        $selectedDoctype = $documentInSession->doctype;
                    }
                    if (in_array($selectedDoctype, $possibleDoctypes) === false) {
                        // TODO: error message
                        // document type does not exists, back to select form
                        $this->_redirector->gotoSimple('index');
                    }
                    
                    if ($alternateForm->isValid($data) === true) {
            	        $gpg = new Opus_GPG();

                        $upload = new Zend_File_Transfer_Adapter_Http();
                        $files = $upload->getFileInfo();

                        // save the file
                        foreach ($files as $file) {
                            $gpg->importKeyFile($file['tmp_name']);
                        }
                    }
                    #else if ($form->isValid($data) === false) {
                    #	$this->_redirector->gotoSimple('index');
                    #}
                    // If author selected that he has a GPG-Key, redirect to Uploadform
                    $gpgkey = $form->getValue('gpgkey');
                    if ($gpgkey === '1') {
                    	$documentInSession->keyupload = true;
                    	$this->_redirector->gotoSimple('keyupload');
                    }
                    $type = new Opus_Document_Type($selectedDoctype);
                    $pages = $type->getPages();
                    $document = new Opus_Document(null, $type);

                    // add standard field filter
                    $documentWithFilter = new Opus_Model_Filter;

                    if (true === array_key_exists(0, $pages)) {
                        $documentWithFilter->setModel($document)
                            ->setWhitelist(array_diff($pages[0]['fields'], $type->getPublishFormBlackList()))
                            ->setSortOrder($type->getPublishFormSortOrder());
                        $caption = $pages[0]['caption'];
                        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create', 'page' => 1));
                    } else {
                        $alwayshidden = array('IdentifierOpus3', 'Type', 'ServerState', 'ServerDateModified', 'ServerDatePublished');
                        $documentWithFilter->setModel($document)
                            ->setBlacklist(array_merge($type->getPublishFormBlackList(), $alwayshidden))
                            ->setSortOrder($type->getPublishFormSortOrder());
                        $caption = 'publish_index_create_' . $type->getName();
                        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
                    }

                    $createForm = $form_builder->build($documentWithFilter);
                    $createForm->setAction($action_url);
                    $createForm->setDescription($this->view->translate($caption));
                    $createForm->setDecorators(array('FormElements', array('Description', array('placement' => 'prepend','tag' => 'h2')), 'Form'));
                    $this->view->form = $createForm;
                } else {
                    // submitted form data is not valid, back to select form
                    $this->view->form = $form;
                }
            } else if (array_key_exists('submit', $data) === false) {
                $form = $form_builder->buildFromPost($data);
                $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
                $form->setAction($action_url);
                $this->view->form = $form;
            } else {
                $form = $form_builder->buildFromPost($data);
                if ($form->isValid($data) === true) {
                    // retrieve old version from model
                    $model = $form_builder->getModelFromForm($form);
                    // overwrite old data in the model with the new data from the form
                    $form_builder->setFromPost($model, $form->getValues());

                    // Get the document from the filter, use type to get paging configuration.
                    $document = $model->getModel();
                    $type = new Opus_Document_Type($document->getType());
                    $pages = $type->getPages();
                    $requested_page = $this->_request->getParam('page');

                    if (array_key_exists($requested_page, $pages)) {
                        // Handle remaining multi-page form steps.
                        $documentWithFilter = new Opus_Model_Filter;
                        $documentWithFilter->setModel($document)
                            ->setWhitelist(array_diff($pages[$requested_page]['fields'], $type->getPublishFormBlackList()))
                            ->setSortOrder($type->getPublishFormSortOrder());
                        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create', 'page' => $requested_page + 1));
                        $createForm = $form_builder->build($documentWithFilter);
                        $createForm->setAction($action_url);
                        $createForm->setDescription($this->view->translate($pages[$requested_page]['caption']));
                        $createForm->setDecorators(array('FormElements', array('Description', array('placement' => 'prepend','tag' => 'h2')), 'Form'));
                        $this->view->form = $createForm;
                    } else {
                        // go ahead to summary
                        $this->view->document_data = $document->toArray();
                        $this->view->title = $this->view->translate('publish_controller_summary');
                        $summaryForm = new Summary();
                        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'summary'));
                        $summaryForm->setAction($action_url);
                        $model_ser = $form_builder->compressModel($document);
                        $model_hidden = Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;
                        $summaryForm->$model_hidden->setValue($model_ser);
                        $this->view->form = $summaryForm;
                    }
                } else {
                    $this->view->form = $form;
                }
            }
        } else {
            // action used directly go back to main index
            $this->_redirector->gotoSimple('index');
        }
    }

    public function summaryAction() {
        $this->view->title = $this->view->translate('publish_controller_summary');
        if ($this->_request->isPost() === true) {
            $summaryForm = new Summary();
            $postdata = $this->_request->getPost();
            if ($summaryForm->isValid($postdata) === true) {
                $form_builder = new Form_Builder();
                $model_hidden = Form_Builder::HIDDEN_MODEL_ELEMENT_NAME;
                $document = $form_builder->uncompressModel($postdata[$model_hidden]);
                if (array_key_exists('submit', $postdata) === true) {
                    $id = $document->store();
                    $this->view->title = $this->view->translate('publish_controller_upload');
                    $uploadForm = new FileUpload();
                    $action_url = $this->view->url(array('controller' => 'index', 'action' => 'upload'));
                    $uploadForm->setAction($action_url);
                    // TODO: Security save id to session not to form
                    // Actually it is possible to add Files to every document for everybody!
                    $uploadForm->DocumentId->setValue($id);
                    $this->view->form = $uploadForm;
                } else if (array_key_exists('back', $postdata) === true) {
                    $documentWithFilter = new Opus_Model_Filter;
                    $type = new Opus_Document_Type($document->getType());
                    $alwayshidden = array('Type', 'ServerState', 'ServerDateModified', 'ServerDatePublished', 'File',
                            'IdentifierOpus3', 'Source');
                    $documentWithFilter->setModel($document)
                        ->setBlacklist(array_merge($type->getPublishFormBlackList(), $alwayshidden))
                        ->setSortOrder($type->getPublishFormSortOrder());
                    $form = $form_builder->build($documentWithFilter);
                    $action_url = $this->view->url(array('controller' => 'index', 'action' => 'create'));
                    $form->setAction($action_url);
                    $this->view->title = $this->view->translate('publish_controller_create');
                    $this->view->form = $form;
                } else {
                    // invalid form return to index
                    $this->_redirector->gotoSimple('index');
                }
            } else {
                // invalid form return to index
                $this->_redirector->gotoSimple('index');
            }
        } else {
            // on non post request redirect to index action
            $this->_redirector->gotoSimple('index');
        }
    }


    /**
     * Create form and handling file uploading
     *
     * @return void
     */
    public function uploadAction() {
        $this->view->title = $this->view->translate('publish_controller_upload');
        $uploadForm = new FileUpload();
        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'upload'));
        $uploadForm->setAction($action_url);
        // store uploaded data in application temp dir
        if ($this->_request->isPost() === true) {
            $data = $this->_request->getPost();
            if ($uploadForm->isValid($data) === true) {
                // This works only from Zend 1.7 on
                // $upload = $uploadForm->getTransferAdapter();
                $upload = new Zend_File_Transfer_Adapter_Http();
                $files = $upload->getFileInfo();
                // TODO: Validate document id, error message on fail
                $documentId = $uploadForm->getValue('DocumentId');
                $document = new Opus_Document($documentId);

                $this->view->message = $this->view->translate('publish_controller_upload_successful');

                // one form has only one file
                $file = $files['fileupload'];
                $hash = null;
                if (array_key_exists('sigupload', $files) === true) {
                	$sigfile = $files['sigupload'];
                }
                /* TODO: Uncaught exception 'Zend_File_Transfer_Exception' with message '"fileupload" not found by file transfer adapter
                 * if (!$upload->isValid($file)) {
                 *    $this->view->message = 'Upload failed: Not a valid file!';
                 *    break;
                 * }
                 */
                $docfile = $document->addFile();
                $docfile->setDocumentId($document->getId());
                $docfile->setLabel($uploadForm->getValue('comment'));
                $docfile->setLanguage($uploadForm->getValue('language'));
                $docfile->setPathName($file['name']);
                $docfile->setMimeType($file['type']);
                $docfile->setTempFile($file['tmp_name']);
                $docfile->setFromPost($file);
                if (array_key_exists('sigupload', $files) === true) {
                	$signature = implode("", file($sigfile['tmp_name']));
            		$hash = $docfile->addHashValue();
    	        	$hash->setType('gpg-0');
    		        $hash->setValue($signature);                	
                }
                $document->store();

                // reset input values fo re-displaying
                $uploadForm->reset();
                // re-insert document id
                $uploadForm->DocumentId->setValue($document->getId());
                $this->view->form = $uploadForm;
            } else {
                // invalid form, populate with transmitted data
                $uploadForm->populate($data);
                $this->view->form = $uploadForm;
            }
        } else {
            // on non post request redirect to index action
            $this->_redirector->gotoSimple('index');
        }
    }

}
