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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Collection;
use Opus\Common\Date;
use Opus\Common\DnbInstitute;
use Opus\Common\Document;
use Opus\Common\DocumentInterface;
use Opus\Common\Enrichment;
use Opus\Common\Identifier;
use Opus\Common\Licence;
use Opus\Common\Model\ModelException;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Note;
use Opus\Common\Person;
use Opus\Common\Series;
use Opus\Common\Subject;
use Opus\Common\Title;
use Opus\Model\Dependent\Link\DocumentPerson;
use Opus\Reference;

/**
 * TODO get logger from Application_Configuration if not provided
 */
class Publish_Model_Deposit
{
    /** @var DocumentInterface */
    private $document;

    /** @var array */
    private $documentData;

    /** @var Zend_Log */
    private $log;

    /** @var int */
    private $docId;

    /**
     * @param null|Zend_Log $log
     */
    public function __construct($log = null)
    {
        $this->log = $log;
    }

    /**
     * @param int           $docId
     * @param Zend_Log|null $log
     * @param array|null    $documentData
     * @param string|null   $documentType
     * @throws Publish_Model_Exception
     * @throws Publish_Model_FormDocumentNotFoundException
     */
    public function storeDocument($docId, $log = null, $documentData = null, $documentType = null)
    {
        if ($log !== null) {
            $this->log = $log;
        }

        $this->docId = $docId;

        try {
            $this->document = Document::get($this->docId);

            if ($documentType !== null) {
                $this->document->setType($documentType);
            }
        } catch (NotFoundException $e) {
            $this->log->err('Could not find document ' . $this->docId . ' in database');
            throw new Publish_Model_FormDocumentNotFoundException();
        }

        $this->documentData = $documentData;
        if ($this->document->getServerState() !== 'temporary') {
            $this->log->err('unexpected state: document ' . $this->docId . ' is not in ServerState "temporary"');
            throw new Publish_Model_FormDocumentNotFoundException();
        }

        $this->storeDocumentData();
    }

    /**
     * @return DocumentInterface
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * @throws Publish_Model_Exception If a requested OPUS_Model does not exist in database.
     */
    private function storeDocumentData()
    {
        foreach ($this->documentData as $dataKey => $dataEntry) {
            $datasetType  = $dataEntry['datatype'];
            $dataValue    = $dataEntry['value'];
            $dataSubfield = $dataEntry['subfield'];

            $this->log->debug("Store -- " . $datasetType . " --");
            $this->log->debug("Name: " . $dataKey . " : Value " . $dataValue . " (" . $dataSubfield . ")");

            if (! $dataSubfield) {
                switch ($datasetType) {
                    case 'Person':
                        $this->preparePersonObject($dataKey, $dataValue);
                        break;
                    case 'Title':
                        $this->prepareTitleObject($dataKey, $dataValue);
                        break;
                    case 'Subject':
                        $this->storeSubjectObject($dataKey, $dataValue);
                        break;
                    case 'Note':
                        $this->storeNoteObject($dataValue);
                        break;
                    case 'Collection':
                    case 'CollectionLeaf':
                        $this->storeCollectionObject($dataValue);
                        break;
                    case 'Licence':
                        $this->storeLicenceObject($dataValue);
                        break;
                    case 'ThesisGrantor':
                        $this->storeThesisObject($dataValue, true);
                        break;
                    case 'ThesisPublisher':
                        $this->storeThesisObject($dataValue, false);
                        break;
                    case 'Identifier':
                        $this->storeIdentifierObject($dataKey, $dataValue);
                        break;
                    case 'Reference':
                        $this->storeReferenceObject($dataKey, $dataValue);
                        break;
                    case 'Enrichment':
                        $this->storeEnrichmentObject($dataKey, $dataValue);
                        break;
                    case 'Series':
                        break;
                    case 'SeriesNumber':
                        $this->storeSeriesObject($dataKey, $dataValue);
                        break;

                    default:
                        $this->log->debug(
                            "Want to store a internal field: type = " . $datasetType . " name = " . $dataKey
                            . " value = " . $dataValue
                        );
                        $this->storeInternalValue($datasetType, $dataKey, $dataValue);
                }
            }
        }
    }

