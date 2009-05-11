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
    			    if (substr($hash->getType(), 0, 3) === 'gpg')
    			    {
    				    try
    				    {
    				        $result[] = $this->verifyFile($filepath . $file->getPathName(), $hash->getValue());
    				    }
    				    catch (Exception $e) {
    				    	$result[] = array($e->getMessage());
    				    }
    			    }
    		    }
    		}
    		else
    		{
    			if (substr($hashes->getType(), 0, 3) === 'gpg')
    			{
    			    try {
    			        $result[] = $this->verifyFile($filepath . $file->getPathName(), $hashes->getValue());
    			    }
    			    catch (Exception $e) {
    			    	$result[] = array($e->getMessage());
    			    }
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
    		$key_id = $this->getMasterkey()->getPrimaryKey()->getId();

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
    		$signature->setType('gpg-' . $key_id);
    		$signature->setValue($this->signFile($filepath . $file->getPathName(), null, Crypt_GPG::SIGN_MODE_DETACHED));

    		$docfile->addHashValue($signature);

    		#print_r($docfile->toXml()->saveXml());

    		$doc->store();
    }
}