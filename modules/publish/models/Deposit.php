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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Publish_Model_Deposit {

    private $_document;
    private $_documentData;
    private $_log;
    private $_docId;

    public function __construct($docId, $log, $documentData = null) {
        $this->_log = $log;
        $this->_docId = $docId;

        try {
            $this->_document = new Opus_Document($this->_docId);
        }
        catch (Opus_Model_NotFoundException $e) {
            $this->_log->err('Could not find document ' . $this->_docId . ' in database');
            throw new Publish_Model_FormDocumentNotFoundException();
        }

        $this->_documentData = $documentData;
        if ($this->_document->getServerState() !== 'temporary') {
            $this->_log->err('unexpected state: document ' . $this->_docId . ' is not in ServerState "temporary"');
            throw new Publish_Model_FormDocumentNotFoundException();
        }

        $this->_storeDocumentData();
    }

    public function getDocument() {
        return $this->_document;
    }

    /**
     * @throws Publish_Model_Exception if a requested OPUS_Model does not exist in database
     */
    private function _storeDocumentData() {

        foreach ($this->_documentData as $dataKey => $dataEntry) {
            $datasetType = $dataEntry['datatype'];
            $dataValue = $dataEntry['value'];
            $dataSubfield = $dataEntry['subfield'];

            $this->_log->debug("Store -- " . $datasetType . " --");
            $this->_log->debug("Name: " . $dataKey . " : Value " . $dataValue . " (" . $dataSubfield . ")");

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
                        $this->_log->debug(
                            "Want to store a internal field: type = " . $datasetType . " name = " . $dataKey
                            . " value = " . $dataValue
                        );
                        $this->storeInternalValue($datasetType, $dataKey, $dataValue);
                }
            }
        }
    }

    private function storeInternalValue($datasetType, $dataKey, $dataValue) {

        if ($datasetType === 'Date') {
            if (!is_null($dataValue) and $dataValue !== "") {
                $dataValue = $this->castStringToOpusDate($dataValue);
            }
        }

        //external Field
        if ($this->_document->hasMultipleValueField($dataKey)) {
            $function = "add" . $dataKey;
            try {
                $addedValue = $this->_document->$function();
                $addedValue->setValue($dataValue);
            }
            catch (Opus_Model_Exception $e) {
                $this->_log->err(
                    "could not add field $dataKey with value $dataValue to document " . $this->_docId . " : "
                    . $e->getMessage()
                );
                throw new Publish_Model_Exception();
            }
        }
        else {
            //internal Fields
            if ($dataKey === 'Language') {
                $files = $this->_document->getFile();
                foreach ($files as $file) {
                    $file->setLanguage($dataValue);
                }
            }

            $function = "set" . $dataKey;
            try {
                $this->_document->$function($dataValue);
            }
            catch (Opus_Model_Exception $e) {
                $this->_log->err(
                    "could not set field $dataKey with value $dataValue to document " . $this->_docId . " : "
                    . $e->getMessage()
                );
                throw new Publish_Model_Exception();
            }
        }
    }

    /**
     * Method to retrieve a possible counter from key name. The counter is divided by _ from element name.
     * If _x can't be found, 0 is returned.
     * @param <String> $dataKey
     * @return <Int> counter or 0
     */
    private function getCounter($dataKey) {
        //counters may appear after _
        if (strstr($dataKey, '_')) {
            $array = explode('_', $dataKey);
            $i = count($array);
            $counter = $array[$i-1];
            return (int) $counter;
        }
        else {
            return 0;
        }
    }

    /**
     * Method returns which type of person is given
     * @param <String> $dataKey
     * @return <String> Type of Person
     */
    private function getPersonType($dataKey) {
        $dataKey = strtolower($dataKey);
        if (strstr($dataKey, 'author')) {
            return 'Author';
        }
        else if (strstr($dataKey, 'submitter')) {
            return 'Submitter';
        }
        else if (strstr($dataKey, 'referee')) {
            return 'Referee';
        }
        else if (strstr($dataKey, 'editor')) {
            return 'Editor';
        }
        else if (strstr($dataKey, 'advisor')) {
            return 'Advisor';
        }
        else if (strstr($dataKey, 'translator')) {
            return 'Translator';
        }
        else if (strstr($dataKey, 'contributor')) {
            return 'Contributor';
        }
        else if (strstr($dataKey, 'other')) {
            return 'Other';
        }
    }

    /**
     * Method returns which type of title is given
     * @param <String> $dataKey
     * @return <String> Type of Title
     */
    private function getTitleType($dataKey) {
        $dataKey = strtolower($dataKey);
        if (strstr($dataKey, 'main')) {
                return 'Main';
        }
        else if (strstr($dataKey, 'abstract')) {
                return 'Abstract';
        }
        else if (strstr($dataKey, 'sub')) {
                return 'Sub';
        }
        else if (strstr($dataKey, 'additional')) {
                return 'Additional';
        }
        else if (strstr($dataKey, 'parent')) {
                return 'Parent';
        }
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
            $this->_log->debug("Person type:" . $type);

            $counter = $this->getCounter($dataKey);
            $this->_log->debug("counter: " . $counter);

            $addFunction = 'add' . $type;
            try {
                $person = $this->_document->$addFunction(new Opus_Person());
            }
            catch (Opus_Model_Exception $e) {
                $this->_log->err(
                    "could not add person of type $type to document " . $this->_docId . " : " . $e->getMessage()
                );
                throw new Publish_Model_Exception();
            }

            // person model
            $this->storePersonAttribute($person, $type, 'FirstName', 'first', $counter);
            $this->storePersonAttribute($person, $type, 'LastName', 'last', $counter);
            $this->storePersonAttribute($person, $type, 'Email', 'email', $counter);
            $this->storePersonAttribute($person, $type, 'PlaceOfBirth', 'pob', $counter);
            $this->storePersonAttribute($person, $type, 'AcademicTitle', 'title', $counter);
            $this->storePersonAttribute($person, $type, 'DateOfBirth', 'dob', $counter);
            $this->storePersonAttribute($person, $type, 'IdentifierGnd', 'Identifier', $counter);
            $this->storePersonAttribute($person, $type, 'IdentifierOrcid', 'Identifier', $counter);
            $this->storePersonAttribute($person, $type, 'IdentifierMisc', 'Identifier', $counter);

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
            $index = $personType . $attribute . '_' . $counter;
        }
        else {
            $index = $personType . $attribute;
        }
        if (array_key_exists($index, $this->_documentData)) {
            $entry = $this->_documentData[$index]['value'];
            if ($entry !== "") {
                switch ($attributeType) {
                    case 'first' :
                        $this->_log->debug("First name: " . $entry);
                        $person->setFirstName($entry);
                        break;
                    case 'last' :
                        $this->_log->debug("Last name: " . $entry);
                        $person->setLastName($entry);
                        break;
                    case 'email' :
                        $this->_log->debug("Email: " . $entry);
                        $person->setEmail($entry);
                        break;
                    case 'pob' :
                        $this->_log->debug("Place of Birth: " . $entry);
                        $person->setPlaceOfBirth($entry);
                        break;
                    case 'title' :
                        $this->_log->debug("Academic Title: " . $entry);
                        $person->setAcademicTitle($entry);
                        break;
                    case 'dob' :
                        $entry = $this->castStringToOpusDate($entry);
                        $this->_log->debug("Date of Birth: " . $entry);
                        $person->setDateOfBirth($entry);
                        break;
                    case 'check' :
                        $this->_log->debug("Allow Email Contact?: " . $entry);
                        if (is_null($entry)) {
                            $entry = 0;
                        }
                        $person->setAllowEmailContact($entry);
                        break;
                    case 'Identifier' :
                        $this->_log->debug("Identifier?: " . $entry);
                        $functionName = 'set' . $attribute;
                        $person->$functionName($entry);
                        break;
                }
            }
        }
    }

    private function prepareTitleObject($dataKey, $dataValue) {
        $type = 'Title' . $this->getTitleType($dataKey);
        $this->_log->debug("Title type:" . $type);
        $addFunction = 'add' . $type;
        $title = new Opus_Title();

        $counter = $this->getCounter($dataKey);
        $this->_log->debug("counter: " . $counter);
        $this->storeTitleValue($title, $type, $counter);
        $this->storeTitleLanguage($title, $type, 'Language', $counter);
        try {
            $this->_document->$addFunction($title);
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err(
                "could not add title of type $type to document " . $this->_docId . " : " . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    private function storeTitleValue($title, $type, $counter) {
        if ($counter >= 1) {
            $index = $type .  '_' . $counter;
        }
        else {
            $index = $type;
        }
        $entry = $this->_documentData[$index]['value'];
        if ($entry !== "") {
            $this->_log->debug("Value of title: " . $entry);
            $title->setValue($entry);
        }
    }

    private function storeTitleLanguage($title, $type, $short, $counter) {
        if ($counter >= 1) {
            $index = $type . $short . '_' . $counter;
        }
        else {
            $index = $type . $short;
        }
        $entry = $this->_documentData[$index]['value'];
        if ($entry !== "") {
            $this->_log->debug("Value of title language: " . $entry);
            $title->setLanguage($entry);
        }
    }

    private function getSubjectType($dataKey) {
        $dataKey = strtolower($dataKey);

        if (strstr($dataKey, 'swd')) {
            return 'Swd';
        }
        else {
            return 'Uncontrolled';
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
    private function storeSubjectObject($dataKey, $dataValue) {
        $type = $this->getSubjectType($dataKey);
        $this->_log->debug("subject is a " . $type);
        $counter = $this->getCounter($dataKey);
        $this->_log->debug("counter: " . $counter);

        $subject = new Opus_Subject();

        if ($type === 'Swd') {
            $subject->setLanguage('deu');
        }
        else {
            $index = 'Subject'. $type . 'Language' . '_' . $counter;
            $entry = $this->_documentData[$index]['value'];
            if ($entry !== "") {
                $subject->setLanguage($entry);
            }
        }

        $subject->setValue($dataValue);
        $subject->setType(strtolower($type));
        try {
            $this->_document->addSubject($subject);
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err(
                "could not add subject of type $dataKey with value $dataValue to document " . $this->_docId . " : "
                . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * Store a note in the current document
     * @param type $dataValue Note text
     */
    private function storeNoteObject($dataValue) {
        $note = new Opus_Note();
        $note->setMessage($dataValue);
        $note->setVisibility("private");
        try {
            $this->_document->addNote($note);
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err(
                "could not add note with message $dataValue to document " . $this->_docId . " : " . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * Store a collection in the current document.
     * @param type $dataValue Collection ID
     */
    private function storeCollectionObject($dataValue) {
        try {
            $collection = new Opus_Collection($dataValue);
        }
        catch (Opus_Model_NotFoundException $e) {
            $this->_log->err('Could not find collection #' . $dataValue . ' in database');
            throw new Publish_Model_Exception();
        }

        try {
            $this->_document->addCollection($collection);
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err(
                "could not add collection #$dataValue to document " . $this->_docId . " : " . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * Prepare and store a series in the current document.
     * @param String $dataKey Fieldname of series number
     * @param String $dataValue Number of series
     */
    private function storeSeriesObject($dataKey, $dataValue) {
        //find the series ID
        $id = str_replace('Number', '', $dataKey);
        $seriesId = $this->_documentData[$id]['value'];
        $this->_log->debug('Deposit: ' . $dataKey . ' and ' . $id . ' = ' . $seriesId);

        try {
            $s = new Opus_Series($seriesId);
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err('Could not find series #' . $dataValue . ' in database');
            throw new Publish_Model_Exception();
        }

        try {
            $this->_document->addSeries($s)->setNumber($dataValue);
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err(
                "could not add series #$seriesId with number $dataValue to document " . $this->_docId . " : "
                . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * Prepare and store a licence for the current document.
     * @param <type> $dataValue Licence ID
     */
    private function storeLicenceObject($dataValue) {
        try {
            $licence = new Opus_Licence($dataValue);
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err('Could not find licence #' . $dataValue . ' in database');
            throw new Publish_Model_Exception();
        }

        try {
            $this->_document->addLicence($licence);
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err(
                "could not add licence #$dataValue to document " . $this->_docId . " : " . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * Prepare and store a dnb institute for the current document.
     * @param <type> $grantor
     * @param <type> $dataValue
     */
    private function storeThesisObject($dataValue, $grantor=false) {
        try {
            $thesis = new Opus_DnbInstitute($dataValue);
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err('Could not find DnbInstitute #' . $dataValue . ' in database');
            throw new Publish_Model_Exception();
        }

        try {
            if ($grantor) {
                $this->_document->addThesisGrantor($thesis);
            }
            else {
                $this->_document->addThesisPublisher($thesis);
            }
        }
        catch (Opus_Model_Exception $e) {
            $function = ($grantor) ? 'grantor' : 'publisher';
            $this->_log->err(
                "could not add DnbInstitute #$dataValue as $function to document " . $this->_docId . " : "
                . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    private function storeIdentifierObject($dataKey, $dataValue) {
        $identifier = new Opus_Identifier();
        $identifier->setValue($dataValue);
        try {
            if (strstr($dataKey, 'Old')) {
                $this->_document->addIdentifierOld($identifier);
            }
            else if (strstr($dataKey, 'Serial')) {
                $this->_document->addIdentifierSerial($identifier);
            }
            else if (strstr($dataKey, 'Uuid')) {
                $this->_document->addIdentifierUuid($identifier);
            }
            else if (strstr($dataKey, 'Isbn')) {
                $this->_document->addIdentifierIsbn($identifier);
            }
            else if (strstr($dataKey, 'Urn')) {
                $this->_document->addIdentifierUrn($identifier);
            }
            else if (strstr($dataKey, 'StdDoi')) {
                $this->_document->addIdentifierStdDoi($identifier);
            }
            else if (strstr($dataKey, 'Doi')) {
                $this->_document->addIdentifierDoi($identifier);
            }
            else if (strstr($dataKey, 'Handle')) {
                $this->_document->addIdentifierHandle($identifier);
            }
            else if (strstr($dataKey, 'SplashUrl')) {
                $this->_document->addIdentifierSplashUrl($identifier);
            }
            else if (strstr($dataKey, 'Url')) {
                $this->_document->addIdentifierUrl($identifier);
            }
            else if (strstr($dataKey, 'Issn')) {
                $this->_document->addIdentifierIssn($identifier);
            }
            else if (strstr($dataKey, 'CrisLink')) {
                $this->_document->addIdentifierCrisLink($identifier);
            }
            else if (strstr($dataKey, 'SplashUrl')) {
                $this->_document->addIdentifierSplashUrl($identifier);
            }
            else if (strstr($dataKey, 'Opus3')) {
                $this->_document->addIdentifierOpus3($identifier);
            }
            else if (strstr($dataKey, 'Opac')) {
                $this->_document->addIdentifierOpac($identifier);
            }
            else if (strstr($dataKey, 'Arxiv')) {
                $this->_document->addIdentifierArxiv($identifier);
            }
            else if (strstr($dataKey, 'Pubmed')) {
                $this->_document->addIdentifierPubmed($identifier);
            }
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err(
                "could not add identifier of type $dataKey with value $dataValue to document " . $this->_docId . " : "
                . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     *
     * @deprecated
     */
    private function storeReferenceObject($dataKey, $dataValue) {
        //TODO: probably no valid storing possible because a label is missing
        //a reference should be a new datatype with implicit fields value and label

        $reference = new Opus_Reference();
        $reference->setValue($dataValue);
        $reference->setLabel("no Label given");
        try {
            if (strstr($dataKey, 'Isbn')) {
                $this->_document->addReferenceIsbn($reference);
            }
            else if (strstr($dataKey, 'Urn')) {
                $this->_document->addReferenceUrn($reference);
            }
            else if (strstr($dataKey, 'Doi')) {
                $this->_document->addReferenceDoi($reference);
            }
            else if (strstr($dataKey, 'Handle')) {
                $this->_document->addReferenceHandle($reference);
            }
            else if (strstr($dataKey, 'Url')) {
                $this->_document->addReferenceUrl($reference);
            }
            else if (strstr($dataKey, 'Issn')) {
                $this->_document->addReferenceIssn($reference);
            }
            else if (strstr($dataKey, 'StdDoi')) {
                $this->_document->addReferenceStdDoi($reference);
            }
            else if (strstr($dataKey, 'CrisLink')) {
                $this->_document->addReferenceCrisLink($reference);
            }
            else if (strstr($dataKey, 'SplashUrl')) {
                $this->_document->addReferenceSplashUrl($reference);
            }
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err(
                "could not add reference of type $dataKey with value $dataValue to document " . $this->_docId . " : "
                . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    private function storeEnrichmentObject($dataKey, $dataValue) {
        $counter = $this->getCounter($dataKey);
        if ($counter != 0) {
            //remove possible counter
            $dataKey = str_replace('_' . $counter, '', $dataKey);
        }

        $this->_log->debug("try to store " . $dataKey . " with id: " . $dataValue);
        $keyName = str_replace('Enrichment', '', $dataKey);

        $enrichment = new Opus_Enrichment();
        $enrichment->setValue($dataValue);
        $enrichment->setKeyName($keyName);

        try {
            $this->_document->addEnrichment($enrichment);
        }
        catch (Opus_Model_Exception $e) {
            $this->_log->err(
                "could not add enrichment key $keyName with value $dataValue to document "
                . $this->_docId . " : " . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

}
