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
 */

/**
 * Klasse fuer die Handhabung von Datei-Hashes.
 *
 * @category    Application
 * @package     Admin_Model
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_Model_Hash
{

    private $_hash = null;

    /**
     * @var Opus_File
     */
    private $_file = null;

    public function __construct($file, $hash)
    {
        $this->_hash = $hash;
        $this->_file = $file;
    }

    public function getHashType()
    {
        return $this->_hash->getType();
    }

    public function getSignatureType()
    {
        return substr($this->getHashType(), 0, 3);
    }

    public function getSoll()
    {
        return $this->_hash->getValue();
    }

    /**
     * @return boolean
     */
    public function canVerify()
    {
        return $this->_file->canVerify();
    }

    /**
     * @return boolean
     */
    public function checkFilePermission()
    {
        return $this->_file->isReadable();
    }

    /**
     * @return boolean
     */
    public function checkFileExists()
    {
        return $this->_file->exists();
    }

    public function getIst()
    {
        if ($this->_file->exists() && $this->getSignatureType() !== 'gpg' && $this->_file->canVerify()) {
            return $this->_file->getRealHash($this->getHashType());
        } else {
            return null;
        }
    }
}
