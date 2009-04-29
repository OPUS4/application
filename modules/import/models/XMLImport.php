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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class XMLImport
{

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
     * Holds the collections predifined in OPUS
     *
     * @var array  Defaults to the OPUS collections (Name => ID).
     */    
    protected $collections = array();

    /**
     * Holds the collections predifined in OPUS
     *
     * @var array  Defaults to the OPUS collections (Name => ID).
     */    
    protected $collectionShortNames = array('Dewey Decimal Classification' => 'ddc', 'Computing Classification System' => 'ccs');

    /**
     * Do some initialization on startup of every action
     *
     * @param string $xslt Filename of the stylesheet to be used
     * @param string $stylesheetPath Path to the stylesheet
     * @return void
     */
    public function __construct($xslt, $stylesheetPath)
    {
        // Reading Collection IDs
        $roles = Opus_Collection_Information::getAllCollectionRoles();
        foreach ($roles as $role) {
            $this->collections[$role['name']] = $role['id'];
        }
        // Initialize member variables.
        $this->_xml = new DomDocument;
        $this->_xslt = new DomDocument;
    	$this->_xslt->load($stylesheetPath . '/' . $xslt);
        $this->_proc = new XSLTProcessor;
        $this->_proc->registerPhpFunctions();
        $this->_proc->importStyleSheet($this->_xslt);
    }

	/**
	 * Imports metadata from an XML-Document
	 *
	 * @param DOMDocument $data XML-Document to be imported
	 * @return array List of documents that have been imported
	 */
	public function import($data)
	{
		foreach ($this->collections as $collection => $id) {
		    $this->createMappingfile(array($id, $this->collectionShortNames[$collection]));
		}
		$imported = array();
		$imported['success'] = array();
		$imported['failure'] = array();
		$documentsXML = new DOMDocument;
		$documentsXML->loadXML($this->_proc->transformToXml($data));
		$doclist = $documentsXML->getElementsByTagName('Opus_Document');
		foreach ($doclist as $document) 
		{
            $licence = null;
            $lic = null;
            $ddcNotation = null;
            $ddc = null;
            $ccsNotation = null;
            $ccs = null;
            $oldid = $document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value');
            $licence = $document->getElementsByTagName('OldLicence')->Item(0);
            $ddcNotation = $document->getElementsByTagName('OldDdc')->Item(0);
            $ccsNotations = $document->getElementsByTagName('OldCcs');
            if ($licence !== null)
            {
                $licenceValue = $licence->getAttribute('Value');
                $lic = new Opus_Licence($this->getLicence($licenceValue));
                $document->removeChild($licence);
            }
            if ($ddcNotation !== null)
            {
                $ddcName = 'Dewey Decimal Classification';
                $ddcValue = $ddcNotation->getAttribute('Value');
                $this->ddc_id = null;
                $this->ddc_id = $this->map($this->collectionShortNames[$ddcName], $ddcValue);
                if ($this->ddc_id !== null) {
                    $ddc = new Opus_Collection($this->collections[$ddcName], $this->ddc_id);
                }
                $document->removeChild($ddcNotation);
            }
            if ($ccsNotations->length > 0)
            {
                $ccs = array();
                $ccsName = 'Computing Classification System';
                for ($c = 0; $c < $ccsNotations->length; $c++) {
                	$ccsNotation = $ccsNotations->Item($c);
                    $ccsValue = $ccsNotation->getAttribute('Value');
                    $ccs_id = null;
                    $ccs_id = $this->map($this->collectionShortNames[$ccsName], $ccsValue);
                    if ($ccs_id !== null) {
                        $ccs[] = new Opus_Collection($this->collections[$ccsName], $ccs_id);
                    }
                    $document->removeChild($ccsNotation);
                }
            }
			try {
			    $doc = Opus_Document::fromXml('<Opus>' . $documentsXML->saveXML($document) . '</Opus>');
			    if ($lic !== null) {
			    	$doc->addLicence($lic);
			    }
			    // What about publicationVersion field?
			    //$doc->setPublicationVersion('published');
			    $doc->store();
			    // Add this document to its DDC classification
			    if ($ddc !== null) {
			        $ddc->addEntry($doc);
			    }
			    if (count($ccs) > 0) {
			        foreach($ccs as $ccsEntry) {
			            $ccsEntry->addEntry($doc);
			        }
			    }
			    $index = count($imported['success']);
			    $imported['success'][$index]['entry'] = $documentsXML->saveXML($document);
			    $imported['success'][$index]['document'] = $doc;
			    $imported['success'][$index]['oldid'] = $oldid;
			}
			catch (Exception $e) {
				$index = count($imported['failure']);
                $imported['failure'][$index]['message'] = $e->getMessage();
                $imported['failure'][$index]['entry'] = $documentsXML->saveXML($document);
			} 
		}
		return $imported;
	}
	
	/**
	 * Get the licence for a document and add it
	 * 
	 * @return Opus_Licence Licence to be added to the document
	 */
	 public function getLicence($shortName)
	 {
	 	$fp = file('../workspace/tmp/licenseMapping.txt');
		foreach ($fp as $licence) {
			$mappedLicence = split("\ ", $licence);
			$lic[$mappedLicence[0]] = $mappedLicence[1];
		}
		return $lic[$shortName];
	 }

	/**
	 * creates a mapping file for a OPUS3 classification system to OPUS4
	 *
	 * @param array $classification  
	 * @return void
	 */
	protected function createMappingfile($classification, $coll = null)
	{
		if ($coll === null) $ddcCollectionRole = new Opus_CollectionRole($classification[0]);
		else $ddcCollectionRole = $coll;
		
		foreach ($ddcCollectionRole->getSubCollection() as $ddcNotation) {
			$this->writeNotation($ddcNotation, $classification[1]);
			$this->createMappingfile($classification, $ddcNotation);
		}
		
	}
	
	protected function writeNotation($notation, $classification) {
	    $fp = fopen('../workspace/tmp/'.$classification.'Mapping.txt', 'a');
	    fputs($fp, $notation->getNumber() . "\t" . $notation->getId() . "\n");
	    fclose($fp);
	}

	/**
	 * maps a notation from Opus3 on Opus4 schema
	 *
	 * @param string $data notation
	 * @return integer ID in Opus4
	 */
	protected function map($classification, $data)
	{
	 	$fp = file('../workspace/tmp/'.$classification.'Mapping.txt');
		foreach ($fp as $licence) {
			$mappedLicence = split("\t", $licence);
			$lic[$mappedLicence[0]] = $mappedLicence[1];
		}
		return $lic[$data];
	}
}
