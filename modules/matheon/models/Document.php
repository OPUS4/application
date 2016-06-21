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
class Matheon_Model_Document {

    /**
     * @var Opus_Document
     */
    private $_document;

    /**
     * @var Zend_Log
     */
    private $_log;

    /**
     * Create new instance of Matheon_Model_Document.
     *
     * @param mixed $docId
     *
     * @throws Application_Exception
     */
    public function __construct($docId) {
        $this->_log = Zend_Registry::get('Zend_Log');

        if (empty($docId)) {
            $error = "Empty docId given.";
            $this->_log->err($error);
            throw new Application_Exception($error);
        }

        if (!preg_match('/\d+/', $docId) or $docId <= 0) {
            $error = "No or invalid docId given (docId:$docId).";
            $this->_log->err($error);
            throw new Application_Exception($error);
        }

        $document = new Opus_Document($docId);
        if ($document->isNewRecord() or is_null($document->getId())) {
            $error = "Document '$docId' has not been stored.";
            $this->_log->err($error);
            throw new Application_Exception($error);
        }

        $this->_document = $document;

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
        $docState = $this->_document->getServerState();
        if ($docState !== $state) {
            $error = "Document (id:{$this->getId()}) has wrong state (state:$docState).";
            $this->_log->err($error);
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
            $this->_log->err($error);
            throw new Application_Exception($error);
        }

        $hasSubmitterEnrichment = false;
        $hasRightSubmitterId = false;

        foreach ($this->_document->getEnrichment() AS $enrichment) {
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
            $this->_log->err($error);
            throw new Application_Exception($error);
        }

        if (!$hasRightSubmitterId) {
            $error = "Document (id:{$this->getId()}) does not belong to this user (user_id:$loggedUserId).";
            $this->_log->err($error);
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
        foreach ($this->_document->getEnrichment() AS $e) {
            if ($e->getKeyName() == $key) {
                if ($e->getValue() == $value) {
                    return $this;
                }
            }
        }

        $this->_document->addEnrichment()
                ->setKeyName($key)
                ->setValue($value);
        return $this;
    }

    /**
     * Add readFile privilege to all files of this document.
     *
     * @param string $roleName
     * @return Matheon_Model_Document Fluent interface.
     */
    public function addReadFilePrivilege($roleName = 'guest') {
        $role = Opus_UserRole::fetchByName($roleName);

        if (is_null($role)) {
            $this->_log->err(
                "Cannot add readFile privilege for non-existent role '{$role->getName()}' to document "
                . $this->getId() . "."
            );
            return $this;
        }

        $this->_log->warn(
            "Warning: Setting all files readable for role '{$role->getName()}' (document " . $this->getId() . ")"
        );
        $role->appendAccessDocument($this->getId());
        foreach ($this->_document->getFile() AS $file) {
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
        return $this->_document->getId();
    }

    /**
     * Store current document.
     * 
     * @return integer
     */
    public function store() {
        return $this->_document->store();
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

        $loggedUserModel = new Publish_Model_LoggedUser();
        $person = $loggedUserModel->createPerson();
        $submitterString = '';
        if (!is_null($person) and $person->isValid()) {
            $submitterString = trim($person->getFirstName() . " " . $person->getLastName());
        }

        $titleModels = $this->_document->getTitleMain();
        $titleString = '';
        if (count($titleModels) > 0) {
            $titleString = trim($titleModels[0]->getValue());
        }

        $abstractModels = $this->_document->getTitleAbstract();
        $abstractString = '';
        if (count($abstractModels) > 0) {
            $abstractString = trim($abstractModels[0]->getValue());
        }

        $template = new Matheon_Model_Template();
        $template->template = APPLICATION_PATH . '/modules/matheon/models/confirmation-mail.template';

        return $template->render(
            array(
            'baseUrlServer'   => $baseUrlServer,
            'baseUrlFiles'    => $baseUrlFiles,
            'docId'           => $this->getId(),

            'submitterString' => $submitterString,
            'titleString'     => $titleString,
            'abstractString'  => $abstractString,
            'files'           => $this->_document->getFile(),
            )
        );
    }

}
