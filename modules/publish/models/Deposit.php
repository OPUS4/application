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
    public $documentData;
    public $log;

    public function __construct($documentId = null, $documentData = null) {

        $this->log = Zend_Registry::get('Zend_Log');
        $this->document = new Opus_Document($documentId);
        $this->documentData = $documentData;

        if ($this->document->getServerState() !== 'temporary') {
            $this->log->err('Could not find document: Tried to return document, which is not in state "temporary"');
            throw new Publish_Model_OpusServerException('Could not find document.');
        }

        $this->_storeDocumentData();
    }

    public function getDocument() {
        return $this->document;
    }

    private function _storeDocumentData() {

        foreach ($this->documentData as $dataKey => $dataValue) {
            $datasetType = $this->_getDatasetType($dataKey, $dataValue);

            if (isset($datasetType) && !empty($datasetType)) {
                $this->log->info("Wanna store a " . $datasetType . "!");
                $this->log->info("dataKey: " . $dataKey . " AND dataValue " . $dataValue);
                $storeMethod = "_prepare" . $datasetType . "Object";

                $this->$storeMethod($dataKey, $dataValue);
            }
            else {
                $this->log->info("wanna store something else...");
                if ($this->document->hasMultipleValueField($dataKey)) {

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
                }
                else {
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
        if (strstr($dataKey, 'Person'))
            return 'Person';
        else if (strstr($dataKey, 'Title'))
            return 'Title';
        else if (strstr($dataKey, 'Subject'))
            return 'Subject';
        else if (strstr($dataKey, 'Note'))
            return 'Note';
        else if (strstr($dataKey, 'Project') || strstr($dataKey, 'Institute') || strstr($dataKey, 'Collection'))
            return 'Collection';
        else if (strstr($dataKey, 'Licence'))
            return 'Licence';
        else if (strstr($dataKey, 'ThesisGrantor') || strstr($dataKey, 'ThesisPublisher'))
            return 'Thesis';
        else if (strstr($dataKey, 'Identifier'))
            return 'Identifier';
        else if (strstr($dataKey, 'Reference'))
            return 'Reference';
        else if (strstr($dataKey, 'Patent'))
            return 'Patent';
        else if (strstr($dataKey, 'Enrichment'))
            return 'Enrichment';

        return "";
    }

    /**
     * Method to retrieve the last character of an data key. If that is a number, it is a counter.
     * @param <String> $dataKey
     * @return <String> last character
     */
    private function getCounter($dataKey) {
        //always on last position
        return (substr($dataKey, -1, 1));
    }

    private function getObjectType($dataKey, $removeString) {
        $pos = strpos($dataKey, $removeString);
        if ($pos !== false)
            return substr($dataKey, 0, $pos);
    }

    /**
     * Methode to prepare a person object for saving in database.
     * @param <type> $dataKey
     * @param <type> $dataValue
     */
    private function _preparePersonObject($dataKey = null, $dataValue = null) {
        //String can be changed here
        $first = "FirstName";
        $last = "LastName";
        $email = "Email";
        $birthplace = "PlaceOfBirth";
        $birthdate = "DateOfBirth";
        $academic = "AcademicTitle";
        $allowEmail = "AllowEmailContact";

        if (strstr($dataKey, $first))
            $type = $this->getObjectType($dataKey, $first);
        else
        if (strstr($dataKey, $last))
            $type = $this->getObjectType($dataKey, $last);

        if (isset($type)) {
            $this->log->debug("Person type:" . $type);

            $counter = (int) $this->getCounter($dataKey);
            $this->log->debug("counter: " . $counter);

            if ($this->documentData[$type . $last . $counter] !== "") {

                $addFunction = 'add' . $type;
                $person = $this->document->$addFunction(new Opus_Person());

                // person model                
                $this->storePersonAttribute($person, $type, $first, 'first', $counter);
                $this->storePersonAttribute($person, $type, $last, 'last', $counter);
                $this->storePersonAttribute($person, $type, $email, 'email', $counter);
                $this->storePersonAttribute($person, $type, $birthplace, 'pob', $counter);
                $this->storePersonAttribute($person, $type, $academic, 'title', $counter);
                $this->storePersonAttribute($person, $type, $birthdate, 'dob', $counter);

                // link-person-model                
                $this->storePersonAttribute($person, $type, $allowEmail, 'check', $counter);

                $this->log->debug("person stored");
            }
        }
    }

    /**
     * Method stores attributes like name or email for a given person object.
     * @param <Opus_Person> $person - given person object
     * @param <String> $personType - type of person (editor, author etc.)
     * @param <String> $attribute - the value to store
     * @param <String> $attributeType - type of attribute (first name, email etc.)
     * @param <Int> $counter - number in case of more than one person per type
     */
    private function storePersonAttribute($person, $personType, $attribute, $attributeType, $counter) {
        $entry = "";
        if (isset($this->documentData[$personType . $attribute . $counter])) {
            $entry = $this->documentData[$personType . $attribute . $counter];
        }
        else {
            if (isset($this->documentData[$personType . $attribute])) {
                $entry = $this->documentData[$personType . $attribute];
            }
        }

        if ($entry !== "") {
            $this->log->debug("Value: " . $entry);
            switch ($attributeType) {
                case 'first' : $person->setFirstName($entry);
                    break;
                case 'last' : $person->setLastName($entry);
                    break;
                case 'email' : $person->setEmail($entry);
                    break;
                case 'pob' : $person->setPlaceOfBirth($entry);
                    break;
                case 'title' : $person->setAcademicTitle($entry);
                    break;
                case 'dob' : $person->setDateOfBirth($entry);
                    break;
                case 'check' : $person->setAllowEmailContact($entry);
                    break;
            }

            if ($counter >= '1')
                $this->documentData[$personType . $attribute . $counter] = "";

            else
                $this->documentData[$personType . $attribute] = "";
        }
    }

    private function _prepareTitleObject($dataKey, $dataValue) {
        if (!isset($dataValue)) {
            return;
        }
        else {
            //String can be changed here
            $lang = "Language";
            $this->log->info("try to store title: " . $dataKey);
            $title = new Opus_Title();

            if (strstr($dataKey, $lang))
                $type = $this->getObjectType($dataKey, $lang);
            else
                $type = substr($dataKey, 0, strlen($dataKey) - 1);

            if (isset($type)) {
                $this->log->debug("Title type:" . $type);
                $counter = (int) $this->getCounter($dataKey);
                $this->log->debug("counter: " . $counter);
                if ($this->documentData[$type . $counter] !== "") {
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
        }
        else {
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
        }
        else {
            $entry = $this->documentData[$type . $short];
            $this->log->debug("Value: " . $entry);
            $title->setLanguage($entry);
            $this->documentData[$type . $short] = "";
        }
    }

    private function getSubjectType($dataKey) {
        if (strstr($dataKey, 'MSC'))
            return 'MSC';
        else if (strstr($dataKey, 'DDC'))
            return 'DDC';
        else if (strstr($dataKey, 'Swd'))
            return 'Swd';
        else if (strstr($dataKey, 'CCS'))
            return 'CCS';
        else if (strstr($dataKey, 'PACS'))
            return 'PACS';
        else
            return 'Uncontrolled';
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
        }
        else {
            $this->log->debug("try to store subject: " . $dataKey);
            $type = $this->getSubjectType($dataKey);
            switch ($type) {
                case 'MSC' :
                    $this->log->debug("subject is a MSC subject and has to be stored as a Collection.");
                    $this->_storeCollectionObject('msc', $dataValue);
                    $this->log->debug("subject has also be stored as subject.");
                    $subject = new Opus_Subject();
                    break;

                case 'DDC' :
                    $this->log->debug("subject is a DDC subject and has to be stored as a Collection.");
                    $this->_storeCollectionObject('ddc', $dataValue);
                    $this->log->debug("subject has also be stored as subject.");
                    $subject = new Opus_Subject();
                    break;

                case 'CCS' :
                    $this->log->debug("subject is a CCS subject and has to be stored as a Collection.");
                    $this->_storeCollectionObject('ccs', $dataValue);
                    break;

                case 'PACS' :
                    $this->log->debug("subject is a PACS subject and has to be stored as a Collection.");
                    $this->_storeCollectionObject('pacs', $dataValue);
                    break;

                case 'Swd' :
                    $this->log->debug("subject is a swd subject.");
                    $subject = new Opus_SubjectSwd();
                    break;

                case 'Uncontrolled':
                    $this->log->debug("subject is a uncontrolled or other subject.");
                    $subject = new Opus_Subject();
                    break;
            }

            if ($type != 'CCS' && $type != 'PACS') {
                $counter = (int) $this->getCounter($dataKey);
                $this->log->debug("counter: " . $counter);


                if ($counter >= 1) {
                    $subjectType = 'Subject' . $type;
                    $this->log->debug("subjectType: " . $subjectType);
                    $subject->setValue($dataValue);

                    $addFunction = "add" . $subjectType;
                    $this->log->debug("addfunction: " . $addFunction);
                    $this->document->$addFunction($subject);
                }
            }
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
        }
        else {
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
        }
        else {
            $this->log->debug("try to store Collection:");

            if (strstr($dataKey, "Project"))
                $this->_storeCollectionObject('projects', $dataValue);

            else if (strstr($dataKey, "Institute"))
                $this->_storeCollectionObject('institutes', $dataValue);

            else if (strstr($dataKey, "Collection"))
                $this->_storeCollectionObject('', $dataValue);
        }
    }

    private function _storeCollectionObject($collectionRole, $dataValue) {
        if ($collectionRole == "") {
            $this->document->addCollection(new Opus_Collection($dataValue));
        }
        else {
            $role = Opus_CollectionRole::fetchByName($collectionRole);
            if (isset($role)) {
                $this->log->debug("Role: " . $role);

                if ($collectionRole === 'institutes')
                    $collArray = Opus_Collection::fetchCollectionsByRoleName($role->getId(), $dataValue);
                else
                    $collArray = Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $dataValue);

                $this->log->debug("Role ID: " . $role->getId() . ", value: " . $dataValue);

                //if (!is_null($collArray) && count($collArray) <= 1) {
                if (!is_null($collArray)) {
                    $this->document->addCollection($collArray[0]);
                }
                if (count($collArray) >= 2) {
                    $this->log->info("While trying to store " . $collectionRole . " as Collection, an error occurred. " .
                            "The method fetchCollectionsByRoleNumber returned an array with > 1 values. The " . $collectionRole .
                            " cannot be definitely assigned but was stored to the first entry.");
                }
//                else
//                    throw new Publish_Model_OpusServerException("While trying to store " . $collectionRole . " as Collection, an error occurred.
//                        The method fetchCollectionsByRoleNumber returned an array with > 1 values. The " . $collectionRole . " cannot be definitely assigned.");
            }
        }
    }

    /**
     * Prepare and store a licence for the current document.
     * @param <type> $dataKey
     * @param <type> $dataValue
     */
    private function _prepareLicenceObject($dataKey, $dataValue) {

        if ($dataValue == "") {
            $this->log->debug("Licence already stored.");
        }
        else {
            $dataValue = substr($dataValue, 3);

            $this->log->debug("try to store Licence with id: " . $dataValue);

            $licence = new Opus_Licence($dataValue);

            $addFunction = "addLicence";
            $this->log->debug("addfunction: " . $addFunction);
            $this->document->$addFunction($licence);
        }
    }

    /**
     * Prepare and store a dnb institute for the current document.
     * @param <type> $dataKey
     * @param <type> $dataValue
     */
    private function _prepareThesisObject($dataKey, $dataValue) {

        if ($dataValue == "") {
            $this->log->debug("ThesisGrantor or ThesisPublisher already stored.");
        }
        else {
            $dataValue = substr($dataValue, 3);

            $this->log->debug("try to store " . $dataKey . " with id: " . $dataValue);

            $thesis = new Opus_DnbInstitute($dataValue);

            if (strstr($dataKey, 'Grantor')) {
                $addFunction = "addThesisGrantor";
                $this->log->debug("addfunction: " . $addFunction);
                $this->document->$addFunction($thesis);
            }
            else if (strstr($dataKey, 'Publisher')) {
                $addFunction = "addThesisPublisher";
                $this->log->debug("addfunction: " . $addFunction);
                $this->document->$addFunction($thesis);
            }
        }
    }

    private function _prepareIdentifierObject($dataKey, $dataValue) {
        if ($dataValue == "") {
            $this->log->debug("Identifier already stored.");
        }
        else {

            $this->log->debug("try to store " . $dataKey . " with id: " . $dataValue);

            $identifier = new Opus_Identifier();
            $identifier->setValue($dataValue);
            $addFunction = 'addIdentifier';

            if (strstr($dataKey, 'Old')) {
                $addFunction .= 'Old';
            }
            else if (strstr($dataKey, 'Serial')) {
                $addFunction .= 'Serial';
            }
            else if (strstr($dataKey, 'Uuid')) {
                $addFunction .= 'Uuid';
            }
            else if (strstr($dataKey, 'Isbn')) {
                $addFunction .= 'Isbn';
            }
            else if (strstr($dataKey, 'Urn')) {
                $addFunction .= 'Urn';
            }
            else if (strstr($dataKey, 'Doi')) {
                $addFunction .= 'Doi';
            }
            else if (strstr($dataKey, 'Handle')) {
                $addFunction .= 'Handle';
            }
            else if (strstr($dataKey, 'Url')) {
                $addFunction .= 'Url';
            }
            else if (strstr($dataKey, 'Issn')) {
                $addFunction .= 'Issn';
            }
            else if (strstr($dataKey, 'StdDoi')) {
                $addFunction .= 'StdDoi';
            }
            else if (strstr($dataKey, 'CrisLink')) {
                $addFunction .= 'CrisLink';
            }
            else if (strstr($dataKey, 'SplashUrl')) {
                $addFunction .= 'SplashUrl';
            }
            else if (strstr($dataKey, 'Opus3')) {
                $addFunction .= 'Opus3';
            }
            else if (strstr($dataKey, 'Opac')) {
                $addFunction .= 'Opac';
            }

            $this->log->debug("addfunction: " . $addFunction);
            $this->document->$addFunction($identifier);
        }
    }

    private function _prepareReferenceObject($dataKey, $dataValue) {
        //TODO: probably no valid storing possible because a label is missing
        //a reference should be a new datatype with implicit fields value and label

        if ($dataValue == "") {
            $this->log->debug("Reference already stored.");
        }
        else {

            $this->log->debug("try to store " . $dataKey . " with id: " . $dataValue);

            $reference = new Opus_Reference();
            $reference->setValue($dataValue);
            $reference->setLabel("no Label given");
            $addFunction = 'addReference';

            if (strstr($dataKey, 'Isbn')) {
                $addFunction .= 'Isbn';
            }
            else if (strstr($dataKey, 'Urn')) {
                $addFunction .= 'Urn';
            }
            else if (strstr($dataKey, 'Doi')) {
                $addFunction .= 'Doi';
            }
            else if (strstr($dataKey, 'Handle')) {
                $addFunction .= 'Handle';
            }
            else if (strstr($dataKey, 'Url')) {
                $addFunction .= 'Url';
            }
            else if (strstr($dataKey, 'Issn')) {
                $addFunction .= 'Issn';
            }
            else if (strstr($dataKey, 'StdDoi')) {
                $addFunction .= 'StdDoi';
            }
            else if (strstr($dataKey, 'CrisLink')) {
                $addFunction .= 'CrisLink';
            }
            else if (strstr($dataKey, 'SplashUrl')) {
                $addFunction .= 'SplashUrl';
            }

            $this->log->debug("addfunction: " . $addFunction);
            $this->document->$addFunction($reference);
        }
    }

    private function _prepareEnrichmentObject($dataKey, $dataValue) {
        if ($dataValue == "") {
            $this->log->debug("Enrichment already stored.");
        }
        else {

            $this->log->debug("try to store " . $dataKey . " with id: " . $dataValue);

            $enrichment = new Opus_Enrichment();
            $enrichment->setValue($dataValue);

            $keyName = str_replace('Enrichment', '', $dataKey);
            $enrichment->setKeyName($keyName);
            $addFunction = 'addEnrichment';

            $this->log->debug("addfunction: " . $addFunction);
            $this->document->$addFunction($enrichment);
        }
    }

}
