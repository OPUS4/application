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

    CONST BUTTON_ADD = 'addMore';
    CONST BUTTON_DELETE = 'deleteMore';
    CONST BUTTON_BROWSE_UP = 'browseUp';
    CONST BUTTON_BROWSE_DOWN = 'browseDown';

    CONST STEP = 'step';


    public $session;
    public $document;

    public function init() {
        $this->session = new Zend_Session_Namespace('Publish');
        parent::init();
    }

    public function uploadAction() {
        $this->view->languageSelectorDisabled = true;
        $this->view->title = 'publish_controller_index';
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
        $this->view->enableUpload = $indexForm->enableUpload;
        if (!$indexForm->enableUpload) {
            $this->view->subtitle = $this->view->translate('publish_controller_index_sub_without_file');
        }
        
        if (is_array($postData) && count($postData) === 0) {
            $this->_logger->err('FormController: EXCEPTION during uploading. Possibly the upload_max_filesize in php.ini is lower than the expected value in OPUS4 config.ini. Further information can be read in our documentation.');
            return $this->_redirectTo('index', $this->view->translate('error_empty_post_array'), 'index');
        }

        //don't allow MAX_FILE_SIZE to get overridden
        $config = Zend_Registry::get('Zend_Config');
        $postData['MAX_FILE_SIZE'] = $config->publish->maxfilesize;

        $indexForm->populate($postData);
        $this->_initializeDocument($postData);
        
        $files = $this->document->getFile();
        if (!empty($files)) {
            $this->view->subtitle = $this->view->translate('publish_controller_index_anotherFile');
        }
        
        // validate fileupload (if the current form contains a file upload field and file upload is enabled in application config)
        if ($indexForm->enableUpload) {
            if ($indexForm->getElement('fileupload') != null && !$indexForm->getElement('fileupload')->isValid($postData)) {
                $indexForm->setViewValues();
                $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
            }
            else {
                //file valid-> store file                
                $this->view->uploadSuccess = $this->_storeUploadedFiles($postData);
                if ($this->view->uploadSuccess) {
                    $this->view->subtitle = $this->view->translate('publish_controller_index_anotherFile');
                }
                // TODO warum wird hier nochmal eine Form instanziiert und nicht die weiter oben bereits erzeugte verwendet?
                $indexForm = new Publish_Form_PublishingFirst($this->view);
                $indexForm->populate($postData);
                $indexForm->setViewValues();                

                if (array_key_exists('addAnotherFile', $postData)) {
                    $postData['uploadComment'] = "";
                    return $this->renderScript('index/index.phtml');
                }                
            }
        }

        //validate whole form
        if (!$indexForm->isValid($postData)) {            
            $indexForm->setViewValues();
            $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
            return $this->renderScript('index/index.phtml');
        }

        //form entries are valid: store data
        $this->_storeBibliography($postData, $config);

        //call the appropriate template
        $this->_helper->viewRenderer($this->session->documentType);
        $publishForm = null;
        try {
            $publishForm = new Publish_Form_PublishingSecond($this->_logger);
        }
        catch (Publish_Model_FormSessionTimeoutException $e) {
            // Session timed out.            
            return $this->_redirectTo('index', '', 'index');
        }
        catch (Publish_Model_FormIncorrectFieldNameException $e) {
            $this->view->translateKey = preg_replace('/%value%/', $e->fieldName, $this->view->translate($e->getTranslateKey()));
            return $this->render('error');
        }
        catch (Publish_Model_FormIncorrectEnrichmentKeyException $e) {
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
        $this->view->title = 'publish_controller_index';

        if (isset($this->session->documentType)) {
            $this->view->subtitle = $this->view->translate($this->session->documentType);
        }

        $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');

        if ($this->getRequest()->isPost() === true) {           

            $postData = array_merge($this->session->additionalFields, $this->getRequest()->getPost());

            //abort publish process
            if (array_key_exists('abort', $postData)) {
                if (isset($this->session->documentId)) {
                    try {
                        $document = new Opus_Document($this->session->documentId);
                        $document->deletePermanent();
                    }
                    catch (Opus_Model_Exception $e) {
                        $this->_logger->err("deletion of document # " . $this->session->documentId . " was not successful", $e);
                    }
                }
                return $this->_redirectTo('index', '', 'index');
            }

            //go back and change data
            if (array_key_exists('back', $postData)) {                
                if (isset($this->session->elements))
                    foreach ($this->session->elements AS $element)
                        $postData[$element['name']] = $element['value'];
            }

            if (!array_key_exists('send', $postData) || array_key_exists('back', $postData)) {
                // A button (not SEND) was pressed => add / remove fields or browse fields
                $this->_helper->viewRenderer($this->session->documentType);

                $this->manipulateSession($postData);
                
                if (isset($this->view->translateKey)) {                    
                    return $this->render('error');
                }

                //now create a new form with extended fields
                $form = null;
                try {
                    $form = new Publish_Form_PublishingSecond($this->_logger, $postData);
                }
                catch (Publish_Model_FormSessionTimeoutException $e) {
                    return $this->_redirectTo('index', '', 'index');
                }
                
                $this->setViewValues('form', 'check', '#current', $form);
                
                if (array_key_exists('LegalNotices', $postData) && $postData['LegalNotices'] != '1') {
                    $legalNotices = $form->getElement('LegalNotices');
                    $legalNotices->setChecked(false);
                }
                return;
            }
            
            // SEND was pressed => check the form

            // der nachfolgende Schritt ist erforderlich, da die Selectbox der obersten Ebene einer Collection-Gruppe
            // gegen das Naming Scheme der Selectboxen der tieferen Ebenen verstößt
            foreach ($postData as $key => $value) {
                if (preg_match("/^collId1\D/", $key) === 1) {
                    $subkey = substr($key, 7);
                    if (!array_key_exists($subkey, $postData)) {
                        $postData[$subkey] = $value;
                    }
                }
            }
            
            $form = null;
            try {
                $form = new Publish_Form_PublishingSecond($this->_logger, $postData);
            }
            catch (Publish_Model_FormSessionTimeoutException $e) {
                // Session timed out.
                return $this->_redirectTo('index', '', 'index');
            }

            if (!$form->isValid($postData)) {
                $form->setViewValues();
                $this->view->form = $form;
                $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
                return $this->_helper->viewRenderer($this->session->documentType);
            }

            // form is valid: move to third form step (confirmation page)
            return $this->showCheckPage($form);
        }

        return $this->_redirectTo('upload');
    }

    /**
     * Method initializes the current document object by setting ServerState and DocumentType.
     */
    private function _initializeDocument($postData = null) {
        $documentType = isset($postData['documentType']) ? $postData['documentType'] : '';
        $this->session->documentType = $documentType;

        $docModel = new Publish_Model_DocumentWorkflow();

        if (!isset($this->session->documentId) || $this->session->documentId == '') {
            $this->_logger->info(__METHOD__ . ' documentType = ' . $documentType);
            $this->document = $docModel->createDocument($documentType);
            $this->session->documentId = $this->document->store();            
            $this->_logger->info(__METHOD__ . ' The corresponding document ID is: ' . $this->session->documentId);
        }
        else {
            $this->document = $docModel->loadDocument($this->session->documentId);
        }
    }

    /**
     * Method stores the uploaded files with comment for the current document
     */
    private function _storeUploadedFiles($postData) {
        $comment = array_key_exists('uploadComment', $postData) ? $postData['uploadComment'] : '';
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
            if (!isset($this->session->fulltext)) {
                $this->session->fulltext = '0';
            }
            return false;
        }

        $this->_logger->debug("File uploaded!!!");
        $this->session->fulltext = '1';

        $perfomStore = false;
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
                $perfomStore = true;
            }
        }

        if ($perfomStore) {
            $this->document->store();
        }
        return true;
    }

    /**
     * Method sets the bibliography flag in database.
     */
    private function _storeBibliography($data, $config) {
        if (!isset($config->form->first->bibliographie) || $config->form->first->bibliographie != '1') {
            return;
        }
        
        if (isset($data['bibliographie']) && $data['bibliographie'] === '1') {
            $this->_logger->debug("Bibliographie is set -> store it!");
            //store the document internal field BelongsToBibliography
            $this->document->setBelongsToBibliography(1);
            $this->document->store();
        }
    }

    /**
     * Prepare view template (second form step) for the given document type.
     * 
     * @param Publish_Form_PublishingSecond $form
     */
    private function showTemplate($form) {
        $this->view->subtitle = $this->view->translate($this->session->documentType);
        $this->view->doctype = $this->session->documentType;
        $this->setViewValues('form', 'check', '#current', $form);
    }

    /**
     * Prepare confirmation page (third form step) for the current document.
     * 
     * @param Publish_Form_PublishingSecond $form
     */
    private function showCheckPage($form) {
        $this->view->subtitle = $this->view->translate('publish_controller_check2');
        $this->view->header = $this->view->translate('publish_controller_changes');
        $this->setViewValues('deposit', 'deposit', '', $form, true);
    }

    private function setViewValues($controller, $action, $anchor, $form, $prepareCheck = false) {
        $url = $this->view->url(array('controller' => $controller, 'action' => $action)) . $anchor;
        $form->setAction($url);
        $form->setMethod('post');
        if ($prepareCheck) {
            $form->prepareCheck();
        }
        $this->view->action_url = $url;
        $this->view->form = $form;
    }

    private function manipulateSession($postData) {
        $this->view->currentAnchor = "";       

        try {
            //find out which button was pressed
            $pressedButtonName = $this->_getPressedButton($postData);
        } catch (Publish_Model_FormNoButtonFoundException $e) {
            $this->view->translateKey = $e->getTranslateKey();
            return null;
        }

        //find out the resulting workflow and the field to extend
        $result = $this->_workflowAndFieldFor($pressedButtonName);
        $fieldName = $result[0];
        $workflow = $result[1];

        // Häufigkeit des Felds im aktuellen Formular (Standard ist 1)
        $currentNumber = 1;
        if (isset($this->session->additionalFields[$fieldName])) {
            $currentNumber = $this->session->additionalFields[$fieldName];
        }

        // update collection fields in session member addtionalFields and find out the current level of collection browsing
        $level = $this->_updateCollectionField($fieldName, $currentNumber, $postData);

        $saveName = "";
        //Enrichment-Gruppen haben Enrichment im Namen, die aber mit den currentAnchor kollidieren
        if (strstr($fieldName, 'Enrichment')) {
            $saveName = $fieldName;
            $fieldName = str_replace('Enrichment', '', $fieldName);
        }
        if ($saveName != "")
            $fieldName = $saveName;

        $this->view->currentAnchor = 'group' . $fieldName;

        // Updates several counter in additionalFields that depends on the button label.
        switch ($workflow) {

            case 'add':
                // Add another form field.
                $this->session->additionalFields[$fieldName] = $currentNumber + 1;
                break;

            case 'delete':
                // Delete the last field.
                if ($currentNumber > 1) {
                    for ($i = 0; $i <= $level; $i++) {
                        unset($this->session->additionalFields['collId' . $i . $fieldName . '_' . $currentNumber]);
                    }
                    //remove one more field, only down to 0
                    $this->session->additionalFields[$fieldName] = $currentNumber - 1;
                }
                break;

            case 'down':
                // Browse down in the Collection hierarchy.
                if (($level == 1 && $postData[$fieldName . '_' . $currentNumber] !== '') || ($postData['collId' . $level . $fieldName . '_' . $currentNumber] != '')) {
                    $this->session->additionalFields[self::STEP . $fieldName . '_' . $currentNumber] = $level + 1;
                }
                break;

            case 'up' :
                // Browse up in the Collection hierarchy.
                unset($this->session->additionalFields['collId' . $level . $fieldName . '_' . $currentNumber]);

                if ($level >= 2) {
                    $this->session->additionalFields[self::STEP . $fieldName . '_' . $currentNumber] = $level - 1;
                }
                else {
                    unset($this->session->additionalFields[self::STEP . $fieldName . '_' . $currentNumber]);
                }

                break;

            default:
                break;
        }
    }

    /**
     * Method to check which button in the form was pressed
     * @param array $post array of POST request values
     * @return <String> name of button
     */
    private function _getPressedButton($post) {        
        foreach ($post AS $name => $value) {
            if (strstr($name, self::BUTTON_ADD) ||
                    strstr($name, self::BUTTON_DELETE) ||
                    strstr($name, self::BUTTON_BROWSE_DOWN) ||
                    strstr($name, self::BUTTON_BROWSE_UP)) {
                return $name;
            }
        }
        throw new Publish_Model_FormNoButtonFoundException();
    }

    /**
     * Finds out which button for which field was pressed.
     * @param string $button button label
     * @return two-element array with fieldname and workflow
     */
    private function _workflowAndFieldFor($button) {
        $result = array();
        if (substr($button, 0, 7) == self::BUTTON_ADD) {
            $result[0] = substr($button, 7);
            $result[1] = 'add';
        } else if (substr($button, 0, 10) == self::BUTTON_DELETE) {
            $result[0] = substr($button, 10);
            $result[1] = 'delete';
        } else if (substr($button, 0, 10) == self::BUTTON_BROWSE_DOWN) {
            $result[0] = substr($button, 10);
            $result[1] = 'down';
        } else if (substr($button, 0, 8) == self::BUTTON_BROWSE_UP) {
            $result[0] = substr($button, 8);
            $result[1] = 'up';
        }
        return $result;
    }

    /**
     * Finds the current level of collection browsing for a given field.
     * @param string $field name of field
     * @param string $value counter of fieldsets
     * @param array $post Array of post data
     * @return int current level
     */
    private function _updateCollectionField($field, $value, $post) {
        if (!array_key_exists(self::STEP . $field . '_' . $value, $this->session->additionalFields)) {
            return 1;
        }

        $level = $this->session->additionalFields[self::STEP . $field . '_' . $value];

        if ($level == 1) {
            // Root Node
            if (isset($post[$field . '_' . $value]) && $post[$field . '_' . $value] !== '') {                
                $this->session->additionalFields['collId1' . $field . '_' . $value] = $post[$field . '_' . $value];
            }
        }
        else {
            // Middle Node or Leaf
            if (isset($post['collId' . $level . $field . '_' . $value])) {
                $this->session->additionalFields['collId' . $level . $field . '_' . $value] = $post['collId' . $level . $field . '_' . $value];
            }
        }

        return $level;
    }
    
}
