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
 * @package     Module_Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Opus3XMLImport.php -1   $
 */
class ZIBXMLImport {

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
    protected $document = null;
    /**
     * Holds the complete XML-Representation of the Importfile
     *
     * @var DomDocument  XML-Representation of the importfile
     */
    protected $completeXML = null;
    /**
     * Holds the collections predifined in OPUS
     *
     * @var array  Defaults to the OPUS collections (Name => ID).
     */
    protected $collections = array();
    /**
     * Holds the logfile for Importer
     *
     * @var string  Path to logfile
     */
    protected $logfile = '../workspace/log/import.log';
    /**
     * Holds the filehandle of the logfile
     *
     * @var file  Fileandle logfile
     */
    protected $_logfile;

    /**
     * Do some initialization on startup of every action
     *
     * @param string $xslt Filename of the stylesheet to be used
     * @param string $stylesheetPath Path to the stylesheet
     * @return void
     */
    public function __construct($xslt, $stylesheetPath) {
        // Initialize member variables.
        $this->_xml = new DomDocument;
        $this->_xslt = new DomDocument;
        $this->_xslt->load($stylesheetPath . '/' . $xslt);
        $this->_proc = new XSLTProcessor;
        $this->_proc->registerPhpFunctions();
        $this->_proc->importStyleSheet($this->_xslt);
        $this->_logfile = fopen($this->logfile, 'w');
    }

    public function log($string) {
        fputs($this->_logfile, $string);
    }

    public function finalize() {
        fclose($this->_logfile);
    }

    public function initImportFile($data) {
        $this->completeXML = new DOMDocument;
        $this->completeXML->loadXML($this->_proc->transformToXml($data));
        $doclist = $this->completeXML->getElementsByTagName('Opus_Document');
        return $doclist;
    }

