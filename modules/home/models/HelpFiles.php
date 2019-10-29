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
 * @package     Module_Home
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2012, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Model for encapsuling access to help files.
 *
 * Using this class it is easy to change the location of the files or add a
 * mechanism for overwritting the standard files with custom files in similar
 * to the 'language' and 'language_custom' folders.
 *
 * TODO add handling of language (English/German) to this class
 */
class Home_Model_HelpFiles
{

    /**
     * Returns the path to the help files.
     * @return string Path to help files
     */
    public static function getHelpPath()
    {
        return APPLICATION_PATH . '/application/configs/help/';
    }

    /**
     * Returns the contant of a help file.
     * @param string $file File basename
     * @return string Content of file
     */
    public static function getFileContent($file)
    {
        $path = Home_Model_HelpFiles::getHelpPath() . $file;
        if (! is_null($file) && file_exists($path) && is_readable($path)) {
            return file_get_contents($path);
        } else {
            return null;
        }
    }

    /**
     * Returns available help files.
     * @return array Basenames of help files
     */
    public static function getFiles()
    {
        $helpFilesAvailable = [];
        $dir = new DirectoryIterator(Home_Model_HelpFiles::getHelpPath());
        foreach ($dir as $file) {
            if ($file->isFile() && $file->getFilename() != '.' && $file->getFilename() != '..' && $file->isReadable()
                    && pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'txt') {
                array_push($helpFilesAvailable, $file->getBasename());
            }
        }
        return $helpFilesAvailable;
    }

    public static function getHelpEntries()
    {
        $config = Home_Model_HelpFiles::getHelpConfig();

        $data = $config->toArray();

        return $data;
    }

    /**
     * Stores help configuration after reading it for the first time.
     * @var array
     */
    private static $_helpConfig;

    /**
     * Loads help configuration.
     * @return Zend_Config_Ini
     */
    private static function getHelpConfig()
    {
        if (empty(Home_Model_HelpFiles::$_helpConfig)) {
            $config = null;

            $filePath = Home_Model_HelpFiles::getHelpPath() . 'help.ini';

            if (file_exists($filePath)) {
                try {
                    $config = new Zend_Config_Ini($filePath);
                } catch (Zend_Config_Exception $zce) {
                    // TODO einfachere Lösung?
                    $logger = Zend_Registry::get('Zend_Log');
                    if (! is_null($logger)) {
                        $logger->err("could not load help configuration", $zce);
                    }
                }
            }

            if (is_null($config)) {
                $config = new Zend_Config([]);
            }

            Home_Model_HelpFiles::$_helpConfig = $config;
        }

        return Home_Model_HelpFiles::$_helpConfig;
    }
}
