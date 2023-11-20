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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StreamableInputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Reports and removes or tags duplicate documents.
 *
 * TODO support CSV output
 * TODO support --from
 * TODO support --until
 * TODO support checkout all documents
 * TODO make removable an explicit option like --remove
 * TODO use URL for generating links to found documents in CSV report
 */
class Application_Console_Document_DuplicatesCommand extends Command
{
    public const OPTION_DOI = 'doi';

    public const OPTION_DOI_FILE = 'doi-file';

    public const OPTION_OUTPUT_FILE = 'output';

    public const OPTION_FROM = 'from';

    public const OPTION_UNTIL = 'until';

    public const OPTION_FORMAT = 'format';

    public const OPTION_DRYRUN = 'dry-run';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
Removes or tags duplicate documents by checking DOIs.
EOT;

        $this->setName('document:duplicates')
            ->setDescription('Removes duplicate documents by checking DOIs.')
            ->setHelp($help)
            ->addOption(
                self::OPTION_DOI,
                null,
                InputOption::VALUE_REQUIRED,
                'Single DOI'
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
            );
    }

    /**
     * @return int
     * @throws NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = new Application_Util_RemoveDocumentsByDoi();
        $helper->setOutput($output);

        if ($input->getOption(self::OPTION_DRYRUN)) {
            $helper->setDryRunEnabled(true);
        }

        $doiValues = $this->getDoiInput($input);

        // Processing

        $doiCount = count($doiValues);

        if ($doiCount > 0) {
            $output->writeln("Checking {$doiCount} DOIs");

            foreach ($doiValues as $doi) {
                $output->writeln($doi);
            }
        } else {
            $output->write('Checkinf all documents');
            // TODO check all documents
        }

        return 0;
    }

    /**
     * Reads DOI values from STDIN or file.
     *
     * @param InputInterface $input
     * @return string[]
     */
    protected function getDoiInput($input)
    {
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

        if (strlen(trim($doiInput)) > 0) {
            return $doiValues = preg_split("/((\r?\n)|(\r\n?))/", $doiInput);
        } else {
            return [];
        }
    }
}
