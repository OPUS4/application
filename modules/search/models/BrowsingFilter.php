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
        $titles = Opus_Document::getAllIds();

        $paginator = Zend_Paginator::factory($titles);

        $hitlist = new Opus_Search_List_HitList();

        foreach ($titles as $title=>$docid)
        {
       		$searchhit = new Opus_Search_SearchHit( (int) $docid);
       		$hitlist->add($searchhit);
        }

        return ($hitlist);
	}

    /**
     * Returns a paginator of all entries from the repository
     *
     * @return Zend_Paginator resultlist
     * @static
     */
    public static function getAllTitlesAsPaginator()
    {
        $titles = Opus_Document::getAllIds();

        $paginator = Zend_Paginator::factory($titles);

        return ($paginator);
    }

    /**
     * Returns a list of all entries from a given author
     *
     * @return HitList resultlist
     * @static
     * @param Integer authorId Id of the author from the Opus database
     */
    public static function getPersonTitles($personId, $role)
    {
        $person = new Opus_Person( (int) $personId);
        $docresult = $person->getDocumentsByRole($role);

        $hitlist = new Opus_Search_List_HitList();
        $searchhits = array();
        foreach ($docresult as $row)
        {
            array_push($searchhits, (int) $row->getId());
        }

        $paginator = Zend_Paginator::factory($searchhits);
        return ($paginator);


    }

    /**
     * Returns the number of documents connected with the given person
     *
     * @param Integer authorId Id of the author from the Opus database
     * @return integer number of documents
     */
    public static function hasTitles($personId, $role)
    {
        $person = new Opus_Person( (int) $personId);
        $docresult = $person->getDocumentsByRole($role);

        return (count($docresult));
    }

    /**
	 * Returns a list of all entries from a given author
	 *
	 * @return HitList resultlist
	 * @static
	 * @param Integer authorId Id of the author from the Opus database
	 */
	public static function getAuthorTitles($authorId)
	{
        $person = new Opus_Person( (int) $authorId);
        $docresult = $person->getDocumentsByRole('author');

        $hitlist = new Opus_Search_List_HitList();
        $searchhits = array();
        foreach ($docresult as $row)
        {
       		array_push($searchhits, (int) $row->getId());
        }

        $paginator = Zend_Paginator::factory($searchhits);
        return ($paginator);


	}

    /**
     * Returns a list of all entries from a given author
     *
     * @return HitList resultlist
     * @static
     * @param Integer authorId Id of the author from the Opus database
     */
    public static function getEditorTitles($editorId)
    {
        $person = new Opus_Person( (int) $editorId);
        $docresult = $person->getDocumentsByRole('editor');

        $hitlist = new Opus_Search_List_HitList();
        $searchhits = array();
        foreach ($docresult as $row)
        {
            array_push($searchhits, (int) $row->getId());
        }

        $paginator = Zend_Paginator::factory($searchhits);
        return ($paginator);


    }

	/**
	 * Returns a list of all entries from a given Documenttype
	 *
	 * @return HitList resultlist
	 * @static
	 * @param Integer|String doctype name or Id of the doctype that should be presented
	 *
	 * @todo Put it to the model class
	 */
	public static function getDocumentTypeTitles($doctype)
	{
        #$doctype = str_replace("_", " ", $doctype);
        $table = new Opus_Db_Documents();
        $select = $table->select()
            ->from($table)
            ->where('type = ?', $doctype);
        $rows = $table->fetchAll($select);

        $result = array();
        foreach ($rows as $row) {
            $result[] = $row->id;
        }
        return $result;
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
        $table = new Opus_Db_Documents();
        $select = $table->select()
            ->from($table)
            ->where('completed_year = ?', $year);
        $rows = $table->fetchAll($select);

        $result = array();
        foreach ($rows as $row) {
            $result[] = $row->id;
        }
        return $result;
	}
}
