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
class Publish_IndexController extends Controller_Action {

    /**
     * @todo: extends Zend_Controller_Action ausreichend?
     */
    public $documentType;
    public $documentId;

    /**
     * Renders a list of available document types and provide upload field
     * STEP 1
     * @return void
     *
     */
    public function indexAction() {
        $log = Zend_Registry::get('Zend_Log');
        // STEP 1: CHOOSE DOCUMENT TYPE AND UPLOAD FILE
        $this->view->title = $this->view->translate('publish_controller_index');
        $form = new Publish_Form_PublishingFirst();
        $log->debug("Module Publishing <=> PublishingFirst was created.");
        $action_url = $this->view->url(array('controller' => 'index', 'action' => 'step2'));
        $form->setMethod('post');
        $form->setAction($action_url);
        $this->view->form = $form;
    }

    /**
     * XML GENERATING FORMS
     * used to store the upload and doc type
     * build the form that depends on the doc type and call render doctype template
     * STEP 2
     */
    public function step2Action() {
        $log = Zend_Registry::get('Zend_Log');

        $this->view->title = $this->view->translate('publish_controller_index');

        //check the input from step 1
        $step1Form = new Publish_Form_PublishingFirst();
        if ($this->getRequest()->isPost() === true) {
            $data = $this->getRequest()->getPost();

            if (!$step1Form->isValid($this->getRequest()->getPost())) {
                $this->view->form = $step1Form;
                //show errors, go back to index
                return $this->render('index');
            }
            $this->documentType = $data['type'];
            $this->documentId = "";

            //Flag for checking if fulltext of not => must be string, or else Zend_Form collaps
            $fulltext = "0";
            //store the file
            $upload = new Zend_File_Transfer_Adapter_Http();
            $files = $upload->getFileInfo();
            $file = $files['fileupload'];

            if (!empty($file['name'])) {
                $log->info("A file was uploaded: " . $file['name'] . " => Fulltext is given.");
                $document = new Opus_Document();
                $document->setType($this->documentType);
                $docId = $document->store();
                $this->documentId = $docId;
                $log->info("The corresponding doucment ID is: " . $this->documentId);

                $docfile = $document->addFile();
                $docfile->setFromPost($file);
                $document->store();
                $fulltext = "1";
            }
            else
                $log->info("No file uploaded: => Fulltext is NOT given.");

            $log->debug("TYPE -step2-: " . $this->documentType);
            $log->debug("ID -step2-: " . $this->documentId);
            $log->debug("Fulltext -step2-: " . $fulltext);

            // STEP 2: BUILD THE FORM THAT DEPENDS ON THE DOC TYPE
            //use a specified view for the document type
            $this->_helper->viewRenderer($this->documentType);

            //create the form
            $step2Form = new Publish_Form_PublishingSecond($this->documentType, $this->documentId, $fulltext, null, null);
            $action_url = $this->view->url(array('controller' => 'index', 'action' => 'check'));
            $step2Form->setAction($action_url);
            $step2Form->setMethod('post');
            $this->renderFormForView($step2Form);
            //$this->view->form = $step2Form;
        }
    }

