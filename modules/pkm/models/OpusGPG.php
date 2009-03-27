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
	
    /**
     * Construct the Crypt_GPG-Object
     * and set the paths necessary to do some operation
     * The paths are taken from config file and do not need to be set by parameter
     * 
     * @throws Crypt_GPG_Exception When the parent object cant get built successfully 
     * @return void
     */
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

    /**
     * Get the internally used key (system key/masterkey)
     * The key is autodetected (it has to have a private key and should not be expired)
     * 
     * @return Crypt_GPG_Key System key (false if there is no system key)
     */
    public function getMasterkey() 
    {
    	foreach ($this->getKeys() as $key) 
    	{
    		// System key (masterkey) autodetection
    		// check if there is a private key for this key in the keyring, 
    		// take the first private key, that is not expired as system key
    		if ($key->getPrimaryKey()->hasPrivate() === true && (0 === $key->getPrimaryKey()->getExpirationDate() || $key->getPrimaryKey()->getExpirationDate() > time()))
    		{
    			return $key;
    		}
    	}
    	
    	return false;
    }
	
    /**
     * Verifies all signatures of any file of a given publication
     *
     * @param integer $id ID of the publication that should get verified 
     * @return array Associative array with filenames as index and all Crypt_GPG_Signatures inside another array
     */
    public function verifyPublication($id) 
    {
    	$doc = new Opus_Document($id);
    	
    	$result = array();
    	
    	foreach ($doc->getFile() as $file) 
    	{
    		$result[$file->getPathName()] = $this->verifyPublicationFile($file);
    	}
    	return $result;
    }

    /**
     * Removes a key from the keyring
     * If the system key is the one to be removed, only the private key is deleted
     *
     * @param string $fingerprint Fingerprint-ID of the key that should be removed 
     * @return void
     */
    public function disableKey($fingerprint) 
    {
        if ($this->getMasterkey() !== false)
        {
            if ($this->getMasterkey()->getPrimaryKey()->getFingerprint() === $fingerprint)
            {
                $this->deletePrivateKey($fingerprint);
            }
        }
    }

    /**
     * Removes a key from the keyring
     *
     * @param string $fingerprint Fingerprint-ID of the key that should be removed 
     * @return void
     */
    public function deleteKey($fingerprint) 
    {
        try {
            $this->deletePublicKey($fingerprint);
        }
        catch (Crypt_GPG_DeletePrivateKeyException $e) {
        	$this->deletePrivateKey($fingerprint);
        	$this->deletePublicKey($fingerprint);
        }
    }

    /**
     * Verifies all signatures of a given file
     *
     * @param Opus_File $file File that should get verified 
     * @return array Associative array with filenames as index and all Crypt_GPG_Signatures inside another array
     */
    public function verifyPublicationFile($file)
    {
    		// FIXME: hardcoded path
    		$filepath = '../workspace/files/' . $file->getDocumentId() . '/';    		
    		$hashes = $file->getHashValue();
    		$result = array();
    		if (true === is_array($hashes))
    		{
    		    foreach ($hashes as $hash)
    		    {
    			    if ($hash->getType() === 'gpg')
    			    {
    				    $result[] = $this->verifyFile($filepath . $file->getPathName(), $hash->getValue());
    			    }
    		    }
    		}
    		else
    		{
    			if ($hashes->getType() === 'gpg')
    			{
    			    $result[] = $this->verifyFile($filepath . $file->getPathName(), $hashes->getValue());
    			}    			
    		}
    		
    		return $result;
    }

    /**
     * Signs a given file
     *
     * @param Opus_File $file File that should get signed
     * @param string    $password Passphrase for the internal key
     * @throws Exception when no internal key is found
     * @return void
     */
    public function signPublicationFile($file, $password)
    {
    		if ($this->getMasterkey() === false) {
    			throw new Exception('No internal key for this repository!');
    		}
    		
    		$this->addSignKey($this->getMasterkey(), $password);
    		
    		// FIXME: hardcoded path
    		$filepath = '../workspace/files/' . $file->getDocumentId() . '/';    		
    		
    		$doc = new Opus_Document($file->getDocumentId());
    		
    		foreach ($doc->getFile() as $f)
    		{
    			if ($f->getPathName() === $file->getPathName())
    			{
    				$docfile = $f;
    			}
    		}
    		
    		$signature = new Opus_HashValues();
    		$signature->setType('gpg');
    		$signature->setValue($this->signFile($filepath . $file->getPathName(), null, Crypt_GPG::SIGN_MODE_DETACHED));
    		
    		$docfile->addHashValue($signature);
    		
    		print_r($docfile->toXml()->saveXml());
    		
    		$doc->store();
    }

    /* The following methods are old methods used in OPUS 3.x, they should get replaced */

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
}