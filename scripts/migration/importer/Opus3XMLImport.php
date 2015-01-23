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
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Opus3XMLImport {
    
   /**
    * Holds Zend-Configurationfile
    *
    * @var file.
    */
    protected $_config = null;

   /**
    * Holds Logger
    *
    * @var file
    */
    protected $_logger = null;

    /**
     * Holds xml representation of document information to be processed.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xml = null;
    /**
     * Holds the stylesheet for the transformation.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_xslt = null;
    /**
     * Holds the xslt processor.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_proc = null;
    /**
     * Holds the document that should get imported
     *
     * @var DomNode  XML-Representation of the document to import
     */
    protected $_document = null;
     /**
     * Holds the Mappings forCollections
     *
     * @var Array
     */
    protected $_mappings = array();

    /**
     * Holds the Collections the document should be added
     *
     * @var Array
     */
    protected $_collections = array();

    /**
     * Holds the Series the document should be added
     *
     * @var Array
     */
    protected $_series = array();

    /**
     * Holds Values for Grantor, Licence and PublisherUniversity
     *
     * @var Array
     */
    protected $_values = array();

    /**
     * Holds SortOrder of Authors
     *
     * @var Array
     */
    protected $_personSortOrder = array();

    /**
     * Holds Doctypes for Thesis
     *
     * @var Array
     */
    protected $_thesistypes = array();

    /**
     * Holds the old identifier of the document
     *
     * @var string
     */
    protected $_oldId = null;


    /**
     * Holds the complete XML-Representation of the Importfile
     *
     * @var DomDocument  XML-Representation of the importfile
     */
    protected $_completeXML = null;


    /**
     * Do some initialization on startup of every action
     *
     * @param string $xslt Filename of the stylesheet to be used
     * @param string $stylesheetPath Path to the stylesheet
     * @return void
     */
    public function __construct($xslt, $stylesheetPath) {
        // Initialize member variables.
        $this->_config = Zend_Registry::get('Zend_Config');
        $this->_logger = Zend_Registry::get('Zend_Log');

        $this->_xml = new DomDocument;
        $this->_xslt = new DomDocument;
        $this->_xslt->load($stylesheetPath . '/' . $xslt);
        $this->_proc = new XSLTProcessor;
        $this->_proc->registerPhpFunctions();
        $this->_proc->importStyleSheet($this->_xslt);

        $this->mapping['language'] =  array(
            'old' => 'OldLanguage', 'new' => 'Language', 'config' => $this->_config->migration->language
        );
        $this->mapping['type'] =  array(
            'old' => 'OldType', 'new' => 'Type', 'config' => $this->_config->migration->doctype
        );

        $this->mapping['collection'] = array(
            'name' => 'OldCollection', 'mapping' => $this->_config->migration->mapping->collections
        );
        $this->mapping['institute'] = array(
            'name' => 'OldInstitute',  'mapping' => $this->_config->migration->mapping->institutes
        );
        $this->mapping['series'] = array(
            'name' => 'OldSeries',  'mapping' => $this->_config->migration->mapping->series
        );
        $this->mapping['grantor'] = array(
            'name' => 'OldGrantor', 'mapping' => $this->_config->migration->mapping->grantors
        );
        $this->mapping['licence'] = array(
            'name' => 'OldLicence',  'mapping' => $this->_config->migration->mapping->licences
        );
        $this->mapping['publisherUniversity'] = array(
            'name' => 'OldPublisherUniversity', 'mapping' => $this->_config->migration->mapping->universities
        );
        $this->mapping['role'] = array(
            'name' => 'OldRole', 'mapping' => $this->_config->migration->mapping->roles
        );

        array_push($this->_thesistypes, 'bachelorthesis');
        array_push($this->_thesistypes, 'doctoralthesis');
        array_push($this->_thesistypes, 'habilitation');
        array_push($this->_thesistypes, 'masterthesis');
        array_push($this->_thesistypes, 'studythesis');
    }

    public function initImportFile($data) {
        $this->_completeXML = new DOMDocument;
        if (isset($this->_config->migration->bem_extern)) {
            $this->_proc->setParameter('', 'bem_extern', $this->_config->migration->bem_extern);
        }
        if (isset($this->_config->migration->subjects)) {
            $this->_proc->setParameter('', 'subjects', $this->_config->migration->subjects);
        }
        $this->_completeXML->loadXML($this->_proc->transformToXml($data));
        $doclist = $this->_completeXML->getElementsByTagName('Opus_Document');
        return $doclist;
    }

    /**
     * Imports metadata from an XML-Document
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array information about the document that has been imported
     */
    public function import($document) {
        $this->_collections = array();
        $this->_values = array();
        $this->_series = array();
        $this->_personSortOrder = array();
        $this->_document = $document;
        $this->_oldId = $this->_document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value');

        $this->skipEmptyFields();
    $this->validatePersonSubmitterEmail();
        $this->checkTitleMainAbstractForDuplicateLanguage();
        $this->checkTitleAdditional();

        $this->mapDocumentTypeAndLanguage();
        $this->mapElementLanguage();
        $this->mapClassifications();
        $this->mapCollections();
        $this->mapValues();

        $this->getSortOrder();
        $this->createNewEnrichmentKeys();

        $imported = array();
        $doc = null;

        try {
            $doc = Opus_Document::fromXml('<Opus>' . $this->_completeXML->saveXML($this->_document) . '</Opus>');

            // ThesisGrantor and ThesisPublisher only for Thesis-Documents
            if (in_array($doc->getType(), $this->_thesistypes)) {
                if (array_key_exists('grantor', $this->_values)) {
                    $dnbGrantor = new Opus_DnbInstitute($this->_values['grantor']);
                    $doc->setThesisGrantor($dnbGrantor);
                }
                if (array_key_exists('publisherUniversity', $this->_values)) {
                    $dnbPublisher = new Opus_DnbInstitute($this->_values['publisherUniversity']);
                    $doc->setThesisPublisher($dnbPublisher);
                }
            }

            if (array_key_exists('licence', $this->_values)) {
                $doc->addLicence(new Opus_Licence($this->_values['licence']));
            }

            // TODO Opus4.x : Handle SortOrder via Opus_Document_Model
             foreach ($doc->getPersonAuthor() as $a) {
                $lastname = $a->getLastName();
                $firstname = $a->getFirstName();
                $sortorder = $this->_personSortOrder[$lastname.",".$firstname];
                $a->setSortOrder($sortorder);
             }



            foreach ($this->_collections as $c) {
                $coll = new Opus_Collection($c);
                $coll->setVisible(1);
                $coll->store();
                $doc->addCollection($coll);
            }

            foreach ($this->_series as $s) {
                $series = new Opus_Series($s[0]);
                $doc->addSeries($series)->setNumber($s[1]);
            }

            //$this->logger->log("(3):".$this->completeXML->saveXML($this->document), Zend_Log::DEBUG);
            $doc->store();

            $imported['result'] = 'success';
            $imported['oldid'] = $this->_oldId;
            $imported['newid'] = $doc->getId();
       
            if (array_key_exists('role', $this->_values)) {
                $imported['roleid'] = $this->_values['role'];
                //$this->logger->log("ROLE_ID'" . $this->values['roleid'] . "', Zend_Log::DEBUG);
            }
        } catch (Exception $e) {
            $imported['result'] = 'failure';
            $imported['message'] = $e->getMessage();
            if (!is_null($doc)) {
                $imported['entry'] = $doc->toXml()->saveXML();
            }
            else {
                $imported['entry'] = $this->_completeXML->saveXML($this->_document);
            }
            $imported['oldid'] = $this->_oldId;
        }

        unset($this->_collections);
        unset($this->_series);
        unset($this->_values);
        unset($this->_document);
        unset($this->_oldId);

        return $imported;
    }

    private function createNewEnrichmentKeys() {

        $elements = $this->_document->getElementsByTagName('Enrichment');
        foreach ($elements as $e) {
            $keyname = $e->getAttribute('KeyName');
            if (is_null(Opus_EnrichmentKey::fetchByName($keyname))) {
                $enrichmentkey = new Opus_EnrichmentKey();
                $enrichmentkey->setName($keyname);
                $enrichmentkey->store();
            }
        }
    }

    private function skipEmptyFields() {

        $roles = array();

        array_push($roles, array('TitleMain', 'Value'));
        array_push($roles, array('TitleAbstract', 'Value'));
        array_push($roles, array('TitleParent', 'Value'));
        array_push($roles, array('TitleSub', 'Value'));
        array_push($roles, array('TitleAdditional', 'Value'));

        array_push($roles, array('Subject', 'Value'));
        array_push($roles, array('IdentifierIsbn', 'Value'));
        array_push($roles, array('Enrichment', 'Value'));

        array_push($roles, array('Note', 'Message'));
 
        foreach ($roles as $r) {
            $elements = $this->_document->getElementsByTagName($r[0]);
            foreach ($elements as $e) {
                if (trim($e->getAttribute($r[1])) == "") {
                    $this->_logger->log(
                        "Old ID '" . $this->_oldId . "' : '" . $r[0] . "' with empty '" .
                        $r[1] . "' will not be imported", Zend_Log::ERR
                    );
                    $this->_document->removeChild($e);
                }
            }
        }
    }


    private function checkTitleMainAbstractForDuplicateLanguage() {
        $tagnames = array('TitleMain', 'TitleAbstract');
        $oa = $this->mapping['language'];
        foreach ($tagnames as $tag) {
            $language = array();
            $elements = $this->_document->getElementsByTagName($tag);
            foreach ($elements as $e) {
                $oldValue = $e->getAttribute($oa['old']);
                if ($oa['config']->$oldValue) {
                    $newValue = $oa['config']->$oldValue;
                }
                else {
                    $this->_logger->log(
                        "Old ID '" .
                        $this->_oldId . "' : No Mapping for 'language' in '" . $tag . "' with value '" . $oldValue .
                        "' found. Set to default-Value '" .  $oa['config']->default . "'", Zend_Log::ERR
                    );
                    $newValue = $oa['config']->default;
                }
                /* Check for TitleElements with duplicated Languages */
                if (in_array($newValue, $language)) {
                    $this->_logger->log(
                        "Old ID '" . $this->_oldId . "' : This document has two '" .
                        $tag . "' with equal language. Document will not be indexed", Zend_Log::ERR
                    );
                }
                else {
                    array_push($language, $newValue);
                }
            }
        }
    }

    private function checkTitleAdditional() {
        $elements = $this->_document->getElementsByTagName('TitleAdditional');
        foreach ($elements as $e) {
            $this->_logger->log(
                "Old ID '" . $this->_oldId . "' : 'title_en' or 'title_de' mapped to ".
                "'TitleAdditional' to prevent 'TitleMain' with duplicate language", Zend_Log::WARN
            );
        }
    }

     private function validatePersonSubmitterEmail() {

        $roles = array();
        $validator = new Zend_Validate_EmailAddress();
        $elements = $this->_document->getElementsByTagName('PersonSubmitter');
        foreach ($elements as $e) {
            if (trim($e->getAttribute('Email')) != "") {
                if (!($validator->isValid($e->getAttribute('Email')))) {
                    $this->_logger->log(
                        "Old ID '" . $this->_oldId . "' : Invalid Email-Address '"
                        . $e->getAttribute('Email') . "' will be imported as 'InvalidVerification'-Enrichment",
                        Zend_Log::ERR
                    );
                    $enrichment =  $this->_document->appendChild(new DOMElement('Enrichment'));
                    $enrichment->setAttributeNode(new DOMAttr('KeyName', 'InvalidVerification'));
                    $enrichment->setAttributeNode(new DOMAttr('Value', $e->getAttribute('Email')));
                    $e->removeAttribute('Email');
                }
            }
        }
     }

    
    private function mapDocumentTypeAndLanguage() {
        $mapping = array('language', 'type');
        foreach ($mapping as $m) {
            $oa = $this->mapping[$m];
            $oldValue = $this->_document->getAttribute($oa['old']);
            if ($oa['config']->$oldValue) {
                $newValue = $oa['config']->$oldValue;
            }
            else {
                $this->_logger->log(
                    "Old ID '" . $this->_oldId . "' : No Mapping for '" . $m .
                    "' in Document with value '" . $oldValue . "' found. Set to default-Value '" .
                    $oa['config']->default . "'", Zend_Log::ERR
                );
                $newValue = $oa['config']->default;
            }
            $this->_document->removeAttribute($oa['old']);
            $this->_document->setAttribute($oa['new'], $newValue);
        }
    }

    private function mapElementLanguage() {
        $tagnames = array('TitleMain', 'TitleAbstract', 'TitleAdditional', 'Subject');
        $oa = $this->mapping['language'];
        foreach ($tagnames as $tag) {
            $elements = $this->_document->getElementsByTagName($tag);
            foreach ($elements as $e) {
                $oldValue = $e->getAttribute($oa['old']);
                if ($oa['config']->$oldValue) {
                    $newValue = $oa['config']->$oldValue;
                }
                else {
                    // TODO bug $m not defined
                    $this->_logger->log(
                        "Old ID '" . $this->_oldId . "' : No Mapping for '" . $m .
                        "' in '" . $tag . "' with value '" . $oldValue . "' found. Set to default-Value '" .
                        $oa['config']->default . "'", Zend_Log::ERR
                    );
                    $newValue = $oa['config']->default;
                }
                $e->removeAttribute($oa['old']);
                $e->setAttribute($oa['new'], $newValue);
            }
        }
    }

    private function mapClassifications() {
        $oldBkl = array('name' => 'OldBkl', 'role' => 'bkl');
        $oldCcs = array('name' => 'OldCcs', 'role' => 'ccs');
        $oldDdc = array('name' => 'OldDdc', 'role' => 'ddc');
        $oldJel = array('name' => 'OldJel', 'role' => 'jel');
        $oldMsc = array('name' => 'OldMsc', 'role' => 'msc');
        $oldPacs = array('name' => 'OldPacs', 'role' => 'pacs');
        $oldArray = array($oldBkl, $oldCcs, $oldDdc, $oldJel, $oldMsc, $oldPacs);

        foreach ($oldArray as $oa) {
            $elements = $this->_document->getElementsByTagName($oa['name']);

            while ($elements->length > 0) {
                $e = $elements->Item(0);
                $value = $e->getAttribute('Value');
                $role = Opus_CollectionRole::fetchByName($oa['role']);
                $colls = Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $value);

                if (count($colls) > 0) {
                    foreach ($colls as $c) {
                        /* TODO: DDC-Hack */
                        if (($oa['role'] === 'ddc') and ($c->hasChildren())) {
                            continue;
                        }
                        array_push($this->_collections, $c->getId());
                    }
                }
                else {
                    $this->_logger->log(
                        "Old ID '" . $this->_oldId . "' : Document not added to '" .
                        $oa['role'] . "' '" . $value . "'", Zend_Log::ERR
                    );
                }
                $this->_document->removeChild($e);
            }
        }
    }

    private function mapCollections() {
        $mapping = array('collection', 'institute', 'series');

        foreach ($mapping as $m) {
            $oa = $this->mapping[$m];
            $elements = $this->_document->getElementsByTagName($oa['name']);
            while ($elements->length > 0) {
                $e = $elements->Item(0);
                $oldValue = $e->getAttribute('Value');

                if (!is_null($this->getMapping($oa['mapping'], $oldValue))) {
                    $newValue = $this->getMapping($oa['mapping'], $oldValue);

                    if ($m === 'series') {

                        $oldIssue = $e->getAttribute('Issue');
                        $newSeries = array($newValue, $oldIssue);
                        // $this->logger->log("Found Mapping in ".$oa['mapping'].": '".$old_value."' --> '".$new_value
                        // ."' with Issue '".$old_issue."'", Zend_Log::DEBUG);
                        array_push($this->_series, $newSeries);

                    }
                    else {
                        array_push($this->_collections, $newValue);
                    }

                }
                else {
                    $this->_logger->log(
                        "Old ID '" . $this->_oldId . "' : ('$m'): No valid Mapping in '"
                        . $oa['mapping'] . "' for '" . $oldValue . "'", Zend_Log::ERR
                    );
                }

                $this->_document->removeChild($e);
            }
        }
    }

     private function mapValues() {
        $mapping = array('grantor', 'licence', 'publisherUniversity', 'role');
        foreach ($mapping as $m) {
            $oa = $this->mapping[$m];
            //$this->logger->log("($m): Mapping  '" . $oa['mapping'] . "' for '" . $oa['name'] . "'", Zend_Log::DEBUG);
            $elements = $this->_document->getElementsByTagName($oa['name']);
            while ($elements->length > 0) {
                $e = $elements->Item(0);
                $oldValue = $e->getAttribute('Value');


                if ($m === 'publisherUniversity') {
                    $oldValue = str_replace(" ", "_", $oldValue);
                }

                if (!is_null($this->getMapping($oa['mapping'], $oldValue))) {
                    $newValue = $this->getMapping($oa['mapping'], $oldValue);
                    $this->_values[$m] = $newValue;
                    // $this->logger->log("Found Mapping in " . $oa['mapping'] . ": '" .$old_value . "' --> '"
                    // . $new_value . "'", Zend_Log::DEBUG);
                }
                else {
                    $this->_logger->log(
                        "Old ID '" . $this->_oldId . "' : ('$m'): No valid Mapping in '"
                        . $oa['mapping'] . "' for '" . $oldValue . "'", Zend_Log::ERR
                    );
                }

                $this->_document->removeChild($e);
            }
        }
     }

    private function getSortorder() {

        $roles = array();
        array_push($roles, 'PersonAuthor');

        foreach ($roles as $r) {
            $elements = $this->_document->getElementsByTagName($r);
            foreach ($elements as $e) {
                $firstname = $e->getAttribute('FirstName');
                $lastname = $e->getAttribute('LastName');
                $sortorder = $e->getAttribute('SortOrder');
                $this->_personSortOrder[$lastname.",".$firstname] = $sortorder;
                $e->removeAttribute('SortOrder');
            }
        }
    }


    /**
     * Get mapped Values for a document and add it
     *
     * @mappingFile: name of the Mapping-File
     * @id: original id
     * @return new id
     */
     private function getMapping($mappingFile, $id) {
        /* TODO: CHECK if File exists , echo ERROR and return null if not*/
        if (!is_readable($mappingFile)) {
            $this->_logger->log("MappingFile '" . $mappingFile . "' is not readable", Zend_Log::ERR);
            return null;
        }
        $fp = file($mappingFile);
        $mapping = array();
        foreach ($fp as $line) {
            $values = explode(" ", $line);
            $mapping[$values[0]] = trim($values[1]);
        }
        if (array_key_exists($id, $mapping) === false) {
            unset($fp);
            return null;
        }
        unset($fp);
        return $mapping[$id];
     }

}
