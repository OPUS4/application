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

use Opus\Common\Collection;
use Opus\Common\CollectionInterface;
use Opus\Common\CollectionRole;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Application_Console_Collection_RemoveCommand extends Command
{
    public const OPTION_COL_ID = 'col-id';

    public const OPTION_ROLE_NAME = 'role-name';

    public const OPTION_ROLE_OAI_NAME = 'role-oai';

    public const OPTION_COL_NUMBER = 'col-number';

    public const OPTION_FILTER_COL_ID = 'filter-col-id';

    public const OPTION_FILTER_ROLE_NAME = 'filter-role-name';

    public const OPTION_FILTER_ROLE_OAI_NAME = 'filter-role-oai';

    public const OPTION_FILTER_COL_NUMBER = 'filter-col-number';

    public const OPTION_UPDATE_DATE_MODIFIED = 'update-date-modified';

    /** @var CollectionInterface */
    protected $targetCol;

    /** @var CollectionInterface */
    protected $filterCol;

    /** @var bool */
    protected $updateDateModified = false;

    protected function configure()
    {
        parent::configure();

        $help = <<<EOT
The <fg=green>collection:remove</> command can be used to remove documents from a collection. 

If a filter collection is specified, only the documents in the filter collection are removed 
from the target collection.
EOT;

        $this->setName('collection:remove')
            ->setDescription('Removes documents from collection')
            ->setHelp($help)
            ->addOption(
                self::OPTION_COL_ID,
                'c',
                InputOption::VALUE_REQUIRED,
                'ID of collection'
            )->addOption(
                self::OPTION_COL_NUMBER,
                null,
                InputOption::VALUE_REQUIRED,
                'Number of collection'
            )->addOption(
                self::OPTION_ROLE_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Name of collection role'
            )->addOption(
                self::OPTION_ROLE_OAI_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'OAI name of collection role'
            )->addOption(
                self::OPTION_FILTER_COL_ID,
                'f',
                InputOption::VALUE_REQUIRED,
                'ID of collection for filtering'
            )->addOption(
                self::OPTION_FILTER_COL_NUMBER,
                null,
                InputOption::VALUE_REQUIRED,
                'Number of collection for filtering'
            )->addOption(
                self::OPTION_FILTER_ROLE_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'Name of collection role for filtering'
            )->addOption(
                self::OPTION_FILTER_ROLE_OAI_NAME,
                null,
                InputOption::VALUE_REQUIRED,
                'OAI name of collection role for filtering'
            )->addOption(
                self::OPTION_UPDATE_DATE_MODIFIED,
                'u',
                InputOption::VALUE_NONE,
                'Update ServerDateModified of documents'
            );
    }

    protected function processOptions(InputInterface $input)
    {
        $this->updateDateModified = $input->getOption(self::OPTION_UPDATE_DATE_MODIFIED);

        // Get target collection from ID
        $colId = $input->getOption(self::OPTION_COL_ID);

        if ($colId !== null) {
            $this->targetCol = Collection::get($colId);
        } else {
            // Get target collection from role and collection number
            $roleName = $input->getOption(self::OPTION_ROLE_NAME);
            $role     = null;
            if ($roleName === null) {
                $roleOaiName = $input->getOption(self::OPTION_ROLE_OAI_NAME);
                if ($roleOaiName !== null) {
                    $role = CollectionRole::fetchByOaiName($roleOaiName);
                }
            } else {
                $role = CollectionRole::fetchByName($roleName);
            }

            $colNumber = $input->getOption(self::OPTION_COL_NUMBER);

            if ($colNumber !== null && $role !== null) {
                $collections     = Collection::getModelRepository()->fetchCollectionsByRoleNumber(
                    $role->getId(),
                    $colNumber
                );
                $this->targetCol = $collections[0];
            }
        }

        // Get filter collection from ID
        $filterColId = $input->getOption(self::OPTION_FILTER_COL_ID);

        if ($filterColId !== null) {
            $this->filterCol = Collection::get($filterColId);
        } else {
            // Get filter collection from role and collection number
            $filterRoleName = $input->getOption(self::OPTION_FILTER_ROLE_NAME);
            $filterRole     = null;
            if ($filterRoleName === null) {
                $filterRoleOaiName = $input->getOption(self::OPTION_FILTER_ROLE_OAI_NAME);
                if ($filterRoleOaiName !== null) {
                    $filterRole = CollectionRole::fetchByOaiName($filterRoleOaiName);
                }
            } else {
                $filterRole = CollectionRole::fetchByName($filterRoleName);
            }

            $filterColNumber = $input->getOption(self::OPTION_FILTER_COL_NUMBER);

            if ($filterColNumber !== null && $filterRole !== null) {
                $collections     = Collection::getModelRepository()->fetchCollectionsByRoleNumber(
                    $filterRole->getId(),
                    $filterColNumber
                );
                $this->filterCol = $collections[0];
            }
        }
    }

    /**
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->processOptions($input);

        $targetCol = $this->targetCol;
        $filterCol = $this->filterCol;

        if ($targetCol === null) {
            $output->writeln('Collection must be specified');
            return 0;
        }

        $targetId = $targetCol->getId();

        $targetDocuments = $targetCol->getDocumentIds();
        $targetCount     = count($targetDocuments);

        if ($targetCount === 0) {
            $output->writeln("Collection (ID = ${targetId}) does not contain documents.");
            $output->writeln('');
            $output->writeln('  "' . $targetCol->getDisplayName() . '"');
            $output->writeln('');
            return 0;
        }

        $removeDocuments = null;

        if ($filterCol === null) {
            $output->writeln("Remove all documents (${targetCount}) from collection (ID = ${targetId})");
            $output->writeln('');
            $output->writeln('  "' . $targetCol->getDisplayName() . '"');
            $output->writeln('');
            $removeDocuments = $targetDocuments;
        } else {
            $filterId        = $filterCol->getId();
            $filterDocuments = $filterCol->getDocumentIds();
            $filterCount     = count($filterDocuments);

            if ($filterCount === 0) {
                $output->writeln("The filter collection (ID = ${filterId}) does not contain any documents.");
                $output->writeln('');
                $output->writeln('  "' . $filterCol->getDisplayName() . '"');
                $output->writeln('');
                return 0;
            }

            $removeDocuments = array_intersect($filterDocuments, $targetDocuments);
            $removeCount     = count($removeDocuments);

            if ($removeCount === 0) {
                $output->writeln("Non of the documents in the filter collection (ID = ${filterId})");
                $output->writeln('');
                $output->writeln('  "' . $filterCol->getDisplayName() . '"');
                $output->writeln('');
                $output->writeln("is present in the target collection (ID= ${targetId})");
                $output->writeln('');
                $output->writeln('  "' . $targetCol->getDisplayName() . '"');
                $output->writeln('');
                return 0;
            }

            $output->writeln("Remove documents (${filterCount}) in collection (ID = ${filterId})");
            $output->writeln('');
            $output->writeln('  "' . $filterCol->getDisplayName() . '"');
            $output->writeln('');
            $output->writeln("from collection (ID= ${targetId})");
            $output->writeln('');
            $output->writeln('  "' . $targetCol->getDisplayName() . '"');
            $output->writeln('');
        }

        if ($this->updateDateModified) {
            $output->writeln('NOTE: ServerDateModified of documents will be updated');
        } else {
            $output->writeln('NOTE: ServerDateModified of documents will NOT be updated');
        }
        $output->writeln('');

        $askHelper = $this->getHelper('question');
        $question  = new ConfirmationQuestion('Remove documents [Y|n]?', true);

        if ($askHelper->ask($input, $output, $question)) {
            $targetCol->removeDocuments($removeDocuments, $this->updateDateModified);
        } else {
            $output->writeln('Removing cancelled');
        }

        return 0;
    }
}
