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
 * @package     Module_Admin
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2010, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Provides basic functionality for handling files in file manager.
 *
 *
 * FIXME old code that was commented out: do I need to check if a file actually exists
 * if (file_exists($fileNames[$fi]) === false) {
 *     $fileNumber--;
 *     continue;
 * }
 *
 */
class Admin_Model_FileHelper {

    private $view;

    private $document;

    private $file = null;

    public function __construct($view, $document, $file) {
        $this->view = $view;
        $this->document = $document;
        $this->file = $file;
    }

    public function getId() {
        return $this->file->getId();
    }

    public function getSize() {
        return $this->file->getFileSize();
    }

    public function getSignatureForm() {
        $form = new Admin_Form_SignatureForm();
        $form->FileObject->setValue($this->file->getId());
        $form->setAction($this->_getActionUrl());
        return $form;
    }

    public function getAccessForm() {
        $accessForm = new Admin_Form_FileAccess($this->file->getId());
        $accessForm->FileObject->setValue($this->file->getId());
        $accessForm->setSelectedRoles($this->_getRolesForFile());
        $accessForm->setAction($this->_getActionUrl());
        return $accessForm;
    }

    protected function _getRolesForFile() {
        return Admin_Model_FileHelper::getRolesForFile($this->file->getId());
    }

    public static function getRolesForFile($fileId) {
        $checkedRoles = array();
        
        $roles = Opus_UserRole::getAll();

        $items = array();
        foreach($roles as $role) {
            $files = $role->listAccessFiles();
            if (in_array($fileId, $files)) {
                array_push($checkedRoles, $role->getName());
            }
        }

        return $checkedRoles;
    }

    /**
     *
     * @return type 
     * 
     * TODO use view helper
     */
    protected function _getActionUrl() {
        $actionUrl = $this->view->url(array(
            'module' => 'admin',
            'controller' => 'filemanager',
            'action' => 'access',
            'docId' => $this->document->getId()), null, true);
        return $actionUrl;
    }

    public function getForms() {
        $fileForms[] = $this->getAccessForm();
        return $fileForms;
    }

    /**
     * Returns filename.
     * @return type String
     */
    public function getFileName() {
        return $this->file->getPathName();
    }

    /**
     * Constructs download URL for files.
     * @param type $view
     * @return type String
     */
    public function getUrl($view) {
        $config = Zend_Registry::get("Zend_Config");
        $rawUrl = $view->serverUrl() . $view->baseUrl . "/files/" . $this->document->getId() . "/" . urlencode($this->getFileName());
        return htmlspecialchars($rawUrl);
    }

    public function getHashes() {
        if (!$this->file->exists()) {
            return array();
        }

        $hashHelpers = array();

        $hashes = $this->file->getHashValue();

        if (is_array($hashes)) {
            foreach ($hashes as $hash) {
                $hashHelper = new Admin_Model_Hash($this->file, $hash);
                $hashHelpers[] = $hashHelper;
            }
        }

        return $hashHelpers;
    }

    public function exists() {
        return $this->file->exists();
    }

}

?>
