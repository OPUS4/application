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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: XMLImport.php 5666 2010-09-21 12:56:01Z gmaiwald $
 */
class ZIBBibtexImport {

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
     * Holds the name of the bibtex-imprtfile.
     *
     * @var DomDocument  Defaults to null.
     */
    protected $_importFile = null;
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
    public function __construct($xslt, $stylesheetPath, $importFile) {

        // Initialize member variables.
        $this->_xml = new DomDocument;
        $this->_xslt = new DomDocument;
        $this->_xslt->load($stylesheetPath . '/' . $xslt);
        $this->_proc = new XSLTProcessor;
        $this->_proc->registerPhpFunctions();
        $this->_proc->importStyleSheet($this->_xslt);
        $this->_logfile = fopen($this->logfile, 'w');
        $this->_importFile = $importFile;
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
     * Returns the title auf a document from an import-file 
     *
     * @param DOMDocument $document XML-Document to be imported
     * @return array information about the document that has been imported
     */
    public function getTitle($document) {
	$this->document = $document;
	$doc = Opus_Document::fromXml('<Opus>' . $this->completeXML->saveXML($this->document) . '</Opus>');
	 if ($doc->getTitleMain()) {
	    return $doc->getTitleMain(0)->getValue();
	}
	return null;
    }

    /**
     * Returns the oldidentifuier auf a document from an import-file
     *
     * @param DOMDocument $document XML-Document to be imported
     * @return array information about the document that has been imported
     */
    public function getId($document) {
	$this->document = $document;
	$doc = Opus_Document::fromXml('<Opus>' . $this->completeXML->saveXML($this->document) . '</Opus>');
	 if ($doc->getIdentifierOld()) {
	    return $doc->getIdentifierOld(0)->getValue();
	}
	return null;
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

        $institutes = null;
        $institutes = $this->document->getElementsByTagName('PublicationGroup');
        $instituteValues = array();

        if (count($institutes) > 0) {
            while ($institutes->length > 0) {
                $i = $institutes->Item(0);
                array_push($instituteValues, $i->getAttribute('Value'));
                $this->document->removeChild($i);
            }
        }

        $projects = null;
        $projects = $this->document->getElementsByTagName('PublicationProject');
        $projectValues = array();

        if (count($projects) > 0) {
            while ($projects->length > 0) {
                $p = $projects->Item(0);
                array_push($projectValues, $p->getAttribute('Value'));
                $this->document->removeChild($p);
            }
        }

        $projectnames = null;
        $projectnames = $this->document->getElementsByTagName('PublicationProjectName');
        $projectnameValues = array();

        if (count($projectnames) > 0) {
            while ($projectnames->length > 0) {
                $p = $projectnames->Item(0);
                array_push($projectnameValues, $p->getAttribute('Value'));
                $this->document->removeChild($p);
            }
        }

        $persons = null;
        $persons = $this->document->getElementsByTagName('PublicationPerson');
        $personValues = array();

        if (count($persons) > 0) {
            while ($persons->length > 0) {
                $p = $persons->Item(0);
                array_push($personValues, $p->getAttribute('Value'));
                $this->document->removeChild($p);
            }
        }

        $enrichment = $this->document->getElementsByTagName('Enrichment');
        if (count($enrichment) > 0) {
            for ($i = 0; $i < $enrichment->length; $i++) {
                if ($enrichment->Item($i)->getAttribute('KeyName') === 'report') {
                    echo "Found Report-Id: ". $enrichment->Item($i)->getAttribute('Value')  . "\n";
                }
            }
        }

        $opus3id = null;
        $opus3id = $this->document->getElementsByTagName('Opus3Identifier');
        $reportid = null;

        if (count($opus3id) > 0) {
            while ($opus3id->length > 0) {
                $id1 = $opus3id->Item(0)->getAttribute('Value');
                $docid = Opus_Document::getDocumentByIdentifier($id1, 'opus3-id');
                $doc = new Opus_Document($docid);
                $reportid = $doc->getIssue();
                echo "Found Opus3Identifier " . $id1 ." with Report-Id: ". $reportid . "\n";;
                $this->document->removeChild($opus3id->Item(0));
            }
        }

        //$authors = null;
        //$authors = $this->document->getElementsByTagName('PersonAuthor');
	
	try {
            $doc = Opus_Document::fromXml('<Opus>' . $this->completeXML->saveXML($this->document) . '</Opus>');
            // Set the publication status to published since only published documents shall be imported
            $doc->setServerState('published');

            $oldid = null;
            if ($doc->getIdentifierOld()) {
                $oldid = $doc->getIdentifierOld(0)->getValue();
            }

            if ($doc->getBelongsToBibliography() === '0') {
                throw new Exception("ZIB Preprint/Report/Technical-Report will be ignored");
            }

	    $role = Opus_CollectionRole::fetchByName('institutes');
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());

            foreach ($instituteValues as $i) {
                foreach ($colls as $c) {
                    if ($c->getNumber() === $i) {
                        $doc->addCollection($c);
                        echo "Via Tag: Document added to Institute  " . $i . "\n";
                    }
                }
            }

	    $role = Opus_CollectionRole::fetchByName('projects');
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());

            foreach ($projectValues as $p) {
                foreach ($colls as $c) {
                    if ($c->getName() === $p) {
                        $doc->addCollection($c);
                        echo "Via Tag: Document added to Project  " . $i . "\n";
                    }
                }
            }


	    $role = Opus_CollectionRole::fetchByName('persons');
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());

            if (count($personValues) > 0) {
                /* Numerik, Optimierung */
                foreach ($personValues as $p) {
                    foreach ($colls as $c) {
                        if ($c->getNumber() === $p) {
                            $doc->addCollection($c);
                            echo "Via Tag:  Document added to Person " . $p . "\n";
                        } 
                    }
                }
            }

            else {
                /* Visualisierung, Parallele */
                foreach ($colls as $c) {
                    $names = explode(", ", $c->getName());
                    foreach ($doc->getPersonAuthor() as $author) {
                        if (strcmp($names[0], $author->getLastName()) != 0) { continue; }
                        $firstname = trim(str_replace(".","",$author->getFirstName()));

                        if (stripos($names[1], $firstname) === 0) {
                            $doc->addCollection($c);
                            echo "Via Name: Document added to Person " . $p . "\n";
                        }
                    }
                }
            }

            // store the document
            $doc->store();

            if (!is_null($reportid)) {
                $e = new Opus_Enrichment();
                $e->setKeyName('report_ref');
                $e->setValue($reportid);
                $e->setParentId($doc->getId());
                $e->store();
            }

	    $imported['result'] = 'success';
            #$imported['entry'] = $this->completeXML->saveXML($this->document);
            #$imported['document'] = $doc;
            $imported['newid'] = $doc->getId();
            $imported['oldid'] = $oldid;
        } catch (Exception $e) {
            //echo $this->completeXML->saveXML($this->document)."\n";
            $imported['result'] = 'failure';
            $imported['message'] = $e->getMessage();
            $imported['entry'] = $this->completeXML->saveXML($this->document);
            $imported['oldid'] = $oldid;
        }

        unset($this->document);
        unset($document);

        return $imported;
    }
}
?>
