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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: Opus3CollectionsImport.php -1   $
 */
class ZIBCollectionsImport {

    /**
     * Imports Collection data to Opus4
     *
     * @param Strring $data XML-String with classifications to be imported
     * @return array List of documents that have been imported
     */
    public function __construct($data) {


        /*
         * Serien sind flache Datenstrukturen. Unterhalb der Wurzel sind alle Serien auf zweiter Ebene angeordnet.
         * Collections sind hierarchische Datenstrukturen. Mehrere Ebenen sind möglich.
         * Daher werden zwei unterschiedliche Methoden für den Import verwendet
         */
        //$collRole = Opus_CollectionRole::fetchByName('collections');
        $seriesRole = Opus_CollectionRole::fetchByName('series');
       
        $doclist = $data->getElementsByTagName('table_data');
	foreach ($doclist as $document)	{
            if ($document->getAttribute('name') === 'collections') {
                //$this->importCollectionsDirectly($document, $seriesRole);
                $this->importCollections($document, $seriesRole);
            }
            if ($document->getAttribute('name') === 'schriftenreihen') {
                $this->importSeries($document, $seriesRole);
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
     * sort multidimensional arrays
     */
    private function msort($array, $id="id") {
        $temp_array = array();
        while(count($array)>0) {
            $lowest_id = 0;
            $index=0;
            foreach ($array as $item) {
                if (isset($item[$id]) && $array[$lowest_id][$id]) {
                    if ($item[$id]<$array[$lowest_id][$id]) {
                        $lowest_id = $index;
                    }
                }
                $index++;
            }
            $temp_array[] = $array[$lowest_id];
            $array = array_merge(array_slice($array, 0,$lowest_id), array_slice($array, $lowest_id+1));
        }
        return $temp_array;
    }

    /**
     * Imports Collections from Opus3 to Opus4 directly (from DB-table to DB-tables)
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importCollectionsDirectly($data, $collRole)  {
        $collections = $this->transferOpusClassification($data);
        
        // sort by lft-values
        $sorted_collections = $this->msort($collections, 'lft');

        if (count($sorted_collections) == 0) {
            // TODO: Improve error handling in case of empty collections.
            throw new Exception("ERROR: Sorted collections empty.");
        }

        if ($sorted_collections[0]['lft'] != 1) {
            var_dump($sorted_collections[0]);
            // TODO: Improve error handling in case of wrong left-ids.
            throw new Exception("ERROR: First left_id is not 1.");
        }

        // 1 is used as a predefined key and should not be used again!
        // so lets increment all old IDs by 1
        $previousRight = null;
        $previousNode = array();
        $new_collection = null;

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/collections.map', 'w');

        foreach ($sorted_collections as $row) {

            echo ".";
            // parent_id is needed
            if ($previousRight === null) {

                $root = $collRole->getRootCollection();
                $new_collection = $root->addLastChild();
//                $collRole->store();

                array_push($previousNode, $new_collection);
            }
            else if ( (int) $row['lft'] === (int) $previousRight+1) {
            	// its a brother of previous node
            	$left_brother = array_pop($previousNode);
                $new_collection = $left_brother->addNextSibling();
                $left_brother->store();
                
                array_push($previousNode, $new_collection);
            }
            else if ($row['rgt'] < $previousRight) {
            	// its a child of previous node
            	$father = array_pop($previousNode);
                $new_collection = $father->addLastChild();
                $father->store();

            	array_push($previousNode, $father);
            	array_push($previousNode, $new_collection);
            }
            else {
                throw new Exception("Should never happen, ({$row['rgt']} < $previousRight) failed");
            }

            if ($previousRight === null) { continue;}
            
            $new_collection->setVisible(1);
            $new_collection->setName($row['coll_name']);
            $new_collection->store();
            $previousRight = $row['rgt'];
            fputs($fp, $row['coll_id'] . ' ' . $new_collection->getId() . "\n");
        }
        echo "\n";
	fclose($fp);

    }


    /**
     * Imports Series from Opus3 to Opus4
     *      
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importSeries($data, $role) {
        $classification = $this->transferOpusClassification($data);

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/series.map', 'w');
            foreach ($classification as $class) {
                echo ".";
                $root = $role->getRootCollection();
                $coll = $root->addLastChild();
                $coll->setVisible(1);
                $coll->setName($class['name']);
                $root->store();
                fputs($fp, $class['sr_id'] . ' ' . $coll->getId() . "\n");
            }
        echo "\n";
	fclose($fp);
    }

    /**
     * Imports Collections from Opus3 to Opus4
     *
     * @param DOMDocument $data XML-Document to be imported
     * @return array List of documents that have been imported
     */
    protected function importCollections($data, $role) {
        $classification = $this->transferOpusClassification($data);

        // Build a mapping file to associate old IDs with the new ones
        $fp = fopen('../workspace/tmp/collections.map', 'w');
            foreach ($classification as $class) {
                echo ".";
                $root = $role->getRootCollection();
                $coll = $root->addLastChild();
                $coll->setVisible(1);
                $coll->setName($class['coll_name']);
                $root->store();
                fputs($fp, $class['coll_id'] . ' ' . $coll->getId() . "\n");
            }
        echo "\n";
	fclose($fp);
    }

}
