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
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Oai_Model_Container {

    private $docId;

    private $doc;

    private $_logger;

    public function  __construct($docId, $logger = null) {
        $this->_logger = $logger;
        $this->doc = $this->validateId($docId);
        $this->docId = $this->doc->getId();
    }

    private function logErrorMessage($message) {
        if (!is_null($this->_logger)) {
            $this->_logger->err(__CLASS__ . ': ' . $message);
        }
    }

    /**
     *
     * @param string $docId
     * @return Opus_Document returns a valid Opus_Document object if given docId is valid, otherwise throws an Oai_Model_Exception
     * @throws Oai_Model_Exception throws Oai_Model_Exception if the given docId is invalid
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
     * @return array an array of all associated Opus_File objects that are visible in OAI and accessible by user role guest
     */
    private function getAccessibleFiles() {
        $realm = Opus_Security_Realm::getInstance();
        if ($this->doc->getServerState() != 'published' || !$realm->checkDocument($this->docId)) {
            $this->logErrorMessage('document with id ' .     $this->docId . ' is not in server state published');
            throw new Oai_Model_Exception('access to requested document is forbidden');
        }

        $files = $this->doc->getFile();
        if (empty($files)) {
            $this->logErrorMessage('document with id ' . $this->docId . ' does not have any associated files');
            throw new Oai_Model_Exception('requested document does not have any associated files');
        }

        $containerFiles = array();
        foreach ($files as $file) {
            if ($file->getVisibleInOai() && $realm->checkFile($file->getId())) {
                array_push($containerFiles, $file);
            }
        }

        if (empty($containerFiles)) {
            $this->logErrorMessage('document with id ' . $this->docId . ' does not have associated files that are accessible');
            throw new Oai_Model_Exception('access denied on all files that are associated to the requested document');
        }

        return $containerFiles;
    }

    private function getWorkspacePath() {
        $config = Zend_Registry::get('Zend_Config');

        if (!isset($config->workspacePath)) {
            $this->logErrorMessage('missing config key workspacePath');
            throw new Oai_Model_Exception('missing configuration key workspacePath');
        }

        return $config->workspacePath . DIRECTORY_SEPARATOR;
    }

    private function getTempPath() {
        return $this->getWorkspacePath() . 'tmp' . DIRECTORY_SEPARATOR;
    }

    public function getTar() {
        $tarball = $this->getTempPath() . uniqid($this->docId, true) . '.tar';
        $phar = new PharData($tarball);

        foreach ($this->getAccessibleFiles() as $file) {
            $filePath = $this->getWorkspacePath() . 'files' . DIRECTORY_SEPARATOR . $this->docId . DIRECTORY_SEPARATOR;
            $phar->addFile($filePath . $file->getPathName(), $file->getPathName());
        }

        return $tarball;
    }

    public function getZip() {
        // TODO
        throw new Exception('Not Implemented');
    }

    public function getCompressedTar() {
        // TODO
        throw new Exception('Not Implemented');
    }

    public function getName() {
        return $this->docId;
    }

    public function deleteContainer($filename) {
        unlink($filename);
    }
}
