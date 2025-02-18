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

use Opus\Common\Config;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Model\ModelException;

class Publish_FormController extends Application_Controller_Action
{
    public const BUTTON_ADD         = 'addMore';
    public const BUTTON_DELETE      = 'deleteMore';
    public const BUTTON_BROWSE_UP   = 'browseUp';
    public const BUTTON_BROWSE_DOWN = 'browseDown';

    public const STEP = 'step';

    /** @var Zend_Session_Namespace */
    public $session;

    /** @var DocumentInterface */
    public $document;

    public function init()
    {
        $this->session = new Zend_Session_Namespace('Publish');
        parent::init();
        $this->view->headScript()->prependFile($this->view->layoutPath() . '/js/form-enter.js');
    }

    /**
     * @throws Application_Exception
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     */
    public function uploadAction()
    {
        $this->view->languageSelectorDisabled = true;
        $this->view->title                    = 'publish_controller_index';
        $this->view->requiredHint             = $this->view->translate('publish_controller_required_hint');
        $this->view->subtitle                 = $this->view->translate('publish_controller_index_sub');

        if ($this->getRequest()->isPost() !== true) {
            $this->_helper->Redirector->redirectTo('index', '', 'index');
            return;
        }

        //initializing
        $indexForm                = new Publish_Form_PublishingFirst($this->view);
        $postData                 = $this->getRequest()->getPost();
        $this->view->showBib      = $indexForm->bibliographie;
        $this->view->showRights   = $indexForm->showRights;
        $this->view->enableUpload = $indexForm->enableUpload;
        if (! $indexForm->enableUpload) {
            $this->view->subtitle = $this->view->translate('publish_controller_index_sub_without_file');
        }

        if (is_array($postData) && count($postData) === 0) {
            $this->getLogger()->err(
                'FormController: EXCEPTION during uploading. Possibly the upload_max_filesize in php.ini is lower than'
                . ' the expected value in OPUS4 config.ini. Further information can be read in our documentation.'
            );
            $this->_helper->Redirector->redirectTo(
                'index',
                ['failure' => 'error_empty_post_array'],
                'index'
            );
            return;
        }

        // Adds translated messages for javascript files
        $javascriptTranslations = $this->view->getHelper('javascriptMessages');
        $javascriptTranslations->getDefaultMessageSet();

        //don't allow MAX_FILE_SIZE to get overridden
        $config                    = $this->getConfig();
        $postData['MAX_FILE_SIZE'] = $config->publish->maxfilesize;

        $indexForm->populate($postData);
        $this->initializeDocument($postData); // TODO do not create a document unnecessarily

        $files = $this->document->getFile();
        if (! empty($files)) {
            $this->view->subtitle = $this->view->translate('publish_controller_index_anotherFile');
        }

        $config = Config::get();

        if (isset($config->publish->filetypes->allowed)) {
            $this->view->extensions = $config->publish->filetypes->allowed;
        }

        // validate fileupload (if the current form contains a file upload field and file upload is enabled in
        // application config)
        if ($indexForm->enableUpload) {
            $fileUpload = $indexForm->getElement('fileupload');
            if ($fileUpload !== null && ! $fileUpload->isValid($postData)) {
                $indexForm->setViewValues();
                $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
            } else {
                $this->view->uploadSuccess = true; // TODO no file to upload also means success

                if ($fileUpload->getValue() !== null && strlen(trim($fileUpload->getValue())) > 0) {
                    // file valid-> store file
                    $this->view->uploadSuccess = $this->storeUploadedFiles($postData);
                    if ($this->view->uploadSuccess) {
                        $this->view->subtitle = $this->view->translate('publish_controller_index_anotherFile');
                    }
                    // TODO warum wird hier nochmal eine Form instanziiert und nicht das bereits erzeugte verwendet?
                    $indexForm = new Publish_Form_PublishingFirst($this->view);
                    $indexForm->populate($postData);
                    $indexForm->setViewValues();

                    if (array_key_exists('addAnotherFile', $postData)) {
                        $postData['uploadComment'] = "";
                        $this->renderScript('index/index.phtml');
                        return;
                    }
                }
            }
        }

        //validate whole form
        if (! $indexForm->isValid($postData)) {
            $indexForm->setViewValues();
            $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
            $this->renderScript('index/index.phtml');
            return;
        }

        //form entries are valid: store data
        $this->storeBibliography($postData, $config);

        try {
            $publishForm = $this->createPublishingSecondForm();
        } catch (Publish_Model_FormSessionTimeoutException $e) {
            // Session timed out.
            $this->_helper->Redirector->redirectTo('index', '', 'index');
            return;
        }

        $this->showTemplate($publishForm);

        $this->renderDocumenttypeForm();
    }

