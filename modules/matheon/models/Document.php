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
 * @package     Module_Matheon
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Domain model for documents in the matheon model.
 *
 * @category    Framework
 * @package     Opus
 * @uses        Opus_Model_Abstract
 */
class Matheon_Model_Document {

    /**
     * @var Opus_Document
     */
    private $document;

    /**
     * @var Zend_Log
     */
    private $log;

    /**
     * Create new instance of Matheon_Model_Document.
     *
     * @param mixed $docId
     *
     * @throws Application_Exception
     */
    public function __construct($docId) {
        $this->log = Zend_Registry::get('Zend_Log');

        if (empty($docId)) {
            $error = "Empty docId given.";
            $this->log->err($error);
            throw new Application_Exception($error);
        }

        if (!preg_match('/\d+/', $docId) or $docId <= 0) {
            $error = "No or invalid docId given (docId:$docId).";
            $this->log->err($error);
            throw new Application_Exception($error);
        }

        $document = new Opus_Document($docId);
        if ($document->isNewRecord() or is_null($document->getId())) {
            $error = "Document '$docId' has not been stored.";
            $this->log->err($error);
            throw new Application_Exception($error);
        }

        $this->document = $document;

    }

    /**
     * Fail on wrong server state.
     *
     * @param string $state
     * @return Matheon_Model_Document Fluent interface.
     *
     * @throws Application_Exception
     */
    public function requireServerState($state) {
        $docState = $this->document->getServerState();
        if ($docState !== $state) {
            $error = "Document (id:{$this->getId()}) has wrong state (state:$docState).";
            $this->log->err($error);
            throw new Application_Exception($error);
        }
        return $this;
    }

    /**
     * Fail if the document was not submitted by the current user.
     *
     * @return Matheon_Model_Document Fluent interface.
     *
     * @throws Application_Exception
     */
    public function requireSubmitterIsCurrentUser() {
        $loggedUserModel = new Publish_Model_LoggedUser();
        $loggedUserId = $loggedUserModel->getUserId();

        if (is_null($loggedUserId)) {
            $error = "No user logged in.  Unable to compare submitter for document (id:{$this->getId()}).";
            $this->log->err($error);
            throw new Application_Exception($error);
        }

        $hasSubmitterEnrichment = false;
        $hasRightSubmitterId = false;

        foreach ($this->document->getEnrichment() AS $enrichment) {
            if ($enrichment->getKeyName() == 'submitter.user_id') {
                $hasSubmitterEnrichment = true;

                if ($enrichment->getValue() == $loggedUserId) {
                    $hasRightSubmitterId = true;
                    break;
                }
            }
        }

        if (!$hasSubmitterEnrichment) {
            $error = "Document (id:{$this->getId()}) does not contain submitter information.";
            $this->log->err($error);
            throw new Application_Exception($error);
        }

        if (!$hasRightSubmitterId) {
            $error = "Document (id:{$this->getId()}) does not belong to this user (user_id:$loggedUserId).";
            $this->log->err($error);
            throw new Application_Exception($error);
        }

    }

    /**
     * Add the given (key,value) to the documents enrichments.
     *
     * @param mixed $key
     * @param mixed $value
     * @return Matheon_Model_Document Fluent interface.
     */
    public function storeEnrichmentKeyValue($key, $value) {
        $this->document->addEnrichment()
                ->setKeyName($key)
                ->setValue($value);
        return $this;
    }

    /**
     * Add readFile privilege to all files of this document.
     *
     * @param string $role_name
     * @return Matheon_Model_Document Fluent interface.
     */
    public function addReadFilePrivilege($role_name = 'guest') {
        $role = Opus_UserRole::fetchByName($role_name);

        if (is_null($role)) {
            $this->log->err("Cannot add readFile privilege for non-existent role '{$role->getName()}' to document " . $this->getId() . ".");
            return $this;
        }

        $this->log->warn("Warning: Setting all files readable for role '{$role->getName()}' (document " . $this->getId() . ")");
        $role->appendAccessDocument($this->getId());
        foreach ($this->document->getFile() AS $file) {
              $role->appendAccessFile($file->getId());
        }
        $role->store();

        return $this;
    }

    /**
     * Get current document Id.
     *
     * @return integer
     */
    public function getId() {
        return $this->document->getId();
    }

    /**
     * Store current document.
     * 
     * @return integer
     */
    public function store() {
        return $this->document->store();
    }

    /**
     * Render subject of notification mail.
     *
     * @return string
     */
    public function renderPublishMailSubject() {
        $docId = $this->getId();
        return "Matheon: Please review a new preprint ($docId)";
    }

     /**
      * Render body of notification mail.
      *
      * @param string $baseUrlServer
      * @param string $baseUrlFiles
      * @return string
      */
    public function renderPublishMailBody($baseUrlServer, $baseUrlFiles) {
        $baseUrlServer = preg_replace('/[\/]+$/', '', $baseUrlServer);
        $baseUrlFiles = preg_replace('/[\/]+$/', '', $baseUrlFiles);

        $config = Zend_Registry::getInstance()->get('Zend_Config');
        $docId = $this->getId();

        $loggedUserModel = new Publish_Model_LoggedUser();
        $person = $loggedUserModel->createPerson();
        $submitterString = '';
        if (!is_null($person) and $person->isValid()) {
            $submitterString = trim($person->getFirstName() . " " . $person->getLastName());
        }
        if (empty($submitterString)) {
            $submitterString = '-- No submitter name given! --';
        }

        $titleModels = $this->document->getTitleMain();
        $titleString = "";
        if (count($titleModels) > 0) {
            $titleString = trim($titleModels[0]->getValue());
        }
        if (empty($titleString)) {
            $titleString = '-- No title given! --';
        }

        $abstractModels = $this->document->getTitleAbstract();
        $abstractString = '';
        if (count($abstractModels) > 0) {
            $abstractString = $abstractModels[0]->getValue();
        }
        if (empty($abstractString)) {
            $abstractString = '-- No abstract given! --';
        }

        $files = $this->document->getFile();
        $filesCount = count($files);
        $filesString = "\n";
        if ($filesCount === 0) {
            $filesString = "-- The user did not upload any files! --";
        }
        foreach ($files AS $file) {
            $filesString .= "  * {$baseUrlFiles}/" . $docId . "/" . $file->getPathName() . "\n";
            $filesString .= "    (Size: " . $file->getFileSize() . " Bytes, Mime-Type: " . $file->getMimeType() . ")\n";
        }

        $body = "Dear Referee,
you are asked by {$submitterString} to approve the preprint


Title:
{$titleString}

Abstract:
{$abstractString}

on the Matheon preprint server at {$baseUrlServer}.


In order to start the release process, please FIRST log in with your default
webbrowser on the website:
    {$baseUrlServer}/auth/login/

You find an overview of the preprints which you are asked to release:
    {$baseUrlServer}/review

You can check the document information and the preprint files:
    {$baseUrlServer}/frontdoor/index/index/docId/{$docId}

In addition you can also directly download and read the preprint. 
    {$filesString}

and accepting:
    {$baseUrlServer}/review/index/clear?selected={$docId}

or reject it:
    {$baseUrlServer}/review/index/reject?selected={$docId}


Thank you for your efforts!

The Preprint webmaster.


Note: This is an autogenerated mail. Please do not reply.
For response and suggestions use webmaster@matheon.de";

        return $body;

    }

}
