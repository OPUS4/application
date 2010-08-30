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
    public $additionalFields;

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
        $this->view->subtitle = $this->view->translate('publish_controller_indexsub');
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

            $this->view->title = $this->view->translate('publish_controller_index');
            $this->view->subtitle = $this->view->translate($this->documentType);

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
                $document->setServerState('temporary');
                $docId = $document->store();
                $this->documentId = $docId;
                $log->info("The corresponding doucment ID is: " . $this->documentId);

                $docfile = $document->addFile();
                $docfile->setLanguage("eng");
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
            $this->setViewVariables($step2Form);
            $this->view->form = $step2Form;
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

            foreach ($postData AS $pk => $pv) {
                $log->debug("postdata: " . $pk . " => " . $pv);
            }
            //read ans save the most important values
            $this->documentType = $postData['documentType'];
            $this->documentId = $postData['documentId'];
            $fulltext = $postData['fullText'];

            //get out the additional fields
            $additionalFields = array();
            foreach ($postData AS $element => $value) {
                if (substr($element, 0, 9) == "countMore") {
                    $key = substr($element, 9);
                    $log->debug("Add Key to additionalFields: " . $key . " => " . $value);
                    $additionalFields[$key] = (int) $value;
                }
            }
            $this->additionalFields = $additionalFields;

            //create the proper form and populate all needed values
            $form = new Publish_Form_PublishingSecond($this->documentType, $this->documentId, $fulltext, $this->additionalFields, $postData);
            $action_url = $this->view->url(array('controller' => 'index', 'action' => 'check'));
            $form->setAction($action_url);
            $form->populate($postData);

            if (!$form->send->isChecked()) {
                $this->view->title = $this->view->translate('publish_controller_index');
                $this->view->subtitle = $this->view->translate($this->documentType);
                $log->debug("A BUTTON (NOT SEND) WAS PRESSED!!!!!!!!!!!!!!!!!");
                //a button was pressed, but not the send button => add / remove fields
                //RENDER specific documentType.phtml
                $this->_helper->viewRenderer($this->documentType);
                $pressedButtonName = $this->getPressedButton($form);

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

                //call help funtion to render the form for specific view
                $this->setViewVariables($form);
                return $this->render($this->documentType);
            } else {
                //a button was pressed and it was send => check the form
                //RENDER specific documentType.phtml
                $this->view->title = $this->view->translate('publish_controller_check');
                $this->view->subtitle = $this->view->translate($this->documentType);

                if (!$form->isValid($this->getRequest()->getPost())) {
                    $log->debug("NOW CHECK THE ERROR CASE!!!!!!!!!!!!!!!!!");
                    //variables NOT valid
                    $this->view->form = $form;
                    //call help funtion to render the form for specific view
                    $this->setViewVariables($form);

                    return $this->render($this->documentType);
                } else {
                    //variables VALID
                    //RENDER check.phtml
                    $this->view->title = $this->view->translate('publish_controller_check');
                    $log->debug("Variables are valid!");
                    
                    $this->view->formValues = $form->getValues();

                    //finally: deposit the data!
                    $depositForm = new Publish_Form_PublishingSecond($this->documentType, $this->documentId, $fulltext, $this->additionalFields, $form->getValues());
                    $action_url = $this->view->url(array('controller' => 'index', 'action' => 'deposit'));
                    $depositForm->setAction($action_url);
                    $depositForm->populate($form->getValues());
                    foreach ($depositForm->getElements() as $element) {
                        if ($element->getValue() == "" || $element->getType() == "Zend_Form_Element_Submit" || $element->getType() == "Zend_Form_Element_Hidden") {

                            $depositForm->removeElement($element->getName());
                            $log->debug("remove " . $element->getName() . "from depositForm!");
                        }
                    }
                    $docId = $depositForm->createElement("hidden", 'documentId');
                    $docId->setValue($form->getElement('documentId')->getValue());

                    $docType = $depositForm->createElement('hidden', 'documentType');
                    $docType->setValue($form->getElement('documentType')->getValue());

                    $fullText = $depositForm->createElement('hidden', 'fullText');
                    $fullText->setValue($form->getElement('fullText')->getValue());
                    //$depositForm->addValues($formValues);
                    $deposit = $depositForm->createElement('submit', 'deposit');
                    $depositForm->addElements(array($docId, $docType, $fullText, $deposit));

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
            $log->debug("Method depositAction begins...");
            $postData = $this->getRequest()->getPost();

            //read ans save the most important values
            $this->documentType = $postData['documentType'];
            $fulltext = $postData['fullText'];
            if ($postData['documentId'] != "") {
                $this->documentId = $postData['documentId'];
                $document = new Opus_Document($this->documentId);
                $documentType = $document->getField('Type');
                $documentType->setValue($this->documentType);
                $id = $document->store();
                $log->debug("docID: " . $id);
            } else {
                $document = new Opus_Document();
                $documentType = $document->getField('Type');
                $documentType->setValue($this->documentType);
                $id = $document->store();
                $log->debug("docID: " . $id);
            }
            unset($postData["documentId"]);
            unset($postData["documentType"]);
            unset($postData["fullText"]);
            unset($postData["deposit"]);

            //get the available external fields of an document
            $externalFields = $document->getAllExternalFields();
            $log->debug("External fields loaded...");

            //save the post variables
            foreach ($postData as $key => $value) {
                $datasetType = $this->getDatasetType($key);
                if ($value != "") {
                    if ($datasetType != "") {
                        $log->debug("Wanna store a " . $datasetType . "...");
                        $storeMethod = "prepare" . $datasetType . "Object";
                        $postData = $this->$storeMethod($document, $postData, $key);
                        $log->debug($datasetType . " stored!");
                    } else {
                        $log->debug("wanna store something else...");
                        if (array_key_exists($key, $externalFields)) {
                            if ($key === 'Language') {
                                $file = $document->getFile();
                                $file->setLanguage($value);
                            }
                            // store an external field with adder
                            $function = "add" . $key;
                            $log->debug("adder function: " . $function);
                            $addedValue = $document->$function();
                            $addedValue->setValue($value);
                            $log->debug("with value: " . $value);
                        } else {
                            //store an internal field with setter
                            $function = "set" . $key;
                            $log->debug("setter function: " . $function);
                            $addedValue = $document->$function($value);
                            $log->debug("with value: " . $value);
                        }
                    }
                }
            }
            $document->setServerState('unpublished');
            $document->store();
        }
    }

    /**
     * method to set the different variables and arrays for the view and the templates
     * @param <Zend_Form> $form
     */
    private function setViewVariables($form) {
        $log = Zend_Registry::get('Zend_Log');
        $log->debug("Method setViewVariables begins...");

        //show errors
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
            $log->debug("current Element: " . $currentElement);
            $singleField = $currentElement . "_";
            
            $elementAttributes = $form->getElementAttributes($currentElement); //array
//            foreach ($elementAttributes as $key1 => $value1) {
//                $log->debug($key1 . " => " . $value1);
//            }
            $this->view->$singleField = $elementAttributes;
            
            $log->debug("singlefield " . $singleField . " filled");

            //also support more difficult templates for "expert admins"
            $this->view->$currentElement = $form->getElement($currentElement)->getValue();
            $name = $currentElement . "_label";
            $this->view->$name = $this->view->translate($form->getElement($currentElement)->getLabel());
            if (isset($errors[$currentElement]))
                foreach ($errors[$currentElement] as $error => $errorMessage) {
                    //error values
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
    private function getPressedButton($form) {
        $log = Zend_Registry::get('Zend_Log');
        $log->debug("MethodgetPressedButton begins...");
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
            throw new Exception("No pressed button found! Possibly the values of the buttons are not equal in the view and Publish class.");
        //todo: which exeption to choose?
        else
            return $pressedButtonName;
    }

    /**
     * get the dataset type of the current post data key (used to store the post data in db)
     * @param <String> $postDataKey
     * @return <String> Type or ""
     */
    private function getDatasetType($postDataKey) {
        if (strstr($postDataKey, "Person"))
            return "Person";
        else if (strstr($postDataKey, "Title"))
            return "Title";
        else if (strstr($postDataKey, "Subject"))
            return "Subject";
        else if (strstr($postDataKey, "Note"))
            return "Note";
        else if (strstr($postDataKey, "Project") || strstr($postDataKey, "Institute"))
            return "Collection";
        else if (strstr($postDataKey, "Licence"))
            return "Licence";
        else
            return "";
    }

    /**
     * method to prepare a person object for storing
     * @param <Opus_Document> $document
     * @param <Array> $formValues
     * @param <String> $key current Element of formValues
     * @param <Array> $externalFields
     * @return <Array> $formValues 
     */
    private function preparePersonObject($document, $formValues, $key) {
        $log = Zend_Registry::get('Zend_Log');
        if ($formValues[$key] == "") {
            $log->debug("Person already stored.");
            return $formValues;
        } else {
            $log->debug("try to store person: " . $key);
            $person = new Opus_Person();
            $first = "FirstName";
            $last = "LastName";
            $firstPos = stripos($key, $first);
            $lastPos = stripos($key, $last);

            if ($firstPos != false) {
                //store first name of a person
                $counter = (int) substr($key, $firstPos - 1, $firstPos);
                $log->debug("counter: " . $counter);
                if ($counter >= 1) {
                    //remove the counter at the end of the field name
                    $personType = substr($key, 0, $firstPos - 1);
                } else {
                    $personType = substr($key, 0, $firstPos);
                }
                return $this->storePersonObject("first", $counter, $person, $personType, $document, $formValues[$key], $formValues);
            } else if ($lastPos != false) {
                //store last name of a person
                $counter = (int) substr($key, $lastPos - 1, $lastPos);
                //$log->debug("counter at the end: " . $counter);
                if ($counter >= 1) {
                    //remove the counter at the end of the field name
                    $personType = substr($key, 0, $lastPos - 1);
                } else {
                    $personType = substr($key, 0, $lastPos);
                }
                return $this->storePersonObject("last", $counter, $person, $personType, $document, $formValues[$key], $formValues);
            }
        }
    }

    /**
     * method to store a prepared person object
     * @param <String> $workflow
     * @param <Opus_Person> $person
     * @param <String> $personType
     * @param <Opus_Document> $document
     * @param <Array> $formValues
     * @param <String> $key
     * @return <Array> formValues
     */
    private function storePersonObject($workflow, $counter, $person, $personType, $document, $value, $formValues) {
        $log = Zend_Registry::get('Zend_Log');
        if ($workflow === "first") {
            $log->debug("personType: " . $personType);
            $log->debug("1) set first name: " . $value);
            $person->setFirstName($value);

            if ($counter != 0) {
                $lastNameKey = $personType . $counter . "LastName";
                $log->debug("2) set last name: " . $formValues[$lastNameKey]);
                $person->setLastName($formValues[$lastNameKey]);
            } else {
                $lastNameKey = $personType . "LastName";
                $log->debug("2) set last name: " . $formValues[$lastNameKey]);
                $person->setLastName($formValues[$lastNameKey]);
            }

            $addFunction = "add" . $personType;
            $log->debug("addfunction: " . $addFunction);
            $document->$addFunction($person);

            //"delete" the second value for the name to avoid duplicates
            $formValues[$lastNameKey] = "";
            return $formValues;
        } else if ($workflow === "last") {
            $log->debug("personType: " . $personType);
            $log->debug("1) set last name: " . $value);
            $person->setLastName($value);

            if ($counter != 0) {
                $firstNameKey = $personType . $counter . "FirstName";
                $log->debug("2) set first name: " . $formValues[$firstNameKey]);
                $person->setFirstName($formValues[$firstNameKey]);
            } else {
                $firstNameKey = $personType . "FirstName";
                $log->debug("2) set first name: " . $formValues[$firstNameKey]);
                $person->setFirstName($formValues[$firstNameKey]);
            }

            $addFunction = "add" . $personType;
            $log->debug("addfunction: " . $addFunction);
            $document->$addFunction($person);

            //"delete" the second value for the name to avoid duplicates
            $formValues[$firstNameKey] = "";
            return $formValues;
        }
    }

    /**
     *
     * @param <type> $document
     * @param <type> $formValues
     * @param <type> $key
     * @param <type> $externalFields
     * @return <type>
     */
    private function prepareTitleObject($document, $formValues, $key) {
        $log = Zend_Registry::get('Zend_Log');
        if ($formValues[$key] == "") {
            $log->debug("Title already stored.");
            return $formValues;
        } else {
            $log->debug("try to store title: " . $key);
            $title = new Opus_Title();
            $language = "Language";
            $languagePos = stripos($key, $language);

            if ($languagePos != false) {
                //store language of a title
                $counter = (int) substr($key, $languagePos - 1, $languagePos);
                $log->debug("counter: " . $counter);
                if ($counter >= 1) {
                    //remove the counter at the end of the field name
                    $titleType = substr($key, 0, $languagePos - 1);
                } else {
                    $titleType = substr($key, 0, $languagePos);
                }
                return $this->storeTitleObject("language", $counter, $title, $titleType, $document, $formValues[$key], $formValues);
            } else {
                //store value of a title
                $len = strlen($key);
                $counter = (int) substr($key, $len - 1, $len);
                $log->debug("counter: " . $counter);
                if ($counter >= 1) {
                    //remove the counter at the end of the field name
                    $titleType = substr($key, 0, $len - 1);
                } else {
                    $titleType = substr($key, 0, $len);
                }
                return $this->storeTitleObject("value", $counter, $title, $titleType, $document, $formValues[$key], $formValues);
            }
        }
    }

    /**
     *
     * @param <type> $workflow
     * @param <type> $counter
     * @param <type> $title
     * @param <type> $titleType
     * @param <type> $document
     * @param <type> $value
     * @param <type> $formValues
     * @return string
     */
    private function storeTitleObject($workflow, $counter, $title, $titleType, $document, $value, $formValues) {
        $log = Zend_Registry::get('Zend_Log');
        if ($workflow === "Language") {
            $log->debug("titleType: " . $titleType);
            
            $log->debug("1) set language: " . $value);
            $title->setLanguage($value);

            $valueKey = $titleType . $counter;
            $log->debug("2) set value: " . $formValues[$valueKey]);
            $title->setValue($formValues[$valueKey]);

            $addFunction = "add" . $titleType;
            $document->$addFunction($title);

            //"delete" the second value for the name to avoid duplicates
            $formValues[$valueKey] = "";
            return $formValues;
        } else if ($workflow === "value") {
            $log->debug("titleType: " . $titleType);
            $log->debug("1) set value: " . $value);
            $title->setValue($value);

            $languageKey = $titleType . $counter . "Language";
            $log->debug("2) set language: " . $formValues[$languageKey]);
            $title->setLanguage($formValues[$languageKey]);

            $addFunction = "add" . $titleType;
            $document->$addFunction($title);

            //"delete" the second value for the name to avoid duplicates
            $formValues[$languageKey] = "";
            return $formValues;
        }
    }

    /**
     * method to prepare a subject object for storing
     * @param <Opus_Document> $document
     * @param <Array> $formValues
     * @param <String> $key current Element of formValues
     * @param <Array> $externalFields
     * @return <Array> $formValues
     */
    private function prepareSubjectObject($document, $formValues, $key) {
        $log = Zend_Registry::get('Zend_Log');
        if ($formValues[$key] == "") {
            $log->debug("Subject already stored.");
            return $formValues;
        } else {
            $log->debug("try to store subject: " . $key);
            if (strstr($key, "Swd")) {
                $subject = new Opus_SubjectSwd();
                $log->debug("subject is a swd subject.");
            } else if (strstr($key, "MSC")) {

                $log->debug("subject is a MSC subject and has to be stored as a Collection.");
                $value = $formValues[$key];
                $role = Opus_CollectionRole::fetchByOaiName('msc');
                $log->debug("Role: " . $role);
                $collArray = Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $value);
                $log->debug("Role ID: " . $role->getId() . ", value: " . $value);
               
                if (count($collArray) === 1) {
                    $document->addCollection($collArray[0]);
                    //return;
                } else
                    throw new Publish_Model_OpusServerException("While trying to store " . $key . " as Collection, an error occurred.
                        The method fetchCollectionsByRoleNumber returned an array with > 1 values. The " . $key . " cannot be definitely assigned.");
                $subject = new Opus_Subject();
                $log->debug("subject has also be stored as subject.");

            } else {
                $subject = new Opus_Subject();
                $log->debug("subject is a uncontrolled or other subject.");
            }

            $len = strlen($key);
            $counter = (int) substr($key, $len - 1, $len);
            $log->debug("counter: " . $counter);

            if ($counter >= 1)
            //remove the counter at the end of the field name
                $subjectType = substr($key, 0, $len - 1);
            else
                $subjectType = substr($key, 0, $len);

            return $this->storeSubjectObject($subject, $subjectType, $document, $formValues[$key], $formValues);
        }
    }

    /**
     * method to store a prepared subject object
     * @param <String> $workflow
     * @param <Opus_Subject> $subject
     * @param <String> $subjectType
     * @param <Opus_Document> $document
     * @param <Array> $formValues
     * @param <String> $key
     * @return <Array> formValues
     */
    private function storeSubjectObject($subject, $subjectType, $document, $value, $formValues) {
        $log = Zend_Registry::get('Zend_Log');
        $log->debug("subjectType: " . $subjectType);
        $log->debug("set value: " . $value);
        $subject->setValue($value);

        $addFunction = "add" . $subjectType;
        $log->debug("addfunction: " . $addFunction);
        $document->$addFunction($subject);

        return $formValues;
    }

    /**
     * method to prepare a note object for storing
     * @param <Opus_Document> $document
     * @param <Array> $formValues
     * @param <String> $key current Element of formValues
     * @param <Array> $externalFields
     * @return <Array> $formValues
     */
    private function prepareNoteObject($document, $formValues, $key) {
        $log = Zend_Registry::get('Zend_Log');
        if ($formValues[$key] == "") {
            $log->debug("Note already stored.");
            return $formValues;
        } else {
            $log->debug("try to store note: " . $key);
            $note = new Opus_Note();

            return $this->storeNoteObject($note, $document, $formValues[$key], $formValues);
        }
    }

    /**
     * method to store a prepared note object
     * @param <String> $workflow
     * @param <Opus_Subject> $note
     * @param <String> $noteType
     * @param <Opus_Document> $document
     * @param <Array> $formValues
     * @param <String> $key
     * @return <Array> formValues
     */
    private function storeNoteObject($note, $document, $value, $formValues) {
        $log = Zend_Registry::get('Zend_Log');
        $log->debug("set value: " . $value);
        $note->setMessage($value);

        $note->setVisibility("private");
        $addFunction = "addNote";
        $log->debug("addfunction: " . $addFunction);
        $document->$addFunction($note);

        return $formValues;
    }

    /**
     * method to prepare a Collection object for storing
     * @param <Opus_Document> $document
     * @param <Array> $formValues
     * @param <String> $key current Element of formValues
     * @param <Array> $externalFields
     * @return <Array> $formValues
     * @throws Publish_Model_OpusServerException
     */
    private function prepareCollectionObject($document, $formValues, $key) {
        $log = Zend_Registry::get('Zend_Log');
        if ($formValues[$key] == "") {
            $log->debug("Collection already stored.");
            return $formValues;
        } else {
            $log->debug("try to store Collection: " . $key . " with value " . $formValues[$key]);

            return $this->storeCollectionObject($document, $formValues, $key);
        }
    }

    /**
     * method to store a prepared Collection object
     * @param <String> $workflow
     * @param <Opus_Subject> $note
     * @param <String> $noteType
     * @param <Opus_Document> $document
     * @param <Array> $formValues
     * @param <String> $key
     * @return <Array> formValues
     */
    private function storeCollectionObject($document, $formValues, $key) {
        $log = Zend_Registry::get('Zend_Log');
        $value = $formValues[$key];

        if (strstr($key, "Project")) {
            $role = Opus_CollectionRole::fetchByOaiName('projects');
            $log->debug("Role: " . $role);
            $collArray = Opus_Collection::fetchCollectionsByRoleName($role->getId(), $value);
            $log->debug("Role ID: " . $role->getId() . ", value: " . $value);

            if ($collArray !== null && count($collArray) <= 1)
                $document->addCollection($collArray[0]);
            else
                throw new Publish_Model_OpusServerException("While trying to store " . $key . " as Collection, an error occurred.
                        The method fetchCollectionsByRoleNumber returned an array with > 1 values. The " . $key . " cannot be definitely assigned.");
        }
        else if (strstr($key, "Institute")) {
            $role = Opus_CollectionRole::fetchByOaiName('instituts');
            $collArray = Opus_Collection::fetchCollectionsByRoleName($role->getId(), $value);
            if (count($collArray) <= 1)
                $document->addCollection($collArray[0]);
            else
                throw new Publish_Model_OpusServerException("While trying to store " . $key . " as Collection, an error occurred.
                        The method fetchCollectionsByRoleNumber returned an array with > 1 values. The " . $key . " cannot be definitely assigned.");
        }
        return $formValues;
    }

    /**
     * method to prepare a Licence object for storing
     * @param <Opus_Document> $document
     * @param <Array> $formValues
     * @param <String> $key current Element of formValues
     * @param <Array> $externalFields
     * @return <Array> $formValues
     * @throws Publish_Model_OpusServerException
     */
    private function prepareLicenceObject($document, $formValues, $key) {
        $log = Zend_Registry::get('Zend_Log');
        if ($formValues[$key] == "") {
            $log->debug("Licence already stored.");
            return $formValues;
        } else {
            $value = $formValues[$key];
            $log->debug("try to store Licence: " . $key);
            $licence = new Opus_Licence();

            return $this->storeLicenceObject($licence, $document, $value, $formValues);
        }
    }

    /**
     * method to store a prepared Licence object
     * @param <String> $workflow
     * @param <Opus_Subject> $note
     * @param <String> $noteType
     * @param <Opus_Document> $document
     * @param <Array> $formValues
     * @param <String> $key
     * @return <Array> formValues
     */
    private function storeLicenceObject($licence, $document, $value, $formValues) {
        $log = Zend_Registry::get('Zend_Log');
        $log->debug("set value: " . $value);
        $licence->setNameLong($value);

        $addFunction = "addLicence";
        $log->debug("addfunction: " . $addFunction);
        $document->$addFunction($licence);

        return $formValues;
    }

}

