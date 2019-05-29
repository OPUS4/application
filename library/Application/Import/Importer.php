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
 * @package     Import
 * @author      Sascha Szott
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2016-2018
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Application_Import_Importer
{

    private $logfile;
    private $logger;
    private $xml;
    private $xmlFile;
    private $xmlString;
    private $fieldsToKeepOnUpdate = [];

    // variables used in SWORD context
    private $swordContext = false;
    private $importDir = null;

    private $statusDoc = null;

    /**
     * Additional enrichments that will be added to each imported document.
     *
     * This could be for instance a timestamp and other information about the import.
     *
     * @var array
     */
    private $additionalEnrichments = null;

    private $importCollection = null;
    private $singleDocImport = false;

    /**
     * Last imported document.
     *
     * Contains the document object if the import was successful.
     *
     * @var Opus_Document
     */
    private $document;

    public function __construct($xml, $isFile = false, $logger = null, $logfile = null)
    {
        $this->logger = $logger;
        $this->logfile = $logfile;
        if ($isFile) {
            $this->xmlFile = $xml;
        } else {
            $this->xmlString = $xml;
        }
    }

    public function getStatusDoc()
    {
        return $this->statusDoc;
    }

    public function enableSwordContext()
    {
        $this->swordContext = true;
        $this->statusDoc = new Application_Import_ImportStatusDocument();
    }

    public function setImportDir($imporDir)
    {
        $this->importDir = trim($imporDir);
        // always ensure that importDir ends with a directory separator
        if (substr($this->importDir, -1) !== DIRECTORY_SEPARATOR) {
            $this->importDir .= DIRECTORY_SEPARATOR;
        }
    }

    public function setAdditionalEnrichments($additionalEnrichments)
    {
        $this->additionalEnrichments = $additionalEnrichments;
    }

    public function setImportCollection($importCollection)
    {
        $this->importCollection = $importCollection;
    }

    private function initDocument()
    {
        $doc = new Opus_Document();
        // since OPUS 4.5 attribute serverState is optional: if no attribute
        // value is given we set server state to unpublished
        $doc->setServerState('unpublished');
        return $doc;
    }

    /**
     * @throws Application_Import_MetadataImportInvalidXmlException
     * @throws Application_Import_MetadataImportSkippedDocumentsException
     * @throws Opus_Model_Exception
     * @throws Opus_Security_Exception
     */
    public function run()
    {
        $this->setXml();
        $this->validateXml();

        $numOfDocsImported = 0;
        $numOfSkippedDocs = 0;

        $opusDocuments = $this->xml->getElementsByTagName('opusDocument');

        // in case of a single document deposit (via SWORD) we allow to omit
        // the explicit declaration of file elements (within <files>..</files>)
        // and automatically import all files in the root level of the SWORD package
        $this->singleDocImport = $opusDocuments->length == 1;

        foreach ($opusDocuments as $opusDocumentElement) {

            // save oldId for later referencing of the record under consideration
            // according to the latest documentation the value of oldId is not
            // stored as an OPUS identifier
            $oldId = $opusDocumentElement->getAttribute('oldId');
            if ($oldId !== '') { // oldId is now an optional attribute
                $opusDocumentElement->removeAttribute('oldId');
                $this->log("Start processing of record #" . $oldId . " ...");
            }

            /*
             * @var Opus_Document
             */
            $doc = null;
            if ($opusDocumentElement->hasAttribute('docId')) {
                if ($this->swordContext) {
                    // update of existing documents is not supported in SWORD context
                    // ignore docId and create an empty document instead
                    $opusDocumentElement->removeAttribute('docId');
                    $this->log('Value of attribute docId is ignored in SWORD context');
                    $doc = $this->initDocument();
                }
                else {
                    // perform metadata update on given document
                    // please note that existing files that are already associated
                    // with the given document are not deleted or updated
                    $docId = $opusDocumentElement->getAttribute('docId');
                    try {
                        $doc = new Opus_Document($docId);
                        $opusDocumentElement->removeAttribute('docId');
                    } catch (Opus_Model_NotFoundException $e) {
                        $this->log('Could not load document #' . $docId . ' from database: ' . $e->getMessage());
                        $this->appendDocIdToRejectList($oldId);
                        $numOfSkippedDocs++;
                        continue;
                    }

                    $this->resetDocument($doc);
                }
            } else {
                // create a new OPUS document and populate it with data
                $doc = $this->initDocument();
            }

            try {
                $this->processAttributes($opusDocumentElement->attributes, $doc);
                $filesElementFound = $this->processElements($opusDocumentElement->childNodes, $doc);
                if ($this->swordContext && $this->singleDocImport && !$filesElementFound) {
                    // add all files in the root level of the package to the currently
                    // processed document
                    $this->importFilesDirectly($doc);
                }
            } catch (Exception $e) {
                $this->log('Error while processing document #' . $oldId . ': ' . $e->getMessage());
                $this->appendDocIdToRejectList($oldId);
                $numOfSkippedDocs++;
                continue;
            }

            if (!is_null($this->additionalEnrichments)) {
                $enrichments = $this->additionalEnrichments->getEnrichments();
                foreach ($enrichments as $key => $value) {
                    $this->addEnrichment($doc, $key, $value);
                }
            }

            if (!is_null($this->importCollection)) {
                $doc->addCollection($this->importCollection);
            }

            try {
                $doc->store();
                $this->document = $doc;
                if (!is_null($this->statusDoc)) {
                    $this->statusDoc->addDoc($doc);
                }
            } catch (Exception $e) {
                $this->log('Error while saving imported document #' . $oldId . ' to database: ' . $e->getMessage());
                $this->appendDocIdToRejectList($oldId);
                $numOfSkippedDocs++;
                continue;
            }

            $numOfDocsImported++;
            $this->log('... OK');
        }

        if ($numOfSkippedDocs == 0) {
            $this->log("Import finished successfully. $numOfDocsImported documents were imported.");
        } else {
            $this->log("Import finished. $numOfDocsImported documents were imported. $numOfSkippedDocs documents were skipped.");
            if (!$this->swordContext) {
                throw new Application_Import_MetadataImportSkippedDocumentsException("$numOfSkippedDocs documents were skipped during import.");
            }
        }
    }

    private function log($message)
    {
        if (is_null($this->logger)) {
            return;
        }
        $this->logger->debug($message);
    }

    private function setXml()
    {
        // Enable user error handling while validating input
        libxml_clear_errors();
        libxml_use_internal_errors(true);

        $this->log("Load XML ...");
        $xml = null;

        if (!is_null($this->xmlFile)) {
            $xml = new DOMDocument();
            $xml->load($this->xmlFile);
            if (!$xml) {
                throw new Application_Import_MetadataImportInvalidXmlException('XML is not well-formed.');
            }
        } else {
            $xml = new DOMDocument();
            $xml->loadXML($this->xmlString);
            if (!$xml) {
                throw new Application_Import_MetadataImportInvalidXmlException('XML is not well-formed.');
            }
        }

        $this->log('Loading Result: OK');
        $this->xml = $xml;
    }

    private function validateXml()
    {
        $this->log("Validate XML ...");

        $validation = new Application_Import_XmlValidation($this->xml);
        if ($validation->validate($this->xml)) {
            $this->log('Validation Result: OK');
            return;
        }

        $this->log("... ERROR: Cannot load XML document: make sure it is well-formed." . $validation->getErrorsPrettyPrinted());
        throw new Application_Import_MetadataImportInvalidXmlException();
    }

    private function appendDocIdToRejectList($docId)
    {
        $this->log('... SKIPPED');
        if (is_null($this->logfile)) {
            return;
        }
        $this->logfile->log($docId);
    }

    /**
     * Allows certain fields to be kept on update.
     * @param array $fields DescriptionArray of fields to keep on update
     */
    public function keepFieldsOnUpdate($fields)
    {
        $this->fieldsToKeepOnUpdate = $fields;
    }

    /**
     *
     * @param Opus_Document $doc
     */
    private function resetDocument($doc)
    {
        $fieldsToDelete = array_diff([
            'TitleMain',
            'TitleAbstract',
            'TitleParent',
            'TitleSub',
            'TitleAdditional',
            'Identifier',
            'Note',
            'Enrichment',
            'Licence',
            'Person',
            'Series',
            'Collection',
            'Subject',
            'ThesisPublisher',
            'ThesisGrantor',
            'PublishedDate',
            'PublishedYear',
            'CompletedDate',
            'CompletedYear',
            'ThesisDateAccepted',
            'ThesisYearAccepted',
            'ContributingCorporation',
            'CreatingCorporation',
            'Edition',
            'Issue',
            'Language',
            'PageFirst',
            'PageLast',
            'PageNumber',
            'PublisherName',
            'PublisherPlace',
            'Type',
            'Volume',
            'BelongsToBibliography',
            'ServerState',
            'ServerDateCreated',
            'ServerDateModified',
            'ServerDatePublished',
            'ServerDateDeleted'],
            $this->fieldsToKeepOnUpdate);

        $doc->deleteFields($fieldsToDelete);
    }

    /**
     *
     * @param DOMNamedNodeMap $attributes
     * @param Opus_Document $doc
     */
    private function processAttributes($attributes, $doc)
    {
        foreach ($attributes as $attribute) {
            $method = 'set' . ucfirst($attribute->name);
            $value = trim($attribute->value);
            if ($attribute->name == 'belongsToBibliography') {
                if ($value == 'true') {
                    $value = '1';
                } else if ($value == 'false') {
                    $value = '0';
                }
            }
            $doc->$method($value);
        }
    }

    /**
     *
     * @param DOMNodeList $elements
     * @param Opus_Document $doc
     *
     * @return boolean returns true if the import XML definition of the
     *                 currently processed document contains the first level
     *                 element files
     */
    private function processElements($elements, $doc)
    {
        $filesElementPresent = false;

        foreach ($elements as $node) {
            if ($node instanceof DOMElement) {
                switch ($node->tagName) {
                    case 'titlesMain':
                        $this->handleTitleMain($node, $doc);
                        break;
                    case 'titles':
                        $this->handleTitles($node, $doc);
                        break;
                    case 'abstracts':
                        $this->handleAbstracts($node, $doc);
                        break;
                    case 'persons':
                        $this->handlePersons($node, $doc);
                        break;
                    case 'keywords':
                        $this->handleKeywords($node, $doc);
                        break;
                    case 'dnbInstitutions':
                        $this->handleDnbInstitutions($node, $doc);
                        break;
                    case 'identifiers':
                        $this->handleIdentifiers($node, $doc);
                        break;
                    case 'notes':
                        $this->handleNotes($node, $doc);
                        break;
                    case 'collections':
                        $this->handleCollections($node, $doc);
                        break;
                    case 'series':
                        $this->handleSeries($node, $doc);
                        break;
                    case 'enrichments':
                        $this->handleEnrichments($node, $doc);
                        break;
                    case 'licences':
                        $this->handleLicences($node, $doc);
                        break;
                    case 'dates':
                        $this->handleDates($node, $doc);
                        break;
                    case 'files':
                        $filesElementPresent = true;
                        if (!is_null($this->importDir)) {
                            $baseDir = trim($node->getAttribute('basedir'));
                            $this->handleFiles($node, $doc, $baseDir);
                        }
                        break;
                    default:
                        break;
                }
            }
        }
        return $filesElementPresent;
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleTitleMain($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $t = $doc->addTitleMain();
                $t->setValue(trim($childNode->textContent));
                $t->setLanguage(trim($childNode->getAttribute('language')));
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleTitles($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $method = 'addTitle' . ucfirst($childNode->getAttribute('type'));
                $t = $doc->$method();
                $t->setValue(trim($childNode->textContent));
                $t->setLanguage(trim($childNode->getAttribute('language')));
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleAbstracts($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $t = $doc->addTitleAbstract();
                $t->setValue(trim($childNode->textContent));
                $t->setLanguage(trim($childNode->getAttribute('language')));
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handlePersons($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $p = new Opus_Person();

                // mandatory fields
                $p->setFirstName(trim($childNode->getAttribute('firstName')));
                $p->setLastName(trim($childNode->getAttribute('lastName')));

                // optional fields
                $optionalFields = ['academicTitle', 'email', 'placeOfBirth', 'dateOfBirth'];
                foreach ($optionalFields as $optionalField) {
                    if ($childNode->hasAttribute($optionalField)) {
                        $method = 'set' . ucfirst($optionalField);
                        $p->$method(trim($childNode->getAttribute($optionalField)));
                    }
                }

                $method = 'addPerson' . ucfirst($childNode->getAttribute('role'));
                $link = $doc->$method($p);

                if ($childNode->hasAttribute('allowEmailContact') && ($childNode->getAttribute('allowEmailContact') === 'true' || $childNode->getAttribute('allowEmailContact') === '1')) {
                    $link->setAllowEmailContact(true);
                }

                // handling of person identifiers was introduced with OPUS 4.6
                // it is allowed to specify multiple identifiers (of different type) per person
                if ($childNode->hasChildNodes()) {
                    $identifiers = $childNode->childNodes;
                    foreach ($identifiers as $identifier) {
                        if ($identifier instanceof DOMElement && $identifier->tagName == 'identifiers') {
                            $this->handlePersonIdentifiers($identifier, $p);
                        }
                    }
                }
            }
        }
    }

    /**
     *
     * @param DOMNodeList $identifiers
     * @param Opus_Person $person
     */
    private function handlePersonIdentifiers($identifiers, $person)
    {
        $identifiers = $identifiers->childNodes;
        $idTypesFound = []; // print log message if an identifier type is used more than once
        foreach ($identifiers as $identifier) {
            if ($identifier instanceof DOMElement && $identifier->tagName == 'identifier') {
                $idType = $identifier->getAttribute('type');
                if ($idType == 'intern') {
                    $idType = 'misc';
                }
                if (array_key_exists($idType, $idTypesFound)) {
                    $this->log('could not save more than one identifier of type ' . $idType . ' for person ' . $person->getId());
                    continue; // ignore current identifier
                }
                $idValue = trim($identifier->textContent);
                $methodName = 'setIdentifier' . ucfirst($idType);
                $person->$methodName($idValue);
                $idTypesFound[$idType] = true; // do not allow further values for this identifier type
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleKeywords($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $s = new Opus_Subject();
                $s->setLanguage(trim($childNode->getAttribute('language')));
                $s->setType($childNode->getAttribute('type'));
                $s->setValue(trim($childNode->textContent));
                $doc->addSubject($s);
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleDnbInstitutions($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {

                $instId = trim($childNode->getAttribute('id'));
                $instRole = $childNode->getAttribute('role');
                // check if dnbInstitute with given id and role exists
                try {
                    $inst = new Opus_DnbInstitute($instId);

                    // check if dnbInstitute supports given role
                    $method = 'getIs' . ucfirst($instRole);
                    if ($inst->$method() === '1') {
                        $method = 'addThesis' . ucfirst($instRole);
                        $doc->$method($inst);
                    } else {
                        throw new Exception('given role ' . $instRole . ' is not allowed for dnbInstitution id ' . $instId);
                    }
                } catch (Opus_Model_NotFoundException $e) {
                    $msg = 'dnbInstitution id ' . $instId . ' does not exist: ' . $e->getMessage();
                    if ($this->swordContext) {
                        $this->log($msg);
                        continue;
                    }
                    throw new Exception($msg);
                }
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleIdentifiers($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $i = $doc->addIdentifier();
                $i->setValue(trim($childNode->textContent));
                $i->setType($childNode->getAttribute('type'));
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleNotes($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $n = $doc->addNote();
                $n->setMessage(trim($childNode->textContent));
                $n->setVisibility($childNode->getAttribute('visibility'));
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleCollections($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {

                $collectionId = trim($childNode->getAttribute('id'));
                // check if collection with given id exists
                try {
                    $c = new Opus_Collection($collectionId);
                    $doc->addCollection($c);
                } catch (Opus_Model_NotFoundException $e) {
                    $msg = 'collection id ' . $collectionId . ' does not exist: ' . $e->getMessage();
                    if ($this->swordContext) {
                        $this->log($msg);
                        continue;
                    }
                    throw new Exception($msg);
                }
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleSeries($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {

                $seriesId = trim($childNode->getAttribute('id'));
                // check if document set with given id exists
                try {
                    $s = new Opus_Series($seriesId);
                    $link = $doc->addSeries($s);
                    $link->setNumber(trim($childNode->getAttribute('number')));
                } catch (Opus_Model_NotFoundException $e) {
                    $msg = 'series id ' . $seriesId . ' does not exist: ' . $e->getMessage();
                    if ($this->swordContext) {
                        $this->log($msg);
                        continue;
                    }
                    throw new Exception($msg);
                }
            }
        }
    }

    /**
     * Processes the enrichments in the document xml.
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleEnrichments($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {

                $key = trim($childNode->getAttribute('key'));
                // check if enrichment key exists
                try {
                    new Opus_EnrichmentKey($key);
                } catch (Opus_Model_NotFoundException $e) {
                    $msg = 'enrichment key ' . $key . ' does not exist: ' . $e->getMessage();
                    if ($this->swordContext) {
                        $this->log($msg);
                        continue;
                    }
                    throw new Exception($msg);
                }

                $this->addEnrichment($doc, $key, $childNode->textContent);
            }
        }
    }

    /**
     * Adds an enrichment to the document.
     * @param $doc Opus_Document
     * @param $key Name of enrichment
     * @param $value Value of enrichment
     */
    private function addEnrichment($doc, $key, $value)
    {
        if ($value == null || strlen(trim($value)) == 0) {
            // enrichment must have a value
            // TODO log? how to identify the document before storing? improve import for easier monitoring
            return;
        }
        $enrichment = $doc->addEnrichment();
        $enrichment->setKeyName($key);
        $enrichment->setValue(trim($value));
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleLicences($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {

                $licenceId = trim($childNode->getAttribute('id'));
                try {
                    $l = new Opus_Licence($licenceId);
                    $doc->addLicence($l);
                } catch (Opus_Model_NotFoundException $e) {
                    $msg = 'licence id ' . $licenceId . ' does not exist: ' . $e->getMessage();
                    if ($this->swordContext) {
                        $this->log($msg);
                        continue;
                    }
                    throw new Exception($msg);
                }
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleDates($node, $doc)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $method = '';
                if ($childNode->hasAttribute('monthDay')) {
                    $method = 'Date';
                } else {
                    $method = 'Year';
                }

                if ($childNode->getAttribute('type') === 'thesisAccepted') {
                    $method = 'setThesis' . $method . 'Accepted';
                } else {
                    $method = 'set' . ucfirst($childNode->getAttribute('type')) . $method;
                }

                $date = trim($childNode->getAttribute('year'));
                if ($childNode->hasAttribute('monthDay')) {
                    // ignore first character of monthDay's attribute value (is always a hyphen)
                    $date .= substr(trim($childNode->getAttribute('monthDay')), 1);
                }

                $doc->$method($date);
            }
        }
    }

    /**
     * Handling of files was introduced with OPUS 4.6.
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     * @param string $baseDir
     */
    private function handleFiles($node, $doc, $baseDir)
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {

                $name = trim($childNode->getAttribute('name'));
                $path = trim($childNode->getAttribute('path'));
                if ($name == '' && $path == '') {
                    $this->log('At least one of the file attributes name or path must be defined!');
                    continue;
                }

                $this->addSingleFile($doc, $name, $baseDir, $path, $childNode);
            }
        }
    }

    /**
     *
     * Add a single file to the given Opus_Document.
     *
     * @param Opus_Document $doc the given document
     * @param type $name name of the file that should be imported (relative to baseDir)
     * @param string $baseDir (optional) path of the file that should be imported (relative to the import directory)
     * @param string $path (optional) path (and name) of the file that should be imported (relative to baseDir)
     * @param DOMNodeList $childNode (optional) additional metadata of the file (taken from import XML)
     */
    private function addSingleFile($doc, $name, $baseDir = '', $path = '', $childNode = null)
    {
        $fullPath = $this->importDir;
        if ($baseDir != '') {
            $fullPath .= $baseDir . DIRECTORY_SEPARATOR;
        }
        $fullPath .= ($path != '') ? $path : $name;

        if (!is_readable($fullPath)) {
            $this->log('Cannot read file ' . $fullPath . ': make sure that it is contained in import package');
            return;
        }

        if (!$this->validMimeType($fullPath)) {
            $this->log('MIME type of file ' . $fullPath . ' is not allowed for import');
            return;
        }

        if (!is_null($childNode) && !$this->checksumValidation($childNode, $fullPath)) {
            $this->log('Checksum validation of file ' . $fullPath . ' was not successful: check import package');
            return;
        }

        $file = new Opus_File();
        if (!is_null($childNode)) {
            $this->handleFileAttributes($childNode, $file);
        }
        if (is_null($file->getLanguage())) {
            $file->setLanguage($doc->getLanguage());
        }

        $file->setTempFile($fullPath);
        // allow to overwrite file name (if attribute name was specified)
        $pathName = $name;
        if ($pathName == '') {
            $pathName = $fullPath;
        }
        $file->setPathName(basename($pathName));

        if (!is_null($childNode)) {
            $comments = $childNode->getElementsByTagName('comment');
            if ($comments->length == 1) {
                $comment = $comments->item(0);
                $file->setComment(trim($comment->textContent));
            }
        }

        $doc->addFile($file);
    }

    /**
     * Prüft, ob die übergebene Datei überhaupt importiert werden darf.
     * Dazu gibt es in der Konfiguration die Schlüssel filetypes.mimetypes.*
     *
     * @param type $fullPath
     *
     * TODO move check to file types helper?
     */
    private function validMimeType($fullPath)
    {
        $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeTypeFound = $finfo->file($fullPath);

        $fileTypes = Zend_Controller_Action_HelperBroker::getStaticHelper('fileTypes');

        return $fileTypes->isValidMimeType($mimeTypeFound, $extension);
    }

    /**
     * Prüft, ob die im Element checksum angegebene Prüfsumme mit der Prüfsumme
     * der zu importierenden Datei übereinstimmt. Liefert das Ergebnis des
     * Vergleichs zurück.
     *
     * Wurde im Import-XML keine Prüfsumme für die Datei angegeben, so liefert
     * die Methode ebenfalls true zurück.
     *
     * @param DOMElement $childNode
     * @param string $fullPath
     */
    private function checksumValidation($childNode, $fullPath)
    {
        $checksums = $childNode->getElementsByTagName('checksum');
        if ($checksums->length == 0) {
            return true;
        }

        $checksumElement = $checksums->item(0);
        $checksumVal = trim($checksumElement->textContent);
        $checksumAlgo = $checksumElement->getAttribute('type');
        $hashValue = hash_file($checksumAlgo, $fullPath);
        return strcasecmp($checksumVal, $hashValue) == 0;
    }

    /**
     *
     * @param DOMElement $node
     * @param Opus_File $file
     */
    private function handleFileAttributes($node, $file)
    {
        $attrsToConsider = [
            'language',
            'displayName',
            'visibleInOai',
            'visibleInFrontdoor',
            'sortOrder'
        ];
        foreach ($attrsToConsider as $attribute) {
            $value = trim($node->getAttribute($attribute));
            if ($value != '') {
                switch ($attribute) {
                    case 'displayName':
                        $attribute = 'label';
                        break;
                    case 'visibleInFrontdoor':
                        $value = ($value == 'true')? true : false;
                        break;
                    case 'visibleInOai':
                        $value = ($value == 'true')? true : false;
                        break;
                    case 'sortOrder':
                        $value = intval($value);
                        break;
                }
                $methodName = 'set' . ucfirst($attribute);
                $file->$methodName($value);
            }
        }
    }

    /**
     * Add all files in the root level of the import package to the given
     * document.
     *
     * @param Opus_Document $doc document
     */
    private function importFilesDirectly($doc)
    {
        $files = array_diff(scandir($this->importDir), ['..', '.', 'opus.xml']);
        foreach ($files as $file) {
            $this->addSingleFile($doc, $file);
        }
    }

    /**
     * Returns the imported document.
     * @return Opus_Document
     */
    public function getDocument()
    {
        return $this->document;
    }
}
