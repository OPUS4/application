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
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Browsing of file import folder for adding files to documents.
 *
 */
class Admin_FilebrowserController extends Controller_Action {

    /**
     * Shows files in import folder.
     */
    public function indexAction() {
        $docId = $this->getRequest()->getParam('docId');
        if (is_null($docId)) {
            throw new Application_Exception('missing parameter docId');
        }

        $document = null;
        try {
            $document = new Opus_Document($docId);
        }
        catch (Opus_Model_NotFoundException $e) {
            throw new Application_Exception('no document found for id ' . $docId, null, $e);
        }

        $importHelper = new Admin_Model_FileImport();
        $this->view->files = $importHelper->listFiles();
        $this->view->document = $document;        
    }

    /**
     * Imports file(s) from import folder for document.
     */
    public function importAction() {
        if (!$this->getRequest()->isPost()) {
            throw new Application_Exception('unsupported HTTP method');
        }
        
        $docId = $this->getRequest()->getPost('docId');
        if (is_null($docId)) {
            throw new Application_Exception('missing parameter docId');
        }

        $files = $this->getRequest()->getPost('file');
        if (is_null($files) || is_array($files) && empty($files)) {
            return $this->_redirectToAndExit('index', null, 'filebrowser', 'admin', array('docId' => $docId));
        }

        if (!is_array($files)) {
            throw new Application_Exception('invalid POST parameter');
        }

        $fileImportModel = new Admin_Model_FileImport();
        $fileImportModel->addFilesToDocument($docId, $files);
        return $this->_redirectToAndExit('index', null, 'filemanager', 'admin', array('docId' => $docId));
    }
}
?>