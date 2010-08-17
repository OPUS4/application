<?php
/**
 * Central class to construct any browsing list
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

class Search_Model_BrowsingListFactory
{
  /**
   * Holds the browsing list produced in this factory
   * @access private
   */
	private $browsinglist;

  /**
   * Constructor
   * @access public
   * @param String browsingList Keyword of the browsingList that should be created in this factory
   * Possible keywords are:
   * authors	- list of all authors
   * doctypes	- list of all document types
   */
	public function __construct($browsingList, $role = null, $collection = null, $node = null)
	{
		$this->createBrowsingList($browsingList, $role, $collection, $node);
	}

  /**
   * Create the browsing list by keyword
   * @return void
   * @param String browsingList Keyword of the browsingList that should be created in this factory
   * @access private
   */
	private function createBrowsingList($browsingList, $role, $collection, $node)
	{
		switch ($browsingList)
		{
			case 'persons':
				$browseList = Search_Model_BrowsingList::getPersonsRoleList($role);
				break;
			case 'doctypes':
				$browseList = Search_Model_BrowsingList::getDocumentTypeList();
				break;
			case 'collectionRoles':
				$browseList = Search_Model_BrowsingList::getCollectionRoleList();
				break;
			case 'collection':
				$browseList = Search_Model_BrowsingList::getCollectionList($collection, $node);
				break;
			default:
				throw new Search_Model_BrowsingListFactoryException("This type of list is not supported (yet)!");
		}
		$this->browsingList = $browseList;
	}

  /**
   * Get the BrowsingList out of the factory
   * @return BasicList BrowsingList, extended from BasicList
   * @access public
   */
	public function getBrowsingList()
	{
		return $this->browsingList;
	}
}