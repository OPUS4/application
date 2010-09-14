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

    public $documentType;
    public $documentId;
    public $fulltext;
    public $additionalFields;

    public function uploadAction() {
        $log = Zend_Registry::get('Zend_Log');
        $this->view->languageSelectorDisabled = true;

        if ($this->getRequest()->isPost() === true) {
            $indexForm = new Publish_Form_PublishingFirst();

            $data = $this->getRequest()->getPost();

            if (!$indexForm->isValid($data)) {
                //error case, and redirect to form, show errors
                $this->view->form = $indexForm;
                return $this->renderScript('index/index.phtml');
            } else {

                $this->_setDocumentParameters($data);

                $this->view->title = $this->view->translate('publish_controller_index');
                $this->view->subtitle = $this->view->translate($this->documentType);
                $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');
                $this->view->doctype = $this->documentType;

                $this->_storeDocumentFiles();

                $templateName = $this->_helper->documentTypes->getTemplateName($this->documentType);

                $this->_helper->viewRenderer($templateName);

                $publishForm = new Publish_Form_PublishingSecond($this->documentType, $this->documentId, $this->fulltext, null, null);
                $action_url = $this->view->url(array('controller' => 'form', 'action' => 'check'));
                $publishForm->setAction($action_url);
                $publishForm->setMethod('post');
                $this->_setViewVariables($publishForm);
                $this->view->action_url = $action_url;
                $this->view->form = $publishForm;
            }
        } else {
            // GET Reqquest is redirected to index
            $url = $this->view->url(array('controller' => 'index', 'action' => 'index'));
            return $this->redirectTo($url);
        }
    }

    /**
     * displays and checks the publishing form contents and calls deposit to store the data
     * uses check_array
     * @return <type>
     */
    public function checkAction() {
        $log = Zend_Registry::get('Zend_Log');
        $this->view->languageSelectorDisabled = true;

        if ($this->getRequest()->isPost() === true) {
            $postData = $this->getRequest()->getPost();
            foreach ($postData as $key => $value) {
                $log->debug("POST --> [" . $key . "] : " . $value);
            }

            $this->_setDocumentParameters($postData);

            //initialize the form object
            $form = new Publish_Form_PublishingSecond($this->documentType, $this->documentId, $this->fulltext, $this->additionalFields, $postData);
            $form->populate($postData);

            if (!$form->send->isChecked()) {
                // A button (not SEND) was pressed => add / remove fields

                $this->view->title = $this->view->translate('publish_controller_index');
                $this->view->subtitle = $this->view->translate($this->documentType);
                $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');

                $this->_helper->viewRenderer($this->documentType);

                //call method to add or delete buttons
                return $this->_getExtendedForm($form, $postData);
            } else {
                // SEND was pressed => check the form

                $this->view->title = $this->view->translate('publish_controller_check');
                $this->view->subtitle = $this->view->translate($this->documentType);
                $this->view->requiredHint = $this->view->translate('publish_controller_required_hint');

                if (!$form->isValid($this->getRequest()->getPost())) {

                    //error case, and redirect to form, show errors
                    $this->view->form = $form;
                    $this->view->errorCaseMessage = $this->view->translate('publish_controller_form_errorcase');
                    $this->_setViewVariables($form);

                    return $this->render($this->documentType);
                } else {
                    // Form variables all VALID
                    $log->debug("Variables are valid!");

                    $this->view->title = $this->view->translate('publish_controller_check');
                    $this->view->subtitle = $this->view->translate('publish_controller_check2');
                    $this->view->header = $this->view->translate('publish_controller_changes');

                    $depositForm = new Publish_Form_PublishingSecond($this->documentType, $this->documentId, $this->fulltext, $this->additionalFields, $form->getValues());
                    $action_url = $this->view->url(array('controller' => 'deposit', 'action' => 'deposit'));
                    $depositForm->setAction($action_url);
                    $depositForm->setMethod('post');
                    $depositForm->populate($form->getValues());

                    $depositForm = $this->_removeUnsavableElements($depositForm, $form);

                    $this->view->form = $depositForm;
                }
            }
        } 
        else {
            //GET Request on check
            return $this->redirectTo('upload');
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
     * Methods sets the documents parameters which are also the memeber variables of this controller
     * @param <array> $postData
     */
    private function _setDocumentParameters($postData = null) {
        $log = Zend_Registry::get('Zend_Log');
        if (!empty($postData)) {

            if (isset($postData['documentType']))
                $this->documentType = $postData['documentType'];
            else
                $this->documentType = "";

            $log->debug("documentType = " . $this->documentType);
            if (isset($postData['documentId']))
                $this->documentId = $postData['documentId'];
            else
                $this->documentId = "";

            $log->debug("documentId = " . $this->documentId);

            if (isset($postData['fullText']))
                $this->fulltext = $postData['fullText'];
            else
                $this->fulltext = "0";

            $log->debug("fulltext = " . $this->fulltext);

            $additionalFields = array();

            foreach ($postData AS $element => $value) {
                if (substr($element, 0, 9) == "countMore") {
                    $key = substr($element, 9);
                    $additionalFields[$key] = (int) $value;
                }
            }

            $this->additionalFields = $additionalFields;
        }
    }

    /**
     * Method stores th uploaded files
     */
    private function _storeDocumentFiles() {
        $log = Zend_Registry::get('Zend_Log');

        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();

        if ($upload->isUploaded()) {
            $log->info("Fileupload of: " . count($files) . " possible files => Fulltext is '1'.");
            $this->fulltext = "1";
            $document = new Opus_Document();
            $document->setType($this->documentType);
            $document->setServerState('temporary');

            foreach ($files AS $file => $fileValues) {
                if (!empty($fileValues['name'])) {
                    $log->info("uploaded: " . $fileValues['name']);
                    $docfile = $document->addFile();
                    //todo: default language should come from doc type
                    $docfile->setLanguage("eng");
                    $docfile->setFromPost($fileValues);
                }
            }
            $this->documentId = $document->store();
            $log->info("The corresponding doucment ID is: " . $this->documentId);
        } else {
            $log->info("No file uploaded: => Fulltext is NOT given.");
        }
    }

    /**
     * Methodgets the current form and finds out which fields has to be edded or deleted
     * @param Publish_Form_PublishingSecond $form
     * @return <View>
     */
    private function _getExtendedForm($form, $postData=null) {
        $log = Zend_Registry::get('Zend_Log');

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

        $currentNumber = $form->getElement('countMore' . $fieldName)->getValue();
        if ($workflow == "add") {
            //show one more fields
            $currentNumber = (int) $currentNumber + 1;
        } else {
            if ($currentNumber > 0) {
                //remove one more field, only down to 0
                $currentNumber = (int) $currentNumber - 1;
            }
        }

        //set the increased value for the pressed button and create a new form
        $this->additionalFields[$fieldName] = $currentNumber;

        $form = new Publish_Form_PublishingSecond($this->documentType, $this->documentId, $this->fulltext, $this->additionalFields, $postData);
        $action_url = $this->view->url(array('controller' => 'form', 'action' => 'check'));
        $form->setAction($action_url);
        $this->view->action_url = $action_url;
        $this->_setViewVariables($form);

        return $this->render($this->documentType);
    }

    /**
     * Method deletes elements like submit buttons before sending the form
     * @param <PublishingSecond> $formToSave
     * @param <PublishingSecond> $baseForm
     * @return <PublishingSecond>
     */
    private function _removeUnsavableElements($formToSave, $baseForm) {
        $log = Zend_Registry::get('Zend_Log');
        foreach ($formToSave->getElements() as $element) {
            if ($element->getValue() == "" || $element->getType() == "Zend_Form_Element_Submit" || $element->getType() == "Zend_Form_Element_Hidden") {

                $formToSave->removeElement($element->getName());
                $log->debug("remove " . $element->getName() . " from depositForm!");
            }
        }
        $docid = $formToSave->createElement("hidden", 'documentId');
        $docid->setValue($baseForm->getElement('documentId')->getValue());
        $docid->removeDecorator('Label');

        $doctype = $formToSave->createElement('hidden', 'documentType');
        $doctype->setValue($baseForm->getElement('documentType')->getValue());
        $doctype->removeDecorator('Label');

        $fulltext = $formToSave->createElement('hidden', 'fullText');
        $fulltext->setValue($baseForm->getElement('fullText')->getValue());
        $fulltext->removeDecorator('Label');

        $deposit = $formToSave->createElement('submit', 'Abspeichern');
        $formToSave->addElements(array($docid, $doctype, $fulltext, $deposit));

        return $formToSave;
    }

}