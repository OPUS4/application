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
 * @package     Module_Export
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2013-2014, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Export_Model_PublicationList for usage in publist XSLT.
 *
 * Die Klasse wird im XSLT fuer die Publikationslisten verwendet, um auf die Konfiguration zuzugreifen.
 *
 * TODO is there a better solution, that fits better with the plugin model?
 * TODO Doesn't work with multiple configurations for same plugin.
 */
class Export_Model_PublicationList {

    protected static $allowedMimeTypes;
    
    /**
     * Initialize the mime types from configuration
     */
    public static function initMimeTypes() {
        $config = Zend_Registry::get('Zend_Config');
        self::$allowedMimeTypes =
            isset($config->plugins->export->publist->file->allow->mimetype) ?
                $config->plugins->export->publist->file->allow->mimetype->toArray() : array();
    }
    
    /**
     * Return the display name as configured for a specific mime type
     * @param string $mimeType Mime type to get display name for. 
     * If mime type is not configured, an empty string is returned.
     * 
     * @result string display name for mime type
     */
    public static function getMimeTypeDisplayName($mimeType) {
        if(!is_array(self::$allowedMimeTypes)) {
            self::initMimeTypes();
        }
        return isset(self::$allowedMimeTypes[$mimeType]) ? self::$allowedMimeTypes[$mimeType] : '';
    }

}