<?php
/**
 * Implementation of PHP5 Iterator interface
 * Parent Class of all Iterators used in OPUS Module_Search
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
 * @category    Browsing
 * @package     Module_Search
 * @author      Oliver Marahrens (o.marahrens@tu-harburg.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id: ListIterator.php 1001 2008-11-25 16:03:19Z marahrens $
 */

/**
 * interface Iterator
 * already defined by PHP5
 */
/**
 * Reference-Implementation of Iterator for Opus-Lists
 */
class ListIterator implements Iterator
{
	
   /*** Attributes: ***/

  /**
   * @access private
   */
  protected $list;

  /**
   * @access private
   */
  protected $_currentIndex = 0;

   /*** Methods: ***/

  /**
   * Constructor
   * 
   * @param BasicList list List for this Iterator
   */
  public function __construct($list) {
    $this->list = $list;
  } // end of Constructor

  /**
   * moves internal counter to the beginning of the list
   * 
   * @return void
   */
  public function rewind() 
  {
  	$this->_currentIndex = 0;
  }

  /**
   * Checks if the list has more elements, returns false if current element is the last element of list (there are no more elements)
   * 
   * @return boolean last element of the list?
   */
  public function valid() 
  {
  	if (is_object($this->list) === false) return false;
    if ($this->_currentIndex > $this->list->count())
    {
    	throw new ListOutOfBoundsException("Listenüberlauf!");
    }
    if ($this->_currentIndex == $this->list->count())
    {
    	return false;
    }
    return true;
  }

  /**
   * Returns the current position in the hitlist
   * 
   * @return integer current position of internal counter
   */
  public function key() 
  {
  	return $this->_currentIndex;
  }

  /**
   * increments internal counter
   * 
   * @return void
   */ 
  public function next() {
    $this->_currentIndex++;
  }

  /**
   * checks if the list has a following element
   * 
   * @return boolean if true, there is another element, otherwise its false
   */ 
  public function hasNext() {
    if ($this->_currentIndex < $this->list->count()-1) return true;
    return false;
  }
  
  /**
   * Returns the list of this Iterator
   * needed to count the elements of the current list or to rebuild it (resort)
   * 
   * @return BasicList list this this Iterator is working with
   */
  public function getList()
  {
  	return $this->list;
  }
  
  /**
   * Definition of the method to get the current element from the list
   * needs to be implemented in all child classes
   * 
   * @return Object the currently selected object of the list 
   */
  public function current() { } // to be implemented in child classes
}