    /**
     * displays and checks the publishing form contents and calls deposit to store the data
     * uses check_array
     * @return <type>
     */
    public function checkAction() {
        $log = Zend_Registry::get('Zend_Log');

        if ($this->getRequest()->isPost() === true) {

            $postData = $this->getRequest()->getPost();

            //read ans save the most important values
            $this->documentType = $postData['documentType'];
            $this->documentId = $postData['documentId'];
            $fulltext = $postData['fullText'];
            $log->debug("TYPE -check-: " . $this->documentType);
            $log->debug("ID -check-: " . $this->documentId);
            $log->debug("Fulltext -check-: " . $fulltext);

            //get out the additional fields
            $additionalFields = array();
            foreach ($postData AS $element => $value) {
                if (substr($element, 0, 9) == "countMore") {
                    $key = substr($element, 9);
                    $log->debug("Add Key to additionalFields: " . $key . " => " . $value);
                    $additionalFields[$key] = (int) $value;
                }
            }

            //create the proper form and populate all needed values
            $form = new Publish_Form_PublishingSecond($this->documentType, $this->documentId, $fulltext, $additionalFields, $postData);
            $action_url = $this->view->url(array('controller' => 'index', 'action' => 'check'));
            $form->setAction($action_url);
            $form->populate($postData);

            if (!$form->send->isChecked()) {
                $log->debug("A BUTTON WAS PRESSED!!!!!!!!!!!!!!!!!");
                //a button was pressed, but not the send button => add / remove fields
                //check which button other than send was pressed
                //RENDER specific documentType.phtml
                $this->_helper->viewRenderer($this->documentType);
                $pressedButton = "";
                $pressedButtonName = "";
                foreach ($form->getElements() AS $element) {
                    if ($element->getType() === 'Zend_Form_Element_Submit' && $element->isChecked()) {
                        $log->debug('Following Button Is Checked: ' . $element->getName());
                        $pressedButton = $element;
                        $pressedButtonName = $pressedButton->getName();
                        break;
                    }
                }
                $workflow = "";
                if (substr($pressedButtonName, 0, 7) == "addMore") {
                    $fieldName = substr($pressedButtonName, 7);
                    $workflow = "add";
                    $log->debug("Fieldname for addMore => " . $fieldName);
                } else if (substr($pressedButtonName, 0, 10) == "deleteMore") {
                    $fieldName = substr($pressedButtonName, 10);
                    $workflow = "delete";
                    $log->debug("Fieldname for deleteMore => " . $fieldName);
                }

                //hidden field has the allowed value for counting the added fields, can be *
                $currentNumber = $form->getElement('countMore' . $fieldName)->getValue();
                $log->debug("old current number: " . $currentNumber);
                if ($workflow == "add")
                //show one more fields
                    $currentNumber = (int) $currentNumber + 1;
                else
                if ($currentNumber > 0)
                //remove one more field, only down to 0
                    $currentNumber = (int) $currentNumber - 1;

                //set the increased value for the pressed button and create a new form
                $additionalFields[$fieldName] = $currentNumber;
                $log->debug("new current number: " . $currentNumber);

                //create the proper form and populate all needed values
                $form = new Publish_Form_PublishingSecond($this->documentType, $this->documentId, $fulltext, $additionalFields, $postData);
                $action_url = $this->view->url(array('controller' => 'index', 'action' => 'check'));
                $form->setAction($action_url);

                //$this->view->form = $form;
                //call help funtion to render the form for specific view
                $this->renderFormForView($form);

                $this->view->form = $form;
                //return $this->render($this->documentType);
            } else {
                //a button was pressed and it was send => check the form
                //RENDER specific documentType.phtml
                if (!$form->isValid($this->getRequest()->getPost())) {
                    $log->debug("NOW CHECK THE ERROR CASE!!!!!!!!!!!!!!!!!");
                    //variables NOT valid
                    $this->view->form = $form;
                    //call help funtion to render the form for specific view
                    $this->renderFormForView($form);

                    return $this->render($this->documentType);
                } else {
                    //variables VALID
                    //RENDER check.phtml
                    $this->view->title = $this->view->translate('publish_controller_check');

                    //send form values to check view
                    $formElements = $form->getElements();
                    $formValues = array();
                    foreach ($formElements as $element) {
                        if (!($element->getType() == 'Zend_Form_Element_Hidden') && !($element->getType() == 'Zend_Form_Element_Submit'))
                            $formValues[$element->getName()] = $element->getValue();
                    }
                    $this->view->formValues = $formValues;

                    //finally: deposit the data!
                    //$depositForm = new Publish_Form_PublishingSecond($this->documentType, $this->documentId, $fulltext, $additionalFields, $postData);
                    $action_url = $this->view->url(array('controller' => 'index', 'action' => 'deposit'));
//                    $depositForm->setAction($action_url);
//                    $depositForm->setMethod('post');


                    $depositForm = new Zend_Form();
                    $depositForm->setAction($action_url);
                    $deposit = $form->createElement('submit', 'deposit');
                    $depositForm->addElement($deposit);
                    foreach ($form->getValues() as $key => $value) {
                        if ($key != 'send') {
                            $hidden = $depositForm->createElement('hidden', $key);
                            $hidden->setValue($value);
                            $depositForm->addElement($hidden);
                        } else {
                            //do not send the field "send" with the form
                            $depositForm->removeElement('send');
                        }
                    }
                    //send form to view
                    $this->view->form = $depositForm;
                    $log->debug("Check was successful! Next step: deposit data!");
                }
            }
        }
    }

