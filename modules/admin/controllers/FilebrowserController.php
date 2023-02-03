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
use Opus\Common\Model\NotFoundException;

/**
 * Browsing of file import folder for adding files to documents.
 */
class Admin_FilebrowserController extends Application_Controller_Action
{
    public const PARAM_DOCUMENT_ID = 'id';

    /**
     * Shows files in import folder.
     */
    public function indexAction()
    {
        $docId = $this->getRequest()->getParam(self::PARAM_DOCUMENT_ID);
        if ($docId === null) {
            throw new Application_Exception('missing parameter docId');
        }

        $document = null;
        try {
            $document = Document::get($docId);
        } catch (NotFoundException $e) {
            throw new Application_Exception('no document found for id ' . $docId, 0, $e);
        }

        $this->breadcrumbs->setDocumentBreadcrumb($document);
        $this->breadcrumbs->setParameters(
            'admin_filemanager_index',
            [
                self::PARAM_DOCUMENT_ID => $docId,
                'continue'              => true,
            ]
        );

        $importHelper                = new Admin_Model_FileImport();
        $this->view->files           = $importHelper->listFiles();
        $this->view->documentAdapter = new Application_Util_DocumentAdapter($this->view, $document);
        $this->view->document        = $document;
    }

    /**
     * Imports file(s) from import folder for document.
     */
    public function importAction()
    {
        if (! $this->getRequest()->isPost()) {
            throw new Application_Exception('unsupported HTTP method');
        }

        $docId = $this->getRequest()->getPost(self::PARAM_DOCUMENT_ID);
        if ($docId === null) {
            throw new Application_Exception('missing parameter docId');
        }

        $post = $this->getRequest()->getPost();

        if (isset($post['Cancel'])) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                null,
                'filemanager',
                'admin',
                [self::PARAM_DOCUMENT_ID => $docId, 'continue' => 'true']
            );
            return;
        }

        $files = $this->getRequest()->getPost('file');
        if ($files === null || is_array($files) && empty($files)) {
            $this->_helper->Redirector->redirectToAndExit(
                'index',
                null,
                'filebrowser',
                'admin',
                [self::PARAM_DOCUMENT_ID => $docId]
            );
            return;
        }

        if (! is_array($files)) {
            throw new Application_Exception('invalid POST parameter');
        }

        $fileImportModel = new Admin_Model_FileImport();
        $fileImportModel->addFilesToDocument($docId, $files);
        $this->_helper->Redirector->redirectToAndExit(
            'index',
            null,
            'filemanager',
            'admin',
            [self::PARAM_DOCUMENT_ID => $docId, 'continue' => 'true']
        );
    }
}
