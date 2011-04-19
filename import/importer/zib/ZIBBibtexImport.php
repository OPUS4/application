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
     * Holds the filehandle of the logfile
     *
     * @var file  Fileandle logfile
     */
    protected $normalizedZRTitles = array();

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
        // $data= str_replace("&#38;","&", $data);
        $this->completeXML = new DOMDocument;
        $this->completeXML->loadXML($this->_proc->transformToXml($data));
        $doclist = $this->completeXML->getElementsByTagName('Opus_Document');
        return $doclist;
    }
    
    public function initZRTitleArray() {
        // FIXME: hardcoded Collection-Number of ZIB-reports
	    $coll = new Opus_Collection(15993);
            foreach ($coll->getDocumentIds() as $id) {
                $doc = new Opus_Document($id);
		$title = strtolower($doc->getTitleMain(0)->getValue());
		$this->normalizedZRTitles[preg_replace('/[^a-z]/', '', $title)] = $doc->getId();
            }	    
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

	$doctype = $this->document->getAttribute('Type');
        //echo "(1)".$this->completeXML->saveXML($this->document)."\n";

        $this->skipEmptyFields();

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

        $opus4ids = array();
        $enrichment = $this->document->getElementsByTagName('Enrichment');
	$reportid = null;
	$opus3id = null;

	// Deduplication per ReportId and Opus3Id
        if (count($enrichment) > 0) {
            for ($i = 0; $i < $enrichment->length; $i++) {

                if ($enrichment->Item($i)->getAttribute('KeyName') === 'reportid') {
                    echo "(1) '".$doctype."' references Report-Id: ". $enrichment->Item($i)->getAttribute('Value')  . "\n";
		    $reportid = $enrichment->Item($i)->getAttribute('Value');
                    foreach (Opus_Document::getDocumentByIdentifier($reportid, 'serial') as $d) {
                        array_push($opus4ids, $d);
                        //$this->document->removeChild($enrichment->Item($i));
                    }
                }
		else if ($enrichment->Item($i)->getAttribute('KeyName') === 'opus3id') {
                    echo "(2) '".$doctype."' references Opus3-Id: ". $enrichment->Item($i)->getAttribute('Value')  . "\n";
		    $opus3id = $enrichment->Item($i)->getAttribute('Value');
                    foreach (Opus_Document::getDocumentByIdentifier($opus3id, 'opus3-id') as $d) {
                        array_push($opus4ids, $d);
                        //$this->document->removeChild($enrichment->Item($i));
                    }
                }
            }
        }
	
	// TODO: Deduplication per Title
	$title = $this->document->getElementsByTagName('TitleMain')->Item(0)->getAttribute('Value');
	$normalizedTitle = preg_replace('/[^a-z]/', '', strtolower($title));
	//echo "TITLE:'".$title."'\n";
	//echo "TITLE:'".$normalizedTitle."'\n";
	if (array_key_exists($normalizedTitle, $this->normalizedZRTitles)) {
		$id = $this->normalizedZRTitles[$normalizedTitle];
		echo "(3) '".$doctype."' references ZIB-Report with Opus4-Id '".$id."'\n";
		array_push($opus4ids, $id);
	}
	
        $nolist = null;
        $nolist = $this->document->getElementsByTagName('NoPublicationList');
        $nolistValues = array();

        if (count($nolist) > 0) {
            while ($nolist->length > 0) {
                $n = $nolist->Item(0);
                array_push($nolistValues, $n->getAttribute('Value'));
                $this->document->removeChild($n);
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
	    
	    $doctype = null;
            if ($doc->getType()) {
                $doctype = $doc->getType();
            }	    

            if (($doc->getBelongsToBibliography() === '0') && ($reportid != null)) {
                throw new Exception("DocType: ".$doctype."' -- ZIB Preprint/Report/Technical-Report with Report-Id '".$reportid."' will be ignored");
            }
            else if (($doc->getBelongsToBibliography() === '0') && ($opus3id != null)) {
                throw new Exception("DocType: ".$doctype."' -- ZIB Preprint/Report/Technical-Report with Opus3-Id '".$opus3id."' will be ignored");
            }
            else if ($doctype === 'unpublished') {
                throw new Exception("DocType: ".$doctype."' -- Unpublished Document will be ignored");
            }
            else if ($doc->getBelongsToBibliography() === '0') {
                throw new Exception("DocType: ".$doctype."' -- ZIB Preprint/Report/Technical-Report without known Id  will be ignored");
            }	    	  
	/*
	    if ($opus3id != null) {
		echo "DocType: ".$doctype."' -- Found Opus3-Id '".$opus3id."'\n";
	    }
	    if ($reportid != null) {
		echo "DocType: ".$doctype."' -- Found Report-Id '".$reportid."'\n";
	    }
	*/	
            foreach (array_unique($opus4ids) as $opus4id) {
                echo "Document references Opus4Id '".$opus4id."'\n";
                $ref = new Opus_Reference();
                //$ref->setType('opus4-id');
                //$ref->setType('isbn');
                $ref->setValue($opus4id);
                $ref->setLabel('reportzib');
                $ref->setParentId($doc->getId());
                //$ref->store();
                $doc->addReferenceOpus4Id($ref);
            }
	    

           // echo "Search for Institutes.\n";
	    $role = Opus_CollectionRole::fetchByName('institutes');
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());

            foreach ($instituteValues as $i) {
                //echo "Found $i\n";
                foreach ($colls as $c) {
                    if ($c->getNumber() === $i) {
                        $doc->addCollection($c);
                        //echo "Via Tag: Document added to Institute '" . $i . "'\n";
                    }
                }
            }


	    $role = Opus_CollectionRole::fetchByName('projects');
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());

            foreach ($projectValues as $p) {
                //echo "Found Project '$p' .\n";
                foreach ($colls as $c) {
                    //echo "Check '".$c->getName()." and '".$p."'\n";
                    if ($c->getName() === $p) {
                        $c->setVisible(1);
                        $c->store();
                        $doc->addCollection($c);
                        //echo "Via Tag: Document added to Project '" . $p . "'\n";
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
                            $c->setVisible(1);
                            $c->store();
                            $doc->addCollection($c);
                            echo "Via Tag: Document added to Person '" . $p . "'\n";
                        } 
                    }
                }
            }

            //else {
                /* Visualisierung, Parallele */
                foreach ($colls as $c) {
                    $names = explode(", ", $c->getName());
                    foreach ($doc->getPersonAuthor() as $author) {
                        if (strcmp($names[0], $author->getLastName()) != 0) { continue; }
                        $firstname = trim(str_replace(".","",$author->getFirstName()));

                        if (stripos($names[1], $firstname) === 0) {
                            $c->setVisible(1);
                            $c->store();
                            $doc->addCollection($c);
                            echo "Via Name: Document added to Person '" . $c->getName() . "'\n";
                        }
                    }
                }
            //}

            /* Some Tags did match neither Project, Person nor Workinggroup */
            if (count($nolistValues) > 0) {
                /* Numerik, Optimierung */
                foreach ($nolistValues as $n) {
                    echo "No Match found for Tag: $n .\n";
                }
            }

            // store the document
            $doc->store();