    /**
     * @param array|null $postData
     * @return Publish_Form_PublishingSecond
     * @throws Application_Exception
     * @throws Publish_Model_FormSessionTimeoutException
     * @throws Zend_Exception
     */
    private function createPublishingSecondForm($postData = null)
    {
        $logger = $this->getLogger();

        try {
            return new Publish_Form_PublishingSecond($logger, $postData);
        } catch (Publish_Model_FormSessionTimeoutException $e) {
            $logger->info('Session Timeout beim Verarbeiten des zweiten Formularschritts');
            throw $e; // unmittelbarer Redirect erfolgt in Action-Methode
        } catch (Publish_Model_FormIncorrectFieldNameException $e) {
            $logger->err('invalider Feldname ' . $e->fieldName);
            throw new Application_Exception(
                preg_replace(
                    '/%value%/',
                    htmlspecialchars($e->fieldName),
                    $this->view->translate($e->getTranslateKey())
                )
            );
        } catch (Publish_Model_FormIncorrectEnrichmentKeyException $e) {
            $logger->err('invalider EnrichmentKey ' . $e->enrichmentKey);
            throw new Application_Exception(
                preg_replace(
                    '/%value%/',
                    htmlspecialchars($e->enrichmentKey),
                    $this->view->translate($e->getTranslateKey())
                )
            );
        } catch (Publish_Model_FormException $e) {
            $logger->err('Exception bei der Erzeugung des zweiten Formulars: ' . $e->enrichmentKey);
            throw new Application_Exception($e->getTranslateKey());
        } catch (Application_Exception $e) {
            throw $e;
        } catch (Exception $e) {
            $logger->err('unerwartete Exception bei der Erzeugung des zweiten Formulars: ' . $e->getMessage());
            throw new Application_Exception('publish_error_unexpected');
        }
    }

