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

use Opus\Common\LoggingTrait;

/**
 * This class is a helper class for scripts/task/task-runner.php to run the configured task classes
 */
class Application_Task_TaskRunner
{
    use LoggingTrait;

    /** @var string */
    private $taskClass = "";

    /** @var array */
    private $taskOptions = [];

    /**
     * Sets the options for the desired task class
     *
     * @param array $options
     */
    public function setTaskConfig($options)
    {
        // Get the name of the desired opus task class
        $this->taskClass = $options['taskclass'];

        // Get task option values if configured in the ini file.
        if (isset($options['taskoptions'])) {
            $this->taskOptions = unserialize($options['taskoptions']);
        }
    }

    /**
     * Runs the task
     */
    public function runTask()
    {
        // Run the opus task if the task class is defined
        if (class_exists($this->taskClass)) {
            // Get an instance of the desired opus task
            $task = new $this->taskClass();

            // Set option values if configured in the ini file.
            if (is_array($this->taskOptions)) {
                foreach ($this->taskOptions as $optionName => $optionValue) {
                    $setterName = 'set' . ucfirst($optionName);
                    if (method_exists($this->taskClass, $setterName)) {
                        $task->$setterName($optionValue);
                    }
                }
            }

            $task->run();
        } else {
            $this->getLogger()->err('Task class unknown: ' . $this->taskClass);
        }
    }
}
