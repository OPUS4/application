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
     * @return Schedule
     */
    public function run()
    {
        $log    = $this->getLogger();
        $config = $this->getConfig();

        $schedule = new Schedule();

        $cronScript = APPLICATION_PATH . "/" . $config->cron->taskRunner;

        if (isset($config->cron->jobs)) {
            foreach ($config->cron->jobs as $job => $options) {
                if (
                    isset($options->class)
                    && isset($options->schedule)
                    && filter_var($options->enabled, FILTER_VALIDATE_BOOLEAN)
                ) {
                    // $log->debug("Adding job " . $options->class);
                    $task = $schedule->run(PHP_BINARY . " " . $cronScript, ['--taskclass' => $options->class]);
                    $task
                        ->cron($options->schedule)
                        ->description($options->class);

                    $schedule
                        ->onError(function (Event $evt) use (&$error) {
                            $error .= $evt->getExpression() . ' ' . $evt->buildCommand() . PHP_EOL;
                            throw new Exception($error);
                        });
                } else {
                    $log->err("Cron job class name or schedule not configured");
                }
            }
        } else {
            $log->err("Couldn't access jobs from configuration file");
        }

        return $schedule;
    }
}
