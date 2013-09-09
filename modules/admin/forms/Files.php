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
 */

/**
 * Formular fuer alle Dateien eines Dokuments.
 *
 * @category    Application
 * @package     Admin_Form
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */
class Admin_Form_Files extends Admin_Form_DocumentMultiSubForm {

    const ELEMENT_IMPORT = 'Import';

    public function __construct($options = null) {
        parent::__construct('Admin_Form_File', 'File', $options);
    }

    public function init() {
        parent::init();

        $this->setLegend('admin_document_section_files');

        $this->getDecorator('FieldsetWithButtons')->setLegendButtons(array(self::ELEMENT_IMPORT, self::ELEMENT_ADD));
    }

    protected function initButton() {
        parent::initButton();
        $this->addElement('submit', self::ELEMENT_IMPORT, array('order' => 1002, 'label' => 'button_file_import',
            'decorators' => array(), 'disableLoadDefaultDecorators' => true));
    }

    public function processPost($post, $context) {
        $result = parent::processPost($post, $context);

        if (is_null($result)) {
            if (array_key_exists(self::ELEMENT_IMPORT, $post)) {
                $result = array(
                    'result' => Admin_Form_Document::RESULT_SWITCH_TO,
                    'target' => array(
                        'module' => 'admin',
                        'controller' => 'filebrowser',
                        'action' => 'index'
                    )
                );
            }
        }

        return $result;
    }

    protected function processPostAdd() {
        // Hinzufuegen wurde ausgewaehlt
        return array(
            'result' => Admin_Form_Document::RESULT_SWITCH_TO,
            'target' => array(
                'module' => 'admin',
                'controller' => 'filemanager',
                'action' => 'upload'
            )
        );
    }

    public function updateFromPost($post) {
        foreach ($post as $file) {
            $fileId = $file['Id'];
            $subform = $this->getSubFormForId($fileId);
            if (!is_null($subform)) {
                $subform->populate($post);
            }
        }
    }

    public function getSubFormForId($fileId) {
        foreach ($this->getSubForms() as $subform) {
            if ($subform->getElementValue(Admin_Form_File::ELEMENT_ID) == $fileId) {
                return $subform;
            }
        }
        return null;
    }

}

/*
     /**
     * Action for deleting a file.
     *
     * The action redirects the request to a confirmation form bevor actually
     * deleting the file.
     *
     * TODO catch invalid file IDs
     *
public function deleteAction() {
    $docId = $this->getRequest()->getParam('docId');
    $fileId = $this->getRequest()->getParam('fileId');

    $documentsHelper = $this->_helper->getHelper('Documents');

    $document = $documentsHelper->getDocumentForId($docId);

    if (!isset($document)) {
        return $this->_redirectToAndExit('index', array('failure' =>
        $this->view->translate('admin_document_error_novalidid')), 'documents', 'admin');
    }

    if (!$this->_isValidFileId($fileId)) {
        return $this->_redirectToAndExit('index', array('failure' =>
        $this->view->translate('admin_filemanager_error_novalidid')), 'filemanager', 'admin', array('docId' => $docId));
    }

    if (!$this->_isFileBelongsToDocument($docId, $fileId)) {
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
 *
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
 *
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
 *
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

 */
