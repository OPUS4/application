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
 * @copyright   Copyright (c) 2009, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\File;
use Opus\Common\Model\ModelException;

/**
 * Controller fuer die Verwaltung der Dateien eines Dokuments.
 *
 * TODO redundanter Code mit DocumentController
 */
class Admin_FilemanagerController extends Application_Controller_Action
{
    public const PARAM_DOCUMENT_ID = 'id';

    public const PARAM_FILE_ID = 'fileId';

    /**
     * Zeigt Upload-Formular und Formulare fuer Dateien an.
     */
    public function indexAction()
    {
        $docId    = $this->getRequest()->getParam(self::PARAM_DOCUMENT_ID);
        $document = $this->getHelper('documents')->getDocumentForId($docId);

        $form = null;

        if (isset($document)) {
            $editSession = new Admin_Model_DocumentEditSession($docId);

            if ($this->getRequest()->isPost()) {
                $post = $this->getRequest()->getPost();

                $form = new Admin_Form_FileManager();

                $data = $post[$form->getName()]; // TODO

                $form->constructFromPost($data, $document);
                $form->populate($post);
                $result = $form->processPost($data, $data);

                if (is_array($result)) {
                    $target = $result['target']; // TODO check if present
                    $result = $result['result']; // TODO check if present
                }

                switch ($result) {
                    case Admin_Form_FileManager::RESULT_SAVE:
                        if ($form->isValid($post)) {
                            $form->updateModel($document);
                            try {
                                $document->store();
                            } catch (ModelException $ome) {
                                $this->getLogger()->err(
                                    __METHOD__ . ' Error saving file metadata: '
                                    . $ome->getMessage()
                                );
                                $this->_helper->Redirector->redirectTo(
                                    'index',
                                    'admin_filemanager_save_failure',
                                    'document',
                                    'admin',
                                    ['id' => $docId]
                                );
                                return;
                            }

                            $this->_helper->Redirector->redirectTo(
                                'index',
                                'admin_filemanager_save_success',
                                'document',
                                'admin',
                                ['id' => $docId]
                            );
                            return;
                        } else {
                            $form->setMessage($this->view->translate('admin_filemanager_error_validation'));
                        }
                        break;

                    case Admin_Form_FileManager::RESULT_CANCEL:
                        // TODO Rücksprung zur Ursprungsseite
                        $this->_helper->Redirector->redirectTo(
                            'index',
                            null,
                            'document',
                            'admin',
                            ['id' => $docId]
                        );
                        return;

                    case Admin_Form_Document::RESULT_SWITCH_TO:
                        $editSession->storePost($data, 'files');

                        // TODO Parameter in Unterarray 'params' => array() verlagern?
                        $target[self::PARAM_DOCUMENT_ID] = $docId;

                        $action = $target['action'];
                        unset($target['action']);
                        $controller = $target['controller'];
                        unset($target['controller']);
                        $module = $target['module'];
                        unset($target['module']);

                        $this->_helper->Redirector->redirectTo($action, null, $controller, $module, $target);
                        return;

                    case Admin_Form_Document::RESULT_SHOW:
                    default:
                        // $form->populate($post);
                        break;
                }
            } else {
                // GET-Request; Neues Formular anzeigen bzw. Editieren fortsetzen
                $form = new Admin_Form_FileManager();
                $form->populateFromModel($document);

                $post = $editSession->retrievePost('files');

                if ($this->getRequest()->getParam('continue') && $post !== null) {
                    $form->continueEdit($this->getRequest(), $post);
                }
            }
        } else {
            // missing or bad parameter => go back to main page
            $this->_helper->Redirector->redirectTo(
                'index',
                ['failure' => 'admin_document_error_novalidid'],
                'documents',
                'admin'
            );
            return;
        }

        // Set dynamic breadcrumb
        $this->breadcrumbs->setDocumentBreadcrumb($document);

        $this->view->languageSelectorDisabled = true;
        $this->view->contentWrapperDisabled   = true; // wrapper wird innerhalb des Formulars gerendert

        $form->setAction(
            $this->view->url(
                [
                    'module'                => 'admin',
                    'controller'            => 'filemanager',
                    'action'                => 'index',
                    self::PARAM_DOCUMENT_ID => $document->getId(),
                ],
                null,
                true
            )
        );

        $this->renderForm($form);
    }

