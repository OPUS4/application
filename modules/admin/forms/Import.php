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
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Bibtex\Import\Config\BibtexConfigException;
use Opus\Bibtex\Import\Config\BibtexService;

/**
 * Form for uploading metadata file, e.g. BibTeX file.
 */
class Admin_Form_Import extends Application_Form_Abstract
{
    public const ELEMENT_FILE = 'File';

    public const ELEMENT_VERBOSE = 'Verbose';

    public const ELEMENT_COLLECTION_IDS = 'Collections';

    public const ELEMENT_DRY_MODE = 'DryMode';

    public const ELEMENT_IMPORT = 'Import';

    public const ELEMENT_MAPPING = 'Mapping';

    public function init()
    {
        parent::init();

        $this->addElement(
            'file',
            self::ELEMENT_FILE,
            [
                'label'    => 'admin_import_file',
                'required' => true,
            ]
        );

        $mapping = $this->getBibtexMappingSelect();
        $this->addElement($mapping);

        $this->addElement(
            'checkbox',
            self::ELEMENT_VERBOSE,
            [
                'label' => 'admin_import_verbose',
            ]
        );

        $this->addElement(
            'checkbox',
            self::ELEMENT_DRY_MODE,
            [
                'label' => 'admin_import_dry_mode',
            ]
        );

        $this->addElement(
            'submit',
            self::ELEMENT_IMPORT,
            [
                'label'      => 'admin_import_start',
                'decorators' => [
                    'ViewHelper',
                    [['liWrapper' => 'HtmlTag'], ['tag' => 'li', 'class' => 'save-element']],
                ],
            ]
        );

        $this->addElement(
            'collectionAutoComplete',
            'CollectionIds', // TODO different name for input to avoid conflicts with 'Collections' - fix
            [
                'label'       => 'admin_import_collections',
                'description' => 'admin_import_collection_ids_hint', // TODO not used right now -> see view helper
            ]
        );

        $this->addDisplayGroup(
            [self::ELEMENT_IMPORT],
            'actions',
            [
                'order'      => 1000,
                'decorators' => [
                    'FormElements',
                    [['ulWrapper' => 'HtmlTag'], ['tag' => 'ul', 'class' => 'form-action']],
                    [['divWrapper' => 'HtmlTag'], ['id' => 'form-action']],
                ],
            ]
        );

        $this->setDecorators([
            'FormElements',
            'Form',
        ]);
    }

    /**
     * @return Zend_Form_Element
     * @throws BibtexConfigException
     */
    protected function getBibtexMappingSelect()
    {
        $mappingElement = $this->createElement('select', self::ELEMENT_MAPPING, [
            'label' => 'admin_import_mapping_name',
        ]);

        $bibtex = BibtexService::getInstance();

        $mappingNames = $bibtex->listAvailableMappings();

        $options = [];

        foreach ($mappingNames as $name) {
            $mapping = $bibtex->getFieldMapping($name);

            $translator     = $this->getTranslator();
            $translationKey = "bibtex_mapping_description_$name";

            if ($translator->isTranslated($translationKey)) {
                $description = $translationKey;
            } else {
                $description = $mapping->getDescription();
            }

            $mappingElement->addMultiOption($name, $description);
        }

        return $mappingElement;
    }
}
