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
 * @copyright   Copyright (c) 2023, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\ConfigTrait;
use Opus\Common\LoggingTrait;

/**
 * Class to read configuration data for tasks
 */
class Application_Task_TaskConfigReader
{
    use ConfigTrait;
    use LoggingTrait;

    /**
     * Name of the default configuration file where all tasks to be run are defined.
     */
    const INI_FILE = 'tasks.ini';

    /** @var Zend_Config_Ini */
    protected $tasksConfig;

    public function __construct()
    {
        $this->tasksConfig = $this->getConfiguration();
    }

    /**
     * Returns the task configuration from the task ini file.
     * Name of the INI file to be used for configuration, if not set, the default configuration file will be used
     *
     * @return Zend_Config_Ini
     * @throws Application_Task_TaskConfigException If the task config does not exist or is invalid.
     */
    private function getConfiguration()
    {
        $config = $this->getConfig();

        $fileName = isset($config->cron->configFile) && $config->cron->configFile ? $config->cron->configFile : self::INI_FILE;

        if (strpos($fileName, '/') === false) {
            $fileName = __DIR__ . '/../../../application/configs/' . $fileName;
        }

        if (! is_readable($fileName)) {
            throw new Application_Task_TaskConfigException("could not find or read ini file '$fileName'");
        } else {
            $tasksConfig = new Zend_Config_Ini($fileName);
            if ($tasksConfig === false) {
                throw new Application_Task_TaskConfigException("could not parse ini file '$fileName'");
            }
        }

        return $tasksConfig;
    }

    /**
     * Determines all task configurations for existing task classes.
     *
     * @return array
     */
    public function getTaskConfigurations()
    {
        $tasks = [];
        foreach ($this->tasksConfig as $name => $config) {
            if (
                isset($config->class)
                && class_exists($config->class)
            ) {
                $tasks[$name] = $this->createTaskConfig($name, $config);
            }
        }

        return $tasks;
    }

    /**
     * Determines all active task configurations for existing task classes.
     *
     * @return array
     */
    public function getActiveTaskConfigurations()
    {
        return array_filter($this->getTaskConfigurations(), function ($taskConfig) {
            if ($taskConfig->isEnabled()) {
                return $taskConfig;
            }
        });
    }

    /**
     * Gets task configuration by name
     *
     * @param array $name
     * @return Application_Task_TaskConfig|false
     */
    public function getTaskConfig($name)
    {
        if (isset($this->tasksConfig->$name)) {
            $taskConfig = $this->tasksConfig->$name;

            if (isset($taskConfig->class) && class_exists($taskConfig->class)) {
                return $this->createTaskConfig($name, $taskConfig);
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @param mixed $config
     * @return Application_Task_TaskConfig
     */
    protected function createTaskConfig($name, $config)
    {
        $taskConfig = new Application_Task_TaskConfig();

        $taskConfig->setName($name);

        $taskConfig->setClass($config->class ?? '');

        $taskConfig->setSchedule(
            $config->schedule ?? Application_Task_TaskConfig::SCHEDULE_DEFAULT
        );

        $taskConfig->setPreventOverlapping(
            isset($config->preventOverlapping) &&
            filter_var($config->preventOverlapping, FILTER_VALIDATE_BOOLEAN)
        );

        $taskConfig->setEnabled(
            isset($config->enabled) &&
            filter_var($config->enabled, FILTER_VALIDATE_BOOLEAN)
        );

        $options = [];
        if (isset($config->options)) {
            foreach ($config->options as $optionName => $optionValue) {
                $options[$optionName] = $optionValue;
            }
        }

        $taskConfig->setOptions($options);

        return $taskConfig;
    }
}
