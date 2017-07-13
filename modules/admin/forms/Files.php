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
 * @author      Michael Lang <lang@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
class Admin_Form_Files extends Admin_Form_Document_MultiSubForm {

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
        $this->addElement(
            'submit', self::ELEMENT_IMPORT, array('order' => 1002, 'label' => 'button_file_import',
            'decorators' => array(), 'disableLoadDefaultDecorators' => true)
        );
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

    protected function processPostRemove($subFormName, $subdata) {
        if (isset($subdata[Admin_Form_File::ELEMENT_ID])) {
            $fileId = $subdata[Admin_Form_File::ELEMENT_ID];
        }
        else {
            // no fileId specified (manipulated POST)
            // TODO error message
            return Admin_Form_Document::RESULT_SHOW;
        }

        // Hinzufuegen wurde ausgewaehlt
        return array(
            'result' => Admin_Form_Document::RESULT_SWITCH_TO,
            'target' => array(
                'module' => 'admin',
                'controller' => 'filemanager',
                'action' => 'delete',
                'fileId' => $fileId
            )
        );
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

    public function continueEdit($request, $post = null) {
        $removedFileId = $request->getParam('fileId'); // TODO make robuster

        if (is_array($post)) {
            foreach ($post as $file) {
                if (isset($file['Id']))
                {
                    $fileId = $file['Id'];
                    $subform = $this->getSubFormForId($fileId);
                    if (!is_null($subform))
                    {
                        if ($fileId != $removedFileId)
                        {
                            $subform->populate($file);
                        } else
                        {
                            $this->removeSubForm($subform->getName());
                        }
                    }
                }
            }
        }
        else {
            $subform = $this->getSubFormForId($removedFileId);

            if (!is_null($subform)) {
                $this->removeSubForm($subform->getName());
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

    /**
     * Liefert Opus_File objects for document through getFile function to get proper order of files.
     * @param Opus_Document $document
     * @return array Array of Opus_File objects
     */
    public function getFieldValues($document) {
        return $document->getFile();
    }

}