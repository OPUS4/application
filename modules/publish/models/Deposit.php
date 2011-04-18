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
    public $session;

    public function __construct($documentData = null) {

        $this->log = Zend_Registry::get('Zend_Log');
        $this->session = new Zend_Session_Namespace('Publish');
        $this->document = new Opus_Document($this->session->documentId);
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
            $datasetType = $this->_getDatasetType($dataKey);

            if (isset($datasetType) && !empty($datasetType)) {
                $this->log->debug("Wanna store a " . $datasetType . "!");
                $this->log->debug("dataKey: " . $dataKey . " AND dataValue " . $dataValue);
                $storeMethod = "_prepare" . $datasetType . "Object";

                $this->$storeMethod($dataKey, $dataValue);
            }
            else {
                $this->log->debug("wanna store something else...");
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
    private function _getDatasetType($dataKey) {
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
     * Method to retrieve a possible counter from key name. The counter can be the last character of an data key.
     * Counter > 0 is returned if last character is a integer. Else 0 is returned.
     * @param <String> $dataKey
     * @return <Int> counter or 0
     */
    private function getCounter($dataKey) {
        //counters may appear on the last position
        $lastChar = (substr($dataKey, -1, 1));
        if (is_numeric($lastChar))
            return (int) $lastChar;
        else
            return 0;
    }

    /**
     * Method finds out which type of person or title is given by removing unneccesary chars.
     * @param <type> $dataKey
     * @param <type> $removeString
     * @return <type>
     */
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
        $last = "LastName";

        if (strstr($dataKey, $last)) {
            $type = $this->getObjectType($dataKey, $last);
            $this->log->debug("Person type:" . $type);

            $counter = $this->getCounter($dataKey);
            $this->log->debug("counter: " . $counter);

            $addFunction = 'add' . $type;
            $person = $this->document->$addFunction(new Opus_Person());

            // person model
            $this->storePersonAttribute($person, $type, 'FirstName', 'first', $counter);
            $this->storePersonAttribute($person, $type, $last, 'last', $counter);
            $this->storePersonAttribute($person, $type, 'Email', 'email', $counter);
            $this->storePersonAttribute($person, $type, 'PlaceOfBirth', 'pob', $counter);
            $this->storePersonAttribute($person, $type, 'AcademicTitle', 'title', $counter);
            $this->storePersonAttribute($person, $type, 'DateOfBirth', 'dob', $counter);

            // link-person-model
            $this->storePersonAttribute($person, $type, 'AllowEmailContact', 'check', $counter);
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
        if ($counter >= 1) {
            $index = $personType . $attribute . $counter;
        }
        else {
            $index = $personType . $attribute;
        }
        $entry = $this->documentData[$index];
        if ($entry !== "") {
            switch ($attributeType) {
                case 'first' :
                    $this->log->debug("First name: " . $entry);
                    $person->setFirstName($entry);
                    break;
                case 'last' :
                    $this->log->debug("Last name: " . $entry);
                    $person->setLastName($entry);
                    break;
                case 'email' :
                    $this->log->debug("Email: " . $entry);
                    $person->setEmail($entry);
                    break;
                case 'pob' :
                    $this->log->debug("Place of Birth: " . $entry);
                    $person->setPlaceOfBirth($entry);
                    break;
                case 'title' :
                    $this->log->debug("Academic Title: " . $entry);
                    $person->setAcademicTitle($entry);
                    break;
                case 'dob' :
                    $this->log->debug("Date of Birth: " . $entry);
                    $person->setDateOfBirth($entry);
                    break;
                case 'check' :
                    $this->log->debug("Allow Email Contact?: " . $entry);
                    if (is_null($entry))
                        $entry = 0;
                    $person->setAllowEmailContact($entry);
                    break;
            }

            $this->documentData[$index] = "";
        }
    }

    private function _prepareTitleObject($dataKey, $dataValue) {
        if ($dataValue == "") {
            $this->log->info("title already stored!");
            return;
        }
        //String can be changed here
        $lang = "Language";

        if (!strstr($dataKey, $lang)) {
            $type = substr($dataKey, 0, strlen($dataKey) - 1);
            $addFunction = 'add' . $type;
            $title = new Opus_Title();

            $this->log->debug("Title type:" . $type);
            $counter = $this->getCounter($dataKey);
            $this->log->debug("counter: " . $counter);
            $this->storeTitleValue($title, $type, $counter);
            $this->storeTitleLanguage($title, $type, $lang, $counter);
            $this->document->$addFunction($title);
        }
    }

    private function storeTitleValue($title, $type, $counter) {
        if ($counter >= 1) {
            $index = $type . $counter;
        }
        else {
            $index = $type;
        }
        $entry = $this->documentData[$index];
        if ($entry !== "") {
            $this->log->debug("Value of title: " . $entry);
            $title->setValue($entry);
            //"delete" value to avoid possible redundant record
            $this->documentData[$index] = "";
        }
    }

    private function storeTitleLanguage($title, $type, $short, $counter) {
        if ($counter >= 1) {
            $index = $type . $short . $counter;
        }
        else {
            $index = $type . $short;
        }
        $entry = $this->documentData[$index];
        if ($entry !== "") {
            $this->log->debug("Value of title language: " . $entry);
            $title->setLanguage($entry);
            //"delete" value to avoid possible redundant record
            $this->documentData[$index] = "";
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
            return;
        }
        $this->log->debug("try to store subject: " . $dataKey);
        $type = $this->getSubjectType($dataKey);
        $counter = (int) $this->getCounter($dataKey);
        $this->log->debug("counter: " . $counter);

        switch ($type) {
            case 'MSC' :
            case 'DDC' :
                $this->log->debug("subject is a " . $type . " subject and has to be stored as a Collection.");
                if (strstr($dataValue, 'collId'))
                        return;
                if (isset ($this->session->additionalFields['step'.$dataKey])) {
                        $step = $this->session->additionalFields['step'.$dataKey];
                        if (array_key_exists('collId'. $step . $dataKey, $this->documentData))
                                $dataValue = $this->documentData['collId'. $step . $dataKey];
                }
                $this->_storeCollectionObject(strtolower($type), $dataValue);
                $this->log->debug("subject has also be stored as subject.");
                $subject = new Opus_Subject();
                break;

            case 'CCS' :
            case 'PACS' :
                $this->log->debug("subject is a " . $type . " subject and has only to be stored as a Collection.");
                if (isset ($this->session->additionalFields['step'.$dataKey])) {
                        $step = $this->session->additionalFields['step'.$dataKey];
                        if (array_key_exists('collId'. $step . $dataKey, $this->documentData))
                                $dataValue = $this->documentData['collId'. $step . $dataKey];
                }
                $this->_storeCollectionObject(strtolower($type), $dataValue);
                if (isset($step))
                    $dataValue = $this->documentData['collId'. $step . $dataKey] = "";
                return;

            case 'Swd' :
                $this->log->debug("subject is a swd subject.");
                $subject = new Opus_SubjectSwd();
                break;

            case 'Uncontrolled':
                $this->log->debug("subject is a uncontrolled or other subject.");
                $subject = new Opus_Subject();
                break;
        }       
        if ($counter >= 1) {
            $subjectType = 'Subject' . $type;
            $this->log->debug("subjectType: " . $subjectType);
            if (strstr($dataValue, 'ID:')) {
                $dataValue = substr($dataValue, 3);
                //store a simple collection
                $collection = new Opus_Collection($dataValue);
                $dataValue = $collection->getDisplayName();
            }
            $subject->setValue($dataValue);
            $addFunction = "add" . $subjectType;
            $this->log->debug("addfunction: " . $addFunction);
            $this->document->$addFunction($subject);
            if (isset($step))
                    $dataValue = $this->documentData['collId'. $step . $dataKey] = "";
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
            return;
        }
        $this->log->debug("try to store note: " . $dataKey);
        $note = new Opus_Note();
        $note->setMessage($dataValue);
        $note->setVisibility("private");
        $this->document->addNote($note);
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
            return;
        }
        $this->log->debug("try to store Collection: " . $dataValue);

        if (strstr($dataKey, "Project"))
            $this->_storeCollectionObject('projects', $dataValue);

        else if (strstr($dataKey, "Institute"))
            $this->_storeCollectionObject('institutes', $dataValue);

        else if (strstr($dataKey, "Collection"))
            $this->_storeCollectionObject('', $dataValue);
    }

    private function _storeCollectionObject($collectionRole, $dataValue) {
        if ($dataValue == "") {
            $this->log->debug("Collection already stored.");            
            return;
        }
        if (strstr($dataValue, 'ID:')) {
            $dataValue = substr($dataValue, 3);
            //store a simple collection
            $this->document->addCollection(new Opus_Collection($dataValue));
            return;
        }

        if ($collectionRole == "") {
            //store a simple collection            
            $this->document->addCollection(new Opus_Collection($dataValue));
            return;
        }
        $role = Opus_CollectionRole::fetchByName($collectionRole);
        if (isset($role)) {
            $roleId = $role->getId();
            $this->log->debug("Role: " . $role . " with ID " . $roleId . " and value " . $dataValue);

            if ($collectionRole === 'institutes') {
                //fetch role name for institutes (they don't have a number)
                $collArray = Opus_Collection::fetchCollectionsByRoleName($roleId, $dataValue);
            }
            else {
                //all other roles are catched by number
                $collArray = Opus_Collection::fetchCollectionsByRoleNumber($roleId, $dataValue);
            }

            if (!is_null($collArray)) {
                $this->document->addCollection($collArray[0]);
            }
            if (count($collArray) >= 2) {
                $this->log->err("While trying to store " . $collectionRole . " as Collection, an error occurred. The method fetchCollectionsByRoleNumber returned an array with > 1 values. The " . $collectionRole . " cannot be definitely assigned but was stored to the first entry.");
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
            return;
        }
        $dataValue = substr($dataValue, 3);
        $this->log->debug("try to store Licence with id: " . $dataValue);
        $licence = new Opus_Licence($dataValue);
        $this->document->addLicence($licence);
    }

    /**
     * Prepare and store a dnb institute for the current document.
     * @param <type> $dataKey
     * @param <type> $dataValue
     */
    private function _prepareThesisObject($dataKey, $dataValue) {

        if ($dataValue == "") {
            $this->log->debug("ThesisGrantor or ThesisPublisher already stored.");
            return;
        }
        $dataValue = substr($dataValue, 3);
        $this->log->debug("try to store " . $dataKey . " with id: " . $dataValue);
        $thesis = new Opus_DnbInstitute($dataValue);

        if (strstr($dataKey, 'Grantor')) {
            $this->document->addThesisGrantor($thesis);
        }
        else if (strstr($dataKey, 'Publisher')) {
            $this->document->addThesisPublisher($thesis);
        }
    }

    private function _prepareIdentifierObject($dataKey, $dataValue) {
        if ($dataValue == "") {
            $this->log->debug("Identifier already stored.");
            return;
        }
        $this->log->debug("try to store " . $dataKey . " with id: " . $dataValue);
        $identifier = new Opus_Identifier();
        $identifier->setValue($dataValue);
        if (strstr($dataKey, 'Old')) {
            $this->document->addIdentifierOld($identifier);
        }
        else if (strstr($dataKey, 'Serial')) {
            $this->document->addIdentifierSerial($identifier);
        }
        else if (strstr($dataKey, 'Uuid')) {
            $this->document->addIdentifierUuid($identifier);
        }
        else if (strstr($dataKey, 'Isbn')) {
            $this->document->addIdentifierIsbn($identifier);
        }
        else if (strstr($dataKey, 'Urn')) {
            $this->document->addIdentifierUrn($identifier);
        }
        else if (strstr($dataKey, 'Doi')) {
            $this->document->addIdentifierDoi($identifier);
        }
        else if (strstr($dataKey, 'Handle')) {
            $this->document->addIdentifierHandle($identifier);
        }
        else if (strstr($dataKey, 'Url')) {
            $this->document->addIdentifierUrl($identifier);
        }
        else if (strstr($dataKey, 'Issn')) {
            $this->document->addIdentifierIssn($identifier);
        }
        else if (strstr($dataKey, 'StdDoi')) {
            $this->document->addIdentifierStdDoi($identifier);
        }
        else if (strstr($dataKey, 'CrisLink')) {
            $this->document->addIdentifierCrisLink($identifier);
        }
        else if (strstr($dataKey, 'SplashUrl')) {
            $this->document->addIdentifierSplashUrl($identifier);
        }
        else if (strstr($dataKey, 'Opus3')) {
            $this->document->addIdentifierOpus3($identifier);
        }
        else if (strstr($dataKey, 'Opac')) {
            $this->document->addIdentifierOpac($identifier);
        }
    }

    private function _prepareReferenceObject($dataKey, $dataValue) {
        //TODO: probably no valid storing possible because a label is missing
        //a reference should be a new datatype with implicit fields value and label

        if ($dataValue == "") {
            $this->log->debug("Reference already stored.");
            return;
        }

        $this->log->debug("try to store " . $dataKey . " with id: " . $dataValue);

        $reference = new Opus_Reference();
        $reference->setValue($dataValue);
        $reference->setLabel("no Label given");

        if (strstr($dataKey, 'Isbn')) {
            $this->document->addReferenceIsbn($reference);
        }
        else if (strstr($dataKey, 'Urn')) {
            $this->document->addReferenceUrn($reference);
        }
        else if (strstr($dataKey, 'Doi')) {
            $this->document->addReferenceDoi($reference);
        }
        else if (strstr($dataKey, 'Handle')) {
            $this->document->addReferenceHandle($reference);
        }
        else if (strstr($dataKey, 'Url')) {
            $this->document->addReferenceUrl($reference);
        }
        else if (strstr($dataKey, 'Issn')) {
            $this->document->addReferenceIssn($reference);
        }
        else if (strstr($dataKey, 'StdDoi')) {
            $this->document->addReferenceStdDoi($reference);
        }
        else if (strstr($dataKey, 'CrisLink')) {
            $this->document->addReferenceCrisLink($reference);
        }
        else if (strstr($dataKey, 'SplashUrl')) {
            $this->document->addReferenceSplashUrl($reference);
        }
    }

    private function _prepareEnrichmentObject($dataKey, $dataValue) {
        if ($dataValue == "") {
            $this->log->debug("Enrichment already stored.");
            return;
        }
        $counter = $this->getCounter($dataKey);
        if ($counter != 0) {
            //remove possible counter char
            $dataKey = str_replace($counter, '', $dataKey);
        }

        $this->log->debug("try to store " . $dataKey . " with id: " . $dataValue);
        $keyName = str_replace('Enrichment', '', $dataKey);

        $enrichment = new Opus_Enrichment();
        $enrichment->setValue($dataValue);
        $enrichment->setKeyName($keyName);

        $this->document->addEnrichment($enrichment);
    }

}
