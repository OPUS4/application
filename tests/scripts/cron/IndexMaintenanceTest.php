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
 * @category    Cronjob
 * @package     Tests
 * @author      Sascha Szott <szott@zib.de>
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

require_once('CronTestCase.php');

class IndexMaintenanceTest extends CronTestCase {
    
    public function testJobSuccess() {
        $this->createJob(Opus_Job_Worker_ConsistencyCheck::LABEL);
        $this->executeScript('cron-check-consistency.php');
        
        $allJobs = Opus_Job::getByLabels(array(Opus_Job_Worker_ConsistencyCheck::LABEL), null, Opus_Job::STATE_UNDEFINED);
        $this->assertTrue(empty($allJobs), 'Expected no more jobs in queue: found ' . count($allJobs) . ' jobs');
        
        $failedJobs = Opus_Job::getByLabels(array(Opus_Job_Worker_ConsistencyCheck::LABEL), null, Opus_Job::STATE_FAILED);
        $this->assertTrue(empty($failedJobs), 'Expected no failed jobs in queue: found ' . count($failedJobs) . ' jobs');
        
        $logPath = parent::$scriptPath . '/../../workspace/log/';
        $this->assertFileExists($logPath . 'opus_consistency-check.log', 'Logfile opus_consistency-check.log does not exist');
        $this->assertFileNotExists($logPath . 'opus_consistency-check.log.lock', 'Lockfile opus_consistency-check.log.lock was not removed');
        
        $contents = file_get_contents($logPath . 'opus_consistency-check.log');
        $this->assertFalse(strpos($contents, 'checking 137 published documents for consistency.') === false, 'Logfile opus_consistency-check.log does not contain expected message');
        $this->assertFalse(strpos($contents, 'No inconsistency was detected.') === false, 'Logfile opus_consistency-check.log does not contain expected message');
        $this->assertFalse(strpos($contents, 'Completed operation after') === false, 'Logfile opus_consistency-check.log does not contain expected message');
    }
    
    public function testJobSuccessWithInconsistency() {
        $indexer = new Opus_SolrSearch_Index_Indexer();
        $indexer->deleteAllDocs();
        $indexer->commit();

        $this->createJob(Opus_Job_Worker_ConsistencyCheck::LABEL);
        $this->executeScript('cron-check-consistency.php');
        
        $allJobs = Opus_Job::getByLabels(array(Opus_Job_Worker_ConsistencyCheck::LABEL), null, Opus_Job::STATE_UNDEFINED);
        $this->assertTrue(empty($allJobs), 'Expected no more jobs in queue: found ' . count($allJobs) . ' jobs');
        
        $failedJobs = Opus_Job::getByLabels(array(Opus_Job_Worker_ConsistencyCheck::LABEL), null, Opus_Job::STATE_FAILED);
        $this->assertTrue(empty($failedJobs), 'Expected no failed jobs in queue: found ' . count($failedJobs) . ' jobs');
        
        $logPath = parent::$scriptPath . '/../../workspace/log/';
        $this->assertFileExists($logPath . 'opus_consistency-check.log', 'Logfile opus_consistency-check.log does not exist');
        $this->assertFileNotExists($logPath . 'opus_consistency-check.log.lock', 'Lockfile opus_consistency-check.log.lock was not removed');
        
        $contents = file_get_contents($logPath . 'opus_consistency-check.log');
        $this->assertFalse(strpos($contents, 'checking 137 published documents for consistency.') === false, 'Logfile opus_consistency-check.log does not contain expected message');
        $this->assertFalse(strpos($contents, 'inconsistency found for document 1: document is in database, but is not in Solr index.') === false, 'Logfile opus_consistency-check.log does not contain expected message');
        $this->assertFalse(strpos($contents, 'inconsistency found for document 200: document is in database, but is not in Solr index.') === false, 'Logfile opus_consistency-check.log does not contain expected message');
        $this->assertFalse(strpos($contents, '137 inconsistencies were detected: 137 of them were resolved.') === false, 'Logfile opus_consistency-check.log does not contain expected message');
        $this->assertFalse(strpos($contents, 'number of updates: 137') === false, 'Logfile opus_consistency-check.log does not contain expected message');
        $this->assertFalse(strpos($contents, 'number of deletions: 0') === false, 'Logfile opus_consistency-check.log does not contain expected message');
        $this->assertFalse(strpos($contents, 'Completed operation after') === false, 'Logfile opus_consistency-check.log does not contain expected message');        
    }
}