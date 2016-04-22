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
 * @package     Module_Frontdoor
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2016, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Frontdoor_Model_File {

    const SERVER_STATE_DELETED = 'deleted';
    const SERVER_STATE_PUBLISHED = 'published';
    const ILLEGAL_DOCID_MESSAGE_KEY = 'illegal_argument_docid';
    const ILLEGAL_FILENAME_MESSAGE_KEY = 'illegal_argument_filename';

    /**
     * @var Opus_Document
     */
    private $_doc;

    /**
     * @var string
     */
    private $_filename;

    /**
     * @var
     */
    private $_accessControl;

    /**
     * Frontdoor_Model_File constructor.
     * @param $docId int OPUS document id number
     * @param $filename string Name of file
     */
    public function __construct($docId, $filename) {
        if (mb_strlen($docId) < 1 || preg_match('/^[\d]+$/', $docId) === 0 || $docId == null) {
            throw new Frontdoor_Model_FrontdoorDeliveryException(self::ILLEGAL_DOCID_MESSAGE_KEY, 400);
        }
        if (mb_strlen($filename) < 1 || preg_match('/\.\.\//', $filename) === 1) {
            throw new Frontdoor_Model_FrontdoorDeliveryException(self::ILLEGAL_FILENAME_MESSAGE_KEY, 400);
        }
        try {
            $this->_doc = new Opus_Document($docId);
        }
        catch (Opus_Model_NotFoundException $e) {
            throw new Frontdoor_Model_DocumentNotFoundException();
        }
        $this->_filename = $filename;
    }

    public function getFileObject($realm) {
        $this->checkDocumentApplicableForFileDownload($realm);
        return $this->fetchFile($realm);
    }

    public function checkDocumentApplicableForFileDownload($realm) {
        if (!$this->isDocumentAccessAllowed($this->_doc->getId(), $realm)) {
            switch ($this->_doc->getServerState()) {
                case self::SERVER_STATE_DELETED:
                    throw new Frontdoor_Model_DocumentDeletedException();
                    break;
                case self::SERVER_STATE_PUBLISHED:
                    // do nothing if in published state - access is granted!
                    break;
                default:
                    // Dateien dürfen bei Nutzer mit Zugriff auf "documents" heruntergeladen werden
                    throw new Frontdoor_Model_DocumentAccessNotAllowedException();
            }
        }
    }

    private function fetchFile($realm) {
        $targetFile = Opus_File::fetchByDocIdPathName($this->_doc->getId(), $this->_filename);
        if (is_null($targetFile)) {
            throw new Frontdoor_Model_FileNotFoundException();
        }
        if (!$this->isFileAccessAllowed($targetFile, $realm)) {
            throw new Frontdoor_Model_FileAccessNotAllowedException();
        }
        return $targetFile;
    }

    private function isDocumentAccessAllowed($docId, $realm) {
        if (!($realm instanceof Opus_Security_IRealm)) {
            return false;
        }
        return $realm->checkDocument($docId) || $this->getAclHelper()->accessAllowed('documents');
    }

    private function isFileAccessAllowed($file, $realm) {
        if (is_null($file) or !($realm instanceof Opus_Security_IRealm)) {
            return false;
        }

        return ($realm->checkFile($file->getId())
            && $file->getVisibleInFrontdoor()
            && $this->_doc->hasEmbargoPassed())
            || $this->getAclHelper()->accessAllowed('documents');
    }

    public function getAclHelper() {
        if (is_null($this->_accessControl)) {
            $this->_accessControl = Zend_Controller_Action_HelperBroker::getStaticHelper('accessControl');
        }

        return $this->_accessControl;
    }

    public function setAclHelper($helper) {
        if ($helper instanceof Application_Security_AccessControl || is_null($helper)) {
            $this->_accessControl = $helper;
        }
        else {
            throw new Application_Exception(
                '#1 argument must be of type Application_Security_AccessControl (not \''
                . get_class($helper) . '\')'
            );
        }
    }

}
