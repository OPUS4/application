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

class Application_Task_TaskConfigReaderTest extends ControllerTestCase
{
    public function testGetTaskConfigurations()
    {
        $taskConfigReader = new Application_Task_TaskConfigReader();
        $taskConfigurations = $taskConfigReader->getTaskConfigurations();
        $this->assertNotEmpty($taskConfigurations);
        $this->assertEquals(2, sizeof($taskConfigurations));

        $taskConfig = new Application_Task_TaskConfig();
        $taskConfig->setEnabled(true);
        $taskConfig->setName('testTask1');
        $taskConfig->setSchedule('*/1 * * * *');
        $taskConfig->setClass("Application_Job_CleanTemporariesJob");
        $taskConfig->setPreventOverlapping(true);

        $taskConfig->setOptions(
            [
                'optionName1' => 'option1Value',
                'optionName2' => 'option2Value'
            ]
        );

        $this->assertEquals($taskConfig, $taskConfigurations['testTask1']);

        $taskConfig = new Application_Task_TaskConfig();
        $taskConfig->setEnabled(false);
        $taskConfig->setName('testTask2');
        $taskConfig->setSchedule('*/2 * * * *');
        $taskConfig->setClass("Application_Job_SendNotificationJob");
        $taskConfig->setPreventOverlapping(false);
        $taskConfig->setOptions([]);

        $this->assertEquals($taskConfig, $taskConfigurations['testTask2']);
    }

    public function testGetActiveTaskConfigurations()
    {
        $taskConfigReader = new Application_Task_TaskConfigReader();
        $taskConfigurations = $taskConfigReader->getActiveTaskConfigurations();
        $this->assertNotEmpty($taskConfigurations);
        $this->assertEquals(1, sizeof($taskConfigurations));
        $this->assertTrue(array_pop($taskConfigurations)->isEnabled());
    }

    public function testGetTaskConfig()
    {
        $taskConfig = new Application_Task_TaskConfig();
        $taskConfig->setEnabled(false);
        $taskConfig->setName('testTask2');
        $taskConfig->setSchedule('*/2 * * * *');
        $taskConfig->setClass("Application_Job_SendNotificationJob");
        $taskConfig->setPreventOverlapping(false);
        $taskConfig->setOptions([]);

        $taskConfigReader = new Application_Task_TaskConfigReader();
        $taskConfiguration = $taskConfigReader->getTaskConfig('unknownTask');
        $this->assertFalse($taskConfiguration);

        $taskConfiguration = $taskConfigReader->getTaskConfig('testTask2');
        $this->assertEquals('Application_Job_SendNotificationJob', $taskConfiguration->getClass());
    }

    public function testNotExistingTaskClass()
    {
        $taskConfigReader = new Application_Task_TaskConfigReader();
        $this->assertFalse($taskConfigReader->isValidTaskClass('UnknownClass'));
    }

    public function testTaskClassWithNotImplementingTaskInterface()
    {
        $taskConfigReader = new Application_Task_TaskConfigReader();
        $this->assertFalse($taskConfigReader->isValidTaskClass('Application_Configuration_MaxUploadSize'));
    }
}
