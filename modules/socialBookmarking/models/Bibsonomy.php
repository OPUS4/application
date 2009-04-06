<?php
/**
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
 * @package     Module_SocialBookmarking
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// Connection variables
define ('x401_host', 'www.connotea.org');
define ('x401_port', 80);
// Debugging Connotea interface: 0 = no logging, 1 = Logging in LOGFILE
define ('DEBUG', 0);
define ('LOGFILE', '/tmp/connotea_debug.log');

class Bibsonomy
{		
	/**
	 * @var String Username for Bibsonomy 
	 */
	var $user;
	
	/**
	 * @var String Bibsonomy-Password
	 */
	var $password;
	
	/**
	 * @var Integer Timeout (constant)
	 */
	var $timeout = 20;
	
	function logit($stringtolog)
	{
		$fp = fopen(LOGFILE, "a");
		fwrite($fp, date("Y-m-d H:i:s")." ".$stringtolog."\n");
		fclose($fp);
	}

	/**
	 * @param string $file          Script to call at Connotea
	 * @param array  $data_to_send  All values that should gat posted to Connotea 
	 */
	function postit($file, $data_to_send) 
	{
		if (DEBUG) $this->logit("Methode postit($file, $data_to_send) aufgerufen");
		// prepare parameters
		foreach ($data_to_send as $key => $dat)
		{
			$data_to_send[$key] = "$key=".rawurlencode(utf8_encode(stripslashes($dat)));
		}
		$postData = implode("&", $data_to_send);
		
		if (DEBUG) $this->logit("data_to_post vorbereitet (postData ist $postData)");
		
		// HTTP-Header vorbereiten
		$out  = "POST $file HTTP/1.1\r\n";
		$out .= "Host: ".x401_host."\r\n";
		$out .= "Content-type: application/x-www-form-urlencoded\r\n";
		$out .= "Content-length: ". strlen($postData) ."\r\n";
		$out .= "User-Agent: ".$_SERVER["HTTP_USER_AGENT"]."\r\n";
		$out .= "Authorization: Basic ".base64_encode($this->user.":".$this->password)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "\r\n";
		$out .= $postData;
		if (!$conex = @fsockopen(x401_host, x401_port, $errno, $errstr, 10)) return 0;
		if (DEBUG) $this->logit("conex geoeffnet");
		fwrite($conex, $out);
		if (DEBUG) $this->logit("$out auf conex geschrieben");
		$data = '';
		while (!feof($conex)) {
			$data .= fgets($conex, 512);
		}
		fclose($conex);
		if (DEBUG) $this->logit("Methode postit($file, $data_to_send) abgeschlossen, returne...");
		return $data;
	}
	
	/**
	 * @param String url URL der Datei, deren Tags abgefragt werden sollen 
	 * @return Array mit den einzelnen Tags 
	 */
	function gettags($url) 
	{
		if (DEBUG) $this->logit("Methode gettags($url) aufgerufen");
		// HTTP-Header vorbereiten
		$out  = "GET /data/tags/uri/$url HTTP/1.1\r\n";
		$out .= "Host: ".x401_host."\r\n";
		$out .= "User-Agent: ".$_SERVER["HTTP_USER_AGENT"]."\r\n";
		$out .= "Authorization: Basic ".base64_encode($this->user.":".$this->password)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "\r\n";
		if (!$conex = @fsockopen(x401_host, x401_port, $errno, $errstr, 10)) return 0;
		fwrite($conex, $out);
		$data = '';
		while (!feof($conex)) {
			$data .= fgets($conex, 512);
		}
		fclose($conex);
		#echo $data;
		
		// Der Header der R�ckgabe muss rausgecuttet werden, sonst ist das XML-Dokument nicht wohlgeformt
		$xmlStart = strpos($data, "<?xml");
		$xmlCode = substr($data, $xmlStart);

		#echo $xmlCode;

		$p = xml_parser_create();
		xml_parse_into_struct($p, $xmlCode, $vals, $index);
		xml_parser_free($p);
		#echo "Index array\n";
		#print_r($index);
		#echo "\nVals array\n";
		#print_r($vals);
		
		$tags = array();
		if (!$index)
		{
			return (-1);
		}
		if (array_key_exists("RDF:VALUE", $index))
		{
			foreach ($index["RDF:VALUE"] as $key)
			{
				array_push($tags, $vals[$key]["value"]);
			}
		}
		return ($tags);
	}

	/**
	 * @return Array mit den Bookmarks des Connotea-Users 
	 */
	function getbookmarks() 
	{
		if (DEBUG) $this->logit("Methode getbookmarks() aufgerufen");
		// HTTP-Header vorbereiten
		$out  = "GET /data/user/".$this->user." HTTP/1.1\r\n";
		$out .= "Host: ".x401_host."\r\n";
		$out .= "Authorization: Basic ".base64_encode($this->user.":".$this->password)."\r\n";
		$out .= "User-Agent: ".$_SERVER["HTTP_USER_AGENT"]."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "\r\n";
		if (!$conex = @fsockopen(x401_host, x401_port, $errno, $errstr, 10)) return 0;
		fwrite($conex, $out);
		$data = '';
		while (!feof($conex)) {
			$data .= fgets($conex, 512);
		}
		fclose($conex);
		#echo $data;
		
		// Der Header der R�ckgabe muss rausgecuttet werden, sonst ist das XML-Dokument nicht wohlgeformt
		$xmlStart = strpos($data, "<?xml");
		$xmlCode = substr($data, $xmlStart);

		#echo $xmlCode;

		$p = xml_parser_create();
		xml_parse_into_struct($p, $xmlCode, $vals, $index);
		xml_parser_free($p);
		#echo "Index array\n";
		#print_r($index);
		#echo "\nVals array\n";
		#print_r($vals);
		
		$tags = array();
		if (!$index)
		{
			return (-1);
		}
		if (array_key_exists("LINK", $index))
		{
			foreach ($index["LINK"] as $key)
			{
				array_push($tags, $vals[$key]["value"]);
			}
		}
		return ($tags);
	}

	/**
	 * @param String url URL der Datei, die beim User auf Vorhandensein gepr�ft werden soll
	 * @return Integer: User hat Bookmark schon eingetragen: 1, sonst 0, bei Timeout aber -1 
	 */
	function userHatBookmark($url) 
	{
		if (DEBUG) $this->logit("Methode userHatBookmark($url) aufgerufen");
		$bookmarkliste = $this->getbookmarks();
		$i = 0;
		while ($bookmarkliste == -1)
		{
			// Automatischer Retry, bis Bookmarkliste erfolgreich eingelesen
			sleep(1);
			$bookmarkliste = $this->getbookmarks();
			$i++;
			if ($i == $this->timeout)
			{
				return -1;
			}
		}
		if (in_array($url, $bookmarkliste))
		{
			return 1;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * @param String return XML-R�ckgabe von Connotea
	 * @abstract Liefert zur�ck, ob das Posting erfolgreich war oder nicht
	 */
	function isSuccess($return)
	{
		if (DEBUG) $this->logit("Methode isSuccess($return) aufgerufen");
		// Der Header der R�ckgabe muss rausgecuttet werden, sonst ist das XML-Dokument nicht wohlgeformt
		$xmlStart = strpos($return, "<?xml");
		$xmlCode = substr($return, $xmlStart);

		#echo $xmlCode;

		$p = xml_parser_create();
		xml_parse_into_struct($p, $xmlCode, $vals, $index);
		xml_parser_free($p);
		#echo "Index array\n";
		#print_r($index);
		#echo "\nVals array\n";
		#print_r($vals);
		// In den Indizes muss irgendwo der Key ISSUCCESS auftauchen, value ist der Index im vals-Array
		if (array_key_exists("ISSUCCESS", $index))
		{
			$successkey = $index["ISSUCCESS"][0];
		} 
		else
		{
			return 0;
		}
		// Nun den Wert �berpr�fen (er muss 1 sein)
		if ($vals[$successkey]["value"] == 1) return 1;
		else return 0;
	}

	/**
	 * @param Array POST-Werte des einzutragenden Eintrags
	 * @return Integer oder Array -1 = Tagliste nicht erfolgreich geholt (Timeout), Array = Tagliste
	 */
	function listTags($url)
	{
		if (DEBUG) $this->logit("Methode listTags($url) aufgerufen");
		$return = $this->gettags($url);
		$i = 0;
		while ($return == -1)
		{
			// Automatischer Retry, bis Tagliste erfolgreich eingelesen
			sleep(1);
			$return = $this->gettags($url);
			$i++;
			if ($i == $this->timeout)
			{
				return -1;
			}
		}
		return $return;
	}

	/**
	 * @param Array POST-Werte des einzutragenden Eintrags
	 * @return Boolean 0 = Eintrag nicht erfolgreich (Timeout), 1 = Eintrag erfolgreich
	 */
	function addBookmark($params)
	{
		if (DEBUG) $this->logit("Methode addBookmark($params) aufgerufen");
		$return = $this->postit("/data/add", $params);
		$i = 0;
		while (!$this->isSuccess($return))
		{
			// Automatischer Retry, bis Eintrag erfolgreich
			sleep(1);
			$return = $this->postit("/data/add", $params);
			$i++;
			if ($i == $this->timeout)
			{
				return 0;
			}
		}
		return 1;
	}

	/**
	 * @param String URL des zu entfernenden Eintrags
	 */
	function deleteBookmark($url)
	{
		if (DEBUG) $this->logit("Methode deleteBookmark($url) aufgerufen");
		$params = array('uri' => $url);
		$return = $this->postit("/data/remove", $params);
		$i = 0;
		while (!$this->isSuccess($return))
		{
			// Automatischer Retry, bis Eintrag erfolgreich
			sleep(1);
			$return = $this->postit("/data/remove", $params);
			$i++;
			if ($i == $this->timeout)
			{
				return 0;
			}
		}
		return 1;
	}

	/**
	 * Login to Connotea
	 * 
	 * @return boolean true if the User is authenticated successfully, otherwise flase
	 */
	function login() 
	{
		if (DEBUG) $this->logit("Methode login() aufgerufen");
		// Prepare HTTP-Header
		$out  = "GET /data/noop HTTP/1.1\r\n";
		$out .= "Host: ".x401_host."\r\n";
		$out .= "User-Agent: ".$_SERVER["HTTP_USER_AGENT"]."\r\n";
		$out .= "Authorization: Basic ".base64_encode($this->user.":".$this->password)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "\r\n";
		if (!$conex = @fsockopen(x401_host, x401_port, $errno, $errstr, 10)) return 0;
		fwrite($conex, $out);
		$data = '';
		while (!feof($conex)) {
			$data .= fgets($conex, 512);
		}
		fclose($conex);
		if ($this->isSuccess($data))
		{
			return true;
		}
		return false;
	}
}