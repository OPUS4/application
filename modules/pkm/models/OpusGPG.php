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

/**
 * GPG-Module of Opus
 * based on PEAR/Crypt_GPG
 */
 
class OpusGPG extends Crypt_GPG 
{
	
	public function __construct() 
	{
		$config = new Zend_Config_Ini('../config/config.ini');

		try {
			parent::__construct(array('homedir' => $config->gpg->keyring->path, 'binary' => $config->gpg->path, 'debug' => false));
		}
		catch (Exception $e) {
			throw $e;
		}
	}

    public function getMasterkey() 
    {
    	$config = new Zend_Config_Ini('../config/config.ini');
    	
    	foreach ($this->getKeys() as $key) 
    	{
    		if (strtoupper($config->gpg->masterkey->id) === substr($key->getPrimaryKey()->getId(), -(strlen($config->gpg->masterkey->id))))
    		{
    			return $key;
    		}
    	}
    	
    	return false;
    }
	
    public function verifyPublication($id) 
    {
    	$doc = new Opus_Document($id);
    	
    	$filepath = '../workspace/files/' . $id . '/';

    	$result = array();
    	
    	foreach ($doc->getFile() as $file) 
    	{
    		$hashes = $file->getHashValue();
    		$result[$file->getPathName()] = array();
    		if (true === is_array($hashes))
    		{
    		    foreach ($hashes as $hash)
    		    {
    			    if ($hash->getType() === 'gpg')
    			    {
    				    $result[$file->getPathName()][] = $this->verifyFile($filepath . $file->getPathName(), $hash->getValue());
    			    }
    		    }
    		}
    		else
    		{
    			if ($hashes->getType() === 'gpg')
    			{
    			    $result[$file->getPathName()][] = $this->verifyFile($filepath . $file->getPathName(), $hashes->getValue());
    			}    			
    		}
    	}
    	return $result;
    }





	/** 
	 * verify prüft zu einer angegebenen Datei eine Signatur
     * Die Signatur muss entweder als .asc oder .sig-Datei im gleichen Verzeichnis wie die signierte Datei liegen
     * und den gleichen Dateinamen haben (plus Endung)
     * Return-Codes:
     * 0: Bad signature
     * 1: Good signature
     * 2: No signature
     * 3: Defekte Signaturdatei
	*/
	public function oldVerify () 
	{   
		if (!$this->sigfile || !file_exists($this->sigfile))
    	{
    		// Keine Signaturdatei vorhanden
			return 2;
    	}

       	exec($this->gpg." ".$this->gpg_home." --verify ".$this->sigfile." ".$this->signedfile." 2> ".$this->gpg_tmp."signaturcheck", $keyinfo, $keyinforeturn);
 
    	# Am Returncode Status der Signatur festmachen
    	switch ($keyinforeturn) {
	        case 0: 
			    return 1; 
	    		#echo "<font color=\"green\">Signatur g&uuml;ltig!</font>"; 
	    		break;
        	case 1: 
	    		return 0;
	    		#echo "<font color=\"red\">Signatur ung&uuml;ltig!</font>"; 
	    		break;
        	default: 
	    		return 3;
	    		#echo "<font color=\"red\">Fehler bei der Signaturpr&uuml;fung; evtl. korrupte Signaturdatei!</font>"; 
	    		break;
    	}
	}

 	/**
	 * Verifiziert die bibliothekseigene Signatur
	 */
	function biblVerify()
	{
		$opus = new OPUS(dirname(__FILE__).'/opus.conf');
		$db = $opus->value("db");
		$sock = $opus->connect();
		$opus->select_db($db);

		$signature_pfad = $opus->value("signature_pfad");
		$signature_url = $opus->value("signature_url");
		
		$filenameArray = split("/", $this->signedfile);
		$src_opus = $filenameArray[(count($filenameArray)-3)];
		$extension = $filenameArray[(count($filenameArray)-2)];
		$filename = $filenameArray[(count($filenameArray)-1)];

		$key = $opus->query("SELECT signature_file FROM opus_signatures WHERE source_opus = '".$src_opus."' AND filename = '".$extension."/".mysql_real_escape_string($filename)."' AND signature_type = 'bibl'");
		$currentKey = $opus->fetch_row($key);

		if ($currentKey[0])
		{
			$this->sigfile = $signature_pfad."/".$src_opus."/".$currentKey[0];
			$this->sigfile_url = str_replace($signature_pfad, $signature_url, $this->sigfile);
		}
		else 
		{
			// Keine Signatur registriert
			return 2;
		}
		
		return $this->verify();
	} 

