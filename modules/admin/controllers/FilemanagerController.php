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

        $importUrl = $this->view->url(array(
            'module' => 'admin',
            'controller' => 'filebrowser',
            'action' => 'index',
            'docId' => $docId
        ));

        $this->view->importUrl = $importUrl;
        $this->view->baseUrl = $this->getRequest()->getBaseUrl();
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
                $message = $this->view->translate('admin_filemanager_invalid_upload');
                // Because of redirect below errors are not passed to new page
                // Only important error is missing file
                $errors = $uploadForm->getErrors('fileupload');
                if (!empty($errors)) {
                    $message = $this->view->translate('admin_filemanager_error_nofile');
                }
                $this->_redirectTo('index', array('failure' => $message), 'filemanager', 'admin', array('docId' => $docId));
            }
        }
        else {
            if (!empty($docId)) {
                $postMaxSize = ini_get('post_max_size');
                $uploadMaxFilesize = ini_get('upload_max_filesize');

                $maxSize = ($postMaxSize > $uploadMaxFilesize) ? $uploadMaxFilesize : $postMaxSize;

                $message = $this->view->translate('admin_filemanager_error_upload', '>' . $maxSize);
                $this->_redirectTo('index', array('failure' => $message), 'filemanager', 'admin', array('docId' => $docId));
            }
            else {
                $this->_redirectTo('index', null, 'documents', 'admin');
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

        $data = $this->_request->getPost();
        $docId = $this->getRequest()->getParam('docId');
        $uploadForm = $this->_getUploadForm();
        $this->configureView($docId, $uploadForm);
        $document = $this->view->document->getDocument();
        $files = $this->getNumberedFiles($document);
        $this->view->documentAdapter = new Util_DocumentAdapter($this->view, $document);

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

    private function configureView($docId, $uploadForm) {
        $this->view->title = 'admin_filemanager_index';
        $this->view->docId = $docId;
        $this->view->editUrl = $this->view->url(array('module' => 'admin',
            'controller' => 'documents', 'action' => 'edit', 'id' => $docId),
                null, true);
        $this->view->uploadform = $uploadForm;
        $this->view->document = new Util_DocumentAdapter($this->view, $docId);
        $this->view->verifyResult = array();
    }

    private function getNumberedFiles($document) {
        $files = $document->getFile();
        if (true === is_array($files)) {
            $this->view->fileNumber = count($files);
        }
        return $files;
    }

    public function accessAction() {
        $docId = $this->getRequest()->getParam('docId');

        if ($this->getRequest()->isPost()) {
            $postData = $this->getRequest()->getPost();
            $this->_processAccessSubmit($postData);
        }

        $this->_redirectTo('index', null, 'filemanager', 'admin', array('docId' => $docId));
    }

    /**
     * Action for deleting a file.
     *
     * The action redirects the request to a confirmation form bevor actually
     * deleting the file.
     *
     * TODO catch invalid file IDs
     */
    public function deleteAction() {
        $docId = $this->getRequest()->getParam('docId');
        $fileId = $this->getRequest()->getParam('fileId');

        $documentsHelper = $this->_helper->getHelper('Documents');

        $document = $documentsHelper->getDocumentForId($docId);

        if (!isset($document)) {
            $this->_logger->err(__METHOD__ . " doc $docId does not exist or invalid");
            return $this->_redirectToAndExit('index', array('failure' =>
                $this->view->translate('admin_document_error_novalidid')), 'documents', 'admin');
        }

        if (!$this->_isValidFileId($fileId)) {
            $this->_logger->err(__METHOD__ . " doc $docId: file $fileId does not exist or invalid");
            return $this->_redirectToAndExit('index', array('failure' =>
                $this->view->translate('admin_filemanager_error_novalidid')), 'filemanager', 'admin', array('docId' => $docId));
        }

        if (!$this->_isFileBelongsToDocument($docId, $fileId)) {
            $this->_logger->err(__METHOD__ . " file $fileId for doc $docId does not exist");
            return $this->_redirectToAndExit('index', array('failure' =>
                $this->view->translate('admin_filemanager_error_filenotlinkedtodoc')), 'filemanager', 'admin', array('docId' => $docId));
        }

        switch ($this->_confirm($document, $fileId)) {
            case 'YES':
                try {
                    $this->_deleteFile($docId, $fileId);
                    $message = $this->view->translate('admin_filemanager_delete_success');
                }
                catch (Opus_Model_Exception $e) {
                    $this->_logger->debug($e->getMessage());
                    $message = array('failure' => $this->view->translate('admin_filemanager_delete_failure'));
                }

                $this->_redirectTo('index', $message, 'filemanager', 'admin', array('docId' => $docId));
                break;
            case 'NO':
                $this->_redirectTo('index', null, 'filemanager', 'admin', array('docId' => $docId));
                break;
            default:
                break;
        }
    }

    /**
     * Deletes a single file from a document.
     * @param type $docId
     * @param type $fileId
     * @return type
     */
    protected function _deleteFile($docId, $fileId) {
        $doc = new Opus_Document($docId);
        $keepFiles = array();
        $files = $doc->getFile();
        foreach($files as $index => $file) {
            if ($file->getId() !== $fileId) {
                $keepFiles[] = $file;
            }
        }
        $doc->setFile($keepFiles);
        $doc->store();
    }

    /**
     * Checks if a file id is formally correct and file exists.
     * @param string $fileId
     * @return boolean True if file ID is valid
     */
    protected function _isValidFileId($fileId) {
        if (empty($fileId) || !is_numeric($fileId)) {
            return false;
        }

        $file = null;

        try {
            $file = new Opus_File($fileId);
        }
        catch (Opus_Model_NotFoundException $omnfe) {
            return false;
        }

        return true;
    }

    /**
     * Checks if a file ID is linked to a document.
     * @param int $docId
     * @param int $fileId
     * @return boolean True - if the file is linked to the document
     */
    protected function _isFileBelongsToDocument($docId, $fileId) {
        $doc = new Opus_Document($docId);

        $files = $doc->getFile();

        foreach ($files as $file) {
            if ($file->getId() === $fileId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handles confirmation of action.
     *
     * The return value null means that the confirmation page should be shown.
     *
     * @param Opus_Document $document
     * @param int $fileId
     * @return string 'YES' if confirmed, 'NO' if denied, NULL otherwise
     */
    protected function _confirm($document, $fileId) {
        $sureyes = $this->getRequest()->getPost('sureyes');
        $sureno = $this->getRequest()->getPost('sureno');

        if (isset($sureyes) === true or isset($sureno) === true) {
            // Safety question answered, deleting
            if (isset($sureyes) === true) {
                return 'YES';
            }
            else {
                return 'NO';
            }
        }
        else {
            // show safety question
            $this->view->title = $this->view->translate('admin_filemanager_delete');
            $this->view->text = $this->view->translate('admin_filemanager_delete_sure', $fileId);
            $yesnoForm = $this->_getConfirmationForm($fileId, 'delete');
            $this->view->form = $yesnoForm;
            $this->view->documentAdapter = new Util_DocumentAdapter($this->view, $document);
            $this->renderScript('document/confirm.phtml');
        }
    }

    /**
     * Returns form for asking yes/no question like 'Delete file?'.
     *
     * @param type $id
     * @param type $action
     * @return Admin_Form_YesNoForm
     */
    protected function _getConfirmationForm($id, $action) {
        $yesnoForm = new Admin_Form_YesNoForm();
        $idElement = new Zend_Form_Element_Hidden('id');
        $idElement->setValue($id);
        $yesnoForm->addElement($idElement);
        $yesnoForm->setAction($this->view->url(array(
            "controller" => "filemanager",
            "action" => $action)));
        $yesnoForm->setMethod('post');
        return $yesnoForm;
    }

    /**
     *
     * @param <type> $postData
     *
     * TODO use Zend validation
     */
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

            $file->setLanguage($postData['language']);

            $visibleInFrontdoor = $postData['visibleInFrontdoor'];
            $file->setVisibleInFrontdoor($visibleInFrontdoor);

            $visibleInOai = $postData['visibleInOai'];
            $file->setVisibleInOai($visibleInOai);

            $file->store();

            $currentRoleNames = Admin_Model_FileHelper::getRolesForFile($file->getId());

            $selectedRoleNames = Admin_Form_FileAccess::parseSelectedRoleNames($postData);

            // remove roles that are not selected
            foreach ($currentRoleNames as $index => $roleName) {
                if (!in_array($roleName, $selectedRoleNames)) {
                    $role = Opus_UserRole::fetchByName($roleName);
                    $role->removeAccessFile($file->getId());
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
                    $role->appendAccessFile($file->getId());
                    $role->store();
                }
            }
        }
        else {
            // TODO error message?
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
            $docfile->setPathName(urldecode($file['name']));
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
