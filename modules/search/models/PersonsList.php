<?php
/**
 * List of persons
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
 * class PersonsList
 * List of persons
 */
class PersonsList extends BasicList
{
   /*** Attributes: ***/

  /**
   * Number of persons in this list
   * @access private
   */
  private $numberOfPersons;

  /**
   * Elements in this list
   * @access private
   */
  private $persons;

  /**
   * Constructor
   * @access public
   * @return void
   */
  public function __construct() {
    $this->persons = array();
  } // end of Constructor

  /**
   * Add a person to the list
   * 
   * @access public
   * @param OpusPersonAdapter pers person that should be added to this list
   * @return void
   */
  public function add($pers) {
    array_push($this->persons, $pers);
  } // end of member function add 

  /**
   * Returns the number of persons in this list
   * 
   * @access public
   * @return integer number of persons in this list
   * @deprecated 17.11.2008 use count() instead
   */
  public function getNumberOfPersons() {
    $this->numberOfPersons = count($this->persons);
    return $this->numberOfPersons;
  } // end of member function getNumberOfPersons 

  /**
   * Gets the number of persons in this list
   * 
   * @access public
   * @return integer number of persons in this list
   */
  public function count() {
    return $this->getNumberOfPersons();
  } // end of member function count

  /**
   * Deletes a person from the list
   * 
   * @return void
   * @param OpusPersonAdapter|Integer item element (or index of element) that should be removed from the list
   * @access public
   * 
   * @todo implement method
   */
  public function delete($item) {
    
  } // end of member function delete

  /**
   * Gets an element from the list by its index
   * @return OpusPersonAdapter
   * @param Integer index index number of the element
   * @access public
   */
  public function get($index) {
    return $this->persons[$index];
  }  

  /**
   * Sorts the list
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
}