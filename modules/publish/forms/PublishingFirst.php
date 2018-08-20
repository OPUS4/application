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
 * @package     Module_Publish
 * @author      Susanne Gottwald <gottwald@zib.de>
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Builds the fist page of an upload form for one file
 *
 */
class Publish_Form_PublishingFirst extends Publish_Form_PublishingAbstract {

    public $bibliographie;

    public $showRights;
    /**
     *
     * @var boolean
     */
    public $enableUpload = false;

    public function __construct() {
        parent::__construct();
    }

    public function isValid($data) {
        $valid = parent::isValid($data);

        if ($this->_config->form->first->show_rights_checkbox == 1) {
            if (array_key_exists('rights', $data)) {
                if ($data['rights'] == '0') {
                    $rights = $this->getElement('rights');
                    $rights->addError($this->view->translate('publish_error_rights_checkbox_empty'));
                    $valid = false;
                }
            }
        }

        return $valid;
    }

    /**
     * First publishing form of two forms
     * Here: Doctype + Upload-File
     *
     * @return void
     */
    public function init() {
        parent::init();

        $this->setDisableTranslator(true);

        //create and add document type
        $doctypes = $this->_createDocumentTypeField();
        $this->addElement($doctypes);

        //create and add file upload
        $this->enableUpload = ($this->_config->form->first->enable_upload == 1);
        if ($this->enableUpload) {
            $fileupload = $this->_createFileuploadField();
            $this->addDisplayGroup($fileupload, 'documentUpload');
        }

        //create and add bibliographie
        $bibliographie = $this->_createBibliographyField();
        if (!is_null($bibliographie)) {
            $this->addElement($bibliographie);
        }

        //create and add rights checkbox
        $rights = $this->_createRightsCheckBox();
        if (!is_null($rights)) {
            $this->addElement($rights);
        }

        // TODO can be removed?
        //$this->addSubmitButton('Send', 'send');

        $this->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);
        $this->setViewValues();
    }

    /**
     * Method shows the field for document types by looking in config file
     * shows selection: >1 Options
     * shows text field: =1 Option
     *
     * @return <Zend_Element>
     */
    private function _createDocumentTypeField() {
        $optionsSorted = array();
        foreach ($this->_documentTypesHelper->getDocumentTypes() as $value => $path) {
            $optionsSorted[$value] = $this->view->translate($value);
        }
        asort($optionsSorted);

        $doctypes = $this->createElement('select', 'documentType');
        $doctypes->setDisableTranslator(true)
                ->setLabel('selecttype')
                ->setMultiOptions(
                    array_merge(array('' => $this->view->translate('choose_valid_doctype')), $optionsSorted)
                )
                ->setRequired(true)
                ->setErrorMessages(array($this->view->translate('publish_error_missing_doctype')));

        return $doctypes;
    }

    /**
     * Method shows the fields for file uploads by looking in config file
     * @return <Zend_Element>
     */
    private function _createFileuploadField() {
        // get path to store files
        $tempPath = $this->_config->form->first->temp;
        if (true === empty($tempPath)) {
            $tempPath = APPLICATION_PATH . '/workspace/tmp/';
        }

        // get allowed filetypes
        $filetypes = $this->_config->publish->filetypes->allowed;
        if (true === empty($filetypes)) {
            $filetypes = 'pdf,txt,html,htm';
        }

        //get allowed file size
        $maxFileSize = (int) $this->_config->publish->maxfilesize;
        if (true === empty($maxFileSize)) {
            $maxFileSize = 1024000; //1MB
        }

        // Upload-fields required to enter second stage
        $requireUpload = $this->_config->form->first->require_upload;
        if (true === empty($requireUpload)) {
            $requireUpload = 0;
        }

        //initialization of filename-validator
        $filenameMaxLength = $this->_config->publish->filenameMaxLength;
        $filenameFormat = $this->_config->publish->filenameFormat;
        $filenameOptions = [
            'filenameMaxLength' => $filenameMaxLength,
            'filenameFormat' => $filenameFormat
        ];
        $filenameValidator = new Application_Form_Validate_Filename($filenameOptions);

        //file upload field(s)
        $fileupload = new Zend_Form_Element_File('fileupload');
        $fileupload
                ->setDisableTranslator(true)
                ->setLabel('fileupload')
                ->setDestination($tempPath)
                ->addValidator('Size', false, $maxFileSize)     // limit to value given in application.ini
                ->setMaxFileSize($maxFileSize)
                ->addValidator('Extension', false, $filetypes)  // allowed filetypes by extension
                ->setValueDisabled(true)
                ->addValidator($filenameValidator, false)       // filename-format
                ->setAttrib('enctype', 'multipart/form-data');

        if (1 == $requireUpload) {
            if (!isset($this->_session->fulltext) || $this->_session->fulltext == '0') {
                $fileupload->setRequired(true);
            }
        }
        else {
            $fileupload->setRequired(false);
        }

        $this->addElement($fileupload);

        $this->addSubmitButton('addAnotherFile', 'addAnotherFile');

        $comment = $this->createElement('textarea', 'uploadComment');
        $comment->setDisableTranslator(true);
        $comment->setLabel('uploadComment');
        $this->addElement($comment);

        $group = array($fileupload->getName(), 'addAnotherFile', $comment->getName());

        return $group;
    }

    /**
     * Method shows bibliography field by looking in config file
     * @return <Zend_Element>
     */
    private function _createBibliographyField() {
        $bib = $this->_config->form->first->bibliographie;
        if (true === empty($bib)) {
            $bib = 0;
            $this->bibliographie = 0;
        }

        $bibliographie = null;

        if ($bib == 1) {
            $this->bibliographie = 1;
            $bibliographie = $this->createElement('checkbox', 'bibliographie');
            $bibliographie->setDisableTranslator(true);
            $bibliographie->setLabel('bibliographie');
        }

        return $bibliographie;
    }

    private function _createRightsCheckBox() {
        $showRights = $this->_config->form->first->show_rights_checkbox;
        if (true === empty($showRights)) {
            $showRights = 0;
            $this->showRights = 0;
        }

        $rightsCheckbox = null;

        if ($showRights == 1) {
            $this->showRights = 1;
            $rightsCheckbox = $this->createElement('checkbox', 'rights');
            $rightsCheckbox
                    ->setDisableTranslator(true)
                    ->setLabel('rights')
                    ->setRequired(true)
                    ->setChecked(false);
        }

        return $rightsCheckbox;
    }

    /**
     * Method sets the different variables and arrays for the view and the templates in the first form
     */
    public function setViewValues() {
        foreach ($this->getElements() AS $currentElement => $value) {
            $this->view->$currentElement = $this->getElementAttributes($currentElement);
        }

        if ($this->enableUpload) {
            $displayGroup = $this->getDisplayGroup('documentUpload');

            $group = $this->buildViewDisplayGroup($displayGroup);
            $group['Name'] = 'documentUpload';
            $group['Counter'] = 2;

            $this->view->documentUpload = $group;
            $this->view->MAX_FILE_SIZE = $this->_config->publish->maxfilesize;
            $this->view->filenameMaxLength = $this->_config->publish->filenameMaxLength;
            $this->view->filenameFormat = $this->_config->publish->filenameFormat;
        }
    }

}
