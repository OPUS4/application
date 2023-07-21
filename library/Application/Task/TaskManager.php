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
class Application_Task_TaskManager
{
    use ConfigTrait;
    use LoggingTrait;

    /** @var Zend_Config */
    protected $tasksConfig;

    public function __construct()
    {
        $this->init();
    }

    /**
     * Initializes the task configuration from the task ini file.
     * The name/path of the INI file to be used should be configured in the global configuration,
     * if not set, the global configuration file will be used to determine a configuration as a fallback.
     */
    protected function init()
    {
        $config = $this->getConfig();
        $logger = $this->getLogger();

        $fileName = $config->cron->configFile ?? '';

        if ($fileName) {
            if (! is_readable($fileName)) {
                $logger->err("Could not find or read task ini file: '$fileName'");
            } else {
                $tasksConfig = new Zend_Config_Ini($fileName);
                if ($tasksConfig === false) {
                    $logger->err("Could not parse task ini file: '$fileName'");
                } else {
                    $this->tasksConfig = $tasksConfig;
                }
            }
        } else {
            if (isset($config->cron->tasks)) {
                $this->tasksConfig = $config->cron->tasks;
            }
        }
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
            if (isset($config->class)) {
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

            if (isset($taskConfig->class)) {
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

        $taskConfig->setName($name)
            ->setClass($config->class ?? '')
            ->setSchedule(
                $config->schedule ?? Application_Task_TaskConfig::SCHEDULE_DEFAULT
            );

        if (
            isset($config->preventOverlapping) &&
            false === filter_var($config->preventOverlapping, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
        ) {
            $taskConfig->setPreventOverlapping(false);
        } else {
            $taskConfig->setPreventOverlapping(true);
        }

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

    /**
     * Check if a task class exists and implements the correct task interface.
     *
     * @param string $className
     * @return bool
     */
    public function isValidTaskClass($className)
    {
        if (! class_exists($className)) {
            $this->getLogger()->err('Task class unknown: ' . $className);
            return false;
        }

        $class = new ReflectionClass($className);
        if (! $class->implementsInterface(Application_Task_TaskInterface::class)) {
            $this->getLogger()->err(
                'Task class does not implement interface: ' . Application_Task_TaskInterface::class
            );

            return false;
        }

        return true;
    }
}
