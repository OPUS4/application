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
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_BibteximportController extends Controller_Action {

     public function indexAction() {
        $uploadForm = new Admin_Form_BibtexUpload();
        $actionUrl = $this->view->url(array('action' => 'upload'));
        $uploadForm->setAction($actionUrl);
        $this->view->form = $uploadForm;
    }

    public function uploadAction() {
        if (!$this->getRequest()->isPost()) {
            return $this->_redirectTo('index');
        }

        $postData = $this->getRequest()->getPost();
        $uploadForm = new Admin_Form_BibtexUpload();

        if (false === array_key_exists('uploadsubmit', $postData)) {
            /* Fehler: Zu große Datei hochgeladen */
            $postMaxSize = ini_get('post_max_size');
            $uploadMaxFilesize = ini_get('upload_max_filesize');
            $maxSize = ($postMaxSize > $uploadMaxFilesize) ? $uploadMaxFilesize : $postMaxSize;
   
            $message = $this->view->translate('admin_filemanager_error_upload', '>' . $maxSize);

            return $this->_redirectTo('index', array('failure' => $message));
        }

        if (!$uploadForm->isValid($postData)) {
            $message = $this->view->translate('admin_filemanager_invalid_upload');

            $errors = $uploadForm->getErrors('fileupload');
            if (!empty($errors)) {
                /* Fehler: Keine Datei ausgewählt */
                $message = $this->view->translate('admin_filemanager_error_nofile');
            }
            return $this->_redirectTo('index', array('failure' => $message));
        }
        

        try {
            $uploadForm->fileupload->receive();
        } catch (Opus_Model_Exception $e) {
            $message = $this->view->translate('admin_filemanager_error_upload');
            return $this->_redirectTo('index', array('failure' => $message));
        }
	
	register_shutdown_function(array($this, "shutdown"));	

        $location = $uploadForm->fileupload->getFileName();
        try {
            $import = new Admin_Model_BibtexImport($location);
            $import->import();
        } catch (Admin_Model_BibtexImportException $e) {
            $message = $this->view->translate($e->mapTranslationKey($e->getCode()), $e->getMessage());
            return $this->_redirectTo('index', array('failure' => $message));
        }

        $config = Zend_Registry::get('Zend_Config');
        if (isset($config->runjobs->asynchronous) && $config->runjobs->asynchronous) {
            $message = $this->view->translate('bibtex_import_success_upload', $import->getNumDocuments());
        } else {
            $message = $this->view->translate('bibtex_import_success_import', $import->getNumDocuments());
        }

        $this->_redirectTo('index', array('success' => $message));
    }
    
    
	function catchError($errno, $errstr){
            // TODO
	}    
    
	function shutdown() {
	    $last_error = error_get_last();
            if (!is_null($last_error)) {
                $this->_logger->info('an error occurred: ' . var_export($last_error, true));
                
                if ($last_error['type'] === E_ERROR) {
                    $message = $this->view->translate('bibtex_import_error_upload');
                    $this->_redirectTo('index', array('failure' => $message));
                    exit();
                }                
            }
	}    

}
