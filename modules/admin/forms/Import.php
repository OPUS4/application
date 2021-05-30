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
 * @author      Sascha Szott <opus-repository@saschaszott.de>
 * @copyright   Copyright (c) 2021, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Form for uploading metadata file, e.g. BibTeX file.
 */
class Admin_Form_Import extends Application_Form_Abstract
{

    const ELEMENT_FILE = 'File';

    const ELEMENT_VERBOSE = 'Verbose';

    const ELEMENT_COLLECTION_IDS = 'CollectionIds';

    const ELEMENT_DRY_MODE = 'DryMode';

    const ELEMENT_IMPORT = 'Import';

    const ELEMENT_MAPPING_NAME = 'MappingName';

    const ELEMENT_INI_FILENAME = 'IniFilename';

    public function init()
    {
        parent::init();

        $this->addElement(
            'file',
            self::ELEMENT_FILE,
            [
                'label' => 'admin_import_file',
                'required' => true
            ]
        );

        $this->addElement(
            'text',
            self::ELEMENT_INI_FILENAME,
            [
                'label' => 'admin_import_ini_filename',
                'size' => 50
            ]
        );

        $this->addElement(
            'text',
            self::ELEMENT_MAPPING_NAME,
            [
                'label' => 'admin_import_mapping_name',
                'size' => 50
            ]
        );

        $this->addElement(
            'text',
            self::ELEMENT_COLLECTION_IDS,
            [
                'label' => 'admin_import_collection_ids',
                'size' => 50
            ]
        );

        $this->addElement(
            'checkbox',
            self::ELEMENT_VERBOSE,
            [
                'label' => 'admin_import_verbose'
            ]
        );

        $this->addElement(
            'checkbox',
            self::ELEMENT_DRY_MODE,
            [
                'label' => 'admin_import_dry_mode'
            ]
        );

        $this->addElement(
            'submit',
            self::ELEMENT_IMPORT,
            [
                'label' => 'admin_import_start'
            ]
        );

        $this->setDecorators([
            'FormElements',
            'Form'
        ]);
    }
}
