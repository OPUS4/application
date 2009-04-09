<?php
/**
 * collection of static mathods to get different browsing lists
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

class BrowsingList
{
	/**
	 * Get a list of all authors from the repository
	 *
	 * @return PersonsList list of authors, unsorted (call method sort() on it in order to sort it)
	 * @static
	 */
	public static function getPersonsList()
	{
        $browsinglist = Opus_Person::getAll();
		$personsList = new Opus_Search_List_PersonsList();
		$done = array();
		foreach ($browsinglist as $member)
		{
			if (false === array_key_exists($member->getLastName(), $done))
			{
			    $pers = new Opus_Search_Adapter_PersonAdapter(array('id' => $member->getId(), 'firstName' => $member->getFirstName(), 'lastName' => $member->getLastName()));
			    $personsList->add($pers);
			    $done[$member->getLastName()] = $member->getFirstName();
			}
			else if ($done[$member->getLastName()] !== $member->getFirstName())
			{
			    $pers = new Opus_Search_Adapter_PersonAdapter(array('id' => $member->getId(), 'firstName' => $member->getFirstName(), 'lastName' => $member->getLastName()));
			    $personsList->add($pers);
			    $done[$member->getLastName()] = $member->getFirstName();
			}
		}
		return $personsList;
	}

	/**
	 * Get a list of all documentTypes from the repository
	 *
	 * @return DocumentTypeList list of documenttypes, unsorted (call method sort() on it in order to sort it)
	 * @static
	 */
	public static function getDocumentTypeList()
	{
        // TODO Do not use a hardcoded path to xml files
        $xml_path = '../config/xmldoctypes/';
        $result = array();
        if ($dirhandle = opendir($xml_path)) {
            while (false !== ($file = readdir($dirhandle))) {
                $path_parts = pathinfo($file);
                $filename = $path_parts['filename'];
                $basename = $path_parts['basename'];
                $extension = $path_parts['extension'];
                if (($basename === '.') or ($basename === '..') or ($extension !== 'xml')) {
                    continue;
                }
                $result[$filename] = $filename;
            }
            closedir($dirhandle);
            asort($result);
        }
        return $result;
	}

	/**
	 * Get a list of all CollectionRoles from the repository
	 *
	 * @return CollectionNodeList of all CollectionRole-Titles, unsorted (call method sort() on it in order to sort it)
	 * @static
	 *
	 * @todo get the information from the real CollectionClass
	 */
	public static function getCollectionRoleList()
	{
		$browsinglist = Opus_CollectionRole::getAll();
		return $browsinglist;
	}

	/**
	 * Get a list of the addressed CollectionNode from the repository
	 *
	 * @return CollectionNode Including all content of this node
	 * @static
	 *
	 * @todo get the information from the real CollectionClass
	 */
	public static function getCollectionList($role, $node)
	{
		if ($node === 0) {
		    $browsinglist = new Opus_CollectionRole((int) $role);
		}
		else {
	       $browsinglist = new Opus_Collection((int) $role, (int) $node);
		}
		return $browsinglist;
	}
}
