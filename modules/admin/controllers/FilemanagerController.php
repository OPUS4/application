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

class Admin_FilemanagerController extends Controller_Action {

    /**
     * Just to be there. No actions taken.
     *
     * @return void
     *
     */
    public function indexAction() {
        $docId = $this->_prepareView();

        if (empty($docId)) {
            return $this->renderScript('filemanager/nodoc.phtml');
        }

        // TODO not needed anymore
//        if (!empty($this->view->actionresult)) {
//            $this->_redirectTo('index', $this->view->actionresult, 'filemanager', 'admin', array('docId' => $docId));
//        }
    }

    public function uploadAction() {
        
        $data = $this->_request->getPost();

        $uploadForm = $this->_getUploadForm();

        $docId = $this->getRequest()->getParam('docId');

        $this->view->docId = $docId;

        // store uploaded data in application temp dir
        if (true === array_key_exists('uploadsubmit', $data)) {
            if ($uploadForm->isValid($data) === true) {
                $this->_storeUpload($docId, $uploadForm);
                $this->_redirectTo('index', $this->view->actionresult, 'filemanager', 'admin', array('docId' => $docId));
            }
            else {
                $this->_prepareView();

                // invalid form, populate with transmitted data
                $uploadForm->populate($data);
                $this->view->form = $uploadForm;

                $this->view->actionresult = 'Invalid form input.';
                // TODO forward to index action
                $this->render('index');
            }
        }
    }

    protected function _getUploadForm() {
        $uploadForm = new Admin_Form_FileUpload();

        $actionUrl = $this->view->url(array('controller' => 'filemanager', 'action' => 'upload'));

        $uploadForm->setAction($actionUrl);

        return $uploadForm;
    }

    protected function _prepareView() {
        $this->view->title = 'admin_filemanager_index';

        $data = $this->_request->getPost();

        $docId = $this->getRequest()->getParam('docId');

        $this->view->docId = $docId;

        $this->view->editUrl = $this->view->url(array('module' => 'admin',
            'controller' => 'documents', 'action' => 'edit', 'id' => $docId),
                null, true);

        $uploadForm = $this->_getUploadForm();

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
        if (!empty($files)) {
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

        return $docId;
    }

    public function accessAction() {
        $docId = $this->getRequest()->getParam('docId');

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $this->_processAccessSubmit($postData);
        }

        $this->_redirectTo('index', null, 'filemanager', 'admin', array('docId' => $docId));
    }

    public function signAction() {

    }

    public function deleteAction() {
        $docId = $this->getRequest()->getParam('docId');
        $fileId = $this->getRequest()->getParam('fileId');

        if (empty($fileId)) {
            $this->_redirectTo('index', '', 'filemanager', 'admin', array('docId' => $docId));
        }

        try {
            $file = new Opus_File($fileId);
            $file->doDelete($file->delete());
            $this->view->actionresult = $this->view->translate('admin_filemanager_delete_success');
        }
        catch (Opus_Storage_Exception $e) {
            $this->view->actionresult = $e->getMessage();
        }

        $this->_redirectTo('index', $this->view->translate('admin_filemanager_delete_success'), 'filemanager', 'admin', array('docId' => $docId));
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

            $file = new Opus_File(( int )$fileId);

            if (!$file->exists()) {
                throw new Exception('file ' . $fileId . ' does not exist.');
            }

            $comment = $postData['comment'];
            $file->setComment($comment);

            $label = $postData['label'];
            $file->setLabel($label);

            $visibleInFrontdoor = $postData['visibleInFrontdoor'];
            $file->setVisibleInFrontdoor($visibleInFrontdoor);

            $visibleInOai = $postData['visibleInOai'];
            $file->setVisibleInOai($visibleInOai);

            $file->store();

            $currentRoleNames = Admin_Model_FileHelper::getRolesForFile($file);

            $selectedRoleNames = Admin_Form_FileAccess::parseSelectedRoleNames($postData);

            // remove roles that are not selected
            // TODO implement
            foreach ($currentRoleNames as $roleName) {
                if (!in_array($roleName, $selectedRoleNames)) {
                    $role = Opus_UserRole::fetchByName($roleName);
                    $role->store();
                }
            }

            // add selected roles
            foreach ($selectedRoleNames as $roleName) {
                $role = Opus_UserRole::fetchByName($roleName);
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
            $this->view->actionresult = $this->view->translate('admin_filemanager_delete_success');
        }
    }

    protected function _storeUpload($docId, $uploadForm) {
        $log = Zend_Registry::get('Zend_Log');
        $upload = new Zend_File_Transfer_Adapter_Http();
        $files = $upload->getFileInfo();

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
            $docfile->setLabel($uploadForm->getValue('label'));
            $docfile->setComment($uploadForm->getValue('comment'));
            $docfile->setLanguage($uploadForm->getValue('language'));
            $docfile->setPathName($file['name']);
            $docfile->setMimeType($file['type']);
            $docfile->setTempFile($file['tmp_name']);
        }

        try {
            $document->store();
            $this->view->actionresult = $this->view->translate('admin_filemanager_uploadsuccess');
        }
        catch (Opus_Model_Exception $e) {
            $log->warn("File upload failed: " . $e);
            $this->view->actionresult = array(
                'failure' => $this->view->translate('error_uploaded_files'));
        }

        // reset input values fo re-displaying
        $uploadForm->reset();
        // re-insert document id
        $uploadForm->DocumentId->setValue($document->getId());
    }

}
