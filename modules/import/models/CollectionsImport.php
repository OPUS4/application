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
class CollectionsImport
{
	/**
	 * Imports Collection data to Opus4
	 *
	 * @param Strring $data XML-String with classifications to be imported
	 * @return array List of documents that have been imported
	 */
	public function __construct($data)
	{
		// Add a CollectionRole for Collections
        $collRole = new Opus_CollectionRole();
        $collRole->setName('Collections');
        $collRole->setOaiName('colls');
        $collRole->setVisible(1);
        $collRole->setLinkDocsPathToRoot('count');
        $collRole->store();
		// Add a CollectionRole for Series (Schriftenreihen)
        $seriesRole = new Opus_CollectionRole();
        $seriesRole->setName('Schriftenreihen');
        $seriesRole->setOaiName('series');
        $seriesRole->setVisible(1);
        $seriesRole->setLinkDocsPathToRoot('count');
        $roleId = $seriesRole->store();
		
		$doclist = $data->getElementsByTagName('table_data');
		foreach ($doclist as $document) 
		{
            if ($document->getAttribute('name') === 'collections') {
                #$facNumbers = $this->importCollections($document, $collRole);
                $facNumbers = $this->importCollectionsDirectly($document, $collRole);
	/*<row>
		<field name="coll_id">1</field>
		<field name="root_id">1</field>
		<field name="coll_name">TUHH Spektrum Specials</field>
		<field name="lft">1</field>
		<field name="rgt">2</field>
	</row>*/
            }
            if ($document->getAttribute('name') === 'schriftenreihen') {
                $instNumbers = $this->importSeries($document, $seriesRole);
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
	 * Imports Collections from Opus3 to Opus4 directly (from DB-table to DB-tables)
	 *
	 * @param DOMDocument $data XML-Document to be imported
	 * @return array List of documents that have been imported
	 */
	protected function importCollectionsDirectly($data, $collRole)
	{
        $collections = $this->transferOpusClassification($data);
        $contentTable = new Opus_Db_CollectionsContents($collRole->getId());
        $structTable = new Opus_Db_CollectionsStructure($collRole->getId());

        // 1 is used as a predefined key and should not be used again!
        // so lets increment all old IDs by 1
        foreach ($collections as $row) {
            $contentData = array(
                'id'      => ($row['coll_id']+1),
                'name'    => $row['coll_name']
            );

            $contentTable->insert($contentData);
                        
            $structureData = array(
                'collections_id' => ($row['coll_id']+1),
                'left'           => ($row['lft']+1),
                'right'          => ($row['rgt']+1)
            );
            
            $structTable->insert($structureData);

            if ($row['coll_id'] === "1") {
            	// set right-value for ID 1
            	$newRight = array(
                    'right'      => ($row['rgt']+2)
                );

                $where = $structTable->getAdapter()->quoteInto('id = ?', 1);

                $structTable->update($newRight, $where);
            }
        }
	}
	
	/**
	 * Imports Collections from Opus3 to Opus4 directly (without XML)
	 *
	 * @param DOMDocument $data XML-Document to be imported
	 * @return array List of documents that have been imported
	 */
	protected function importCollections($data, $collRole)
	{
        $collections = $this->transferOpusClassification($data);
        
        // sort collections array by left-value
        foreach ($collections as $key => $row) {
            $colls[$key]    = $row['lft'];
        }

        sort($colls);
        
		$subcoll = array();

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/collections.map', 'w');

		foreach ($colls as $key => $c) {
		    $class = $collections[$key];
		    // check if this is first level Collection
		    // its first level, if coll_id = root_id
		    if ($class['coll_id'] === $class['root_id']) {
		        echo ".";
		        $coll = new Opus_Collection(null, $collRole->getId());
    		    $coll->setName($class['coll_name']);
	    	    $coll->setTheme('default');
	    	    $newCollId = $coll->store();
		    	fputs($fp, $class['coll_id'] . ' ' . $newCollId . "\n");
		    	$subcoll[$class['coll_id']] = $newCollId;
			    $collRole->addSubCollection($coll);
			    $collRole->store();
		    }
		}
		reset($colls);
		foreach ($colls as $key => $c) {
		    $class = $collections[$key];
		    // check if this is first level Collection
		    // its first level, if coll_id = root_id
		    if ($class['coll_id'] !== $class['root_id']) {
		    	// first level elements are already inside, so lets proceed with next level
		        echo ".";
		        // Warning: every Collection is now imported as a direct subcollection of the first level
		        // This is not necessarily true
		        // TODO: import real hierarchy
		        $parentCollId = $subcoll[$class['root_id']];
		        $parentColl = new Opus_Collection($parentCollId, $collRole->getId());
		        $coll = new Opus_Collection(null, $collRole->getId());
    		    $coll->setName($class['coll_name']);
	    	    $coll->setTheme('default');
	    	    $newCollId = $coll->store();
			   	fputs($fp, $class['coll_id'] . ' ' . $newCollId . "\n");
		    	$subcoll[$class['coll_id']] = $newCollId;
			    $parentColl->addSubCollection($coll);
			    $parentColl->store();
		    }
		}		
        echo "\n";
		fclose($fp);
	}

	/**
	 * Imports Series from Opus3 to Opus4 directly (without XML)
	 *
	 * @param DOMDocument $data XML-Document to be imported
	 * @return array List of documents that have been imported
	 */
	protected function importSeries($data, $collRole)
	{
        $classification = $this->transferOpusClassification($data);

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/series.map', 'w');

		foreach ($classification as $class) {
          	echo ".";
		    // first level category
		    $coll = new Opus_Collection(null, $collRole->getId());
		    $coll->setName($class['name']);
		    $coll->setTheme('default');
			fputs($fp, $class['sr_id'] . ' ' . $coll->store() . "\n");
			$collRole->addSubCollection($coll);
			$collRole->store();
		}
         echo "\n";
		 fclose($fp);
	}
}
