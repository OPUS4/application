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
 * @copyright   Copyright (c) 2024, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\EnrichmentKey;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command for deleting enrichments.
 *
 * TODO Should there be a command for just removing enrichment from documents?
 * TODO Should there be a command for just removing the configuration without removing the values?
 */
class Application_Console_Model_EnrichmentDeleteCommand extends Command
{
    public const ARGUMENT_KEY = 'key';

    public const OPTION_FORCE = 'force';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
The <fg=green>enrichment:delete</> command can be used to delete enrichments. The command
also removes the translations associated with the enrichment. 
EOT;

        $this->setName('enrichment:delete')
            ->setDescription('Rename enrichment')
            ->setHelp($help)
            ->addArgument(
                self::ARGUMENT_KEY,
                InputArgument::OPTIONAL,
                'Enrichment key'
            )->addOption(
                self::OPTION_FORCE,
                'f',
                InputOption::VALUE_NONE,
                'Do not prompt for confirmation.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $key = $input->getArgument(self::ARGUMENT_KEY);

        if ($key === null) {
            $output->writeln('<error>Enrichment key is required</error>');
            return 1;
        }

        $enrichment = EnrichmentKey::fetchByName($key);

        if ($enrichment === null) {
            $output->writeln("<error>Enrichment key \"{$key}\" not found</error>");
            return 1;
        }

        if (! $input->getOption(self::OPTION_FORCE)) {
            $questionText = "<question>Do you want to remove enrichment key \"{$key}\" (Y/n)?</question>";
            $confirmation = new ConfirmationQuestion($questionText, true);
            $question     = $this->getHelper('question');

            if (! $question->ask($input, $output, $confirmation)) {
                $output->writeln("Not removing enrichment key \"{$key}\".");
                return 0;
            }
        }

        $output->writeln("Removing enrichment key \"{$key}\".");
        $enrichment->delete();

        $output->writeln("Removing translations for enrichment key \"{$key}\".");
        $helper = new Admin_Model_EnrichmentKeys();
        $helper->removeTranslations($key);

        return 0;
    }
}