    /**
     * stores a delivered form as document in the database
     * uses check_array
     */
    public function depositAction() {
        $log = Zend_Registry::get('Zend_Log');
        $this->view->title = $this->view->translate('publish_controller_index');
        $this->view->subtitle = $this->view->translate('publish_controller_deposit_successful');

        if ($this->getRequest()->isPost() === true) {
            $postData = $this->getRequest()->getPost();
            $log->debug("Mehtod depositAction begins...");

            //read ans save the most important values
            $this->documentType = $postData['documentType'];
            $fulltext = $postData['fullText'];
            if (!isset($postData['documentId'])) {
                $this->documentId = $postData['documentId'];
                $document = new Opus_Document($this->documentId);
                $documentType = $document->getField('Type');
                $documentType->setValue($this->documentType);
                $id = $document->store();
            } else {
                $document = new Opus_Document();
                $documentType = $document->getField('Type');
                $documentType->setValue($this->documentType);
                $id = $document->store();
            }

            $log->debug("document generated with id " . $id);
            //delete values that do not concern the document (anymore)
            unset($postData['documentType']);
            unset($postData['documentId']);
            unset($postData['deposit']);

            //get the available external fields of an document
            $externalFields = $document->getAllExternalFields();
            $log->debug("External fields loaded...");

            //save the post variables
            foreach ($postData as $key => $value) {
                if (strstr($key, "Person")) {

                    if ($value != "") {
                        //store person object using help function
                        $log->debug("wanna store a person...");
                        $postData = $this->storePerson($document, $postData, $key, $externalFields);
                        $log->debug("person stored! ");
                    }
                } else {
                    $log->debug("wanna store something else...");
                    if (array_key_exists($key, $externalFields)) {
                        // store an external field with adder
                        $function = "add" . $key;
                        $log->debug("adder function: " . $function);
                        $addedValue = $document->$function();
                        $addedValue->setValue($value);
                        $log->debug("with value: " . $function);
                    } else {
                        //store an internal field with setter
                        $function = "set" . $key;
                        $log->debug("setter function: " . $function);
                        $addedValue = $document->$function($value);
                        $log->debug("with value: " . $function);
                    }
                }
            }
            $document->store();
        }
    }

    private function renderFormForView($form) {
        $log = Zend_Registry::get('Zend_Log');
        $log->debug("Method renderFormForView begins...");
        //show errors
        $errors = $form->getMessages();
        //regular and error values for placeholders
        foreach ($form->getElements() as $key => $value) {
            $pos = stripos($key, "FirstName");
            if ($pos != false) {
                $name = substr($key, 0, $pos);
            } else {
                $pos = stripos($key, "1");
                if ($pos != false)
                    $name = substr($key, 0, $pos);
                else
                    $name=$key;
            }
            $groupName = 'group' . $name;
            //translate the group name and gibe to the view
            $this->view->$name = $this->view->translate($name);
            $displayGroup = $form->getDisplayGroup($groupName);
            if ($displayGroup != null) {
                $log->debug(" --> GROUP <-- found: " . $groupName);
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
                $group["Fields"] = $groupFields;
                $group["Hiddens"] = $groupHiddens;
                $group["Buttons"] = $groupButtons;
                $this->view->$groupName = $group;
            }

            //regular values
            $this->view->$key = $form->getElement($key)->getValue();
            $name = $key . "_label";
            $this->view->$name = $this->view->translate($form->getElement($key)->getLabel());
            if (isset($errors[$key]))
                foreach ($errors[$key] as $error => $errorMessage) {
                    //error values
                    $errorElement = $key . 'Error';
                    $this->view->$errorElement = $errorMessage;
                }
        }
    }

    private function storePerson($document, $formValues, $key, $externalFields) {
        $log = Zend_Registry::get('Zend_Log');

        if ($formValues[$key] == "")
            return $formValues;
        else {
            //get all possible Person roles
            $personRoles = array();
            foreach ($externalFields as $value) {
                if (strstr($value, "Person")) {
                    array_push($personRoles, $value);
                }
            }
            $person = new Opus_Person();
            $first = "FirstName";
            $last = "LastName";
            $firstPos = stripos($key, $first);
            $log->debug("key: " . $key);
            $lastPos = stripos($key, $last);

            if ($firstPos != false) {
                //FirstName is given
                $log->debug("1) set first name: " . $formValues[$key]);
                $person->setFirstName($formValues[$key]);
                $personType = substr($key, 0, $firstPos);
                $log->debug("personType: " . $personType);
                $lastNameKey = $personType . $last;
                $log->debug("2) set last name: " . $formValues[$lastNameKey]);
                $person->setLastName($formValues[$lastNameKey]);
                $addFunction = "add" . $personType;
                $document->$addFunction($person);
                //"delete" the second value for the name to avoid duplicates
                $formValues[$lastNameKey] = "";
            } else if ($lastPos != false) {
                //LastName is given
                $log->debug("1) set last: " . $formValues[$key]);
                $person->setLastName($formValues[$key]);
                //personType example: PersonAuthor
                $personType = substr($key, 0, $lastPos);
                $firstNameKey = $personType . $first;
                $log->debug("2) set first: " . $formValues[$firstNameKey]);
                $person->setFirstName($formValues[$firstNameKey]);
                $addFunction = "add" . $personType;
                $document->$addFunction($person);
                //"delete" the second value for the name to avoid duplicates
                $formValues[$firstNameKey] = "";
            }

            return $formValues;
        }
    }

    protected function getPressedButton($form) {
        $pressedButton = "";
        foreach ($form->getElements() AS $element) {
            if ($element->getType() == 'Submit' && $element->isChecked()) {
                $pressedButton = $element;
            }
        }
        return $pressedButton;
    }

}

