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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class MappingFile
{

    /**
     * Holds the collections predifined in OPUS
     *
     * @var array  Defaults to the OPUS collections (Name => ID).
     */    
    protected static $collectionShortNames = array('Dewey Decimal Classification' => 'ddc', 'Computing Classification System' => 'ccs', 'Physics and Astronomy Classification Scheme' => 'pacs', 'Journal of Economic Literature (JEL) Classification System' => 'jel');

    /**
     * Get the short name of the given collections
     * 
     * @return string short name of the classification; if the classification is not supported, null will be returned
     */
    public static function getShortName($longname) {
    	if (array_key_exists($longname, self::$collectionShortNames)) {
    	    return self::$collectionShortNames[$longname];
    	}
    	return null;
    }

    /**
     * Do some initialization on startup of every action
     *
     * @param Opus_Collection|Opus_CollectionRole $role CollectionRole the Mapping file should be generated from
     * @return void
     */
    public function __construct($role)
    {
    	// Initialize the file
    	$fp = fopen('../workspace/tmp/'.self::$collectionShortNames[$role['name']].'Mapping.txt', 'w');
    	fclose($fp);
        $this->createMappingfile(array($role['id'], self::$collectionShortNames[$role['name']]));
    }
	
	/**
	 * creates a mapping file for a OPUS3 classification system to OPUS4
	 *
	 * @param array $classification  
	 * @return void
	 */
	protected function createMappingfile($classification, $coll = null)
	{
		if ($coll === null) $CollectionRole = new Opus_CollectionRole($classification[0]);
		else $CollectionRole = $coll;
		foreach ($CollectionRole->getSubCollection() as $Notation) {
			$this->writeNotation($Notation, $classification[1]);
			$this->createMappingfile($classification, $Notation);
		}
	}
	
	/**
	 * writes a line to the mapping file
	 * 
	 * @param Opus_Collection $notation Collection to get mapped
	 * @param string $classification short name of the classification this collection belongs to
	 * @return void 
	 */	
	protected function writeNotation($notation, $classification) {
	    if ($notation->getNumber() !== '') {
	        $fp = fopen('../workspace/tmp/'.$classification.'Mapping.txt', 'a');
	        fputs($fp, $notation->getNumber() . "\t" . $notation->getId() . "\n");
	        fclose($fp);
	    }
	}
}