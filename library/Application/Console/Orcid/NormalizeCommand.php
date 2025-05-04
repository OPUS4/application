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

class Application_Console_Orcid_NormalizeCommand extends Command
{
    public const OPTION_FIX = 'fix';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
Removes URL part from ORCiD IDs.
EOT;

        $this->setName('orcid:normalize')
            ->setDescription('Normalizes ORCiD ID values in database')
            ->setHelp($help)
            ->addOption(
                self::OPTION_FIX,
                'f',
                InputOption::VALUE_NONE,
                'Fix ORCID iDs with missing \'X\''
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $questionHelper = $this->getHelper('question');

        $question = new ConfirmationQuestion(
            "Do you want to remove all URL prefixes from ORCID iDs [y|N]?",
            false
        );

        if (! $questionHelper->ask($input, $output, $question)) {
            return 0;
        }

        $persons = Repository::getInstance()->getModelRepository(Person::class);

        $output->writeln('Removing URL prefixes from ORCID iDs...');
        $persons->normalizeOrcidValues();
        $output->writeln('Done');

        if ($input->getOption(self::OPTION_FIX)) {
            $this->fixOrcidValues($output);
        }

        return 0;
    }

    public function fixOrcidValues(OutputInterface $output)
    {
        $persons = Repository::getInstance()->getModelRepository(Person::class);

        $orcidValues = $persons->getAllUniqueIdentifierOrcid();

        $output->writeln('Attempting to fix ORCID iDs ...');

        foreach ($orcidValues as $orcid) {
            $fixedOrcid = $this->fixOrcid($orcid);
            if ($fixedOrcid !== null) {
                $persons->replaceOrcid($orcid, $fixedOrcid);
                $output->writeln("{$orcid} => {$fixedOrcid}");
            }
        }

        $output->writeln('Done');
    }

    /**
     * @param string $orcid
     * @return string|null
     * @throws Zend_Validate_Exception
     */
    public function fixOrcid($orcid)
    {
        $validator = new Application_Form_Validate_Orcid();

        if (! $validator->isValid($orcid)) {
            if (strlen($orcid) === 18) {
                $fixedOrcid = $orcid . 'X';

                if ($validator->isValid($fixedOrcid)) {
                    return $fixedOrcid;
                }
            }
        }

        return null;
    }
}
