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

use Opus\Common\Config\ConfigException;
use Opus\Common\Document;
use Opus\Common\Model\NotFoundException;
use Opus\Common\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shows differences between two or more documents.
 *
 * TODO support different levels of detail
 */
class Application_Console_Document_DiffCommand extends Command
{
    public const OPTION_DOI = 'doi';

    public const OPTION_SERVER_STATE = 'server-state';

    public const OPTION_IGNORE_DELETED = 'ignore-deleted';

    public const ARGUMENT_DOC_ID = 'DocID';

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
Shows the differences between two or more documents.

Multiple document IDs can be provided as argument. The fields of the documents
are then compared and differences reported.

If a DOI is provided, using the <fg=green>--doi</> Option, the database is
searched for matching documents. If more than one is found, the differences
between the documents are reported.

<fg=red>NOTES:
- Complex values like persons and patents are not shown with all their metadata.
- For text values, like titles and abstracts, the exact differences are not 
  highlighted. The report just shows that the values are different.    
</>
EOT;

        $this->setName('document:diff')
            ->setDescription('Shows differences between documents.')
            ->setHelp($help)
            ->addOption(
                self::OPTION_DOI,
                null,
                InputOption::VALUE_REQUIRED,
                'DOI value'
            )
            ->addOption(
                self::OPTION_SERVER_STATE,
                's',
                InputOption::VALUE_REQUIRED,
                'Include docs in state (DOI) - <fg=yellow>unpublished</>, <fg=yellow>published</>, <fg=yellow>inprogress</>, <fg=yellow>audited</>, <fg=yellow>restricted</>, <fg=yellow>deleted</>'
            )
            ->addOption(
                self::OPTION_IGNORE_DELETED,
                null,
                InputOption::VALUE_NONE,
                'Ignore deleted documents (DOI)'
            )
            ->addArgument(
                self::ARGUMENT_DOC_ID,
                InputArgument::OPTIONAL + InputArgument::IS_ARRAY,
                'Two or more document IDs'
            );
    }

    /**
     * @return int
     * @throws NotFoundException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $diff = new Application_Document_DocumentDiff();
        $diff->setOutput($output);

        $docIds = $this->getDocumentsInput($input, $output);

        $docCount = count($docIds);

        if ($docCount === 0) {
            $output->writeln('No documents for comparison');
        } elseif ($docCount === 1) {
            $docId = $docIds[0];
            $output->writeln("Only one document for comparison (ID = $docId)");
        } else {
            $diff->diff($docIds);
        }

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int[]
     * @throws ConfigException
     */
    protected function getDocumentsInput($input, $output)
    {
        $docIds = $input->getArgument(self::ARGUMENT_DOC_ID);

        if (count($docIds) === 0) {
            $doi = $input->getOption(self::OPTION_DOI);

            if ($doi !== null) {
                $finder = Repository::getInstance()->getDocumentFinder();
                $finder->setIdentifierValue('doi', $doi);

                $serverStates = $this->getServerStateInput($input, $output);

                if ($serverStates !== null && count($serverStates) < 6) {
                    $finder->setServerState($serverStates);
                }

                $docIds = $finder->getIds();

                if (count($docIds) === 0) {
                    $output->writeln("No documents found for DOI: $doi");
                }
            }
        }

        return $docIds;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return string[]|null
     */
    protected function getServerStateInput($input, $output)
    {
        $validStates = $this->getValidServerStates();

        if ($input->getOption(self::OPTION_IGNORE_DELETED)) {
            unset($validStates['deleted']);
        }

        $serverStateOption = $input->getOption(self::OPTION_SERVER_STATE);

        $serverStates = [];

        if ($serverStateOption !== null) {
            $values = explode(',', $serverStateOption);
            foreach ($values as $state) {
                if (in_array(strtolower($state), $validStates)) {
                    $serverStates[$state] = $state;
                } else {
                    $output->writeln("Invalid ServerState value: $state");
                }
            }
        } else {
            $serverStates = $validStates;
        }

        return $serverStates;
    }

    /**
     * @return string[]
     *
     * TODO better way of getting valid states (configurable states in the future?)
     */
    protected function getValidServerStates()
    {
        $doc   = Document::new();
        $field = $doc->getField('ServerState');
        return $field->getDefault();
    }
}