 	/**
	 * Verifiziert die Autorensignatur oder eine beliebige übergebene
	 */
	function authorVerify($sigfile = 0)
	{
		$opus = new OPUS(dirname(__FILE__).'/opus.conf');
		$db = $opus->value("db");
		$sock = $opus->connect();
		$opus->select_db($db);

		$signature_pfad = $opus->value("signature_pfad");
		$signature_url = $opus->value("signature_url");

		$filenameArray = split("/", $this->signedfile);
		$src_opus = $filenameArray[(count($filenameArray)-3)];
		$extension = $filenameArray[(count($filenameArray)-2)];
		$filename = $filenameArray[(count($filenameArray)-1)];

		if (!$sigfile) {
			$key = $opus->query("SELECT signature_file FROM opus_signatures WHERE source_opus = '".$src_opus."' AND filename = '".$extension."/".mysql_real_escape_string($filename)."' AND signature_type = 'author'");
			$currentKey = $opus->fetch_row($key);
			if ($currentKey[0])
			{
				$this->sigfile = $signature_pfad."/".$src_opus."/".$currentKey[0];
			}
		}
		else 
		{
			$this->sigfile = $sigfile;
		}
    	if ($this->sigfile)
    	{
	    	$this->sigfile_url = str_replace($signature_pfad, $signature_url, $this->sigfile);
    	}
    	
    	return $this->verify();
	}
	
 	/**
	 * Gibt die Rückgabenachricht von GPG zurück
	 */
	function getGpgMessage()
	{
		@$fp = file($this->gpg_tmp."signaturcheck");
    	for ($c=0; $fp[$c]; $c++) {
        	if (ereg("@", $fp[$c])) return $fp[$c];
    	}
    	@unlink($this->gpg_tmp."signaturcheck");
    	return '';
	} 

 	/**
	 * Gibt den Link zum public Userkey zurück, sofern dieser verfügbar ist
	 */
	function getFingerprintLink()
	{
		$opus = new OPUS(dirname(__FILE__).'/opus.conf');
		$db = $opus->value("db");
		$sock = $opus->connect();
		$opus->select_db($db);
		
		$filenameArray = split("/", $this->signedfile);
		$src_opus = $filenameArray[(count($filenameArray)-3)];
		$extension = $filenameArray[(count($filenameArray)-2)];
		$filename = $filenameArray[(count($filenameArray)-1)];
		
		$keyfp = $opus->query("SELECT signature_key FROM opus_signatures WHERE source_opus = '".$src_opus."' AND filename = '".$extension."/".mysql_real_escape_string($filename)."'");
		$keyfingerprint = $opus->fetch_row($keyfp);
		$keyfp = new GPGKey();
		$keyfp->id = $keyfingerprint[0];
		if ($keyfp->isExported() === true) {
	    	return ($keyfp->keyurl."/".$keyfp->getFingerprint().".asc");
		}
		return false;
	}

