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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Handling (read, write) of resumption tokens
 */
class Oai_Model_Resumptiontokens
{
    /** @var string Holds resumption path without trailing slash. */
    private $resumptionPath;

    /** @var string Holds resumption id */
    private $resumptionId;

    /** @var string Holds file prefix */
    protected $filePrefix = 'rs_';

    /** @var string Holds file extension without starting dot */
    protected $fileExtension = 'txt';

    /**
     * Generate a unique file name and resumption id for storing resumption token.
     * Double action because file name 8without prefix and file extension)
     * and resumption id should be equal.
     *
     * @return string filename Generated filename including path and file extension.
     */
    protected function generateResumptionName()
    {
        $fc = 0;

        // generate a unique partial name
        // return value of time() should be enough
        $uniqueId = time();

        $fileExtension = $this->fileExtension;
        if (false === empty($fileExtension)) {
            $fileExtension = '.' . $fileExtension;
        }

        do {
            $uniqueName = sprintf('%s%05d', $uniqueId, $fc++);
            $file       = $this->resumptionPath . DIRECTORY_SEPARATOR . $this->filePrefix . $uniqueName . $fileExtension;
        } while (true === is_readable($file));

        $this->resumptionId = $uniqueName;

        return $file;
    }

    /**
     * Constructor of class
     *
     * @param string|null $resPath (Optional) Initialise resumption path on create.
     */
    public function __construct($resPath = null)
    {
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
    public function getResumptionId()
    {
        return $this->resumptionId;
    }

    /**
     * Return setted resumption path.
     *
     * @return string
     */
    public function getResumptionPath()
    {
        return $this->resumptionPath;
    }

    /**
     * Returns a resumption token of a specific resumption id
     *
     * @param string $resId
     * @return Oai_Model_Resumptiontoken|null Oai_Model_Resumptiontoken on success else null;
     */
    public function getResumptionToken($resId)
    {
        $token = null;

        $fileName = $this->resumptionPath . DIRECTORY_SEPARATOR . $this->filePrefix . $resId;
        if (false === empty($this->fileExtension)) {
            $fileName .= '.' . $this->fileExtension;
        }

        if (true === is_readable($fileName)) {
            $fileContents = file_get_contents($fileName);
            // if data is not unserializueabke an E_NOTICE will be triggerd and false returned
            // avoid this E_NOTICE
            $token = @unserialize($fileContents);
            if (false === $token instanceof Oai_Model_Resumptiontoken) {
                $token = null;
            }
        }

        return $token;
    }

    /**
     * Set resumption path where the resumption token files are stored.
     *
     * @param string $resPath
     * @throws Oai_Model_ResumptionTokenException Thrown if directory operations failed.
     */
    public function setResumptionPath($resPath)
    {
        // expanding all symbolic links and resolving references
        $realPath = realpath($resPath);

        if (empty($realPath) || false === is_dir($realPath)) {
            throw new Oai_Model_ResumptionTokenException(
                'Given resumption path "' . $resPath . '" (real path: "' . $realPath . '") is not a directory.'
            );
        }

        if (false === is_writable($realPath)) {
            throw new Oai_Model_ResumptionTokenException(
                'Given resumption path "' . $resPath . '" (real path: "' . $realPath . '") is not writeable.'
            );
        }

        $this->resumptionPath = $realPath;
    }

    /**
     * Store a resumption token
     *
     * @param Oai_Model_Resumptiontoken $token Token to store.
     * @throws Oai_Model_ResumptionTokenException Thrown on file operation error.
     */
    public function storeResumptionToken(Oai_Model_Resumptiontoken $token)
    {
        $fileName = $this->generateResumptionName();

        $file = fopen($fileName, 'w+');
        if (false === $file) {
            throw new Oai_Model_ResumptionTokenException('Could not open file "' . $fileName . '" for writing!');
        }

        $serialToken = serialize($token);

        if (false === fwrite($file, $serialToken)) {
            throw new Oai_Model_ResumptionTokenException('Could not write file "' . $fileName . '"!');
        }

        if (false === fclose($file)) {
            throw new Oai_Model_ResumptionTokenException('Could not close file "' . $fileName . '"!');
        }

        $token->setResumptionId($this->resumptionId);
    }

    /**
     * Validate a resumption id on an existing resumption token.
     *
     * @param string $resId
     * @return bool
     */
    public function validateResumptionToken($resId)
    {
        return $this->getResumptionToken($resId) !== null;
    }
}
