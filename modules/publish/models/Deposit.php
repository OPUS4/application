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
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_Model_Deposit {

    public $document;
    public $externalFields;
    public $documentData;
    public $projects = array();
    public $log;

    public function __construct($documentId = null, $documentType = null, $documentData = null) {
        if (isset($documentId) && !empty($documentId))
            $this->document = new Opus_Document($documentId);

        else
            $this->document = new Opus_Document();


        if (isset($documentType)) {
            $docType = $this->document->getField('Type');
            $docType->setValue($documentType);
            $this->document->store();
        }

        if (isset($documentData))
            $this->documentData = $documentData;

        $this->externalFields = $this->document->getAllExternalFields();

        $this->log = Zend_Registry::get('Zend_Log');

        $this->_storeDocumentData();
    }

    public function getDocument() {
        return $this->document;
    }

    public function getDocProjects() {
        return $this->projects;
    }

    private function _storeDocumentData() {

        foreach ($this->documentData as $dataKey => $dataValue) {
            
            $datasetType = $this->_getDatasetType($dataKey);

            if (isset($dataValue)) {

                if (isset($datasetType) && !empty($datasetType)) {
                    $this->log->info("Wanna store a " . $datasetType . "!");
                    $this->log->info("dataKey: " . $dataKey . " AND dataValue " . $dataValue);
                    $storeMethod = "_prepare" . $datasetType . "Object";
                    
                    $this->$storeMethod($dataKey, $dataValue);
                    
                } else {
                    $this->log->info("wanna store something else...");
                    if (array_key_exists($dataKey, $this->externalFields)) {

                        if ($dataKey === 'Language') {
                            $file = $this->document->getFile();
                            $file->setLanguage($dataValue);
                        }
                        // store an external field with adder
                        $function = "add" . $dataKey;
                        $this->log->debug("external field with adder function: " . $function);
                        $addedValue = $this->document->$function();
                        $addedValue->setValue($dataValue);
                        $this->log->debug("with value: " . $dataValue);
                    } else {
                        //store an internal field with setter
                        $function = "set" . $dataKey;
                        $this->log->debug("internal field with setter function: " . $function);
                        $addedValue = $this->document->$function($dataValue);
                        $this->log->debug("with value: " . $dataValue);
                    }
                }
            }
        }
    }

    /**
     * get the dataset type of the current post data key (used to store the post data in db)
     * @param <String> $postDataKey
     * @return <String> Type or ""
     */
    private function _getDatasetType($postDataKey) {
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
     * @param <Array> $this->documentData
     * @param <String> $dataKey current Element of formValues
     * @param <Array> $externalFields
     * @return <Array> $formValues
     */
    private function _preparePersonObject($dataKey, $dataValue) {

        if ($dataValue !== "") {
            $person = new Opus_Person();
            $first = "FirstName";
            $last = "LastName";
            $firstPos = stripos($dataKey, $first);
            $lastPos = stripos($dataKey, $last);

            if ($firstPos != false) {
                //store first name of a person
                $counter = (int) substr($dataKey, $firstPos - 1, $firstPos);
                if ($counter >= 1) {
                    //remove the counter at the end of the field name
                    $personType = substr($dataKey, 0, $firstPos - 1);
                } else
                    $personType = substr($dataKey, 0, $firstPos);

                $this->_storePersonObject("first", $counter, $person, $personType, $dataValue);
            } else if ($lastPos != false) {
                //store last name of a person
                $counter = (int) substr($dataKey, $lastPos - 1, $lastPos);
                //$log->debug("counter at the end: " . $counter);
                if ($counter >= 1) {
                    //remove the counter at the end of the field name
                    $personType = substr($dataKey, 0, $lastPos - 1);
                } else
                    $personType = substr($dataKey, 0, $lastPos);

                $this->_storePersonObject("last", $counter, $person, $personType, $dataValue);
            }
        }
    }

    /**
     * method to store a prepared person object
     * @param <String> $workflow
     * @param <Opus_Person> $person
     * @param <String> $personType
     * @param <Opus_Document> $this->document
     * @param <Array> $this->documentData
     * @param <String> $key
     * @return <Array> formValues
     */
    private function _storePersonObject($workflow, $counter, $person, $personType, $dataValue) {

        switch ($workflow) {

            case 'first' :
                $this->log->info("personType: " . $personType);
                $this->log->info("1) set first name: " . $dataValue);
                $person->setFirstName($dataValue);

                if ($counter != 0) {
                    $lastNameKey = $personType . $counter . "LastName";
                    $this->log->info("2) set last name: " . $this->documentData[$lastNameKey]);
                    $person->setLastName($this->documentData[$lastNameKey]);
                } else {
                    $lastNameKey = $personType . "LastName";
                    $this->log->info("2) set last name: " . $this->documentData[$lastNameKey]);
                    $person->setLastName($this->documentData[$lastNameKey]);
                }

                $addFunction = "add" . $personType;
                $this->document->$addFunction($person);

                //"delete" the second value for the name to avoid duplicates
                $this->log->info("delete lastNameKey: " . $lastNameKey . " with value " . $this->documentData[$lastNameKey]);
                $this->documentData[$lastNameKey] = "";

                break;

            case 'last' :
                $this->log->info("personType: " . $personType);
                $this->log->info("1) set last name: " . $dataValue);
                $person->setLastName($dataValue);

                if ($counter != 0) {
                    $firstNameKey = $personType . $counter . "FirstName";
                    $this->log->info("2) set first name: " . $this->documentData[$firstNameKey]);
                    $person->setFirstName($this->documentData[$firstNameKey]);
                } else {
                    $firstNameKey = $personType . "FirstName";
                    $this->log->info("2) set first name: " . $this->documentData[$firstNameKey]);
                    $person->setFirstName($this->documentData[$firstNameKey]);
                }

                $addFunction = "add" . $personType;
                $this->document->$addFunction($person);

                //"delete" the second value for the name to avoid duplicates
                $this->documentData[$firstNameKey] = "";
                break;
        }
    }

    /**
     *
     * @param <type> $document
     * @param <type> $this->documentData
     * @param <type> $dataKey
     * @param <type> $externalFields
     * @return <type>
     */
    private function _prepareTitleObject($dataKey, $dataValue) {

        if ($dataValue !== "") {

            $this->log->info("try to store title: " . $dataKey);
            $title = new Opus_Title();
            $language = "Language";
            $languagePos = stripos($dataKey, $language);

            if ($languagePos != false) {
                //store language of a title
                $counter = (int) substr($dataKey, $languagePos - 1, $languagePos);
                if ($counter >= 1) {
                    //remove the counter at the end of the field name
                    $titleType = substr($dataKey, 0, $languagePos - 1);
                } else {
                    $titleType = substr($dataKey, 0, $languagePos);
                }

                $this->_storeTitleObject("language", $counter, $title, $titleType, $dataValue);
            } else {
                //store value of a title
                $len = strlen($dataKey);
                $counter = (int) substr($dataKey, $len - 1, $len);

                if ($counter >= 1) {
                    //remove the counter at the end of the field name
                    $titleType = substr($dataKey, 0, $len - 1);
                } else {
                    $titleType = substr($dataKey, 0, $len);
                }
                $this->_storeTitleObject("value", $counter, $title, $titleType, $dataValue);
            }
        }
    }

    /**
     *
     * @param <type> $workflow
     * @param <type> $counter
     * @param <type> $title
     * @param <type> $titleType
     * @param <type> $this->document
     * @param <type> $dataValue
     * @param <type> $this->documentDataformValues
     * @return string
     */
    private function _storeTitleObject($workflow, $counter, $title, $titleType, $dataValue) {

        switch ($workflow) {
            case 'Language' :
                $this->log->info("titleType: " . $titleType);

                $this->log->info("1) set language: " . $dataValue);
                $title->setLanguage($dataValue);

                $valueKey = $titleType . $counter;
                $this->log->info("2) set value: " . $this->documentData[$valueKey]);
                $title->setValue($this->documentData[$valueKey]);

                $addFunction = "add" . $titleType;
                $this->document->$addFunction($title);

                //"delete" the second value for the title to avoid duplicates
                $this->documentData[$valueKey] = "";
                break;

            case 'value' :
                $this->log->info("titleType: " . $titleType);
                $this->log->info("1) set value: " . $dataValue);
                $title->setValue($dataValue);

                $languageKey = $titleType . $counter . "Language";
                $this->log->info("2) set language: " . $this->documentData[$languageKey]);
                $title->setLanguage($this->documentData[$languageKey]);

                $addFunction = "add" . $titleType;
                $this->document->$addFunction($title);

                //"delete" the second value for the title to avoid duplicates
                $this->documentData[$languageKey] = "";
                break;
        }
    }

    /**
     * method to prepare a subject object for storing
     * @param <Opus_Document> $this->document
     * @param <Array> $formValues
     * @param <String> $dataKey current Element of formValues
     * @param <Array> $externalFields
     * @return <Array> $formValues
     */
    private function _prepareSubjectObject($dataKey, $dataValue) {

        if ($dataValue == "") {
            $this->log->debug("Subject already stored.");
        } else {
            $this->log->debug("try to store subject: " . $dataKey);
            if (strstr($dataKey, "Swd")) {
                $subject = new Opus_SubjectSwd();
                $this->log->debug("subject is a swd subject.");
            } else if (strstr($dataKey, "MSC")) {

                $this->log->debug("subject is a MSC subject and has to be stored as a Collection.");

                $this->_storeCollectionObject('msc', $dataValue);

                $subject = new Opus_Subject();
                $this->log->debug("subject has also be stored as subject.");
            } else {
                $subject = new Opus_Subject();
                $this->log->debug("subject is a uncontrolled or other subject.");
            }

            $len = strlen($dataKey);
            $counter = (int) substr($dataKey, $len - 1, $len);
            $this->log->debug("counter: " . $counter);

            if ($counter >= 1)
            //remove the counter at the end of the field name
                $subjectType = substr($dataKey, 0, $len - 1);
            else
                $subjectType = substr($dataKey, 0, $len);

            $this->log->debug("subjectType: " . $subjectType);
            $this->log->debug("set value: " . $dataValue);
            $subject->setValue($dataValue);

            $addFunction = "add" . $subjectType;
            $this->log->debug("addfunction: " . $addFunction);
            $this->document->$addFunction($subject);
        }
    }

    /**
     * method to prepare a note object for storing
     * @param <Opus_Document> $this->document
     * @param <Array> $formValues
     * @param <String> $key current Element of formValues
     * @param <Array> $externalFields
     * @return <Array> $formValues
     */
    private function _prepareNoteObject($dataKey, $dataValue) {

        if ($dataValue == "") {
            $this->log->debug("Note already stored.");
        } else {
            $this->log->debug("try to store note: " . $dataKey);
            $note = new Opus_Note();

            $this->log->debug("set value: " . $dataValue);
            $note->setMessage($dataValue);

            $note->setVisibility("private");
            $addFunction = "addNote";
            $this->log->debug("addfunction: " . $addFunction);
            $this->document->$addFunction($note);
        }
    }

    /**
     * method to prepare a Collection object for storing
     * @param <Opus_Document> $this->document
     * @param <Array> $formValues
     * @param <String> $dataKey current Element of formValues
     * @param <Array> $externalFields
     * @return <Array> $formValues
     * @throws Publish_Model_OpusServerException
     */
    private function _prepareCollectionObject($dataKey, $dataValue) {

        if ($dataValue == "") {
            $this->log->debug("Collection already stored.");
        } else {
            $this->log->debug("try to store Collection:");

            if (strstr($dataKey, "Project")) 
                $this->_storeCollectionObject('projects', $dataValue);
            
            else if (strstr($dataKey, "Institute")) 
                    $this->_storeCollectionObject('institutes', $dataValue);
        }
    }

    private function _storeCollectionObject($collectionRole, $dataValue) {
        $role = Opus_CollectionRole::fetchByOaiName($collectionRole);
        if (isset($role)) {
            $this->log->debug("Role: " . $role);
            
            if ($collectionRole === 'institutes')
                $collArray = Opus_Collection::fetchCollectionsByRoleName($role->getId(), $dataValue);
            else
                $collArray = Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $dataValue);

            $this->log->debug("Role ID: " . $role->getId() . ", value: " . $dataValue);

                if ($collArray !== null && count($collArray) <= 1) {

                    $this->document->addCollection($collArray[0]);

                    if (strstr($collectionRole, 'project')) {
                            $this->projects[] = $dataValue;
                            $this->log->debug("Project array for referee, extended by " . $dataValue);
                    }
                }
                else
                    throw new Publish_Model_OpusServerException("While trying to store " . $dataKey . " as Collection, an error occurred.
                        The method fetchCollectionsByRoleNumber returned an array with > 1 values. The " . $dataKey . " cannot be definitely assigned.");
        }
    }


    /**
     * method to prepare a Licence object for storing
     * @param <Opus_Document> $this->document
     * @param <Array> $formValues
     * @param <String> $key current Element of formValues
     * @param <Array> $externalFields
     * @return <Array> $formValues
     * @throws Publish_Model_OpusServerException
     */
    private function _prepareLicenceObject($dataKey, $dataValue) {

        if ($dataValue == "") {
            $this->log->debug("Licence already stored.");
        } else {

            $this->log->debug("try to store Licence: " . $dataKey);
            $licence = new Opus_Licence();

            $this->log->debug("set value: " . $dataValue);
            $licence->setNameLong($dataValue);

            $addFunction = "addLicence";
            $this->log->debug("addfunction: " . $addFunction);
            $this->document->$addFunction($licence);
        }
    }

}

?>