    /**
     * @param string $datasetType
     * @param string $dataKey
     * @param string $dataValue
     * @throws Publish_Model_Exception
     */
    private function storeInternalValue($datasetType, $dataKey, $dataValue)
    {
        if ($datasetType === 'Date') {
            if ($dataValue !== null && $dataValue !== '') {
                $dataValue = $this->castStringToOpusDate($dataValue);
            }
        }

        //external Field
        if ($this->document->hasMultipleValueField($dataKey)) {
            $function = "add" . $dataKey;
            try {
                $addedValue = $this->document->$function();
                $addedValue->setValue($dataValue);
            } catch (ModelException $e) {
                $this->log->err(
                    "could not add field $dataKey with value $dataValue to document " . $this->docId . ' : '
                    . $e->getMessage()
                );
                throw new Publish_Model_Exception();
            }
        } else {
            //internal Fields
            if ($dataKey === 'Language') {
                $files = $this->document->getFile();
                foreach ($files as $file) {
                    $file->setLanguage($dataValue);
                }
            }

            $function = "set" . $dataKey;
            try {
                $this->document->$function($dataValue);
            } catch (ModelException $e) {
                $this->log->err(
                    "could not set field $dataKey with value $dataValue to document " . $this->docId . ' : '
                    . $e->getMessage()
                );
                throw new Publish_Model_Exception();
            }
        }
    }

    /**
     * Method to retrieve a possible counter from key name. The counter is divided by _ from element name.
     * If _x can't be found, 0 is returned.
     *
     * @param string $dataKey
     * @return int Counter or 0
     */
    private function getCounter($dataKey)
    {
        //counters may appear after _
        if (strstr($dataKey, '_')) {
            $array   = explode('_', $dataKey);
            $i       = count($array);
            $counter = $array[$i - 1];
            return (int) $counter;
        } else {
            return 0;
        }
    }

    /**
     * Method returns which type of person is given
     *
     * @param string $dataKey
     * @return string|null Type of Person
     */
    private function getPersonType($dataKey)
    {
        $dataKey = strtolower($dataKey);
        if (strstr($dataKey, 'author')) {
            return 'Author';
        } elseif (strstr($dataKey, 'submitter')) {
            return 'Submitter';
        } elseif (strstr($dataKey, 'referee')) {
            return 'Referee';
        } elseif (strstr($dataKey, 'editor')) {
            return 'Editor';
        } elseif (strstr($dataKey, 'advisor')) {
            return 'Advisor';
        } elseif (strstr($dataKey, 'translator')) {
            return 'Translator';
        } elseif (strstr($dataKey, 'contributor')) {
            return 'Contributor';
        } elseif (strstr($dataKey, 'other')) {
            return 'Other';
        }

        return null; // TODO throw exception
    }

    /**
     * Method returns which type of title is given
     *
     * @param string $dataKey
     * @return string|null Type of Title
     */
    private function getTitleType($dataKey)
    {
        $dataKey = strtolower($dataKey);
        if (strstr($dataKey, 'main')) {
                return 'Main';
        } elseif (strstr($dataKey, 'abstract')) {
                return 'Abstract';
        } elseif (strstr($dataKey, 'sub')) {
                return 'Sub';
        } elseif (strstr($dataKey, 'additional')) {
                return 'Additional';
        } elseif (strstr($dataKey, 'parent')) {
                return 'Parent';
        }

        return null; // TODO throw exception?
    }

    /**
     * @param string $date
     * @return Date
     */
    public function castStringToOpusDate($date)
    {
        $dates = new Application_Controller_Action_Helper_Dates();
        return $dates->getOpusDate($date);
    }

