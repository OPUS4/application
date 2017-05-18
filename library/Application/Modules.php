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
 * Management for modules.
 *
 * @category    Application
 * @package     Application
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO is this the right package?
 */
class Application_Modules
{

    /**
     * Instance of modules manager.
     * @var Application_Modules
     */
    static private $_moduleManager = null;

    /**
     * Path for modules.
     * @var string
     */
    private $_modulesPath = null;

    /**
     * Descriptors for all modules.
     * @var array
     */
    private $_modules;

    /**
     * Descriptors for explicitly registered modules.
     * @var array
     */
    private $_registeredModules;

    /**
     * Prevent direct instantiation of class.
     */
    private function __construct() {
    }

    /**
     * Return instance of module management class.
     */
    static public function getInstance()
    {
        if (is_null(self::$_moduleManager))
        {
            self::$_moduleManager = new Application_Modules();
        }

        return self::$_moduleManager;
    }

    static public function setInstance($modules)
    {
        self::$_moduleManager = $modules;
    }

    /**
     * Register a module with the manager.
     * @param $module
     */
    static public function registerModule($module)
    {
        self::getInstance()->_addModule($module);
    }

    /**
     * Check is a module has been registered.
     * @param $name
     * @return bool
     */
    public function isRegistered($name)
    {
        if (is_array($this->_registeredModules))
        {
            return array_key_exists($name, $this->_registeredModules);
        }
        else {
            return false;
        }
    }

    /**
     * Returns descriptors of all modules.
     *
     * @return array
     */
    public function getModules()
    {
        if (is_null($this->_modules))
        {
            $this->_modules = array_merge($this->findModules(), $this->_registeredModules);
        }

        return $this->_modules;
    }

    /**
     * Returns path to directory containing modules.
     */
    public function getModulesPath()
    {
        if (is_null($this->_modulesPath))
        {
            $this->_modulesPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules';
        }

        return $this->_modulesPath;
    }

    /**
     * Iterates over module directories and returns all module names
     *
     * 'default' gets filtered - it must always be present and accessible
     *
     * @return array List of module names
     */
    public function findModules() {
        $modulesPath = $this->getModulesPath();

        $modules = array();

        foreach (new DirectoryIterator($modulesPath) as $fileInfo)
        {
            if ($fileInfo->isDot()) continue; // ignore '.' and '..'
            if ($fileInfo->isFile()) continue; // ignore files

            $name = $fileInfo->getBasename();

            if (substr($name, 0, 1) === '.' ) continue; // ignore folders starting with a dot

            // ignore directories without 'controllers' subdirectory
            $controllersPath = $fileInfo->getRealPath() . DIRECTORY_SEPARATOR . 'controllers';
            if (!is_dir($controllersPath)) continue;

            // filter 'default' ?
            if ($name !== 'default')
            {
                $modules[$name] = new Application_Configuration_Module($name);
            }
        }

        return $modules;
    }

    /**
     * Internal function for adding a module to registry.
     *
     * @param $module
     */
    protected function _addModule($module)
    {
        if (is_null($this->_registeredModules))
        {
            $this->_registeredModules = array();
        }

        $this->_registeredModules[$module->getName()] = $module;
    }

}
