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
 * @copyright   Copyright (c) 2025, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Person;
use Opus\Common\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Application_Console_Person_CleanCommand extends Command
{
    public const OPTION_KEEP = 'keep';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
Deletes Person objects without link to document.
EOT;

        $this->setName('person:clean')
            ->setDescription('Removes Person objects without document')
            ->setHelp($help)
            ->addOption(
                self::OPTION_KEEP,
                'k',
                InputOption::VALUE_NONE,
                'Keep Person objects with identifiers (ORCID iD, ...)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $questionHelper = $this->getHelper('question');

        $question = new ConfirmationQuestion(
            "Do you want to delete all Person objects without link to documents [y|N]?",
            false
        );

        if (! $questionHelper->ask($input, $output, $question)) {
            return 0;
        }

        $keep = $input->getOption(self::OPTION_KEEP);

        $persons = Repository::getInstance()->getModelRepository(Person::class);

        $unlinkedPersonsCount = $persons->getOrphanedPersonsCount();
        $output->writeln("{$unlinkedPersonsCount} Person objects without document found");

        if ($unlinkedPersonsCount > 0) {
            $output->writeln('Deleting Person objects not linked to a document...');
            $persons->deleteOrphanedPersons($keep);
            $output->writeln('Done');
        }

        $unlinkedPersonsCount = $persons->getOrphanedPersonsCount();

        $output->writeln("{$unlinkedPersonsCount} Person objects without document remaining");

        return 0;
    }
}