    /**
     * Methode to prepare a person object for saving in database.
     *
     * @param string|null $dataKey
     * @param string|null $dataValue
     */
    private function preparePersonObject($dataKey = null, $dataValue = null)
    {
            $type = 'Person' . $this->getPersonType($dataKey);
            $this->log->debug("Person type:" . $type);

            $counter = $this->getCounter($dataKey);
            $this->log->debug("counter: " . $counter);

            $addFunction = 'add' . $type;
        try {
            $person = $this->document->$addFunction(Person::new());
        } catch (ModelException $e) {
            $this->log->err(
                "could not add person of type $type to document " . $this->docId . " : " . $e->getMessage()
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
     *
     * @param DocumentPerson $person - given person object
     * @param string         $personType - type of person (editor, author etc.)
     * @param string         $attribute - the value to store
     * @param string         $attributeType - type of attribute (first name, email etc.)
     * @param int            $counter - number in case of more than one person per type
     */
    private function storePersonAttribute($person, $personType, $attribute, $attributeType, $counter)
    {
        if ($counter >= 1) {
            $index = $personType . $attribute . '_' . $counter;
        } else {
            $index = $personType . $attribute;
        }
        if (array_key_exists($index, $this->documentData)) {
            $entry = $this->documentData[$index]['value'];
            if ($entry !== "") {
                switch ($attributeType) {
                    case 'first':
                        $this->log->debug("First name: " . $entry);
                        $person->setFirstName($entry);
                        break;
                    case 'last':
                        $this->log->debug("Last name: " . $entry);
                        $person->setLastName($entry);
                        break;
                    case 'email':
                        $this->log->debug("Email: " . $entry);
                        $person->setEmail($entry);
                        break;
                    case 'pob':
                        $this->log->debug("Place of Birth: " . $entry);
                        $person->setPlaceOfBirth($entry);
                        break;
                    case 'title':
                        $this->log->debug("Academic Title: " . $entry);
                        $person->setAcademicTitle($entry);
                        break;
                    case 'dob':
                        $entry = $this->castStringToOpusDate($entry);
                        $this->log->debug("Date of Birth: " . $entry);
                        $person->setDateOfBirth($entry);
                        break;
                    case 'check':
                        $this->log->debug("Allow Email Contact?: " . $entry);
                        if ($entry === null) {
                            $entry = 0;
                        }
                        $person->setAllowEmailContact($entry);
                        break;
                    case 'Identifier':
                        $this->log->debug("Identifier?: " . $entry);
                        $functionName = 'set' . $attribute;
                        $person->$functionName($entry);
                        break;
                }
            }
        }
    }

    /**
     * @param string $dataKey
     * @param string $dataValue
     * @throws Publish_Model_Exception
     */
    private function prepareTitleObject($dataKey, $dataValue)
    {
        $type = 'Title' . $this->getTitleType($dataKey);
        $this->log->debug("Title type:" . $type);
        $addFunction = 'add' . $type;
        $title       = Title::new();

        $counter = $this->getCounter($dataKey);
        $this->log->debug("counter: " . $counter);
        $this->storeTitleValue($title, $type, $counter);
        $this->storeTitleLanguage($title, $type, 'Language', $counter);
        try {
            $this->document->$addFunction($title);
        } catch (ModelException $e) {
            $this->log->err(
                "could not add title of type $type to document " . $this->docId . " : " . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * @param string $title
     * @param string $type
     * @param int    $counter
     */
    private function storeTitleValue($title, $type, $counter)
    {
        if ($counter >= 1) {
            $index = $type . '_' . $counter;
        } else {
            $index = $type;
        }
        $entry = $this->documentData[$index]['value'];
        if ($entry !== "") {
            $this->log->debug("Value of title: " . $entry);
            $title->setValue($entry);
        }
    }

    /**
     * @param object $title
     * @param string $type
     * @param string $short
     * @param int    $counter
     */
    private function storeTitleLanguage($title, $type, $short, $counter)
    {
        if ($counter >= 1) {
            $index = $type . $short . '_' . $counter;
        } else {
            $index = $type . $short;
        }
        $entry = $this->documentData[$index]['value'];
        if ($entry !== '') {
            $this->log->debug("Value of title language: " . $entry);
            $title->setLanguage($entry);
        }
    }

    /**
     * @param string $dataKey
     * @return string
     */
    private function getSubjectType($dataKey)
    {
        $dataKey = strtolower($dataKey);

        if (strstr($dataKey, 'swd')) {
            return 'Swd';
        } else {
            return 'Uncontrolled';
        }
    }

    /**
     * method to prepare a subject object for storing
     *
     * @param string $dataKey
     * @param string $dataValue
     */
    private function storeSubjectObject($dataKey, $dataValue)
    {
        $type = $this->getSubjectType($dataKey);
        $this->log->debug("subject is a " . $type);
        $counter = $this->getCounter($dataKey);
        $this->log->debug("counter: " . $counter);

        $subject = Subject::new();

        if ($type === 'Swd') {
            $subject->setLanguage('deu');
        } else {
            $index = 'Subject' . $type . 'Language_' . $counter;
            $entry = $this->documentData[$index]['value'];
            if ($entry !== "") {
                $subject->setLanguage($entry);
            }
        }

        $subject->setValue($dataValue);
        $subject->setType(strtolower($type));
        try {
            $this->document->addSubject($subject);
        } catch (ModelException $e) {
            $this->log->err(
                "could not add subject of type $dataKey with value $dataValue to document " . $this->docId . " : "
                . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * Store a note in the current document
     *
     * @param string $dataValue Note text
     */
    private function storeNoteObject($dataValue)
    {
        $note = Note::new();
        $note->setMessage($dataValue);
        $note->setVisibility("private");
        try {
            $this->document->addNote($note);
        } catch (ModelException $e) {
            $this->log->err(
                "could not add note with message $dataValue to document " . $this->docId . " : " . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * Store a collection in the current document.
     *
     * @param string $dataValue Collection ID
     */
    private function storeCollectionObject($dataValue)
    {
        try {
            $collection = Collection::get($dataValue);
        } catch (NotFoundException $e) {
            $this->log->err('Could not find collection #' . $dataValue . ' in database');
            throw new Publish_Model_Exception();
        }

        try {
            $this->document->addCollection($collection);
        } catch (ModelException $e) {
            $this->log->err(
                "could not add collection #$dataValue to document " . $this->docId . " : " . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * Prepare and store a series in the current document.
     *
     * @param string $dataKey Fieldname of series number
     * @param string $dataValue Number of series
     */
    private function storeSeriesObject($dataKey, $dataValue)
    {
        //find the series ID
        $id       = str_replace('Number', '', $dataKey);
        $seriesId = $this->documentData[$id]['value'];
        $this->log->debug('Deposit: ' . $dataKey . ' and ' . $id . ' = ' . $seriesId);

        try {
            $s = Series::get($seriesId);
        } catch (ModelException $e) {
            $this->log->err('Could not find series #' . $dataValue . ' in database');
            throw new Publish_Model_Exception();
        }

        try {
            $this->document->addSeries($s)->setNumber($dataValue);
        } catch (ModelException $e) {
            $this->log->err(
                "could not add series #$seriesId with number $dataValue to document " . $this->docId . " : "
                . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * Prepare and store a licence for the current document.
     *
     * @param string $dataValue Licence ID
     */
    private function storeLicenceObject($dataValue)
    {
        try {
            $licence = Licence::get($dataValue);
        } catch (ModelException $e) {
            $this->log->err('Could not find licence #' . $dataValue . ' in database');
            throw new Publish_Model_Exception();
        }

        try {
            $this->document->addLicence($licence);
        } catch (ModelException $e) {
            $this->log->err(
                "could not add licence #$dataValue to document " . $this->docId . " : " . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * Prepare and store a dnb institute for the current document.
     *
     * @param int  $dataValue
     * @param bool $grantor
     */
    private function storeThesisObject($dataValue, $grantor = false)
    {
        try {
            $thesis = DnbInstitute::get($dataValue);
        } catch (ModelException $e) {
            $this->log->err('Could not find DnbInstitute #' . $dataValue . ' in database');
            throw new Publish_Model_Exception();
        }

        try {
            if ($grantor) {
                $this->document->addThesisGrantor($thesis);
            } else {
                $this->document->addThesisPublisher($thesis);
            }
        } catch (ModelException $e) {
            $function = $grantor ? 'grantor' : 'publisher';
            $this->log->err(
                "could not add DnbInstitute #$dataValue as $function to document " . $this->docId . " : "
                . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * @param string $dataKey
     * @param string $dataValue
     * @throws Publish_Model_Exception
     */
    private function storeIdentifierObject($dataKey, $dataValue)
    {
        $identifier = Identifier::new();
        $identifier->setValue($dataValue);
        try {
            if (strstr($dataKey, 'Old')) {
                $this->document->addIdentifierOld($identifier);
            } elseif (strstr($dataKey, 'Serial')) {
                $this->document->addIdentifierSerial($identifier);
            } elseif (strstr($dataKey, 'Uuid')) {
                $this->document->addIdentifierUuid($identifier);
            } elseif (strstr($dataKey, 'Isbn')) {
                $this->document->addIdentifierIsbn($identifier);
            } elseif (strstr($dataKey, 'Urn')) {
                $this->document->addIdentifierUrn($identifier);
            } elseif (strstr($dataKey, 'StdDoi')) {
                $this->document->addIdentifierStdDoi($identifier);
            } elseif (strstr($dataKey, 'Doi')) {
                $this->document->addIdentifierDoi($identifier);
            } elseif (strstr($dataKey, 'Handle')) {
                $this->document->addIdentifierHandle($identifier);
            } elseif (strstr($dataKey, 'SplashUrl')) {
                $this->document->addIdentifierSplashUrl($identifier);
            } elseif (strstr($dataKey, 'Url')) {
                $this->document->addIdentifierUrl($identifier);
            } elseif (strstr($dataKey, 'Issn')) {
                $this->document->addIdentifierIssn($identifier);
            } elseif (strstr($dataKey, 'CrisLink')) {
                $this->document->addIdentifierCrisLink($identifier);
            } elseif (strstr($dataKey, 'SplashUrl')) {
                $this->document->addIdentifierSplashUrl($identifier);
            } elseif (strstr($dataKey, 'Opus3')) {
                $this->document->addIdentifierOpus3($identifier);
            } elseif (strstr($dataKey, 'Opac')) {
                $this->document->addIdentifierOpac($identifier);
            } elseif (strstr($dataKey, 'Arxiv')) {
                $this->document->addIdentifierArxiv($identifier);
            } elseif (strstr($dataKey, 'Pubmed')) {
                $this->document->addIdentifierPubmed($identifier);
            }
        } catch (ModelException $e) {
            $this->log->err(
                "could not add identifier of type $dataKey with value $dataValue to document " . $this->docId . " : "
                . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * @deprecated
     *
     * @param string $dataKey
     * @param string $dataValue
     */
    private function storeReferenceObject($dataKey, $dataValue)
    {
        //TODO: probably no valid storing possible because a label is missing
        //a reference should be a new datatype with implicit fields value and label

        $reference = new Reference();
        $reference->setValue($dataValue);
        $reference->setLabel("no Label given");
        try {
            if (strstr($dataKey, 'Isbn')) {
                $this->document->addReferenceIsbn($reference);
            } elseif (strstr($dataKey, 'Urn')) {
                $this->document->addReferenceUrn($reference);
            } elseif (strstr($dataKey, 'Doi')) {
                $this->document->addReferenceDoi($reference);
            } elseif (strstr($dataKey, 'Handle')) {
                $this->document->addReferenceHandle($reference);
            } elseif (strstr($dataKey, 'Url')) {
                $this->document->addReferenceUrl($reference);
            } elseif (strstr($dataKey, 'Issn')) {
                $this->document->addReferenceIssn($reference);
            } elseif (strstr($dataKey, 'StdDoi')) {
                $this->document->addReferenceStdDoi($reference);
            } elseif (strstr($dataKey, 'CrisLink')) {
                $this->document->addReferenceCrisLink($reference);
            } elseif (strstr($dataKey, 'SplashUrl')) {
                $this->document->addReferenceSplashUrl($reference);
            }
        } catch (ModelException $e) {
            $this->log->err(
                "could not add reference of type $dataKey with value $dataValue to document " . $this->docId . " : "
                . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }

    /**
     * @param string $dataKey
     * @param string $dataValue
     * @throws Publish_Model_Exception
     */
    private function storeEnrichmentObject($dataKey, $dataValue)
    {
        $counter = $this->getCounter($dataKey);
        if ($counter !== 0) {
            //remove possible counter
            $dataKey = str_replace('_' . $counter, '', $dataKey);
        }

        $this->log->debug("try to store " . $dataKey . " with id: " . $dataValue);
        $keyName = str_replace('Enrichment', '', $dataKey);

        $enrichment = Enrichment::new();
        $enrichment->setValue($dataValue);
        $enrichment->setKeyName($keyName);

        try {
            $this->document->addEnrichment($enrichment);
        } catch (ModelException $e) {
            $this->log->err(
                "could not add enrichment key $keyName with value $dataValue to document "
                . $this->docId . " : " . $e->getMessage()
            );
            throw new Publish_Model_Exception();
        }
    }
}
