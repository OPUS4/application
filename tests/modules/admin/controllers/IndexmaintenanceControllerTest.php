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
 * @category    Application
 * @package     Tests
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_IndexmaintenanceControllerTest extends ControllerTestCase {
    
    private $config = null;
    
    protected function tearDown() {
        if (!is_null($this->config)) {
            Zend_Registry::set('Zend_Config', $this->config);
        }
        parent::tearDown();
    }

    public function testIndexActionWithDisabledFeature() {
        $this->dispatch('/admin/indexmaintenance/index');
        
        $this->assertResponseCode(200);
        
        $baseUrl = $this->getRequest()->getBaseUrl();
        $body = $this->getResponse()->getBody();
        $this->assertNotContains("action=\"$baseUrl/admin/indexmaintenance/checkconsistency\"", $body);
        $this->assertNotContains("action=\"$baseUrl/admin/indexmaintenance/checkfulltexts\"", $body);
        $this->assertNotContains("action=\"$baseUrl/admin/indexmaintenance/optimizeindex\"", $body);
    }
    
    public function testIndexActionWithEnabledFeature() {
        $this->enableAsyncMode();
        $this->dispatch('/admin/indexmaintenance/index');
                
        $this->assertResponseCode(200);
        
        $baseUrl = $this->getRequest()->getBaseUrl();
        $body = $this->getResponse()->getBody();        
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkconsistency\"", $body);
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkfulltexts\"", $body);
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/optimizeindex\"", $body);
    }
    
    public function testIndexActionWithEnabledFeatureAlt() {
        $this->enableAsyncIndexmaintenanceMode();
        $this->dispatch('/admin/indexmaintenance/index');
                
        $this->assertResponseCode(200);
        
        $baseUrl = $this->getRequest()->getBaseUrl();
        $body = $this->getResponse()->getBody();        
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkconsistency\"", $body);
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkfulltexts\"", $body);
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/optimizeindex\"", $body);
    }    
    
    private function enableAsyncMode() {
        $this->config = Zend_Registry::get('Zend_Config');

        $config = Zend_Registry::get('Zend_Config');        
        if (isset($config->runjobs->asynchronous)) {
            $config->runjobs->asynchronous = 1;
        }
        else {
            $config = new Zend_Config(array('runjobs' => array('asynchronous' =>  1)), true);
            $config->merge(Zend_Registry::get('Zend_Config'));
        }
        Zend_Registry::set('Zend_Config', $config);       
    }
    
    private function enableAsyncIndexmaintenanceMode() {
        $this->config = Zend_Registry::get('Zend_Config');

        $config = Zend_Registry::get('Zend_Config');        
        if (isset($config->runjobs->asynchronous->indexmaintenance)) {
            $config->runjobs->asynchronous->indexmaintenance = 1;
        }
        else {
            $config = new Zend_Config(array('runjobs' => array('asynchronous' =>  array('indexmaintenance' => 1))), true);
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
        
        $numOfJobs = Opus_Job::getCountForLabel(Opus_Job_Worker_ConsistencyCheck::LABEL);
        $this->assertEquals(0, $numOfJobs);
        
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/admin/indexmaintenance/checkconsistency');
        
        $this->assertResponseCode(302);
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');
        
        $this->assertEquals(1, Opus_Job::getCountForLabel(Opus_Job_Worker_ConsistencyCheck::LABEL));
        
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
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkfulltexts\"", $body);
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/optimizeindex\"", $body);
        
        /*
         * run job immediately and check for result
         */
        $jobrunner = new Opus_Job_Runner;
        $jobrunner->setLogger(Zend_Registry::get('Zend_Log'));
        $worker = new Opus_Job_Worker_ConsistencyCheck();       
        $jobrunner->registerWorker($worker);
        $jobrunner->run();        
        
        $this->assertEquals(0, Opus_Job::getCountForLabel(Opus_Job_Worker_ConsistencyCheck::LABEL));

        $this->resetResponse();
        $this->resetRequest();
        $this->dispatch('/admin/indexmaintenance/index');
                       
        $this->assertResponseCode(200, 'bar');
                        
        $baseUrl = $this->getRequest()->getBaseUrl();
        $body = $this->getResponse()->getBody();        
        $this->assertNotContains('div class="opprogress"', $body);
        $this->assertContains('pre class="opoutput"', $body);
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkconsistency\"", $body);
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/checkfulltexts\"", $body);
        $this->assertContains("action=\"$baseUrl/admin/indexmaintenance/optimizeindex\"", $body);        
    }
    
    public function testOptimizeindexActionWithEnabledFeature() {
        $this->enableAsyncIndexmaintenanceMode();
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/admin/indexmaintenance/optimizeindex');
        
        // TODO currently not implemented
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');        
    }
    
    public function testCheckfulltextsActionWithEnabledFeature() {        
        $this->enableAsyncIndexmaintenanceMode();
        $this->getRequest()->setMethod('POST');
        $this->dispatch('/admin/indexmaintenance/checkfulltexts');
        
        // TODO currently not implemented
        $this->assertResponseLocationHeader($this->getResponse(), '/admin/indexmaintenance');        
    }
}
