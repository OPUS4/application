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

/*
 * The scheduler class to run all configured active opus tasks.
 * Used by the scheduledTask script which will be by a single cron job,
 * to be configured in the crontab: * * * * * cd /your-opus-directory && vendor/bin/crunz schedule:run
 */
class Application_Task_TaskScheduler
{
    use ConfigTrait;
    use LoggingTrait;

    /**
     * Initializes the scheduler with all active tasks by adding them to a Crunz scheduler.
     *
     * @return Schedule The Crunz scheduler instance required by our single Crunz task
     */
    public function init()
    {
        $log      = $this->getLogger();
        $schedule = new Schedule();

        if ($this->isEnabled()) {
            foreach ($this->getActiveTaskConfigurations() as $taskConfig) {
                $crunzTask = $schedule->run(
                    PHP_BINARY . " " . $this->getTaskRunnerScriptPath(),
                    ['--taskname' => $taskConfig->getName()]
                );

                if ($taskConfig->isPreventOverlapping()) {
                    $crunzTask->preventOverlapping();
                }

                $crunzTask
                    ->cron($taskConfig->getSchedule())
                    ->description($taskConfig->getName());

                $schedule
                    ->onError(function (Event $evt) use (&$error) {
                        $error .= $evt->getExpression() . ' ' . $evt->buildCommand() . PHP_EOL;
                        throw new Exception($error);
                    });
            }
        } else {
            $log->err("Couldn't access task scheduler configuration from ini file");
        }

        return $schedule;
    }

    /**
     * Gets the full path of the task runner script from the main configuration.
     *
     * @return string|null
     */
    public function getTaskRunnerScriptPath()
    {
        $config = $this->getConfig();
        $log    = $this->getLogger();

        $taskRunner = $config->cron->taskRunner;

        if (! isset($taskRunner)) {
            $log->err("Could not read the task runner path from configuration");
        }

        if (! is_readable($taskRunner)) {
            $log->err("Could not find or read task runner file: '" . $taskRunner . "'");
        }

        return $taskRunner;
    }

    /**
     * Checks if task scheduling is enabled in the main configuration
     *
     * @return bool
     */
    private function isEnabled()
    {
        $config = $this->getConfig();
        return filter_var($config->cron->enabled, FILTER_VALIDATE_BOOLEAN) &&
            $this->getTaskRunnerScriptPath();
    }

    /**
     * Gets the configurations of the active tasks.
     *
     * @return array
     */
    public function getActiveTaskConfigurations()
    {
        $taskManager = new Application_Task_TaskManager();
        return $taskManager->getActiveTaskConfigurations();
    }
}
