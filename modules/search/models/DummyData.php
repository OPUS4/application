<?php
/**
 * Just Dummydata for Module_Search-Models, later the data out of the database should be used instaed
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
 * @category    OAI
 * @package     Module_Oai
 * @author      Oliver Marahrens (o.marahrens@tu-harburg.de)
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class DummyData
{
	/**
	 * Dummydata with Structure of Documents using the adapters
	 * 
	 * @return Array Array of OpusDocumentAdapters containing dummy data
	 * @static
	 */
	public static function getDummyDocuments()
	{
		$dummydata = 	array(
							new OpusDocumentAdapter(
								array(
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
								)
							),
							new OpusDocumentAdapter(
								array(
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
										"id"=>"357"
									), 
									"title" => "Entwicklung einer Suchfunktion mit Information-Retrieval-Schnittstelle für den Dokumentenserver OPUS unter besonderer Berücksichtigung von Entwurfsmustern", 
									"abstract" => "In der vorliegenden Abschlussarbeit wurde eine Suchfunktion für den Dokumentenserver TUBdok, der von der Universitätsbibliothek an der Technischen Universität Hamburg-Harburg betrieben wird, entwickelt. Für die Suche wurde eine in das Dokumentenserver-System eingepasste Oberfläche entwickelt. Außerdem steht eine per URL ansteuerbare Information-Retrieval-Schnittstelle zur Verfügung, die das layout-unabhängige XML-Format OpenSearch zurückgibt. Die Suchfunktion basiert auf Apache Lucene und wurde über eine im Zend-Framework vorhandene Implementierung in den PHP-basierten Dokumentenserver integriert. Auch das Indizierungssystem, mit dem die Inhalte des Dokumentenservers in den Datenbestand der Suchmaschine übertragen werden, wurde in PHP implementiert. Die Indexstruktur ist selbst erstellt und an die Dokumentenserver-Umgebung angepasst. Das System ist nach Abschluss der Arbeit grundsätzlich funktionsfähig.", 
									"fileUrl" => array(
										"module"=>"frontdoor", 
										"controller" => "file", 
										"action"=>"view", 
										"id"=>"357",
										"filename"=>"marahrens.pdf"
									), 
									"documentType" => new DocumentTypeAdapter(
										array(
											"id" => "2", 
											"name" => "Monographie", 
											"type" => "book"
										)
									)
								)
							),
							new OpusDocumentAdapter(
								array(
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
										"id"=>"358"
									), 
									"title" => "Europa und die Informationsgesellschaft", 
									"abstract" => "Bei dem Artikel handelt es sich um einen Bericht zum 3. SuMa-eV-Forum am 28.09.2006 in Berlin. Die Tagung stand unter dem Titel 'Suchmaschinen: In Technik, Wirtschaft und Medienkunst'. Dieses Dokument ist die Eigenarchivierung des Autors eines in der Ausgabe 1/2007 der Zeitschrift für Bibliothekswesen und Bibliographie (ZfBB, Klostermann Verlag, ISSN 0044-2380) erschienenen Artikels.", 
									"fileUrl" => array(
										"module"=>"frontdoor", 
										"controller" => "file", 
										"action"=>"view", 
										"id"=>"358",
										"filename"=>"zfbb_suma_marahrens_eigenarchivierung.pdf"
									), 
									"documentType" => new DocumentTypeAdapter(
										array(
											"id" => "2", 
											"name" => "Monographie", 
											"type" => "book"
										)
									)
								)
							)
						);
		return $dummydata;
	}

	/**
	 * Dummydata with Structure of Persons using the adapters
	 * 
	 * @return Array Array of OpusPersonAdapters containing dummy data
	 * @static
	 */
	public static function getDummyPersons()
	{
		$dummydata = array( 
						new OpusPersonAdapter(
							array(
								"id" => "1", 
								"lastName" => "Marahrens", 
								"firstName" => "Oliver"
							)
						),
						new OpusPersonAdapter(
							array(
								"id" => "2", 
								"lastName" => "Dummy", 
								"firstName" => "DummyVorname"
							)
						)
					);
		return $dummydata;
	}

	/**
	 * Dummydata with Structure of DocumentTypes using the adapters
	 * 
	 * @return Array Array of DocumentTypeAdapters containing dummy data
	 * @static
	 */
	public static function getDummyDocumentTypes()
	{
		$dummydata = array( 
						new DocumentTypeAdapter( 
							array(
								"id" => "1", 
								"name" => "Dissertation", 
								"type" => "Thesis"
							)
						),
						new DocumentTypeAdapter(
							array(
								"id" => "2", 
								"name" => "Monographie", 
								"type" => "book"
							)
						)
					);
		return $dummydata;
	}

	/**
	 * Dummydata with Structure of CollectionRoles
	 * 
	 * @return Array Array of CollectionRoles-data (not an Object!) containing dummy data
	 * @static
	 */
	public static function getDummyCollections()
	{
		$dummydata = array( 
						array(
							"eng" => array( 
								"collections_roles_id" => 1, 
								"collections_language" => "eng",
								"name" => "Dewey Decimal Classification (DDC)",	
								"visible" => 1
							), 
							"ger" => array( 
								"collections_roles_id" => 1, 
								"collections_language" => "ger", 
								"name" => "Sachgruppen der Dewey Decimal Classification (DDC)", 
								"visible" => 1
							)
						),
						array(
							"eng" => array( 
								"collections_roles_id" => 2, 
								"collections_language" => "eng", 
								"name" => "Collections", 
								"visible" => 1 
							),
							"ger" => array( 
								"collections_roles_id" => 2, 
								"collections_language" => "ger", 
								"name" => "Schriftenreihen", 
								"visible" => 1 
							) 
						) 
					);
		return $dummydata;
	}

	/**
	 * Dummydata with Structure of a CollectionNode
	 * 
	 * @return Array Array of CollectionNode-data (not an Object!) containing dummy data
	 * @static
	 */
	public static function getDummyCollectionNode()
	{
		$dummydata = array( 
						array(
							"content" => array (
								"eng" => array (
									"collections_id" => 1, 
									"collections_language" => "eng", 
									"name" => "Computer science, information, and general works", 
									"number" => "000" 
								), 
								"ger" => array (
									"collections_id" => 1, 
									"collections_language" => "ger",
									"name" => "Informatik, Informationswissenschaft, allgemeine Werke",
									"number" => "000"
								)
							),
							"structure" => array(
								"collections_id" => 1,
								"left" => 2,
								"right" => 35,
								"visible" => 1
							)
						),
						array(
							"content" => array (
								"eng" => array (
									"collections_id" => 2,
									"collections_language" => "eng",
									"name" => "Philosophy and psychology",
									"number" => "100"
								),
								"ger" => array (
									"collections_id" => 2,
									"collections_language" => "ger",
									"name" => "Philosophie und Psychologie",
									"number" => "100"
								)
							),
							"structure" => array (
								"collections_id" => 2,
								"left" => 36,
								"right" => 57,
								"visible" => 1
							) 
						)
					); 
		return $dummydata;
	}
}

?>