    /**
     * Zeigt und verarbeitet Formular zum Hochladen von Dateien.
     *
     * Wenn eine Datei hochgeladen wird, die zu groß ist, wird der Upload nicht vollständig entgegen genommen und das
     * Submit-Feld am Ende fehlt.
     *
     * TODO es muss erkannt werden ob upload zu groß war
     */
    public function uploadAction()
    {
        $docId = $this->getRequest()->getParam(self::PARAM_DOCUMENT_ID);

        $document = $this->getHelper('documents')->getDocumentForId($docId);

        if ($this->getRequest()->isPost()) {
            // POST verarbeiten
            $post = $this->getRequest()->getPost();

            $form = new Admin_Form_File_Upload();

            $form->populate($post);
            $result = $form->processPost($post, $post);

            switch ($result) {
                case Admin_Form_File_Upload::RESULT_SAVE:
                    if ($form->isValid($post)) {
                        $form->updateModel($document);
                        try {
                            $document->store();
                        } catch (ModelException $e) {
                            $this->getLogger()->err("Storing document with new files failed" . $e);
                            $this->_helper->Redirector->redirectTo(
                                'index',
                                ['failure' => 'error_uploaded_files'],
                                'filemanager',
                                'admin',
                                [
                                    self::PARAM_DOCUMENT_ID => $docId,
                                    'continue'              => 'true',
                                ]
                            );
                            return;
                        }
                        $this->_helper->Redirector->redirectTo(
                            'index',
                            'admin_filemanager_upload_success',
                            'filemanager',
                            'admin',
                            [
                                self::PARAM_DOCUMENT_ID => $docId,
                                'continue'              => 'true',
                            ]
                        );
                    } else {
                        // Formular wieder anzeigen
                        // $form->populate($post); // currently not needed because no invalid value should be kept
                        $form->populateFromModel($document); // sets document ID and info in form
                    }
                    break;

                case Admin_Form_File_Upload::RESULT_CANCEL:
                    $this->_helper->Redirector->redirectTo(
                        'index',
                        null,
                        'filemanager',
                        'admin',
                        [
                            self::PARAM_DOCUMENT_ID => $docId,
                            'continue'              => 'true',
                        ]
                    );
                    break;

                default:
                    break;
            }
        } else {
            // Formular anzeigen
            if (isset($document)) {
                $form = new Admin_Form_File_Upload();
                $form->populateFromModel($document);
            } else {
                // missing or bad parameter => go back to main page
                $this->_helper->Redirector->redirectTo(
                    'index',
                    ['failure' => 'admin_document_error_novalidid'],
                    'documents',
                    'admin'
                );
                return;
            }
        }

        $this->breadcrumbs->setDocumentBreadcrumb($document);
        $this->breadcrumbs->setParameters('admin_filemanager_index', [self::PARAM_DOCUMENT_ID => $docId]);

        $config = $this->getConfig();

        if (isset($config->publish->filetypes->allowed)) {
            $this->view->extensions = $config->publish->filetypes->allowed;
        }

        // Adds translated messages for javascript files
        $javascriptTranslations = $this->view->getHelper('javascriptMessages');
        $javascriptTranslations->getDefaultMessageSet();

        $this->renderForm($form);
    }

    public function deleteAction()
    {
        $request = $this->getRequest();

        $docId    = (int) $this->getRequest()->getParam(self::PARAM_DOCUMENT_ID);
        $document = $this->getHelper('documents')->getDocumentForId($docId);

        if (! isset($document)) {
            // missing or bad parameter => go back to main page
            $this->_helper->Redirector->redirectTo(
                'index',
                ['failure' => 'admin_document_error_novalidid'],
                'documents',
                'admin'
            );
            return;
        }

        $fileId = (int) $this->getRequest()->getParam(self::PARAM_FILE_ID);

        $fileHelper = new Admin_Model_FileImport();

        if (! $fileHelper->isValidFileId($fileId)) {
            $this->_helper->Redirector->redirectTo(
                'index',
                ['failure' => 'admin_filemanager_error_novalidid'],
                'filemanager',
                'admin',
                [self::PARAM_DOCUMENT_ID => $docId]
            );
            return;
        }

        if (! $fileHelper->isFileBelongsToDocument($docId, $fileId)) {
            $this->_helper->Redirector->redirectTo(
                'index',
                ['failure' => 'admin_filemanager_error_filenotlinkedtodoc'],
                'filemanager',
                'admin',
                [self::PARAM_DOCUMENT_ID => $docId]
            );
            return;
        }

        $form = new Application_Form_Confirmation('Opus_File');

        if ($request->isPost()) {
            $post = $request->getPost();

            if ($form->isConfirmed($post)) {
                // Delete file
                $fileId = $form->getModelId();

                try {
                    $fileHelper->deleteFile($docId, $fileId);
                } catch (ModelException $ome) {
                    $this->getLogger()->err(__METHOD__ . ' Error deleting file. (' . $ome->getMessage . ')');
                    $this->_helper->Redirector->redirectTo(
                        'index',
                        ['failure' => 'admin_filemanager_delete_failure'],
                        'filemanager',
                        'admin',
                        [self::PARAM_DOCUMENT_ID => $docId, 'continue' => 'true']
                    );
                    return;
                }

                $this->_helper->Redirector->redirectTo(
                    'index',
                    'admin_filemanager_delete_success',
                    'filemanager',
                    'admin',
                    [self::PARAM_DOCUMENT_ID => $docId, 'continue' => 'true', self::PARAM_FILE_ID => $fileId]
                );
                return;
            } else {
                // Delete cancelled
                $this->_helper->Redirector->redirectTo(
                    'index',
                    null,
                    'filemanager',
                    'admin',
                    [self::PARAM_DOCUMENT_ID => $docId, 'continue' => 'true']
                );
                return;
            }
        } else {
            // Show confirmation page
            $file = File::get($fileId);

            $form->setModel($file);
            $form->setModelDisplayName($file->getPathName());
        }

        $this->breadcrumbs->setDocumentBreadcrumb($document);
        $this->breadcrumbs->setParameters('admin_filemanager_index', [self::PARAM_DOCUMENT_ID => $docId]);

        $this->renderForm($form);
    }

/* TODO reintegrate this code?
        // invalid form, populate with transmitted data
        // Because of redirect below errors are not passed to new page
        // Only important error is missing file
        $errors = $uploadForm->getErrors('fileupload');
        if (!empty($errors)) {
            $message = $this->view->translate('admin_filemanager_error_nofile');
        }
    }
    else {
        if (!empty($docId)) {
            TODO überlange Uploads
            $postMaxSize = ini_get('post_max_size');
            $uploadMaxFilesize = ini_get('upload_max_filesize');

            $maxSize = ($postMaxSize > $uploadMaxFilesize) ? $uploadMaxFilesize : $postMaxSize;

            $message = $this->view->translate('admin_filemanager_error_upload', '>' . $maxSize);
            $this->_helper->Redirector->redirectTo('index', array('failure' => $message), 'filemanager', 'admin', array('docId' => $docId));
        }
        else {
            $this->_helper->Redirector->redirectTo('index', null, 'documents', 'admin');
        }
    }    */
}
