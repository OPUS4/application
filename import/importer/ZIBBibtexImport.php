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
     * Imports metadata from an XML-Document
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array information about the document that has been imported
     */
    public function import($document) {
        // Use the document as attribute
        $this->document = $document;
	
	try {
            // Dummyobject, does not need any content, because only one node is transformed
            $doc = Opus_Document::fromXml('<Opus>' . $this->completeXML->saveXML($this->document) . '</Opus>');
            // Set the publication status to published since only published documents shall be imported
            $doc->setServerState('published');

            $oldid = null;
            if ($doc->getIdentifierOld()) {
                $oldid = $doc->getIdentifierOld(0)->getValue();
            }

            // ZIB_reports or ZIB_Preprints will be ignored
            if ($doc->getNote()) {
                if(preg_match('/^ZIB/', $doc->getNote(0)->getValue())) {
                    throw new Exception("ZIB Preprint/Report/Technical report will be ignored");
                }
                if(preg_match('/^Preprint SC/', $doc->getNote(0)->getValue())) {
                    throw new Exception("ZIB Preprint/Report/Technical report will be ignored");
                }
                if(preg_match('/^ZR\s/', $doc->getNote(0)->getValue())) {
                    throw new Exception("ZIB Preprint/Report/Technical report will be ignored");
                }
             }

            foreach ($doc->getIdentifierUrl() as $url) {
               if(preg_match('/opus\.kobv\.de/', $url->getValue())) {
                    throw new Exception("ZIB-Opus-Documents report will be ignored");
               }
            }
            
            foreach ($doc->getEnrichment() as $enrichment) {
                if ($enrichment->getKeyName() === 'type') {
                    if(preg_match('/ZIB/', $enrichment->getValue())) {
                        throw new Exception("ZIB Preprint/Report/Technical report will be ignored");
                    }
                }
                if ($enrichment->getKeyName() === 'howpublished') {
                    if(preg_match('/ZIB/', $enrichment->getValue())) {
                        throw new Exception("ZIB Preprint/Report/Technical report will be ignored");
                    }
                }
            }
  
	     // The Institutes-Name is part of the import-file
	    $role = Opus_CollectionRole::fetchByName('institutes');
            $colls = Opus_Collection::fetchCollectionsByRoleId($role->getId());
            foreach ($colls as $c) {
		
		$filename = $this->_importFile;
		$filename = preg_replace('/.*\//', '', $filename);
		$filename = preg_replace('/\..*/', '', $filename);
		
		//echo "Filename:".$filename."#".$c->getName()."#".stripos($c->getName(), $filename)."\n";
		
                if (strrpos($c->getName(), $filename) !== false) {
                    $doc->addCollection($c);
                    //echo "Add Document  to Abteilungs-Collection ".$c->getName()."\n";
                }
            }

            // store the document
            $doc->store();

	    $imported['result'] = 'success';
            #$imported['entry'] = $this->completeXML->saveXML($this->document);
            #$imported['document'] = $doc;
            $imported['oldid'] = $oldid;
        } catch (Exception $e) {
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