    /**
     * Imports metadata from an XML-Document
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array information about the document that has been imported
     */
    public function import($document) {
        // Use the document as attribute
        $this->document = $document;

        //echo "BEFORE:".$this->completeXML->saveXML($this->document)."\n";

        $doc = null;

        $oldid = null;
        $oldid = $document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value');

        $ddcNotation = null;
        $ddcNotation = $document->getElementsByTagName('OldDdc')->Item(0);
        $ddcValue = null;

        $oldclasses = array();
        $oldclasses = $document->getElementsByTagName('OldClasses');
        $newclasses = array();

        $institutes = null;
        $institutes = $document->getElementsByTagName('OldInstitute');
        $instituteValues = array();

        $series = null;
        $series = $document->getElementsByTagName('OldSeries');
        $newseries = array();

        $collections = array();
        $collections = $document->getElementsByTagName('OldCollections');
        $collectionValues = array();

        $licence = null;
        $licence = $document->getElementsByTagName('OldLicence');
        $licenceValue = null;

        /*  ThesisPublisher */
        $publisher = null;
        $publisher = $document->getElementsByTagName('OldPublisherUniversity');
        $publisherValue = null;
        
	/* ThesisGrantor */
        $faculty = null;
        $faculty = $document->getElementsByTagName('OldGrantor');
        $grantorValue = null;

        $imported = array();

        if ($ddcNotation !== null) {
            $ddcValue = $ddcNotation->getAttribute('Value');
            $this->document->removeChild($ddcNotation);
        }

        if (count($oldclasses) > 0) { // Array: (Key=>...,Value=>...)

            while ($oldclasses->length >0) {
                $oc=$oldclasses->Item(0);
                if (($oc->getAttribute('Key') != "") && ($oc->getAttribute('Value')) != "") {
                    $nc = array('Key'=>strtolower($oc->getAttribute('Key')), 'Value'=>$oc->getAttribute('Value'));
                    array_push($newclasses, $nc);
                }
                $this->document->removeChild($oc);
            }
        }

        if (count($series) > 0) {
           foreach ($series as $s) {
               $mappingFile = '../workspace/tmp/series.map';
               //echo "Found a series\n";
               if (!is_null($this->getMapping($mappingFile, $s->getAttribute('Value')))) {
                    $ns = array('Key'=>$this->getMapping($mappingFile, $s->getAttribute('Value')), 'Value'=>$s->getAttribute('Issue'));
                    array_push($newseries, $ns);
               }
               $this->document->removeChild($s);
           }
        }

        if (count($collections) > 0) {
           while ($collections->length > 0) {
               $c=$collections->Item(0);
               //echo "Collection Found: ". $c->getAttribute('Value')."\n";
               $mappingFile = '../workspace/tmp/collections.map';
               if (!is_null( $this->getMapping($mappingFile, $c->getAttribute('Value')))) {
                    //echo "Collection resolved\n";
                    array_push($collectionValues, $this->getMapping($mappingFile, $c->getAttribute('Value')));
               }
               //echo "Collection remove\n";
               $this->document->removeChild($c);
           }
        }

        if (count($institutes) > 0) {
            foreach ($institutes as $i) {
                $mappingFile = '../workspace/tmp/institute.map';
                if (!is_null($this->getMapping($mappingFile, $i->getAttribute('Value')))) {
                    array_push($instituteValues, $this->getMapping($mappingFile, $i->getAttribute('Value')));
                }
                $this->document->removeChild($i);
            }
        }

	
        if (count($licence) > 0) {
            foreach ($licence as $l) {
                $mappingFile = '../workspace/tmp/license.map';
                if (!is_null($this->getMapping($mappingFile, $l->getAttribute('Value')))) {
                    $licenceValue = $this->getMapping($mappingFile, $l->getAttribute('Value'));
                }
                $this->document->removeChild($l);
            }
        } else {
            /* TODO: Throw Exception if Licence '1' not valid */
            $licenceValue = '1';
        }

        if (count($faculty) > 0) {
            foreach ($faculty as $f) {
                $mappingFile = '../workspace/tmp/faculties.map';
                if (!is_null($this->getMapping($mappingFile, $f->getAttribute('Value')))) {
                    $grantorValue =  $this->getMapping($mappingFile, $f->getAttribute('Value'));
                    //echo "ThesisGrantor: ".$f->getAttribute('Value')."\n";
                }
                $this->document->removeChild($f);
            }
        }

        /* Special-ZIB: Der OldPublisher soll nicht als ThesisPublisher sondern als "echter" Publisher gelistet werden */
        if (count($publisher) > 0) {
            foreach ($publisher as $p) {
               $publisherValue =  $p->getAttribute('Value');
               //echo "ThesisPublisher: ".$p->getAttribute('Value')."\n";
               $this->document->removeChild($p);
            }
        }


        try {
            //echo "AFTER:".$this->completeXML->saveXML($this->document)."\n";
            // Dummyobject, does not need any content, because only one node is transformed
            $doc = Opus_Document::fromXml('<Opus>' . $this->completeXML->saveXML($this->document) . '</Opus>');


            if ($licenceValue !== null) {
                /* TODO: Throw Exception if Licence not valid */
                $doc->addLicence(new Opus_Licence($licenceValue));
            }

            /* Special-ZIB: Wir lassen ThesisGrantor und ThesisPublisher estmal weg */
            /*
            if ($publisherValue !== null) {
                $doc->setPublisherName($publisherValue);
            }

            if ($grantorValue !== null) {
                $doc->setThesisGrantor($grantorValue);
            }
             * 
             */
            // Set the publication status to published since only published documents shall be imported
            $doc->setServerState('published');

            // Add Document to Institute-Collections
            if (count($instituteValues) > 0) {
                foreach ($instituteValues as $i) {
                    $coll = new Opus_Collection($i);
                    $doc->addCollection($coll);
                  }
            }

            if (count($newseries) > 0) {
                foreach ($newseries as $s) {
                    $coll = new Opus_Collection($s['Key']);
                    $doc->addCollection($coll);
                    $doc->setIssue($s['Value']);
                }
            }

            if (count($collectionValues) > 0) {
                foreach ($collectionValues as $c) {
                    $coll = new Opus_Collection($c);
                    $doc->addCollection($coll);
                }
            }

            if ($ddcValue !== null) {
                $this->addDocumentToCollectionNumber($doc, 'ddc', $ddcValue);
            }

            if (count($newclasses) > 0) {
                foreach ($newclasses as $c) {
                    $this->addDocumentToCollectionNumber($doc, $c['Key'], $c['Value']);
                }
            }
             // store the document
           
            $doc->store();

            $imported['result'] = 'success';
            $imported['oldid'] = $oldid;
            $imported['newid'] = $doc->getId();
        } catch (Exception $e) {
            $imported['result'] = 'failure';
            $imported['message'] = $e->getMessage();
            $imported['entry'] = $this->completeXML->saveXML($this->document);
            $imported['oldid'] = $oldid;
        }

        /* TODO: Handle Unset */
        unset($doc);
	unset($this->document);
        unset($oldid );
        unset($ddcNotation);
        unset($ddcValue);
        unset($oldclasses);
        unset($newclasses);
	unset($faculty);
	unset($grantorValue);
	unset($licence);
	unset($licenceValue);
	unset($institutes);
	unset($instituteValues);
	unset($series);
	unset($seriesValues);
	unset($publisher);
	unset($publisherValue);
	
        return $imported;
    }

    /**
     * Get mapped Valuies for a document and add it
     *
     * @name: name of the Mapping ('institutes'
     * @return Opus_Licence Licence to be added to the document
     */
    public function getMapping($mappingFile, $id) {
        $fp = file($mappingFile);
        $mapping = array();
        foreach ($fp as $line) {
            $values = explode(" ", $line);
            $mapping[$values[0]] = $values[1];
        }
        unset($fp);
        if (array_key_exists($id, $mapping) === false) {
            return null;
        }
        return $mapping[$id];
    }

    protected function addDocumentToCollectionNumber($document, $role_name, $number) {

        //echo "addDocumentToCollectionNumber:".$role_name.":".$number."\n";
        $role = Opus_CollectionRole::fetchByName($role_name);
        $colls = Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $number);

        if (count($colls) > 0) {
            foreach ($colls as $c) {
                /* TODO: DDC-Hack */
                if (($role_name == 'ddc') and (count($c->getChildren()) > 0)) { continue; }
                $document->addCollection($c);
                //echo "Document added to $role_name Collection $number \n";
            }
        }
        else {
            echo "ERROR: Document not added to $role_name Collection $number \n";
        }
    }
}
