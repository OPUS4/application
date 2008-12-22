<?php
/**
 * Collection node
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

/**
 * class CollectionNode
 * includes a list of documents from this Node
 */
class CollectionNode extends BasicList
{
   /*** Attributes: ***/

  /**
   * Number of hits in this list
   * @access pivate
   */
  private $numberOfDocuments;

  /**
   * Documents belonging to this node
   * @access private
   */
  private $documents;

  /**
   * Name of this node
   * @access private
   */
  private $name;

  /**
   * Role-ID of the CollectionRole of this Node
   * @access private
   */
  private $roleId;

  /**
   * Collection-ID of the CollectionNode
   * @access private
   */
  private $collectionId;

  /**
   * Constructor
   * @access public
   * @return void
   */
  public function __construct($coll = null, $collnode = null) {
  		$this->documents = array();
  		#$this->name = array();
  		$this->roleId = $coll;
  		$this->collectionId = $collnode;
  		if (is_array($coll)) {
			$this->name = $coll["name"];
			$this->roleId = (int) $coll["collections_roles_id"];
  		}
  		if (is_array($collnode)) {
			$this->name = $collnode[0]["name"];
			$this->collectionId = (int) $collnode[0]["collections_id"];
  		}
  		$this->getDocuments();
  } // end of Constructor

  /**
   * Add a Document to this node
   * 
   * @access public
   * @param OpusDocumentAdapter doc Document in this node
   * @return void
   */
  public function add($doc) {
    array_push($this->documents, $doc);
  } // end of member function add 

  /**
   * Returns the number of documents in this node
   * 
   * @access public
   * @return integer number of hits in this list
   */
  public function count() {
  	$this->numberOfDocuments = count($this->documents);
    return $this->numberOfDocuments;
  } // end of member function count

  /**
   * Deletes a Search hit from the list
   * 
   * @return void
   * @param OpusDocumentAdapter|Integer item element (or index of element) that should be removed from the list
   * @access public
   * 
   * @todo implement method
   */
  public function delete($item) {
    
  } // end of member function delete

  /**
   * Gets an element from the list by its index
   * @return SearchHit
   * @param Integer index index number of the element
   * @access public
   */
  public function get($index) {
    return $this->documents[$index];
  }  

  /**
   * Sorts the list
   * @abstract
   * @access public
   * @return void
   * @param String sortCriteria criteria the list should be sorted with
   * Possible sort criteria are:
   * not defined yet
   * 
   * @todo implement method
   */
  public function sort($criteria) {
    
  }  

  /**
   * Gets the name of this node by its language
   * @return String if the language does not exist, null will be returned
   * @param String language desired language of the element, if null or not given the language will be detected using Zend_Locale
   * @access public
   * 
   * @todo if language does not exist, return default language from element!
   */
  public function getName($language = null) {
  	#if ($language === null) 
  	#{
     #   $translate = Zend_Registry::get('Zend_Translate');
  		#$lang = $translate->getLocale();
		// get the correct language from the database...
  		#switch($lang)
  		#{
	  	#	case "de_DE":
  		#		$language = "ger";
  		#		break;
  		#	default:
	  	#		$language = "eng";
  		#		break;
  		#}
  	#}
  	#if (array_key_exists($language, $this->name)) return $this->name[$language];
  	return $this->name;
  }  

  /**
   * Gets the ID of the CollectionRole containing this Node
   * @return Integer RoleId
   * @access public
   */
  public function getRoleId() {
  	return $this->roleId;
  }  

  /**
   * Gets the ID of the CollectionNode
   * @return Integer CollectionId
   * @access public
   */
  public function getNodeId() {
  	return $this->collectionId;
  }  

  /**
   * Gets the SubNodes ID of this CollectionNode
   * @return Integer CollectionId
   * @access public
   * 
   * @todo get the subnodes not only from Dummydata, but from real
   */
  public function getSubNodes() {
  		$nodeData = Opus_Collection_Information::getSubCollections($this->roleId, $this->collectionId);
  		#$nodeData = DummyData::getDummyCollectionNode();
  		$doctypeList = new CollectionNodeList();
		foreach ($nodeData as $member)
		{
			$node = new CollectionNode($this->roleId, $member["content"]);
			# Später: Nicht mehr $member uebergeben, sondern anhand der role_id die Collection aus der DB auslesen
			#$node->getCollectionNode($this->roleId, $nodeData["collection_id"]);
			$doctypeList->add($node);
		}
  	return $doctypeList;
  }  

  /**
   * Builds the CollectionNode-Object mapping the information from Opus_Collection
   * @return void
   * @access public
   */
  public function getCollectionNode() {
  		if ($this->collectionId > 0) $nodeInfo = Opus_Collection_Information::getPathToRoot($this->roleId, $this->collectionId);
  		else $nodeInfo = null;
		return $nodeInfo;
  }  

  /**
   * Gets the documents from this Node from the database
   * @return void
   * @access public
   */
  public function getDocuments($alsoSubnodes = false) {
  		$docs = Opus_Collection_Information::getAllCollectionDocuments($this->roleId, $this->collectionId, $alsoSubnodes);
		unset ($this->documents);
		$this->documents = array();
		foreach ($docs as $member)
		{
			$doc = new Opus_Search_Adapter_DocumentAdapter( (int) $member);
			$this->add($doc);
		}
  		return $this->documents;
  }
}