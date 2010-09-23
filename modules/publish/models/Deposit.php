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

        if (isset($documentData)) {
            $this->documentData = $documentData;
        }

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
            $datasetType = $this->_getDatasetType($dataKey, $dataValue);

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

    /**
     * get the dataset type of the current post data key (used to store the post data in db)
     * @param <String> $dataKey
     * @return <String> Type or ""
     */
    private function _getDatasetType($dataKey, $dataValue) {
        if (strstr($dataKey, "Person"))
            return "Person";
        else if (strstr($dataKey, "Title"))
            return "Title";
        else if (strstr($dataKey, "Subject"))
            return "Subject";
        else if (strstr($dataKey, "Note"))
            return "Note";
        else if (strstr($dataKey, "Project") || strstr($dataKey, "Institute"))
            return "Collection";
        else if (strstr($dataKey, "Licence"))
            return "Licence";
        else
            return "";
    }

    private function getCounter($dataKey) {
        //always on last position
        return (substr($dataKey, -1, 1));
    }

    private function getObjectType($dataKey, $removeString) {
        $pos = strpos($dataKey, $removeString);
        if ($pos !== false)
            return substr($dataKey, 0, $pos);
    }

    private function _preparePersonObject($dataKey = null, $dataValue = null) {
        // $this->log->debug("_preparePersonObject()");
        //String can be changed here
        $first = "FirstName";
        $last = "LastName";
        $email = "Email";
        $birthplace = "PlaceOfBirth";
        $birthdate = "DateOfBirth";
        $academic = "AcademicTitle";

        $person = new Opus_Person();

        if (strstr($dataKey, $first))
            $type = $this->getObjectType($dataKey, $first);
        else
        if (strstr($dataKey, $last))
            $type = $this->getObjectType($dataKey, $last);

        if (isset($type)) {
            $this->log->debug("Person type:" . $type);

            $counter = (int) $this->getCounter($dataKey);
            $this->log->debug("counter: " . $counter);

            if ($this->documentData[$type . $last . $counter] == "") {
                $this->storeFirstName($person, $type, $first, $counter);
                $this->storeLastName($person, $type, $last, $counter);
                $this->storeEmail($person, $type, $email, $counter);
                $this->storePlaceOfBirth($person, $type, $birthplace, $counter);
                $this->storeAcademicTitle($person, $type, $academic, $counter);
                $this->storeDateOfBirth($person, $type, $birthdate, $counter);

                $addFunction = 'add' . $type;
                $this->document->$addFunction($person);
                $this->log->debug("person stored");
            }
        }
    }

    private function storeFirstName($person, $type, $first, $counter) {
        if ($counter >= 1) {
            $entry = $this->documentData[$type . $first . $counter];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $person->setFirstName($entry);
                $this->documentData[$type . $first . $counter] = "";
            }
        } else {
            $entry = $this->documentData[$type . $first];
            $person->setFirstName($entry);
            $this->documentData[$type . $first] = "";
        }
    }

    private function storeLastName($person, $type, $last, $counter) {
        if ($counter >= 1) {
            $entry = $this->documentData[$type . $last . $counter];
            if ($entry !== "") {
                $person->setLastName($entry);
                $this->log->debug("Value: " . $entry);
                $this->documentData[$type . $last . $counter] = "";
            }
        } else {
            $entry = $this->documentData[$type . $last];
            $person->setLastName($entry);
            $this->documentData[$type . $last] = "";
        }
    }

    private function storeEmail($person, $type, $email, $counter) {
        if ($counter >= 1) {
            $entry = $this->documentData[$type . $email . $counter];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $person->setEmail($entry);
                $this->documentData[$type . $email . $counter] = "";
            }
        } else {
            $entry = $this->documentData[$type . $email];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $person->setEmail($entry);
                $this->documentData[$type . $email] = "";
            }
        }
    }

    private function storePlaceOfBirth($person, $type, $birthplace, $counter) {
        if ($counter >= 1) {
            $entry = $this->documentData[$type . $birthplace . $counter];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $person->setPlaceOfBirth($entry);
                $this->documentData[$type . $birthplace . $counter] = "";
            }
        } else {
            $entry = $this->documentData[$type . $birthplace];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $person->setPlaceOfBirth($entry);
                $this->documentData[$type . $birthplace] = "";
            }
        }
    }

    private function storeDateOfBirth($person, $type, $birthdate, $counter) {
        if ($counter >= 1) {
            $entry = $this->documentData[$type . $birthdate . $counter];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $person->setDateOfBirth($entry);
                $this->documentData[$type . $birthdate . $counter] = "";
            }
        } else {
            $entry = $this->documentData[$type . $birthdate];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $person->setDateOfBirth($entry);
                $this->documentData[$type . $birthdate] = "";
            }
        }
    }

    private function storeAcademicTitle($person, $type, $academic, $counter) {
        if ($counter >= 1) {
            $entry = $this->documentData[$type . $academic . $counter];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $person->setAcademicTitle($entry);
                $this->documentData[$type . $academic . $counter] = "";
            }
        } else {
            $entry = $this->documentData[$type . $academic];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $person->setAcademicTitle($entry);
                $this->documentData[$type . $academic] = "";
            }
        }
    }

    private function _prepareTitleObject($dataKey, $dataValue) {
        if (!isset($dataValue)) {
            return;
        } else {
            //String can be changed here
            $lang = "Language";
            $this->log->info("try to store title: " . $dataKey);
            $title = new Opus_Title();
            $this->log->info("try to store title: " . $dataKey . " ...");

            if (strstr($dataKey, $lang))
                $type = $this->getObjectType($dataKey, $lang);
            else
                $type = substr($dataKey, 0, strlen($dataKey) - 1);

            if (isset($type)) {
                $this->log->debug("Title type:" . $type);
                $counter = (int) $this->getCounter($dataKey);
                $this->log->debug("counter: " . $counter);
                if ($this->documentData[$type . $counter] == "") {
                    $this->storeTitleValue($title, $type, $counter);
                    $this->log->debug("title value stored");

                    $this->storeTitleLanguage($title, $type, $lang, $counter);
                    $this->log->debug("title language stored");

                    $addFunction = 'add' . $type;
                    $this->document->$addFunction($title);
                    $this->log->debug("title stored");
                }
            }
        }
    }

    private function storeTitleValue($title, $type, $counter) {
        if ($counter >= 1) {
            $entry = $this->documentData[$type . $counter];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $title->setValue($entry);
                $this->documentData[$type . $counter] = "";
            }
        } else {
            $entry = $this->documentData[$type];
            $this->log->debug("Value: " . $entry);
            $title->setValue($entry);
            $this->documentData[$type] = "";
        }
    }

    private function storeTitleLanguage($title, $type, $short, $counter) {
        if ($counter >= 1) {
            $entry = $this->documentData[$type . $short . $counter];
            if ($entry !== "") {
                $this->log->debug("Value: " . $entry);
                $title->setLanguage($entry);
                $this->documentData[$type . $short . $counter] = "";
            }
        } else {
            $entry = $this->documentData[$type . $short];
            $this->log->debug("Value: " . $entry);
            $title->setLanguage($entry);
            $this->documentData[$type . $short] = "";
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
