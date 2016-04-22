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
 * @package     Module_Oai
 * @author      Sascha Szott <szott@zib.de>
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Oai_Model_Container
 *
 * TODO document class
 */
class Oai_Model_Container extends Application_Model_Abstract {

    /**
     * OPUS document identifier.
     * @var int
     */
    private $_docId;

    /**
     * @var Opus_Document
     */
    private $_doc;

    /**
     * @var Application_Configuration
     */
    private $_appConfig;

    /**
     * Oai_Model_Container constructor.
     * @param $docId
     * @param null $logger
     */
    public function  __construct($docId) {
        $this->_doc = $this->validateId($docId);
        $this->_docId = $this->_doc->getId();
        $this->_appConfig = Application_Configuration::getInstance();
    }

    /**
     * Writes log message with additional class information.
     * @param $message
     */
    private function logErrorMessage($message) {
        $this->getLogger()->err(__CLASS__ . ': ' . $message);
    }

    /**
     *
     * @param string $docId
     * @return Opus_Document returns valid Opus_Document if docId is valid, otherwise throws an Oai_Model_Exception
     * @throws Oai_Model_Exception throws Oai_Model_Exception if the given docId is invalid
     *
     * TODO centralize this function (is used in several controllers)
     */
    private function validateId($docId) {
        if (is_null($docId)) {
            $this->logErrorMessage('missing parameter docId');
            throw new Oai_Model_Exception('missing parameter docId');
        }

        if (!is_numeric($docId)) {
            $this->logErrorMessage('given document id is not valid');
            throw new Oai_Model_Exception('invalid value for parameter docId');
        }

        try {
            return new Opus_Document($docId);
        }
        catch (Opus_Model_NotFoundException $e) {
            $this->logErrorMessage('document with id ' . $docId . ' does not exist');
            throw new Oai_Model_Exception('requested docId does not exist');
        }
    }

    /**
     * Returns all associated Opus_File objects that are visible in OAI and accessible by user
     * @return array Accessible Opus_File objects
     *
     * TODO check embargo date
     * TODO merge access checks with code for deliver controller
     */
    public function getAccessibleFiles() {
        $realm = Opus_Security_Realm::getInstance();

        // admins sollen immer durchgelassen werden, nutzer nur wenn das doc im publizierten Zustand ist
        if (!$realm->skipSecurityChecks()) {
            // kein administrator

            // PUBLISHED Dokumente sind immer verfügbar (Zugriff auf Modul kann eingeschränkt sein)
            if ($this->_doc->getServerState() !== 'published') {
                // Dokument nicht published

                if (!$realm->checkDocument($this->_docId)) {
                    // Dokument ist nicht verfügbar für aktuellen Nutzer
                    $this->logErrorMessage(
                        'document id =' . $this->_docId
                        . ' is not published and access is not allowed for current user'
                    );
                    throw new Oai_Model_Exception('access to requested document is forbidden');
                }
            }

            if ($this->_doc->hasEmbargoPassed() === false) {
                if (!$realm->checkDocument($this->_docId)) {
                    // Dokument ist nicht verfügbar für aktuellen Nutzer
                    $this->logErrorMessage(
                        'document id =' . $this->_docId
                        . ' is not embargoed and access is not allowed for current user'
                    );
                    throw new Oai_Model_Exception('access to requested document files is embargoed');
                }

            }
        }

        $files = array();
        $filesToCheck = $this->_doc->getFile();
        /* @var $file Opus_File */
        foreach ($filesToCheck as $file) {
            $filename = $this->_appConfig->getFilesPath() . $this->_docId . DIRECTORY_SEPARATOR . $file->getPathName();
            if (is_readable($filename)) {
                array_push($files, $file);
            }
            else {
                $this->logErrorMessage("skip non-readable file $filename");
            }
        }

        if (empty($files)) {
            $this->logErrorMessage('document with id ' . $this->_docId . ' does not have any associated files');
            throw new Oai_Model_Exception('requested document does not have any associated readable files');
        }

        $containerFiles = array();
        /* @var $file Opus_File */
        foreach ($files as $file) {
            if ($file->getVisibleInOai() && $realm->checkFile($file->getId())) {
                array_push($containerFiles, $file);
            }
        }

        if (empty($containerFiles)) {
            $this->logErrorMessage(
                'document with id ' . $this->_docId . ' does not have associated files that are accessible'
            );
            throw new Oai_Model_Exception('access denied on all files that are associated to the requested document');
        }

        return $containerFiles;
    }

    public function getFileHandle() {
        $config = $this->_appConfig;
        $filesToInclude = $this->getAccessibleFiles();
        if (count($filesToInclude) > 1) {
            return new Oai_Model_TarFile(
                $this->_docId, $filesToInclude, $config->getFilesPath(), $config->getTempPath(), $this->getLogger()
            );
        }
        else {
            return new Oai_Model_SingleFile(
                $this->_docId, $filesToInclude, $config->getFilesPath(), $config->getTempPath(), $this->getLogger()
            );
        }
    }

    public function getZip() {
        // TODO
        throw new Exception('Not Implemented');
    }

    public function getCompressedTar() {
        // TODO
        throw new Exception('Not Implemented');
    }

    /**
     * Returns name of file.
     *
     * For OAI the name of the file should be the document ID.
     *
     * @return int
     */
    public function getName() {
        return $this->_docId;
    }

}
