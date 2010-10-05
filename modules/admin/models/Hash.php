<?php
/*
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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Model_Hash {

    private $hash = null;

    private $file = null;

    public function __construct($file, $hash) {
        $this->hash = $hash;
        $this->file = $file;
    }

    public function getHashType() {
        return $this->hash->getType();
    }

    public function getSignatureType() {
        return substr($this->getHashType(), 0, 3);
    }

    public function getSoll() {
        return $this->hash->getValue();
    }

    public function getIst() {
        if (!($this->getSignatureType() === 'gpg') && !($this->_isGpgEnabled())) {
            if (true === $this->file->canVerify()) {
                return $this->file->getRealHash($hashType);
            }
            else {
                return 0;
            }
        }
    }

    /**
     * try {
     * } catch (Exception $e) {
     *     $this->view->verifyResult[$fileNames[$fi]] = array('result' => array($e->getMessage()), 'signature' => $hashSoll[$fi][$hi]);
     * }
     */
    public function getVerified() {
        $verified = array();

        $gpg = new Opus_GPG();

        $verifyResult = $gpg->verifyPublicationFile($this->file);
        foreach($verifyResult as $verifiedArray) {
            foreach($verifiedArray as $index => $verificationResult) {
                if ($index === 'result') {
                    foreach ($verificationResult as $result) {
                        // Show key used for signature
                        if (true === is_object($result) && get_class($result) === 'Crypt_GPG_Signature') {
                            $verified[] = $result->getKeyFingerprint();
                        }
                    }
                }
            }
        }

        return $verified;
    }


    /**
     *
     * @return <type>
     *
     * Check if GPG is used
     * GPG is not used
     * by default
     * if admin has disabled it in config
     * if no masterkey has been found
     * TODO if GPG is not configured correctly
     *
     * FIXME move into controller helper
     */
    protected function _isGpgEnabled() {
        if (isset($config->gpg->enable->admin)) {
            return ($config->gpg->enable->admin === 1) ? true : false;
        }
        else {
            return false;
        }
    }

}

?>
