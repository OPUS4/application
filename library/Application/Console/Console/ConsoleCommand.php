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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Console command to interactively execute PHP code.
 */
class Application_Console_Console_ConsoleCommand extends Command
{
    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
The <fg=green>console:console</> command can be used to interactively execute PHP code.
EOT;

        $this->setName('console:console')
            ->setDescription('Interactively executes PHP code')
            ->setHelp($help);
    }

    /**
     * Executes this command to run the passed PHP code.
     *
     * @return int 0 in case of success, otherwise an exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while ($input !== false) {
            $input = readline('opus> ');
            readline_add_history($input);
            try {
                eval($input);
            } catch (Exception $e) {
                $output->writeln('# failed executing code:');
                $output->writeln('Caught exception ' . get_class($e) . ': ' . $e->getMessage());
                $output->writeln($e->getTraceAsString());

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }
}
