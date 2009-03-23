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
 * @package     Module_Pkm
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class OpusKey
{
	var $gpg;
	var	$gpg_home;
	var $gpg_pass;
	var $gpg_tmp;
	var $bibkeys;
	
	var $keypath;
	var $keyurl;
	
	var $id;
	var $owner;
	var $owner_email;
	var $fingerprint;
	var $expired;
	
	/**
	 * Konstruktor
	 */
	 function GPGKey()
	 {
		$opus = new OPUS(dirname(__FILE__)."/opus.conf");
		$db = $opus->value("db");
		$sock = $opus->connect();
		$opus->select_db($db);
		
		// Holen der Systempfade aus der opus.conf
		$this->gpg = $opus->value("gpg");
		$this->gpg_home = $opus->value("gpg_home");
		$this->gpg_pass = $opus->value("gpg_pass");
		$this->gpg_tmp = $opus->value("gpg_tmp");
		
		$this->bibkeys = array();
		array_push($this->bibkeys, $opus->value("bibkey_id"));
		$hist_keys = split("\ ", $opus->value("hist_keys"));
		foreach ($hist_keys as $histkey)
		{
			array_push($this->bibkeys, $histkey);
		}
		$this->keyurls = array();
		array_push($this->keyurls, $opus->value("bibkey_url"));
		$hist_keyurls = split("\ ", $opus->value("hist_key_urls"));
		foreach ($hist_keyurls as $histkeyurl)
		{
			array_push($this->keyurls, $histkeyurl);
		}
		
		$this->keypath = $opus->value("signature_pfad")."/keys";
		$this->keyurl = $opus->value("signature_url")."/keys";
		
	 }

 	/**
	 * Listet alle Schlüssel aus dem Schlüsselbund des Webservers auf
	 */
	function listKeys()
	{
		exec($this->gpg." ".$this->gpg_home." --list-keys", $keyexport, $keyexportreturn);
		$ergebnisarray = array();
		foreach ($keyexport as $line)
		{
			// Wenn das aktuelle Key-Objekt schon beide Werte hat, muss ein neues Objekt instanziiert werden
			// und das alte Objekt wird auf den Rückgabearray geschaufelt
			// Auch wenn das Objekt noch völlig uninstanziiert ist, wird ein neues Objekt angefangen
			if (($k->id && $k->owner) || (!$k->id && !$k->owner))
			{
				$k = new GPGKey();
			}
			if (ereg("^pub", $line))
			{
				$check_expired = strstr($line, "[expired:");
				if ($check_expired) $k->expired = true;
				$find_id = split("/", $line);
				$find_id2 = split("\ ", $find_id[1]);
				$k->id = $find_id2[0];
			}
			if (ereg("^uid", $line)) 
			{
				$owner = ereg_replace("^(uid[[:space:]]*)", "", $line);
				$owner = ereg_replace("(<[[:print:]]*>)", "", $owner);
				$owner_email_start = strpos($line, "<");
				$owner_email = substr($line, ($owner_email_start+1));
				$owner_email = ereg_replace(">", "", $owner_email);
				$k->owner = $owner;
				$k->owner_email = $owner_email;
			}
			if ($k->id && $k->owner)
			{
				array_push($ergebnisarray, $k);
			}
		}
		return $ergebnisarray;
	}

 	/**
	 * Prüft, ob ein Schlüssel signiert ist von der Bibliothek
	 * @return Boolean
	 */
	function hasBiblSig()
	{
		exec($this->gpg." ".$this->gpg_home." --list-sigs 0x".$this->id, $keyexport, $keyexportreturn);
		foreach ($keyexport as $line)
		{
			for ($n = 0; $this->bibkeys[$n]; $n++)
			{
				if (ereg("^sig[[:space:]3X]*".$this->bibkeys[$n], $line)) return true;
			}
		}		
		return false;
	}

 	/**
	 * Holt den vollständigen Fingerprint zu einem Schlüssel
	 */
	function getFingerprint()
	{
		exec($this->gpg." ".$this->gpg_home." --fingerprint 0x".$this->id, $keyexport, $keyexportreturn);
		$fp = false;
		foreach ($keyexport as $line)
		{
			if (ereg("Key\ fingerprint", $line))
			{
				$kfp = split("=", $line);
				$fp = ereg_replace("\ ", "", $kfp[1]);
			}
		}		
        return $fp;
	}

 	/**
	 * Exportiert einen Schlüssel
	 */
	function export()
	{
		exec($this->gpg." ".$this->gpg_home." -a --export ".$this->getFingerprint()." > ".$this->keypath."/".$this->getFingerprint().".asc", $keyexport, $keyexportreturn);
		if ($keyexportreturn == 0) return 1;
        return 0;
	}

 	/**
	 * Löscht einen Schlüssel
	 */
	function delete()
	{
		if ($this->isExported() === true)
		{
			unlink ($this->keypath."/".$this->getFingerprint().".asc");
		}
		exec($this->gpg." --batch --yes ".$this->gpg_home." --delete-key ".$this->getFingerprint(), $keyexport, $keyexportreturn);
		if ($keyexportreturn == 0) return true;
        return false;
	}

 	/**
	 * Prüft, ob dieser Schlüssel bereits exportiert vorliegt
	 */
	function isExported()
	{
		$opus = new OPUS(dirname(__FILE__).'/opus.conf');
		$db = $opus->value("db");
		$bibkey_id = $opus->value("bibkey_id");
		$bibkey_url = $opus->value("bibkey_url");
		if (file_exists($this->keypath."/".$this->getFingerprint().".asc")) 
		{
			return true;
		}
		if ($bibkey_id == $this->id)
		{
			return true;
		}
        return false;
	}

 	/**
	 * Ermittelt den zum Schlüssel gehörenden URL
	 */
	function getKeyUrl()
	{
		$opus = new OPUS(dirname(__FILE__).'/opus.conf');
		$bibkey_id = $opus->value("bibkey_id");
		$bibkey_url = $opus->value("bibkey_url");
        $this->keypath = $opus->value("signature_pfad")."/keys";
        $this->keyurl = $opus->value("signature_url")."/keys";

		if (file_exists($this->keypath."/".$this->getFingerprint().".asc")) 
		{
			return $this->keyurl .= "/".$this->getFingerprint().".asc";
		}
		
		$this->keyurl = false;
		if ($bibkey_id == $this->id)
		{
			return $this->keyurl = $bibkey_url;
		}
		if (in_array($this->id, $this->bibkeys))
		{
			// Position im Array herausfinden
			for ($n = 1; $n <= count($this->bibkeys); $n++ )
			{
				if ($this->bibkeys[$n] == $this->id)
				{
					$position = $n;
				}
			}
			return $this->keyurl = $this->keyurls[$position];
		}
        return $this->keyurl;
	}
	
 	/**
	 * Signiert einen Schlüssel
	 */
	function signKey($passwd = 0)
	{
		$opus = new OPUS(dirname(__FILE__).'/opus.conf');
		$db = $opus->value("db");
		$bibkey_id = $opus->value("bibkey_id");
		$sock = $opus->connect();
		$opus->select_db($db);

		if ($this->gpg_pass) {
			exec("echo \"y\" | ".$this->gpg." --batch --yes ".$this->gpg_home." -u 0x$bibkey_id --sign-key --passphrase-fd 0 < ".$this->gpg_pass." --command-fd 0 ".$this->getFingerprint(), $keysign, $keysignreturn);
		}
		else
		{
			exec("echo ".escapeshellarg($passwd)." | ".$this->gpg." --batch --yes ".$this->gpg_home." -u 0x$bibkey_id --sign-key --passphrase-fd 0 --command-fd 0 ".$this->getFingerprint(), $keysign, $keysignreturn);
		}
		if ($keysigntreturn == 0) return true;
        return false;
	}

 	/**
	 * Holt alle mit dem aktuellen Schlüssel signierten Publikationen und gibt die IDs zurück
	 */
	function getSignedPublications()
	{
		$opus = new OPUS(dirname(__FILE__).'/opus.conf');
		$db = $opus->value("db");
		$sock = $opus->connect();
		$opus->select_db($db);
		
        # Fingerprint raussuchen zwecks Export
        $res = $opus->query("SELECT source_opus FROM opus_signatures WHERE signature_key='".$this->id."' GROUP BY source_opus");
		$result_array = array();
		while ($krow = $opus->fetch_row($res))
		{
			array_push($result_array, $krow[0]);
		}
        return $result_array;
	}

 	/**
	 * Importiert einen Key in den lokalen Schlüsselbund 
	 */
	function import($keyfile) {
		exec($this->gpg." ".$this->gpg_home." --import ".$keyfile, $keyimport, $keyimportreturn);
		if ($keyimportreturn == 0) return true;
        return false;
	}

 	/**
	 * Importiert einen Key in den lokalen Schlüsselbund
	 * @param FILE-Array identifier uebergebenes FILE-Array aus PHP  
	 */
	function checkKeyfileExtension($identifier) {
    	$endung = explode(".", $identifier['name']);
    	$last = count($endung) - 1;
    	$ext = strtolower($endung[$last]);
	    if ($ext == "asc") 
    	{
			return true;
    	}
    	return false;
	}
}
 