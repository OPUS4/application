<?php
/**
 * Index Controller for all actions dealing with encryption and signatures
 *
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
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_FilemanagerController extends Controller_Action
{

    /**
     * Just to be there. No actions taken.
     *
     * @return void
     *
     */
    public function indexAction()
    {
        $this->view->title = 'admin_filemanager_index';

        $data = $this->_request->getPost();

        if (true === array_key_exists('signsubmit', $data)) {
            $this->_processSignSubmit($data);
        }

        if (true === array_key_exists('deletesubmit', $data)) {
            $this->_processDeleteSubmit($data);
        }

        if (true === array_key_exists('accesssubmit', $data)) {
            $this->_processAccessSubmit($data);
        }

        $docId = $this->getRequest()->getParam('docId');

        $this->view->docId = $docId;

    	if (!empty($docId))
    	{
            $this->view->editUrl = $this->view->url(array('module' => 'admin', 
                'controller' => 'documents', 'action' => 'edit', 'id' => $docId),
                    null, true);

            $uploadForm = $this->_getUploadForm();

            // store uploaded data in application temp dir
            if (true === array_key_exists('uploadsubmit', $data)) {
                if ($uploadForm->isValid($data) === true) {
                    $this->_storeUpload($docId, $uploadForm);
                }
                else {
                    // invalid form, populate with transmitted data
                    $uploadForm->populate($data);
                    $this->view->form = $uploadForm;
                }
            }

            $this->view->uploadform = $uploadForm;

            $this->view->document = new Review_Model_DocumentAdapter($this->view, $docId);

            $document = $this->view->document->getDocument();

            //searching for files, getting filenumbers and hashes
            $files = $document->getFile();
            if (true === is_array($files)) {
                $this->view->fileNumber = count($files);
            }

            // Iteration over all files, hashtypes and -values
            // Check if GPG for admin is enabled
            $config = Zend_Registry::get('Zend_Config');
                
            $this->view->verifyResult = array();

            $fileHelpers = array();
            if (empty($files)) {
                $this->view->actionresult = $this->view->translate('admin_filemanager_nofiles');
            }
            else {
                foreach ($files as $file) {
                    try {
                        $fileHelpers[] = new Admin_Model_FileHelper($this->view, $document, $file);
                    }
                    catch (Exception $e) {
                        $this->view->noDocumentSelectedMessage = $e->getMessage();
                        // TODO collect multiple error messages?
                        return $this->renderScript('filemanager/error.phtml');
                    }
                }
            }

            $this->view->fileHelpers = $fileHelpers;
    	}
    	else {
            return $this->renderScript('filemanager/nodoc.phtml');
    	}
    }

    public function deleteAction() {
        $docId = $this->getRequest()->getParam('docId');
        $fileId = $this->getRequest()->getParam('fileId');

        $fileName = null;

        if (!empty($fileId)) {
            try {
                $file = new Opus_File($fileId);
                $fileName = $file->getPathName();
                // Really delete this file
                $file->doDelete($file->delete());
            }
            catch (Exception $e) {
//                $this->view->actionresult = $e->getMessage();
            }
//            if ($e === null) {
//                $this->view->actionresult = $this->view->translate('admin_filemanager_deletesuccess');
//            }

            $this->_redirectTo('index', $this->view->translate('admin_filemanager_delete_success', $fileName), 'filemanager', 'admin', array('docId' => $docId));
        }

        $this->_redirectTo('index', '', 'filemanager', 'admin', array('docId' => $docId));
    }

    protected function _getUploadForm() {
        $uploadForm = new Admin_Form_FileUpload();

        $actionUrl = $this->view->url(array('controller' => 'filemanager', 'action' => 'index'));

        $uploadForm->setAction($actionUrl);

        return $uploadForm;
    }

    protected function _processSignSubmit($postData) {
        $gpg = new Opus_GPG();

        $e = null;
        try {
            $gpg->signPublicationFile(new Opus_File($postData['FileObject']), $postData['password']);
        }
        catch (Exception $e) {
            $this->view->actionresult = $e->getMessage();
        }
        if ($e === null) {
            $this->view->actionresult = $this->view->translate('admin_filemanager_signsuccess');
        }
    }

    protected function _isGpgEnabled() {
        if (isset($config->gpg->enable->admin)) {
            return ($config->gpg->enable->admin === 1) ? true : false;
        }
        else {
            return false;
        }
    }

    protected function _processAccessSubmit($postData) {
        $log = Zend_Registry::get('Zend_Log');

        if (isset($postData['FileObject'])) {
            $fileId = $postData['FileObject'];

            $file = new Opus_File($fileId);

            $currentRoleNames = Admin_Model_FileHelper::getRolesForFile($file);

            $selectedRoleNames = Admin_Form_FileAccess::parseSelectedRoleNames($postData);

            // remove roles that are not selected
            foreach ($currentRoleNames as $roleName) {
                if (!in_array($roleName, $selectedRoleNames)) {
                    $role = Opus_Role::fetchByName($roleName);
                    $privileges = $role->getPrivilege();
                    foreach ($privileges as $index => $privilege) {
                        if ($privilege->getPrivilege() === 'readFile') {
                            if ($privilege->getFileId() === $fileId) {
                                $log->debug('remove readFile: ' . $fileId . ' from role ' . $roleName);
                                $privileges[$index] = null;
                            }
                        }
                    }
                    $role->setPrivilege($privileges);
                    $role->store();
                }
            }

            // add selected roles
            foreach ($selectedRoleNames as $roleName) {
                $role = Opus_Role::fetchByName($roleName);
                if (in_array($roleName, $currentRoleNames)) {
                    $log->debug('readFile for role ' . $roleName . ' already set');
                }
                else {
                    $log->debug('add readFile to role ' . $roleName);
                    $privilege = $role->addPrivilege();
                    $privilege->setPrivilege('readFile');
                    $privilege->setFile($file);
                    $role->store();
                }
            }
        }
        else {
            // TODO error message?
        }
    }

    protected function _processDeleteSubmit($postData) {
        $e = null;
        try {
            $file = new Opus_File($postData['FileObject']);
            // Really delete this file
            $file->doDelete($file->delete());
        }
        catch (Exception $e) {
            $this->view->actionresult = $e->getMessage();
        }
        if ($e === null) {
            $this->view->actionresult = $this->view->translate('admin_filemanager_deletesuccess');
        }
    }

    protected function _storeUpload($docId, $uploadForm) {
        // This works only from Zend 1.7 on
        // $upload = $uploadForm->getTransferAdapter();
        $upload = new Zend_File_Transfer_Adapter_Http();

        $files = $upload->getFileInfo();

        // TODO: Validate document id, error message on fail
        $document = new Opus_Document($docId);

        // save each file
        foreach ($files as $file) {
            /* TODO: Uncaught exception 'Zend_File_Transfer_Exception' with message '"fileupload" not found by file transfer adapter
            * if (!$upload->isValid($file)) {
            *    $this->view->message = 'Upload failed: Not a valid file!';
            *    break;
            * }
            */
            $docfile = $document->addFile();
            $docfile->setLabel($uploadForm->getValue('comment'));
            $docfile->setLanguage($uploadForm->getValue('language'));
            $docfile->setPathName($file['name']);
            $docfile->setMimeType($file['type']);
            $docfile->setTempFile($file['tmp_name']);
            $docfile->setFromPost($file);
        }

        $e = null;

        try {
            $document->store();
            $config = Zend_Registry::get('Zend_Config');

            $searchEngine = $config->searchengine->engine;
            if (empty($searchEngine) === true) {
                $searchEngine = 'Lucene';
            }
            // Reindex
            $engineclass = 'Opus_Search_Index_'.$searchEngine.'_Indexer';
            $indexer = new $engineclass();
            $indexer->removeDocumentFromEntryIndex($document);
            $indexer->addDocumentToEntryIndex($document);
        }
        catch (Exception $e) {
            $this->view->actionresult = $this->view->translate('admin_filemanager_uploadfailure');
        }
        if ($e === null) {
            $this->view->actionresult = $this->view->translate('admin_filemanager_uploadsuccess');
        }

        // reset input values fo re-displaying
        $uploadForm->reset();
        // re-insert document id
        $uploadForm->DocumentId->setValue($document->getId());
    }

}
