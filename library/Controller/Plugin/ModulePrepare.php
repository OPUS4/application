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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

/**
 * Provides adding paths of helpers, forms, additional classes etc. for
 * controllers dynamicly. When registered as FrontController plugin it hooks into
 * dispatchLoopStartup().
 *
 * TODO this plugin does several things, split up?
 *
 * @category    Application
 * @package     Controller
 */
class Controller_Plugin_ModulePrepare extends Zend_Controller_Plugin_Abstract {

    /**
     * Holds reference to front controller instance.
     *
     * @var unknown_type
     */
    protected $_front = null;
    /**
     * Holds the name of the directory that contains application modules.
     *
     * @var string
     */
    protected $_path_to_modules = null;
    /**
     * Holds a generated name for path variables that do not belong
     * to an specific module.
     *
     * @var string
     */
    protected $_nomodule_key = null;
    /**
     * Contains all paths the plugin has been given on initialization.
     *
     * @var array
     */
    protected $_paths = array();

    /**
     * Setup the plugin with directories to contain modules and special classes.
     *
     * @param string $module_path_name Name of the directory that contains application modules.
     */
    public function __construct($module_path_name) {
        $this->_nomodule_key = md5('nomodule');
        $this->_front = Zend_Controller_Front::getInstance();
        $this->_path_to_modules = $module_path_name;
    }

    /**
     * Append a class path. Forewards to _appendPath();
     *
     * @param string $cp     Path relative to model path that contains required classes.
     * @param string $module (Optional) Name of a module if the class path is specific to that module.
     * @return Opus_Controller_Plugin_ModulePrepare Returns the plugin itself for providing a fluent interface.
     */
    public function appendClassPath($cp, $module = null) {
        return $this->_appendPath('class', $cp, $module);
    }

    /**
     * Append a view helper path. Forwards to _appendPath();
     *
     * @param string $vhp    Relative path to module specific view helpers.
     * @param string $module (Optional) Name of a module if the pattern is specific to that module.
     * @return unknown	Returns the plugin itself for providing a fluent interface.
     */
    public function appendViewHelperPath($vhp, $module = null) {
        return $this->_appendPath('viewhelper', $vhp, $module);
    }

    /**
     * Append a given path to an appropriate path list depending on the path's
     * type.
     *
     * @param string $type   Type of the path e.g. 'viewhelper'.
     * @param string $path   The path pattern itself e.g. 'views/helpers'
     * @param string $module (Optional) Name of a module if the pattern is specific to that module.
     * @return Opus_Controller_Plugin_ModulePrepare Returns the plugin itself for providing a fluent interface.
     */
    protected function _appendPath($type, $path, $module = null) {
        if (empty($module) === true) {
            $module = $this->_nomodule_key;
        }
        if (array_key_exists($module, $this->_paths) === false) {
            $this->_paths[$module] = array();
        }
        $module_struct = &$this->_paths[$module];
        if (array_key_exists($type, $module_struct) === false) {
            $module_struct[$type] = array();
        }
        $viewhelperpath_struct = &$module_struct[$type];
        $viewhelperpath_struct[] = $path;
        return $this;
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
        if (array_key_exists($current_module, $this->_paths) === true) {
            $current_module_paths = $this->_paths[$current_module];
        }
        $module_paths = array();
        $module_paths[] = $this->_paths[$this->_nomodule_key];
        if (isset($current_module_paths) === true) {
            $module_paths[] = $current_module_paths;
        }

        foreach ($module_paths as $path) {
            // Add classes to include_path.
//            if (array_key_exists('class', $path) === true) {
//                $class_paths = $path['class'];
//                $include_paths = explode(PATH_SEPARATOR, get_include_path());
//                foreach ($class_paths as $cp) {
//                    $include_paths[] = "$this->_path_to_modules/$current_module/$cp";
//                }
//                set_include_path(implode(PATH_SEPARATOR, $include_paths));
//            }
            // Add translation
            if ($current_module !== 'default') {
                $languageDir = "$this->_path_to_modules/$current_module/language/";
                if (file_exists($languageDir) === true) {
                    if ($handle = opendir($languageDir)) {
                        while (false !== ($file = readdir($handle))) {
                            // ignore directories
                            if (is_dir($file) === true)
                                continue;
                            // ignore files with leading dot and files without extension tmx
                            if (preg_match('/^[^.].*\.tmx$/', $file) === 0)
                                continue;
                            $translate = Zend_Registry::get('Zend_Translate');
                            $options = array(
                                'adapter' => Zend_Translate::AN_TMX,
                                'locale' => 'auto',
                                'clear' => false,
                                'scan' => Zend_Translate::LOCALE_FILENAME,
                                'ignore' => '.',
                                'disableNotices' => true
                            );
                            $translate->addTranslation(array_merge(array(
                                'content' => $languageDir . $file,
                            ), $options));
                        }
                    }
                }
            }
            // Add view helper paths.
            if (array_key_exists('viewhelper', $path) === true) {
                $view = Zend_Layout::getMvcInstance()->getView();
                $viewhelper_paths = $path['viewhelper'];
                foreach ($viewhelper_paths as $vhp) {
                    $view->addHelperPath("$this->_path_to_modules/$current_module/$vhp");
                }
            }
        }
    }

    // TODO move code here
    protected function _addViewHelperPaths() {
    }

}
