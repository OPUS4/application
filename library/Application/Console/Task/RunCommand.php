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

use Opus\Job\TaskManager;
use Opus\Job\TaskRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Runs a single task.
 *
 * TODO unit testing
 */
class Application_Console_Task_RunCommand extends Command
{
    public const ARGUMENT_TASK_NAME = 'TaskName';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
The <fg=green>task:run</> command can be used to run a single background task directly. 
The name of the task can be given via the optional <fg=green>TaskName</> argument or
be chosen from a list of available tasks.
EOT;

        $this->setName('task:run')
            ->setDescription('Runs a single background task.')
            ->setHelp($help)
            ->addArgument(
                self::ARGUMENT_TASK_NAME,
                InputArgument::OPTIONAL,
                'Name of the task'
            );
    }

    /**
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $taskName = $input->getArgument(self::ARGUMENT_TASK_NAME);

        $taskManager        = new TaskManager();
        $taskConfigurations = $taskManager->getTaskConfigurations();

        if (empty($taskConfigurations)) {
            $output->writeln('<fg=red>There are no tasks to run</>');
            return Command::FAILURE;
        }

        if (empty($taskName)) {
            $askHelper = $this->getHelper('question');

            $availableTasks = [];

            /** @var Application_Task_TaskConfig $taskConfig */
            foreach ($taskConfigurations as $taskConfig) {
                $availableTasks[] = $taskConfig->getName();
            }

            $question = new ChoiceQuestion(
                'Please select the task you want to run:',
                $availableTasks,
                -1
            );

            $question->setErrorMessage('Please select a task');
            $taskName = $askHelper->ask($input, $output, $question);
        }

        $taskConfig = $taskManager->getTaskConfig($taskName);
        if (! $taskConfig) {
            $output->writeln('Task not found: <fg=red>' . $taskName . '</>');
            return Command::FAILURE;
        }

        if (! $taskManager->isValidTaskClass($taskConfig->getClass())) {
            $output->writeln(
                'Invalid task class <fg=red>' . $taskConfig->getClass()
                . '</> for task <fg=red>' . $taskName . '</>'
            );
            return Command::FAILURE;
        }

        $taskRunner       = new TaskRunner();
        $taskRunnerLogger = $taskRunner->getTaskLogger();
        $taskRunnerLogger->info('CLI triggered run: "' . $taskName . '"');
        $taskRunner->runTask($taskName);

        return Command::SUCCESS;
    }
}