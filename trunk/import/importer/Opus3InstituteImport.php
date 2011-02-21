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
 * @version     $Id: Opus3InstituteImport.php -1   $
 */
class Opus3InstituteImport {

   /**
    * Holds Zend-Configurationfile
    *
    * @var file
    */
    protected $config = null;


    /**
     * Imports Collection data to Opus4
     *
     * @param Strring $data XML-String with classifications to be imported
     * @return array List of documents that have been imported
     */
    public function __construct($data)	{

        $this->config = Zend_Registry::get('Zend_Config');
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
                $uniNumbers = $this->importUniversities($document, $role);
            }
            if ($document->getAttribute('name') === 'faculty_de') {
                $facNumbers = $this->importFaculties($document, $uniNumbers[0]);
            }
            if ($document->getAttribute('name') === 'institute_de') {
                $instNumbers = $this->importInstitutes($document, $facNumbers);
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
     * Imports Universities from Opus3 to Opus4 directly (without XML)
     * University is also a DNB Institute
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importUniversities($data, $role) {
        $mf = $this->config->import->mapping->universities;
        $fp = null;
        try {
            $fp = @fopen($mf, 'w');
            if (!$fp) {
                throw new Exception("ERROR Opus3InstituteImport: Could not create '".$mf."' for Universities.\n");
            }
        } catch (Exception $e){
            echo $e->getMessage();
            return;
        }


        $classification = $this->transferOpusClassification($data);
        $subcoll = array();

        foreach ($classification as $class) {

            if (array_key_exists('universitaet_anzeige', $class) === false) { continue; }
            if (array_key_exists('universitaet', $class) === false) { continue; }
           
            /* Create a Collection for University */
            $root = $role->getRootCollection();
	    $coll = $root->addLastChild();
            $coll->setName($class['universitaet_anzeige']);
            $coll->setVisible(1);
            $root->store();
            $subcoll[] = $coll->getId();

            /* Create a DNB-Institute for University */
            $uni = new Opus_DnbInstitute();
            $uni->setName($class['universitaet_anzeige']);
            $this->uniname = $class['universitaet_anzeige'];
            $uni->setAddress($class['instadresse']);
            $uni->setCity($class['univort']);
            $this->unicity = $class['univort'];
            $uni->setDnbContactId($class['ddb_idn']);
            $uni->setIsGrantor('1');
            $uni->store();

            echo "University imported: " . $class['universitaet_anzeige'] ."\n";
            fputs($fp, str_replace(" ", "_", $class['universitaet']) . ' ' .  $uni->getId() . "\n");
        }
        fclose($fp);
        return $subcoll;
    }

	
    /**
     * Imports Faculties from Opus3 to Opus4 directly (without XML)
     * Faculty is also a DNB Institute
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importFaculties($data, $pColl) {
        $mf1 = $this->config->import->mapping->faculties;
        $fp1 = null;
        try {
            $fp1 = @fopen($mf1, 'w');
            if (!$fp1) {
                throw new Exception("ERROR Opus3InstituteImport: Could not create '".$mf1."' for Faculties.\n");
            }
        } catch (Exception $e){
            echo $e->getMessage();
            return;
        }

        $mf2 = $this->config->import->mapping->grantors;
        $fp2 = null;
        try {
            $fp2 = @fopen($mf2, 'w');
            if (!$fp2) {
                throw new Exception("ERROR Opus3InstituteImport: Could not create '".$mf2."' for Grantors.\n");
            }
        } catch (Exception $e){
            echo $e->getMessage();
            return;
        }

        $classification = $this->transferOpusClassification($data);
        $subcoll = array();

        foreach ($classification as $class) {
            if (array_key_exists('fakultaet', $class) === false) { continue; }
            if (array_key_exists('nr', $class) === false) { continue; }

            /* Create a Collection for Faculty */
            $root = new Opus_Collection($pColl);
            $coll = $root->addLastChild();
            $coll->setName($class['fakultaet']);
            $coll->setVisible(1);
            $root->store();
            $subcoll[$class["nr"]] = $coll->getId();

            /* Create a DNB-Institute for Faculty */
            $fac = new Opus_DnbInstitute();
            $fac->setName($this->uniname.",".$class['fakultaet']);
            $fac->setCity($this->unicity);
            $fac->setIsGrantor('1');
            $fac->store();

            echo "Faculty imported: " . $class['fakultaet'] ."\n";
            fputs($fp1, $class['nr'] . ' ' . $subcoll[$class["nr"]] . "\n");
            fputs($fp2, $class['nr'] . ' ' . $fac->getId() . "\n");

	}
        fclose($fp1);
        fclose($fp2);
	return $subcoll;
    }

    /**
     * Imports Institutes from Opus3 to Opus4 directly (without XML)
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importInstitutes($data, $pColls)     {
        $mf = $this->config->import->mapping->institutes;
        $fp = null;
        try {
            $fp = @fopen($mf, 'w');
            if (!$fp) {
                throw new Exception("ERROR Opus3InstituteImport: Could not create '".$mf."' for Institutes.\n");
            }
        } catch (Exception $e){
            echo $e->getMessage();
            return;
        }

        $classification = $this->transferOpusClassification($data);

        foreach ($classification as $class) {
            if (array_key_exists('fakultaet', $class) === false) { continue; }
            if (array_key_exists('name', $class) === false) { continue; }
            if (array_key_exists('nr', $class) === false) { continue; }

            /*  Create a Collection for Institute */
            $root = new Opus_Collection($pColls[$class['fakultaet']]);
            $coll = $root->addLastChild();
            $coll->setName($class['name']);
	    $coll->setVisible(1);
	    $root->store();

            echo "Institute imported: " . $class['name'] ."\n";
            fputs($fp, $class['nr'] . ' ' . $coll->getId() . "\n");
        }
        fclose($fp);
    }
}
