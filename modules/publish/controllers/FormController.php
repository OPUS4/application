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
 * @package     Application - Module Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Publish_2_IndexController$
 */

/**
 * Main entry point for this module.
 *
 * @category    Application
 * @package     Module_Publish
 */
class Publish_FormController extends Controller_Action {
    CONST FIRST = "Firstname";
    CONST COUNTER = "1";
    CONST GROUP = "group";
    CONST EXPERT = "X";
    CONST LABEL = "_label";
    CONST ERROR = "Error";

    public $log;
    public $session;
    public $document;
    public $helper;

    public function __construct(Zend_Controller_Request_Abstract $request, Zend_Controller_Response_Abstract $response, array $invokeArgs = array()) {
        $this->log = Zend_Registry::get('Zend_Log');
        $this->session = new Zend_Session_Namespace('Publish');
        $this->helper = new Publish_Model_FormHelper();

        parent::__construct($request, $response, $invokeArgs);
    }

    public function uploadAction() {        
        $this->view->languageSelectorDisabled = true;
        $this->view->title = $this->view->translate('publish_controller_index');

        if ($this->getRequest()->isPost() === true) {

            //initializing
            $indexForm = new Publish_Form_PublishingFirst($this->view);
            $this->helper->setCurrentView($this->view);
            $data = $this->getRequest()->getPost();

            if (is_array($data) && count($data) === 0) {
                $this->log->err('FormController: EXCEPTION during uploading. Possibly the upload_max_filesize in php.ini is lower than the expected value in OPUS4 config.ini. Further information can be read in our documentation.');
                 return $this->_redirectTo('index', $this->view->translate('error_empty_post_array'), 'index');
            }

            $indexForm->populate($data);
            $this->_initializeDocument($data);

            //reject manipulated hidden field for file size
            if (isset($data['MAX_FILE_SIZE']) && $data['MAX_FILE_SIZE'] != $this->session->maxFileSize) {
                $this->log->debug("wrong Max_file_size and redirect to index");
                return $this->_redirectTo('index', '', 'index');
            }

            //validate fileupload
            if (!$indexForm->getElement('fileupload')->isValid($data)) {
                $this->view->form = $indexForm;
                $this->view->subtitle = $this->view->translate('publish_controller_index_sub');
                $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');
                $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
                $this->helper->setCurrentForm($indexForm);            
                $this->helper->setFirstFormViewVariables();
            }
            else {
                //file valid-> store file
                $this->view->subtitle = $this->view->translate('publish_controller_index_anotherFile');
                $this->view->form = $indexForm;
                $this->helper->setCurrentForm($indexForm);
                $this->helper->setFirstFormViewVariables();
                if (array_key_exists('uploadComment', $data))
                        $comment = $data['uploadComment'];
                else 
                    $comment = "";
                $this->session->uploadSuccess = $this->_storeUploadedFiles($comment);

                if (array_key_exists('addAnotherFile', $data)) {
                    $data['uploadComment'] = "";
                    return $this->renderScript('index/index.phtml');
                }
            }

            //validate whole form
            if (!$indexForm->isValid($data)) {
                $this->view->form = $indexForm;
                $this->view->subtitle = $this->view->translate('publish_controller_index_sub');
                $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');
                $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
                $this->helper->setCurrentForm($indexForm);
                $this->helper->setFirstFormViewVariables();
                return $this->renderScript('index/index.phtml');
            }

            //form entries are valid: store data
            $this->_storeBibliography($data);
            $this->_storeSubmitterEnrichment();

            //call the appropriate template
            return $this->helper->showTemplate($this->_helper);
        }
        return $this->_redirectTo('index', '', 'index');
    }

