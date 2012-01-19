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
 * @package     Module_Publish
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
    public $session2;

    public function __construct($documentData = null) {

        $this->log = Zend_Registry::get('Zend_Log');
        $this->session = new Zend_Session_Namespace('Publish');
        $this->session2 = new Zend_Session_Namespace();
        $this->document = new Opus_Document($this->session->documentId);
        $this->documentData = $documentData;

        if ($this->document->getServerState() !== 'temporary') {
            $this->log->err('Could not find document: Tried to return document, which is not in state "temporary"');
            throw new Publish_Model_FormDocumentNotFoundException();
        }

        $this->_storeDocumentData();
    }

    public function getDocument() {
        return $this->document;
    }

    private function _storeDocumentData() {

        foreach ($this->documentData as $dataKey => $dataEntry) {                        
            $datasetType = $dataEntry['datatype'];            
            $dataValue = $dataEntry['value']; 
            $dataSubfield = $dataEntry['subfield']; 
                        
            $this->log->debug("Store -- " . $datasetType . " --");
            $this->log->debug("Name: " . $dataKey . " : Value " . $dataValue . " (" . $dataSubfield . ")");
            
            if (!$dataSubfield) {
                            
                switch ($datasetType) {
                    case 'Person' : $this->preparePersonObject($dataKey, $dataValue);
                        break;
                    case 'Title' : $this->prepareTitleObject($dataKey, $dataValue);
                        break;
                    case 'Subject' : $this->storeSubjectObject($dataKey, $dataValue);
                        break;
                    case 'Note' : $this->storeNoteObject($dataValue);
                        break;
                    case 'Collection' :
                    case 'CollectionLeaf' : $this->storeCollectionObject($dataValue);
                        break;
                    case 'Licence' : $this->storeLicenceObject($dataValue);
                        break;
                    case 'ThesisGrantor' : $this->storeThesisObject($dataValue, true);
                        break;
                    case 'ThesisPublisher' : $this->storeThesisObject($dataValue, false);
                        break;
                    case 'Identifier' : $this->storeIdentifierObject($dataKey, $dataValue);
                        break;
                    case 'Reference' : $this->storeReferenceObject($dataKey, $dataValue);
                        break;
                    case 'Enrichment' : $this->storeEnrichmentObject($dataKey, $dataValue);
                        break;  
                    case 'Series' :
                        break;
                    case 'SeriesNumber' :
                        $this->storeSeriesObject($dataKey, $dataValue);
                        break;
                    
                    default: 
                        $this->log->debug("Want to store a internal field: type = " . $datasetType . " name = " . $dataKey . " value = " . $dataValue);
                        $this->storeInternalValue($datasetType, $dataKey, $dataValue);               
                }
            }
        }
    }

    private function storeInternalValue($datasetType, $dataKey, $dataValue) {

        if ($datasetType === 'Date') {
            if (!is_null($dataValue) and $dataValue !== "")
                $dataValue = $this->castStringToOpusDate($dataValue);
        }

        //external Field
        if ($this->document->hasMultipleValueField($dataKey)) {

            if ($dataKey === 'Language') {
                $file = $this->document->getFile();
                $file->setLanguage($dataValue);
            }            
            $function = "add" . $dataKey;            
            $addedValue = $this->document->$function();
            $addedValue->setValue($dataValue);            
        }
        //internal Fiels
        else {            
            $function = "set" . $dataKey;            
            $this->document->$function($dataValue);            
        }
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
        $seclastChar = (substr($dataKey, -2, 1));
        if (is_numeric($lastChar))
            if (is_numeric($seclastChar))
                return (int) $seclastChar . $lastChar;
            else
                return (int) $lastChar;
        return 0;
    }
    
    /**
     * Method returns which type of person is given
     * @param <String> $dataKey     
     * @return <String> Type of Person
     */
    private function getPersonType($dataKey) {
        $dataKey = strtolower($dataKey);
        if (strstr($dataKey, 'author'))
                return 'Author';
        else if (strstr($dataKey, 'submitter'))
                return 'Submitter';
        else if (strstr($dataKey, 'referee'))
                return 'Referee';
        else if (strstr($dataKey, 'editor'))
                return 'Editor';
        else if  (strstr($dataKey, 'advisor'))
                return 'Advisor';
        else if  (strstr($dataKey, 'translator'))
                return 'Translator';
        else if  (strstr($dataKey, 'contributor'))
                return 'Contributor';        
    }
    
    /**
     * Method returns which type of title is given
     * @param <String> $dataKey
     * @return <String> Type of Title
     */
    private function getTitleType($dataKey) {
        $dataKey = strtolower($dataKey);
        if (strstr($dataKey, 'main'))
                return 'Main';
        else if (strstr($dataKey, 'abstract'))
                return 'Abstract';
        else if (strstr($dataKey, 'sub'))
                return 'Sub';
        else if (strstr($dataKey, 'additional'))
                return 'Additional';
        else if (strstr($dataKey, 'parent'))
                return 'Parent';        
    }
    
    /**
     * @param String $date
     * @return Opus_Date
     */
    private function castStringToOpusDate($date) {
        return new Opus_Date(new Zend_Date($date));
    }

    /**
     * Methode to prepare a person object for saving in database.
     * @param <type> $dataKey
     * @param <type> $dataValue
     */
    private function preparePersonObject($dataKey = null, $dataValue = null) {            
            $type = 'Person' . $this->getPersonType($dataKey);
            $this->log->debug("Person type:" . $type);

            $counter = $this->getCounter($dataKey);
            $this->log->debug("counter: " . $counter);

            $addFunction = 'add' . $type;
            $person = $this->document->$addFunction(new Opus_Person());

            // person model
            $this->storePersonAttribute($person, $type, 'FirstName', 'first', $counter);
            $this->storePersonAttribute($person, $type, 'LastName', 'last', $counter);
            $this->storePersonAttribute($person, $type, 'Email', 'email', $counter);
            $this->storePersonAttribute($person, $type, 'PlaceOfBirth', 'pob', $counter);
            $this->storePersonAttribute($person, $type, 'AcademicTitle', 'title', $counter);
            $this->storePersonAttribute($person, $type, 'DateOfBirth', 'dob', $counter);

            // link-person-model
            $this->storePersonAttribute($person, $type, 'AllowEmailContact', 'check', $counter);
        }
    
    /**
     * Method stores attributes like name or email for a given person object.
     * @param <Opus_Model_Dependent_LinkDocumentPerson> $person - given person object
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
        if (array_key_exists($index, $this->documentData)) {
            $entry = $this->documentData[$index]['value'];
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
                        $entry = $this->castStringToOpusDate($entry);
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
            }
        }
    }

    private function prepareTitleObject($dataKey, $dataValue) {
        $type = 'Title' . $this->getTitleType($dataKey);
        $this->log->debug("Title type:" . $type);
        $addFunction = 'add' . $type;
        $title = new Opus_Title();
           
        $counter = $this->getCounter($dataKey);
        $this->log->debug("counter: " . $counter);
        $this->storeTitleValue($title, $type, $counter);
        $this->storeTitleLanguage($title, $type, 'Language', $counter);
        $this->document->$addFunction($title);        
    }

    private function storeTitleValue($title, $type, $counter) {
        if ($counter >= 1) {
            $index = $type . $counter;
        }
        else {
            $index = $type;
        }
        $entry = $this->documentData[$index]['value'];
        if ($entry !== "") {
            $this->log->debug("Value of title: " . $entry);
            $title->setValue($entry);                        
        }
    }

    private function storeTitleLanguage($title, $type, $short, $counter) {
        if ($counter >= 1) {
            $index = $type . $short . $counter;
        }
        else {
            $index = $type . $short;
        }
        $entry = $this->documentData[$index]['value'];
        if ($entry !== "") {
            $this->log->debug("Value of title language: " . $entry);
            $title->setLanguage($entry);                        
        }
    }

    private function getSubjectType($dataKey) {
        $dataKey = strtolower($dataKey);
        
        if (strstr($dataKey, 'swd'))
            return 'Swd';        
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
    private function storeSubjectObject($dataKey, $dataValue) {                        
        $type = $this->getSubjectType($dataKey);
        $this->log->debug("subject is a " . $type);
        $counter = (int) $this->getCounter($dataKey);
        $this->log->debug("counter: " . $counter);
        
        $subject = new Opus_Subject();        
        
        if ($type === 'Swd')
            $subject->setLanguage('deu'); 
        else {
            $index = 'Subject'. $type . 'Language' . $counter;
            $entry = $this->documentData[$index]['value']; 
            if ($entry !== "") {
                $subject->setLanguage($entry);                
            }
        }
                        
        $subject->setValue($dataValue);
        $subject->setType(strtolower($type));        
        $this->document->addSubject($subject);    
    }

    /**
     * Store a note in the current document
     * @param type $dataValue Note text
     */
    private function storeNoteObject($dataValue) {        
        $note = new Opus_Note();
        $note->setMessage($dataValue);
        $note->setVisibility("private");
        $this->document->addNote($note);
    }

    /**
     * Store a collection in the current document.
     * @param type $dataValue Collection ID
     */
    private function storeCollectionObject($dataValue) {
        if (strstr($dataValue, 'ID:')) {
            $dataValue = substr($dataValue, 3);
        }
        //store a simple collection
        $this->document->addCollection(new Opus_Collection($dataValue));        
    }

    /**
     * Prepare and store a series in the current document.
     * @param String $dataKey Fieldname of series number
     * @param String $dataValue Number of series     
     */
    private function storeSeriesObject($dataKey, $dataValue) {
        //find the series ID
        $id = str_replace('Number', '', $dataKey);
        $seriesId = $this->documentData[$id]['value'];
        $this->log->debug('Deposit: ' . $dataKey . ' and ' . $id . ' = ' . $seriesId);
        
        if (strstr($seriesId, 'ID:')) {
            $seriesId = substr($seriesId, 3);
        }
        $s = new Opus_Series($seriesId);
        
        //store a simple collection
        $this->document->addSeries($s)->setNumber($dataValue);                      
    }

    /**
     * Prepare and store a licence for the current document.     
     * @param <type> $dataValue Licence ID
     */
    private function storeLicenceObject($dataValue) {        
        if (strstr($dataValue, 'ID:')) {
            $dataValue = substr($dataValue, 3);
        }        
        $licence = new Opus_Licence($dataValue);
        $this->document->addLicence($licence);
    }

    /**
     * Prepare and store a dnb institute for the current document.
     * @param <type> $grantor
     * @param <type> $dataValue
     */
    private function storeThesisObject($dataValue, $grantor=false) {
        if (strstr($dataValue, 'ID:')) {
            $dataValue = substr($dataValue, 3);
            $thesis = new Opus_DnbInstitute($dataValue);
        }
        if ($grantor)
            $this->document->addThesisGrantor($thesis);
        else
            $this->document->addThesisPublisher($thesis);
        
    }        

    private function storeIdentifierObject($dataKey, $dataValue) {        
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
        else if (strstr($dataKey, 'Arxiv')) {
            $this->document->addIdentifierArxiv($identifier);
        }
        else if (strstr($dataKey, 'Pubmed')) {
            $this->document->addIdentifierPubmed($identifier);
        }
    }

    private function storeReferenceObject($dataKey, $dataValue) {
        //TODO: probably no valid storing possible because a label is missing
        //a reference should be a new datatype with implicit fields value and label
        
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

    private function storeEnrichmentObject($dataKey, $dataValue) {
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
