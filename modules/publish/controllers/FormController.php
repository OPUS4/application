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
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_FormController extends Controller_Action {

    public $session;
    public $document;

    public function init() {
        $this->session = new Zend_Session_Namespace('Publish');
        parent::init();
    }

    public function uploadAction() {
        $this->view->languageSelectorDisabled = true;
        $this->view->title = $this->view->translate('publish_controller_index');
        $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');
        $this->view->subtitle = $this->view->translate('publish_controller_index_sub');

        if ($this->getRequest()->isPost() !== true) {
            return $this->_redirectTo('index', '', 'index');
        }

        //initializing
        $indexForm = new Publish_Form_PublishingFirst($this->view);
        $postData = $this->getRequest()->getPost();
        $this->view->showBib = $indexForm->bibliographie;
        $this->view->showRights = $indexForm->showRights;

        if (is_array($postData) && count($postData) === 0) {
            $this->_logger->err('FormController: EXCEPTION during uploading. Possibly the upload_max_filesize in php.ini is lower than the expected value in OPUS4 config.ini. Further information can be read in our documentation.');
            return $this->_redirectTo('index', $this->view->translate('error_empty_post_array'), 'index');
        }

        //don't allow MAX_FILE_SIZE to get overridden
        $config = Zend_Registry::get('Zend_Config');
        $postData['MAX_FILE_SIZE'] = $config->publish->maxfilesize;

        $indexForm->populate($postData);
        $this->_initializeDocument($postData);

        //validate fileupload
        if (!$indexForm->getElement('fileupload')->isValid($postData)) {
            $indexForm->setViewValues();
            $this->view->form = $indexForm;
            $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
        } else {
            //file valid-> store file                
            $this->view->subtitle = $this->view->translate('publish_controller_index_anotherFile');
            $this->view->uploadSuccess = $this->_storeUploadedFiles($postData);
            $indexForm = new Publish_Form_PublishingFirst($this->view);
            $indexForm->populate($postData);
            $indexForm->setViewValues();
            $this->view->form = $indexForm;

            if (array_key_exists('addAnotherFile', $postData)) {
                $postData['uploadComment'] = "";
                return $this->renderScript('index/index.phtml');
            }
        }

        //validate whole form
        if (!$indexForm->isValid($postData)) {
            $indexForm->setViewValues();
            $this->view->form = $indexForm;
            $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
            return $this->renderScript('index/index.phtml');
        }

        //form entries are valid: store data
        $this->_storeBibliography($postData);

        //call the appropriate template
        $this->_helper->viewRenderer($this->session->documentType);
        try {
            $publishForm = new Publish_Form_PublishingSecond($this->_logger);
        } catch (Publish_Model_FormSessionTimeoutException $e) {
            // Session timed out.
            return $this->_redirectTo('index', '', 'index');
        } catch (Publish_Model_FormIncorrectFieldNameException $e) {
            $this->view->translateKey = preg_replace('/%value%/', $e->fieldName, $this->view->translate($e->getTranslateKey()));
            return $this->render('error');
        } catch (Publish_Model_FormIncorrectEnrichmentKeyException $e) {
            $this->view->translateKey = preg_replace('/%value%/', $e->enrichmentKey, $this->view->translate($e->getTranslateKey()));
            return $this->render('error');
        }

        return $this->showTemplate($publishForm);
    }

    /**
     * Method displays and checks the second form page. It also concerns for extending and reducing form fields.
     * After correct validation the user is redirected to deposit controller for storing data.
     * 
     * @return different types of redirect
     */
    public function checkAction() {
        $this->view->languageSelectorDisabled = true;
        $this->view->title = $this->view->translate('publish_controller_index');

        if (isset($this->session->documentType))
            $this->view->subtitle = $this->view->translate($this->session->documentType);

        $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');

        //reload form or show entries, intial: true
        $reload = true;

        if ($this->getRequest()->isPost() === true) {
            $postData = $this->getRequest()->getPost();

            if (!is_null($this->session->disabled))
                $postData = array_merge($postData, $this->session->disabled);

            //abort publish process
            if (array_key_exists('abort', $postData)) {
                if (isset($this->session->documentId)) {
                    $this->document = new Opus_Document($this->session->documentId);
                    $this->document->deletePermanent();
                }
                return $this->_redirectTo('index', '', 'index');
            }

            //go back and change data
            if (array_key_exists('back', $postData)) {
                $reload = false;
                if (isset($this->session->elements))
                    foreach ($this->session->elements AS $element)
                        $postData[$element['name']] = $element['value'];
            }

            //initialize the form object
            $form = null;
            try {
                $form = new Publish_Form_PublishingSecond($this->_logger, $postData);
            } catch (Publish_Model_FormSessionTimeoutException $e) {
                // Session timed out.
                return $this->_redirectTo('index', '', 'index');
            }

            if (!array_key_exists('send', $postData) || array_key_exists('back', $postData)) {
                // A button (not SEND) was pressed => add / remove fields                
                $this->_helper->viewRenderer($this->session->documentType);

                $form->getExtendedForm($postData, $reload);
                
                if (isset($this->view->translateKey)) {
                    
                    return $this->render('error');
                }

                //now create a new form with extended fields
                $form2 = null;
                try {
                    $form2 = new Publish_Form_PublishingSecond($this->_logger, $postData);
                }
                catch (Publish_Model_FormSessionTimeoutException $e) {

                    return $this->_redirectTo('index', '', 'index');
                }
                $action_url = $this->view->url(array('controller' => 'form', 'action' => 'check')) . '#current';
                $form2->setAction($action_url);
                $this->view->action_url = $action_url;
                $form2->setViewValues();
                $this->view->form = $form2;
                
                if (array_key_exists('LegalNotices', $postData) && $postData['LegalNotices'] != '1') {
                    $legalNotices = $form2->getElement('LegalNotices');
                    $legalNotices->setChecked(false);
                }
                return;
            }
            // SEND was pressed => check the form
            if (!$form->isValid($postData)) {
                $form->setViewValues();
                $this->view->form = $form;
                $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
                return $this->_helper->viewRenderer($this->session->documentType);
            }
            return $this->showCheckPage($form);
        }

        return $this->_redirectTo('upload');
    }

    /**
     * Method initializes the current document object by setting ServerState and DocumentType.
     */
    private function _initializeDocument($postData = null) {
        $documentType = isset($postData['documentType']) ? $postData['documentType'] : '';
        $docModel = new Publish_Model_Document();

        if (!isset($this->session->documentId) || $this->session->documentId == '') {
            $this->_logger->info(__METHOD__ . ' documentType = ' . $documentType);
            $this->document = $docModel->createTempDocument($documentType);
            $this->session->documentId = $this->document->store();
            $this->session->documentType = $documentType;
            $this->_logger->info(__METHOD__ . ' The corresponding document ID is: ' . $this->session->documentId);
        }
        else {
            $documentId = $this->session->documentId;
            $documentType = $this->session->documentType;
            $this->document = $docModel->loadTempDocument($documentId, $documentType);
        }
    }

    /**
     * Method stores the uploaded files with comment for the current document
     */
    private function _storeUploadedFiles($postData) {
        if (array_key_exists('uploadComment', $postData))
            $comment = $postData['uploadComment'];
        else
            $comment = "";
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();
        $upload_count = 0;

        $uploaded_files = $this->document->getFile();
        $uploaded_files_names = array();
        foreach ($uploaded_files as $upfile) {
            $uploaded_files_names[$upfile->getPathName()] = $upfile->getPathName();
        }

        foreach ($files as $file) {
            if (!empty($file['name'])) {

                //file have already been uploaded
                if (array_key_exists($file['name'], $uploaded_files_names)) {
                    return false;
                }
                $upload_count++;
            }
        }

        $this->_logger->info("Fileupload of: " . count($files) . " potential files (vs. $upload_count really uploaded)");

        if ($upload_count < 1) {
            $this->_logger->debug("NO File uploaded!!!");
            if (!isset($this->session->fulltext))
                $this->session->fulltext = '0';
            return;
        }

        $this->_logger->debug("File uploaded!!!");
        $this->session->fulltext = '1';

        foreach ($files AS $file => $fileValues) {
            if (!empty($fileValues['name'])) {
                $this->_logger->info("uploaded: " . $fileValues['name']);
                $docfile = $this->document->addFile();
                //$docfile->setFromPost($fileValues);
                $docfile->setLabel(urldecode($fileValues['name']));
                $docfile->setComment($comment);                
                $docfile->setPathName(urldecode($fileValues['name']));
                $docfile->setMimeType($fileValues['type']);
                $docfile->setTempFile($fileValues['tmp_name']);
            }
        }

        $this->document->store();
        return true;
    }

    /**
     * Method sets the bibliography flag in database.
     */
    private function _storeBibliography($data) {
        if (isset($data['bibliographie']) && $data['bibliographie'] === '1') {
            $this->_logger->debug("Bibliographie is set -> store it!");
            //store the document internal field BelongsToBibliography
            $this->document->setBelongsToBibliography(1);
            $this->document->store();
        }
    }

    /**
     * Method shows the template for the given document type.
     * @param PublishingSecond $form 
     */
    public function showTemplate($form) {
        $this->view->subtitle = $this->view->translate($this->session->documentType);
        $this->view->doctype = $this->session->documentType;
        $action_url = $this->view->url(array('controller' => 'form', 'action' => 'check')) . '#current';
        $form->setAction($action_url);
        $form->setMethod('post');
        $this->view->action_url = $action_url;
        $this->view->form = $form;
    }

    public function showCheckpage($form) {
        $this->view->subtitle = $this->view->translate('publish_controller_check2');
        $this->view->header = $this->view->translate('publish_controller_changes');
        $action_url = $this->view->url(array('controller' => 'deposit', 'action' => 'deposit'));
        $form->setAction($action_url);
        $form->setMethod('post');
        $form->prepareCheck();
        $this->view->action_url = $action_url;
        $this->view->form = $form;
    }

}
