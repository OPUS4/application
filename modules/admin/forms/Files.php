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

use Opus\Common\DocumentInterface;

/**
 * Formular fuer alle Dateien eines Dokuments.
 */
class Admin_Form_Files extends Admin_Form_Document_DefaultMultiSubForm
{
    public const ELEMENT_IMPORT = 'Import';

    /**
     * @param array|null $options
     * @throws Application_Exception
     */
    public function __construct($options = null)
    {
        parent::__construct('Admin_Form_File', 'File', $options);
    }

    public function init()
    {
        parent::init();

        $this->setLegend('admin_document_section_files');

        $this->getDecorator('FieldsetWithButtons')->setLegendButtons([self::ELEMENT_IMPORT, self::ELEMENT_ADD]);
    }

    protected function initButton()
    {
        parent::initButton();
        $this->addElement(
            'submit',
            self::ELEMENT_IMPORT,
            [
                'order'                        => 1002,
                'label'                        => 'button_file_import',
                'decorators'                   => [],
                'disableLoadDefaultDecorators' => true,
            ]
        );
    }

    /**
     * @param array $post
     * @param array $context
     * @return array|string|null
     */
    public function processPost($post, $context)
    {
        $result = parent::processPost($post, $context);

        if ($result === null) {
            if (array_key_exists(self::ELEMENT_IMPORT, $post)) {
                $result = [
                    'result' => Admin_Form_Document::RESULT_SWITCH_TO,
                    'target' => [
                        'module'     => 'admin',
                        'controller' => 'filebrowser',
                        'action'     => 'index',
                    ],
                ];
            }
        }

        return $result;
    }

    /**
     * @param string $subFormName
     * @param array  $subdata
     * @return array|string
     */
    protected function processPostRemove($subFormName, $subdata)
    {
        if (isset($subdata[Admin_Form_File::ELEMENT_ID])) {
            $fileId = $subdata[Admin_Form_File::ELEMENT_ID];
        } else {
            // no fileId specified (manipulated POST)
            // TODO error message
            return Admin_Form_Document::RESULT_SHOW;
        }

        // Hinzufuegen wurde ausgewaehlt
        return [
            'result' => Admin_Form_Document::RESULT_SWITCH_TO,
            'target' => [
                'module'     => 'admin',
                'controller' => 'filemanager',
                'action'     => 'delete',
                'fileId'     => $fileId,
            ],
        ];
    }

    /**
     * @return array
     */
    protected function processPostAdd()
    {
        // Hinzufuegen wurde ausgewaehlt
        return [
            'result' => Admin_Form_Document::RESULT_SWITCH_TO,
            'target' => [
                'module'     => 'admin',
                'controller' => 'filemanager',
                'action'     => 'upload',
            ],
        ];
    }

    /**
     * @param Zend_Controller_Request_Http $request
     * @param array|null                   $post
     */
    public function continueEdit($request, $post = null)
    {
        $removedFileId = (int) $request->getParam('fileId'); // TODO make robuster

        if (is_array($post)) {
            foreach ($post as $file) {
                if (isset($file['Id'])) {
                    $fileId  = (int) $file['Id'];
                    $subform = $this->getSubFormForId($fileId);
                    if ($subform !== null) {
                        if ($fileId !== $removedFileId) {
                            $subform->populate($file);
                        } else {
                            $this->removeSubForm($subform->getName());
                        }
                    }
                }
            }
        } else {
            $subform = $this->getSubFormForId($removedFileId);

            if ($subform !== null) {
                $this->removeSubForm($subform->getName());
            }
        }
    }

    /**
     * @param int $fileId
     * @return Admin_Form_File|null
     */
    public function getSubFormForId($fileId)
    {
        foreach ($this->getSubForms() as $subform) {
            if ($subform->getElementValue(Admin_Form_File::ELEMENT_ID) === $fileId) {
                // TODO TYPE string === int ?
                return $subform;
            }
        }
        return null;
    }

    /**
     * Liefert File objects for document through getFile function to get proper order of files.
     *
     * @param DocumentInterface $document
     * @return array Array of File objects
     */
    public function getFieldValues($document)
    {
        return $document->getFile();
    }
}
