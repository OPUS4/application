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
 * @copyright   Copyright (c) 2008, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Common\Config;
use Opus\Common\Job;
use Opus\Common\Log;
use Opus\Job\Runner;
use Opus\Search\Task\ConsistencyCheck;

class Admin_Model_IndexMaintenanceTest extends ControllerTestCase
{
    /** @var bool */
    protected $configModifiable = true;

    /** @var string */
    protected $additionalResources = 'database';

    public function tearDown(): void
    {
        if ($this->config !== null) {
            Config::set($this->config); // TODO why is this here?
        }

        // Cleanup of Log File
        $config   = $this->getConfig();
        $filename = $config->workspacePath . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'opus_consistency-check.log';
        if (file_exists($filename)) {
            unlink($filename);
        }

        // Cleanup of Lock File
        if (file_exists($filename . '.lock')) {
            unlink($filename . '.lock');
        }

        // Cleanup of Jobs Table
        $jobs = Job::getByLabels([ConsistencyCheck::LABEL]);
        foreach ($jobs as $job) {
            try {
                $job->delete();
            } catch (Exception $e) {
                // ignore
            }
        }

        parent::tearDown();
    }

    public function testConstructorWithFeatureDisabled()
    {
        $model = new Admin_Model_IndexMaintenance();
        $this->assertTrue($model->getFeatureDisabled());
    }

    public function testConstructorWithFeatureEnabled()
    {
        $this->enableAsyncMode();
        $model = new Admin_Model_IndexMaintenance();
        $this->assertFalse($model->getFeatureDisabled());
    }

    public function testConstructorWithFeatureEnabledAlt()
    {
        $this->enableAsyncIndexmaintenanceMode();
        $model = new Admin_Model_IndexMaintenance();
        $this->assertFalse($model->getFeatureDisabled());
    }

    public function testConstructorWithFeatureEnabledBoth()
    {
        $this->enableAsyncIndexmaintenanceMode();
        $this->enableAsyncMode();
        $model = new Admin_Model_IndexMaintenance();
        $this->assertFalse($model->getFeatureDisabled());
    }

    private function enableAsyncMode()
    {
        $this->adjustConfiguration([
            'runjobs' => ['asynchronous' => self::CONFIG_VALUE_TRUE],
        ]);
    }

    private function enableAsyncIndexmaintenanceMode()
    {
        $this->adjustConfiguration([
            'runjobs' => ['indexmaintenance' => ['asynchronous' => self::CONFIG_VALUE_TRUE]],
        ]);
    }

    public function testAllowConsistencyCheck()
    {
        $model = new Admin_Model_IndexMaintenance();
        $this->assertTrue($model->allowConsistencyCheck());
    }

    /**
     * TODO will be implemented in later version OPUSVIER-2956
     */
    public function testNotAllowIndexOptimization()
    {
        $model = new Admin_Model_IndexMaintenance();
        $this->assertFalse($model->allowIndexOptimization());
    }

    /**
     * TODO will be implemented in later version OPUSVIER-2955
     */
    public function testNotAllowFulltextExtractionCheck()
    {
        $model = new Admin_Model_IndexMaintenance();
        $this->assertFalse($model->allowFulltextExtractionCheck());
    }

    public function testNotAllowConsistencyCheck()
    {
        $this->enableAsyncMode();

        $model = new Admin_Model_IndexMaintenance();
        $model->createJob();
        $this->assertFalse($model->allowConsistencyCheck());

        $this->assertEquals(1, Job::getCountForLabel(ConsistencyCheck::LABEL));
    }

    public function testNotAllowConsistencyCheckAlt()
    {
        $this->enableAsyncIndexmaintenanceMode();

        $model = new Admin_Model_IndexMaintenance();
        $model->createJob();
        $this->assertFalse($model->allowConsistencyCheck());

        $this->assertEquals(1, Job::getCountForLabel(ConsistencyCheck::LABEL));
    }

