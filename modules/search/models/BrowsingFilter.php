<?php
/**
 * Collection of static methods to get lists of titles for some filter criteria
 * 
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
 * @package     Module_Search
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class BrowsingFilter
{
	/**
	 * Returns a list of all entries from the repository
	 * 
	 * @return HitList resultlist
	 * @static
	 */
	public static function getAllTitles()
	{
        $table = new Opus_Db_Documents();
        $docresult = $table->fetchAll();
        
        $hitlist = new HitList();
        foreach ($docresult as $row)
        {
       		$searchhit = new SearchHit((int) $row->__get("documents_id"));
       		$hitlist->add($searchhit);
        }
        
        return ($hitlist);		
	}

	/**
	 * Returns a list of all Dummy-entries
	 * 
	 * @return HitList resultlist
	 * @static
	 */
	public static function getAllDummyTitles()
	{
        $docresult = DummyData::getDummyDocuments();
        
        $hitlist = new HitList();
        foreach ($docresult as $row)
        {
       		$searchhit = new SearchHit($row);
       		$hitlist->add($searchhit);
        }
        
        return ($hitlist);		
	}
	
	/**
	 * Returns a list of all entries from a given author
	 * 
	 * @return HitList resultlist
	 * @static
	 * @param Integer authorId Id of the author from the Opus database
	 * 
	 * @todo really get documents from the given Authors, not just dummydata
	 */
	public static function getAuthorTitles($authorId)
	{
        $person = new Opus_Search_Adapter_PersonAdapter((int) $authorId);
        $docresult = $person->getDocumentsByRole("author");
        
        $hitlist = new HitList();
        foreach ($docresult as $row)
        {
       		$searchhit = new SearchHit((int) $row->getId());
       		$hitlist->add($searchhit);
        }
        
        return ($hitlist);		
	}

	/**
	 * Returns a list of all entries from a given Documenttype
	 * 
	 * @return HitList resultlist
	 * @static
	 * @param Integer|String doctype name or Id of the doctype that should be presented
	 *
	 * @todo really get documents from the given Documenttype, not just dummydata
	 */
	public static function getDocumentTypeTitles($doctype)
	{
        $docresult = DummyData::getDummyDocuments();
        
        $hitlist = new HitList();
        foreach ($docresult as $row)
        {
       		$searchhit = new SearchHit($row);
       		$hitlist->add($searchhit);
        }
        
        return ($hitlist);		
	}

	/**
	 * Returns a list of all entries from a given DDC-Class
	 * 
	 * @return HitList resultlist
	 * @static
	 * @param String ddcClass Class out of DDC classification which should be presented
	 * 
	 * @todo really get documents from the given DDC-class, not just dummydata
	 */
	public static function getDdcTitles($ddcClass)
	{		
        $docresult = DummyData::getDummyDocuments();
        
        $hitlist = new HitList();
        foreach ($docresult as $row)
        {
       		$searchhit = new SearchHit($row);
       		$hitlist->add($searchhit);
        }
        
        return ($hitlist);		
	}

	/**
	 * Returns a list of all entries from a given faculty
	 * 
	 * @return HitList resultlist
	 * @static
	 * @param Integer facultyId Id of the faculty or institute that should be presented
	 * 
	 * @todo really get documents from the given faculty or institute, not just dummydata
	 */
	public static function getFacultyTitles($facultyId)
	{
        $docresult = DummyData::getDummyDocuments();
        
        $hitlist = new HitList();
        foreach ($docresult as $row)
        {
       		$searchhit = new SearchHit($row);
       		$hitlist->add($searchhit);
        }
        
        return ($hitlist);		
	}

	/**
	 * Returns a list of all entries from a given class out of a given classification
	 * 
	 * @return HitList resultlist
	 * @static
	 * @param String classification Classification that should be used when trying to find the class entries
	 * @param String class class of the entries that should be presented
	 * 
	 * @todo really get documents from the given class and classification, not just dummydata 
	 */
	public static function getClassTitles($classification, $class)
	{
        $docresult = DummyData::getDummyDocuments();
        
        $hitlist = new HitList();
        foreach ($docresult as $row)
        {
       		$searchhit = new SearchHit($row);
       		$hitlist->add($searchhit);
        }
        
        return ($hitlist);		
	}

	/**
	 * Returns a list of all entries from a given collection
	 * 
	 * @return HitList resultlist
	 * @static
	 * @param Integer collectionId Id of the collection that should be presented
	 * 
	 * @todo really get documents from the given collection, not just dummydata
	 */
	public static function getCollectionTitles($collectionId)
	{
        $docresult = DummyData::getDummyDocuments();
        
        $hitlist = new HitList();
        foreach ($docresult as $row)
        {
       		$searchhit = new SearchHit($row);
       		$hitlist->add($searchhit);
        }
        
        return ($hitlist);		
	}
	
	/**
	 * Returns a list of all entries from the repository, which are published in a given year
	 * 
	 * @return HitList resultlist
	 * @static
	 * @param String year Year of the publications that should be listed
	 * 
	 * @todo really get documents from the given year, not just dummydata
	 */
	public static function getYearTitles($year)
	{
        $docresult = DummyData::getDummyDocuments();
        
        $hitlist = new HitList();
        foreach ($docresult as $row)
        {
       		$searchhit = new SearchHit($row);
       		$hitlist->add($searchhit);
        }
        
        return ($hitlist);		
	}
}
