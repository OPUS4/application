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

class Admin_Model_BibtexImportException extends Exception {

    /**
     * Define all valid Exception Types.
     */
    const BINARY_NOT_INSTALLED = 1;
    const FILE_NOT_READABLE = 2;
    const FILE_NOT_UTF8 = 3;
    const FILE_NOT_BIBTEX = 4;
    const RECORD_WITHOUT_ID = 5;
    const DUPLICATE_ID = 6;  
    const BIBTEX_MODS_ERROR = 7;
    const MODS_XML_ERROR = 8;
    const INVALID_XML_ERROR = 9;
    const STORE_ERROR = 10;


    /**
     * Holds Translation-Keys for Exception Types.
     */
    protected static $_bibtexImportTranslationKeys = array(
        self::BINARY_NOT_INSTALLED => 'bibtex_import_binary_not_installed',
        self::FILE_NOT_READABLE => 'bibtex_import_file_not_readable',
        self::FILE_NOT_UTF8 => 'bibtex_import_file_not_utf8',
        self::FILE_NOT_BIBTEX => 'bibtex_import_file_not_bibtex',
	self::RECORD_WITHOUT_ID => 'bibtex_import_record_without_id',
	self::DUPLICATE_ID => 'bibtex_import_duplicate_id',
        self::BIBTEX_MODS_ERROR => 'bibtex_import_bibtex_mods_error',
        self::MODS_XML_ERROR => 'bibtex_import_mods_xml_error',
        self::INVALID_XML_ERROR => 'bibtex_import_invalid_xml_error',
        self::STORE_ERROR => 'bibtex_import_store_error'
    );


    /**
     * Map Exception Types to Translation Keys.
     */
    public static function mapTranslationKey($type) {
        if (false === array_key_exists($type, self::$_bibtexImportTranslationKeys)) {
                throw new Admin_Model_Exception($e->getMessage());
        }

        return self::$_bibtexImportTranslationKeys[$type];
    }


}
