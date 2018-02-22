<?php
/*
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
 * @category    Tests
 * @package     Admin
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

/**
 * Class Admin_IndexmaintenanceControllerTest
 *
 * @covers Admin_IndexmaintenanceController
 */
class Admin_IndexmaintenanceControllerTest extends ControllerTestCase {
    
    private $config;
    
    public function setUp() {
        parent::setUp();
        $this->config = Zend_Registry::get('Zend_Config');
    }
    
    protected function tearDown() {
        Zend_Registry::set('Zend_Config', $this->config);
        
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
        $jobs = Opus_Job::getByLabels(array(Opus_Job_Worker_ConsistencyCheck::LABEL));
        foreach ($jobs as $job) {
            try {
                $job->delete();
            }   
            catch (Exception $e) {
                // ignore
            }            
        }                
        
        parent::tearDown();
    }

    public function testIndexActionWithDisabledFeature1() {
        $this->dispatch('/admin/indexmaintenance/index');
        $this->checkIfUnavailable();
    }
    
    public function testIndexActionWithDisabledFeature2() {
        $this->disableAsyncIndexmaintenanceMode();
        $this->enableAsyncMode();
        $this->dispatch('/admin/indexmaintenance/index');
        $this->checkIfUnavailable();
    }
    
    public function testIndexActionWithDisabledFeature3() {
        $this->disableAsyncIndexmaintenanceMode();
        $this->dispatch('/admin/indexmaintenance/index');
        $this->checkIfUnavailable();
    }    

    public function testIndexActionWithDisabledFeature4() {
        $this->disableAsyncMode();
        $this->dispatch('/admin/indexmaintenance/index');
        $this->checkIfUnavailable();
    }    
    
    public function testIndexActionWithDisabledFeature5() {
        $this->disableAsyncMode();
        $this->disableAsyncIndexmaintenanceMode();
        $this->dispatch('/admin/indexmaintenance/index');
        $this->checkIfUnavailable();
    }        
    
    public function testIndexActionWithEnabledFeature1() {
        $this->enableAsyncMode();
        $this->dispatch('/admin/indexmaintenance/index');        
        $this->checkIfAvailable();                
    }
    
    public function testIndexActionWithEnabledFeature2() {
        $this->enableAsyncIndexmaintenanceMode();
        $this->dispatch('/admin/indexmaintenance/index');        
        $this->checkIfAvailable();                
    }
    
    public function testIndexActionWithEnabledFeature3() {
        $this->enableAsyncIndexmaintenanceMode();
        $this->disableAsyncMode();
        $this->dispatch('/admin/indexmaintenance/index');        
        $this->checkIfAvailable();                
    }    
    
    public function testIndexActionWithEnabledFeature4() {
        $this->enableAsyncMode();
        $this->enableAsyncIndexmaintenanceMode();
        $this->dispatch('/admin/indexmaintenance/index');        
        $this->checkIfAvailable();                
    }
    
