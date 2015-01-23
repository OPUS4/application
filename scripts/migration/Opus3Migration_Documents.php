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
 * @package     Import
 * @author      Oliver Marahrens <o.marahrens@tu-harburg.de>
 * @author      Gunar Maiwald <maiwald@zib.de>
 * @copyright   Copyright (c) 2009-2011, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 * @version     $Id$
 */

// Configure include path
require_once dirname(__FILE__) . '/../common/bootstrap.php';
require_once 'Opus3Migration_Base.php';

set_include_path(
    '.' . PATH_SEPARATOR
    . PATH_SEPARATOR . dirname(dirname(dirname(__FILE__))) . '/scripts/migration/importer'
    . PATH_SEPARATOR . get_include_path()
);

require_once 'Opus3XMLImport.php';
require_once 'Opus3FileImport.php';


class Opus3Migration_Documents extends Opus3Migration_Base {

    private $_importFile;
    private $_importData;
    private $_stylesheet;
    private $_fulltextPath = array();
    private $_xslt;
    private $_start = null;
    private $_end = null;
    private $_doclist = array();
    private $_role = array();
    private $_lockFile;

    private $_status;
    
    const _FINISHED = "0";
    const _RUNNING = "1";

    /**
     * Constructur.
     *
     * @param array $options Array with input options.
     */
    function __construct($options) {
        parent::__construct();

        if (array_key_exists('f', $options) !== false) {
            $this->_importFile = $options["f"];
        }
        if (array_key_exists('p', $options) !== false) {
            array_push($this->_fulltextPath, $options["p"]);
        }
        if (array_key_exists('q', $options) !== false) {
            $this->_fulltextPath = preg_split('/\s+/', $options["q"]);
        }    
        if (array_key_exists('s', $options) !== false) {
            $this->_start = $options["s"];
        }
        if (array_key_exists('e', $options) !== false) {
            $this->_end  = $options["e"];
        }
        if (array_key_exists('l', $options) !== false) {
            $this->_lockFile  = $options["l"];
        }
    }

    // Import Documents
    private function load_documents() {
        $xmlImporter = new Opus3XMLImport($this->_xslt, $this->_stylesheet);
        $toImport = $xmlImporter->initImportFile($this->_importData);
        $totalCount = 0;

        $this->_status = self::_RUNNING;
    
        foreach ($toImport as $document) {
            $memNow = round(memory_get_usage() / 1024);
            $memPeak = round(memory_get_peak_usage() / 1024);

            $totalCount++;

            if (!(is_null($this->_start)) && ($totalCount < $this->_start)) {
                continue;
            }
            if (!(is_null($this->_end)) && ($totalCount > $this->_end)) {
                break;
            }

            $result = $xmlImporter->import($document);
            if ($result['result'] === 'success') {
                $this->_logger->log(
                    "Successfully imported old ID '" . $result['oldid'] . "' with new ID '" . $result['newid']
                    . "' -- memory $memNow (KB), peak memory $memPeak (KB)", Zend_Log::DEBUG
                );
                array_push($this->_doclist, $result['newid']);
                if (array_key_exists('roleid', $result)) {
                    $this->_role[$result['newid']] = $result['roleid'];
                }
            }
            else if ($result['result'] === 'failure') {
                $this->_logger->log(
                    $result['message'] . " for old ID '" . $result['oldid'] . "'\n" . $result['entry'], Zend_Log::ERR
                );
            }
        }

        if ($totalCount <= $this->_end) {
            $this->_status = self::_FINISHED;
        }

    }

    private function load_fulltext() {
        foreach ($this->_doclist as $id) {
            $role = null;
            if (array_key_exists($id, $this->_role)) {
                $role = $this->_role[$id];
            }
            foreach ($this->_fulltextPath as $path) {
                $fileImporter = new Opus3FileImport($id, $path, $role);

                $numberOfFiles = $fileImporter->loadFiles();

                $memNow = round(memory_get_usage() / 1024);
                $memPeak = round(memory_get_peak_usage() / 1024);

                if ($numberOfFiles > 0) {
                    $this->_logger->log(
                        $numberOfFiles . " file(s) have been imported successfully for document ID " . $id
                        . " -- memory $memNow (KB), peak memory $memPeak (KB)", Zend_Log::DEBUG
                    );
                }
            }
        }
        
    }

    public function getStatus() {
        return $this->_status;
    }

    private function setStylesheet() {
        $this->_stylesheet = 'stylesheets';
        $this->_xslt = 'opus3.xslt';
    }

    private function loadImportFile() {
        $this->_importData = new DOMDocument;
        $this->_importData->load($this->_importFile);
    }

    public function unlinkLockFile() {
        unlink($this->_lockFile);
    }

    public function run() {
        // Set XSLT-Stylesheet for Migration
        $this->setStylesheet();

        // Load Opus3-mySQL-XML-dump
        $this->loadImportFile();
        
        // Load Metadata
        $this->load_documents();

        // Load Fulltext
        $this->load_fulltext();

    }

}

// Bootstrap application.
$application = new Zend_Application(
    APPLICATION_ENV,
    array(
        "config"=>array(
            APPLICATION_PATH . '/application/configs/application.ini',
            APPLICATION_PATH . '/application/configs/config.ini',
            APPLICATION_PATH . '/application/configs/migration.ini',
            APPLICATION_PATH . '/application/configs/migration_config.ini'
        )
    )
);
$application->bootstrap(array('Configuration', 'Logging', 'Database'));

$options = getopt("f:p:q:s:e:l:");

// Start Opus3Migration
$migration = new Opus3Migration_Documents($options);
$migration->run();

if ($migration->getStatus() === Opus3Migration_Documents::_FINISHED) {
    $migration->unlinkLockFile();
}
