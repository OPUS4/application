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
class Publish_DepositController extends Controller_Action {

    /**
     * @todo: extends Zend_Controller_Action ausreichend?
     */
    public $documentType;
    public $documentId;
    public $additionalFields;
    //projects are needed to inform referees
    public $projects = array();    

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
            unset($postData["Abspeichern"]);

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
            $docId = $document->store();
            $log->info("Document was sucessfully stored!");
            //finally send an emial to the referrer named in config.ini
            foreach($this->projects AS $p)
                    $log->debug("projekte: " . $p);
            $mail = new Mail_PublishNotification($docId, $this->projects, $this->view);
            if ($mail->send() === false)
                $log->err("email to referee could not be sended!");
            else
                $log->info("Referee has been informed via email.");
        }
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

                if ($collArray !== null && count($collArray) <= 1) {
                    $document->addCollection($collArray[0]);
                } else
                    throw new Publish_Model_OpusServerException("While trying to store " . $key . " as Collection, an error occurred." .
                            "The method fetchCollectionsByRoleNumber returned an array with > 1 values. The " . $key . " cannot be definitely assigned.");
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
            $collArray = Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $value);
            $log->debug("Role ID: " . $role->getId() . ", value: " . $value);

            if ($collArray !== null && count($collArray) <= 1) {
                $document->addCollection($collArray[0]);
                $this->projects[] = $value;
                $log->debug("Projects array for referee, extended by " . $value);
            }
            else
                throw new Publish_Model_OpusServerException("While trying to store " . $key . " as Collection, an error occurred.
                        The method fetchCollectionsByRoleNumber returned an array with > 1 values. The " . $key . " cannot be definitely assigned.");
        }
        else if (strstr($key, "Institute")) {
            $role = Opus_CollectionRole::fetchByOaiName('institutes');
            $log->debug("Role: " . $role);
            $collArray = Opus_Collection::fetchCollectionsByRoleName($role->getId(), $value);
            $log->debug("Role ID: " . $role->getId() . ", value: " . $value);
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

