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
class Import_Model_CollectionsImport {

    /**
     * Imports Collection data to Opus4
     *
     * @param Strring $data XML-String with classifications to be imported
     * @return array List of documents that have been imported
     */
    public function __construct($data) {

        $collRole = Opus_CollectionRole::fetchByName('series');
        $seriesRole = Opus_CollectionRole::fetchByName('series');
       
        $doclist = $data->getElementsByTagName('table_data');
	foreach ($doclist as $document)	{
            if ($document->getAttribute('name') === 'collections') {
                //$facNumbers = $this->importCollectionsDirectly($document, $collRole);
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
        $contentTable = new Opus_Db_Collections();
        $structTable = new Opus_Db_CollectionsNodes();
        
        // sort by lft-values
        $sorted_collections = $this->msort($collections, 'lft');

        // 1 is used as a predefined key and should not be used again!
        // so lets increment all old IDs by 1
        $previousRight = null;
        $previousLeft = null;
        $previousId = array();
        foreach ($sorted_collections as $row) {
            $contentData = array(
           //     'oldid'   => ($row['coll_id']+1),
                'role_id' => $collRole->getId(),
                'name'    => $row['coll_name']
            );

            $collId = $contentTable->insert($contentData);
            
            // parent_id is needed
            if ($previousLeft === null && $previousRight === null) {
            	// no parent, use RootNode
            	$parentNodeId = $collRole->getRootNode()->getId();
            	array_push($previousId, $collId);
            }
            else if ( (int) $row['lft'] === (int) $previousRight+1) {
            	// its a brother of previous node
            	array_pop($previousId);
            	$parentNodeId = $previousId[count($previousId)-1];
            	array_push($previousId, $collId);
            }
            else {
            	// its a child of previous node
            	$parentNodeId = $previousId[count($previousId)-1];
            	array_push($previousId, $collId);
            }
            
            
            $structureData = array(
                'role_id'        => $collRole->getId(),
                'collection_id'  => $collId,
                'left_id'        => ($row['lft']+1),
                'right_id'       => ($row['rgt']+1),
                'parent_id'      => $parentNodeId,
                'visible'        => 1
            );
            
            $structTable->insert($structureData);

            if ($row['coll_id'] === "1") {
            	// set right-value for ID 1
            	$newRight = array(
                    'right_id'      => ($row['rgt']+2),
                    'visible'       => 1
                );

                $where = $structTable->getAdapter()->quoteInto('left_id = 1 AND role_id = ?', $collRole->getId());

                $structTable->update($newRight, $where);
            }
            $previousLeft = $row['lft'];
            $previousRight = $row['rgt'];
        }
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
}
