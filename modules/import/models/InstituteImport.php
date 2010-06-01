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
class InstituteImport
{
	/**
	 * Imports Collection data to Opus4
	 *
	 * @param Strring $data XML-String with classifications to be imported
	 * @return array List of documents that have been imported
	 */
	public function __construct($data)
	{
        #$roles = Opus_Collection_Information::getAllCollectionRoles();
        #foreach ($roles as $role) {
        #    if ($role['name'] ===  'Organisatorische Einheiten') {
        #        $cr = new Opus_CollectionRole($role['id']);
        #        $cr->delete();
        #    }
        #}

        // Use the institutes collection - always ID 1
        $collRole = new Opus_CollectionRole(1);

        $xml = new DomDocument;
        $xslt = new DomDocument;
        $xslt->load('../modules/import/views/scripts/opus3/institute_structure.xslt');
        $proc = new XSLTProcessor;
        $proc->registerPhpFunctions();
        $proc->importStyleSheet($xslt);
        $xml->loadXML($proc->transformToXml($data));
        // Build the Institutes Collection
        #$collRole = new Opus_CollectionRole();
        #$collRole->setName('Organisatorische Einheiten');
        #$collRole->setPosition(1);
        #$collRole->setVisible(1);
        #$collRole->setLinkDocsPathToRoot('count');
        #$collRole->store();

        $doclist = $xml->getElementsByTagName('table_data');

        foreach ($doclist as $document)
        {
            if ($document->getAttribute('name') === 'university_de') {
                $uniNumbers = $this->importUniversities($document, $collRole);
            }
            if ($document->getAttribute('name') === 'faculty_de') {
                $facNumbers = $this->importFaculties($document, $uniNumbers[0], $collRole->getId());
            }
            if ($document->getAttribute('name') === 'institute_de') {
                $instNumbers = $this->importInstitutes($document, $facNumbers, $collRole->getId());
            }
        }
        echo "\n";
	}

	/**
	 * transfers any OPUS3-conform classification System into an array
	 *
	 * @param DOMDocument $data XML-Document to be imported
	 * @return array List of documents that have been imported
	 */
	protected function transferOpusClassification($data)
	{
		$classification = array();

		$doclist = $data->getElementsByTagName('row');
		$index = 0;
		foreach ($doclist as $document)
		{
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
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importUniversities($data, $collRole)
    {
        $classification = $this->transferOpusClassification($data);

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/universities.map', 'w');

        $subcoll = array();

        foreach ($classification as $class) {
            echo ".";
            // first level category
		    $root = $collRole->getRootNode();
		    $child = $root->addLastChild();
            $coll = new Opus_Collection();
            $coll->setName($class['universitaet_anzeige']);
            $coll->setAddress($class['instadresse']);
            $coll->setCity($class['univort']);
            $coll->setDnbContactId($class['ddb_idn']);
            $coll->setTheme('default');
			$child->addCollection($coll);
			$root->setVisible(1);
			$root->store();
            $sc = $child->getId();
            $subcoll[] = $sc;
            fputs($fp, str_replace(" ", "_", $class['universitaet']) . ' ' . $sc . "\n");
        }

        fclose($fp);

        return $subcoll;
    }

	/**
	 * Imports Faculties from Opus3 to Opus4 directly (without XML)
	 *
	 * @param DOMDocument $data XML-Document to be imported
	 * @return array List of documents that have been imported
	 */
	protected function importFaculties($data, $subColls, $roleId)
	{
        $classification = $this->transferOpusClassification($data);

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/faculties.map', 'w');

		$subcoll = array();

		foreach ($classification as $class) {
          	echo ".";
            $parentColl = new Opus_Collection($subColls);
            // second level category
            $child = $parentColl->addLastChild();
            $coll = new Opus_Collection();
            $coll->setName($class['fakultaet']);
            $coll->setIsGrantor('1');
            $coll->setTheme('default');
			$child->addCollection($coll);
			$parentColl->store();            
            $subcoll[$class["nr"]] = $child->getId();
            fputs($fp, $class['nr'] . ' ' . $subcoll[$class["nr"]] . "\n");
		}
        
        fclose($fp);
		
		return $subcoll;
	}

	/**
     * Imports Institutes from Opus3 to Opus4 directly (without XML)
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importInstitutes($data, $subColls, $roleId)
    {
        $classification = $this->transferOpusClassification($data);

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/institute.map', 'w');

        foreach ($classification as $class) {
            echo ".";
            $parentColl = new Opus_Collection($subColls[$class['fakultaet']]);
            // second level category
            $child = $parentColl->addLastChild();
            $coll = new Opus_Collection();
            $coll->setName($class['name']);
            $coll->setTheme('default');
			$child->addCollection($coll);
			$parentColl->store();            
            fputs($fp, $class['nr'] . ' ' . $child->getId() . "\n");
        }

        fclose($fp);
    }
}
