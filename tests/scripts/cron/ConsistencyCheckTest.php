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

require_once 'CronTestCase.php';

use Opus\Common\Job;
use Opus\Search\Service;
use Opus\Search\Task\ConsistencyCheck;

class ConsistencyCheckTest extends CronTestCase
{
    /** @var string */
    protected $additionalResources = 'database';

    /**
     * @return int
     */
    private function getPublishedDocumentCount()
    {
        $finder = $this->getDocumentFinder();
        $finder->setServerState('published');
        return $finder->getCount();
    }

    /**
     * TODO fix for Solr update
     */
    public function testJobSuccess()
    {
        $this->createJob(ConsistencyCheck::LABEL);
        $this->executeScript('cron-check-consistency.php');

        $allJobs = Job::getByLabels([ConsistencyCheck::LABEL], null, Job::STATE_UNDEFINED);
        $this->assertTrue(empty($allJobs), 'Expected no more jobs in queue: found ' . count($allJobs) . ' jobs');

        $failedJobs = Job::getByLabels([ConsistencyCheck::LABEL], null, Job::STATE_FAILED);
        $this->assertTrue(empty($failedJobs), 'Expected no failed jobs in queue: found ' . count($failedJobs) . ' jobs');

        $logPath = parent::$scriptPath . '/../../workspace/log/';
        $this->assertFileExists($logPath . 'opus_consistency-check.log', 'Logfile opus_consistency-check.log does not exist');
        $this->assertFileNotExists($logPath . 'opus_consistency-check.log.lock', 'Lockfile opus_consistency-check.log.lock was not removed');

        $publishedDocsCount = $this->getPublishedDocumentCount();

        $contents = file_get_contents($logPath . 'opus_consistency-check.log');
        $this->assertFalse(
            strpos($contents, 'checking ' . $publishedDocsCount
                . ' published documents for consistency.') === false,
            "Logfile opus_consistency-check.log does not contain 'checking ' . $publishedDocsCount
            . '...' [$contents]."
        );
        $this->assertFalse(
            strpos($contents, 'No inconsistency was detected.') === false,
            'Logfile opus_consistency-check.log does not contain "No inconsistency ...". ' . $contents
        );
        $this->assertFalse(
            strpos($contents, 'Completed operation after') === false,
            'Logfile opus_consistency-check.log does not contain "Completed operation after".'
        );

        unlink($logPath . 'opus_consistency-check.log');
    }

    /**
     * TODO fix for Solr Update
     */
    public function testJobSuccessWithInconsistency()
    {
        $service = Service::selectIndexingService(null, 'solr');
        $service->removeAllDocumentsFromIndex();

        $this->createJob(ConsistencyCheck::LABEL);
        $this->executeScript('cron-check-consistency.php');

        $allJobs = Job::getByLabels([ConsistencyCheck::LABEL], null, Job::STATE_UNDEFINED);
        $this->assertTrue(empty($allJobs), 'Expected no more jobs in queue: found ' . count($allJobs) . ' jobs');

        $failedJobs = Job::getByLabels([ConsistencyCheck::LABEL], null, Job::STATE_FAILED);
        $this->assertTrue(empty($failedJobs), 'Expected no failed jobs in queue: found ' . count($failedJobs) . ' jobs');

        $logPath = parent::$scriptPath . '/../../workspace/log/';
        $this->assertFileExists($logPath . 'opus_consistency-check.log', 'Logfile opus_consistency-check.log does not exist');
        $this->assertFileNotExists($logPath . 'opus_consistency-check.log.lock', 'Lockfile opus_consistency-check.log.lock was not removed');

        $publishedDocsCount = $this->getPublishedDocumentCount();

        $contents = file_get_contents($logPath . 'opus_consistency-check.log');
        $this->assertFalse(
            strpos($contents, 'checking ' . $publishedDocsCount . ' published documents for consistency.') === false,
            "Logfile opus_consistency-check.log does not contain 'checking ' . $publishedDocsCount
            . ' ...' [$contents]."
        );
        $this->assertFalse(
            strpos($contents, 'inconsistency found for document 1: document is in database, but is not in Solr index.') === false,
            'Logfile opus_consistency-check.log does not contain "inconsistency found for document 1: ...".'
        );
        $this->assertFalse(
            strpos($contents, 'inconsistency found for document 200: document is in database, but is not in Solr index.') === false,
            'Logfile opus_consistency-check.log does not contain "inconsistency found for document 200: ...".'
        );
        $this->assertFalse(
            strpos($contents, $publishedDocsCount . ' inconsistencies were detected: '
                . $publishedDocsCount . ' of them were resolved.') === false,
            'Logfile opus_consistency-check.log does not contain "' . $publishedDocsCount . ' inconsistencies ...".'
        );
        $this->assertFalse(
            strpos($contents, 'number of updates: ' . $publishedDocsCount) === false,
            'Logfile opus_consistency-check.log does not contain "number of updates: ' . $publishedDocsCount . '".'
        );
        $this->assertFalse(
            strpos($contents, 'number of deletions: 0') === false,
            'Logfile opus_consistency-check.log does not contain "number of deletions: 0".'
        );
        $this->assertFalse(
            strpos($contents, 'Completed operation after') === false,
            'Logfile opus_consistency-check.log does not contain "Completed operation after".'
        );

        unlink($logPath . 'opus_consistency-check.log');
    }
}