    /**
     * Method displays and checks the second form page. It also concerns for extending and reducing form fields.
     * After correct validation the user is redirected to deposit controller for storing data.
     */
    public function checkAction()
    {
        $this->view->languageSelectorDisabled = true;
        $this->view->title                    = 'publish_controller_index';

        $request = $this->getRequest();

        $selectedType = null;

        if ($request->isPost()) {
            $postData = $request->getPost();
            if (isset($postData['DocumentType'])) {
                // TODO validate type
                $selectedType                = $postData['DocumentType'];
                $this->session->selectedType = $selectedType;
            }
        }

        if (isset($this->session->documentType)) {
            $this->view->subtitle = $this->view->translate($this->session->documentType);
        }

        $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');

        if ($this->getRequest()->isPost() === true) {
            $postData = $this->getRequest()->getPost();
            if ($this->session->additionalFields !== null) {
                $postData = array_merge($this->session->additionalFields, $postData);
            }

            //abort publish process
            if (array_key_exists('abort', $postData)) {
                if (isset($this->session->documentId)) {
                    try {
                        $document = Document::get($this->session->documentId);
                        $document->delete();
                    } catch (ModelException $e) {
                        $this->getLogger()->err(
                            "deletion of document # " . $this->session->documentId . " was not successful",
                            $e
                        );
                    }
                }
                $this->_helper->Redirector->redirectTo('index', '', 'index');
                return;
            }

            //go back and change data
            if (array_key_exists('back', $postData)) {
                if (isset($this->session->elements)) {
                    foreach ($this->session->elements as $element) {
                        $postData[$element['name']] = $element['value'];
                    }
                }
            }

            if (! array_key_exists('send', $postData) || array_key_exists('back', $postData)) {
                // A button (not SEND) was pressed => add / remove fields or browse fields (both in form step 2)
                // OR back button (in form step 3)

                if (! array_key_exists('back', $postData)) {
                    // die Session muss nur dann manipuliert werden, wenn im zweiten Schritt ADD/DELETE/BROWSE
                    // durchgeführt wurde
                    try {
                        $this->manipulateSession($postData);
                    } catch (Publish_Model_FormNoButtonFoundException $e) {
                        throw new Application_Exception($e->getTranslateKey());
                    }
                }

                //now create a new form with extended fields
                try {
                    $form = $this->createPublishingSecondForm($postData);
                } catch (Publish_Model_FormSessionTimeoutException $e) {
                    // Session timed out.
                    $this->_helper->Redirector->redirectTo('index', '', 'index');
                    return;
                }

                $this->setViewValues('form', 'check', '#current', $form);

                if (array_key_exists('LegalNotices', $postData) && $postData['LegalNotices'] !== '1') {
                    $legalNotices = $form->getElement('LegalNotices');
                    $legalNotices->setChecked(false);
                }

                $this->renderDocumenttypeForm();
                return;
            }

            // SEND was pressed => check the form

            // der nachfolgende Schritt ist erforderlich, da die Selectbox der obersten Ebene einer Collection-Gruppe
            // gegen das Naming Scheme der Selectboxen der tieferen Ebenen verstößt
            foreach ($postData as $key => $value) {
                if (preg_match("/^collId1\D/", $key) === 1) {
                    $subkey = substr($key, 7);
                    if (! array_key_exists($subkey, $postData)) {
                        $postData[$subkey] = $value;
                    }
                }
            }

            try {
                $form = $this->createPublishingSecondForm($postData);
            } catch (Publish_Model_FormSessionTimeoutException $e) {
                // Session timed out.
                $this->_helper->Redirector->redirectTo('index', '', 'index');
                return;
            }

            if (! $form->isValid($postData)) {
                $form->setViewValues();
                $this->view->form             = $form;
                $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
                $this->renderDocumenttypeForm();
                return;
            }

            // form is valid: move to third form step (confirmation page)
            if ($selectedType !== null) {
                $this->view->subtitle = $this->view->translate($selectedType);
            }

            $this->showCheckPage($form);
            return;
        }

        $this->_helper->Redirector->redirectTo('upload');
    }

    /**
     * Method initializes the current document object by setting ServerState and DocumentType.
     *
     * @param array|null $postData
     */
    private function initializeDocument($postData = null)
    {
        $documentType                = $postData['documentType'] ?? '';
        $this->session->documentType = $documentType;

        $docModel = new Publish_Model_DocumentWorkflow();

        if (! isset($this->session->documentId) || $this->session->documentId === '') {
            $this->getLogger()->info(__METHOD__ . ' documentType = ' . $documentType);
            $this->document            = $docModel->createDocument($documentType);
            $this->session->documentId = $this->document->store();
            $this->getLogger()->info(__METHOD__ . ' The corresponding document ID is: ' . $this->session->documentId);
        } else {
            $this->document = $docModel->loadDocument($this->session->documentId);
            if ($documentType !== $this->document->getType()) {
                $this->document->setType($documentType);
                $this->document->store();
            }
        }
    }

