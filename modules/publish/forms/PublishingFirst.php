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

/**
 * Builds the fist page of an upload form for one file
 */
class Publish_Form_PublishingFirst extends Publish_Form_PublishingAbstract
{
    /** @var bool */
    public $bibliographie;

    /** @var bool */
    public $showRights; // TODO BUG used to be int - verify it works

    /** @var bool */
    public $enableUpload = false;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array $data
     * @return bool
     * @throws Zend_Form_Exception
     */
    public function isValid($data)
    {
        $valid = parent::isValid($data);

        if (
            isset($this->config->form->first->show_rights_checkbox) &&
            filter_var($this->config->form->first->show_rights_checkbox, FILTER_VALIDATE_BOOLEAN)
        ) {
            if (array_key_exists('rights', $data)) {
                if (! $data['rights']) {
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
     */
    public function init()
    {
        parent::init();

        $this->setDisableTranslator(true);

        //create and add document type
        $doctypes = $this->createDocumentTypeField();
        $this->addElement($doctypes);

        //create and add file upload
        $this->enableUpload = isset($this->config->form->first->enable_upload) &&
            filter_var($this->config->form->first->enable_upload, FILTER_VALIDATE_BOOLEAN);
        if ($this->enableUpload) {
            $fileupload = $this->createFileuploadField();
            $this->addDisplayGroup($fileupload, 'documentUpload');
        }

        //create and add bibliographie
        $bibliographie = $this->createBibliographyField();
        if ($bibliographie !== null) {
            $this->addElement($bibliographie);
        }

        //create and add rights checkbox
        $rights = $this->createRightsCheckBox();
        if ($rights !== null) {
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
     * @return Zend_Form_Element
     */
    private function createDocumentTypeField()
    {
        $optionsSorted = [];
        foreach ($this->documentTypesHelper->getDocumentTypes() as $value => $path) {
            $optionsSorted[$value] = $this->view->translate($value);
        }
        asort($optionsSorted);

        $doctypes = $this->createElement('select', 'documentType');
        $doctypes->setDisableTranslator(true)
                ->setLabel('selecttype')
                ->setMultiOptions(
                    array_merge(['' => $this->view->translate('choose_valid_doctype')], $optionsSorted)
                )
                ->setRequired(true)
                ->setErrorMessages([$this->view->translate('publish_error_missing_doctype')]);

        return $doctypes;
    }

    /**
     * Method shows the fields for file uploads by looking in config file
     *
     * @return array
     */
    private function createFileuploadField()
    {
        // get path to store files
        $tempPath = $this->config->form->first->temp;
        if (true === empty($tempPath)) {
            $tempPath = APPLICATION_PATH . '/workspace/tmp/';
        }

        // get allowed filetypes
        $filetypes = $this->config->publish->filetypes->allowed;
        if (true === empty($filetypes)) {
            $filetypes = 'pdf,txt,html,htm';
        }

        //get allowed file size
        $maxFileSize = (int) $this->config->publish->maxfilesize;
        if (true === empty($maxFileSize)) {
            $maxFileSize = 1024000; //1MB
        }

        //initialization of filename-validator
        $filenameMaxLength = $this->config->publish->filenameMaxLength;
        $filenameFormat    = $this->config->publish->filenameFormat;
        $filenameOptions   = [
            'filenameMaxLength' => $filenameMaxLength,
            'filenameFormat'    => $filenameFormat,
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

        // Upload-fields required to enter second stage
        if (
            isset($this->config->form->first->require_upload) &&
            filter_var($this->config->form->first->require_upload, FILTER_VALIDATE_BOOLEAN)
        ) {
            if (! isset($this->session->fulltext) || ! $this->session->fulltext) {
                // noch keine Datei zum Upload ausgewählt
                $fileupload->setRequired(true);
            }
        } else {
            $fileupload->setRequired(false);
        }

        $this->addElement($fileupload);

        $this->addSubmitButton('addAnotherFile', 'addAnotherFile');

        $comment = $this->createElement('textarea', 'uploadComment');
        $comment->setDisableTranslator(true);
        $comment->setLabel('uploadComment');
        $this->addElement($comment);

        return [$fileupload->getName(), 'addAnotherFile', $comment->getName()];
    }

    /**
     * Method shows bibliography field by looking in config file
     *
     * @return Zend_Form_Element
     */
    private function createBibliographyField()
    {
        if (
            isset($this->config->form->first->bibliographie) &&
            filter_var($this->config->form->first->bibliographie, FILTER_VALIDATE_BOOLEAN)
        ) {
            $this->bibliographie = true;
            $bibliographie       = $this->createElement('checkbox', 'bibliographie');
            $bibliographie->setDisableTranslator(true);
            $bibliographie->setLabel('bibliographie');
        } else {
            $this->bibliographie = false;
            $bibliographie       = null;
        }

        return $bibliographie;
    }

    /**
     * @return Zend_Form_Element|null
     * @throws Zend_Form_Exception
     */
    private function createRightsCheckBox()
    {
        if (
            isset($this->config->form->first->show_rights_checkbox) &&
            filter_var($this->config->form->first->show_rights_checkbox, FILTER_VALIDATE_BOOLEAN)
        ) {
            $this->showRights = true;
            $rightsCheckbox   = $this->createElement('checkbox', 'rights');
            $rightsCheckbox
                ->setDisableTranslator(true)
                ->setLabel('rights')
                ->setRequired(true)
                ->setChecked(false);
        } else {
            $this->showRights = false;
            $rightsCheckbox   = null;
        }

        return $rightsCheckbox;
    }

    /**
     * Method sets the different variables and arrays for the view and the templates in the first form
     */
    public function setViewValues()
    {
        foreach ($this->getElements() as $currentElement => $value) {
            $this->view->$currentElement = $this->getElementAttributes($currentElement);
        }

        if ($this->enableUpload) {
            $displayGroup = $this->getDisplayGroup('documentUpload');

            $group            = $this->buildViewDisplayGroup($displayGroup);
            $group['Name']    = 'documentUpload';
            $group['Counter'] = 2;

            $this->view->documentUpload    = $group;
            $this->view->MAX_FILE_SIZE     = $this->config->publish->maxfilesize;
            $this->view->filenameMaxLength = $this->config->publish->filenameMaxLength;
            $this->view->filenameFormat    = $this->config->publish->filenameFormat;
        }
    }
}
