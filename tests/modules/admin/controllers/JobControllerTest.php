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
 * @category    Tests
 * @package     Admin
 * @author      Edouard Simon <edouard.simon@zib.de>
 * @copyright   Copyright (c) 2008-2018, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 *
 * @covers Admin_JobController
 */
class Admin_JobControllerTest extends ControllerTestCase {

    private $__configBackup;
    private $jobIds = array();

    public function setUp() {
        parent::setUp();
        $config = Zend_Registry::get('Zend_Config');
        $this->__configBackup = $config;
        $config->merge(new Zend_Config(array('runjobs' => array('asynchronous' => true))));

        $this->assertEquals(0, Opus_Job::getCount(Opus_Job::STATE_FAILED), 'test data changed.');

        for ($i = 0; $i < 10; $i++) {
            $job = new Opus_Job();
            $job->setLabel('testjob' . ($i < 5 ? 1 : 2));
            $job->setData(array(
                'documentId' => $i,
                'task' => 'get-me-a-coffee'));
            $job->setState(Opus_Job::STATE_FAILED);
            $this->jobIds[] = $job->store();
        }
    }

    protected function tearDown() {

        $testJobs = Opus_Job::getAll($this->jobIds);
        foreach ($testJobs as $job) {
            $job->delete();
        }

        Zend_Registry::set('Zend_Config', $this->__configBackup);
        parent::tearDown();
    }
    
    public function testIndexDisplayFailedWorkerJobs() {

        $this->dispatch('/admin/job');
        $this->assertResponseCode(200);
        $this->assertQueryContentContains('table.worker-jobs td', 'testjob1');
        $this->assertQueryContentContains('table.worker-jobs td', 'testjob2');
    }

    public function testMonitorFailedWorkerJobs() {

        $this->dispatch('/admin/job/worker-monitor');
        $this->assertResponseCode(200);
        $this->assertEquals('1', $this->_response->getBody(), 'Expected value 1');
    }
    
    public function testJobDetailsAction() {
        $failedJobsUrl = '/admin/job/detail/label/testjob1/state/'.Opus_Job::STATE_FAILED;
        $this->dispatch($failedJobsUrl);
        $this->assertResponseCode(200);
        $this->assertQueryContentContains('table.worker-jobs td div', 'task: get-me-a-coffee');
        
    }

}