/*
            if (!is_null($reportid)) {
                $e = new Opus_Enrichment();
                $e->setKeyName('report_ref');
                $e->setValue($reportid);
                $e->setParentId($doc->getId());
                $e->store();
            }
 * 
 */

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

        return $imported;
    }
    
    
     private function skipEmptyFields() {
        // BUGFIX:OPUSVIER-938: Fehler beim Import von Dokumenten mit Autoren ohne Vornamen
        $roles = array();
        array_push($roles, array('PersonAdvisor', 'FirstName'));
        array_push($roles, array('PersonAuthor', 'FirstName'));
        array_push($roles, array('PersonContributor', 'FirstName'));
        array_push($roles, array('PersonEditor', 'FirstName'));
        array_push($roles, array('PersonReferee', 'FirstName'));
        array_push($roles, array('PersonOther', 'FirstName'));
        array_push($roles, array('PersonTranslator', 'FirstName'));
        array_push($roles, array('PersonSubmitter', 'FirstName'));

        array_push($roles, array('TitleMain', 'Value'));
        array_push($roles, array('TitleAbstract', 'Value'));
        array_push($roles, array('TitleParent', 'Value'));
        array_push($roles, array('TitleSub', 'Value'));
        array_push($roles, array('TitleAdditional', 'Value'));

        array_push($roles, array('SubjectUncontrolled', 'Value'));
        array_push($roles, array('IdentifierIsbn', 'Value'));
	array_push($roles, array('Enrichment', 'Value'));

        array_push($roles, array('Note', 'Message'));
 
        foreach ($roles as $r) {

           $elements = $this->document->getElementsByTagName($r[0]);
           $elementsToDelete = array();

           foreach ($elements as $e) {
                if (trim($e->getAttribute($r[1])) == "") {
                    $this->log("ERROR Opus3XMLImport: '".$r[0]."' with empty '".$r[1]."' will not be imported.\n");
                    $elementsToDelete[] = $e;
                }
            }

            foreach($elementsToDelete as $e) {
                //echo "Delete '".$r[0]."' ".$e->getAttribute('LastName')."\n";
                $this->document->removeChild($e);
            }

        }
    }   
}
?>
