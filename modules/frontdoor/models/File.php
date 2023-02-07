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
use Opus\Common\DocumentInterface;
use Opus\Common\File;
use Opus\Common\FileInterface;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Security\RealmInterface;

class Frontdoor_Model_File
{
    public const SERVER_STATE_DELETED         = 'deleted';
    public const SERVER_STATE_PUBLISHED       = 'published';
    public const ILLEGAL_DOCID_MESSAGE_KEY    = 'illegal_argument_docid';
    public const ILLEGAL_FILENAME_MESSAGE_KEY = 'illegal_argument_filename';

    /** @var DocumentInterface */
    private $doc;

    /** @var string */
    private $filename;

    /** @var Application_Security_AccessControlInterface */
    private $accessControl;

    /**
     * @param int    $docId OPUS document id number
     * @param string $filename Name of file
     */
    public function __construct($docId, $filename)
    {
        if ($docId === null || mb_strlen($docId) < 1 || preg_match('/^[\d]+$/', $docId) === 0) {
            throw new Frontdoor_Model_FrontdoorDeliveryException(self::ILLEGAL_DOCID_MESSAGE_KEY, 400);
        }

        if (mb_strlen($filename) < 1 || preg_match('/\.\.\//', $filename) === 1) {
            throw new Frontdoor_Model_FrontdoorDeliveryException(self::ILLEGAL_FILENAME_MESSAGE_KEY, 400);
        }

        try {
            $this->doc = Document::get($docId);
        } catch (NotFoundException $e) {
            throw new Frontdoor_Model_DocumentNotFoundException();
        }

        $this->filename = $filename;
    }

    /**
     * @param RealmInterface $realm
     * @return FileInterface
     * @throws Frontdoor_Model_DocumentAccessNotAllowedException
     * @throws Frontdoor_Model_DocumentDeletedException
     * @throws Frontdoor_Model_FileAccessNotAllowedException
     * @throws Frontdoor_Model_FileNotFoundException
     */
    public function getFileObject($realm)
    {
        $this->checkDocumentApplicableForFileDownload($realm);
        return $this->fetchFile($realm);
    }

    /**
     * @param RealmInterface $realm
     * @throws Frontdoor_Model_DocumentAccessNotAllowedException
     * @throws Frontdoor_Model_DocumentDeletedException
     */
    public function checkDocumentApplicableForFileDownload($realm)
    {
        if (! $this->isDocumentAccessAllowed($this->doc->getId(), $realm)) {
            switch ($this->doc->getServerState()) {
                case self::SERVER_STATE_DELETED:
                    throw new Frontdoor_Model_DocumentDeletedException();
                case self::SERVER_STATE_PUBLISHED:
                    // do nothing if in published state - access is granted!
                    break;
                default:
                    // Dateien dürfen bei Nutzer mit Zugriff auf "documents" heruntergeladen werden
                    throw new Frontdoor_Model_DocumentAccessNotAllowedException();
            }
        }
    }

    /**
     * @param RealmInterface $realm
     * @return FileInterface
     * @throws Frontdoor_Model_FileAccessNotAllowedException
     * @throws Frontdoor_Model_FileNotFoundException
     */
    private function fetchFile($realm)
    {
        $targetFile = File::fetchByDocIdPathName($this->doc->getId(), $this->filename);

        if ($targetFile === null) {
            throw new Frontdoor_Model_FileNotFoundException();
        }

        if (! $this->isFileAccessAllowed($targetFile, $realm)) {
            throw new Frontdoor_Model_FileAccessNotAllowedException();
        }

        return $targetFile;
    }

    /**
     * @param int            $docId
     * @param RealmInterface $realm
     * @return bool
     */
    private function isDocumentAccessAllowed($docId, $realm)
    {
        if (! $realm instanceof RealmInterface) {
            return false;
        }
        return $realm->checkDocument($docId) || $this->getAclHelper()->accessAllowed('documents');
    }

    /**
     * @param FileInterface  $file
     * @param RealmInterface $realm
     * @return bool
     */
    private function isFileAccessAllowed($file, $realm)
    {
        if ($file === null || ! $realm instanceof RealmInterface) {
            return false;
        }

        return ($realm->checkFile($file->getId())
            && $file->getVisibleInFrontdoor()
            && $this->doc->hasEmbargoPassed())
            || $this->getAclHelper()->accessAllowed('documents');
    }

    /**
     * @return Application_Security_AccessControlInterface
     */
    public function getAclHelper()
    {
        if ($this->accessControl === null) {
            $this->accessControl = Zend_Controller_Action_HelperBroker::getStaticHelper('accessControl');
        }

        return $this->accessControl;
    }

    /**
     * @param Application_Security_AccessControlInterface|null $helper
     * @throws Application_Exception
     */
    public function setAclHelper($helper)
    {
        if ($helper instanceof Application_Security_AccessControlInterface || $helper === null) {
            $this->accessControl = $helper;
        } else {
            throw new Application_Exception(
                '#1 argument must be of type Application_Security_AccessControl (not \''
                . get_class($helper) . '\')'
            );
        }
    }
}
