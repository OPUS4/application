#!/usr/bin/env php5
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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
require_once dirname(__FILE__) . '/../common/bootstrap.php';
require_once 'Log.php';

class MetadataImporter {

    private $console;

    private $logfile;

    public function run($options) {
        $consoleConf = array('lineFormat' => '[%1$s] %4$s');
        $logfileConf = array('append' => false, 'lineFormat' => '%4$s');

        
        $this->console = Log::factory('console', '', '', $consoleConf, PEAR_LOG_INFO);

        if (count($options) < 2) {
            $this->console->log('Missing parameter: no file to import.');
        }

        $logfilePath = 'reject.log';
        if (count($options) > 2) { 
            // logfile path is given
            $logfilePath = $options[2];
        }
        $this->logfile = Log::factory('file', $logfilePath, '', $logfileConf, PEAR_LOG_INFO);

        $xml = $this->loadAndValidateInputFile($options[1]);

        $numOfDocsImported = 0;
        $numOfSkippedDocs = 0;
        
        foreach ($xml->getElementsByTagName('opusDocument') as $opusDocumentElement) {

            // save oldId for later referencing of the record under consideration
            $oldId = $opusDocumentElement->getAttribute('oldId');
            $opusDocumentElement->removeAttribute('oldId');

            $this->console->log("Start processing of record #" . $oldId . " ...");

            /*
             * @var Opus_Document
             */
            $doc = null;
            if ($opusDocumentElement->hasAttribute('docId')) {                
                // perform metadata update on given document
                $docId = $opusDocumentElement->getAttribute('docId');
                try {                    
                    $doc = new Opus_Document($docId);
                    $opusDocumentElement->removeAttribute('docId');
                }
                catch (Opus_Model_NotFoundException $e) {
                    $this->console->log('Could not load document #' . $docId . ' from database: ' . $e->getMessage());
                    $this->appendDocIdToRejectList($oldId);
                    $numOfSkippedDocs++;
                    continue;
                }

                $this->resetDocument($doc);
            }
            else {
                // create new document
                $doc = new Opus_Document();
            }


            try {
                $this->processAttributes($opusDocumentElement->attributes, $doc);
                $this->processElements($opusDocumentElement->childNodes, $doc);
            }
            catch (Exception $e) {
                $this->console->log('Error while processing document #' . $oldId . ': ' . $e->getMessage());
                $this->appendDocIdToRejectList($oldId);
                $numOfSkippedDocs++;
                continue;
            }
            

            try {
                $doc->store();                
            }
            catch (Exception $e) {
                $this->console->log('Error while saving imported document #' . $oldId . ' to database: ' . $e->getMessage());
                $this->appendDocIdToRejectList($oldId);
                $numOfSkippedDocs++;
                continue;
            }

            $numOfDocsImported++;
            $this->console->log('... OK');
        }

        if ($numOfSkippedDocs == 0) {
            $this->console->log("Import finished successfully. $numOfDocsImported documents were imported.");
        }
        else {
            $this->console->log("Import finished. $numOfDocsImported documents were imported. $numOfSkippedDocs documents were skipped.");
        }
    }

    private function appendDocIdToRejectList($docId) {
        $this->console->log('... SKIPPED');
        $this->logfile->log($docId);
    }

    /**
     *
     * @param Opus_Document $doc
     */
    private function resetDocument($doc) {
                $fieldsToDelete = array(
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
                    'ServerDateDeleted'
                    );

                $doc->deleteFields($fieldsToDelete);
    }

    private function getErrorMessage() {
        $errorMsg = '';
        foreach (libxml_get_errors() as $error) {
            $errorMsg .= "\non line $error->line ";
            switch ($error->level) {
                case LIBXML_ERR_WARNING:
                    $errorMsg .= "(Warning $error->code): ";
                    break;
                case LIBXML_ERR_ERROR:
                    $errorMsg .= "(Error $error->code): ";
                    break;
                case LIBXML_ERR_FATAL:
                    $errorMsg .= "(Fatal Error $error->code): ";
                    break;
            }
            $errorMsg .= trim($error->message);
        }
        libxml_clear_errors();
        return $errorMsg;
    }

    /**
     * Load and validate XML document
     *
     * @param string $filename
     * @return DOMDocument
     */
    private function loadAndValidateInputFile($filename) {
        $this->console->log("Loading XML file '$filename' ...");
        
        if (!is_readable($filename)) {
            $this->console->log("XML file $filename does not exist or is not readable.");
            exit();
        }

        $xml = new DOMDocument();
        if (true !== $xml->load($filename)) {
            $this->console->log("... ERROR: Cannot load XML document $filename: make sure it is well-formed.");
            exit();
        }
        $this->console->log('... OK');

        // Enable user error handling while validating input file
        libxml_use_internal_errors(true);

        $this->console->log("Validate XML file '$filename' ...");
        if (!$xml->schemaValidate(__DIR__ . DIRECTORY_SEPARATOR . 'opus_import.xsd')) {
            $this->console->log("... ERROR: XML document $filename is not valid: " . $this->getErrorMessage());
            exit();
        }
        $this->console->log('... OK');

        return $xml;
    }

