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
 * Formular fuer den Upload von Dateien in der Administration.
 *
 * Das Upload Formular erweitert Admin_Form_File damit potentiell alle Informationen gleich beim Upload eingegeben
 * werden können. Momentan werden aber nur einige Felder angezeigt.
 *
 * - File
 * - Label
 * - Kommentar
 * - Language
 *
 * @category    Application
 * @package     Admin_Form_File
 * @author      Henning Gerhardt (henning.gerhardt@slub-dresden.de)
 * @author      Jens Schwidder <schwidder@zib.de>
 * @author      Maximilian Salomon <salomon@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Admin_Form_File_Upload extends Application_Form_Model_Abstract
{

    const ELEMENT_HASH       = 'OpusHash';
    const ELEMENT_FILE       = 'File';
    const ELEMENT_LABEL      = 'Label';
    const ELEMENT_COMMENT    = 'Comment';
    const ELEMENT_LANGUAGE   = 'Language';
    const ELEMENT_SORT_ORDER = 'SortOrder';

    const SUBFORM_DOCINFO    = 'Info';

    private $_fileInfo = null;

    public function init()
    {
        parent::init();

        $this->addSubForm(new Admin_Form_InfoBox(), self::SUBFORM_DOCINFO);

        $this->setAttrib('enctype', Zend_Form::ENCTYPE_MULTIPART);
        $this->setLegend('admin_filemanager_upload');
        $this->setLabelPrefix('Opus_File_');
        $this->setUseNameAsLabel(true);

        $element = $this->createElement(
            'file',
            self::ELEMENT_FILE,
            [
            'required' => true,
            'label' => 'admin_filemanager_element_file',
            ]
        );

        $config = $this->getApplicationConfig();

        $filenameOptions = [
            'filenameMaxLength' => $config->publish->filenameMaxLength,
            'filenameFormat' => $config->publish->filenameFormat
        ];
        $filenameValidator = new Application_Form_Validate_Filename($filenameOptions);

        $element->addValidator($filenameValidator, false);
        $element->addValidator('Count', false, 1); // ensure only 1 file

        $this->addElement($element);

        $this->addElement('Language', self::ELEMENT_LANGUAGE, ['label' => 'Language', 'required' => true]);
        $this->addElement('text', self::ELEMENT_LABEL);
        $this->addElement('textarea', self::ELEMENT_COMMENT);
        $hash = $this->createElement('hash', self::ELEMENT_HASH, ['salt' => 'unique']);
        $hash->addDecorator('HtmlTag', ['tag' => 'div']);
        $this->addElement($hash);

        $this->addElement('SortOrder', self::ELEMENT_SORT_ORDER);

        $this->getElement(self::ELEMENT_MODEL_ID)->setRequired(true);
    }

    public function populateFromModel($document)
    {
        $this->getSubForm(self::SUBFORM_DOCINFO)->populateFromModel($document);
        $this->getElement(self::ELEMENT_MODEL_ID)->setValue($document->getId());
    }

    /**
     * Speichert Datei und verknüpft sie mit dem Dokument.
     *
     * @param Opus_Model_AbstractDb $document
     */
    public function updateModel($document)
    {
        $files = $this->getFileInfo();

        foreach ($files as $file) {
            /* TODO Uncaught exception 'Zend_File_Transfer_Exception' with message '"fileupload" not found by file
            * TODO (continued) transfer adapter
            * if (!$upload->isValid($file)) {
            *    $this->view->message = 'Upload failed: Not a valid file!';
            *    break;
            * }
            */
            $docfile = $document->addFile();

            $docfile->setLabel($this->getElementValue(self::ELEMENT_LABEL));
            $docfile->setComment($this->getElementValue(self::ELEMENT_COMMENT));
            $docfile->setLanguage($this->getElementValue(self::ELEMENT_LANGUAGE));
            $docfile->setSortOrder($this->getElementValue(self::ELEMENT_SORT_ORDER));

            $docfile->setPathName(urldecode($file['name']));
            $docfile->setMimeType($file['type']);
            $docfile->setTempFile($file['tmp_name']);
        }
    }

    public function getFileInfo()
    {
        if (is_null($this->_fileInfo)) {
            $upload = new Zend_File_Transfer_Adapter_Http();
            return $upload->getFileInfo();
        } else {
            return $this->_fileInfo;
        }
    }

    public function setFileInfo($fileInfo)
    {
        $this->_fileInfo = $fileInfo;
    }
}
