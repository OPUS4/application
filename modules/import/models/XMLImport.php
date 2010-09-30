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
 * @version     $Id$
 */
class Import_Model_XMLImport { 

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
        $doc = null;

        $oldid = null;
        $oldid = $document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value');

        $ddcNotation = null;
        $ddcNotation = $document->getElementsByTagName('OldDdc')->Item(0);
        $ddcValue = null;

        $ccsNotations = null;
        $ccsNotations = $document->getElementsByTagName('OldCcs');
        $ccsValues = array();

        $pacsNotations = null;
        $pacsNotations = $document->getElementsByTagName('OldPacs');
        $pacsValues = array();

        $jelNotations = null;
        $jelNotations = $document->getElementsByTagName('OldJel');
        $jelValues = array();

        $mscNotations = null;
        $mscNotations = $document->getElementsByTagName('OldMsc');
        $mscValues = array();

        $bkNotations = null;
        $bkNotations = $document->getElementsByTagName('OldBk');
        $bkValues = array();

        //$apaNotations = null;
        //$apaNotations = $document->getElementsByTagName('OldApa')->Item(0)->getAttribute('Value');
        //$apaValues = null;

        $licence = null;
        $licence = $document->getElementsByTagName('OldLicence');
        $licenceValue = null;

        $institutes = null;
        $institutes = $document->getElementsByTagName('OldInstitute');
        $instituteValues = array();

        $series = null;
        $series = $document->getElementsByTagName('OldSeries');
        $seriesValues = array();

        /* TODO: Publisher University is ThesisPublisher */
        $publishers = null;
        $publishers = $document->getElementsByTagName('OldPublisherUniversity');
        $publisherValue = null;
        
	/* TODO: Grantor is ThesisGrantor */
        $facultyId = null;
        $facultyId = $document->getElementsByTagName('OldGrantor');
        $grantorValue = null;

        $imported = array();

        if ($ddcNotation !== null) {
            $ddcValue = $ddcNotation->getAttribute('Value');
            $this->document->removeChild($ddcNotation);
        }
        if ($ccsNotations->length > 0) {
            $ccsName = 'Computing Classification System';
            $ccsValues = $this->map($ccsNotations, $ccsName);
        }
        if ($pacsNotations->length > 0) {
            $pacsName = 'Physics and Astronomy Classification Scheme';
            $pacsValues = $this->map($pacsNotations, $pacsName);
        }
        if ($jelNotations->length > 0) {
            $jelName = 'Journal of Economic Literature (JEL) Classification System';
            $jelValues = $this->map($jelNotations, $jelName);
        }
        if ($mscNotations->length > 0) {
            $mscName = 'Mathematics Subject Classification';
            $mscValues = $this->map($mscNotations, $mscName);
        }
        if ($bkNotations->length > 0) {
            $bkName = 'Basisklassifikation (BK)';
            $bkValues = $this->map($bkNotations, $bkName);
        }
	
        /*
		if ($apaNotations->length > 0) {
            $apaName = 'American Psychological Association (APA) Klassifikation';
            $apaValues = $this->map($apaNotations, $apaName);
        }
         *
         */
	 
        if ($licence->length > 0) {
            foreach ($licence as $l) {
                $mappingFile = '../workspace/tmp/license.map';
                $licenceValue = $this->getMapping($mappingFile, $l->getAttribute('Value'));
                $this->document->removeChild($l);
            }
        } else {
            /* TODO: Throw Exception if Licence '1' not valid */
            $licenceValue = '1';
        }

        if (count($series) > 0) {
           foreach ($series as $s) {
               $mappingFile = '../workspace/tmp/series.map';
               array_push($seriesValues, $this->getMapping($mappingFile, $s->getAttribute('Value')));
               $this->document->removeChild($s);
           }
        }
     
        if (count($institutes) > 0) {
            foreach ($institutes as $i) {
                $mappingFile = '../workspace/tmp/institute.map';
                array_push($instituteValues, $this->getMapping($mappingFile, $i->getAttribute('Value')));
                $this->document->removeChild($i);
            }
        }	 
 
        /* TODO: Handle Grantor:
         *  Beim Anlegen der Collections für Institutes und Faculties (InstituteImport)
         *  Universität als DNB-Institut anlegen (Name, Ort, DNBID)
         *  jede Fakultät als DNB-Imnstitut anlegen (Name = "Universität, Fakultät", City = "Berlin o.ä."
         *
         * Nur für Dissertationen oder habilitation
         * $doc->setThesisGrantor($dnbinstitute)
	* f�r alle ZIB-Publikationen
         * $doc->SetThesisPublisher($dnbinstitute)
         */

        if ($facultyId->length > 0) {
            foreach ($facultyId as $f) {
                echo "Grantor: ".$f->getAttribute('Value')."\n";
                /*
                $mappingFile = '../workspace/tmp/faculties.map';
                $GrantorNewId = $this->getNewValue($mappingFile, $facultyId->getAttribute('Value'));
                /*
                if ($GrantorNewId !== null) {
                    $grantor = new Opus_Collection($GrantorNewId);
                }
                 *
                 
                if ($GrantorNewId !== null) {
                    echo "GrantorNew Id :".$GrantorNewId."\n";
                }
                 * 
                 */
                $this->document->removeChild($f);
            }
        }

        if ($publishers->length > 0) {
            foreach ($publishers as $p) {
               //$mappingFile = '../workspace/tmp/universities.map';
               //$publisherValue = $p->getAttribute('Value');
               $this->document->removeChild($p);
            }
        }