    /**
     *
     * @param DOMNamedNodeMap $attributes
     * @param Opus_Document $doc
     */
    private function processAttributes($attributes, $doc) {        
        foreach ($attributes as $attribute) {
            $method = 'set' . ucfirst($attribute->name);
            $doc->$method(trim($attribute->value));
        }
    }

    /**
     *
     * @param DOMNodeList $elements
     * @param Opus_Document $doc
     */
    private function processElements($elements, $doc) {
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
                    default:
                        break;
                }
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleTitleMain($node, $doc) {
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
    private function handleTitles($node, $doc) {
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
    private function handleAbstracts($node, $doc) {
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
    private function handlePersons($node, $doc) {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $p = new Opus_Person();

                // mandatory fields
                $p->setFirstName(trim($childNode->getAttribute('firstName')));
                $p->setLastName(trim($childNode->getAttribute('lastName')));

                // optional fields
                $optionalFields = array('academicTitle', 'email', 'placeOfBirth', 'dateOfBirth');
                foreach ($optionalFields as $optionalField) {
                    if ($childNode->hasAttribute($optionalField)) {
                        $method = 'set' . ucfirst($optionalField);
                        $p->$method(trim($childNode->getAttribute($optionalField)));
                    }
                }

                $method = 'addPerson' . ucfirst($childNode->getAttribute('role'));
                $link = $doc->$method($p);

                if ($childNode->hasAttribute('allowEmailContact') && $childNode->getAttribute('allowEmailContact') === 'true') {
                    $link->setAllowEmailContact(true);
                }
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleKeywords($node, $doc) {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $s = new Opus_Subject();
                $s->setLanguage(trim($childNode->getAttribute('language')));
                $s->setValue(trim($childNode->textContent));

                $method = 'addSubject' . ucfirst($childNode->getAttribute('type'));
                $doc->$method($s);
            }
        }        
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleDnbInstitutions($node, $doc) {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {

                $instId = trim($childNode->getAttribute('id'));
                $instRole = $childNode->getAttribute('role');
                // check if dnbInstitute with given id and role exists
                try {
                    $i = new Opus_DnbInstitute($instId);

                    // check if dnbInstitute supports given role
                    $method = 'getIs' . ucfirst($instRole);
                    if ($i->$method === '1') {
                        $method = 'addThesis' . ucfirst($instRole);
                        $doc->$method($i);
                    }
                    else {
                        throw new Exception('given role ' . $instRole . ' is not allowed for dnbInstitution id ' . $instId);
                    }
                }
                catch (Opus_Model_NotFoundException $e) {
                    throw new Exception('dnbInstitution id ' . $instId . ' does not exist: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleIdentifiers($node, $doc) {
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
    private function handleNotes($node, $doc) {
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
    private function handleCollections($node, $doc) {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {

                $collectionId = trim($childNode->getAttribute('id'));
                // check if collection with given id exists
                try {
                    $c = new Opus_Collection($collectionId);
                    $doc->addCollection($c);
                }
                catch (Opus_Model_NotFoundException $e) {
                    throw new Exception('collection id ' . $collectionId . ' does not exist: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleSeries($node, $doc) {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {

                $seriesId = trim($childNode->getAttribute('id'));
                // check if document set with given id exists
                try {
                    $s = new Opus_DocumentSets($seriesId);
                    $link = $doc->addDocumentSets($s);
                    $link->setNumber(trim($childNode->getAttribute('number')));
                }
                catch (Opus_Model_NotFoundException $e) {
                    throw new Exception('series id ' . $seriesId . ' does not exist: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleEnrichments($node, $doc) {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                
                $key = trim($childNode->getAttribute('key'));
                // check if enrichment key exists
                try {
                    new Opus_EnrichmentKey($key);
                }
                catch (Opus_Model_NotFoundException $e) {
                    throw new Exception('enrichment key ' . $key . ' does not exist: ' . $e->getMessage());
                }

                $e = $doc->addEnrichment();
                $e->setKeyName($key);
                $e->setValue(trim($childNode->textContent));                
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleLicences($node, $doc) {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                
                $licenceId = trim($childNode->getAttribute('id'));
                try {
                    $l = new Opus_Licence($licenceId);
                    $doc->addLicence($l);
                }
                catch (Opus_Model_NotFoundException $e) {
                    throw new Exception('licence id ' . $licenceId . ' does not exist: ' . $e->getMessage());
                }
            }
        }
    }

    /**
     *
     * @param DOMNode $node
     * @param Opus_Document $doc
     */
    private function handleDates($node, $doc) {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof DOMElement) {
                $method = '';
                if ($childNode->hasAttribute('monthDay')) {
                    $method = 'Date';
                }
                else {
                    $method = 'Year';
                }

                if ($childNode->getAttribute('type') === 'thesisAccepted') {
                    $method = 'setThesis' . $method . 'Accepted';
                }
                else {
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

}

try {
    $importer = new MetadataImporter();
    $importer->run($argv);
}
catch (Exception $e) {
    echo "\nAn error occurred while importing: " . $e->getMessage() . "\n\n";
    exit();
}
