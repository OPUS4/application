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

use Opus\Job\TaskConfig;
use Opus\Job\TaskManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Displays the configuration data of a task.
 */
class Application_Console_Task_InfoCommand extends Command
{
    public const ARGUMENT_TASK_NAME = 'TaskName';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
The <fg=green>task:info</> command shows detailed information about a background task.
EOT;

        $this->setName('task:info')
            ->setDescription('Shows information about a background task.')
            ->setHelp($help)
            ->addArgument(
                self::ARGUMENT_TASK_NAME,
                InputArgument::OPTIONAL,
                'Name of the background task'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $taskName = $input->getArgument(self::ARGUMENT_TASK_NAME);

        $taskManager        = new TaskManager();
        $taskConfigurations = $taskManager->getTaskConfigurations();

        if (empty($taskConfigurations)) {
            $output->writeln('<fg=red>There are no tasks configured</>');
            return self::FAILURE;
        }

        if (empty($taskName)) {
            $askHelper = $this->getHelper('question');

            $availableTasks = [];

            /** @var TaskConfig $taskConfig */
            foreach ($taskConfigurations as $taskConfig) {
                $availableTasks[] = $taskConfig->getName();
            }

            $question = new ChoiceQuestion(
                'Please select the task you want show:',
                $availableTasks,
                -1
            );

            $question->setErrorMessage('Please select a task');
            $taskName = $askHelper->ask($input, $output, $question);
        }

        $taskConfig = $taskManager->getTaskConfig($taskName);
        if (! $taskConfig) {
            $output->writeln('Task not found: <fg=red>' . $taskName . '</>');
            return self::FAILURE;
        }

        // show
        $headers = ['Task-Name', 'Active', 'Schedule', 'Prevent overlapping', 'Task-Class'];

        $taskInfo[] = $taskConfig->getName();
        $taskInfo[] = $taskConfig->isEnabled() ? 'yes' : 'no';
        $taskInfo[] = $taskConfig->getSchedule();
        $taskInfo[] = $taskConfig->isPreventOverlapping() ? 'yes' : 'no';

        if (! $taskManager->isValidTaskClass($taskConfig->getClass())) {
            $taskInfo[] = $taskConfig->getClass() . '<fg=red> (invalid value)</>';
        } else {
            $taskInfo[] = $taskConfig->getClass();
        }

        $options = [];
        if (is_array($taskConfig->getOptions())) {
            foreach ($taskConfig->getOptions() as $optionName => $optionValue) {
                $headers[]  = 'Task-Class-Parameter (' . $optionName . ')';
                $taskInfo[] = $optionValue;
            }
        }

        $table = new Table($output);

        $table
            ->setHeaders($headers)
            ->setVertical()
            ->setRows([$taskInfo])
            ->setStyle('compact')
            ->render();

        return self::SUCCESS;
    }
}
