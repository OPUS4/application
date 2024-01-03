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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Document;
use Opus\Common\File;
use Opus\Common\Model\NotFoundException;

/**
 * Model for importing files from a specific folder.
 *
 * TODO umbenennen für allgemeinen Dateisupport
 */
class Admin_Model_FileImport extends Application_Model_Abstract
{
    /** @var string */
    private $importFolder;

    public function __construct()
    {
        $this->importFolder = APPLICATION_PATH . '/workspace/incoming';
    }

    /**
     * @param string $docId
     * @param array  $files
     * @throws Application_Exception In case database contains no document with id $docID.
     */
    public function addFilesToDocument($docId, $files)
    {
        if (empty($files)) {
            throw new Application_Exception('no files for import');
        }

        $document = null;
        try {
            $document = Document::get($docId);
        } catch (NotFoundException $e) {
            throw new Application_Exception('no document found for id ' . $docId, 0, $e);
        }

        $log            = $this->getLogger();
        $validFilenames = $this->getNamesOfIncomingFiles();

        foreach ($files as $file) {
            $log->debug('check filename ' . $file);
            if (in_array($file, $validFilenames)) {
                $pathname = $this->importFolder . DIRECTORY_SEPARATOR . $file;
                $log->info('import file ' . $pathname);

                $docfile = $document->addFile();
                $docfile->setTempFile($pathname);
                $docfile->setPathName($file);
                $docfile->setLabel($file);
                try {
                    $document->store();
                    $log->info('import of file ' . $pathname . ' successful');
                } catch (Exception $e) {
                    $log->err('import of file ' . $pathname . ' failed: ' . $e->getMessage());
                }

                $log->info('try to delete file ' . $pathname);
                if (! unlink($pathname)) {
                    $log->err('could not delete file ' . $pathname);
                }
            }
        }
    }

    /**
     * Lists files in import folder.
     *
     * @return array
     */
    public function listFiles()
    {
        return Zend_Controller_Action_HelperBroker::getStaticHelper('Files')->listFiles($this->importFolder, true);
    }

    /**
     * @return array
     */
    public function getNamesOfIncomingFiles()
    {
        $incomingFilenames = [];
        foreach ($this->listFiles() as $file) {
            array_push($incomingFilenames, $file['name']);
        }
        return $incomingFilenames;
    }

    /**
     * @param string $path
     */
    public function setImportFolder($path)
    {
        $this->importFolder = $path;
    }

    /**
     * @return string
     */
    public function getImportFolder()
    {
        return $this->importFolder;
    }

    /**
     * Deletes a single file from a document.
     *
     * @param int $docId
     * @param int $fileId
     */
    public function deleteFile($docId, $fileId)
    {
        $doc = Document::get($docId);

        $keepFiles = [];

        $files = $doc->getFile();

        foreach ($files as $index => $file) {
            if ($file->getId() !== (int) $fileId) {
                $keepFiles[] = $file;
            }
        }

        $doc->setFile($keepFiles);

        $doc->store();
    }

    /**
     * Checks if a file id is formally correct and file exists.
     *
     * @param int $fileId
     * @return bool True if file ID is valid
     */
    public function isValidFileId($fileId)
    {
        if (empty($fileId) || ! is_numeric($fileId)) {
            return false;
        }

        try {
            File::get($fileId);
        } catch (NotFoundException $omnfe) {
            return false;
        }

        return true;
    }

    /**
     * Checks if a file ID is linked to a document.
     *
     * @param int        $docId
     * @param int|string $fileId
     * @return bool True - if the file is linked to the document
     */
    public function isFileBelongsToDocument($docId, $fileId)
    {
        if (empty($fileId) || ! is_numeric($fileId)) {
            return false;
        }

        $doc = Document::get($docId);

        $files = $doc->getFile();

        foreach ($files as $file) {
            if ($file->getId() === (int) $fileId) {
                return true;
            }
        }

        return false;
    }
}
