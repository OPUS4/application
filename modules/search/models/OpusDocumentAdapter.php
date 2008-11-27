<?php
/**
 * Adapter to use the Documents from the framework in Module_Search
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

class OpusDocumentAdapter extends Opus_Model_Document
{
	/**
	 * Attribute to store the Document as an Array
	 * @access private
	 */
	private $documentData;
	
  /**
   * Constructor
   * 
   * @param Integer|Array|OpusDocumentAdapter opusDocument data for the new OpusDocumentAdapter-Object 
   */
	public function __construct($opusDocument = null)
	{
  		$this->documentData = array();
  		if (is_int($opusDocument)) {
  			$this->documentData["id"] = $opusDocument;
  			$this->mapDocument();
  		}
  		else if (is_array($opusDocument)) {
  			$this->documentData = $opusDocument;
  		}
  		elseif (get_class($opusDocument) === "OpusDocumentAdapter")
  		{
  			$this->documentData = $opusDocument->getDocument();
  		}
	}
	
  /**
   * Returns the document data as an array
   * 
   * @return Array Array with document data usable in Module_Search 
   */
	public function getDocument()
	{
		return $this->documentData;
	} 

	private function mapDocument()
	{
		parent::__construct(new Opus_Document_Builder(), $this->documentData["id"]);
		$title = $this->_fetchTitleMain();
		$this->documentData["title"] = $title['value'];
		$this->documentData["frontdoorUrl"] = array(
										"module"=>"frontdoor", 
										"controller" => "index", 
										"action"=>"index", 
										"id"=>$this->documentData["id"]);
		$authors = $this->_fetchAuthors();
		if (count($authors) > 0)
		{
			foreach ($authors as $authorId)
			{
				$this->documentData["author"][] = new OpusPersonAdapter($authorId);
			}
		}
		else
		{
			$this->documentData["author"][] = new OpusPersonAdapter(array(null, null, null));
		} 
		/*
		 * Fields that should be set by this method 
		 * $this->documentData["author"] = PersonsList
		 * $this->documentData["frontdoorUrl"] = array (with elements for View::Url)
		 * $this->documentData["title"] = String
		 * $this->documentData["abstract"] = String
		 * $this->documentData["fileUrl"] = array (with elements for View::Url)
		 * $this->documentData["documentType"] = DocumentTypeAdapter
		 * 
		 */
		 /* sample datastructure
		 								"author" => new OpusPersonAdapter(
										array(
											"id" => "1", 
											"lastName" => "Marahrens", 
											"firstName" => "Oliver"
										)
									), 
									"frontdoorUrl" => array(
										"module"=>"frontdoor", 
										"controller" => "index", 
										"action"=>"index", 
										"id"=>"82"
									), 
									"title" => "Prüfung und Erweiterung der technischen Grundlagen des Dokumentenservers OPUS zur Zertifizierung gegenüber der DINI anhand der Installation an der TU Hamburg-Harburg", 
									"abstract" => "Viele Hochschulen (bzw. die Hochschulbibliotheken) setzen heutzutage Dokumentenserver ein, um Dokumente online verfügbar zu machen und diese Online-Dokumente zu verwalten. In manchen Hochschulen ist es für die Studierenden sogar möglich, ihre Abschlussarbeit auf diesem Server zu veröffentlichen, was im Sinne der Promotionsordnung als ordnungsgemässe Veröffentlichung akzeptiert werden und so den Doktoranden eventuell hohe Kosten einer Verlagsveröffentlichung oder anderweitigen gedruckten Publikation ersparen kann. Ein solcher Dokumentenserver, der unter anderem in der Bibliothek der Technischen Universität Hamburg eingesetzt wird, ist OPUS. Um die Akzeptanz eines solchen Servers bei den Promovenden (aber auch den Studierenden, da OPUS nicht ausschliesslich Dissertationen und Habilitationen aufnimmt) zu erhöhen und sicherzustellen, dass der Server internationalen Standards folgt und so zum Beispiel auch von anderen Hochschulen oder Metasuchmaschinen etc. durchsucht werden kann, gibt es die Möglichkeit, einen Dokumentenserver zertifizieren zu lassen. Ein solches Zertifikat wird von der DINI (Deutsche Initiative für Netzwerkinformation) vergeben. In der vorliegenden Arbeit wird untersucht, inwiefern die Installation des Dokumentenservers OPUS an der TU Hamburg-Harburg die Zertifizierungsbedingungen der DINI erfüllt und wo ggf. Erweiterungsbedarf besteht.", 
									"fileUrl" => array(
										"module"=>"frontdoor", 
										"controller" => "file", 
										"action"=>"view", 
										"id"=>"82",
										"filename"=>"projektbericht.pdf"
									), 
									"documentType" => new DocumentTypeAdapter(
										array(
											"id" => "1", 
											"name" => "Dissertation", 
											"type" => "Thesis"
										)
									)
	*/	
	}

}

?>
