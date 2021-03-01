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
 * @package     Module_Admin
 * @author      Sascha Szott <szott@zib.de>
 * @author      Jens Schwidder <schwidder@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

use Opus\Config;
use Opus\Job;
use Opus\Log;
use Opus\Search\Task\ConsistencyCheck;

class Admin_Model_IndexMaintenance
{

    private $_config;

    private $_logger;

    private $_consistencyCheckLogfilePath = null;

    private $_featureDisabled = true;

    public function __construct($logger = null)
    {
        $this->_config = Config::get();
        $this->_logger = (is_null($logger)) ?  Log::get() : $logger;
        $this->setFeatureDisabled();

        if ($this->_featureDisabled) {
            return; // abort initialization
        }

        if (! isset($this->_config->workspacePath) || trim($this->_config->workspacePath) == '') {
            $this->_logger->err('configuration key \'workspacePath\' is not set correctly');
        } else {
            $this->_consistencyCheckLogfilePath = $this->_config->workspacePath . DIRECTORY_SEPARATOR . 'log'
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
        $jobsAsyncEnabled = isset($this->_config->runjobs->asynchronous)
            && filter_var($this->_config->runjobs->asynchronous, FILTER_VALIDATE_BOOLEAN);

        $indexMaintenanceConfigured = isset($this->_config->runjobs->indexmaintenance->asynchronous);
        $indexMaintenanceAsyncEnabled = $indexMaintenanceConfigured
            && filter_var($this->_config->runjobs->indexmaintenance->asynchronous, FILTER_VALIDATE_BOOLEAN);

        $this->_featureDisabled = ! ($indexMaintenanceAsyncEnabled ||
            ($jobsAsyncEnabled && ! $indexMaintenanceConfigured));
    }

    public function getFeatureDisabled()
    {
        return $this->_featureDisabled;
    }

    public function createJob()
    {
        $job = new Job();
        $job->setLabel(ConsistencyCheck::LABEL);

        if (! $this->_featureDisabled) {
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
            $worker = new Opus\Search\Task\ConsistencyCheck();
            $worker->setLogger($this->_logger);
            $worker->work($job);
        } catch (Exception $exc) {
            $this->_logger->err($exc);
        }
        return false;
    }

    public function getProcessingState()
    {
        if (is_null($this->_consistencyCheckLogfilePath)) {
            return null; // unable to determine processing state
        }

        if (file_exists($this->_consistencyCheckLogfilePath . '.lock')) {
            return 'inprogress'; // Operation is still in progress
        }

        if (! file_exists($this->_consistencyCheckLogfilePath)) {
            return 'initial'; // Operation was never started before
        }

        if (! is_readable($this->_consistencyCheckLogfilePath)) {
            $this->_logger->err(
                "Log File $this->_consistencyCheckLogfilePath exists but is not readable:"
                . " this might indicate a permission problem"
            );
            return null;
        }

        if (! $this->allowConsistencyCheck()) {
            return 'scheduled'; // Operation was not started yet
        }

        return 'completed';
    }

    public function readLogFile()
    {
        if (is_null($this->_consistencyCheckLogfilePath) || ! is_readable($this->_consistencyCheckLogfilePath)) {
            return null;
        }

        $content = file_get_contents($this->_consistencyCheckLogfilePath);

        if ($content === false || trim($content) == '') {
            // ignore: nothing to read
            return null;
        }

        $logdata = new Admin_Model_IndexMaintenanceLogData();
        $logdata->setContent($content);
        $lastModTime = filemtime($this->_consistencyCheckLogfilePath);
        $logdata->setModifiedDate(date("d-m-y H:i:s", $lastModTime));
        return $logdata;
    }

    public function allowConsistencyCheck()
    {
        return Job::getCountForLabel(Opus\Search\Task\ConsistencyCheck::LABEL) == 0;
    }

    public function allowFulltextExtractionCheck()
    {
        return false; // TODO OPUSVIER-2955
    }

    public function allowIndexOptimization()
    {
        return false; // TODO OPUSVIER-2956
    }
}
