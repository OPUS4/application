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
 * @package     Module_Admin
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Model_BibtexImport {

    /**
     * Logger
     *
     * @var Zend_Log
     */
    private $log;


    /**
     * Binary
     *
     * @var string
     */
    private $binary;

    /**
     * Bibtex File
     *
     * @var string
     */
    private $bibtexFilename;

    /**
     * Mods-Opus-XSL
     *
     * @var string
     */
    private $xsl;

     /**
     * OpusXml
     *
     * @var DOMDocument
     */
    private $xml;

    public function __construct($filename = null) {
        $this->log = Zend_Registry::get('Zend_Log');
        $this->binary = "bib2xml";

        if(!$this->__isBinaryInstalled()) {
            $this->log->err($this->binary . ' is not installed');
            throw new Admin_Model_BibtexImportException(null, Admin_Model_BibtexImportException::BINARY_NOT_INSTALLED);
        }

        if(!is_readable($filename)) {
            $this->log->err($filename . ' is not readable');
            throw new Admin_Model_BibtexImportException(null, Admin_Model_BibtexImportException::FILE_NOT_READABLE);
        }
        
        if(!$this->__isUtf8Encoded($filename)) {
            $this->log->err($filename . ' is not utf8-endoded');
            throw new Admin_Model_BibtexImportException(null, Admin_Model_BibtexImportException::FILE_NOT_UTF8);
        }

        $this->bibtexFilename = $filename;

        $this->xsl = dirname(dirname(__FILE__)) . '/views/scripts/bibteximport/mods-import.xsl';
        $this->xml = new DOMDocument();
    }


    public function convertBibtexToOpusxml() {
	$bibtexRecords = array();
	$bibtexRecords = $this->__getBibTexRecords();
	$numBibtexRecords = count($bibtexRecords);
	
        if ($numBibtexRecords === 0) {
            $this->log->err($this->bibtexFilename . ' contains no valid bibtex');
            throw new Admin_Model_BibtexImportException(null, Admin_Model_BibtexImportException::FILE_NOT_BIBTEX);
        }	
	
	
	$idsBibtexRecords = array();
	foreach ($bibtexRecords as $r) {
            $id = null;
            $id = $this->__getIdFromBibtexRecord($r);
            if (strlen($id) === 0) {
                $message = trim($r);
		$this->log->err(' bibtex-record without id:' . $message);
                throw new Admin_Model_BibtexImportException($message, Admin_Model_BibtexImportException::RECORD_WITHOUT_ID);
            }

            if (array_key_exists($id, $idsBibtexRecords)) {
		$message = $id;
                $this->log->err(' bibtex record with duplicate id:' . $message);
                throw new Admin_Model_BibtexImportException($message, Admin_Model_BibtexImportException::DUPLICATE_ID);
            }
            $idsBibtexRecords[$id] = $r;
	}



        $xml = new DOMDocument();
        $xml->loadXML(shell_exec($this->binary . " " .  $this->bibtexFilename . " 2> /dev/null"));

        $numXmlDocuments = $xml->getElementsByTagName('mods')->length;
        $idsXmlDocuments = array();
        foreach ($xml->getElementsByTagName('mods') as $doc) {
            array_push($idsXmlDocuments, $doc->getAttribute('ID'));
        }

        if ($numXmlDocuments !== $numBibtexRecords) {
            $message = implode(', ', array_diff(array_keys($idsBibtexRecords),  $idsXmlDocuments));
            $this->log->err('numbibtex: ' . $numBibtexRecords . ' and numMods:' . $numXmlDocuments);
            throw new Admin_Model_BibtexImportException($message, Admin_Model_BibtexImportException::BIBTEX_MODS_ERROR);
	}

        $xsl = new DOMDocument();
        $xsl->load($this->xsl);

        $xslt = new XSLTProcessor();
        $xslt->importStylesheet($xsl);

        $this->xml->loadXML($xslt->transformToXML( $xml ));

        $numOpusDocuments = $this->xml->getElementsByTagName('opusDocument')->length;
        $idsOpusDocuments = array();
        foreach ($xml->getElementsByTagName('opusDocument') as $doc) {
            array_push($idsOpusDocuments, $doc->getAttribute('oldId'));
        }

        if ($numOpusDocuments !== $numBibtexRecords) {
            $message = implode(', ', array_diff(array_keys($idsBibtexRecords), $idsOpusDocuments));
            $this->log->err('numbibtex: ' . $numBibtexRecords . ' and numOpus:' . $numOpusDocuments);
            throw new Admin_Model_BibtexImportException($message, Admin_Model_BibtexImportException::MODS_XML_ERROR);
        }

       $this->__addBibtexRecordAsEnrichment();

       $invalidIds = array();
       foreach ($this->xml->getElementsByTagName('opusDocument') as $node) {
            $id = $node->getAttribute('oldId');

            $doc = new DomDocument;
            $import = new DOMElement('import');
            $doc->appendChild($import);
            $import->appendChild($doc->importNode($node, true));
            $validator = new Opus_Util_MetadataImportXmlValidation($doc);

	    try {
                $validator->checkValidXml();
            } catch(Opus_Util_MetadataImportInvalidXmlException $e) {
                array_push($invalidIds, $id);
            }
        }

        if (count($invalidIds) > 0) {
            $message = implode(', ', $invalidIds);
            throw new Admin_Model_BibtexImportException($message, Admin_Model_BibtexImportException::INVALID_XML_ERROR);
        }
   
        return $numOpusDocuments;
    }


    public function getXml() {
        return $this->xml;
    }


    private function __isBinaryInstalled() {
        $returnVal = shell_exec("which " . $this->binary);
        return (empty($returnVal) ? false : true);
    }


    private function __isUtf8Encoded($filename) {
        $output = file_get_contents($filename);
        return (mb_check_encoding($output, 'UTF-8') ? true : false);
    }


    private function __getBibtexRecords() {
	$bom = pack("CCC", 0xef, 0xbb, 0xbf);

	$content = "";
	foreach (file($this->bibtexFilename) as $line) {
            if (preg_match ( "/%/" , $line) ) {
                    continue;
            }
            if (preg_match ( "/^\n$/" , $line) ) {
                    continue;
            }
            if (0 == strncmp($line, $bom, 3)) {
                    $line = substr($line, 3);
            }
            $content .= $line;
	}

        if (!preg_match("/@/", $content)) { return array(); }

        return preg_split("/\n\s*@/", $content);
    }
    
    
    private function __getIdFromBibtexRecord($record) {
	$id = current(preg_split("/\n/", $record));
	$id = trim(strstr($id, '{'));
	$id = preg_replace("/^{/", "", $id);
	$id = preg_replace("/,$/", "", $id);
 	return trim($id);
    }



    private function __addBibtexRecordAsEnrichment() {
        $docs =  $this->xml->getElementsByTagName('opusDocument');
        foreach ($docs as $d) {
            $id = $d->getAttribute("oldId");
            $bibtex = $this->__getBibTexRecordById($id);

            $e = $d->appendChild(new DOMElement('enrichments'));
            $enr = $e->appendChild(new DOMElement('enrichment'));
	    $enr->setAttributeNode(new DOMAttr('key', 'BibtexRecord'));
            $enr->appendChild($this->xml->createTextNode($bibtex));
        }
    }




    private function __getBibTexRecordById($id) {
	$filtered_content = "\n";
	
	foreach (file($this->bibtexFilename) as $line) {
            if (!preg_match ( "/%/" , $line) ) {
                    $filtered_content .= $line;
            }
	}
	
        $records = explode ( "\n@", $filtered_content);
        $hits = preg_grep ( "/$id/" , $records);
        $record = "@" . array_pop($hits);
	
        return $record;
    }
}