    /**
     * displays and checks the publishing form contents and calls deposit to store the data
     * uses check_array
     * @return <type>
     */
    public function checkAction() {
        $this->view->languageSelectorDisabled = true;
        $this->helper->setCurrentView($this->view);
        $reload = true;

        if ($this->getRequest()->isPost() === true) {
            $postData = $this->getRequest()->getPost();

            if (array_key_exists('abort', $postData))
                return $this->_redirectTo('index', '', 'index');

            if (array_key_exists('back', $postData) || array_key_exists('abortCollection', $postData)) {
                $reload = false;
                if (isset($this->session->elements))
                    foreach ($this->session->elements AS $element)
                        $postData[$element['name']] = htmlspecialchars($element['value']);
            }
            
            //initialize the form object
            $form = new Publish_Form_PublishingSecond($postData);

            if (array_key_exists('abortCollection', $postData)) {
                $form = $form->populate($postData);
                $this->helper->setCurrentForm($form);
                return $this->helper->showCheckPage();
            }

            if (!$form->send->isChecked() || array_key_exists('back', $postData)) {                
                // A button (not SEND) was pressed => add / remove fields

                $this->view->title = $this->view->translate('publish_controller_index');
                $this->view->subtitle = $this->view->translate($this->session->documentType);
                $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');

                $this->_helper->viewRenderer($this->session->documentType);

                //call method to add or delete buttons
                $this->helper->setCurrentForm($form);
                return $this->helper->getExtendedForm($postData, $reload);
            }

            // SEND was pressed => check the form
            $this->view->title = $this->view->translate('publish_controller_index');
            $this->view->subtitle = $this->view->translate($this->session->documentType);
            $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');

            if (!$form->isValid($this->getRequest()->getPost())) {
                //Variables are invalid
                $this->helper->setCurrentForm($form);
                $this->helper->setSecondFormViewVariables();
                $this->view->form = $form;
                $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
                //error case, and redirect to form, show errors
                return $this->render($this->session->documentType);
            }
            $this->helper->setCurrentForm($form);
            return $this->helper->showCheckPage();
        }

        return $this->_redirectTo('upload');
    }

    /**
     * Method stores th uploaded files
     */
    private function _initializeDocument($postData = null) {       
        if (!isset($this->session->documentId) || $this->session->documentId == '') {
            $this->document = new Opus_Document();
            $this->document->setServerState('temporary');
            $this->session->documentId = $this->document->store();
            $this->log->info(__METHOD__ . ' The corresponding document ID is: ' . $this->session->documentId);
        }
        else
            $this->document = new Opus_Document($this->session->documentId);

        if (isset($postData['documentType'])) {
            if ($postData['documentType'] !== '') {
                $this->session->documentType = $postData['documentType'];
                $this->log->info(__METHOD__ .  ' documentType = ' . $this->session->documentType);                
                $this->document->setType($this->session->documentType);
                $this->document->store();
            }
            unset($postData['documentType']);
        }

        $this->session->additionalFields = array();
    }

    /**
     * Method stores th uploaded files
     */
    private function _storeSubmitterEnrichment() {
        $loggedUserModel = new Publish_Model_LoggedUser();
        $userId = trim($loggedUserModel->getUserId());

        if (empty($userId)) {
            $this->log->debug("No user logged in.  Skipping enrichment.");
            return;
        }

        $this->document->addEnrichment()
                ->setKeyName('submitter.user_id')
                ->setValue($userId);
        $this->document->store();
    }

    /**
     * Method stores the uploaded files
     */
    private function _storeUploadedFiles($comment) {
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

        $this->log->info("Fileupload of: " . count($files) . " potential files (vs. $upload_count really uploaded)");

        if ($upload_count < 1) {
            $this->log->debug("NO File uploaded!!!");
            $this->session->fulltext = '0';
            return;
        }

        $this->log->debug("File uploaded!!!");
        $this->session->fulltext = '1';

        foreach ($files AS $file => $fileValues) {
            if (!empty($fileValues['name'])) {
                $this->session->publishFiles[] = $fileValues['name'];
                $this->log->info("uploaded: " . $fileValues['name']);
                $docfile = $this->document->addFile();
                $docfile->setFromPost($fileValues);
                //file always requires a language, this value is later overwritten by the exact language
                $docfile->setLanguage("eng");
                $docfile->setComment(htmlspecialchars($comment));
            }
        }

        $this->document->store();
        return true;
    }

    /**
     * Method stores the uploaded files
     */
    private function _storeBibliography($data) {
        if (isset($data['bibliographie']) && $data['bibliographie'] === '1') {
            $this->log->debug("Bibliographie is set -> store it!");
            //store the document internal field BelongsToBibliography
            $this->document->setBelongsToBibliography(1);
            $this->document->store();
        }
    }
  
}