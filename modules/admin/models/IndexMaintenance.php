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
 * @copyright   Copyright (c) 2008-2013, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

class Admin_Model_IndexMaintenance {
    
    private $config;
    
    private $logger;
    
    public function __construct($logger) {
        $this->config = Zend_Registry::get('Zend_Config');
        $this->logger = $logger;
    }

    public function createJob() {
        $job = new Opus_Job();
        $job->setLabel(Opus_Job_Worker_ConsistencyCheck::LABEL);

        if (isset($this->config->runjobs->asynchronous) && $this->config->runjobs->asynchronous) {
            // Queue job (execute asynchronously)
            // skip creating job if equal job already exists
            if (true === $job->isUniqueInQueue()) {
                $job->store();
                return $job->getId();
            }
            return true;
        }

        // Execute job immediately (synchronously)
        try {
            $worker = new Opus_Job_Worker_ConsistencyCheck();
            $worker->setLogger($this->logger);
            $worker->work($job);
        }
        catch(Exception $exc) {
            $this->logger->err($exc);
        }
        return false;
    }

    public function readLogFile() {
        $logfilePath = $this->config->workspacePath . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . 'opus_consistency-check.log';

        if (!is_readable($logfilePath)) {
            return null;
        }

        $content = file_get_contents($logfilePath);

        if ($content === false || trim($content) == '') {
            // ignore: nothing to read
            return null;
        }
        
        $logdata = new Admin_Model_IndexMaintenanceLogData();
        $logdata->setContent($content);
        $lastModTime = filemtime($logfilePath);
        $logdata->setModifiedDate(date("d-m-y H:i:s", $lastModTime));
        return $logdata;
    }
    
    public function allowConsistencyCheck() {
        return Opus_Job::getCountForLabel(Opus_Job_Worker_ConsistencyCheck::LABEL) == 0;
    }
    
    public function allowFulltextExtractionCheck() {
        return true; // TODO
    }
    
    public function allowIndexOptimization() {
        return true; // TODO
    }
}
