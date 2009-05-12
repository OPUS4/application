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
        $roles = Opus_Collection_Information::getAllCollectionRoles();
        foreach ($roles as $role) {
            if ($role['name'] ===  'Organisatorische Einheiten') {
                $cr = new Opus_CollectionRole($role['id']);
                $cr->delete();
            }
        }

        // Build the Institutes Collection
	    $collRole = new Opus_CollectionRole();
        $collRole->setName('Organisatorische Einheiten');
        $collRole->setPosition(1);
        $collRole->setVisible(1);
        $collRole->setLinkDocsPathToRoot('count');
        $collRole->store();

        $doclist = $data->getElementsByTagName('table_data');
        foreach ($doclist as $document)
        {
            if ($document->getAttribute('name') === 'faculty_de') {
                $facNumbers = $this->importFaculties($document, $collRole);
            }
            if ($document->getAttribute('name') === 'institute_de') {
                $instNumbers = $this->importInstitutes($document, $facNumbers, $collRole->getId());
            }
        }
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
	 * Imports Faculties from Opus3 to Opus4 directly (without XML)
	 *
	 * @param DOMDocument $data XML-Document to be imported
	 * @return array List of documents that have been imported
	 */
	protected function importFaculties($data, $collRole)
	{
        $classification = $this->transferOpusClassification($data);

		$subcoll = array();

		foreach ($classification as $class) {
          	echo ".";
		    // first level category
		    $coll = new Opus_Collection($collRole->getId());
		    $coll->setName($class['fakultaet']);
			// store the old ID with the new Collection
			$subcoll[$class['nr']] = $coll->store();
			$collRole->addSubCollection($coll);
			$collRole->store();
		}

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

        foreach ($classification as $class) {
            echo ".";
            $parentColl = new Opus_Collection($roleId, $subColls[$class['fakultaet']]);
            // second level category
            $coll = new Opus_Collection($roleId);
            $coll->setName($class['name']);
            $coll->store();
            $parentColl->addSubCollection($coll);
            $parentColl->store();
        }
    }
}