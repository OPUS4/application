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

use Opus\Common\Model\NotFoundException;
use Opus\Doi\DoiManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reports and removes or tags duplicate documents.
 *
 * TODO support --from
 * TODO support --until
 * TODO add option for base URL for links
 */
class Application_Console_Document_DuplicatesCommand extends Command
{
    public const OPTION_DOI = 'doi';

    public const OPTION_DOI_FILE = 'doi-file';

    public const OPTION_CSV_REPORT = 'csv-report';

    public const OPTION_FROM = 'from';

    public const OPTION_UNTIL = 'until';

    public const OPTION_FORMAT = 'format';

    public const OPTION_DRYRUN = 'dry-run';

    public const OPTION_REMOVE = 'remove';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
Checks for duplicate documents using DOIs and generates a report. 

If no DOIs are provided the database is searched for all duplicate DOIs. 

If the <fg=green>--csv-report</> option is used to provide a file name, a CSV formatted
report is written, containing links to the documents found. The links depend on the 'url'
option being set in the configuration ('config.ini'). The columns in the CSV file are:

  DOI, Doc-ID, Link, Date Created, Server State

Duplicate documents can be removed automatically using the <fg=green>--remove</> option.   
<fg=red>
NOT SUPPORTED YET:
- Tagging and linking of duplicate documents for review by administrator
</>
EOT;

        $this->setName('document:duplicates')
            ->setDescription('Removes duplicate documents by checking DOIs.')
            ->setHelp($help)
            ->addOption(
                self::OPTION_DOI,
                null,
                InputOption::VALUE_REQUIRED,
                'One or more DOI values (CSV)'
            )
            ->addOption(
                self::OPTION_DOI_FILE,
                null,
                InputOption::VALUE_REQUIRED,
                'File containing DOIs (one per line)'
            )
            ->addOption(
                self::OPTION_DRYRUN,
                null,
                InputOption::VALUE_NONE,
                'Check DOIs without making changes'
            )
            ->addOption(
                self::OPTION_CSV_REPORT,
                null,
                InputOption::VALUE_REQUIRED,
                'Output file for CSV report'
            )
            ->addOption(
                self::OPTION_REMOVE,
                null,
                InputOption::VALUE_NONE,
                'Automatically remove newest duplicate document if UNPUBLISHED'
            );
    }

    /**
     * @return int
     * @throws NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $finder = new Application_Document_DuplicateFinder();
        $finder->setOutput($output);

        if ($input->getOption(self::OPTION_DRYRUN)) {
            $finder->setDryRunEnabled(true);
        }

        if ($input->getOption(self::OPTION_REMOVE)) {
            $finder->setRemoveEnabled(true);
        }

        $doiValues = $this->getDoiInput($input, $output);

        if (count($doiValues) === 0) {
            $output->writeln('Searching for duplicate DOI values in database ...');
            $doiValues = $this->getAllDuplicateDoiValues();
        }

        // Processing

        $doiCount = count($doiValues);

        if ($doiCount > 0) {
            $csvPath = $input->getOption(self::OPTION_CSV_REPORT);
            if ($csvPath !== null) {
                $csvFile = fopen($csvPath, 'w');
                $finder->setCsvFile($csvFile);
            }

            $output->writeln("Checking {$doiCount} DOI values");

            $progressBar = null;

            if ($output->getVerbosity() === $output::VERBOSITY_NORMAL) {
                $progressBar = new ProgressBar($output, $doiCount);
                $finder->setProgressBar($progressBar);
            }

            $finder->removeDuplicateDocuments($doiValues);

            if ($csvPath !== null) {
                fclose($csvFile);
            }

            if ($progressBar !== null) {
                $progressBar->finish();
                $output->writeln('');
            }
        } else {
            $output->writeln('No DOI values found');
        }

        return 0;
    }

    /**
     * Reads DOI values from STDIN or file.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return string[]
     */
    protected function getDoiInput($input, $output)
    {
        $doi = $input->getOption(self::OPTION_DOI);

        if ($doi !== null) {
            $doiInput = preg_replace('/,/', "\r\n", $doi);
        } else {
            $doiFile = $input->getOption(self::OPTION_DOI_FILE);

            if ($doiFile !== null) {
                $doiInput = file_get_contents($doiFile);
            } else {
                if ($input instanceof StreamableInputInterface) {
                    $inputStream = $input->getStream();
                }

                $inputStream = $inputStream ?? STDIN;

                stream_set_blocking(STDIN, 0);

                $doiInput = stream_get_contents($inputStream);
            }
        }

        if (strlen(trim($doiInput)) > 0) {
            $doiValues       = preg_split("/((\r?\n)|(\r\n?))/", $doiInput);
            $uniqueDoiValues = array_unique($doiValues);

            if (count($doiValues) !== count($uniqueDoiValues)) {
                $output->writeln('Duplicates entries removed from DOI list.');
            }

            return $uniqueDoiValues;
        } else {
            return [];
        }
    }

    /**
     * @return string[]
     */
    protected function getAllDuplicateDoiValues()
    {
        $doiManager = DoiManager::getInstance();

        return $doiManager->getDuplicateDoiValues();
    }
}
