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

/**
 * Basic process interface as required to define
 * tasks for job cron job
 *
 * There are two types of Tasks:
 * 1. Opus Tasks, described by this Interface.
 * 2. Crunz Tasks used by the Crunz cronjob, expectesd in the task directory specified in crunz.yml.
 * In our case there is and will be only one Crunz task: scheduledTasks.php in which the Crunz scheduler runs
 * all the configured Opus tasks.
 * An Opus task either contains direct code to complete the necessary sub-steps of a task
 * or delegates its task to a worker.
 * A worker processes a specific type of task by working through a job queue.
 * A runner hands over the jobs to be done to the worker and executes the worker.
 * TODO The runner class might be unnecessary and could eventually transformed e.g in a abstract class.
 * TODO An opus task could then inherit from it and start a worker directly.
 */
interface Application_Task_TaskInterface
{
    /**
     * Perform task.
     *
     * @return mixed
     */
    public function run();
}