        try {
            
            // Dummyobject, does not need any content, because only one node is transformed

            $doc = Opus_Document::fromXml('<Opus>' . $this->completeXML->saveXML($this->document) . '</Opus>');                  
            if ($licenceValue !== null) {
                /* TODO: Throw Exception if Licence not valid */
                $doc->addLicence(new Opus_Licence($licenceValue));
            }

            if ($publisherValue !== null) {
                $doc->setThesisPublisher($publisherValue);
            }
            if ($grantorValue !== null) {
                $doc->setThesisGrantor($grantorValue);
            }
            // Set the publication status to published since only published documents shall be imported
            $doc->setServerState('published');

            // Add Document to Collections
            if (count($instituteValues) > 0) {
                foreach ($instituteValues as $i) {
                    $coll = new Opus_Collection($i);
                    $doc->addCollection($coll);
                  }
            }

            if (count($seriesValues) > 0) {
                foreach ($seriesValues as $s) {
                    $coll = new Opus_Collection($s);
                    $doc->addCollection($coll);
                }
            }

            if ($ddcValue !== null) {
                $this->addDocumentToCollectionNumber($doc, 'ddc', $ddcValue);
            }

            if (count($ccsValues) > 0) {
                foreach ($ccsValues as $c) {
                    $this->addDocumentToCollectionNumber($doc, 'ccs', $c);
                }
            }

            if (count($pacsValues) > 0) {
                foreach ($pacsValues as $p) {
                    $this->addDocumentToCollectionNumber($doc, 'pacs', $p);
                }
            }

            if (count($mscValues) > 0) {
                foreach ($mscValues as $m) {
                    $this->addDocumentToCollectionNumber($doc, 'msc', $m);
                }
            }

            if (count($jelValues) > 0) {
                foreach ($jelValues as $j) {
                    $this->addDocumentToCollectionNumber($doc, 'jel', $j);
                }
            }

           if (count($bkValues) > 0) {
                foreach ($bkValues as $b) {
                    $this->addDocumentToCollectionNumber($doc, 'bk', $b);
                }
            }
            /*
            if (count(apaValues) > 0) {
                foreach ($apaValues as $apaValue) {
                    $this->addDocumentToCollection($doc, 'apa', $apaValue);
                }
            }
             *
             */
            // store the document
            $doc->store();

            $imported['result'] = 'success';
            $imported['oldid'] = $oldid;
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
        unset($ccsNotations);
        unset($ccsValues);
	unset($pacsNotations);
	unset($pacsValues);
	unset($jelNotations);
	unset($jelValues);
	unset($mscNotations);
	unset($mscValues);
	unset($bkNotations);
	unset($bkValues);
	//unset($apaNotations);
	//unset($apaValues);
	unset($licence);
	unset($licenceValue);
	unset($institutes);
	unset($instituteValues);
	unset($series);
	unset($seriesValues);
	unset($publishers);
	unset($publisherValue );
	unset($facultyId);
	unset($grantor);
	
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
        return $mapping[$id];
    }


    public function getNewValue($mappingFile, $oldId) {
        $fp = file($mappingFile);
        $firstvalue = null;
        foreach ($fp as $licence) {
            $mappedLicence = explode(" ", $licence);
            if ($firstvalue === null) {
                $firstvalue = $mappedLicence[0];
            }
            $lic[$mappedLicence[0]] = $mappedLicence[1];
        }
        if (array_key_exists($oldId, $lic) === false) {
            if ($firstvalue !== null) {
                return $lic[$firstvalue];
            }
            return null;
        }
        unset($fp);
        return $lic[$oldId];
    }

    /**
     * maps a notation from Opus3 on Opus4 schema
     *
     * @param string $data notation
     * @return integer ID in Opus4
     */
    protected function map($inputCollection, $name) {
        $output = array();
        $length = $inputCollection->length;
        for ($c = 0; $c < $length; $c++) {
            // The item index is 0 any time, because the item is removed after processing
            $item = $inputCollection->Item(0);
            $value = $item->getAttribute('Value');
            $id = null;
            if (array_key_exists($name, $this->collections) === true) {
                try {
                    $returnedId = Opus_Collection::fetchCollectionsByRoleNumber($this->collections[$name], $value);
                    if (count($returnedId) > 0 && is_object($returnedId[0]) === true) {
                        $id = $returnedId[0]->getId();
                    } else {
                        $id = null;
                    }
                } catch (Exception $e) {
                    // TODO: Added Exception to see what we ignored...
                    throw new Exception($e);
                }
                if ($id !== null) {
                    $output[] = new Opus_Collection($id);
                } else {
                    echo "Number $value in $name not found - not imported for old OPUS-ID " . $this->document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value') . "\n";
                    fputs($this->_logfile, "Number $value in $name not found - not imported for old OPUS-ID " . $this->document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value') . "\n");
                }
            }
            $this->document->removeChild($item);
        }
        return $output;
    }

    protected function addDocumentToCollectionNumber($document, $role_name, $number) {
        $role = Opus_CollectionRole::fetchByName($role_name);
        $colls = Opus_Collection::fetchCollectionsByRoleNumber($role->getId(), $number);

        if (count($colls) > 0) {
            foreach ($colls as $c) {
                $document->addCollection($c);
                //echo "Document added to $role_name Collection $number \n";
            }
        }
        else {
            echo "ERROR: Document not added to $role_name Collection $number \n";
        }
    }
}
