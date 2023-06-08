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

abstract class Application_Console_Collection_AbstractCollectionCommand extends Command
{
    public const OPTION_SRC_COL_ID = 'src-id';

    public const OPTION_DST_COL_ID = 'dest-id';

    public const OPTION_SRC_ROLE_NAME = 'src-role';

    public const OPTION_DST_ROLE_NAME = 'dest-role';

    public const OPTION_SRC_COL_NUMBER = 'src-col';

    public const OPTION_DST_COL_NUMBER = 'dest-col';

    public const OPTION_UPDATE_DATE_MODIFIED = 'update-date-modified';

    /** @var CollectionInterface */
    protected $sourceCol;

    /** @var CollectionInterface */
    protected $destCol;

    /** @var bool */
    protected $updateDateModified = false;

    protected function configure()
    {
        parent::configure();

        $this->addOption(
            self::OPTION_SRC_COL_ID,
            's',
            InputOption::VALUE_REQUIRED,
            'ID of source collection'
        )->addOption(
            self::OPTION_DST_COL_ID,
            'd',
            InputOption::VALUE_REQUIRED,
            'ID of destination collection'
        )->addOption(
            self::OPTION_SRC_ROLE_NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Name of source collection role'
        )->addOption(
            self::OPTION_SRC_COL_NUMBER,
            null,
            InputOption::VALUE_REQUIRED,
            'Number of source collection'
        )->addOption(
            self::OPTION_DST_ROLE_NAME,
            null,
            InputOption::VALUE_REQUIRED,
            'Name of destination collection role'
        )->addOption(
            self::OPTION_DST_COL_NUMBER,
            null,
            InputOption::VALUE_REQUIRED,
            'Number of destination collection'
        )->addOption(
            self::OPTION_UPDATE_DATE_MODIFIED,
            'u',
            null,
            'Update ServerDateModified of documents'
        );
    }

    protected function processOptions(InputInterface $input)
    {
        $sourceId = $input->getOption(self::OPTION_SRC_COL_ID);
        $destId   = $input->getOption(self::OPTION_DST_COL_ID);

        if ($sourceId === null) {
            $srcRoleName  = $input->getOption(self::OPTION_SRC_ROLE_NAME);
            $srcColNumber = $input->getOption(self::OPTION_SRC_COL_NUMBER);

            if ($srcRoleName !== null && $srcColNumber !== null) {
                $role = CollectionRole::fetchByName($srcRoleName);
                if ($role === null) {
                    throw new Exception("CollectionRole with name '${srcRoleName}' not found.");
                }
                $collections = Collection::getModelRepository()->fetchCollectionsByRoleNumber(
                    $role->getId(),
                    $srcColNumber
                );
                if (count($collections) === 0) {
                    throw new Exception("No collection found for role '${srcRoleName}' and number '$srcColNumber'.");
                }
                $this->sourceCol = $collections[0];
            }
        } else {
            $this->sourceCol = Collection::get($sourceId);
        }

        if ($destId === null) {
            $destRoleName  = $input->getOption(self::OPTION_DST_ROLE_NAME);
            $destColNumber = $input->getOption(self::OPTION_DST_COL_NUMBER);

            if ($destRoleName !== null && $destColNumber !== null) {
                $role = CollectionRole::fetchByName($destRoleName);
                if ($role === null) {
                    throw new Exception("CollectionRole with name '${destRoleName}' not found.");
                }
                $collections = Collection::getModelRepository()->fetchCollectionsByRoleNumber(
                    $role->getId(),
                    $destColNumber
                );
                if (count($collections) === 0) {
                    throw new Exception("No collection found for role '${destRoleName}' and number '$destColNumber'.");
                }
                $this->destCol = $collections[0];
            }
        } else {
            $this->destCol = Collection::get($destId);
        }

        $this->updateDateModified = $input->getOption(self::OPTION_UPDATE_DATE_MODIFIED);
    }
}