    public function testProcessingStateInvalidContext()
    {
        $model = new Admin_Model_IndexMaintenance();
        $state = $model->getProcessingState();
        $this->assertNull($state);
    }

    private function runJobImmediately()
    {
        $this->assertEquals(1, Job::getCountForLabel(ConsistencyCheck::LABEL));

        $jobrunner = new Runner();
        $jobrunner->setLogger(Log::get());
        $worker = new ConsistencyCheck();
        $jobrunner->registerWorker($worker);
        $jobrunner->run();

        $this->assertEquals(0, Job::getCountForLabel(ConsistencyCheck::LABEL));
    }

    public function testProcessingStateInitial()
    {
        $this->enableAsyncMode();

        $model = new Admin_Model_IndexMaintenance();
        $state = $model->getProcessingState();
        $this->assertEquals('initial', $state);

        $model->createJob();
        $state = $model->getProcessingState();
        $this->assertEquals('initial', $state);

        $this->runJobImmediately();

        $state = $model->getProcessingState();
        $this->assertEquals('completed', $state);
    }

    public function testProcessingState()
    {
        $this->enableAsyncMode();

        $this->touchLogfile();

        $model = new Admin_Model_IndexMaintenance();
        $state = $model->getProcessingState();
        $this->assertEquals('completed', $state);

        $model->createJob();
        $state = $model->getProcessingState();
        $this->assertEquals('scheduled', $state);

        $this->runJobImmediately();

        $state = $model->getProcessingState();
        $this->assertEquals('completed', $state);
    }

    public function testProcessingStateInProgress()
    {
        $this->enableAsyncMode();

        $this->touchLogfile(true);

        $model = new Admin_Model_IndexMaintenance();
        $state = $model->getProcessingState();
        $this->assertEquals('inprogress', $state);
    }

    public function testReadLogfileWithEmptyFile()
    {
        $this->enableAsyncMode();

        $this->touchLogfile();

        $model = new Admin_Model_IndexMaintenance();
        $this->assertNull($model->readLogFile());
    }

    public function testReadLogfileWithNonEmptyFile()
    {
        $this->enableAsyncMode();

        $finder = $this->getDocumentFinder();
        $finder->setServerState('published');
        $numOfPublishedDocs = $finder->getCount();

        $model = new Admin_Model_IndexMaintenance();
        $model->createJob();

        $this->runJobImmediately();

        $logdata = $model->readLogFile();

        $this->assertNotNull($logdata);
        $this->assertNotNull($logdata->getContent());
        $this->assertNotNull($logdata->getModifiedDate());

        $this->assertContains("checking $numOfPublishedDocs published documents for consistency.", $logdata->getContent(), "content of logfile:\n" . $logdata->getContent());
        $this->assertContains('No inconsistency was detected.', $logdata->getContent());
        $this->assertContains('Completed operation after ', $logdata->getContent());
    }

    /**
     * @param bool $lock
     */
    private function touchLogfile($lock = false)
    {
        $config = $this->getConfig();
        if ($lock) {
            $filename = $config->workspacePath . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'opus_consistency-check.log.lock';
        } else {
            $filename = $config->workspacePath . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'opus_consistency-check.log';
        }
        if (! file_exists($filename)) {
            touch($filename);
        }
    }

    public function testSubmitJobTwice()
    {
        $this->enableAsyncMode();

        $model = new Admin_Model_IndexMaintenance();
        $id1   = $model->createJob();
        $this->assertFalse(is_bool($id1));
        $this->assertTrue($id1 >= 0, "Job seems to be not unique (id is $id1)");

        $id2 = $model->createJob();
        $this->assertTrue(is_bool($id2));
        $this->assertTrue($id2);
    }

    public function testSubmitJobAndExecuteSynchronosly()
    {
        $model = new Admin_Model_IndexMaintenance();
        $id    = $model->createJob();
        $this->assertFalse($id);
    }
}
