<?php
/**
 * Adapter to use the DocumentTypes from the framework in Module_Search
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
 * @category    Framework-Adapter
 * @package     Module_Search
 * @author      Oliver Marahrens (o.marahrens@tu-harburg.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class DocumentTypeAdapter
{
	/**
	 * Attribute to store the DocumentType as an Array
	 * @access private
	 */
	private $documentType;
	
  /**
   * Constructor
   * 
   * @param Integer|Array|DocumentTypeAdapter|Opus_Document_Type documentType data for the new DocumentTypeAdapter-Object 
   */
	public function __construct($documentType = null)
	{
  		if (is_int($documentType)) {
  			$this->documentType = $this->mapDocumentType(new Opus_Document_Type($documentType));
  		}
  		else if (is_array($documentType)) {
  			$this->documentType = $documentType;
  		}
  		elseif (get_class($documentType) === "DocumentTypeAdapter")
  		{
  			$this->documentType = $documentType->get();
  		}
  		elseif (get_class($documentType) === "Opus_Document_Type")
  		{
  			$this->documentType = $this->mapDocumentType($documentType);
  		}
	}
	
  /**
   * Returns the documenttype data as an array
   * 
   * @return Array Array with documenttype data usable in Module_Search 
   */
	public function get()
	{
		return $this->documentType;
	} 

  /**
   * Get a document type by its ID
   * 
   * @return DocumentTypeAdapter DocumentTypeAdapter of the Document type with the given ID, if this ID does not exists, null will be returned
   * @param Integer id ID of the document type
   */
	public static function getDocType($id)
	{
		# Später die Werte anhand der ID aus der DB holen lassen
		#$author = Opus_Person_Information::get($id);
		# Jetzt noch statisch Person aus den Testdaten holen
		$data = DummyData::getDummyDocumentTypes();
		foreach ($data as $obj)
		{
			$d = $obj->get();
			if ($d["id"] == $id) return $obj;
		}
		return null;
	}

}
