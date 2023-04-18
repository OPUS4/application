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
use Opus\Search\Task\ConsistencyCheck;

class Admin_Model_IndexMaintenance
{
    /** @var Zend_Config */
    private $config;

    /** @var Log */
    private $logger;

    /** @var string */
    private $consistencyCheckLogfilePath;

    /** @var bool */
    private $featureDisabled = true;

    /**
     * @param null|Zend_Log $logger
     * @throws Zend_Exception
     */
    public function __construct($logger = null)
    {
        $this->config = Config::get();
        $this->logger = $logger ?? Log::get();
        $this->setFeatureDisabled();

        if ($this->featureDisabled) {
            return; // abort initialization
        }

        if (! isset($this->config->workspacePath) || trim($this->config->workspacePath) === '') {
            $this->logger->err('configuration key \'workspacePath\' is not set correctly');
        } else {
            $this->consistencyCheckLogfilePath = $this->config->workspacePath . DIRECTORY_SEPARATOR . 'log'
                . DIRECTORY_SEPARATOR . 'opus_consistency-check.log';
        }
    }

    /**
     * Disables index maintenance feature depending on configuration.
     *
     * runjobs.indexmaintenance.asynchronous
     * runjobs.asynchronous
     *
     * One of the two options needs to be enabled for the "feature" to be enabled.
     *
     * TODO a nightmare of inversions - Why not setFeatureEnabled
     * TODO and what is the "feature"?
     */
    private function setFeatureDisabled()
    {
        $jobsAsyncEnabled = isset($this->config->runjobs->asynchronous)
            && filter_var($this->config->runjobs->asynchronous, FILTER_VALIDATE_BOOLEAN);

        $indexMaintenanceConfigured   = isset($this->config->runjobs->indexmaintenance->asynchronous);
        $indexMaintenanceAsyncEnabled = $indexMaintenanceConfigured
            && filter_var($this->config->runjobs->indexmaintenance->asynchronous, FILTER_VALIDATE_BOOLEAN);

        $this->featureDisabled = ! ($indexMaintenanceAsyncEnabled ||
            ($jobsAsyncEnabled && ! $indexMaintenanceConfigured));
    }

    /**
     * @return bool
     */
    public function getFeatureDisabled()
    {
        return $this->featureDisabled;
    }

    /**
     * @return bool
     */
    public function createJob()
    {
        $job = Job::new();
        $job->setLabel(ConsistencyCheck::LABEL);

        if (! $this->featureDisabled) {
            // Queue job (execute asynchronously)
            // skip creating job if equal job already exists
            if (true === $job->isUniqueInQueue()) {
                $job->store();
                return $job->getId();
            }
            return true;
        }

        // Execute job immediately (synchronously): currently NOT supported
        try {
            $worker = new ConsistencyCheck();
            $worker->setLogger($this->logger);
            $worker->work($job);
        } catch (Exception $exc) {
            $this->logger->err($exc);
        }
        return false;
    }

    /**
     * @return string|null
     */
    public function getProcessingState()
    {
        if ($this->consistencyCheckLogfilePath === null) {
            return null; // unable to determine processing state
        }

        if (file_exists($this->consistencyCheckLogfilePath . '.lock')) {
            return 'inprogress'; // Operation is still in progress
        }

        if (! file_exists($this->consistencyCheckLogfilePath)) {
            return 'initial'; // Operation was never started before
        }

        if (! is_readable($this->consistencyCheckLogfilePath)) {
            $this->logger->err(
                "Log File $this->consistencyCheckLogfilePath exists but is not readable:"
                . " this might indicate a permission problem"
            );
            return null;
        }

        if (! $this->allowConsistencyCheck()) {
            return 'scheduled'; // Operation was not started yet
        }

        return 'completed';
    }

    /**
     * @return Admin_Model_IndexMaintenanceLogData|null
     */
    public function readLogFile()
    {
        if ($this->consistencyCheckLogfilePath === null || ! is_readable($this->consistencyCheckLogfilePath)) {
            return null;
        }

        $content = file_get_contents($this->consistencyCheckLogfilePath);

        if ($content === false || trim($content) === '') {
            // ignore: nothing to read
            return null;
        }

        $logdata = new Admin_Model_IndexMaintenanceLogData();
        $logdata->setContent($content);
        $lastModTime = filemtime($this->consistencyCheckLogfilePath);
        $logdata->setModifiedDate(date("d-m-y H:i:s", $lastModTime));
        return $logdata;
    }

    /**
     * @return bool
     */
    public function allowConsistencyCheck()
    {
        return Job::getCountForLabel(ConsistencyCheck::LABEL) === 0;
    }

    /**
     * @return false
     */
    public function allowFulltextExtractionCheck()
    {
        return false; // TODO OPUSVIER-2955
    }

    /**
     * @return false
     */
    public function allowIndexOptimization()
    {
        return false; // TODO OPUSVIER-2956
    }
}
