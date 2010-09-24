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

//    public $documentType;
//    public $documentId;
//    public $fulltext;
//    public $additionalFields;

    public function uploadAction() {
        $log = Zend_Registry::get('Zend_Log');
        $defaultNS = new Zend_Session_Namespace('Publish');

        $this->view->languageSelectorDisabled = true;

        if ($this->getRequest()->isPost() === true) {
            $indexForm = new Publish_Form_PublishingFirst();

            $data = $this->getRequest()->getPost();

            if (!$indexForm->isValid($data)) {
                //error case, and redirect to form, show errors
                $this->view->form = $indexForm;
                return $this->renderScript('index/index.phtml');
            } else {

                $this->_setDocumentParameters($data, true);

                $this->view->title = $this->view->translate('publish_controller_index');
                $this->view->subtitle = $this->view->translate($defaultNS->documentType);
                $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');
                $this->view->doctype = $defaultNS->documentType;

                $this->_storeDocumentFiles();

                $templateName = $this->_helper->documentTypes->getTemplateName($defaultNS->documentType);

                $this->_helper->viewRenderer($templateName);

                $publishForm = new Publish_Form_PublishingSecond($defaultNS->documentType, $defaultNS->documentId, $defaultNS->fulltext, $defaultNS->additionalFields, null);
                $action_url = $this->view->url(array('controller' => 'form', 'action' => 'check'));
                $publishForm->setAction($action_url);
                $publishForm->setMethod('post');
                $this->_setViewVariables($publishForm);
                $this->view->action_url = $action_url;
                $this->view->form = $publishForm;
            }
        } else {            
            return $this->_redirectTo('index');
        }
    }

    /**
     * displays and checks the publishing form contents and calls deposit to store the data
     * uses check_array
     * @return <type>
     */
    public function checkAction() {
        $log = Zend_Registry::get('Zend_Log');
        $defaultNS = new Zend_Session_Namespace('Publish');

        $this->view->languageSelectorDisabled = true;
        $log->debug("check action begins");

        if ($this->getRequest()->isPost() === true) {
            $postData = $this->getRequest()->getPost();
            foreach ($postData as $key => $value) {
                $log->debug("POST --> [" . $key . "] : " . $value);
            }

            //initialize the form object
            $form = new Publish_Form_PublishingSecond($defaultNS->documentType, $defaultNS->documentId, $defaultNS->fulltext, $defaultNS->additionalFields, $postData);
            //$form->populate($postData);

            if (!$form->send->isChecked()) {
                // A button (not SEND) was pressed => add / remove fields

                $this->view->title = $this->view->translate('publish_controller_index');
                $this->view->subtitle = $this->view->translate($defaultNS->documentType);
                $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');

                $this->_helper->viewRenderer($defaultNS->documentType);

                //call method to add or delete buttons
                return $this->_getExtendedForm($form, $postData);
            } else {
                // SEND was pressed => check the form

                $this->view->title = $this->view->translate('publish_controller_index');
                $this->view->subtitle = $this->view->translate($defaultNS->documentType);
                $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');

                if (!$form->isValid($this->getRequest()->getPost())) {

                    //error case, and redirect to form, show errors
                    $this->view->form = $form;
                    $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
                    $this->_setViewVariables($form);

                    return $this->render($defaultNS->documentType);
                } else {
                    // Form variables all VALID
                    $log->debug("Variables are valid!");

                    $this->view->title = $this->view->translate('publish_controller_index');
                    $this->view->subtitle = $this->view->translate('publish_controller_check2');
                    $this->view->header = $this->view->translate('publish_controller_changes');

                    $depositForm = new Publish_Form_PublishingSecond($defaultNS->documentType, $defaultNS->documentId, $defaultNS->fulltext, $defaultNS->additionalFields, $form->getValues());
                    $action_url = $this->view->url(array('controller' => 'deposit', 'action' => 'deposit'));
                    //$action_url = $this->view->url(array('controller' => 'collection', 'action' => 'top'));
                    $depositForm->setAction($action_url);
                    $depositForm->setMethod('post');
                    $depositForm->populate($form->getValues());

                    $depositForm->prepareCheck();

                    foreach ($depositForm->getValues() AS $key => $value)
                            $defaultNS->documentData[$key] = $value;

                    $this->view->form = $depositForm;
                }
            }
        } else {
            return $this->_redirectTo('upload');
        }
    }

    /**
     * method to set the different variables and arrays for the view and the templates
     * @param <Zend_Form> $form
     */
    private function _setViewVariables($form) {
        //todo: refactor me!!!
        $log = Zend_Registry::get('Zend_Log');

        $errors = $form->getMessages();

        //group fields and single fields for view placeholders
        foreach ($form->getElements() AS $currentElement => $value) {
            //first element names have to loose special strings for finding groups
            $pos = stripos($currentElement, "FirstName");
            if ($pos != false) {
                $name = substr($currentElement, 0, $pos);
            } else {
                $pos = stripos($currentElement, "1");
                if ($pos != false)
                    $name = substr($currentElement, 0, $pos);
                else
                    $name=$currentElement; //"normal" element name without changes
            }

            $groupName = 'group' . $name;
            //translate the group name and give a array to view
            $this->view->$name = $this->view->translate($name);
            $displayGroup = $form->getDisplayGroup($groupName);
            if ($displayGroup != null) {
                $groupFields = array();
                $groupHiddens = array();
                $groupButtons = array();
                foreach ($displayGroup->getElements() AS $groupElement) {
                    //$log->debug(" Element: " . $groupElement);
                    $elementAttributes = $form->getElementAttributes($groupElement->getName()); //array
                    if ($groupElement->getType() === 'Zend_Form_Element_Submit') {
                        //buttons
                        $groupButtons[$elementAttributes["id"]] = $elementAttributes;
                    } else if ($groupElement->getType() === 'Zend_Form_Element_Hidden') {
                        //hidden fields
                        $groupHiddens[$elementAttributes["id"]] = $elementAttributes;
                    } else {
                        //normal fields
                        $groupFields[$elementAttributes["id"]] = $elementAttributes;
                    }
                }
                $group[] = array();
                $group["Name"] = $groupName;
                $group["Fields"] = $groupFields;
                $group["Hiddens"] = $groupHiddens;
                $group["Buttons"] = $groupButtons;
                $this->view->$groupName = $group;
            }
            //single fields (for calling with helper class)
            $singleField = $currentElement . "_";

            $elementAttributes = $form->getElementAttributes($currentElement); //array
            $this->view->$singleField = $elementAttributes;

            //also support more difficult templates for "expert admins"
            $this->view->$currentElement = $form->getElement($currentElement)->getValue();

            $name = $currentElement . "_label";
            $this->view->$name = $this->view->translate($form->getElement($currentElement)->getLabel());

            //error values
            if (isset($errors[$currentElement]))
                foreach ($errors[$currentElement] as $error => $errorMessage) {
                    $errorElement = $currentElement . 'Error';
                    $this->view->$errorElement = $errorMessage;
                }
        }
    }

    /**
     * method to check which buttob was pressed
     * @param <Zend_Form> $form
     * @return <type>
     */
    private function _getPressedButton($form) {
        $log = Zend_Registry::get('Zend_Log');
        $log->debug("Method getPressedButton begins...");
        $pressedButton = "";
        foreach ($form->getElements() AS $element) {
            if ($element->getType() === 'Zend_Form_Element_Submit' && $element->isChecked()) {
                $log->debug('Following Button Is Checked: ' . $element->getName());
                $pressedButton = $element;
                $pressedButtonName = $pressedButton->getName();
                break;
            }
        }

        if ($pressedButton == "")
            throw new Publish_Model_OpusServerException("No pressed button found! Possibly the values of the buttons are not equal in the view and Publish class.");
        //todo: which exeption to choose?
        else
            return $pressedButtonName;
    }

    /**
     * Methods sets session parameters of the document.
     * If they are not set in session then the value ist fetched from the post request.
     * @param <array> $postData
     */
    private function _setDocumentParameters($postData = null, $set = null) {
        $log = Zend_Registry::get('Zend_Log');
        $defaultNS = new Zend_Session_Namespace('Publish');

        if (!isset($defaultNS->documentType) || $set === true) {
            if (isset($postData['documentType'])) {
                $defaultNS->documentType = $postData['documentType'];
                unset($postData['documentType']);
            }
            else
                $defaultNS->documentType = "";

            $log->info("documentType = " . $defaultNS->documentType);
        }

        if (!isset($defaultNS->documentId) || $set === true) {
            if (isset($postData['documentId'])) {
                $defaultNS->documentId = $postData['documentId'];
                unset($postData['documentId']);
            }
            else
                $defaultNS->documentId = "";

            $log->info("documentId = " . $defaultNS->documentId);
        }

        if (!isset($defaultNS->fulltext) || $set === true) {
            if (isset($postData['fullText'])) {
                $defaultNS->fulltext = $postData['fullText'];
                unset($postData['fulltext']);
            }
            else
                $defaultNS->fulltext = "0";

            $log->info("fulltext = " . $defaultNS->fulltext);
        }
    }

    /**
     * Method stores th uploaded files
     */
    private function _storeDocumentFiles() {
        $log = Zend_Registry::get('Zend_Log');
        $defaultNS = new Zend_Session_Namespace('Publish');

        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();

        $defaultNS->document = new Opus_Document();
        $defaultNS->document->setType($defaultNS->documentType);
        $defaultNS->document->setServerState('temporary');

        if ($upload->isUploaded()) {
            $log->info("Fileupload of: " . count($files) . " possible files => Fulltext is '1'.");
            $defaultNS->fulltext = "1";

            foreach ($files AS $file => $fileValues) {
                if (!empty($fileValues['name'])) {
                    $log->info("uploaded: " . $fileValues['name']);
                    $docfile = $defaultNS->document->addFile();
                    //todo: default language should come from doc type
                    $docfile->setLanguage("eng");
                    $docfile->setFromPost($fileValues);
                }
            }
        } else {
            $log->info("No file uploaded: => Fulltext is NOT given.");
        }
        $defaultNS->documentId = $defaultNS->document->store();
        $log->info("The corresponding doucment ID is: " . $defaultNS->documentId);
    }

    /**
     * Methodgets the current form and finds out which fields has to be edded or deleted
     * @param Publish_Form_PublishingSecond $form
     * @return <View>
     */
    private function _getExtendedForm($form, $postData=null) {
        $log = Zend_Registry::get('Zend_Log');
        $defaultNS = new Zend_Session_Namespace('Publish');

        //find out which button was pressed
        $pressedButtonName = $this->_getPressedButton($form);

        if (substr($pressedButtonName, 0, 7) == "addMore") {
            $fieldName = substr($pressedButtonName, 7);
            $workflow = "add";
            $log->debug("Fieldname for addMore => " . $fieldName);
        } else if (substr($pressedButtonName, 0, 10) == "deleteMore") {
            $fieldName = substr($pressedButtonName, 10);
            $workflow = "delete";
            $log->debug("Fieldname for deleteMore => " . $fieldName);
        }
        
        $currentNumber = $defaultNS->additionalFields[$fieldName];
        $log->debug("old current number: " . $currentNumber);
        if ($workflow == "add") {
            //show one more fields
            $currentNumber = (int) $currentNumber + 1;
        } else {
            if ($currentNumber > 1) {
                //remove one more field, only down to 0
                $currentNumber = (int) $currentNumber - 1;
            }
        }

        //set the increased value for the pressed button and create a new form
        $defaultNS->additionalFields[$fieldName] = $currentNumber;
        $log->debug("new current number: " . $currentNumber . " for field ". $fieldName);

        $form = new Publish_Form_PublishingSecond($defaultNS->documentType, $defaultNS->documentId, $defaultNS->fulltext, $defaultNS->additionalFields, $postData);
        $action_url = $this->view->url(array('controller' => 'form', 'action' => 'check'));
        $form->setAction($action_url);
        $this->view->action_url = $action_url;
        $this->_setViewVariables($form);

        return $this->render($defaultNS->documentType);
    }

}