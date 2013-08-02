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
 * @package     Controller
 * @author      Ralf Claussnitzer (ralf.claussnitzer@slub-dresden.de)
 * @author      Thoralf Klein <thoralf.klein@zib.de>
 * @copyright   Copyright (c) 2008-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Loads languages from modules.  When registered as FrontController plugin
 * it hooks into dispatchLoopStartup().
 *
 * TODO this plugin only initializes languages.  Rename?
 *
 * @category    Application
 * @package     Controller
 */
class Controller_Plugin_ModulePrepare extends Zend_Controller_Plugin_Abstract {

    /**
     * Holds the name of the directory that contains application modules.
     *
     * @var string
     */
    protected $_path_to_modules = null;

    /**
     * Setup the plugin with directories to contain modules and special classes.
     *
     * @param string $module_path_name Name of the directory that contains application modules.
     */
    public function __construct($module_path_name) {
        $this->_path_to_modules = $module_path_name;
    }

    /**
     * Hooks into preDispatch to setup include path for every request.
     *
     * @param Zend_Controller_Request_Abstract $request The request passed to the FrontController.
     * @return void
     *
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        $current_module = $request->getModuleName();

        // Add translation
        if ($current_module !== 'default') {
            $moduleDir = $this->_path_to_modules . "/$current_module/";
            $this->_loadLanguageDirectory("$moduleDir/language/");
            $this->_loadLanguageDirectory("$moduleDir/language_custom/");
        }
    }

    /**
     * Load the given language directory.
     *
     * @param string $directory
     * @return boolean
     */
    protected function _loadLanguageDirectory($directory) {
        $directory = realpath($directory);
        if (($directory === false) or (!is_dir($directory)) or (!is_readable($directory))) {
            return false;
        }

        $handle = opendir($directory);
        if (!$handle) {
            return false;
        }

        $translate = Zend_Registry::get('Zend_Translate');
        $options = array(
            'logUntranslated' => true,
            'logMessage' => "Unable to translate key '%message%' into locale '%locale%'",
            'log' => Zend_Registry::get('Zend_Log'),

            'adapter' => Zend_Translate::AN_TMX,
            'locale' => 'auto',
            'clear' => false,
            'scan' => Zend_Translate::LOCALE_FILENAME,
            'ignore' => '.',
            'disableNotices' => true
        );

        while (false !== ($file = readdir($handle))) {
            // Ignore directories.
            if (!is_file($directory . DIRECTORY_SEPARATOR . $file)) {
                continue;
            }

            // Ignore files with leading dot and files without extension tmx.
            if (preg_match('/^[^.].*\.tmx$/', $file) === 0) {
                continue;
            }

            $translate->addTranslation(array_merge(array(
                        'content' => $directory . DIRECTORY_SEPARATOR . $file,
            ), $options));
        }

        return true;
    }

}
