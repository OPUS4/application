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
define ('x401_host', 'www.bibsonomy.org');
define ('x401_port', 80);
// Debugging Connotea interface: 0 = no logging, 1 = Logging in LOGFILE
define ('DEBUG', 0);
define ('LOGFILE', '/tmp/bibsonomy_debug.log');

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
	 * @var String Opus-Username for Bibsonomy 
	 */
	var $sysuser;
	
	/**
	 * @var String Opus User Bibsonomy-Password
	 */
	var $syspassword;

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
	 * @param string $file          Script to call at Bibsonomy
	 * @param array  $data_to_send  All values that should get posted to Bibsonomy
	 * Should include the following elements
	 * description
	 * tags (space seperated list)
	 * uri
	 * usertitle 
	 */
	function postit($file, $data_to_send) 
	{
		if (DEBUG) $this->logit("Methode postit($file, $data_to_send) aufgerufen");
		$postData = new DOMDocument;
		$rootNode = $postData->createElement('bibsonomy');
		$postData->appendChild($rootNode);
		
		$posting = $postData->createElement('post');
		$posting->setAttribute('description', $data_to_send['description']);
	    $rootNode->appendChild($posting);

		$user = $postData->createElement('user');
		$user->setAttribute('name', $this->user);
	    $posting->appendChild($user);
		
		$tags = split("\ ", $data_to_send['tags']);
		$i = 0;
		foreach ($tags as $tag) {
		    if ($tag !== "")
		    {
		        $t[$i] = $postData->createElement('tag');
		        $t[$i]->setAttribute('name', $tag);
	            $posting->appendChild($t[$i]);
	            $i++;
		    }
		}

		$group = $postData->createElement('group');
		$group->setAttribute('name', 'public');
	    $posting->appendChild($group);

		$bookmark = $postData->createElement('bookmark');
		$bookmark->setAttribute('url', $data_to_send['uri']);
		$bookmark->setAttribute('title', html_entity_decode($data_to_send['usertitle']));
	    $posting->appendChild($bookmark);

		if (DEBUG) $this->logit("data_to_post vorbereitet (postData ist ".$postData->saveXml().")");
		
		// HTTP-Header vorbereiten
		$out  = "POST $file HTTP/1.1\r\n";
		$out .= "Host: ".x401_host."\r\n";
		$out .= "Content-type: text/plain\r\n";
		$out .= "Content-length: ". strlen($postData->saveXml()) ."\r\n";
		$out .= "User-Agent: ".$_SERVER["HTTP_USER_AGENT"]."\r\n";
		$out .= "Authorization: Basic ".base64_encode($this->user.":".$this->password)."\r\n";
		$out .= "Connection: Close\r\n";
		$out .= "\r\n";
		$out .= $postData->saveXml();
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
		$out  = "GET /api/users/".$this->user."/posts?resourcetype=bookmark HTTP/1.1\r\n";
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

		print ($xmlCode);
		$documentsXML = new DOMDocument;
		$documentsXML->loadXML($xmlCode);
		$postList = $documentsXML->getElementsByTagName('post');
		$tags = array();
		foreach ($postList as $post) 
		{
			$postXml = new DOMDocument; 
			$postXml->loadXml($documentsXML->saveXML($post));
			$postData = $postXml->getElementsByTagName('bookmark');
			if ($postData->item(0)->getAttribute('url') === $url)
			{
		        $tagList = $postXml->getElementsByTagName('tag');
		        foreach ($tagList as $tag) 
		        {
			        $tagValue = $tag->getAttribute('name');
			        $tags[] = $tagValue;
		        }
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
		$out  = "GET /api/users/".$this->user."/posts?resourcetype=bookmark HTTP/1.1\r\n";
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

		$documentsXML = new DOMDocument;
		$documentsXML->loadXML($xmlCode);
		$postList = $documentsXML->getElementsByTagName('post');
		$tags = array();
		foreach ($postList as $post) 
		{
			$postData = $post->getElementsByTagName('bookmark');
			$tags[] = $postData->item(0)->getAttribute('url');
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
	 * Checks the result of the API request
	 * 
	 * @param string $return XML-Output by Bibsonomy
	 * @return boolean true if there has been no error
	 */
	function isSuccess($return)
	{
		if (DEBUG) $this->logit("Methode isSuccess($return) aufgerufen");
		// Der Header der R�ckgabe muss rausgecuttet werden, sonst ist das XML-Dokument nicht wohlgeformt
		$xmlStart = strpos($return, "<?xml");
		$xmlCode = substr($return, $xmlStart);

		$documentsXML = new DOMDocument;
		$documentsXML->loadXML($xmlCode);
		$postList = $documentsXML->getElementsByTagName('bibsonomy');
		$root = $postList->item(0);

		// In den Indizes muss irgendwo der Key ISSUCCESS auftauchen, value ist der Index im vals-Array
		if ($root->getAttribute('stat') === 'fail')
		{
			return false;
		} 
		else
		{
			return true;
		}
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
		$return = $this->postit("/api/users/" . $this->user . "/posts", $params);
		return 1;
	}

	/**
	 * @param String URL des zu entfernenden Eintrags
	 */
	function deleteBookmark($url)
	{
		if (DEBUG) $this->logit("Methode deleteBookmark($url) aufgerufen");
		$out  = 'DELETE /api/users/' . $this->user . '/posts/' . $this->getUrlHash($url) ." HTTP/1.1\r\n";
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
		return 1;
	}

	/**
	 * @param String URL des zu entfernenden Eintrags
	 */
	function getUrlHash($url)
	{
		if (DEBUG) $this->logit("Methode getUrlHash($url) aufgerufen");
		// HTTP-Header vorbereiten
		$out  = "GET /api/users/".$this->user."/posts?resourcetype=bookmark HTTP/1.1\r\n";
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

		$documentsXML = new DOMDocument;
		$documentsXML->loadXML($xmlCode);
		$postList = $documentsXML->getElementsByTagName('post');
		foreach ($postList as $post) 
		{
			$postData = $post->getElementsByTagName('bookmark');
			if ($postData->item(0)->getAttribute('url') === $url)
			{
			    return $postData->item(0)->getAttribute('intrahash');
			}
		}
		return null;
	}

	/**
	 * Login to Bibsonomy
	 * 
	 * @return boolean true if there is no authentication failure
	 */
	function login() 
	{
		if (DEBUG) $this->logit("Methode login() aufgerufen");
		// Prepare HTTP-Header
		$out  = "GET /api/users/" . $this->user . " HTTP/1.1\r\n";
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
		return $this->isSuccess($data);
	}
}