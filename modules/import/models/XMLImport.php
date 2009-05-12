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
            // Dont build mapping files, use method to get a id for a number
            #$mf = new MappingFile($role);
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
            $institutes = null;
            $inst = null;
            $ddcNotation = null;
            $ddc = null;
            $ccsNotations = null;
            $ccs = null;
            $jelNotations = null;
            $jel = null;
            $pacsNotations = null;
            $pacs = null;
            $mscNotations = null;
            $msc = null;
            $apaNotations = null;
            $apa = null;
            $bkNotations = null;
            $bk = null;
            $oldid = $document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value');
            $licence = $document->getElementsByTagName('OldLicence')->Item(0);
            $institutes = $document->getElementsByTagName('OldInstitute');
            $ddcNotation = $document->getElementsByTagName('OldDdc')->Item(0);
            $ccsNotations = $document->getElementsByTagName('OldCcs');
            $jelNotations = $document->getElementsByTagName('OldJel');
            $pacsNotations = $document->getElementsByTagName('OldPacs');
            $mscNotations = $document->getElementsByTagName('OldMsc');
            $apaNotations = $document->getElementsByTagName('OldApa');
            $bkNotations = $document->getElementsByTagName('OldBk');
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
                $ddc_id = null;
                if (array_key_exists($ddcName, $this->collections) === true) {
                    //$ddc_id = $this->map(MappingFile::getShortName($ddcName), $ddcValue);
                    $ddc_id = Opus_Collection_Information::getClassification($this->collections[$ddcName], $ddcValue);
                    if ($ddc_id !== null) {
                        $ddc = new Opus_Collection($this->collections[$ddcName], $ddc_id);
                    }
                    else {
                    	echo "Mapping file for " . $this->collections[$ddcName]. " does not exist or class not found. Class $ddc_id not imported for old ID $oldid\n";
                    }
                }
                $document->removeChild($ddcNotation);
            }
            if ($institutes->length > 0)
            {
                $institute = array();
                $instituteName = 'Organisatorische Einheiten';
                $length = $institutes->length;
                for ($c = 0; $c < $length; $c++) {
                    // The item index is 0 any time, because the item is removed after processing
                    $instituteId = $institutes->Item(0);
                    $instituteValue = $instituteId->getAttribute('Value');
                    if (array_key_exists($instituteName, $this->collections) === true) {
                        if ($instituteValue !== null) {
                            $institute[] = new Opus_Collection($this->collections[$instituteName], $this->getInstitute($instituteValue));
                        }
                        else {
                            echo "Mapping file for " . $this->collections[$instituteName]. " does not exist or class not found. Institute assignation $inst_id not imported for old ID $oldid\n";
                        }
                    }
                    $document->removeChild($instituteId);
                }
            }
            if ($ccsNotations->length > 0)
            {
                $ccs = array();
                $ccsName = 'Computing Classification System';
                $length = $ccsNotations->length;
                for ($c = 0; $c < $length; $c++) {
                    // The item index is 0 any time, because the item is removed after processing
                	$ccsNotation = $ccsNotations->Item(0);
                    $ccsValue = $ccsNotation->getAttribute('Value');
                    $ccs_id = null;
                    if (array_key_exists($ccsName, $this->collections) === true) {
                        #$ccs_id = $this->map(MappingFile::getShortName($ccsName), $ccsValue);
                        $ccs_id = Opus_Collection_Information::getClassification($this->collections[$ccsName], $ccsValue);
                        if ($ccs_id !== null) {
                            $ccs[] = new Opus_Collection($this->collections[$ccsName], $ccs_id);
                        }
                        else {
                        	echo "Mapping file for " . $this->collections[$ccsName]. " does not exist or class not found. Class $ccs_id not imported for old ID $oldid\n";
                        }
                    }
                    $document->removeChild($ccsNotation);
                }
            }
            if ($pacsNotations->length > 0)
            {
                $pacs = array();
                $pacsName = 'Physics and Astronomy Classification Scheme';
                $length = $pacsNotations->length;
                for ($c = 0; $c < $length; $c++) {
                    // The item index is 0 any time, because the item is removed after processing
                	$pacsNotation = $pacsNotations->Item(0);
                    $pacsValue = $pacsNotation->getAttribute('Value');
                    $pacs_id = null;
                    if (array_key_exists($pacsName, $this->collections) === true) {
                        #$pacs_id = $this->map(MappingFile::getShortName($pacsName), $pacsValue);
                        $pacs_id = Opus_Collection_Information::getClassification($this->collections[$pacsName], $pacsValue);
                        if ($pacs_id !== null) {
                            $pacs[] = new Opus_Collection($this->collections[$pacsName], $pacs_id);
                        }
                        else {
                    	   echo "Mapping file for " . $this->collections[$pacsName]. " does not exist or class not found. Class $pacs_id not imported for old ID $oldid\n";
                        }
                    }
                    $document->removeChild($pacsNotation);
                }
            }
            if ($jelNotations->length > 0)
            {
                $jel = array();
                $jelName = 'Journal of Economic Literature (JEL) Classification System';
                $length = $jelNotations->length;
                for ($c = 0; $c < $length; $c++) {
                    // The item index is 0 any time, because the item is removed after processing
                	$jelNotation = $jelNotations->Item(0);
                    $jelValue = $jelNotation->getAttribute('Value');
                    $jel_id = null;
                    if (array_key_exists($jelName, $this->collections) === true) {
                        #$jel_id = $this->map(MappingFile::getShortName($jelName), $jelValue);
                        $jel_id = Opus_Collection_Information::getClassification($this->collections[$jelName], $jelValue);
                        if ($jel_id !== null) {
                            $jel[] = new Opus_Collection($this->collections[$jelName], $jel_id);
                        }
                        else {
                    	   echo "Mapping file for " . $this->collections[$jelName]. " does not exist or class not found. Class $jel_id not imported for old ID $oldid\n";
                        }
                    }
                    $document->removeChild($jelNotation);
                }
            }
            if ($mscNotations->length > 0)
            {
                $msc = array();
                $mscName = 'Mathematics Subject Classification';
                $length = $mscNotations->length;
                for ($c = 0; $c < $length; $c++) {
                    // The item index is 0 any time, because the item is removed after processing
                	$mscNotation = $mscNotations->Item(0);
                    $mscValue = $mscNotation->getAttribute('Value');
                    $msc_id = null;
                    if (array_key_exists($mscName, $this->collections) === true) {
                        #$msc_id = $this->map(MappingFile::getShortName($mscName), $mscValue);
                        $msc_id = Opus_Collection_Information::getClassification($this->collections[$mscName], $mscValue);
                        if ($msc_id !== null) {
                            $msc[] = new Opus_Collection($this->collections[$mscName], $msc_id);
                        }
                        else {
                    	   echo "Mapping file for " . $this->collections[$mscName]. " does not exist or class not found. Class $msc_id not imported for old ID $oldid\n";
                        }
                    }
                    $document->removeChild($mscNotation);
                }
            }
            if ($bkNotations->length > 0)
            {
                $bk = array();
                $bkName = 'Basisklassifikation';
                $length = $bkNotations->length;
                for ($c = 0; $c < $length; $c++) {
                    // The item index is 0 any time, because the item is removed after processing
                    $bkNotation = $bkNotations->Item(0);
                    $bkValue = $bkNotation->getAttribute('Value');
                    $bk_id = null;
                    if (array_key_exists($bkName, $this->collections) === true) {
                        #$bk_id = $this->map(MappingFile::getShortName($bkName), $bkValue);
                        $bk_id = Opus_Collection_Information::getClassification($this->collections[$bkName], $bkValue);
                        if ($bk_id !== null) {
                            $bk[] = new Opus_Collection($this->collections[$bkName], $bk_id);
                        }
                        else {
                    	    echo "Mapping file for " . $bkName . " does not exist or class not found. Class $bk_id not imported for old ID $oldid\n";
                        }
                    }
                    $document->removeChild($bkNotation);
                }
            }
            if ($apaNotations->length > 0)
            {
                $apa = array();
                $apaName = 'Classification and Indexing System der American Psychological Association';
                $length = $apaNotations->length;
                for ($c = 0; $c < $length; $c++) {
            	    // The item index is 0 any time, because the item is removed after processing
                    $apaNotation = $apaNotations->Item(0);
                    $apaValue = $apaNotation->getAttribute('Value');
                    $apa_id = null;
                    if (array_key_exists($apaName, $this->collections) === true) {
                        #$apa_id = $this->map(MappingFile::getShortName($apaName), $apaValue);
                        $apa_id = Opus_Collection_Information::getClassification($this->collections[$apaName], $apaValue);
                        if ($apa_id !== null) {
                            $apa[] = new Opus_Collection($this->collections[$apaName], $apa_id);
                        }
                        else {
                        	echo "Mapping file for " . $apaName. " does not exist or class not found. Class $apa_id not imported for old ID $oldid\n";
                        }
                    }
                    $document->removeChild($apaNotation);
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
                if (count($pacs) > 0) {
                    foreach($pacs as $pacsEntry) {
                        $pacsEntry->addEntry($doc);
                    }
                }
			    if (count($msc) > 0) {
                    foreach($msc as $mscEntry) {
                        $mscEntry->addEntry($doc);
                    }
                }
			    if (count($jel) > 0) {
                    foreach($jel as $jelEntry) {
                        $jelEntry->addEntry($doc);
                    }
                }
			    if (count($apa) > 0) {
                    foreach($apa as $apaEntry) {
                        $apaEntry->addEntry($doc);
                    }
                }
			    if (count($bk) > 0) {
                    foreach($bk as $bkEntry) {
                        $bkEntry->addEntry($doc);
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
	 	$fp = file('../workspace/tmp/license.map');
		foreach ($fp as $licence) {
			$mappedLicence = split("\ ", $licence);
			$lic[$mappedLicence[0]] = $mappedLicence[1];
		}
		return $lic[$shortName];
	 }

    /**
     * Get the institute ID for a document
     *
     * @return Opus_Licence Licence to be added to the document
     */
     public function getInstitute($oldId)
     {
        $fp = file('../workspace/tmp/institute.map');
        foreach ($fp as $licence) {
            $mappedLicence = split("\ ", $licence);
            $lic[$mappedLicence[0]] = $mappedLicence[1];
        }
        return $lic[$oldId];
     }

	 /**
	 * maps a notation from Opus3 on Opus4 schema
	 * depracated - dont use it any more since Mapping files are no longer supported
	 *
	 * @param string $data notation
	 * @return integer ID in Opus4
	 */
	protected function map($classification, $data)
	{
	 	// if the mapping file for this classification does not exists, there is nothing to map...
	 	if (file_exists('../workspace/tmp/'.$classification.'.map') === false) {
	 		return null;
	 	}
	 	$fp = file('../workspace/tmp/'.$classification.'.map');
		foreach ($fp as $licence) {
			$mappedLicence = split("\t", $licence);
			$lic[$mappedLicence[0]] = $mappedLicence[1];
		}
		if (array_key_exists($data, $lic) === true) {
			return $lic[$data];
		}
		return null;
	}
}
