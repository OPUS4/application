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

/**
 * Management for modules.
 *
 * TODO is this the right package?
 */
class Application_Modules
{
    /**
     * Instance of modules manager.
     *
     * @var self
     */
    private static $moduleManager;

    /**
     * Path for modules.
     *
     * @var string
     */
    private $modulesPath;

    /**
     * Descriptors for all modules.
     *
     * @var array
     */
    private $modules;

    /**
     * Descriptors for explicitly registered modules.
     *
     * @var array
     */
    private $registeredModules = [];

    /**
     * Prevent direct instantiation of class.
     */
    private function __construct()
    {
    }

    /**
     * Return instance of module management class.
     *
     * @return self
     */
    public static function getInstance()
    {
        if (self::$moduleManager === null) {
            self::$moduleManager = new Application_Modules();
        }

        return self::$moduleManager;
    }

    /**
     * @param self $modules
     */
    public static function setInstance($modules)
    {
        self::$moduleManager = $modules;
    }

    /**
     * Register a module with the manager.
     *
     * @param Application_Configuration_Module $module
     */
    public static function registerModule($module)
    {
        self::getInstance()->addModule($module);
    }

    /**
     * Check is a module has been registered.
     *
     * @param string $name
     * @return bool
     */
    public function isRegistered($name)
    {
        if (is_array($this->registeredModules)) {
            return array_key_exists($name, $this->registeredModules);
        } else {
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
        if ($this->modules === null) {
            $this->modules = array_merge($this->findModules(), $this->registeredModules);
        }

        ksort($this->modules);

        return $this->modules;
    }

    /**
     * Returns path to directory containing modules.
     *
     * @return string
     */
    public function getModulesPath()
    {
        if ($this->modulesPath === null) {
            $this->modulesPath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'modules';
        }

        return $this->modulesPath;
    }

    /**
     * Iterates over module directories and returns all module names
     *
     * @return array List of module names
     */
    public function findModules()
    {
        $modulesPath = $this->getModulesPath();

        $modules = [];

        foreach (new DirectoryIterator($modulesPath) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue; // ignore '.' and '..'
            }
            if ($fileInfo->isFile()) {
                continue; // ignore files
            }

            $name = $fileInfo->getBasename();

            if (substr($name, 0, 1) === '.') {
                continue; // ignore folders starting with a dot
            }

            // ignore directories without 'controllers' subdirectory
            $controllersPath = $fileInfo->getRealPath() . DIRECTORY_SEPARATOR . 'controllers';
            if (! is_dir($controllersPath)) {
                continue;
            }

            $modules[$name] = new Application_Configuration_Module($name);
        }

        return $modules;
    }

    /**
     * Internal function for adding a module to registry.
     *
     * @param Application_Configuration_Module $module
     */
    protected function addModule($module)
    {
        if ($this->registeredModules === null) {
            $this->registeredModules = [];
        }

        $this->registeredModules[$module->getName()] = $module;
    }
}
