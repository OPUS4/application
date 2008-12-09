<?php
/**
 * Reader for OAI-repository interfaces
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
 * @package     Module_Oai
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class OaiReader
{
	
    /**
     * Array of identifiers in the OAI-repository
     *
     * @var array with strings (OAI-Identifiers)
     */
	public $identifierList = array();
    /**
     * URL of the OAI-repository
     *
     * @var string URL
     */
	public $oai_url;
	
	/**
	 * Reads OAI-Metadata from a given address
	 *
	 * @return DOMDocument with OAI-Metadata
	 * @param src URL (local or remote file) to load the OAI Metadata from
	 *
	 */
    public function getOaiData($src)
    {
    	// per OAI XML-Daten vom Exporthost harvesten 
    	$dom = new DOMDocument();
    	if (!$dom->load($src)) {
    		throw new Exception("Error beim Parsen von $src<br/>\n");
		}    	
    	return ($dom);
    }
    
	/**
	 * parses a complete OAI Record List
	 *
	 * @return void
	 * @param dom DOMDocument with the OAI-formatted RecordList of a repository 
	 *
	 */
    public function parseOaiRecordList($dom)
    {
    	// Erstelle eine Liste der Identifier

		$node_array = $dom->getElementsByTagName("identifier");
		
		for ($i = 0; $i < $node_array->length; $i++) {
		   $id = $node_array->item($i);
		   // Nur auf den Ergebnisarray packen, wenn der Elementname wirklich identifier ist (ohne dc: oder anderen Namespace)
		   if ($id->nodeName === "identifier") array_push($this->identifierList, $id->nodeValue);
		}
		
		// Auf Resumption-Token pruefen
		$more_data = $dom->getElementsByTagName("resumptionToken");
		if ($more_data->length > 0)
		{
			$resumptionToken = $more_data->item(0)->nodeValue;
			$this->parseOaiRecordList($this->getOaiData($this->oai_url."?verb=ListRecords&resumptionToken=$resumptionToken"));
		}
		
    }
    
	/**
	 * parses a OAI Record formatted in XMetaDissPlus
	 *
	 * @return array DocumentData
	 * @param dom DOMDocument with an OAI-formatted Record 
	 *
	 */
    public function parseOaiXMetaDissPlusRecord($dom)
    {
    	// Verarbeitet einen einzelnen OAI-Record

		$node_array = $dom->getElementsByTagName("xMetaDiss");
		
		// Es kann nur einen XMetaDiss-Knoten geben, in dem die Metadaten gekapselt sind
		// Daher statisch das Item an Index 0 auswählen 
		$metadata = $node_array->item(0)->childNodes;
		
		$n_title = 0;
		$n_person = 0;
		$n_subject = 0;

		$documentData = array();
		$title = array();
		
		for ($i = 0; $i < $metadata->length; $i++) {
			$id = $metadata->item($i);

	   		// dc:title und dcterms:alternative muessen zu einem zusammenhaengenden Titel zusammengesetzt werden
	   		// Daher muessen die Strukturen aus dem Originaldokument erstmal separat ausgelesen werden
	   		// Am Ende der if-Klauseln werden die einzelnen $doc->titel-Elemente in einem TitleField registriert
	   		if ($id->nodeName === "dc:title") 
	   		{
	   			#$doc->title[$n_title]["title"] = $id->nodeValue;
	   			#$doc->title[$n_title]["language"] = $this->mapLanguage($id->attributes->getNamedItem("lang")->nodeValue);
	   			$title[$n_title]["title"] = $id->nodeValue;
	   			$title[$n_title]["language"] = $id->attributes->getNamedItem("lang")->nodeValue;
	   			$n_title++;
	   		}
	   		if ($id->nodeName === "dcterms:alternative") 
	   		{
	   			for ($title_index = 0; $title_index < $n_title; $title_index++)
	   			{
	   				#if ($this->mapLanguage($doc->title[$title_index]["language"]) == $this->mapLanguage($id->attributes->getNamedItem("lang")->nodeValue))
	   				if ($title[$title_index]["language"] == $id->attributes->getNamedItem("lang")->nodeValue)
	   				{
	   					#$doc->title[$title_index]["title"] .= " : ".$id->nodeValue;
	   					$title[$title_index]["title"] .= " : ".$id->nodeValue;
	   				}
	   			}
	   		}
	   		
	   		if (isset($title))
	   		{
	   			foreach ($title as $t)
	   			{
					$documentData['title_main'][] = array('value' => $t["title"], 'language' => $t["language"]);
	   			}
	   		}
	   		unset($title);
	   			   		
	   		if ($id->nodeName === "dc:creator") 
	   		{
	   			// Creator ist eine (oder mehrere) Person(en)
	   			if (@$id->attributes->getNamedItem("type")->prefix == "xsi" && $id->attributes->getNamedItem("type")->nodeValue == "pc:MetaPers")
	   			{
	   				// Trag die Person in die DB ein
					$personId = $this->parsePerson($id);
					// Trag die Personen-ID mit Rolle in die Personen-Dokument-Tabelle ein
					#$documentData['author'][] = array('personId'=>$personId,'instituteId'=>1);
					# insert $personId, author
	   			}
	   			// Creator ist eine Institution/Koerperschaft
	   			#else
	   			#{
	   			#	$person[$n_person]["corporate"] = $id->nodeValue;
	   			#	$person[$n_person]["role"] = "author";
	   			#}
	   			$n_person++;
	   		}
	   		if ($id->nodeName === "dc:contributor") 
	   		{
	   			// Creator ist eine (oder mehrere) Person(en)
	   			if (@$id->attributes->getNamedItem("role")->prefix == "thesis" && $id->attributes->getNamedItem("role")->nodeValue == "advisor" && $id->attributes->getNamedItem("type")->prefix == "xsi" && $id->attributes->getNamedItem("type")->nodeValue == "pc:Contributor")
	   			{
	   				// Trag die Person in die DB ein
					$personId = $this->parsePerson($id);
					// Trag die Personen-ID mit Rolle in die Personen-Dokument-Tabelle ein
					# insert $personId, advisor
	   			}
				// Contributor ist eine (oder mehrere) Person(en)
	   			else if (@$id->attributes->getNamedItem("type")->prefix == "xsi" && $id->attributes->getNamedItem("type")->nodeValue == "pc:MetaPers")
	   			{
	   				// Trag die Person in die DB ein
					$personId = $this->parsePerson($id);
					// Trag die Personen-ID mit Rolle in die Personen-Dokument-Tabelle ein
					# insert $personId, contributor
	   			}
	   			// Creator ist eine Institution/Koerperschaft
	   			#else
	   			#{
	   			#	$person[$n_person]["corporate"] = $id->nodeValue;
	   			#	$person[$n_person]["role"] = "advisor";
	   			#}
	   			$n_person++;
	   		}
	   		if ($id->nodeName === "dc:subject") 
	   		{
	   			// Sprache wird dummymaessig auf de gesetzt; Opus 3.x zeigt keine Sprache an bei den Subjects
	   			$checkarray = array("DDC-SG","MSC2000","PACS2003","CCS98","BK2000");
	   			if (in_array($id->attributes->getNamedItem("type")->nodeValue, $checkarray))
	   			{
	   				$documentData[$this->mapSubject($id->attributes->getNamedItem("type")->nodeValue)][] = array('value' => $this->resolveClass($id->attributes->getNamedItem("type")->nodeValue,$id->nodeValue), 'external_key' => $id->nodeValue, 'language' => 'ger');
	   			}
	   			else
	   			{
	   				$documentData[$this->mapSubject($id->attributes->getNamedItem("type")->nodeValue)][] = array('value' => $id->nodeValue, 'language' => 'ger');
	   			}
	   		}
	   		if ($id->nodeName === "dcterms:abstract") 
	   		{
	   			$documentData['title_abstract'][] = array('value' => $id->nodeValue, 'language' => $id->attributes->getNamedItem("lang")->nodeValue);
	   		}
	   		if ($id->nodeName === "dc:publisher") 
	   		{
		 	   	// cc:universityOrInstitution
			   	$publisher_data = $id->getElementsByTagName("universityOrInstitution")->item(0)->childNodes;
			   	for ($i_publisher = 0; $i_publisher < $publisher_data->length; $i_publisher++)
			   	{
					$publisher["department"] = "";
					if ($publisher_data->item($i_publisher)->nodeName === "cc:name") 
	   				{
						$documentData["publisher_name"] = $publisher_data->item($i_publisher)->nodeValue;
						// publisher_university ist fremdverknuepft zu Einrichtungstabelle
						#$documentData["publisher_university"] = $publisher_data->item($i_publisher)->nodeValue;
	   				}
					if ($publisher_data->item($i_publisher)->nodeName === "cc:place") 
	   				{
						$documentData["publisher_place"] = $publisher_data->item($i_publisher)->nodeValue;
	   				}
	   				#if ($publisher_data->item($i_publisher)->nodeName == "cc:department")
	   				#{
	   				#	$publisher["department"] = $publisher_data->item($i_publisher)->getElementsByTagName("name")->item(0)->nodeValue;
	   				#}
			   	}
	   		}
	   		if ($id->nodeName === "dcterms:issued") 
	   		{
	   			$documentData["published_year"] = $id->nodeValue;
	   		}
	   		if ($id->nodeName === "dcterms:created") 
	   		{
	   			$documentData["created_year"] = $id->nodeValue;
	   		}
	   		if ($id->nodeName === "dcterms:modified") 
	   		{
	   			$documentData["server_date_modified"] = $id->nodeValue;
	   		}
	   		if ($id->nodeName === "dcterms:dateAccepted") 
	   		{
	   			$documentData["date_accepted"] = $id->nodeValue;
	   		}
	   		if ($id->nodeName === "dc:type") 
	   		{
	   			if ($id->attributes->getNamedItem("type")->prefix == "xsi" && $id->attributes->getNamedItem("type")->nodeValue == "bszterms:PublType")
	   			{
	   				$documentData["document_type"] = $id->nodeValue;
	   			}
	   		}
	   		if ($id->nodeName === "dc:identifier") 
	   		{
	   			if ($id->attributes->getNamedItem("type")->prefix == "xsi" && $id->attributes->getNamedItem("type")->nodeValue == "urn:nbn")
	   			{
	   				$documentData['identifier_urn'] = array('value' => $id->nodeValue);
	   			}
	   		}
	   		if ($id->nodeName === "dc:source") 
	   		{
	   			if ($id->attributes->getNamedItem("type")->prefix == "xsi" && $id->attributes->getNamedItem("type")->nodeValue == "ddb:ISBN")
	   			{
	   				$isbn = $id->nodeValue;
	   				$documentData["source"] = $id->nodeValue;
	   			}
	   		}
	   		if ($id->nodeName === "dc:language") 
	   		{
	   			if ($id->attributes->getNamedItem("type")->prefix == "xsi" && $id->attributes->getNamedItem("type")->nodeValue == "dcterms:ISO639-2")
	   			{
	   				$documentData["language"] = $id->nodeValue;
	   			}
	   		}
	   		if ($id->nodeName === "ddb:identifier") 
	   		{
	   			if ($id->attributes->getNamedItem("type")->prefix == "ddb" && $id->attributes->getNamedItem("type")->nodeValue == "URL")
	   			{
	   				$documentData['identifier_url'] = array('value' => $id->nodeValue);
	   			}
	   		}
	   		
 /*
Nicht per XMetaDissPlus abgreifbare Felder aus fields.xml
 email
 ppn
 dateModified
 dateCreation
 bemIntern
 bemExtern
 dateAccepted
 faculty
 institute
 

 
 
 mscClassification
 ccsClassification
 ddcSubject
 subjectUncontrolled
 subjectSwd
 	subject[n][name]
 	subject[n][type]
 title 
 	title[n]title = "Das ist der Titel"; 
 	title[n]language = "ger"; 
 	title[n]primary = true;
 author
 advisor
 (contributor) noch kein Feld in fields.xml definiert
 	person[n]title
	person[n]forename
	person[n]surname
	person[n]prefix
	person[n]givenname
	person[n]role = "author|advisor|contributor";
	person[n]corporate
 abstract
 	description[n]abstract
 	description[n]language       
 publisherUniversity
 	publisher[name]
 	publisher[place]
 	publisher[department]
  dateYear
	Einzelfeld
 resourceType
 	Einzelfeld documentType
 urn
 	Einzelfeld
 isbn
 	Einzelfeld 	
 language
 	Einzelfeld 	
 opusId        
	Einzelfeld opusID
        

        

        
        // Medium-Type wird innerhalb der Opus-DB nicht benoetigt
        foreach ($metadata->getMimeTypes() as $medium) {
            $output .= '<dcterms:medium xsi:type="dcterms:IMT">' . 
                    $medium . '</dcterms:medium>';
        }
        
        // Problem: isPartOf kann Schriftenreihe oder Originalpublikation anzeigen, 
        // das laesst sich per XMetaDissPlus nicht unterscheiden!
        if ($metadata->getSourceTitle()) {
            $output .= '<dcterms:isPartOf>' . 
                htmlspecialchars($metadata->getSourceTitle()) .
                '</dcterms:isPartOf>';
        }
        foreach ($metadata->getSequence() as $sequence) {
            $output .= '<dcterms:isPartOf>' . 
                htmlspecialchars($sequence->getSequenceName()) . ' ; ' . 
                    $sequence->getSequenceNumber() . '</dcterms:isPartOf>';
        }

        // Lizenz noch nicht in der Feldliste
        $output .= '<dc:rights xsi:type="ddb:noScheme">' . 
                htmlspecialchars($metadata->getLicense()) . '</dc:rights>';

		// Weitere (derzeit) nicht benoetigte Felder aus XMetaDissPlus
        $range =& $metadata->getRange();
        if ($range->getRangeId() > 1) {
            $output .= '<dcterms:accessRights xsi:type="ddb:access" ' .
                    'ddb:type="ddb:noScheme" ddb:kind="domain">' . 
                    $range->getRangeName() . '</dcterms:accessRights>';
        }
        
        if ($metadata->getTypeId() == 8 || $metadata->getTypeId() == 24) {
            $output .= '<thesis:degree>';        
            if ($metadata->getTypeId() == 8) {
                $level = 'thesis.doctoral';
            } else if ($metadata->getTypeId() == 24) {
                $level = 'thesis.habilitation';
            }
            $output .= '<thesis:level>' . $level . '</thesis:level>' .
                    '<thesis:grantor xsi:type="cc:Corporate">';
            $output .= $this->createUniversityorInstitution(
                    $metadata->getInstitutionName(), 
                    $metadata->getUniversityCity(), $metadata->getFaculty());
            $output .= '</thesis:grantor></thesis:degree>';
        }
        
        $output .= '<ddb:contact ddb:contactID="' . $metadata->getDdbId() . 
                '"/>';
        $output .= '<ddb:fileNumber>' . count($metadata->getFiles()) .
                '</ddb:fileNumber>';
        foreach ($metadata->getFiles() as $file) {
            $output .= '<ddb:fileProperties ddb:fileName="' . 
                    $file->getName() . '" ddb:fileID="file' .
                    $file->getId() . '" ddb:fileSize="' . $file->getSize() . 
                    '">aus: Praesentationsformat</ddb:fileProperties>';
        }
        $output .= '<ddb:identifier ddb:type="URL">' . 
                $range->getFulltextUrl() . '/' . 
                $metadata->getDateCreated('%Y') . '/' . 
                $metadata->getSourceOpus() . '</ddb:identifier>';
        
        if ($range->getRangeId() == 1) {
            $output .= '<ddb:rights ddb:kind="free"/>';
        } else {
            $output .= '<ddb:rights ddb:kind="domain">' . 
                    $range->getRangeName(). '</ddb:rights>';
        }


*/    
		}
		
		// Noch statisch auf paper festgelegt, spaeter 
		// $doc = new Document($documentType);
		// $doc = new Document("paper");
		
		// Registrierung der temporaeren Titeldaten in einem echten TitleField
		#foreach ($title as $doctitle)
		#{
		#	$titledata->addValue($doctitle["title"], $doctitle["language"]);
		#}
		#foreach ($person as $docperson)
		#{
	#		if (get_class($docperson) == "Author")
	#		{
	#			$authordata->addValue(serialize($docperson), "de");
	#		}
	#	}
	#	foreach ($subject as $docsubject)
	#	{
	#		if ($docsubject["type"] == "SWD")
	#		{
	#			$subjectdata->addValue($docsubject["name"], "de");
	#		}
	#	}
	#	$doc->setField("titleData", $titledata);
	#	$doc->setField("abstractData", $abstractdata);
	#	$doc->setField("dateYearData", $dateYearData);
	#	$doc->setField("authorData", $authordata);
	#	$doc->setField("subjectSwdData", $subjectdata);

        // Beispiel fuer einzutragende Daten in documents-Tabelle
/*        $data =
        array(
                'range_id' => '123',
                'completed_date' => '2008-01-01',
                'completed_year' => '2008',
                'contributing_corporation' => 'test corporation',
                'creating_corporation' => 'test corporation',
                'date_accepted' => '2008-01-01',
                'document_type' => 'article',
                'edition' => '1',
                'issue' => '1/2008',
                'language' => 'de',
                'non_institute_affiliation' => 'foreign institute',
                'page_first' => '1',
                'page_last' => '100',
                'page_number' => '100',
                'publication_status' => '1',
                'published_date' => '2008-01-01',
                'published_year' => '2008',
                'publisher_name' => 'test publisher',
                'publisher_place' => 'Saarbrücken',
                'publisher_university' => '1',
                'reviewed' => 'open',
                'server_date_modified' => '2008-01-01',
                'server_date_published' => '2008-01-01',
                'server_date_unlocking' => '2008-01-01',
                'server_date_valid' => '2008-01-01',
                'source' => 'teststring',
                'swb_id' => '12345',
                'vg_wort_pixel_url' => 'vg_wort_uri',
                'volume' => '2008'
                );
        // Beispiel fuer Titeldaten in DocumentTitleAbstracts-Tabelle
        $data =
        array(
                'document_type' => 'article',
                array('title_abstract_type' => 'main', 'title_abstract_value' => 'main title', 'title_abstract_language' => 'de')
        );
        */
		return($documentData);
    }

	/**
	 * maps language from ISO 639-2 to OPUS-3.x-compliant two-letter-code
	 *
	 * @return string two-letter-code
	 * @param string  ISO 639-2-code
	 *
	 */
	private function mapLanguage($lang)
	{
		if (strlen($lang) == 2)
		{
			return $lang;
		}
		switch ($lang)
		{
			case "eng":
				return "en";
				break;
			case "ger":
				return "de";
				break;
			case "fre":
				return "fr";
				break;
			case "rus":
				return "ru";
				break;
			case "mul":
				return "mu";
				break;
			default:
				return "??";
				// de nada
		}
		return NULL;
	}

	/**
	 * maps subject type from XMetaDiss(Plus) OPUS-compliant code
	 *
	 * @return string subject code from XMetaDiss(Plus)
	 * @param string  OPUS-compliant subjectcode
	 *
	 */
	private function mapSubject($subject)
	{
		switch ($subject)
		{
			case "DDC-SG":
				return "subject_ddc";
				break;
			case "SWD":
				return "subject_swd";
				break;
			case "MSC2000":
				return "subject_msc2000";
				break;
			case "PACS2003":
				return "subject_pacs2003";
				break;
			case "CCS98":
				return "subject_ccs98";
				break;
			case "BK2000":
				return "subject_bk2000";
				break;
			case "noScheme":
				return "subject_uncontrolled";
				break;
			default:
				return $subject;
				// de nada
		}
		return NULL;
	}

	/**
	 * inserts a person into the OPUS-system
	 *
	 * @return int ID of the person in OPUS database
	 * @param DOMDocument with person-data
	 *
	 */
	private function parsePerson($id)
	{
	   	$person_data = $id->getElementsByTagName("person")->item(0)->childNodes;
	   	for ($i_person = 0; $i_person < $person_data->length; $i_person++)
	   	{
			$personData = array();
            if ($person_data->item($i_person)->nodeName === "pc:academicTitle") 
	   		{
	   			$personData["academicTitle"] = $person_data->item($i_person)->nodeValue;
			}
			if ($person_data->item($i_person)->nodeName === "pc:name") 
	   		{
	   			#$result->givenname = "";
	   			// Alle Tags mit Prefix pc:
	   			$personData["firstName"] = $person_data->item($i_person)->getElementsByTagName("foreName")->item(0)->nodeValue;
	   			$personData["lastName"] = $person_data->item($i_person)->getElementsByTagName("surName")->item(0)->nodeValue;
	   			if ($person_data->item($i_person)->getElementsByTagName("prefix")->item(0)) $personData["lastName"] = $person_data->item($i_person)->getElementsByTagName("prefix")->item(0)->nodeValue." ".$personData["lastName"];
	   			if ($person_data->item($i_person)->getElementsByTagName("personEnteredUnderGivenName")->item(0))
	   			{ 
	   				$personData["firstName"] = $person_data->item($i_person)->getElementsByTagName("personEnteredUnderGivenName")->item(0)->nodeValue;
	   				$personData["lastName"] = $person_data->item($i_person)->getElementsByTagName("personEnteredUnderGivenName")->item(0)->nodeValue;
	   			}
	   			#$result->corporate = "";
	   		}
	   		else
	   		{
	   			$personData["firstName"] = "Dummy";
	   			$personData["lastName"] = "Dummy";
	   		}
	   		// Dummyangaben, weil Pflichtfelder...
            // Angaben, die noch reinmuessten, aber in XMetaDissPlus aus OPUS 3.x nicht enthalten sind
	   		$personData["placeOfBirth"] = 'Musterstadt';
	   		$personData["dateOfBirth"] = new Zend_Date('15.07.2008');
	   		$personData["email"] = 'mustermann@domain.com';
	   	}

	   	if ($this->personExists($personData) !== 0)
	   	{
	   		$id = $this->personExists($personData);
	   	}
	   	else
	   	{
	   		$id = Opus_Person_Information::add($personData);
		}
	   	// Gib die ID der gerade erzeugten Person zurueck
	   	return ($id);
	}
	
	/**
	 * controlls if a person exists in the OPUSsystem
	 *
	 * @return int ID of the person in OPUS database (if 0 the person does not exist)
	 * @param Array with person-data
	 *
	 */
	private function personExists($person)
	{
	    $searchPerson = Opus_Person_Information::get(array("first_name"=>$person["firstName"],"last_name"=>$person["lastName"]));
	    if (count($searchPerson) === 0) return 0;
	    else return $searchPerson["id"];
	}
	
	private function resolveClass($classification, $class) 
	{
		$handle = fopen ("http://doku.b.tu-harburg.de/classResolver.php?classification=$classification&class=$class", "r");
		while (!feof($handle)) {
    		$answer .= fgets($handle, 4096);
		}
		fclose ($handle);
		return $answer; 
	}
}
