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

    private $bibtexFilename;
    private $xmlDirectory;
    private $xmlFilename;

    private $binary;
    private $xsl;
    private $xml;
        
    public function __construct($filename = null, $directory = null) {

        $this->binary = "bib2xml";
        if(!$this->__isBinaryInstalled()) {
             throw new Admin_Model_Exception($this->binary . ' is not installed');
        }

        if(!is_readable($filename)) {
             throw new Admin_Model_Exception($filename . ' is not readable');
        }
        $this->bibtexFilename = $filename;

               
        if(!is_writable($directory)) {
             throw new Admin_Model_Exception($directory . ' is not writeable');
        }
        $this->xmlDirectory = $directory;

        $this->xmlFilename = $this->xmlDirectory . "/bibtex_import--" . sha1($this->xmlFilename . gettimeofday(true)) . ".xml";
        $this->xsl = dirname(dirname(__FILE__)) . '/views/scripts/bibtexupload/mods-import.xsl';

        $this->xml = new DOMDocument();
    }


    public function convertBibtexToOpusxml() {
        $xml = new DOMDocument();
        $xml->loadXML(shell_exec($this->binary . " " .  $this->bibtexFilename . " 2> /dev/null"));

        $xsl = new DOMDocument();
        $xsl->load($this->xsl);

        $xslt = new XSLTProcessor();
        $xslt->importStylesheet($xsl);

        $this->xml->loadXML($xslt->transformToXML( $xml ));

        if ($this->xml->getElementsByTagName('opusDocument')->length === 0) {
            throw new Admin_Model_Exception( $this->bibtexFilename . ' contains no valid metadata');
        }

        $this->__addBibtexRecordAsEnrichment();
        $this->xml->save($this->xmlFilename);
        return $this->xml->getElementsByTagName('opusDocument')->length;
    }
    
    
    public function getXmlFilename() {
        return $this->xmlFilename;
    }

    
    private function __isBinaryInstalled() {
        $returnVal = shell_exec("which " . $this->binary);
        return (empty($returnVal) ? false : true);
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
