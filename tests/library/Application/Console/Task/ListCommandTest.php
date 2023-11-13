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

use Opus\Job\TaskManager;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class Application_Console_Task_ListCommandTest extends ControllerTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (! class_exists(TaskManager::class)) {
            $this->markTestSkipped('TaskManager class not present');
        }

        $this->adjustConfiguration(
            [
                'cron' => [
                    'configFile' => APPLICATION_PATH . '/tests/resources/task/commandtest-tasks.ini',
                ],
            ]
        );
    }

    public function testListTaskOutput()
    {
        $app = new Application();

        $command = new Application_Console_Task_ListCommand();
        $command->setApplication($app);

        $tester = new CommandTester($command);

        $tester->execute([
            '--no-interaction' => true,
        ], [
            'interactive' => false,
        ]);

        // Normalized strings for expected and displayed output because we are not interested in spaces and line breaks.
        $expected  = '2 tasks are configured: Name Active Schedule testTask1 yes */1 * * * * testTask2 no */2 * * * *';
        $displayed = trim(
            preg_replace(
                '/\s+/',
                ' ',
                str_replace(PHP_EOL, ' ', $tester->getDisplay())
            )
        );

        $this->assertEquals($expected, $displayed);
    }
}