    private function checkIfAvailable() {
        $this->assertResponseCode(200);
        
        $baseUrl = $this->getRequest()->getBaseUrl();
        $body = $this->getResponse()->getBody();        
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkconsistency\"", $body);
        // TODO $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkfulltexts\"", $body);
        // TODO $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/optimizeindex\"", $body);        
    }
    
    private function checkIfUnavailable() {
        $this->assertResponseCode(200);
        
        $baseUrl = $this->getRequest()->getBaseUrl();
        $body = $this->getResponse()->getBody();
        $this->assertNotContains("action=\"$baseUrl/admin/indexmaintenance/checkconsistency\"", $body);
        // TODO $this->assertNotContains("action=\"$baseUrl/admin/indexmaintenance/checkfulltexts\"", $body);
        // TODO $this->assertNotContains("action=\"$baseUrl/admin/indexmaintenance/optimizeindex\"", $body);        
    }
        
    private function enableAsyncMode() {
        $this->setAsyncMode(1);
    }

    private function disableAsyncMode() {
        $this->setAsyncMode(0);
    }    
    
    private function setAsyncMode($value) {
        $config = Zend_Registry::get('Zend_Config');        
        if (isset($config->runjobs->asynchronous)) {
            $config->runjobs->asynchronous = $value;
        }
        else {
            $config = new Zend_Config(array('runjobs' => array('asynchronous' =>  $value)), true);
            $config->merge(Zend_Registry::get('Zend_Config'));
        }
        Zend_Registry::set('Zend_Config', $config);        
    }
    
    private function enableAsyncIndexmaintenanceMode() {
        $this->setAsyncIndexmaintenanceMode(1);
    }
    
    private function disableAsyncIndexmaintenanceMode() {
        $this->setAsyncIndexmaintenanceMode(0);
    }
    
    private function setAsyncIndexmaintenanceMode($value) {
        $config = Zend_Registry::get('Zend_Config');      
        if (isset($config->runjobs->indexmaintenance->asynchronous)) {
            $config->runjobs->indexmaintenance->asynchronous = $value;
        }
        else {
            $config = new Zend_Config(array('runjobs' => array('indexmaintenance' => array('asynchronous' => $value))), true);
            $config->merge(Zend_Registry::get('Zend_Config'));
        }
        Zend_Registry::set('Zend_Config', $config);        
    }
    
    public function testCheckconsistencyActionWithDisabledFeature() {
        $this->dispatch('/admin/indexmaintenance/checkconsistency');
        $this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');
    }
    
    public function testOptimizeindexActionWithDisabledFeature() {
        $this->dispatch('/admin/indexmaintenance/optimizeindex');
        $this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');        
    }
    
    public function testCheckfulltextsActionWithDisabledFeature() {
        $this->dispatch('/admin/indexmaintenance/checkfulltexts');
        $this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');        
    }

    public function testCheckconsistencyActionWithGet() {
        $this->enableAsyncIndexmaintenanceMode();
        $this->dispatch('/admin/indexmaintenance/checkconsistency');
        $this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');
    }
    
    public function testOptimizeindexActionWithGet() {
        $this->enableAsyncIndexmaintenanceMode();
        $this->dispatch('/admin/indexmaintenance/optimizeindex');
        $this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');        
    }
    
    public function testCheckfulltextsActionWithGet() {
        $this->enableAsyncIndexmaintenanceMode();
        $this->dispatch('/admin/indexmaintenance/checkfulltexts');
        $this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');        
    }
    
    public function testCheckconsistencyActionWithEnabledFeature() {
        $this->enableAsyncIndexmaintenanceMode();
        
        $numOfJobs = Opus_Job::getCountForLabel(Opus_Job_Worker_ConsistencyCheck::LABEL);
        $this->assertEquals(0, $numOfJobs);
        
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/admin/indexmaintenance/checkconsistency');
        
        $this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');
        
        $this->assertEquals(1, Opus_Job::getCountForLabel(Opus_Job_Worker_ConsistencyCheck::LABEL));
        
        $jobs = Opus_Job::getByLabels(array(Opus_Job_Worker_ConsistencyCheck::LABEL));
        $jobs[0]->delete();
        
        $this->assertEquals(0, Opus_Job::getCountForLabel(Opus_Job_Worker_ConsistencyCheck::LABEL));
    }
    
    public function testCheckconsistencyActionResult() {
        $this->enableAsyncIndexmaintenanceMode();
        
        $this->assertEquals(0, Opus_Job::getCountForLabel(Opus_Job_Worker_ConsistencyCheck::LABEL), 'missing cleanup of jobs table');
        
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/admin/indexmaintenance/checkconsistency');
        
        $this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');
                              
        $this->assertEquals(1, Opus_Job::getCountForLabel(Opus_Job_Worker_ConsistencyCheck::LABEL), 'consistency check job was not stored in database');
        
        /*
         * check if job was scheduled for execution
         */        
        $this->resetResponse();
        $this->resetRequest();
        $this->dispatch('/admin/indexmaintenance/index');
                
        $this->assertResponseCode(200, 'foo');
        
        $baseUrl = $this->getRequest()->getBaseUrl();
        $body = $this->getResponse()->getBody();        
        $this->assertContains('div class="opprogress"', $body);
        $this->assertNotContains("action=\"$baseUrl/admin/indexmaintenance/checkconsistency\"", $body);
        // TODO $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkfulltexts\"", $body);
        // TODO $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/optimizeindex\"", $body);
        
        /*
         * run job immediately and check for result
         */
        $jobrunner = new Opus_Job_Runner();
        $jobrunner->setLogger(Zend_Registry::get('Zend_Log'));
        $worker = new Opus_Job_Worker_ConsistencyCheck();       
        $jobrunner->registerWorker($worker);
        $jobrunner->run();        
                
        $jobs = Opus_Job::getByLabels(array(Opus_Job_Worker_ConsistencyCheck::LABEL));        
        if (count($jobs) > 0) {
            $job = $jobs[0];            
            $message = 'at least one unexpected job found (Label: \'%s\', State: \'%s\', Data: \'%s\', Errors: \'%s\, SHA1 Hash: \'%s\')';
            $label = $job->getLabel();
            $state = $job->getState();
            $data = $job->getData();
            $errors = $job->getErrors();
            $hash = $job->getSha1Id();
            $this->fail(sprintf($message, $label, $state, $data, $errors, $hash));
        }        
        
        $this->assertEquals(0, Opus_Job::getCountForLabel(Opus_Job_Worker_ConsistencyCheck::LABEL), 'consistency check job was not removed from database after execution');

        $this->resetResponse();
        $this->resetRequest();
        $this->dispatch('/admin/indexmaintenance/index');
                       
        $this->assertResponseCode(200, 'bar');
                        
        $baseUrl = $this->getRequest()->getBaseUrl();
        $body = $this->getResponse()->getBody();        
        $this->assertNotContains('div class="opprogress"', $body);
        $this->assertContains('pre class="opoutput"', $body);
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkconsistency\"", $body);
        // TODO $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkfulltexts\"", $body);
        // TODO $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/optimizeindex\"", $body);        
    }

    /**
     * TODO currently not implemented OPUSVIER-2956
     */    
    public function testOptimizeindexActionWithEnabledFeature() {
        $this->enableAsyncIndexmaintenanceMode();
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/admin/indexmaintenance/optimizeindex');
               
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');        
    }
    
    /**
     * TODO currently not implemented OPUSVIER-2955
     */
    public function testCheckfulltextsActionWithEnabledFeature() {        
        $this->enableAsyncIndexmaintenanceMode();
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/admin/indexmaintenance/checkfulltexts');
                
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');        
    }
}