    /**
     * Method stores the uploaded files with comment for the current document
     *
     * @param array $postData
     * @return bool
     */
    private function storeUploadedFiles($postData)
    {
        $comment     = array_key_exists('uploadComment', $postData) ? $postData['uploadComment'] : '';
        $upload      = new Zend_File_Transfer_Adapter_Http();
        $files       = $upload->getFileInfo();
        $uploadCount = 0;

        $uploadedFiles      = $this->document->getFile();
        $uploadedFilesNames = [];
        foreach ($uploadedFiles as $upfile) {
            $uploadedFilesNames[$upfile->getPathName()] = $upfile->getPathName();
        }

        foreach ($files as $file) {
            if (! empty($file['name'])) {
                //file have already been uploaded
                if (array_key_exists($file['name'], $uploadedFilesNames)) {
                    return false;
                }
                $uploadCount++;
            }
        }

        $logger = $this->getLogger();

        $logger->info("Fileupload of: " . count($files) . " potential files (vs. $uploadCount really uploaded)");

        if ($uploadCount < 1) {
            $logger->debug("NO File uploaded!!!");
            if (! isset($this->session->fulltext)) {
                $this->session->fulltext = '0';
            }
            return false;
        }

        $logger->debug("File uploaded!!!");
        $this->session->fulltext = '1';

        $perfomStore = false;
        foreach ($files as $file => $fileValues) {
            if (! empty($fileValues['name'])) {
                $logger->info("uploaded: " . $fileValues['name']);
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
     *
     * @param array       $data
     * @param Zend_Config $config
     */
    private function storeBibliography($data, $config)
    {
        if (
            ! isset($config->form->first->bibliographie) ||
            ! filter_var($config->form->first->bibliographie, FILTER_VALIDATE_BOOLEAN)
        ) {
            return;
        }

        if (isset($data['bibliographie']) && $data['bibliographie']) {
            $this->getLogger()->debug("Bibliographie is set -> store it!");
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
    private function showTemplate($form)
    {
        $this->view->subtitle = $this->view->translate($this->session->documentType);
        $this->view->doctype  = $this->session->documentType;
        $this->setViewValues('form', 'check', '#current', $form);
    }

    /**
     * Prepare confirmation page (third form step) for the current document.
     *
     * @param Publish_Form_PublishingSecond $form
     */
    private function showCheckPage($form)
    {
        $this->view->hint   = $this->view->translate('publish_controller_check2');
        $this->view->header = $this->view->translate('publish_controller_changes');
        $this->setViewValues('deposit', 'deposit', '', $form, true);
    }

    /**
     * @param string    $controller
     * @param string    $action
     * @param string    $anchor
     * @param Zend_Form $form
     * @param bool      $prepareCheck
     */
    private function setViewValues($controller, $action, $anchor, $form, $prepareCheck = false)
    {
        $url = $this->view->url(['controller' => $controller, 'action' => $action]) . $anchor;
        $form->setAction($url);
        $form->setMethod('post');
        if ($prepareCheck) {
            $form->prepareCheck();
        }
        $this->view->action_url = $url;
        $this->view->form       = $form;
    }

    /**
     * @param array $postData
     * @throws Publish_Model_FormNoButtonFoundException
     */
    private function manipulateSession($postData)
    {
        $this->view->currentAnchor = "";

        //find out which button was pressed
        $pressedButtonName = $this->getPressedButton($postData);

        //find out the resulting workflow and the field to extend
        $result    = $this->workflowAndFieldFor($pressedButtonName);
        $fieldName = $result[0];
        $workflow  = $result[1];

        // Häufigkeit des Felds im aktuellen Formular (Standard ist 1)
        $currentNumber = 1;
        if (isset($this->session->additionalFields[$fieldName])) {
            $currentNumber = $this->session->additionalFields[$fieldName];
        }

        // update collection fields in session member addtionalFields and find out the current level of collection
        // browsing
        $level = $this->updateCollectionField($fieldName, $currentNumber, $postData);

        $saveName = "";
        //Enrichment-Gruppen haben Enrichment im Namen, die aber mit den currentAnchor kollidieren
        if (strstr($fieldName, 'Enrichment')) {
            $saveName  = $fieldName;
            $fieldName = str_replace('Enrichment', '', $fieldName);
        }
        if ($saveName !== '') {
            $fieldName = $saveName;
        }

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
                    unset($this->session->additionalFields[self::STEP . $fieldName . '_' . $currentNumber]);
                }
                break;

            case 'down':
                // Browse down in the Collection hierarchy.
                if (
                    ($level === 1 && $postData[$fieldName . '_' . $currentNumber] !== '')
                    || ($level > 1 && $postData['collId' . $level . $fieldName . '_' . $currentNumber] !== '')
                ) {
                    $this->session->additionalFields[self::STEP . $fieldName . '_' . $currentNumber] = $level + 1;
                }
                break;

            case 'up':
                // Browse up in the Collection hierarchy.
                unset($this->session->additionalFields['collId' . $level . $fieldName . '_' . $currentNumber]);

                if ($level >= 2) {
                    $this->session->additionalFields[self::STEP . $fieldName . '_' . $currentNumber] = $level - 1;
                } else {
                    unset($this->session->additionalFields[self::STEP . $fieldName . '_' . $currentNumber]);
                }

                break;

            default:
                break;
        }
    }

    /**
     * Method to check which button in the form was pressed
     *
     * @param array $post array of POST request values
     * @return string Name of button
     * @throws Publish_Model_FormNoButtonFoundException
     */
    private function getPressedButton($post)
    {
        foreach ($post as $name => $value) {
            if (
                strstr($name, self::BUTTON_ADD) ||
                    strstr($name, self::BUTTON_DELETE) ||
                    strstr($name, self::BUTTON_BROWSE_DOWN) ||
                    strstr($name, self::BUTTON_BROWSE_UP)
            ) {
                return $name;
            }
        }
        throw new Publish_Model_FormNoButtonFoundException();
    }

    /**
     * Finds out which button for which field was pressed.
     *
     * @param string $button button label
     * @return array Two-element array with fieldname and workflow
     */
    private function workflowAndFieldFor($button)
    {
        $result = [];
        if (substr($button, 0, 7) === self::BUTTON_ADD) {
            $result[0] = substr($button, 7);
            $result[1] = 'add';
        } elseif (substr($button, 0, 10) === self::BUTTON_DELETE) {
            $result[0] = substr($button, 10);
            $result[1] = 'delete';
        } elseif (substr($button, 0, 10) === self::BUTTON_BROWSE_DOWN) {
            $result[0] = substr($button, 10);
            $result[1] = 'down';
        } elseif (substr($button, 0, 8) === self::BUTTON_BROWSE_UP) {
            $result[0] = substr($button, 8);
            $result[1] = 'up';
        }
        return $result;
    }

    /**
     * Finds the current level of collection browsing for a given field.
     *
     * @param string $field name of field
     * @param string $value counter of fieldsets
     * @param array  $post Array of post data
     * @return int current level
     */
    private function updateCollectionField($field, $value, $post)
    {
        if (! array_key_exists(self::STEP . $field . '_' . $value, $this->session->additionalFields)) {
            return 1;
        }

        $level = (int) $this->session->additionalFields[self::STEP . $field . '_' . $value];

        if ($level === 1) {
            // Root Node
            if (isset($post[$field . '_' . $value]) && $post[$field . '_' . $value] !== '') {
                $this->session->additionalFields['collId1' . $field . '_' . $value] = $post[$field . '_' . $value];
            }
        } else {
            // Middle Node or Leaf
            if (isset($post['collId' . $level . $field . '_' . $value])) {
                $this->session->additionalFields['collId' . $level . $field . '_' . $value] =
                    $post['collId' . $level . $field . '_' . $value];
            }
        }

        return (int) $level;
    }

    private function renderDocumenttypeForm()
    {
        $docTypeHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('DocumentTypes');
        $templateName  = $docTypeHelper->getTemplateName($this->session->documentType);

        if ($templateName === null) {
            throw new Application_Exception(
                'invalid configuration: could not get template name for requested document type'
            );
        }

        $templateFileName = $docTypeHelper->getTemplatePath($templateName);

        $file = $templateFileName !== null ? new SplFileInfo($templateFileName) : null;

        if ($file === null || ! $file->isReadable()) {
            throw new Application_Exception(
                'invalid configuration: template file ' . $templateName . '.phtml is not readable or does not exist'
            );
        }

        $this->view->setScriptPath($file->getPath());
        $this->renderScript($file->getBasename());
    }
}
