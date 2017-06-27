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
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2017, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * TODO cleanup, especially "plugin config" vs. "plugin"
 * TOOD merge with Application_Export_Exporter?
 */
class Application_Export_ExportService extends Application_Model_Abstract
{

    /**
     * @var array containing export plugins
     */
    private $_plugins = null;

    /**
     * @var array Default configuration for plugins
     */
    private $_defaults = null;

    /**
     * Returns plugin for action name.
     *
     * The plugin is setup for execution.
     *
     * @param $name Name of plugin/action.
     * @return null|Application_Export_ExportPlugin
     *
     * TODO should the namespace for plugins be limited (security)?
     */
    public function getPlugin($name)
    {
        $plugins = $this->getAllPlugins();

        if (isset($plugins[$name]))
        {
            $pluginConfig = $plugins[$name];
            $pluginClass = $pluginConfig->class;

            $plugin = new $pluginClass($name); // TODO good design?
            $plugin->setConfig($pluginConfig);

            return $plugin;
        }
        else
        {
            return null;
        }
    }

    /**
     * Returns all plugin configurations.
     *
     * @return array
     *
     * TODO rename
     */
    public function getAllPlugins()
    {
        if (is_null($this->_plugins))
        {
            $this->loadPlugins();
        }

        return $this->_plugins;
    }

    /**
     * Loads export plugins.
     *
     * Der Plugin spezifische Teil der Konfiguation wird festgehalten und später verwendet.
     *
     * TODO rename loadDefaultPlugins (resets loaded plugins back to configuration)
     */
    public function loadPlugins()
    {
        $config = $this->getConfig();

        if (isset($config->plugins->export))
        {
            $exportPlugins = $config->plugins->export->toArray();

            foreach ($exportPlugins as $name => $plugin)
            {
                $pluginName = ($name === 'default') ? 'index' : $name;

                $this->addPlugin($pluginName, $config->plugins->export->$name);
            }
        }
    }

    /**
     * Set default parameters for plugins.
     * @param $config
     */
    public function setDefaults($config)
    {
        $this->_defaults = $config;
    }

    /**
     * Returns default parameters for plugins.
     *
     * @return array|Zend_Config
     */
    public function getDefaults()
    {
        if (is_null($this->_defaults))
        {
            $config = $this->getConfig();

            if (isset($config->plugins->export->default))
            {
                $this->_defaults = $config->plugins->export->default;
            }
            else {
                $this->_defaults = new Zend_Config(array());
            }
        }

        return $this->_defaults;
    }

    /**
     * Adds a plugin configuration.
     * @param $config array
     */
    public function addPlugin($name, $config)
    {
        if (is_null($this->_plugins))
        {
            $this->_plugins = array();
        }

        $defaults = $this->getDefaults();
        $pluginConfig = clone $defaults;
        $pluginConfig->merge($config);
        $this->_plugins[$name] = $pluginConfig;
    }


}
