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
 * @category    TODO
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
class Publish_2_IndexController extends Controller_Action {

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
        // STEP 1: CHOOSE DOCUMENT TYPE AND UPLOAD FILE
        $this->view->title = $this->view->translate('publish_controller_index');
        $form = new PublishingFirst();
        $this->logger("Module Publishing <=> PublishingFirst was created.");
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
        $this->view->title = $this->view->translate('publish_controller_index');

//        $persons = array();
//        $persons[] = array (
//            'FirstName' => '',
//            'LastName' => '',
//        );
//        $persons[] = array (
//            'FirstName' => '',
//            'LastName' => '',
//        );
        //$this->view->Persons = $persons;
        //check the input from step 1
        $step1Form = new PublishingFirst();
        if ($this->getRequest()->isPost() === true) {
            $data = $this->getRequest()->getPost();

            if (!$step1Form->isValid($this->getRequest()->getPost())) {
                $this->view->form = $step1Form;
                //show errors, go back to index
                return $this->render('index');
            }
            $this->documentType = $data['type'];

            //Flag for checking if fulltext of not => must be string, or else Zend_Form collaps
            $fulltext = "0";
            //store the file
            $upload = new Zend_File_Transfer_Adapter_Http();
            $files = $upload->getFileInfo();
            $file = $files['fileupload'];

            if (!empty($file['name'])) {
                $this->logger("A file was uploaded: " . $file['name'] . " => Fulltext is given.");
                $document = new Opus_Document();
                $document->setType($this->documentType);
                $docId = $document->store();
                $this->documentId = $docId;
                $this->logger("The corresponding doucment ID is: " . $this->documentId);

                $docfile = $document->addFile();
                $docfile->setFromPost($file);
                $document->store();
                $fulltext = "1";
            }
            else
                $this->logger("No file uploaded: => Fulltext is NOT given.");

            // STEP 2: BUILD THE FORM THAT DEPENDS ON THE DOC TYPE
            //
            //use a specified view for the document type
            $this->_helper->viewRenderer($this->documentType);
            $this->view->documentId = $this->documentId;

            //create the form
            $step2Form = new PublishingSecond($this->documentType, $fulltext, null);
            $action_url = $this->view->url(array('controller' => 'index', 'action' => 'check'));
            $step2Form->setAction($action_url);
            $step2Form->setMethod('post');

            $this->view->form = $step2Form;
        }
    }

    /**
     * displays and checks the publishing form contents and calls deposit to store the data
     * uses check_array
     * @return <type>
     */
    public function checkAction() {
        if ($this->getRequest()->isPost() === true) {
            $this->documentType = $this->getRequest()->getPost('documentType');
            $this->documentId = $this->getRequest()->getPost('documentId');
            $fulltext = $this->getRequest()->getPost('fullText');

            $form = new PublishingSecond($this->documentType, $fulltext, null);

            //check if variables are valid
            if (!$form->isValid($this->getRequest()->getPost())) {

                if (!$form->send->isChecked()) {
                    //find out which other submit than send was pressed => add more fields!!!
                    $foundPressedButton = false;
                    $pressedButton = "";
                    $pressedButtonName = "";
                    foreach ($form->getElements() AS $element) {
                        if ($element->getType() === 'Zend_Form_Element_Submit' && $element->isChecked()) {
                            $foundPressedButton = true;
                            $pressedButton = $element;
                            $pressedButtonName = $pressedButton->getName();
                            break;
                        }
                    }

                    if (!$foundPressedButton) {
                        throw new Exception("no pressed button found.");
                    }

                    $fieldName = substr($pressedButtonName, 7);
                    echo "Fieldname : " . $fieldName;
                    //hidden field has the allowed value for counting the added fields
                    //can be *
                    if (!is_object($form->getElement('countMore'.$fieldName))) {
                        $msg = "countMore$fieldName\n";
                        $msg = $msg . var_dump($form->getElement('countMore'.$fieldName), TRUE);
                        throw new Exception($msg);
                    }
                    $allowedNumbers = $form->getElement('countMore'.$fieldName)->getValue();
                    //echo "Allowed Numbers : " .$hiddenCountField . " direct from form as hidden!!!!<br />";
                    if ($allowedNumbers == "*") $hiddenCountFields = 99;
                    else $allowedNumbers = (int) $allowedNumbers - 1;
                    //echo "HiddenCountField: " .$hiddenCountField . " after substract!!!!<br />";
                    $additionalFields = array();
                    //set the decreased value for the pressed button and create a new form
                    $additionalFields[$pressedButtonName] = $allowedNumbers;

                    $form1 = new PublishingSecond($this->documentType, $fulltext, $additionalFields);
                    
                } 

                $this->view->form = $form1;
                //show errors
                $errors = $form1->getMessages();

                    //regular and error values for placeholders
                    foreach ($form1->getElements() as $key => $value) {
                        //regular values
                        $this->view->$key = $form1->getElement($key)->getValue();
                        if (isset($errors[$key]))
                            foreach ($errors[$key] as $error => $errorMessage) {
                                //error values
                                $errorElement = $key . 'Error';
                                $this->view->$errorElement = $errorMessage;
                            }
                    }

                    return $this->render($this->documentType);
                }
             else {
                //summery the variables
                $this->view->title = $this->view->translate('publish_controller_check');

                //send form values to check view
                $formValues = $form1->getValues();
                $this->view->formValues = $formValues;

                //finally: deposit the data!
                $depositForm = new PublishingSecond($this->documentType, $fulltext);
                $action_url = $this->view->url(array('controller' => 'index', 'action' => 'deposit'));
                $depositForm->setAction($action_url);
                $depositForm->setMethod('post');

                foreach ($formValues as $key => $value) {
                    if ($key != 'send') {
                        $hidden = $depositForm->createElement('hidden', $key);
                        $hidden->setValue($value);
                        $depositForm->addElement($hidden);
                    } else {
                        //do not send the field "send" with the form
                        $depositForm->removeElement('send');
                    }
                }

                $hiddenDocId = $depositForm->createElement('hidden', 'documentType');
                $hiddenDocId->setValue($this->documentType);
                $hiddenDocId = $depositForm->createElement('hidden', 'documentId');
                $hiddenDocId->setValue($this->documentId);

                $deposit = $depositForm->createElement('submit', 'deposit');
                $depositForm->addElement($deposit)
                        ->addElement($hiddenDocId);

                //send form to view
                $this->view->form = $depositForm;
            }
        }
    }
    

    /**
     * stores a delivered form as document in the database
     * uses check_array
     */
    public function depositAction() {
        $this->view->title = $this->view->translate('publish_controller_index');
        $this->view->subtitle = $this->view->translate('publish_controller_deposit_successful');

        if ($this->getRequest()->isPost() === true) {

            $formValues = $this->getRequest()->getPost();
            $this->documentType = $this->getRequest()->getPost('documentType');
            $this->documentId = $this->getRequest()->getPost('documentId');

            $document = new Opus_Document($this->documentId);
            $document->setType($this->documentType);

            //delete values that do not concern the document (anymore)
            unset($formValues['documentType']);
            unset($formValues['documentId']);
            unset($formValues['deposit']);

            //get the available external fields of an document
            $externalFields = $document->getAllExternalFields();

            //save the post variables
            foreach ($formValues as $key => $value) {
                if (strstr($key, "Person")) {

                    if ($value != "") {
                        //store person object using help function
                        $formValues = $this->storePerson($document, $formValues, $key, $externalFields);
                    }
                } else {
                    if (in_array($key, $externalFields)) {
                        //echo "<b>external: " . $key . "</b><br>";
                        // store an external field with adder
                        $function = "add" . $key;
                        //echo "Try to add " . $key . " with function " . $function . "<br>";
                        $addedValue = $document->$function();
                        $addedValue->setValue($value);
                    } else {
                        //store an internal field with setter
                        //echo "internal: " . $key . "<br>";
                        $function = "set" . $key;
                        //echo "Try to set " . $key . " with function " . $function . "<br>";
                        $addedValue = $document->$function($value);
                    }
                }
            }
            $document->store();
        }
    }

    private function storePerson($document, $formValues, $key, $externalFields) {
        if ($formValues[$key] == "") {
            // unrequired Personfield is empty
            return $formValues;
        } else {
            //get all possible Person fields
            $availablePersons = array();
            foreach ($externalFields as $value) {
                if (strstr($value, "Person")) {
                    array_push($availablePersons, $value);
                }
            }
            $person = new Opus_Person();
            $first = "FirstName";
            $last = "LastName";
            $firstPos = stripos($key, $first);
            $lastPos = stripos($key, $last);

            if ($firstPos != false) {
                //FirstName is given
                echo "1) set first: " . $formValues[$key] . "<br>";
                $person->setFirstName($formValues[$key]);
                $personType = substr($key, 0, $firstPos);
                $lastNameKey = $personType . $last;
                echo "2) set last: " . $formValues[$lastNameKey] . "<br>";
                $person->setLastName($formValues[$lastNameKey]);
                $addFunction = "add" . $personType;
                $document->$addFunction($person);
                //"delete" the second value for the name to avoid duplicates
                $formValues[$lastNameKey] = "";
            } else if ($lastPos != false) {
                //LastName is given
                echo "1) set last: " . $formValues[$key] . "<br>";
                $person->setLastName($formValues[$key]);
                //personType example: PersonAuthor
                $personType = substr($key, 0, $lastPos);
                $firstNameKey = $personType . $first;
                echo "2) set first: " . $formValues[$firstNameKey] . "<br>";
                $person->setFirstName($formValues[$firstNameKey]);
                $addFunction = "add" . $personType;
                $document->$addFunction($person);
                //"delete" the second value for the name to avoid duplicates
                $formValues[$firstNameKey] = "";
            }

            return $formValues;
        }
    }

    protected function logger($message) {
        $registry = Zend_Registry::getInstance();
        $logger = $registry->get('Zend_Log');
        $logger->info(get_class($this) . ": $message");
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

