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
 * @category    Application
 * @package     Tests
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class Admin_Model_IndexMaintenanceTest extends ControllerTestCase
{

    protected $configModifiable = true;

    protected $additionalResources = 'database';

    public function tearDown()
    {
        if (! is_null($this->config)) {
            Zend_Registry::set('Zend_Config', $this->config);
        }

        // Cleanup of Log File
        $config = Zend_Registry::get('Zend_Config');
        $filename = $config->workspacePath . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'opus_consistency-check.log';
        if (file_exists($filename)) {
            unlink($filename);
        }

        // Cleanup of Lock File
        if (file_exists($filename . '.lock')) {
            unlink($filename . '.lock');
        }

        // Cleanup of Jobs Table
        $jobs = Opus_Job::getByLabels([Opus\Search\Task\ConsistencyCheck::LABEL]);
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
        Zend_Registry::get('Zend_Config')->merge(new Zend_Config([
            'runjobs' => ['asynchronous' => self::CONFIG_VALUE_TRUE]
        ]));
    }

    private function enableAsyncIndexmaintenanceMode()
    {
        Zend_Registry::get('Zend_Config')->merge(new Zend_Config([
            'runjobs' => ['indexmaintenance' => ['asynchronous' => self::CONFIG_VALUE_TRUE]]
        ]));
    }

    public function testAllowConsistencyCheck()
    {
        $model = new Admin_Model_IndexMaintenance();
        $this->assertTrue($model->allowConsistencyCheck());
    }

    /*
     * TODO will be implemented in later version OPUSVIER-2956
     */
    public function testNotAllowIndexOptimization()
    {
        $model = new Admin_Model_IndexMaintenance();
        $this->assertFalse($model->allowIndexOptimization());
    }

    /*
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

        $this->assertEquals(1, Opus_Job::getCountForLabel(Opus\Search\Task\ConsistencyCheck::LABEL));
    }

    public function testNotAllowConsistencyCheckAlt()
    {
        $this->enableAsyncIndexmaintenanceMode();

        $model = new Admin_Model_IndexMaintenance();
        $model->createJob();
        $this->assertFalse($model->allowConsistencyCheck());

        $this->assertEquals(1, Opus_Job::getCountForLabel(Opus\Search\Task\ConsistencyCheck::LABEL));
    }

    public function testProcessingStateInvalidContext()
    {
        $model = new Admin_Model_IndexMaintenance();
        $state = $model->getProcessingState();
        $this->assertNull($state);
    }

    private function runJobImmediately()
    {
        $this->assertEquals(1, Opus_Job::getCountForLabel(Opus\Search\Task\ConsistencyCheck::LABEL));

        $jobrunner = new Opus_Job_Runner;
        $jobrunner->setLogger(Zend_Registry::get('Zend_Log'));
        $worker = new Opus\Search\Task\ConsistencyCheck();
        $jobrunner->registerWorker($worker);
        $jobrunner->run();

        $this->assertEquals(0, Opus_Job::getCountForLabel(Opus\Search\Task\ConsistencyCheck::LABEL));
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

        $finder = new Opus_DocumentFinder();
        $finder->setServerState('published');
        $numOfPublishedDocs = $finder->count();

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

    private function touchLogfile($lock = false)
    {
        $config = Zend_Registry::get('Zend_Config');
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
        $id1 = $model->createJob();
        $this->assertFalse(is_bool($id1));
        $this->assertTrue($id1 >= 0, "Job seems to be not unique (id is $id1)");

        $id2 = $model->createJob();
        $this->assertTrue(is_bool($id2));
        $this->assertTrue($id2);
    }

    public function testSubmitJobAndExecuteSynchronosly()
    {
        $model = new Admin_Model_IndexMaintenance();
        $id = $model->createJob();
        $this->assertFalse($id);
    }
}