 	/**
	 * Signiert eine Datei 
	 * Bei übergebenem Parameter passwd wird gegen dieses Passwort gecheckt, 
	 * ansonsten wird versucht, über eine externe  Passwortdatei zu verifizieren  
	 */
	function oldSignFile($passwd = 0)
	{
		$opus = new OPUS(dirname(__FILE__).'/opus.conf');
		$db = $opus->value("db");
		$bibkey_id = $opus->value("bibkey_id");
		$sock = $opus->connect();
		$opus->select_db($db);
		$signature_pfad = $opus->value("signature_pfad");

		$filenameArray = split("/", $this->signedfile);
		$src_opus = $filenameArray[(count($filenameArray)-3)];
		$extension = $filenameArray[(count($filenameArray)-2)];
		$filename = $filenameArray[(count($filenameArray)-1)];

        copy ($this->signedfile, $this->signedfile.".bibl");
#echo $this->gpg." --batch --yes ".$this->gpg_home." -ba -u 0x$bibkey_id --passphrase-fd 0 < ".$this->gpg_pass." '".$this->signedfile.".bibl";
		if ($this->gpg_pass) {
			exec($this->gpg." --batch --yes ".$this->gpg_home." -ba -u 0x$bibkey_id --passphrase-fd 0 < ".$this->gpg_pass." '".$this->signedfile.".bibl'", $keyinfo, $keyinforeturn);
		}
		else
		{
			exec("echo ".escapeshellarg($passwd)." | ".$this->gpg." --batch --yes ".$this->gpg_home." -ba -u 0x$bibkey_id --passphrase-fd 0 '".$this->signedfile.".bibl'", $keyinfo, $keyinforeturn);
		}
		unlink($this->signedfile.".bibl");
		
        # Signaturdatei ins Signatur-Verzeichnis verschieben
        if (!file_exists($signature_pfad."/".$src_opus))
        {
        	mkdir($signature_pfad."/".$src_opus);
        }
        if (!file_exists($signature_pfad."/".$src_opus."/".$extension))
        {
        	mkdir($signature_pfad."/".$src_opus."/".$extension);
        }
        copy ($this->signedfile.".bibl.asc", $signature_pfad."/".$src_opus."/".$extension."/".$filename.".bibl.asc");
        unlink($this->signedfile.".bibl.asc");
                
		$this->registerSignature();
	}

 	/**
	 * Registriert eine Signatur in der Signaturentabelle
	 * @param String owner Von wem ist die Signatur - Bie Autoren hier bitte "author" angeben, bei Bibliotheken nichts
	 * @param String sigfile Dateiname (NUR der Name mit Endung) der Signaturdatei
	 * @param String keyid ID des Signierschlüssels (nicht kompletter Fingerprint!) - nur bei Autorensignatur angeben
	 */
	function registerSignature($owner = "bibl", $sigfile = false, $keyid = "")
	{
		$opus = new OPUS(dirname(__FILE__).'/opus.conf');
		$db = $opus->value("db");
		$sock = $opus->connect();
		$opus->select_db($db);
		$signature_pfad = $opus->value("signature_pfad");

		$filenameArray = split("/", $this->signedfile);
		$src_opus = $filenameArray[(count($filenameArray)-3)];
		$extension = $filenameArray[(count($filenameArray)-2)];
		$filename = $filenameArray[(count($filenameArray)-1)];

		if ($sigfile !== false)
		{
			$this->sigfile = $signature_pfad."/".$src_opus."/".$extension."/".$sigfile;
			$key_id = $keyid;
		}
		else 
		{
			$this->sigfile = $signature_pfad."/".$src_opus."/".$extension."/".$filename;
			$sigfile = $filename.".bibl.asc";
			$key_id = $opus->value("bibkey_id");
		}

        # In der Signaturtabelle vermerken
        $register_sig = $opus->query("INSERT INTO opus_signatures (source_opus, filename, signature_file, signature_key, signature_type) " .
        		"VALUES ('".$src_opus."', '".$extension."/".mysql_real_escape_string($filename)."', '".$extension."/".mysql_real_escape_string($sigfile)."', '".$key_id."', '".$owner."')");
	}

 	/**
	 * Holt den Fingerprint des Autors der signierten Publikation
	 */
	function getAuthorFingerprint()
	{
		$opus = new OPUS(dirname(__FILE__).'/opus.conf');
		$db = $opus->value("db");
		$sock = $opus->connect();
		$opus->select_db($db);
		
		$filenameArray = split("/", $this->signedfile);
		$src_opus = $filenameArray[(count($filenameArray)-3)];
		$extension = $filenameArray[(count($filenameArray)-2)];
		$filename = $filenameArray[(count($filenameArray)-1)];

        # Fingerprint raussuchen zwecks Export
        $res = $opus->query("SELECT signature_key FROM opus_signatures WHERE source_opus='".$src_opus."'
            AND signature_type = 'author' GROUP BY signature_key");
        $krow = $opus->fetch_row($res);
	}
}