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
 * @copyright   Copyright (c) 2020, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Console\DefaultCommandProvider;
use Opus\Job\TaskManager;
use Opus\Pdf\Console\CoverGenerateCommand;
use Symfony\Component\Console\Application;

/**
 * Command line application for OPUS 4 management tasks.
 *
 * TODO CommandProvider for opus4-job (move commands)
 */
class Application_Console_App extends Application
{
    public function __construct()
    {
        parent::__construct('OPUS 4 Console Tool', Application_Configuration::getOpusVersion());

        $commandProvider = new DefaultCommandProvider();
        $commands        = $commandProvider->getCommands();

        foreach ($commands as $command) {
            $this->add($command);
        }

        // $this->add(new Application_Console_Index_RepairCommand());
        // $this->add(new Application_Console_Index_CheckCommand());

        $this->add(new Application_Console_Document_DeleteCommand());
        $this->add(new Application_Console_Document_DuplicatesCommand());
        $this->add(new Application_Console_Document_DiffCommand());

        $this->add(new Application_Console_Debug_DocumentXmlCommand());
        $this->add(new CoverGenerateCommand());
        $this->add(new Application_Console_Console_ConsoleCommand());
        $this->add(new Application_Console_Console_ExecCommand());
        $this->add(new Application_Console_Collection_CopyCommand());
        $this->add(new Application_Console_Collection_MoveCommand());
        $this->add(new Application_Console_Collection_RemoveCommand());

        if (class_exists(TaskManager::class)) {
            /*
                Tasks commands do not work without the TaskManager. If the current PHP version is less than 7.4
                there will be no TaskManager due to lack of crunz support.
            */
            $this->add(new Application_Console_Task_InfoCommand());
            $this->add(new Application_Console_Task_ListCommand());
            $this->add(new Application_Console_Task_RunCommand());
        }

        $this->setDefaultCommand('list');
    }
}
