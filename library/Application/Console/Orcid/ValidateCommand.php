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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Validates ORCID iD values and tags documents for a search facet.
 */
class Application_Console_Orcid_ValidateCommand extends Command
{
    public const OPTION_TAG = 'tag';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
Validates ORCID iD values for persons in database. Optionally the associated documents
can be tagged if an invalid iD is found.
EOT;

        $this->setName('orcid:validate')
            ->setDescription('Validates ORCID iDs in database')
            ->setHelp($help)
            ->addOption(
                self::OPTION_TAG,
                't',
                InputOption::VALUE_NONE,
                'Tag documents with invalid ORCID iDs'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $taggingEnabled = $input->getOption(self::OPTION_TAG);

        $output->writeln('Tagging of documents enabled', OutputInterface::VERBOSITY_VERBOSE);

        $validate = new Application_Orcid_ValidateAllIdentifierOrcid();
        $validate->setTaggingEnabled($taggingEnabled);
        $validate->setOutput($output);
        $validate->run();

        $taggedCount = count($validate->getTaggedDocuments());
        if ($taggedCount > 0) {
            $output->writeln("{$taggedCount} Documents tagged with invalid ORCID iDs");
        }

        $cleanedCount = count($validate->getCleanedDocuments());
        if ($cleanedCount > 0) {
            $output->writeln("{$cleanedCount} Documents no longer tagged with invalid ORCID iDs");
        }

        return 0;
    }
}
