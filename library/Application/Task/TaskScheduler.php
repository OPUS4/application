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

use Crunz\Event;
use Crunz\Schedule;
use Opus\Common\ConfigTrait;
use Opus\Common\LoggingTrait;

class Application_Task_TaskScheduler
{
    use ConfigTrait;
    use LoggingTrait;

    /**
     * Name of the default configuration file where all tasks to be run are defined.
     */
    const INI_FILE = 'tasks.ini';

    /**
     * @return Schedule
     */
    public function run()
    {
        $log              = $this->getLogger();
        $schedule         = new Schedule();
        $tasksConfig      = $this->getConfiguration();
        $taskRunnerScript = $this->getTaskRunnerPath();

        if ($this->isEnabled()) {
            foreach ($tasksConfig as $taskConfig) {
                if (
                    isset($taskConfig->class)
                    && isset($taskConfig->schedule)
                    && filter_var($taskConfig->enabled, FILTER_VALIDATE_BOOLEAN)
                ) {
                    $taskOptions = [];
                    if (isset($taskConfig->options)) {
                        foreach ($taskConfig->options as $optionName => $optionValue) {
                            $taskOptions[$optionName] = $optionValue;
                        }
                    }

                    $task = $schedule->run(
                        PHP_BINARY . " " . $taskRunnerScript,
                        [
                            '--taskclass'   => $taskConfig->class,
                            '--taskoptions' => serialize($taskOptions),
                        ]
                    );

                    $task
                        ->cron($taskConfig->schedule)
                        ->description($taskConfig->class);

                    if (
                        isset($taskConfig->preventOverlapping) &&
                        filter_var($taskConfig->preventOverlapping, FILTER_VALIDATE_BOOLEAN)
                    ) {
                        $task->preventOverlapping();
                    }

                    $schedule
                        ->onError(function (Event $evt) use (&$error) {
                            $error .= $evt->getExpression() . ' ' . $evt->buildCommand() . PHP_EOL;
                            throw new Exception($error);
                        });
                } else {
                    $log->err("Cron task class name or schedule not configured");
                }
            }
        } else {
            $log->err("Couldn't access task configuration from ini file");
        }

        return $schedule;
    }

    /**
     * Returns the cron task configuration from the ini file.
     * Name of the INI file to be used for configuration, if not set, the default configuration file will be used
     *
     * @return array|mixed
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
     * Gets the full path of the task runner script from the main configuration.
     *
     * @return string
     * @throws Application_Task_TaskConfigException If the taskRunner is not configured.
     */
    private function getTaskRunnerPath()
    {
        $config = $this->getConfig();

        if (! isset($config->cron->taskRunner)) {
            throw new Application_Task_TaskConfigException(
                "could not read the task runner path from 'application.ini'"
            );
        }

        if (! is_readable($config->cron->taskRunner)) {
            throw new Application_Task_TaskConfigException(
                "could not find or read task runner file '" . $config->cron->taskRunner . "'"
            );
        }

        return $config->cron->taskRunner;
    }

    /**
     * Checks if task scheduling is enabled in the main configuration
     *
     * @return bool
     */
    private function isEnabled()
    {
        $config = $this->getConfig();
        return filter_var($config->cron->enabled, FILTER_VALIDATE_BOOLEAN) && $this->getTaskRunnerPath();
    }
}
