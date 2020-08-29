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
 * @author      Edouard Simon (edouard.simon@zib.de)
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */
require_once('CronTestCase.php');

/**
 *
 */
class SolrUpdateTest extends CronTestCase
{

    protected $additionalResources = 'database';

    private $document;

    public function setUp()
    {
        parent::setUp();
        $this->document = $this->createTestDocument();
        $this->document->store();
        Opus_Job::deleteAll();
    }

    public function testSolrUpdateIndex()
    {
        $this->createJob(Opus\Search\Task\IndexOpusDocument::LABEL, [
            'documentId' => $this->document->getId(),
            'task' => 'index']);
        $this->executeScript('cron-solr-update.php');
        $allJobs = Opus_Job::getByLabels([Opus\Search\Task\IndexOpusDocument::LABEL], null, Opus_Job::STATE_UNDEFINED);
        $this->assertTrue(empty($allJobs), 'Expected no more jobs in queue');
        $failedJobs = Opus_Job::getByLabels([Opus\Search\Task\IndexOpusDocument::LABEL], null, Opus_Job::STATE_FAILED);
        $this->assertTrue(empty($failedJobs), 'Expected no failed jobs in queue');
    }

    public function testSolrRemoveIndex()
    {
        $this->createJob(Opus\Search\Task\IndexOpusDocument::LABEL, [
            'documentId' => $this->document->getId(),
            'task' => 'remove']);
        $this->executeScript('cron-solr-update.php');
        $allJobs = Opus_Job::getByLabels([Opus\Search\Task\IndexOpusDocument::LABEL], null, Opus_Job::STATE_UNDEFINED);
        $this->assertTrue(empty($allJobs), 'Expected no more jobs in queue');
        $failedJobs = Opus_Job::getByLabels([Opus\Search\Task\IndexOpusDocument::LABEL], null, Opus_Job::STATE_FAILED);
        $this->assertTrue(empty($failedJobs), 'Expected no failed jobs in queue');
    }

    public function testJobFailsIfInvalidTask()
    {
        $this->createJob(Opus\Search\Task\IndexOpusDocument::LABEL, [
            'documentId' => $this->document->getId(),
            'task' => 'do-the-unexpected']);
        $this->executeScript('cron-solr-update.php');
        $allJobs = Opus_Job::getByLabels([Opus\Search\Task\IndexOpusDocument::LABEL], null, Opus_Job::STATE_FAILED);
        $this->assertEquals(1, count($allJobs), 'Expected one failed job in queue (found ' . count($allJobs) . ')');
    }
}
