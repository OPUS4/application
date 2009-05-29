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
            $this->document = $document;
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
                $this->document->removeChild($licence);
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
                $this->document->removeChild($ddcNotation);
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
                    $this->document->removeChild($instituteId);
                }
            }
            if ($ccsNotations->length > 0)
            {
                $ccsName = 'Computing Classification System';
                $ccs = $this->map($ccsNotations, $ccsName);
            }
            if ($pacsNotations->length > 0)
            {
                $pacsName = 'Physics and Astronomy Classification Scheme';
                $pacs = $this->map($pacsNotations, $pacsName);
            }
            if ($jelNotations->length > 0)
            {
                $jelName = 'Journal of Economic Literature (JEL) Classification System';
                $jel = $this->map($jelNotations, $jelName);
            }
            if ($mscNotations->length > 0)
            {
                $mscName = 'Mathematics Subject Classification';
                $msc = $this->map($mscNotations, $mscName);
            }
            if ($bkNotations->length > 0)
            {
                $bkName = 'Basisklassifikation (BK)';
                $bk = $this->map($bkNotations, $bkName);
            }
            if ($apaNotations->length > 0)
            {
                $apaName = 'American Psychological Association (APA) Klassifikation';
                $apa = $this->map($apaNotations, $apaName);
            }
			try {
			    $doc = Opus_Document::fromXml('<Opus>' . $documentsXML->saveXML($this->document) . '</Opus>');
			    if ($lic !== null) {
			    	$doc->addLicence($lic);
			    }
			    // Set the publication status to published since only published documents shall be imported
			    $doc->setServerState('published');
			    $doc->store();
			    // Add this document to its DDC classification
			    if ($ddc !== null) {
			        $ddc->addEntry($doc);
			    }
                if (count($institute) > 0) {
                    foreach($institute as $instEntry) {
                        $instEntry->addEntry($doc);
                    }
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
			    $imported['success'][$index]['entry'] = $documentsXML->saveXML($this->document);
			    $imported['success'][$index]['document'] = $doc;
			    $imported['success'][$index]['oldid'] = $oldid;
			}
			catch (Exception $e) {
				$index = count($imported['failure']);
                $imported['failure'][$index]['message'] = $e->getMessage();
                $imported['failure'][$index]['entry'] = $documentsXML->saveXML($this->document);
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
	protected function map($inputCollection, $name)
	{
        $output = array();
        $length = $inputCollection->length;
        for ($c = 0; $c < $length; $c++) {
            // The item index is 0 any time, because the item is removed after processing
            $item = $inputCollection->Item(0);
            $value = $item->getAttribute('Value');
            $id = null;
            if (array_key_exists($name, $this->collections) === true) {
                try {
                    $id = Opus_Collection_Information::getClassification($this->collections[$name], $value);
                }
                catch (Exception $e) {
                	// do nothing, but continue
                }
                if ($id !== null) {
                    $output[] = new Opus_Collection($this->collections[$name], $id);
                }
                else {
                    echo "Number $value in $name not found - not imported for old OPUS-ID " . $this->document->getElementsByTagName('IdentifierOpus3')->Item(0)->getAttribute('Value') . "\n";
                }
            }
            $this->document->removeChild($item);
        }
        return $output;
   	}
}
