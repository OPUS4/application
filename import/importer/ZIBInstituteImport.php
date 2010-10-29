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
 * @version     $Id: InstituteImport.php 6010 2010-09-29 08:12:55Z gmaiwald $
 */
class ZIBInstituteImport {

    private $uniname;
    private $unicity;

    /**
     * Imports Collection data to Opus4
     *
     * @param Strring $data XML-String with classifications to be imported
     * @return array List of documents that have been imported
     */
    public function __construct($data)	{
        $role = Opus_CollectionRole::fetchByName('institutes');
        $xml = new DomDocument;
        $xslt = new DomDocument;
        $xslt->load('../import/stylesheets/institute_structure.xslt');
        $proc = new XSLTProcessor;
        $proc->registerPhpFunctions();
        $proc->importStyleSheet($xslt);
        $xml->loadXML($proc->transformToXml($data));

        $doclist = $xml->getElementsByTagName('table_data');

        foreach ($doclist as $document) {
            if ($document->getAttribute('name') === 'university_de') {
                $this->importUniversities($document);
            }            
            
            if ($document->getAttribute('name') === 'faculty_de') {
                $this->importFaculties($document);
            }

            if ($document->getAttribute('name') === 'institute_de') {
                $this->importInstitutes($document, $role);
            }
        }
     }

    /**
     * transfers any OPUS3-conform classification System into an array
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function transferOpusClassification($data) {
	$classification = array();

	$doclist = $data->getElementsByTagName('row');
	$index = 0;
	foreach ($doclist as $document)	{
            $classification[$index] = array();
            foreach ($document->getElementsByTagName('field') as $field) {
            	$classification[$index][$field->getAttribute('name')] = $field->nodeValue;
            }
            $index++;
	}
	return $classification;
    }
    
    
    /**
     * Imports Universities from Opus3 to Opus4 into DNBInstitutes
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importUniversities($data) {
        $classification = $this->transferOpusClassification($data);
        //$subcoll = array();

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/universities.map', 'w');
        foreach ($classification as $class) {
            echo ".";
            $uni = new Opus_DnbInstitute();
            $uni->setName($class['universitaet_anzeige']);
            $this->uniname = $class['universitaet_anzeige'];
            $uni->setAddress($class['instadresse']);
            $uni->setCity($class['univort']);
            $this->unicity = $class['univort'];
            $uni->setDnbContactId($class['ddb_idn']);
            $uni->setIsGrantor('1');
            $uni->store();
            fputs($fp, str_replace(" ", "_", $class['universitaet']) . ' ' .  $uni->getId() . "\n");
        }
        echo "\n";
        fclose($fp);
    }

     /**
     * Imports Universities from Opus3 to Opus4 into DNBInstitutes
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importFaculties($data) {
        $classification = $this->transferOpusClassification($data);
        //$subcoll = array();

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/faculties.map', 'w');
        foreach ($classification as $class) {
            echo ".";
            $fac = new Opus_DnbInstitute();
            $fac->setName($this->uniname.",".$class['fakultaet']);
            $fac->setCity($this->unicity);
            $fac->setIsGrantor('1');
            $fac->store();
            fputs($fp, str_replace(" ", "_", $class['nr']) . ' ' .  $fac->getId() . "\n");
        }
        echo "\n";
        fclose($fp);
    }


    /**
     * Imports ZIB-Institutes from Opus3 to Opus4 directly (without XML)
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importInstitutes($data, $role) {
        $classification = $this->transferOpusClassification($data);

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/institute.map', 'w');
        foreach ($classification as $class) {
            echo ".";
            // ZIB-Hack:
            if ($class['name'] === 'High Performance Computing') { $class['name'] = 'Parallele und Verteilte Algorithmen'; }
            if ($class['name'] === 'KOBV') { fputs($fp, $class['nr'] . ' 15994' . "\n"); continue; }
            $root = $role->getRootCollection();
	    $coll = $root->addLastChild();
            $coll->setName($class['name']);
	    $coll->setVisible(1);
	    $root->store();
            fputs($fp, $class['nr'] . ' ' . $coll->getId() . "\n");
        }
        echo "\n";
        fclose($fp);
    }
}
