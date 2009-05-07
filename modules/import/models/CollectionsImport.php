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
		// Analyse the data to find out which classification systems there are
		// and which converter methods should be used
		$doclist = $data->getElementsByTagName('table_data');
		foreach ($doclist as $document) 
		{
			$tempdoc = new DOMDocument;
            $tempdoc->loadXML($data->saveXML($document));
            $tablename = $tempdoc->getElementsByTagName('table_data')->Item(0)->getAttribute('name');
            if (strtolower(substr($tablename, 0, 2)) === 'bk') {
            	// Works!
            	echo "Importing Bk...";
            	// Importing via XML
            	#if (false === file_exists('../workspace/tmp/bk.xml'))
            	#{
            	    #$bkPrepare = $this->convertBk($tempdoc, $tablename);
            	    #$bk = fopen('../workspace/tmp/bk.xml', 'w');
            	    #fputs($bk, $bkPrepare->saveXml());
            	    #fclose($bk);
            	#}
            	#else {
            	#	$bkRead = file('../workspace/tmp/bk.xml');
            	#	$bkPrepare = implode("", $bkRead);
            	#}
            	#$importit = Opus_CollectionRole::fromXml($bkPrepare);
            	$this->importBk($tempdoc, $tablename);
            	echo "done!\n";
            	// store classification system
            }
            if (strtolower(substr($tablename, 0, 3)) === 'apa') {
            	// Should work, but untested
            	echo "Importing APA...";
             	#$apaPrepare = $this->convertApa($tempdoc, $tablename);
             	echo "done!\n";
            	// store classification system
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
			$tempdoc = new DOMDocument;
            $tempdoc->loadXML($data->saveXML($document));
            foreach ($tempdoc->getElementsByTagName('field') as $field) {
           		$classification[$index][$field->getAttribute('name')] = $field->nodeValue;
            }
            $index++;
		}
		return $classification;
	}

	/**
	 * Imports Bk-classification to Opus4 directly (without XML)
	 *
	 * @param DOMDocument $data XML-Document to be imported
	 * @return array List of documents that have been imported
	 */
	protected function importBk($data, $classificationName)
	{
		$classification = $this->transferOpusClassification($data);
		
		$collRole = new Opus_CollectionRole();
		$collRole->setName($classificationName);
		$collRoleId = $collRole->store();

		foreach ($classification as $class) {
            if (ereg("\.00$", $class['class'])) {
            	echo ".";
			    // first level category
			    $coll = new Opus_Collection($collRoleId);
			    $coll->setName($class['bez']);
			    #$coll->setNumber($class['class']);
			    $subcoll[$class['class']] = $coll;
			    $collRole->addSubCollection($coll);
            }
		}
		$collRole->store();
		foreach ($classification as $class) {
            if (ereg("0$", $class['class']) && false === ereg("\.00$", $class['class'])) {
            	$parent = false;
            	// second level category
       			echo ".";
       			if (true === array_key_exists(substr($class['class'], 0, 2).'.00', $subcoll)) {
       				$parent = $subcoll[substr($class['class'], 0, 2).'.00'];
       			}
            	if ($parent === false) {
            		// if parent still is empty, lets put it on top
            		$coll = new Opus_Collection($collRoleId);
			        $coll->setName($class['bez']);
			        #$coll->setNumber($class['class']);
			        $subcoll[$class['class']] = $coll;
			        $collRole->addSubCollection($coll);
            	}
            	else {
			        $coll = new Opus_Collection($collRoleId, $parent->getId());
			        $coll->setName($class['bez']);
			        #$coll->setNumber($class['class']);
			        $subcoll[$class['class']] = $coll;
			        $parent->addSubCollection($coll);
            	}
            }
		}
		$collRole->store();
		foreach ($classification as $class) {
            if (false === ereg("0$", $class['class'])) {
            	$parent = false;
            	// third level category
           		if (true === array_key_exists(substr($class['class'], 0, 4).'0', $subcoll)) {
           			echo ".";
           			$parent = $subcoll[substr($class['class'], 0, 4).'0'];
           		}
            	if ($parent === false) {
            	    // no parent found, try one level higher
            		if (true === array_key_exists(substr($class['class'], 0, 2).'.00', $subcoll)) {
           			    echo ".";
           			    $parent = $subcoll[substr($class['class'], 0, 2).'.00'];
            	    }            		
            	}
            	if ($parent === false) {
            		// if parent still is empty, lets put it on top
            		$coll = new Opus_Collection($collRoleId);
			        $coll->setName($class['bez']);
			        #$coll->setNumber($class['class']);
			        $subcoll[$class['class']] = $coll;
			        $collRole->addSubCollection($coll);
            	}
            	else {
			        $coll = new Opus_Collection($collRoleId, $parent->getId());
			        $coll->setName($class['bez']);
			        #$coll->setNumber($class['class']);
			        $subcoll[$class['class']] = $coll;
			        $parent->addSubCollection($coll);
            	}
            }
		}
		$collRole->store();
	}

	/**
	 * Converts Bk-classification to Opus4
	 *
	 * @param DOMDocument $data XML-Document to be imported
	 * @return array List of documents that have been imported
	 */
	protected function convertBk($data, $classificationName)
	{
		$classification = $this->transferOpusClassification($data);
		
		$classificationDomDocument = new DOMDocument;
		$rootNode = $classificationDomDocument->createElement('Opus_CollectionRole');
		$rootNode->setAttribute('Name', $classificationName);
	    $classificationDomDocument->appendChild($rootNode);

		foreach ($classification as $key => $class) {
            if (ereg("\.00$", $class['class'])) {
            	echo ".";
			    // first level category
			    $node = $classificationDomDocument->createElement('Collection');
			    $node->setAttribute('Name', $class['bez']);
			    $node->setAttribute('Number', $class['class']);
			    $rootNode->appendChild($node);
			    // Reduce the array to improve performance for the next iterations
			    #array_splice($classification, $key, 1);
            }
		}
		foreach ($classification as $key => $class) {
            if (ereg("0$", $class['class']) && false === ereg("\.00$", $class['class'])) {
            	$parent = false;
            	// second level category
            	foreach ($classificationDomDocument->getElementsByTagName('Collection') as $coll) {
            		if ($coll->getAttribute('Number') === substr($class['class'], 0, 2).'.00') {
            			echo ".";
            			$parent = $coll;
            		}
            	}
	            $node = $classificationDomDocument->createElement('Collection');
			    $node->setAttribute('Name', $class['bez']);
			    $node->setAttribute('Number', $class['class']);
			    if ($parent !== false)
			    {
			        $parent->appendChild($node);
			    }
			    else
			    {
			    	// if there is no parent, put elements of the second level directly under the root node
			    	$rootNode->appendChild($node);
			    }
			    // Reduce the array to improve performance for the next iterations
			    #array_splice($classification, $key, 1);
            }
		}
		foreach ($classification as $class) {
            if (false === ereg("0$", $class['class'])) {
            	$parent = false;
            	// third level category
            	foreach ($classificationDomDocument->getElementsByTagName('Collection') as $coll) {
            		if ($coll->getAttribute('Number') === substr($class['class'], 0, 4).'0') {
            			echo ".";
            			$parent = $coll;
            		}
            	}
            	if ($parent === false) {
            	    // no parent found, try one level higher
            	    foreach ($classificationDomDocument->getElementsByTagName('Collection') as $coll) {
            		    if ($coll->getAttribute('Number') === substr($class['class'], 0, 2).'.00') {
            			    echo ".";
            			    $parent = $coll;
            		    }
            	    }            		
            	}
		        $node = $classificationDomDocument->createElement('Collection');
		        $node->setAttribute('Name', $class['bez']);
		        $node->setAttribute('Number', $class['class']);
			    if ($parent !== false)
			    {
			        $parent->appendChild($node);
			    }
			    else
			    {
			    	// if there is no parent, put elements of the second level directly under the root node
			    	$rootNode->appendChild($node);
			    }
            }
		}
		
		#echo $classificationDomDocument->saveXml();
		return $classificationDomDocument;
	}

	/**
	 * Converts Apa classification to Opus4
	 *
	 * @param DOMDocument $data XML-Document to be imported
	 * @return array List of documents that have been imported
	 */
	protected function convertApa($data, $classificationName)
	{
		$classification = $this->transferOpusClassification($data);
		
		$classificationDomDocument = new DOMDocument;
		$rootNode = $classificationDomDocument->createElement('CollectionRole');
		$rootNode->setAttribute('name', $classificationName);
	    $classificationDomDocument->appendChild($rootNode);

		foreach ($classification as $key => $class) {
            if (ereg("00$", $class['class'])) {
			    echo ".";
			    // first level category
			    $node = $classificationDomDocument->createElement('Collection');
			    $node->setAttribute('name', $class['bez']);
			    $node->setAttribute('class', $class['class']);
			    $rootNode->appendChild($node);
			    // Reduce the array to improve performance for the next iterations
			    #array_splice($classification, $key, 1);
            }
		}
		foreach ($classification as $key => $class) {
            if (ereg("0$", $class['class']) && false === ereg("00$", $class['class'])) {
            	$parent = false;
            	// second level category
            	foreach ($classificationDomDocument->getElementsByTagName('Collection') as $coll) {
            		if ($coll->getAttribute('class') === substr($class['class'], 0, 2).'00') {
            			echo ".";
            			$parent = $coll;
            		}
            	}
	            $node = $classificationDomDocument->createElement('Collection');
			    $node->setAttribute('name', $class['bez']);
			    $node->setAttribute('class', $class['class']);
			    if ($parent !== false)
			    {
			        $parent->appendChild($node);
			    }
			    else
			    {
			    	// if there is no parent, put elements of the second level directly under the root node
			    	$rootNode->appendChild($node);
			    }
			    // Reduce the array to improve performance for the next iterations
			    #array_splice($classification, $key, 1);
            }
		}
		foreach ($classification as $class) {
            if (false === ereg("0$", $class['class'])) {
            	$parent = false;
            	// third level category
            	foreach ($classificationDomDocument->getElementsByTagName('Collection') as $coll) {
            		if ($coll->getAttribute('class') === substr($class['class'], 0, 3).'0') {
            			echo ".";
            			$parent = $coll;
            		}
            	}
            	if ($parent === false) {
            	    // no parent found, try one level higher
            	    foreach ($classificationDomDocument->getElementsByTagName('Collection') as $coll) {
            		    if (substr($coll->getAttribute('class'), 0, 2) === substr($class['class'], 0, 2) && ereg("00$", $coll->getAttribute('class'))) {
            			    echo ".";
            			    $parent = $coll;
            		    }
            	    }
            	}
		        $node = $classificationDomDocument->createElement('Collection');
		        $node->setAttribute('name', $class['bez']);
		        $node->setAttribute('class', $class['class']);
			    if ($parent !== false)
			    {
			        $parent->appendChild($node);
			    }
			    else
			    {
			    	// if there is no parent, put elements of the second level directly under the root node
			    	$rootNode->appendChild($node);
			    }
            }
		}
		
		#echo $classificationDomDocument->saveXml();
		return $classificationDomDocument;
	}
}