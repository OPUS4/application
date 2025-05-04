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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * TODO write invalid ORCID iDs to file
 * TODO output only invalid iDs to console
 */
class Application_Console_Orcid_InfoCommand extends Command
{
    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
Shows general info about ORCID iDs in database.
EOT;

        $this->setName('orcid:info')
            ->setDescription('Shows info about ORCiD IDs in database')
            ->setHelp($help);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $persons = Repository::getInstance()->getModelRepository(Person::class);

        $allIdentifierOrcid       = $persons->getAllIdentifierOrcid();
        $allUniqueIdentifierOrcid = $persons->getAllUniqueIdentifierOrcid();

        $orcidValuesCount = count($allIdentifierOrcid);
        $uniqueOrcidCount = count($allUniqueIdentifierOrcid);

        $output->writeln("{$orcidValuesCount} ORCID iD values");
        $output->writeln("{$uniqueOrcidCount} Unique ORCID iDs");

        $validator = new Application_Form_Validate_Orcid();

        $invalidOrcidValues = [];

        if ($output->isVerbose()) {
            $output->writeln('');
            $output->writeln("--- Invalid ORCID iDs");
        }

        foreach ($allUniqueIdentifierOrcid as $orcidId) {
            if (! $validator->isValid($orcidId)) {
                $invalidOrcidValues[] = $orcidId;
                $output->writeln($orcidId, OutputInterface::VERBOSITY_VERBOSE);
            }
        }

        if ($output->isVerbose()) {
            $output->writeln("---");
            $output->writeln('');
        }

        $invalidOrcidCount = count($invalidOrcidValues);

        $output->writeln("{$invalidOrcidCount} Invalid ORCID iDs");

        $orcidWithUrlCount = $this->countOrcidWithUrl($invalidOrcidValues);

        $output->writeln("{$orcidWithUrlCount} ORCID iD values with URL prefix");

        return 0;
    }

    /**
     * @param string[] $values
     * @return int
     */
    public function countOrcidWithUrl($values)
    {
        $orcidWithUrlCount = 0;
        foreach ($values as $orcidId) {
            if (preg_match('/^https?:\/\/orcid.org\/.*/i', trim($orcidId))) {
                $orcidWithUrlCount++;
            }
        }
        return $orcidWithUrlCount;
    }
}
