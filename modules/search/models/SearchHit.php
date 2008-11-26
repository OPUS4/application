<?php
/**
 * Structure of search hits in Module_Search
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
 * @category    Search
 * @package     Module_Search
 * @author      Oliver Marahrens (o.marahrens@tu-harburg.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * class SearchHit
 */
class SearchHit 
{

  /**
   * Document of the search hit matching the query
   * @access private
   */
  private $document;

  /**
   * File of the search hit matching the query
   * @access private
   */
  private $files;

  /**
   * Relevance of the search hit - get it from the search engine framework
   * @access private
   */
  private $relevance;

  /**
   * Type of the Search hit - does the search term match the fulltext or metadata?
   * @access private
   */
  private $type;

  /**
   * Constructor
   * @access public
   * @param Integer id ID of the document for this search hit - if not given or invalid, the Search hit wont have a document
   */
  public function __construct($id = null) {
  	if ($id !== null) $this->getDocument($id);
  	else $this->document = null;
  } // end of Constructor

  /**
   * Get the document as a OpusDocumentAdapter by its ID
   * @return OpusDocumentAdapter
   * @param Integer id ID of the document
   * @access private
   */
  private function getDocument($id) {
    $this->document = new OpusDocumentAdapter($id);
    return $this->document;
  } // end of member function getDocument

  /**
   * Get the OpusDocumentAdapter from this search hit
   * @return OpusDocumentAdapter
   * @access public
   */
  public function getSearchHit() {
    return $this->document;
  } // end of member function getDocument

}