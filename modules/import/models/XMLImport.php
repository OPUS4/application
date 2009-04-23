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
     * Do some initialization on startup of every action
     *
     * @param string $xslt Filename of the stylesheet to be used
     * @param string $stylesheetPath Path to the stylesheet
     * @return void
     */
    public function __construct($xslt, $stylesheetPath)
    {
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
		$this->createDdcMappingfile();
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
            $coll = null;
            $oldid = $document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value');
            $licence = $document->getElementsByTagName('OldLicence')->Item(0);
            $ddcNotation = $document->getElementsByTagName('OldDdc')->Item(0);
            if ($licence !== null)
            {
                $licenceValue = $licence->getAttribute('Value');
                $lic = new Opus_Licence($this->getLicence($licenceValue));
                $document->removeChild($licence);
            }
            if ($ddcNotation !== null)
            {
                $ddcValue = $ddcNotation->getAttribute('Value');
                $this->coll_id = null;
                $this->coll_id = $this->mapDdc($ddcValue);
                if ($this->coll_id !== null) {
                    $coll = new Opus_Collection(1, $this->coll_id);
                }
                $document->removeChild($ddcNotation);
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
			    if ($coll !== null) {
			        $coll->addEntry($doc);
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
	 	$fp = file('../workspace/licenseMapping.txt');
		foreach ($fp as $licence) {
			$mappedLicence = split("\ ", $licence);
			$lic[$mappedLicence[0]] = $mappedLicence[1];
		}
		return $lic[$shortName];
	 }

	/**
	 * maps DDC-notation from Opus3 on Opus4 DDC-schema
	 *
	 * @param string $data DDC-notation
	 * @return void
	 */
	protected function createDdcMappingfile($coll = null)
	{
		if ($coll === null) $ddcCollectionRole = new Opus_CollectionRole(1);
		else $ddcCollectionRole = $coll;
		
		foreach ($ddcCollectionRole->getSubCollection() as $ddcNotation) {
			$this->writeNotation($ddcNotation);
			$this->createDdcMappingfile($ddcNotation);
		}
		
	}
	
	protected function writeNotation($ddcNotation) {
	    $fp = fopen('../workspace/ddcMapping.txt', 'a');
	    fputs($fp, $ddcNotation->getNumber() . ' ' . $ddcNotation->getId() . "\n");
	    fclose($fp);
	}

	/**
	 * maps DDC-notation from Opus3 on Opus4 DDC-schema
	 *
	 * @param string $data DDC-notation
	 * @return integer ID in Opus4
	 */
	protected function mapDdc($data)
	{
	 	$fp = file('../workspace/ddcMapping.txt');
		foreach ($fp as $licence) {
			$mappedLicence = split("\ ", $licence);
			$lic[$mappedLicence[0]] = $mappedLicence[1];
		}
		return $lic[$data];
	}
}
