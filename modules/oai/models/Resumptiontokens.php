<?php
/**
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the North Rhine-Westphalian Library Service Center,
 * the Cooperative Library Network Berlin-Brandenburg, the Saarland University
 * and State Library, the Saxon State Library - Dresden State and University
 * Library, the Bielefeld University Library and the University Library of
 * Hamburg University of Technology with funding from the German Research
 * Foundation and the European Regional Development Fund.
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
 * @author      Henning Gerhardt <henning.gerhardt@slub-dresden.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Handling (read, write) of resumption tokens
 */
class Oai_Model_Resumptiontokens {

    /**
     * Holds resumption path without trailing slash.
     *
     * @var string
     */
    private $_resumptionPath = null;

    /**
     * Holds resumption id
     *
     * @var string
     */
    private $_resumptionId = null;

    /**
     * Holds file prefix
     *
     * @var string
     */
    protected $_filePrefix = 'rs_';

    /**
     * Holds file extension without starting dot
     *
     * @var string
     */
    protected $_fileExtension = 'txt';

    /**
     * Generate a unique file name and resumption id for storing resumption token.
     * Double action because file name 8without prefix and file extension) 
     * and resumption id should be equal.
     *
     * @return filename Generated filename including path and file extension.
     */
    protected function generateResumptionName() {
        $fc = 0;

        // generate a unique partial name
        // return value of time() should be enough
        $uniqueId = time();

        $fileExtension = $this->_fileExtension;
        if (false === empty($fileExtension)) {
            $fileExtension = '.' . $fileExtension;
        }

        do {
            $uniqueName = sprintf('%s%05d', $uniqueId, $fc++);
            $file = $this->_resumptionPath . DIRECTORY_SEPARATOR . $this->_filePrefix . $uniqueName . $fileExtension;
        } while (true === file_exists($file));

        $this->_resumptionId = $uniqueName;

        return $file;
    }

    /**
     * Constructor of class
     *
     * @param $resPath (Optional) Initialise resumption path on create.
     *
     */
    public function __construct($resPath = null) {
        if (false === empty($resPath)) {
            $this->setResumptionPath($resPath);
        }
    }

    /**
     * Get resumption id after a successful writing of resumption file
     * or null if writing failed.
     *
     * @return string|null
     */
    public function getResumptionId() {
        return $this->_resumptionId;
    }

    /**
     * Return setted resumption path.
     *
     * @return string
     */
    public function getResumptionPath() {
        return $this->_resumptionPath;
    }

    /**
     * Returns a resumption token of a specific resumption id
     *
     * @return Oai_Model_Resumptiontoken|null Oai_Model_Resumptiontoken on success else null;
     */
    public function getResumptionToken($resId) {

        $token = null;

        $fileName = $this->_resumptionPath . DIRECTORY_SEPARATOR . $this->_filePrefix . $resId;
        if (false === empty($this->_fileExtension)) {
            $fileName .= '.' . $this->_fileExtension;
        }

        if (true === file_exists($fileName)) {

            $fileContents = file_get_contents($fileName);
            // if data is not unserializueabke an E_NOTICE will be triggerd and false returned
            // avoid this E_NOTICE
            $token = @unserialize($fileContents);
            if (false === ($token instanceof Oai_Model_Resumptiontoken)) {
                $token = null;
            }
        }

        return $token;

    }

    /**
     * Set resumption path where the resumption token files are stored.
     *
     * @throws Exception Thrown if directory operations failed.
     * @return void
     */
    public function setResumptionPath($resPath) {
        if (true === empty($resPath)) {
            throw new InvalidArgumentException('Path for resumption is empty. Non-empty value expected.');
        }

        // expanding all symbolic links and resolving references
        $realPath = realpath($resPath);

        if (false === is_dir($realPath)) {
            throw new Exception('Given resumption path "' . $resPath . '" (real path: "' . $realPath . '") is not a directory.');
        }

        if (false === is_writable($realPath)) {
            throw new Exception('Given resumption path "' . $resPath . '" (real path: "' . $realPath . '") is not writeable.');
        }

        $this->_resumptionPath = $realPath;
    }

    /**
     * Store a resumption token
     *
     * @param Oai_Model_Resumptiontoken $token Token to store.
     * @throws Exception Thrown on file operation error.
     * @return void
     */
    public function storeResumptionToken(Oai_Model_Resumptiontoken $token) {

        $fileName = $this->generateResumptionName();

        $file = fopen($fileName, 'w+');
        if (false === $file) {
            throw new Exception('Could not open file "' . $fileName . '" for writing!');
        }

        $serialToken = serialize($token);

        if (false === fwrite($file, $serialToken)) {
            throw new Exception('Could not write file "' . $fileName . '"!');
        }

        if (false === fclose($file)) {
            throw new Exception('Could not close file "' . $fileName . '"!');
        }

        $token->setResumptionId($this->_resumptionId);

    }

    /**
     * Validate a resumption id on an exisiting resumption token.
     *
     * @param $resId
     * @return boolean
     */
    public function validateResumptionToken($resId) {
        $result = false;

        $token = $this->getResumptionToken($resId);

        if (false === is_null($token)) {
            $result = true;
        }

        return $result;
    }

}

